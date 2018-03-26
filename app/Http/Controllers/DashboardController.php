<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;

class DashboardController extends Controller
{
    /*
     * Current project model.
     *
     * @var object
     */
    protected $project;

    /*
     * Current version object.
     *
     * @var string
     */
    protected $version;

    /*
     * Current file model.
     *
     * @var object
     */
    protected $file;

    /*
     * Check for errors flag.
     *
     * @var object
     */
    protected $error = false;

    /**
     * Status of the version that is in the download queue.
     */
    const IN_PROGRESS_STATUS = 'in_progress';

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
     * The history type.
     */
    const HISTORY_CREDITS = 'credits';

    /**
     * The history type.
     */
    const HISTORY_FILE = 'file';

    /**
     * The history type.
     */
    const HISTORY_ERROR = 'error';

    /**
     * The history type.
     */
    const HISTORY_DELETE = 'delete';

    /**
     * The history type.
     */
    const HISTORY_UPLOAD = 'upload';

    /*
     * Webarchive pages start url.
     *
     * @var string
     */
    const MAIN_URL = 'http://web.archive.org';

    /**
     * The version status that the error occurred while downloading.
     *
     * @var string
     */
    const MBYTE = 1048576;

    /*
     * Server response data format
     *
     * @var array
     */
    protected $response_data = [
        'errors' => ['status'  => 0, 'message' => ''],
        'data'   => []
    ];

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('result');
    }

    /**
     * Response formation
     *
     * @param $status
     * @param $message
     * @param array $data
     * @return string
     */
    protected function formationResponse($status, $message, $data = array())
    {
        $this->response_data['errors']['status']  = $status;
        $this->response_data['errors']['message'] = $message;
        $this->response_data['data']              = $data;

        return;
    }

    /**
     * Creating a json response format
     *
     * @return string
     */
    protected function returnResponse()
    {
        return json_encode($this->response_data);
    }

    /**
     * Check that the project exists and the user has access to it.
     *
     * @return bool
     */
    protected function checkProject()
    {
        if (!isset($this->project->id) || !isset(Auth::user()->id) || $this->project->user_id != Auth::user()->id) {
            $this->error = true;
            $this->formationResponse(1, 'Such project not exists.');
        }

        return $this->error;
    }

    /**
     * Check that the version exists and the user has access to it.
     *
     * @return bool
     */
    protected function checkVersion()
    {
        if (!isset($this->version->id)) {
            $this->error = true;
            $this->formationResponse(1, 'There is no such version.');
        } else {
            $this->project = $this->version->project()->first();
            $this->checkProject();
        }

        return $this->error;
    }

    /**
     * Check that the file exists and the user has access to it.
     *
     * @return bool
     */
    protected function checkFile()
    {
        if (!isset($this->file->id)) {
            $this->error = true;
            $this->formationResponse(1, 'There is no such file.');
        } else {
            $this->version = $this->file->version()->first();
            if (!isset($this->version->status) || !in_array($this->version->status, [static::RESTORED_STATUS, static::ERROR_STATUS])) {
                $this->error = true;
                $this->formationResponse(1, 'There is no such version.');
            } else {
                $this->checkVersion();
            }
        }

        return $this->error;
    }
}
