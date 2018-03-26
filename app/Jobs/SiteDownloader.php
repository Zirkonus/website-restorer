<?php

namespace App\Jobs;


use App\History;
use App\Version;
use Illuminate\Support\Facades\Session;
use LayerShifter\TLDExtract\Extract;
use Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\File;
use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Storage;

class SiteDownloader extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * Guzzle client.
     *
     * @var object
     */
    protected $client;

    /**
     * Current project model.
     *
     * @var object
     */
    protected $project;

    /**
     * Current version object.
     *
     * @var object
     */
    protected $version;

    /**
     * Current user id.
     *
     * @var int
     */
    protected $user_id;

    /**
     * Current history message.
     *
     * @var string
     */
    protected $message;

    /**
     * Scheme current Url ('http').
     *
     * @var string
     */
    protected $scheme;

    /**
     * Host current Url.
     *
     * @var string
     */
    protected $host;

    /**
     * Download depth(0 - unlim).
     *
     * @var integer
     */
    public $max_iteration = 2;

    /**
     * The time for which the execution of the script is postponed in the event of a response from the server(501, 503).
     *
     * @var integer
     */
    const BAD_REQUEST_TIMEOUT = 10;

    /**
     * Check for cancel flag.
     *
     * @var object
     */
    protected $is_cancel = false;


    /**
     * Flag that indicate wayback page.
     *
     * @var bool
     */
    protected $wayback_page = false;

    /**
     * The path for local saving of files.
     *
     * @var string
     */
    const PATH = 'domains/';

    /**
     * Webarchive pages start url.
     *
     * @var string
     */
    const MAIN_URL = 'http://web.archive.org';

    /**
     * Webarchive parse url host.
     *
     * @var string
     */
    const ARCHIVE_HOST = 'web.archive.org';

    /**
     * Template for parsing urls.
     *
     * @var string
     */
    const URL_PATERN = '/(\/web\/\d+\/)([^\"\)\*\s\']*)|(\/web\/\d+im_\/)([^\"\)\*\s\']*)|(\/web\/\d+js_\/)
                        ([^\"\)\*\s\']*)|(\/web\/\d+cs_\/)([^\"\)\*\s\']*)/';

    /**
     * Pointer for url, which is not present.
     *
     * @var string
     */
    const URL_DELETE = '###delete###';

    /**
     * Initial version status.
     */
    const BASIC_STATUS = 'basic';

    /**
     * The version status that the error occurred while downloading.
     */
    const ERROR_STATUS = 'error';

    /**
     * The version status that the error occurred while downloading.
     */
    const RESTORED_STATUS = 'restored';

    /**
     * The version status that was cancel.
     */
    const CANCEL_STATUS = 'cancel';

    /**
     * The version status that the error occurred while downloading.
     */
    const REPLACE_URL_PREFIX = '/getpage=';

    /**
     * The root directory path.
     */
    const ROOT_DIRECTORY = '/app/';

    /**
     * Wayback page text.
     */
    const WAYBACK_PAGE_TEXT = "Internet Archive's Wayback Machine";

    /**
     * References that are processed with an error.
     */
    const BAD_URL = ['http://about:blank', 'http://about:blank.html', 'javascript:void(0)', 'http://about:blank/.html'];

    /**
     * The version status that the error occurred while downloading.
     */
    const IMAGES_CONTENT_TYPE = ['image/gif', 'image/jpeg', 'image/pjpeg', 'image/png'];

    /**
     * The version status that the error occurred while downloading.
     */
    const HTML_CONTENT_TYPE = ['text/xml', 'text/html'];

    /**
     * Array of valid extensions
     */
    const VALID_EXTENSIONS = [
        '.ashx', '.asp', '.aspx', '.atom', '.bc', '.bc!', '.class', '.crdownload', '.css', '.dlc', '.download', '.eml',
        '.flv', '.gdoc', '.gif', '.gsheet', '.gslides', '.htm', '.html', '.jpg', '.js', '.json', '.jsp', '.jws', '.mht',
        '.opml', '.part', '.partial', '.php', '.png', '.rss', '.swf', '.torrent', '.webm', '.webp', '.xap', '.xhtml',
        '.xml', '.xsd', 'xsl', '.xslt', '.ico', '.eot', '.woff', '.ttf'
    ];


    /**
     * Create a new job instance.
     * Formation of initial data.
     *
     * @param  object $project
     * @param  string $version
     */
    public function __construct($project, $version)
    {
        $this->project = $project;
        $this->version = $version;

        preg_match('/\/(web)\/(\d+)\/(.*)/', $this->version->version_url, $parse_dir);
        $parse_url = parse_url($parse_dir[3]);
        $this->scheme = $parse_url['scheme'];
        $this->host = $parse_url['host'];

        $date_archive = strtotime($this->version->date_archive);
        $date_archive = date('d M Y, h:i A', $date_archive);
        $this->message = 'Could not restore ' . $this->project->domain . ' version from ' . $date_archive .
            ' <b>- no credits charged</b>';
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 2) {
            exit;
        }
        Session::push('parent_ids', 0);
        Session::put('iteration', 0);

        $this->client = new Client(['base_uri' => static::MAIN_URL, 'timeout' => 360]);
        $this->max_iteration = $this->project->fetch_level_deep;
        $this->getPages();

        if (!$this->is_cancel) {
            $this->version->job_id = 0;
            $this->version->status = static::RESTORED_STATUS;
            $this->version->save();

            $date_archive = strtotime($this->version->date_archive);
            $date_archive = date('d M Y, h:i A', $date_archive);
            $this->message = $this->project->domain . ' version from ' . $date_archive . ' successfully loaded';
            History::create([
                'user_id' => $this->project->user_id,
                'project_id' => $this->project->id,
                'message' => $this->message,
                'type' => 'file',
            ]);
        }

        Session::forget('parent_ids');
        Session::forget('iteration');
    }

    /**
     * Handling failed job
     */
    public function failed()
    {
        $this->version->job_id = 0;
        if ($this->version->status != static::BASIC_STATUS) {
            $this->version->status = static::ERROR_STATUS;
        }
        $this->version->save();

        History::create([
            'user_id' => $this->project->user_id,
            'project_id' => $this->project->id,
            'message' => $this->message,
            'type' => 'error',
        ]);

        $tmp_path = static::PATH . 'user_' . $this->project->user_id . '/project_' . $this->project->id .
            '/version_' . $this->version->id;

        exec('chmod 0777 ' . storage_path(static::ROOT_DIRECTORY) . $tmp_path);
        exec('chmod -R 0777 ' . storage_path(static::ROOT_DIRECTORY) . $tmp_path);
        exec('chmod 0777 ' . storage_path(static::ROOT_DIRECTORY) . static::PATH . 'user_' . $this->project->user_id . '/project_' . $this->project->id);

        if ($this->job) {
            return $this->job->failed();
        }
    }

    /**
     * Verify that the download has not been canceled.
     *
     * @param $version_id
     * @return bool
     */
    protected function checkCancel($version_id)
    {
        $this->version = Version::find($version_id);
        if (isset($this->version->status) && $this->version->status == static::CANCEL_STATUS) {
            File::where('version_id', $this->version->id)->delete();
            $path = 'domains/user_' . $this->project->user_id . '/project_' . $this->project->id . '/version_' .
                $this->version->id;
            if (Storage::exists($path)) {
                Storage::deleteDirectory($path);
            }
            $this->version->update(['status' => static::BASIC_STATUS, 'job_id' => 0]);

            $date_archive = strtotime($this->version->date_archive);
            $date_archive = date('d M Y, h:i A', $date_archive);
            $this->message = $this->project->domain . ' version from ' . $date_archive . ' successfully canceled';

            History::create([
                'user_id' => $this->project->user_id,
                'project_id' => $this->project->id,
                'message' => $this->message,
                'type' => 'error',
            ]);

            $this->is_cancel = true;

            return true;
        }

        return false;
    }

    /**
     * Checking the file for consistency Content-Type
     *
     * @param $haystack
     * @param $needle
     * @return bool
     */
    protected function checkContentType($haystack, $needle)
    {
        $check = strpos($haystack, $needle);
        if ($check !== false) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * The method to check if this file was previously processed.
     *
     * @param string $storage_path
     * @param string $web_path
     * @return mixed
     */
    protected function checkPathInDb($storage_path, $web_path)
    {
        $file = File::where('version_id', $this->version->id)
            ->where('storage_path', $storage_path)
            ->orWhere('web_path', $web_path)
            ->first();

        return $file;
    }

    /**
     * Clearing the content of files from references to webarchive.org
     *
     * @param string $content
     * @return string cleaned content
     */
    protected function clearContent($content)
    {
        $content = preg_replace('/\/\*\s*FILE ARCHIVED ON [^\*]*\*\//s', '', $content);

        return $content;
    }

    /**
     * Clearing html content from references to webarchive.org.
     *
     * @param string $content
     * @return string  cleaned content
     */
    protected function clearHtml($content)
    {
        $crawler = new Crawler($content);
        $crawler->filter("#wm-ipp")->each(function (Crawler $content) {
            foreach ($content as $node) {
                $node->parentNode->removeChild($node);
            }
        });
        $content = preg_replace('/\<!-- BEGIN WAYBACK TOOLBAR INSERT.*END WAYBACK TOOLBAR INSERT --\>/s', '', $content);
        $content = preg_replace('/\<head.*End Wayback Rewrite JS Include --\>/s', '<head>', $content);
        $content = preg_replace('/\<!--\s*FILE ARCHIVED ON [^\>]*\>/s', '', $content);
        return $content;
    }

    /**
     * Selection of html links to styles.
     *
     * @param string $content
     * @return array of links
     */
    protected function css($content)
    {
        $crawler = new Crawler($content);
        $css = $crawler->filter('link')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        return $css;
    }

    /**
     * Processing links to styles.
     *
     * @param array $css
     * @param integer $pid
     * @param string $content
     * @param string $base
     * @return string   content with replaced and processed links
     */
    protected function getCss($css, $pid, $content, $base = '')
    {
        $content = $this->getFile($css, $pid, $content, 'css', 'css', $base);

        return $content;
    }

    /**
     * Processing of attached files. Replacing Url in the current content.
     *
     * @param array $files_array
     * @param integer $pid
     * @param string $content
     * @param string $type
     * @param string $extension
     * @param string $base
     * @return string content with replaced and processed links
     */
    protected function getFile($files_array, $pid, $content, $type, $extension, $base)
    {
        $files_array = array_unique($files_array);
        foreach ($files_array as $item) {
            if ($item === null) {
                continue;
            }
            if (!$data = $this->parseLink($item, $base)) {
                continue;
            }

            if ($data['local']) {
                $url = $data['url'];
            } else {
                $url = $data['path'];
            }

            $file = $this->checkPathInDb($data['path'], $data['url']);
            if ($file === null) {
                $res = $this->getFileContent($url);
                list($data, $file) = $this->processingResponse($pid, $data, $res, $url, $type, $extension, $base);
            } else {
                $data['url_replace'] = $file->replace_url;

                if ($file->status == 'done') {
                    $data['path'] = $file->storage_path;
                }

                if ($file->status == 'delete') {
                    $data['path'] = static::URL_DELETE;
                }
            }

            if ($file->is_local == 1 && $data['path'] != static::URL_DELETE) {
                $data['path'] = static::REPLACE_URL_PREFIX . $file->id;

            }

            $content = $this->replaceLink($data['url_replace'], $data['path'], $content, $data['local']);

        }

        return $content;
    }

    /**
     * Getting file attribute.
     *
     * @param $storage_path
     * @return array
     */
    protected function getFileAttribute($storage_path)
    {
        $file_name = '';
        $file_ext = '';
        preg_match('/([^\/]+)(\.[a-zA-z0-9]+$)/', $storage_path, $parse_data);
        if (isset($parse_data[1])) {
            $file_name = $parse_data[1];
        }
        if (isset($parse_data[2])) {
            $file_ext = $parse_data[2];
        }

        return [$file_name, $file_ext];
    }

    /**
     * Running a webarchive request and retrieving content.
     *
     * @param $url
     * @return array
     */
    protected function getFileContent($url)
    {
        if (in_array($url, static::BAD_URL)) {
            return [
                'status' => '',
                'content' => '',
                'type' => ''
            ];
        }

        try {
            $response = $this->client->request('GET', $url, [
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)
                                     Chrome/57.0.2987.133 Safari/537.36',
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                ]
            ]);
        } catch (RequestException $e) {
            return [
                'status' => '',
                'content' => '',
                'type' => ''
            ];
        }

        $type = $response->getHeader('Content-Type');
        if (isset($type[0])) {
            $content_type = $type[0];
        } else {
            $content_type = '';
        }
        return [
            'status' => $response->getStatusCode(),
            'content' => $this->clearHtml($response->getBody()->getContents()),
            'type' => $content_type
        ];
    }

    /**
     * Processing links to iframes.
     *
     * @param array $iframes
     * @param integer $pid
     * @param string $content
     * @param string $base
     * @return string   content with replaced and processed links
     */
    protected function getIframes($iframes, $pid, $content, $base = '')
    {
        $content = $this->getFile($iframes, $pid, $content, 'iframes', '', $base);

        return $content;
    }

    /**
     * Processing image references.
     *
     * @param array $images
     * @param integer $pid
     * @param string $content
     * @param string $base
     * @return string  content with replaced and processed links
     */
    protected function getImages($images, $pid, $content, $base = '')
    {
        $content = $this->getFile($images, $pid, $content, 'images', 'png', $base);

        return $content;
    }

    /**
     * Handling of links to js scripts.
     *
     * @param array $js
     * @param integer $pid
     * @param string $content
     * @param string $base
     * @return string  content with replaced and processed links
     */
    protected function getJs($js, $pid, $content, $base = '')
    {
        $content = $this->getFile($js, $pid, $content, 'js', 'js', $base);

        return $content;
    }

    /**
     * Processing Links.
     *
     * @param array $links
     * @param integer $pid
     * @param string $content
     * @param string $base
     * @return string  content with replaced and processed links
     */
    protected function getLinks($links, $pid, $content, $base = '')
    {
        foreach ($links as $link) {
            if (!$link_data = $this->parseLink($link, $base)) {
                continue;
            }
            if ($link_data['local']) {
                $file = File::where('storage_path', $link_data['path'])->where('version_id', $this->version->id)->first();
                if ($file === null) {
                    $file = $this->saveFileInDb($pid, $link_data['url'], $link_data['path'], $link_data['url_replace'],
                        'text/html;charset=utf-8', 0, 'wait', $link_data['local']);
                }

                $link_data['path'] = static::REPLACE_URL_PREFIX . $file->id;
            }

            $content = $this->replaceLink($link_data['url_replace'], $link_data['path'], $content, $link_data['local']);
        }

        return $content;
    }

    /**
     * Recursively load pages of the current level.
     *
     */
    protected function getPages()
    {
        $parent_ids = Session::get('parent_ids');
        $iteration = Session::get('iteration');
        if ((empty($parent_ids) || $iteration >= $this->max_iteration) && $this->max_iteration != 0) {
            return;
        }
        if (in_array(0, $parent_ids)) {
            // Creating the first page with parent_id = 0
            $replace = $this->scheme . '://' . $this->host . '/';
            $files[] = $this->saveFileInDb(0, $this->version->version_url, '/index.html', $replace,
                'text/html;charset=utf-8', 0, 'wait', 1);
        } else {
            // Getting a list of sub-pages
            $files = File::whereIn('pid', $parent_ids)
                ->where('version_id', $this->version->id)
                ->where('status', 'wait')
                ->where('is_local', 1)
                ->get();
        }

        Session::forget('parent_ids');
        Session::forget('iteration');
        $parent_ids = [];

        foreach ($files as $file) {
            if ($this->checkCancel($this->version->id)) {
                return;
            }
            $title = '';
            $res = $this->getFileContent($file->web_path);
            list($res, $status) = $this->processLink($res, $file->web_path);

            if ($status != 'done') {
                $file->update(['status' => $status]);
                continue;
            }

            $content = $res['content'];
            $content = $this->clearHtml($content);

            // Getting links to files from content
            list($responseLinks, $base) = $this->links($content);
            $links = $this->localPages($responseLinks);
            $css = $this->css($content);
            $js = $this->js($content);
            $images = $this->images($content);
            $iframes = $this->iframes($content);

            // Link processing
            $content = $this->getLinks($links, $file->id, $content, $base);

            $content = $this->getImages($images, $file->id, $content, $base);
            $content = $this->getJs($js, $file->id, $content, $base);
            $content = $this->getCss($css, $file->id, $content, $base);
            $content = $this->getIframes($iframes, $file->id, $content, $base);
            if ($base != '') {
                if ($base_data = $this->parseLink($base)) {
                    $content = $this->replaceLink($base_data['url_replace'], '/', $content, true);
                }
            }

            if ($this->checkContentType($res['type'], 'text/html')) {
                $title = $this->title($content);
                $content = $this->removeEmpty($content);
            }

            $size = $this->saveFile($file->storage_path, $content);
            $file->update(['status' => 'done', 'file_size' => $size, 'file_content_type' => $res['type'], 'title' => $title]);
            Session::push('parent_ids', $file->id);
        }

        $iteration++;
        Session::put('iteration', $iteration);
        $this->getPages();
    }

    /**
     * Add prefix to local links
     *
     * @param $links
     * @return array
     */
    protected function localPages($links)
    {
        $domain = tld_extract($this->project->domain, Extract::MODE_ALLOW_ICCAN);
        $resultLinks = [];
        foreach ($links as $link) {
            if (preg_match('%^(https?://)|(www\.)|(\/\/)|(void)|(\()|(\))|(#)%i', $link) || strpos($link, $domain["hostname"] . "." . $domain["suffix"]) !== false) {
                $resultLinks[] = $link;
            } else if (preg_match("/(^\/[a-zA-Z]*)|(^[a-zA-Z])/", $link) && !preg_match('%^(https?://)|(www\.)|(\/\/)|(void)|(\()|(\))|(#)%i', $link) && strpos($link, $domain["hostname"] . "." . $domain["suffix"]) !== true) {
                $resultLinks[] = $this->version->version_url . "/" . $link;
            }
        }
        return array_unique($resultLinks);
    }

    /**
     * Response processing when loading pages
     *
     * @param $res
     * @param $url
     * @return array
     */
    protected
    function processLink($res, $url)
    {
        $status = 'done';
        switch ($res['status']) {
            case '200':
                $target = $this->redirectUrl($res['content']);
                if (!empty($target)) {
                    $data = $this->parseLink($target[0]);
                    if ($data['local'] == 1) {
                        $res = $this->getFileContent($data['url']);
                        if ($res['status'] == '200') {
                            break;
                        }
                    }
                    $status = 'delete';
                }
                break;
            case '301':
            case '301 Moved Permanently':
            case '302':
            case '302 Found':
            case '302 Moved Temporarily':
                $target = $this->redirectUrl($res['content']);
                if (!empty($target)) {
                    $data = $this->parseLink($target[0]);
                    if ($data['local'] == 1) {
                        $res = $this->getFileContent($data['url']);
                        if ($res['status'] == '200') {
                            break;
                        }
                    }
                    $status = 'delete';
                }
                break;
            case '404':
                $status = 'delete';
                break;
            case '500':
            case '501':
            case '503':
                sleep(static::BAD_REQUEST_TIMEOUT);
                $res = $this->getFileContent($url);
                if ($res['status'] != '200') {
                    $status = 'delete';
                }
                break;
            default:
                $status = 'delete';
                break;
        }
        return [$res, $status];
    }

    /**
     * Selection of html links to video.
     *
     * @param string $content
     * @return array of links
     */
    protected
    function iframes($content)
    {
        $crawler = new Crawler($content);
        $iframes = $crawler->filter('iframe')->each(function (Crawler $node, $i) {
            return $node->attr('src');
        });

        return $iframes;
    }

    /**
     * Selection of html links to images.
     *
     * @param string $content
     * @return array of links
     */
    protected
    function images($content)
    {
        $crawler = new Crawler($content);
        $images = $crawler->filter('img')->each(function (Crawler $node, $i) {
            return $node->attr('src');
        });

        return $images;
    }

    /**
     * Selection of html links to scripts.
     *
     * @param string $content
     * @return array of links
     */
    protected
    function js($content)
    {
        $crawler = new Crawler($content);
        $js = $crawler->filter('script')->each(function (Crawler $node, $i) {
            return $node->attr('src');
        });

        return $js;
    }

    /**
     * Selection of html links to other pages.
     *
     * @param string $content
     * @return array of links
     */
    protected
    function links($content)
    {
        $base_url = '';
        $crawler = new Crawler($content);
        $links = $crawler->filter('a')->each(function (Crawler $node, $i) {
            return $node->attr('href');
        });

        $base = $crawler->filter('base')->each(function (Crawler $node, $i) {
            return $node->attr('href');
        });

        if (isset($base[0])) {
            $base_url = $base[0];
        }

        return [$links, $base_url];
    }

    /**
     * Clear the link and split it into parts for further work.
     * Definition of a local reference.
     * Forming paths for local saving of the file and part of the reference for replacement.
     *
     * @param string $link link
     * @param string $base the contents of the tag <base>
     * @return array
     */
    protected
    function parseLink($link, $base = '')
    {
        $local = false;
        $wayback = '';
        $url_replace = '';
        $uri = '';
        $type = '';
        $parse_link = [];
        $relative_link = [];
        $parse = parse_url($link);

        if (isset($parse['host']) && isset($parse['path']) && $parse['host'] == static::ARCHIVE_HOST) {
            $link = $parse['path'];
            if (isset($parse['query'])) {
                $link .= '?' . $parse['query'];
            }
        }
        // Selecting a web console and working reference
        preg_match('/(^\/web\/\d+\/)(.*)|(^\/web\/\d+im_\/)(.*)|(^\/web\/\d+js_\/)(.*)|(^\/web\/\d+cs_\/)(.*)|(^\/web\/\d+if_\/)(.*)/', $link, $parse_link);

        if (empty($parse_link)) {
            preg_match('/(^\w+\:)(.*)|(^\w+)(.*)/', $link, $relative_link);
            if (isset($relative_link[3]) && $base != '') {
                return $this->parseLink($base . $link, $base);
            }
            return false;
        }
        if (isset($parse_link[1])) {
            $wayback .= $parse_link[1];
        }
        if (isset($parse_link[3])) {
            $wayback .= $parse_link[3];
            $type = 'im';
        }
        if (isset($parse_link[5])) {
            $wayback .= $parse_link[5];
            $type = 'js';
        }
        if (isset($parse_link[7])) {
            $wayback .= $parse_link[7];
            $type = 'cs';
        }
        if (isset($parse_link[7])) {
            $wayback .= $parse_link[7];
            $type = 'if';
        }
        if (isset($parse_link[2])) {
            $uri .= $parse_link[2];
        }
        if (isset($parse_link[4])) {
            $uri .= $parse_link[4];
        }
        if (isset($parse_link[6])) {
            $uri .= $parse_link[6];
        }
        if (isset($parse_link[8])) {
            $uri .= $parse_link[8];
        }
        if (isset($parse_link[10])) {
            $uri .= $parse_link[10];
        }

        $data = parse_url($uri);
        if (!isset($data['host']) && isset($data['path'])) {
            $data = parse_url($data['path']);
        }
        if (isset($data['host']) && $data['host'] == $this->host || isset($data['host']) && strpos($data['host'], $this->host) || isset($data['host']) && strpos($this->host, $data['host'])) {
            $local = true;

            if (!isset($data['path'])) {
                $data['path'] = '/';
            }

            $url_replace = $data['scheme'] . '://' . $data['host'] . $data['path'];
            $path = $data['path'];
            if (substr($data['path'], -1) == '/' || $data['path'] == '/') {
                $path .= 'index';
            }

            preg_match('/\.\w+$/', $data['path'], $parse_path);
            if (empty($parse_path)) {
                if (!in_array($path, static::BAD_URL))
                    $path .= '.html';
            }
        } else {
            $path = $uri;
        }

        list($file_name, $file_ext) = $this->getFileAttribute($path);

        if (!in_array($file_ext, static::VALID_EXTENSIONS)) {
            $path .= '.html';
        }

        if ($base != '' && $local) {
            if (substr($base, -1) == '/' && $data['path'] != '/') {
                $url_replace = substr($data['path'], 1);
            } else {
                $url_replace = $data['path'];
            }
        }
        return [
            'local' => $local,
            'wayback' => $wayback,
            'path' => $path,
            'url' => $link,
            'url_replace' => $url_replace,
            'type' => $type
        ];
    }

    /**
     * Handling the response received when downloading content from webarchive.
     *
     * @param integer $pid
     * @param array $data
     * @param array $res
     * @param string $url
     * @param string $type
     * @param string $extension
     * @param string $base
     * @return mixed
     */
    protected
    function processingResponse($pid, $data, $res, $url, $type, $extension, $base)
    {
        $is_recursive = true;
        $status = 'done';

        switch ($res['status']) {
            case '':
                $status = 'delete';
                break;
            case '200':
                if ($data['type'] != '' && $this->checkContentType($res['type'], 'text/html')) {
                    $status = 'delete';
                    break;
                }
                $target = $this->redirectUrl($res['content']);
                if (!empty($target)) {
                    $data = $this->parseLink($target[0]);
                    if ($data['local'] == 1) {
                        $res = $this->getFileContent($data['url']);
                        if ($res['status'] == '200') {
                            break;
                        }
                    }
                    $status = 'delete';
                }
                break;

            case '301':
            case '301 Moved Permanently':
            case '302':
            case '302 Found':
            case '302 Moved Temporarily':
                $target = $this->redirectUrl($res['content']);
                if (!empty($target)) {
                    $data = $this->parseLink($target[0]);
                    if ($data['local'] == 1) {
                        $res = $this->getFileContent($data['url']);
                        if ($res['status'] != '200') {
                            $status = 'delete';
                            break;
                        }
                    }
                    $status = 'delete';
                }
                break;
            case '404':
                if (!$data['local']) {
                    $res = $this->getFileContent($data['url']);
                    if ($res['status'] == '200' && !$this->checkContentType($res['type'], 'text/html')) {
                        $data['url_replace'] = $data['path'];
                        $data['path'] = '/' . $type . '/' . str_random(10) . '.' . $extension;
                        $data['local'] = true;
                        $is_recursive = false;
                        break;
                    }
                }
                $status = 'delete';
                break;
            case '501':
            case '503':
                sleep(static::BAD_REQUEST_TIMEOUT);
                $res = $this->getFileContent($url);
                if ($res['status'] != '200') {
                    $status = 'delete';
                }
                break;
            default:
                if (!$data['local']) {
                    $res = $this->getFileContent($data['url']);
                    if ($res['status'] == '200' && !$this->checkContentType($res['type'], 'text/html')) {
                        $data['url_replace'] = $data['path'];
                        $data['path'] = '/' . $type . '/' . str_random(10) . '.' . $extension;
                        $data['local'] = true;
                        $is_recursive = false;
                        break;
                    }
                }
                $status = 'delete';
        }

        $path = $data['path'];
        $size = 0;
        if ($status == 'done') {
            if ($data['local']) {
                if ($type != 'images') {
                    $res['content'] = $this->clearContent($res['content']);
                    if ($is_recursive) {
                        preg_match_all(static::URL_PATERN, $res['content'], $new_urls);
                        $res['content'] = $this->getFile($new_urls[0], $pid, $res['content'], $type, $extension, $base);
                    }

                    if ($this->checkContentType($res['type'], 'text/html')) {
                        $res['content'] = $this->removeEmpty($res['content']);
                    }
                }
                $size = $this->saveFile($data['path'], $res['content']);
            }

        } else {
            if (!$data['local']) {
                $data['url_replace'] = $data['path'];
            }
            $data['path'] = static::URL_DELETE;
        }
        $title = $this->title($res['content']);
        $file = $this->saveFileInDb($pid, $data['url'], $path, $data['url_replace'], $res['type'], $size, $status,
            $data['local'], $title);
        return [$data, $file];
    }

    /**
     * Clear content from missing items.
     *
     * @param string $content
     * @return string  content with replaced and processed links
     */
    protected
    function removeEmpty($content)
    {
        $crawler = new Crawler($content);

        // Cleaning content from non-working links
        $crawler->filter('a')->each(function (Crawler $crawler) {
            preg_match('/^\#\#\#delete\#\#\#/', $crawler->attr('href'), $res);
            if (!empty($res) && isset($res[0])) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        });

        // Cleaning content from non-working images
        $crawler->filter('img')->each(function (Crawler $crawler) {
            preg_match('/^\#\#\#delete\#\#\#/', $crawler->attr('src'), $res);
            if (!empty($res) && isset($res[0])) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        });

        // Cleaning content from non-working scripts
        $crawler->filter('script')->each(function (Crawler $crawler) {
            preg_match('/^\#\#\#delete\#\#\#/', $crawler->attr('src'), $res);
            if (!empty($res) && isset($res[0])) {
                foreach ($crawler as $node) {

                    $node->parentNode->removeChild($node);
                }
            }
        });

        // Cleaning content from non-working styles
        $crawler->filter('link')->each(function (Crawler $crawler) {
            preg_match('/^\#\#\#delete\#\#\#/', $crawler->attr('href'), $res);
            if (!empty($res) && isset($res[0])) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        });

        // Cleaning content from non-working iframes
        $crawler->filter('iframe')->each(function (Crawler $crawler) {
            preg_match('/^\#\#\#delete\#\#\#/', $crawler->attr('src'), $res);
            if (!empty($res) && isset($res[0])) {
                foreach ($crawler as $node) {
                    $node->parentNode->removeChild($node);
                }
            }
        });

        $new_content = '';
        foreach ($crawler as $domElement) {
            $new_content .= $domElement->ownerDocument->saveHTML($domElement);
        }
        return $new_content;
    }

    /**
     * Replace the url in the content.
     *
     * @param string $search
     * @param string $replace
     * @param string $content
     * @param boolean $local
     * @return string  content with replaced and processed links
     */
    protected
    function replaceLink($search, $replace, $content, $local)
    {

        $content = preg_replace('/(' . preg_quote(static::MAIN_URL, '/') . '\/web\/\d+\/)|(' . preg_quote(static::MAIN_URL, '/') .
            '\/web\/\d+im_\/)|(' . preg_quote(static::MAIN_URL, '/') . '\/web\/\d+js_\/)|(' .
            preg_quote(static::MAIN_URL, '/') . '\/web\/\d+cs_\/)|(' . preg_quote(static::MAIN_URL, '/') .
            '\/web\/\d+if_\/)/', '', $content);

        $content = preg_replace('/(\/\/' . preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+\/)|(\/\/' .
            preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+im_\/)|(\/\/' . preg_quote(static::ARCHIVE_HOST, '/') .
            '\/web\/\d+js_\/)|(\/\/' . preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+cs_\/)|(\/\/' .
            preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+if_\/)/', '', $content);

        $content = preg_replace('/(\/web\/\d+\/)|(\/web\/\d+im_\/)|(\/web\/\d+js_\/)|(\/web\/\d+cs_\/)|(\/web\/\d+if_\/)/', '', $content);

        if ($search != '') {
            $content = str_replace('"' . static::MAIN_URL . $search . '"', '"' . $replace . '"', $content);
            $content = str_replace("'" . static::MAIN_URL . $search . "'", "'" . $replace . "'", $content);
            $content = str_replace(' ' . static::MAIN_URL . $search . '"', ' ' . $replace . '"', $content);
            $content = str_replace(" " . static::MAIN_URL . $search . "'", " " . $replace . "'", $content);
            $content = str_replace(':' . static::MAIN_URL . $search . '"', ':' . $replace . '"', $content);
            $content = str_replace(":" . static::MAIN_URL . $search . "'", ":" . $replace . "'", $content);
            $content = str_replace(' ' . static::MAIN_URL . $search . ")", ' ' . $replace . ")", $content);
            $content = str_replace(':' . static::MAIN_URL . $search . ")", ':' . $replace . ")", $content);
            $content = str_replace('(' . static::MAIN_URL . $search . ")", '(' . $replace . ")", $content);
            $content = str_replace(' ' . static::MAIN_URL . $search . "*", ' ' . $replace . "*", $content);
            $content = str_replace(':' . static::MAIN_URL . $search . "*", ':' . $replace . "*", $content);
            $content = str_replace('(' . static::MAIN_URL . $search . "*", '(' . $replace . "*", $content);
            $content = str_replace('"' . static::MAIN_URL . $search . "?", '"' . $replace . "?", $content);
            $content = str_replace("'" . static::MAIN_URL . $search . "?", "'" . $replace . "?", $content);
            $content = str_replace(' ' . static::MAIN_URL . $search . "?", ' ' . $replace . "?", $content);
            $content = str_replace(':' . static::MAIN_URL . $search . "?", ':' . $replace . "?", $content);
            $content = str_replace('(' . static::MAIN_URL . $search . "?", '(' . $replace . "?", $content);


            $content = str_replace('"' . $search . '"', '"' . $replace . '"', $content);
            $content = str_replace("'" . $search . "'", "'" . $replace . "'", $content);
            $content = str_replace(' ' . $search . '"', ' ' . $replace . '"', $content);
            $content = str_replace(" " . $search . "'", " " . $replace . "'", $content);
            $content = str_replace(':' . $search . '"', ':' . $replace . '"', $content);
            $content = str_replace(":" . $search . "'", ":" . $replace . "'", $content);
            $content = str_replace(' ' . $search . ")", ' ' . $replace . ")", $content);
            $content = str_replace(':' . $search . ")", ':' . $replace . ")", $content);
            $content = str_replace('(' . $search . ")", '(' . $replace . ")", $content);
            $content = str_replace(' ' . $search . "*", ' ' . $replace . "*", $content);
            $content = str_replace(':' . $search . "*", ':' . $replace . "*", $content);
            $content = str_replace('(' . $search . "*", '(' . $replace . "*", $content);
            $content = str_replace('"' . $search . "?", '"' . $replace . "?", $content);
            $content = str_replace("'" . $search . "?", "'" . $replace . "?", $content);
            $content = str_replace(' ' . $search . "?", ' ' . $replace . "?", $content);
            $content = str_replace(':' . $search . "?", ':' . $replace . "?", $content);
            $content = str_replace('(' . $search . "?", '(' . $replace . "?", $content);
            $content = str_replace('"' . $search . "#", '"' . $replace . "#", $content);
            $content = str_replace("'" . $search . "#", "'" . $replace . "#", $content);
            $content = str_replace(' ' . $search . "#", ' ' . $replace . "#", $content);
            $content = str_replace(':' . $search . "#", ':' . $replace . "#", $content);
            $content = str_replace('(' . $search . "#", '(' . $replace . "#", $content);
        }
        if ($local) {
            $parsedUrl = parse_url($search);
            $search = $parsedUrl["path"];
            $search = preg_replace("/^\//", '', $search);
            $content = preg_replace('/(' . preg_quote(static::MAIN_URL, '/') . '\/web\/\d+\/)|(' . preg_quote(static::MAIN_URL, '/') .
                '\/web\/\d+im_\/)|(' . preg_quote(static::MAIN_URL, '/') . '\/web\/\d+js_\/)|(' .
                preg_quote(static::MAIN_URL, '/') . '\/web\/\d+cs_\/)|(' . preg_quote(static::MAIN_URL, '/') .
                '\/web\/\d+if_\/)/', '', $content);

            $content = preg_replace('/(\/\/' . preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+\/)|(\/\/' .
                preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+im_\/)|(\/\/' . preg_quote(static::ARCHIVE_HOST, '/') .
                '\/web\/\d+js_\/)|(\/\/' . preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+cs_\/)|(\/\/' .
                preg_quote(static::ARCHIVE_HOST, '/') . '\/web\/\d+if_\/)/', '', $content);

            $content = preg_replace('/(\/web\/\d+\/)|(\/web\/\d+im_\/)|(\/web\/\d+js_\/)|(\/web\/\d+cs_\/)|(\/web\/\d+if_\/)/', '', $content);

            if ($search != '') {
                $content = str_replace('"' . static::MAIN_URL . $search . '"', '"' . $replace . '"', $content);
                $content = str_replace("'" . static::MAIN_URL . $search . "'", "'" . $replace . "'", $content);
                $content = str_replace(' ' . static::MAIN_URL . $search . '"', ' ' . $replace . '"', $content);
                $content = str_replace(" " . static::MAIN_URL . $search . "'", " " . $replace . "'", $content);
                $content = str_replace(':' . static::MAIN_URL . $search . '"', ':' . $replace . '"', $content);
                $content = str_replace(":" . static::MAIN_URL . $search . "'", ":" . $replace . "'", $content);
                $content = str_replace(' ' . static::MAIN_URL . $search . ")", ' ' . $replace . ")", $content);
                $content = str_replace(':' . static::MAIN_URL . $search . ")", ':' . $replace . ")", $content);
                $content = str_replace('(' . static::MAIN_URL . $search . ")", '(' . $replace . ")", $content);
                $content = str_replace(' ' . static::MAIN_URL . $search . "*", ' ' . $replace . "*", $content);
                $content = str_replace(':' . static::MAIN_URL . $search . "*", ':' . $replace . "*", $content);
                $content = str_replace('(' . static::MAIN_URL . $search . "*", '(' . $replace . "*", $content);
                $content = str_replace('"' . static::MAIN_URL . $search . "?", '"' . $replace . "?", $content);
                $content = str_replace("'" . static::MAIN_URL . $search . "?", "'" . $replace . "?", $content);
                $content = str_replace(' ' . static::MAIN_URL . $search . "?", ' ' . $replace . "?", $content);
                $content = str_replace(':' . static::MAIN_URL . $search . "?", ':' . $replace . "?", $content);
                $content = str_replace('(' . static::MAIN_URL . $search . "?", '(' . $replace . "?", $content);


                $content = str_replace('"' . $search . '"', '"' . $replace . '"', $content);
                $content = str_replace("'" . $search . "'", "'" . $replace . "'", $content);
                $content = str_replace(' ' . $search . '"', ' ' . $replace . '"', $content);
                $content = str_replace(" " . $search . "'", " " . $replace . "'", $content);
                $content = str_replace(':' . $search . '"', ':' . $replace . '"', $content);
                $content = str_replace(":" . $search . "'", ":" . $replace . "'", $content);
                $content = str_replace(' ' . $search . ")", ' ' . $replace . ")", $content);
                $content = str_replace(':' . $search . ")", ':' . $replace . ")", $content);
                $content = str_replace('(' . $search . ")", '(' . $replace . ")", $content);
                $content = str_replace(' ' . $search . "*", ' ' . $replace . "*", $content);
                $content = str_replace(':' . $search . "*", ':' . $replace . "*", $content);
                $content = str_replace('(' . $search . "*", '(' . $replace . "*", $content);
                $content = str_replace('"' . $search . "?", '"' . $replace . "?", $content);
                $content = str_replace("'" . $search . "?", "'" . $replace . "?", $content);
                $content = str_replace(' ' . $search . "?", ' ' . $replace . "?", $content);
                $content = str_replace(':' . $search . "?", ':' . $replace . "?", $content);
                $content = str_replace('(' . $search . "?", '(' . $replace . "?", $content);
                $content = str_replace('"' . $search . "#", '"' . $replace . "#", $content);
                $content = str_replace("'" . $search . "#", "'" . $replace . "#", $content);
                $content = str_replace(' ' . $search . "#", ' ' . $replace . "#", $content);
                $content = str_replace(':' . $search . "#", ':' . $replace . "#", $content);
                $content = str_replace('(' . $search . "#", '(' . $replace . "#", $content);
            }
        }

        return $content;
    }

    /**
     * Local file saving.
     *
     * @param string $path
     * @param string $content
     * @return int  file size
     */
    protected
    function saveFile($path, $content)
    {
        $tmp_path = static::PATH . 'user_' . $this->project->user_id . '/project_' . $this->project->id .
            '/version_' . $this->version->id;
        $path = $tmp_path . $path;

        if (!Storage::exists($path)) {
            Storage::put($path, $content, 'public');

            exec('chmod -R 0777 ' . storage_path(static::ROOT_DIRECTORY) . $tmp_path);
            exec('chmod 0777 ' . storage_path(static::ROOT_DIRECTORY) . $path);
            exec('chmod 0777 ' . storage_path(static::ROOT_DIRECTORY) . static::PATH . 'user_' . $this->project->user_id . '/project_' . $this->project->id);

            return Storage::size($path);
        } else {
            return Storage::size($path);
        }
    }

    /**
     * Saving information about the file in the database.
     *
     * @param integer $pid Id of the parent page
     * @param string $web_path Web file path
     * @param string $storage_path Local path to the file
     * @param string $replace_url Url replace prefix
     * @param string $content_type file Сontent-Type
     * @param integer $size file size
     * @param string $status Result of file processing
     * @param string $local checking external file or internal
     * @param string $title title of page
     * @return object File
     */
    protected
    function saveFileInDb($pid, $web_path, $storage_path, $replace_url, $content_type, $size, $status, $local, $title = '')
    {
        list($file_name, $file_ext) = $this->getFileAttribute($storage_path);
        if (!in_array($file_ext, static::VALID_EXTENSIONS) && count($file_ext) > 0) {
            $file_name .= $file_ext;
            $file_ext = '.html';
        }

        $new_file = new File;
        $new_file->pid = $pid;
        $new_file->file_name = $file_name;
        $new_file->file_ext = $file_ext;
        $new_file->file_content_type = $content_type;
        $new_file->file_size = $size;
        $new_file->title = $title;
        $new_file->is_local = $local;
        $new_file->version_id = $this->version->id;
        $new_file->web_path = $web_path;
        $new_file->storage_path = $storage_path;
        $new_file->replace_url = $replace_url;
        $new_file->status = $status;

        $this->version->files()->save($new_file);

        return $new_file;
    }

    /**
     * Sampling from the html title.
     *
     * @param string $content
     * @return string
     */
    protected
    function title($content)
    {
        preg_match('/<title>(.*)(\s*)<\/title>/', $content, $parse_data);
        if (isset($parse_data[1])) {
            $title = $parse_data[1];
        } else {
            $title = '';
        }

        return $title;
    }

    /**
     * Selection of html links to scripts.
     *
     * @param string $content
     * @return array of links
     */
    protected
    function redirectUrl($content)
    {
        $crawler = new Crawler($content);
        $target = $crawler->filter('p.impatient a')->each(function (Crawler $node) {
            return $node->attr('href');
        });

        return $target;
    }

}
