<?php

namespace App\Http\Controllers;

use App\File;
use App\History;
use App\Jobs\DownloadVersionPreview;
use App\Project;
use App\Version;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Validator;
use Storage;
use Carbon\Carbon;
use Screen\Capture;
use Image;
use App\Setting;

class ProjectController extends DashboardController
{
    /*
     * The default saved screenshot name.
     *
     * @var string
     */
    const DEFAULT_PREVIEW_NAME = 'preview_screenshot.jpg';

    /*
     * Guzzle client.
     *
     * @var object
     */
    protected $client;

    /*
     * Webarchive version prefix.
     *
     * @var string
     */
    const GET_VERSION_URL = '/__wb/calendarcaptures';

    /*
     * For how many years to choose the version from Webarchive.
     *
     * @var string
     */
    protected $get_version_year_count;

    /**
     * Generate version url prefix.
     *
     * @var string
     */
    const VERSION_PREFIX = '/web/';

    /*
     * Create a new controller instance. Init Guzzle client, set configs/
     *
     * @return void
     */
    public function __construct()
    {
        $this->client = new Client(['base_uri' => static::MAIN_URL, 'timeout' => 360]);
        $this->setConfigs();
    }

    /**
     * Domain validation.
     *
     * @param Request $request
     * @return string
     */
    public function checkDomain(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'domain' => 'required|url'
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->project = Project::where('domain', $request->domain)->where('user_id', Auth::user()->id)->first(['id']);

        if (isset($this->project->id)) {
            $this->formationResponse(1, 'Such project already exists.');
        } else {
            $this->formationResponse(0, '');
        }

        return $this->returnResponse();
    }

    /**
     * Delete project.
     *
     * @param Request $request
     * @return string
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->project = Project::find($request->id);
        if ($this->checkProject()) {
            return $this->returnResponse();
        }

        if (count($this->project->versions()->whereIn('status', [static::IN_PROGRESS_STATUS, static::CANCEL_STATUS])->get()) > 0) {
            $this->formationResponse(1, 'The project has versions in processing.');
            return $this->returnResponse();
        }


        $versions = $this->project->versions()->get(['id']);
        $version_ids = array_pluck($versions->toArray(), 'id');

        File::whereIn('version_id', $version_ids)->delete();
        Version::where('project_id', $this->project->id)->delete();

        $path = 'domains/user_' . Auth::user()->id . '/project_' . $this->project->id;
        if (Storage::exists($path)) {
            Storage::deleteDirectory($path);
        }

        $message = $this->project->domain . ' - project was deleted';
        $user_id = $this->project->user_id;

        if ($this->project->delete()) {
            $this->formationResponse(0, 'Project successfully deleted');
            History::create([
                'user_id'    => $user_id,
                'project_id' => $this->project->id,
                'message'    => $message,
                'type'       => static::HISTORY_DELETE,
            ]);
        } else {
            $this->formationResponse(1, 'Project deleting error.');
        }

        return $this->returnResponse();
    }

    /**
     * Get project and related versions data.
     *
     * @param Request $request
     * @return string
     */
    public function get(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->project = Project::find($request->id);

        if (!$this->checkProject()) {
            $this->project->ftp_password = base64_decode($this->project->ftp_password);
            $this->formationResponse(0, '', $this->project->toArray());
        }

        return $this->returnResponse();
    }

    /**
     * Get projects list for dashboard
     *
     * @return string
     */
    public function getList()
    {
        $projects = Project::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->get();

        if ($projects->isEmpty()) {
            $this->formationResponse(0, '');
        } else {
            $response_data = [];
            foreach ($projects as $project) {
                $total_size              = 0;
                $versions                = $project->versions()->get();
                $uploaded_versions       = $project->versions()->where('status', static::RESTORED_STATUS)->get();
                $in_progress_versions    = $project->versions()->where('status', static::IN_PROGRESS_STATUS)->count();
                $versions_count          = $versions->count();
                $uploaded_versions_count = $uploaded_versions->count();
                $status                  = $project->history()->orderBy('id', 'DESC')->first(['type', 'message']);
                $upload                  = $project->versions()->orderBy('upload_date', 'DESC')
                                                               ->first(['upload_date', 'upload_size']);
                $download                = $project->versions()->orderBy('download_date', 'DESC')
                                                               ->first(['download_date', 'download_size']);

                foreach ($uploaded_versions as $version) {
                    $total_size += $version->files()->sum('file_size');
                }




                $res                      = $project->toArray();
                $res['sites_in_progress'] = $in_progress_versions;
                $res['download_size']     = isset($download->download_size) ? round($download->download_size/static::MBYTE, 1) : '';
                $res['upload_size']       = isset($upload->upload_size) ? round($upload->upload_size/static::MBYTE, 1) : '';

                if ($upload and $upload->upload_date) {
                    $upload_date        = strtotime($upload->upload_date);
                    $upload_date        = date('d M y', $upload_date);
                } else {
                    $upload_date        = '';
                }

                if ($download and $download->download_date) {
                    $download_date       = strtotime($download->download_date);
                    $download_date        = date('d M y', $download_date);
                } else {
                    $download_date        = '';
                }

                $res['upload_date']       = $upload_date;
                $res['download_date']     = $download_date;
                $res['last_status']       = isset($status->type) ? $status->type : '';
                $res['last_message']      = isset($status->message) ? $status->message : '';
                $res['sites_total']       = $versions_count;
                $res['sites_uploaded']    = $uploaded_versions_count;
                $res['files_size']        = round($total_size/static::MBYTE, 1);

                $response_data[]          = $res;
            }

            $this->formationResponse(0, '', $response_data);
        }

        return $this->returnResponse();
    }

