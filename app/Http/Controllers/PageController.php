<?php

namespace App\Http\Controllers;

use App\File;
use App\Version;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use Validator;
use Storage;

class PageController extends DashboardController
{
    /**
     * The version status that the error occurred while downloading.
     */
    const IMAGES_CONTENT_TYPE = ['image/jpeg', 'image/pjpeg', 'image/png'];

    /*
     * The path for local saving of images.
     *
     * @var string
     */
    const IMAGES_PATH = '/images/';

    /*
     * The path for local saving of files.
     *
     * @var string
     */
    const PATH = 'domains';

    /**
     * Delete pages and images
     *
     * @param Request $request
     * @return string
     */
    public function delete(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer'
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->file = File::find($request->id);
        if ($this->checkFile()) {
            return $this->returnResponse();
        }

        if (isset($this->file->pid) && $this->file->pid != 0) {
            // Fixme сlarify the logic of replacement
            File::where('pid', $this->file->id)->update(['pid' => $this->file->pid]);

            $path = $this->path();
            if (Storage::exists($path)) {
                Storage::delete($path);
            }

            if ($this->file->delete()) {
                $this->formationResponse(0, 'Delete successful.');
            } else {
                $this->formationResponse(1, 'File deleting error.');
            }
        } else {
            $this->formationResponse(1, 'The home page can not be deleted.');
        }

        return $this->returnResponse();
    }

    /**
     * Add an image
     *
     * @param Request $request
     * @return string
     */
    public function addImages(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image'      => 'required|image',
            'version_id' => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->version = Version::find($request->version_id);
        if ($this->checkVersion()) {
            return $this->returnResponse();
        }

        $image_name      = str_random(10);
        $image_ext       = '.' . $request->file('image')->getClientOriginalExtension();
        $image_size      = $request->file('image')->getClientSize();
        $image_mime_type = $request->file('image')->getClientMimeType();

        $parent = File::where('version_id', $this->version->id)->where('pid', 0)->first(['id']);

        $this->file                    = new File;
        $this->file->pid               = $parent->id;
        $this->file->version_id        = $request->version_id;
        $this->file->file_name         = $image_name;
        $this->file->file_ext          = $image_ext;
        $this->file->file_content_type = $image_mime_type;
        $this->file->file_size         = $image_size;
        $this->file->title             = '';
        $this->file->is_local          = 1;
        $this->file->web_path          = '';
        $this->file->storage_path      = static::IMAGES_PATH . $image_name . $image_ext;
        $this->file->replace_url       = '';
        $this->file->status            = 'wait';

        if (!$this->file->save()) {
            $this->formationResponse(1, 'Error creating image.');
            return $this->returnResponse();
        }

        $storage_path = storage_path() . '/app/' . static::PATH . '/user_' . Auth::user()->id . '/project_' .
                        $this->project->id . '/version_' . $this->version->id . static::IMAGES_PATH;

        $request->file('image')->move($storage_path, $image_name . $image_ext);
        $image_size = Storage::size(static::PATH . '/user_' . Auth::user()->id . '/project_' .
            $this->project->id . '/version_' . $this->version->id . static::IMAGES_PATH . $image_name . $image_ext);

        $data['id']                           = $this->file->id;
        $data['storage_path']                 = $this->file->storage_path;
        list($data['width'], $data['height']) = getimagesize($storage_path . $image_name . $image_ext);

        $response_data[] = $data;

        $this->file->update([
            'file_size'    => $image_size,
            'status'       => 'done'
        ]);

        $this->formationResponse(0, 'Image successfully saved.', $response_data);

        return $this->returnResponse();
    }

