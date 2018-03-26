<?php

namespace App\Jobs;

use App\Jobs\Job;
use App\Project;
use App\Version;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Storage;
use Image;
use Log;

class DownloadVersionPreview extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /*
     * Guzzle client.
     *
     * @var object
     */
    protected $client;

    /*
     * Browsershot client.
     *
     * @var object
     */
    protected $browsershot;

    /*
     * Current project model.
     *
     * @var object
     */
    protected $project;

    /*
     * Webarchive pages start url.
     *
     * @var string
     */
    const MAIN_URL = 'http://web.archive.org';

    /*
     * The default saved screenshot name.
     *
     * @var string
     */
    const DEFAULT_PREVIEW_NAME = 'preview_screenshot.jpg';

    /*
     * Page capture width default.
     *
     * @var string
     */
    const DEFAULT_SCREENSHOT_WIDTH = 1280;

    /*
     * Page capture height default.
     *
     * @var string
     */
    const DEFAULT_SCREENSHOT_HEIGHT = 1280;

    /*
     * Page capture height default.
     *
     * @var string
     */
    const DEFAULT_TIMEOUT = 5000;

    /*
     * The path for local saving of files.
     *
     * @var string
     */
    const PATH = '/app/domains/';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($project)
    {
        $this->project = $project;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 1) {
            exit;
        }

        $this->client      = new Client(['base_uri' => static::MAIN_URL, 'timeout' => 360]);
        $this->browsershot = new \Spatie\Browsershot\Browsershot();

        $this->saveScreenshots();
    }

    protected function saveScreenshots()
    {
        foreach($this->project->versions as $version) {
            if (!$status = $this->getFileContent($version->version_url)) {
                continue;
            }

            $this->preview($version);
        }
    }

    protected function storagePath($id)
    {
        $storage_path = 'app/domains/user_' . $this->project->user_id . '/project_' . $this->project->id .
            '/version_' . $id . '/';

        return $storage_path;
    }

    protected function preview($version)
    {
        $storage_path = $this->storagePath($version->id);
        try {
            $this->browsershot->setURL(static::MAIN_URL . $version->version_url)
                              ->setWidth(static::DEFAULT_SCREENSHOT_WIDTH)
                              ->setHeight(static::DEFAULT_SCREENSHOT_HEIGHT)
                              ->setTimeout(5000)
                              ->save(storage_path($storage_path) . static::DEFAULT_PREVIEW_NAME);
        } catch(Exeption $e) {
            return false;
        }

        $this->previewResize($storage_path);
        exec('chmod -R 0777 ' . storage_path(static::PATH . 'user_' .$this->project->user_id . '/project_' . $this->project->id));
    }

    protected function previewResize($storage_path)
    {
        if (!Storage::exists($storage_path . static::DEFAULT_PREVIEW_NAME)) {
            Image::make(storage_path($storage_path) . static::DEFAULT_PREVIEW_NAME)->fit(1024, 1024, function ($constraint) {
                $constraint->upsize();
            }, 'top')->save(storage_path($storage_path) . static::DEFAULT_PREVIEW_NAME);
        }
    }

    /**
     * Get content from web_archive.
     *
     * @param $url     link
     * @return array
     */
    protected function getFileContent($url)
    {
        try {
            $response = $this->client->request('GET', $url, [
                'http_errors' => false,
                'headers' => [
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko)
                                     Chrome/57.0.2987.133 Safari/537.36',
                    'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                ]
            ]);
        } catch (RequestException $e) {
            return false;
        }

        return $response->getStatusCode();
    }
}