    /**
     * Create new project and save available versions
     *
     * @param Request $request
     * @return string
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'domain'            => 'required|url',
            'niche'             => 'string|max:255',
            'fetch_level_deep'  => 'required|integer',
            'ftp_address'       => 'string|max:255',
            'ftp_port'          => 'integer',
            'ftp_username'      => 'string|max:255',
            'ftp_folder'        => 'string|max:255',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->project = Project::firstOrNew([
            'domain'  => $request->domain,
            'user_id' => Auth::user()->id
        ]);

        $this->project->fetch_level_deep = $request->fetch_level_deep;

        if (isset($request->niche)) {
            $this->project->niche = $request->niche;
        }

        if (isset($request->ftp_address)) {
            $this->project->ftp_address = $request->ftp_address;
        }

        if (isset($request->ftp_port)) {
            $this->project->ftp_port = $request->ftp_port;
        }

        if (isset($request->ftp_username)) {
            $this->project->ftp_username = $request->ftp_username;
        }

        if (isset($request->ftp_password)) {
            $this->project->ftp_password = base64_encode($request->ftp_password);
        }

        if (isset($request->ftp_folder)) {
            $this->project->ftp_folder = $request->ftp_folder;
        }

        if ($this->project->save()) {
            $res = $this->getVersions($this->project->domain);
            if(isset($res['error']) && $res['error'] != 1) {
                foreach ($res['versions'] as $key => $value) {
                    $url = static::VERSION_PREFIX . $value . '/' . $this->project->domain;
                    $version = $this->project->versions()->create(['date_archive' => $value, 'status' => 'basic', 'version_url' => $url]);
                }

                $job = (new DownloadVersionPreview($this->project))->onQueue('preview');
                $this->dispatch($job);

                $message = $this->project->domain . ' - setup successful';
                History::create([
                    'user_id'    => $this->project->user_id,
                    'project_id' => $this->project->id,
                    'message'    => $message,
                    'type'       => static::HISTORY_FILE,
                ]);

                $this->formationResponse(0, 'Project successfully created.');
            } else {
                $this->formationResponse(1, $res['versions']);
            }
        } else {
            $this->formationResponse(1, 'Failed to create project.');
        }

        return $this->returnResponse();
    }



    /**
     * Update project data
     *
     * @param Request $request
     * @return string
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'                => 'required|integer',
            'niche'             => 'string|max:255',
            'ftp_address'       => 'string|max:255',
            'ftp_port'          => 'integer',
            'ftp_username'      => 'string|max:255',
            'ftp_folder'        => 'string|max:255',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->project = Project::find($request->id);

        if ($this->checkProject()) {
            return $this->returnResponse();
        }

        if (isset($request->niche)) {
            $this->project->niche = $request->niche;
        }

        if (isset($request->ftp_address)) {
            $this->project->ftp_address = $request->ftp_address;
        }

        if (isset($request->ftp_port)) {
            $this->project->ftp_port = $request->ftp_port;
        }

        if (isset($request->ftp_username)) {
            $this->project->ftp_username = $request->ftp_username;
        }

        if (isset($request->ftp_password)) {
            $this->project->ftp_password = base64_encode($request->ftp_password);
        }

        if (isset($request->ftp_folder)) {
            $this->project->ftp_folder = $request->ftp_folder;
        }

        $this->project->updated_at = Carbon::now();

        if ($this->project->save()) {
            $this->formationResponse(0, 'Project successfully saved.');
        } else {
            $this->formationResponse(1, 'Project update error.');
        }

        return $this->returnResponse();
    }

    /**
     * Get available versions.
     *
     * @param $url site Url
     * @return array versions array
     */
    protected function getVersions($url)
    {
        $versions     = [];
        $date         = Carbon::now();
        $current_year = $date->year;
        for ($i = 0; $i < $this->get_version_year_count; $i++) {
            $file = $this->getFileContent($url, $current_year - $i);
            if ($file['status'] != '200' || $file['content'] == '') {
                return ['error' => 1, 'versions' => 'Problems with the web.archive.org server, try again later.'];
            }

            $versions[] = $this->parseVersion($file['content']);
        }

        $versions = array_collapse($versions);

        return ['error' => 0, 'versions' => $versions];
    }

    /**
     * Parsing links to versions.
     *
     * @param $content
     * @return array
     */
    protected function parseVersion($content)
    {
        $data = \GuzzleHttp\json_decode($content);
        $res  = [];
        foreach ($data as $row) {
            foreach ($row as $column) {
                foreach ($column as $item) {
                    if (isset($item->ts)) {
                        $res[] = $item->ts[0];
                    }
                }
            }
        }

        return $res;
    }

    /**
     * Get content from web_archive.
     *
     * @param $url     link
     * @return array
     */
    protected function getFileContent($url, $year)
    {
        try {
            $response = $this->client->request('GET', static::GET_VERSION_URL, [
                    'http_errors' => false,
                    'query' => [
                        'url' => $url,
                        'selected_year' => $year
                    ]
                ]
            );

        } catch (RequestException $e) {
            return [
                'status'  => '',
                'content' => '',
                'type'    => ''
            ];
        }

        $content_type = $response->getHeader('Content-Type');
        return [
            'status'  => $response->getStatusCode(),
            'content' => $response->getBody()->getContents(),
            'type'    => $content_type[0]
        ];
    }

    protected function setConfigs()
    {
        $this->get_version_year_count = 10;
    }
}
