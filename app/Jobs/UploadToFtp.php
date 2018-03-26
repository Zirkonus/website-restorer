<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\History;
use App\Version;
use App\File;
use Illuminate\Support\Facades\Storage;
use League\Flysystem\Sftp\SftpAdapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Adapter\Ftp as Adapter;
use Carbon\Carbon;

class UploadToFtp extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /*
     * Current project model.
     *
     * @var object
     */
    protected $project;

    /*
     * Current version object.
     *
     * @var object
     */
    protected $version;

    /*
     * Settings for ftp connection
     *
     * @var array
     */
    protected $config = [
        'host'          => '',
        'port'          => 21,
        'username'      => '',
        'password'      => '',
        'privateKey'    => '',
        'root'          => '',
        'timeout'       => 60,
        'directoryPerm' => 0777

    ];

    /*
     * Default start path to the files
     *
     * @var string
     */
    protected $path = 'domains/user_';

    /*
     * Current history message.
     *
     * @var string
     */
    protected $message;

    /*
     * Check for errors flag.
     *
     * @var object
     */
    protected $error = false;

    /*
     * Array of file paths.
     *
     * @var array
     */
    protected $files = [];


    /*
     * The default folder path.
     *
     * @var string
     */
    protected $ftp_path = '/';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($version)
    {
        $this->version = $version;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if ($this->attempts() > 1) {
            $this->failed();
        }

        if ($this->checkVersion()) {
            $this->failed();
        }

        if ($this->setProject()) {
            $this->failed();
        }

        if ($this->error || $this->setPath()) {
            $this->failed();
        }

        if ($this->error || $this->getFilesPath()) {
            $this->failed();
        }

        $this->setConfig();
        switch($this->config['port']) {
            case 21:
                $adapter = new Adapter($this->config);
                break;
            case 22:
                $adapter = new SftpAdapter($this->config);
                break;
            default:
                $this->failed();
        }

        try {
            $adapter->connect();
        } catch(\Exception $e) {
            $this->failed();
        }

        $filesystem  = new Filesystem($adapter);
        $upload_size = 0;
        foreach ($this->files as $file) {
            $new_path     = $this->parsePath($file);
            $content      = $this->replaceLinks($file);
            $upload_size += Storage::size($file);
            if (!$filesystem->put($this->ftp_path . $new_path, $content)) {
                $this->failed();
            }
        }

        $this->version->upload_date = Carbon::now();
        $this->version->upload_size = $upload_size;
        $this->version->save();

        $this->message = $this->project->domain . ' successfully uploaded to ftp';
        History::create([
            'user_id'    => $this->project->user_id,
            'project_id' => $this->project->id,
            'message'    => $this->message,
            'type'       => 'upload',
        ]);


    }

    /**
     * Handling failed job
     */
    public function failed()
    {
        $this->errorMessage();

        if ($this->job) {
            return $this->job->failed();
        }
    }

    /**
     * Check that the version still exists.
     *
     * @return bool
     */
    protected function checkVersion()
    {
        if (!isset($this->version->id)) {
            $this->error = true;
        }

        return $this->error;
    }

    /**
     * Setting project data.
     *
     * @return bool
     */
    protected function setProject()
    {
        $this->project = $this->version->project()->first();
        if (!isset($this->project->id)) {
            $this->error = true;
        }

        return $this->error;
    }

    /**
     * Path setting.
     *
     * @return bool
     */
    protected function setPath()
    {
        $this->path .= $this->project->user_id . '/project_' . $this->project->id . '/version_' . $this->version->id;

        if (!Storage::exists($this->path)) {
            $this->error = true;
        }

        return $this->error;
    }

    /**
     *
     * Setting preferences.
     */
    protected function setConfig()
    {
        if (isset($this->project->ftp_address)) {
            $this->config['host'] = $this->project->ftp_address;
        }

        if (isset($this->project->ftp_port)) {
            $this->config['port'] = $this->project->ftp_port;
        }

        if (isset($this->project->ftp_username)) {
            $this->config['username'] = $this->project->ftp_username;
        }

        if (isset($this->project->ftp_password)) {
            $this->config['password'] = base64_decode($this->project->ftp_password);
        }

        if (isset($this->project->ftp_folder)) {
            $this->ftp_path = $this->project->ftp_folder;
        }

        $this->ftp_path = $this->setFtpPath($this->ftp_path );
    }

    /**
     * FTP Path setting
     *
     * @param $path
     * @return string
     */
    protected function setFtpPath($path)
    {
        if (substr($path, -1) != '/') {
            $path .= '/';
        }

        return $path;
    }

    /**
     * Getting paths to version files.
     *
     * @return bool
     */
    protected function getFilesPath()
    {
        $this->files = Storage::allFiles($this->path);
        if (empty($this->files)) {
            $this->error = true;
        }

        return $this->error;
    }

    /**
     * Parsing path.
     *
     * @param $path
     * @return string
     */
    protected function parsePath($path)
    {
        $parse_path = [];
        $local_path = '';
        $pattern = '/domains\/user_' . $this->project->user_id . '\/project_' . $this->project->id . '\/version_' .
                   $this->version->id .'(.*)/';
        preg_match($pattern, $path, $parse_path);
        if (isset($parse_path[1]) && !empty($parse_path[1])) {
            $local_path = $parse_path[1];
        }

        return $local_path;
    }

    /**
     * Replacing links in the content.
     *
     * @param $file
     * @return mixed
     */
    protected function replaceLinks($file)
    {
        $content = Storage::get($file);
        $ids     = [];
        preg_match_all('/\/getpage\=([0-9]+)/s', $content, $parse_data);
        if (isset($parse_data[1]) && !empty($parse_data[1])) {
            $ids = $parse_data[1];
        }

        foreach ($ids as $id) {
            $file_data = File::find($id);
            if (isset($file_data->id)) {
                $content = str_replace('/getpage=' . $file_data->id, $file_data->storage_path, $content);
            }
        }

        return $content;
    }

    /**
     * Creating and recording history.
     */
    protected function errorMessage()
    {
        $domain = isset($this->project->domain) ? $this->project->domain : '';
        $this->message = 'Could not finish uploading ' . $domain . ' to FTP';
        History::create([
            'user_id'    => isset($this->project->user_id) ? $this->project->user_id : '',
            'project_id' => isset($this->project->id) ? $this->project->id : '',
            'message'    => $this->message,
            'type'       => 'error',
        ]);
    }

}
