<?php

namespace App\Http\Controllers;

use App\File;
use App\Job;
use App\Jobs\FileDownload;
use App\Jobs\ReloadPreviews;
use App\Jobs\SiteDownloader;
use App\Jobs\UploadToFtp;
use App\Project;
use App\Version;
use App\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Validator;
use Storage;
use Chumper\Zipper\Facades\Zipper;
use Carbon\Carbon;
use Log;

class VersionController extends DashboardController
{
    /**
     * The version status that the error occurred while downloading.
     */
    const IMAGES_CONTENT_TYPE = ['image/jpeg', 'image/pjpeg', 'image/png'];

    /*
     * The path for local saving of files.
     *
     * @var string
     */
    const PATH = '/app/domains/';

    /*
     * Webarchive version prefix.
     *
     * @var string
     */
    const GET_VERSION_URL = '/web/*/';

    /**
     * The history type.
     */
    const PER_PAGE       = 15;

    /*
     * The default saved screenshot name.
     *
     * @var string
     */
    const DEFAULT_PREVIEW_NAME = 'preview_screenshot.jpg';

    /**
     * Cancel version download
     *
     * @param Request $request
     * @return string
     */
    public function cancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->id);
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        if ($this->version->status == static::IN_PROGRESS_STATUS) {
            if (!Job::where('id', $this->version->job_id)->where('reserved', 0)->delete()) {
                $this->version->status = static::CANCEL_STATUS;
                $this->version->save();
                $this->formationResponse(0, 'Version during the deletion process.', array('is_canceled' => 0));
            } else {
                $this->destroy();
                $this->formationResponse(0, 'Version successfully deleted', array('is_canceled' => 1));
            }
        } else {
            $this->formationResponse(1, 'This version is not in the download queue.');
        }

        return $this->returnResponse();
    }

    /**
     * Check for cancellation of version download.
     *
     * @param Request $request
     * @return string
     */
    public function checkCancel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->id);
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        if ($this->version->status == static::CANCEL_STATUS) {
            $this->formationResponse(0, 'Version during the deletion process.', array('is_canceled' => 0));
        } else {
            $this->formationResponse(0, 'Version successfully deleted', array('is_canceled' => 1));
        }

        return $this->returnResponse();
    }

    /**
     * Delete restored version
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

        $this->version = Version::where('id', $request->id)->whereIn('status', [static::RESTORED_STATUS, static::ERROR_STATUS])->first();
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        $this->destroy();
        $this->formationResponse(0, 'Version successfully deleted');

        return $this->returnResponse();
    }

    /**
     * Deleting files and update version
     */
    protected function destroy()
    {
        File::where('version_id', $this->version->id)->delete();
        $path = 'domains/user_' . Auth::user()->id . '/project_' . $this->project->id . '/version_' . $this->version->id;
        if (Storage::exists($path)) {
            Storage::deleteDirectory($path);
        }
        $this->version->update(['status' => static::BASIC_STATUS]);

        $date_archive = strtotime($this->version->date_archive);
        $date_archive = date('d M Y, h:i A', $date_archive);
        $message      = $this->project->domain . ' version from ' . $date_archive . ' successfully deleted';
        $user_id      = $this->project->user_id;
        History::create([
            'user_id'    => $user_id,
            'project_id' => $this->project->id,
            'message'    => $message,
            'type'       => static::HISTORY_DELETE,
        ]);

        return;
    }

    /**
     * Add current version to the download queue.
     *
     * @param Request $request
     * @return string
     */
    public function download(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->id);

        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        if ($this->version->status == static::ERROR_STATUS) {
            $this->destroy();
        }

        if ($this->version->status == static::BASIC_STATUS) {
            $job = (new SiteDownloader($this->project, $this->version))->onQueue('download');
            if ($job_id = $this->dispatch($job)) {
                $this->version->job_id = $job_id;
                $this->version->status = static::IN_PROGRESS_STATUS;
                if (!$this->version->save()) {
                    $this->formationResponse(1, 'Status update error.');
                } else {
                    $date_archive = strtotime($this->version->date_archive);
                    $date_archive = date('d M Y, h:i A', $date_archive);
                    $message      = $this->project->domain . ' version from ' . $date_archive . ' successfully added to the download queue';
                    $user_id      = $this->project->user_id;
                    History::create([
                        'user_id'    => $user_id,
                        'project_id' => $this->project->id,
                        'message'    => $message,
                        'type'       => static::HISTORY_FILE,
                    ]);
                    $this->formationResponse(0, 'Version added to the download queue.');
                }
            } else {
                $this->formationResponse(1, 'Error adding a version to the download queue.');
            }
        } else {
            $this->formationResponse(1, 'This version has already been downloaded.');
        }

        return $this->returnResponse();
    }

    /**
     * Get all images for current version.
     *
     * @param Request $request
     * @return string
     */
    public function getImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::where('id', $request->id)->where('status', static::RESTORED_STATUS)->first();
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        $images = $this->version->files()->whereIn('file_content_type', static::IMAGES_CONTENT_TYPE)
            ->where('is_local', 1)
            ->where('status', 'done')
            ->orderBy('id', 'DESC')
            ->get(['id', 'storage_path']);
        $response_data = [];
        foreach ($images as $image) {
            $data['id']                           = $image->id;
            $data['storage_path']                 = $image->storage_path;
            list($data['width'], $data['height']) = getimagesize(storage_path() . static::PATH . 'user_' .
                Auth::user()->id . '/project_' . $this->project->id .
                '/version_' . $this->version->id . $image->storage_path);

            $response_data[] = $data;
        }
        $this->formationResponse(0, '', $response_data);
        return $this->returnResponse();
    }


    /**
     * Get versions list
     *
     * @param Request $request
     * @param string $status
     * @return string
     */
    public function getList(Request $request, $status = '')
    {
        $validator = Validator::make($request->all(), [
            'id'   => 'required|integer',
            'page' => 'integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->project = Project::find($request->id);
        if ($this->checkProject()) {
            return $this->returnResponse();
        }

        switch($status) {
            case '':
                $versions = $this->project->versions()->orderBy('date_archive', 'DESC')->get();
                break;
            case 'in_progress':
                $versions = $this->project->versions()->whereIn('status', [static::IN_PROGRESS_STATUS, static::ERROR_STATUS])
                    ->orderBy('date_archive', 'DESC')
                    ->get();
                break;
            case 'restored':
                $versions = $this->project->versions()->where('status', static::RESTORED_STATUS)
                    ->orderBy('date_archive', 'DESC')
                    ->get();
                break;
        }

        if (isset($request->page)) {
            $page = $request->page;
        } else {
            $page = 1;
        }

        $response_data = [];
        if ($page * static::PER_PAGE < $versions->count()) {
            $response_data['is_more'] = 1;
        } else {
            $response_data['is_more'] = 0;
        }

        $versions = $versions->slice(($page-1) * static::PER_PAGE, static::PER_PAGE)->all();
        foreach ($versions as $version) {
            $files_count           = $version->files()->where('status', 'done')
                ->where('is_local', 1)
                ->count();
            $total_size            = $version->files()->where('status', 'done')
                ->where('is_local', 1)
                ->sum('file_size');
            $images_count          = $version->files()->whereIn('file_content_type', static::IMAGES_CONTENT_TYPE)
                ->where('status', 'done')
                ->where('is_local', 1)
                ->count();

            $images_size           = $version->files()->whereIn('file_content_type', static::IMAGES_CONTENT_TYPE)
                ->where('status', 'done')
                ->where('is_local', 1)
                ->sum('file_size');

            $date_archive          = strtotime($version->date_archive);
            $version->date_archive = date('d M Y', $date_archive);
            $date_updated          = strtotime($version->updated_at);
            $date_updated          = date('d M Y, h:i A', $date_updated);
            $res                   = $version->toArray();
            $res['version_url']    = static::MAIN_URL . $version->version_url;
            $res['updated_at']     = $date_updated;
            $res['files_total']    = $files_count;
            $res['images_total']   = $images_count;
            $res['files_size']     = round($total_size/static::MBYTE, 1);
            $res['images_size']    = round($images_size/static::MBYTE, 1);

            if ($version->status == static::RESTORED_STATUS) {
                $home_page        = $version->files()->where('pid', 0)
                    ->where('status', 'done')
                    ->where('is_local', 1)
                    ->first(['id']);
                if (isset($home_page->id)) {
                    $home_page_id     = $home_page->id;
                    $res['home_page'] = $home_page_id;
                }
            }

            $response_data['versions'][]       = $res;
        }

        $this->formationResponse(0, '', $response_data);

        return $this->returnResponse();
    }

    /**
     * Get pages tree.
     *
     * @param Request $request
     * @return string
     */
    public function getPages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->id);
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        if ($this->version->status == static::RESTORED_STATUS) {
            $page = $this->version->files()->where('pid', 0)->first(['id', 'title', 'web_path']);
            if (isset($page->id)) {
                $response_data = $page->toArray();
                $response_data['web_path'] = static::MAIN_URL . $response_data['web_path'];
                $response_data['children'] = $this->getTree($response_data['id']);
            } else {
                $response_data = [];
            }
            $this->formationResponse(0, '', $response_data);
        } else {
            $this->formationResponse(1, 'This version is not yet downloaded.');
        }

        return $this->returnResponse();
    }

    public function getPreview(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->id);
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        $path = 'domains/user_' . $this->project->user_id . '/project_' . $this->project->id .
            '/version_' . $this->version->id . '/'  . static::DEFAULT_PREVIEW_NAME;
        if (Storage::exists($path)) {
            if (Storage::size($path) == 3188) {
                Storage::delete($path);
                $job = (new ReloadPreviews($this->project))->onQueue('reload');
                $this->dispatch($job);
                return '';
            } else {
                $content = Storage::get($path);
                return response($content)->header('Content-Type', 'image/jpg');
            }
        } else {
            $job = (new ReloadPreviews($this->project))->onQueue('reload');
            $this->dispatch($job);
            return '';
        }
    }

    /**
     * Upload project files in zip
     *
     * @param Request $request
     * @return string|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->id);
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        $this->prepareFiles();

        $path = storage_path() . '/app/' . 'domains/user_' . Auth::user()->id . '/project_' . $this->project->id .
            '/upload_' . $this->version->id;
        $zip_path = $path . '.zip';
        if (Storage::exists($zip_path)) {
            Storage::delete($zip_path);
        }

        $zipper = Zipper::make($zip_path)->add($path);
        $zipper->close();
        exec('chmod 0777 ' . $zip_path);

        $this->version->download_date = Carbon::now();
        $this->version->download_size = $this->getDirectorySize();
        $this->version->save();

        return response()->download($zip_path);
    }

    /**
     * Create a task to unload files on ftp.
     *
     * @param Request $request
     * @return string
     */
    public function uploadToFtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->id);
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        if ($this->version->status == static::RESTORED_STATUS) {
            $job = (new UploadToFtp($this->version))->onQueue('upload');
            $this->dispatch($job);
            $this->formationResponse(0, 'Version added to the upload queue.');
        } else {
            $this->formationResponse(1, 'This version is not yet downloaded.');
        }

        return $this->returnResponse();
    }

    /**
     * Tree formation
     *
     * @param $id
     * @return array
     */
    protected function getTree($id)
    {
        $res_data = [];
        $pages    = $this->version->files()->where('pid', $id)
            ->where('file_content_type', 'like', '%html%')
            ->where('status', 'done')
            ->where('is_local', 1)
            ->get(['id', 'title', 'web_path']);

        if ($pages->count() > 0) {
            foreach ($pages as $key => $value) {
                $data                       = $value->toArray();
                $res_data[$key]['id']       = $data['id'];
                $res_data[$key]['web_path'] = static::MAIN_URL . $data['web_path'];
                $res_data[$key]['title']    = $data['title'];
                $res_data[$key]['children'] = $this->getTree($data['id']);
            }
        }

        return $res_data;
    }

    /**
     * Preparation of files for uploading (replacement of links).
     */
    protected function prepareFiles()
    {
        $path = 'domains/user_' . Auth::user()->id . '/project_' . $this->project->id . '/version_' . $this->version->id;
        $copy_path = 'domains/user_' . Auth::user()->id . '/project_' . $this->project->id . '/upload_' . $this->version->id;

        if (Storage::exists($copy_path)) {
            Storage::deleteDirectory($copy_path);
        }
        $files = Storage::allFiles($path);

        foreach ($files as $file) {
            $ids = [];
            $local_path = '/';
            $pattern = '/domains\/user_' . Auth::user()->id . '\/project_' . $this->project->id . '\/version_' .
                $this->version->id .'(.*)/';
            preg_match($pattern, $file, $parse_path);
            if (isset($parse_path[1]) && !empty($parse_path[1])) {
                $local_path = $parse_path[1];
            }

            $content = Storage::get($file);
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

            Storage::put($copy_path . $local_path, $content);
            exec('chmod -R 0777 ' . storage_path() . '/app/' . $copy_path);
        }
    }

    /**
     * Getting paths to version files.
     *
     * @return bool
     */
    protected function getFilesPath()
    {
        $path = 'domains/user_' . Auth::user()->id . '/project_' . $this->project->id . '/version_' . $this->version->id;
        $files = Storage::allFiles($path);

        return $files;
    }

    /**
     * Getting paths to version files.
     *
     * @return bool
     */
    protected function getDirectorySize()
    {
        $files = $this->getFilesPath();
        $upload_size = 0;

        foreach ($files as $file) {
            $upload_size += Storage::size($file);
        }

        return $upload_size;
    }
}