    /**
     * Get file content by id
     *
     * @param $id
     * @return $this|string
     */
    public function getPage($id)
    {
        $validator = Validator::make(array('id' => $id), [
            'id'  => 'required|integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->file = File::where('id', $id)
                          ->where('is_local', 1)
                          ->where('status', 'done')
                          ->first(['id', 'version_id', 'storage_path', 'file_content_type']);

        if ($this->checkFile()) {
            // TODO no such file response
            return '';
        }

        $path = $this->path();
        if (Storage::exists($path)) {
            $content = Storage::get($path);
            return response($content)->header('Content-Type', $this->file->file_content_type);
        } else {
            // TODO file not found response
            return '';
        }

    }

    /**
     * Change the page title
     *
     * @param Request $request
     * @return string
     */
    public function rename(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'     => 'required|integer',
            'title'  => 'required|string|max:255'
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->file = File::where('id', $request->id)
                          ->where('is_local', 1)
                          ->where('status', 'done')
                          ->where('file_content_type', 'like', '%html%')
                          ->first();

        if ($this->checkFile()) {
            return $this->returnResponse();
        }

        $this->file->title = $request->title;
        if ($this->file->save()) {
            $path = $this->path();
            if (Storage::exists($path)) {
                $content = Storage::get($path);
                $content = preg_replace('/<title>.*\s*<\/title>/', '<title>' . $request->title . '</title>', $content);
                Storage::put($path, $content);
            }
            $this->formationResponse(0, 'Update successful.');
        } else {
            $this->formationResponse(1, 'File updating error.');
        }

        return $this->returnResponse();
    }

    /**
     * Adding a new page.
     *
     * @param Request $request
     * @return string
     */
    public function save(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'source_id' => 'required|integer',
            'parent_id' => 'required|integer',
            'title'     => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->file = File::where('id', $request->source_id)
                          ->where('is_local', 1)
                          ->where('status', 'done')
                          ->where('file_content_type', 'like', '%html%')
                          ->first();

        if ($this->checkFile()) {
            return $this->returnResponse();
        }

        $source_file = $this->file;

        $this->file = File::where('id', $request->parent_id)
                          ->where('is_local', 1)
                          ->where('status', 'done')
                          ->where('file_content_type', 'like', '%html%')
                          ->first();

        if ($this->checkFile()) {
            return $this->returnResponse();
        }

        $parent_file = $this->file;

        if ($source_file->version_id != $parent_file->version_id) {
            $this->formationResponse(1, 'Source and Parent files from different versions.');
            return $this->returnResponse();
        }

        $this->file            = $source_file->replicate();
        $this->file->pid       = $parent_file->id;
        $this->file->file_name = 'new_page';
        $this->file->file_size = 0;
        $this->file->title     = $request->title;
        $this->file->status    = 'wait';
        if (!$this->file->save()) {
            $this->formationResponse(1, 'Error creating file.');
            return $this->returnResponse();
        }

        $source_path  = $this->path();
        $storage_path = '/page' . $this->file->id . $this->file->file_ext;
        $new_path     = static::PATH . '/user_' . Auth::user()->id . '/project_' . $this->project->id . '/version_' .
                        $this->version->id . $storage_path;

        if (!Storage::exists($source_path)) {
            $this->formationResponse(1, 'Wrong source page. Such page not exists.');
            return $this->returnResponse();
        }

        if (!Storage::copy($source_path, $new_path)) {
            $this->formationResponse(1, 'Error writing file.');
            return $this->returnResponse();
        }

        $file_size = Storage::size($new_path);
        $this->file->update([
            'file_name'    => 'page' . $this->file->id,
            'file_size'    => $file_size,
            'storage_path' => $storage_path,
            'status'       => 'done'
        ]);

        $this->formationResponse(0, 'Page successfully created.');
        return $this->returnResponse();
    }

    /**
     * Preservation of content after CK Editor
     *
     * @param Request $request
     * @return string
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'           => 'required|integer',
            'page_content' => 'required',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $this->file = File::where('id', $request->id)
                          ->where('is_local', 1)
                          ->where('status', 'done')
                          ->where('file_content_type', 'like', '%html%')
                          ->first();

        if ($this->checkFile()) {
            return $this->returnResponse();
        }

        $path = $this->path();
        if (!Storage::put($path, $request->page_content)) {
            $this->formationResponse(1, 'Error writing to file.');
        } else {
            $this->formationResponse(0, 'Data saved successfully.');
        }

        return $this->returnResponse();
    }

    /**
     * The path to the file
     *
     * @return string
     */
    protected function path()
    {
        $path = static::PATH . '/user_' . Auth::user()->id . '/project_' . $this->project->id . '/version_' .
                $this->version->id . $this->file->storage_path;

        return $path;
    }
}
