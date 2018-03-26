<?php

namespace App\Http\Controllers;

use App\Project;
use App\User;
use App\File;
use Illuminate\Http\Request;
use DB;
use App\Http\Requests;
use App\History;
use Validator;

class AdminController extends DashboardController
{
    /**
     * The history type.
     */
    const PER_PAGE       = 10;

    public function index()
    {
        return view('admin');
    }

    public function userList()
    {
        $data   = json_decode(file_get_contents('php://input'), true);

        if (!$data['pageNum']) {

            $this->formationResponse(1, 'Error, wrong pageNum');
            return $this->returnResponse();
        }

        $num = $data['pageNum'] - 1;
        $offset =  10 * $num;
        $limit  = 10;
        $d      = [];

        $users  = User::with('projects')
                ->where('role_id', '!=', 1)
                ->offset($offset)
                ->limit($limit)
                ->get();

        $usersCount = User::where('role_id', '!=', 1)
                    ->count();

        $files  = User::join('projects as p' , 'p.user_id', '=', 'users.id' )
                ->join('versions as v', 'v.project_id', '=', 'p.id' )
                ->join('files as f', 'f.version_id', '=', 'v.id')
                ->where('users.role_id', '!=', 1)
                ->select(DB::raw('SUM(file_size) as files'), 'users.id')
                ->groupBy('users.id')
                ->offset($offset)
                ->limit($limit)
                ->get();

        foreach ($users as $us) {
            foreach ($us['relations'] as $rel){
              $count = count($rel);
            }
            $d['list'][] = [
                'id'            => $us->id,
                'username'      => $us->username,
                'email'         => $us->email,
                'credits'       => $us->credits,
                'projects'      => $count,
                'size'          => 0,
                'size_in_bite'  => 0,
                'is_active'     => $us->is_active
            ];
        }

        foreach ($files as $f) {
            for ($i = 0; $i < count($d['list']); $i++) {
                if ($d['list'][$i]['id'] == $f->id) {
                    $d['list'][$i]['size'] = $this->getDiskSpace((int)($f->files));
                    $d['list'][$i]['size_in_bite'] = (int)($f->files);
                };
            }
        }

        $d['count'] = $usersCount ;

        $this->formationResponse(0, 'Data send', $d);

        return $this->returnResponse();
    }

    public function userUpdate()
    {
        $data   = json_decode(file_get_contents('php://input'), true);
        $d      = [];

        if ($data['id']) {
            $user   = User::with('projects')
                    ->where('id', $data['id'])
                    ->select('id', 'email', 'username', 'is_active', 'credits')
                    ->firstOrFail();

            $count = count($user->projects);
            if(!isset($count)){
                $count = 0;
            }

            unset($user->projects);
            $user->projects = $count;

            $f  = User::join('projects as p' , 'p.user_id', '=', 'users.id' )
                ->join('versions as v', 'v.project_id', '=', 'p.id' )
                ->join('files as f', 'f.version_id', '=', 'v.id')
                ->where('users.id', $data['id'])
                ->select(DB::raw('SUM(file_size) as files'), 'users.id')
                ->groupBy('users.id')
                ->first();

            if ($f) {
                $user->size = $this->getDiskSpace((int)($f->files));
            } else {
                $user->size = 0;
            }

            $this->formationResponse(0, 'User data', $user);
            return $this->returnResponse();
        }
        $this->formationResponse(1, 'User error id', $d);
        return $this->returnResponse();
    }

    public function userUpdatePost()
    {
        $data   = json_decode(file_get_contents('php://input'), true);
        $d      = [];
       
        if ($data) {
            if ($data['id'] ) {
                if ($data['email']) {
                    $check = User::where('email', $data['email'])->count();
                    if ($check > 1) {
                        $this->formationResponse(0, 'Error, this email exist.', $d);
                        return $this->returnResponse();
                    }

                    if ($data['username']) {
                        if (isset($data['credits'])){
                            User::where('id', $data['id'])
                                ->update([
                                    'email' => $data['email'],
                                    'name' => $data['username'],
                                    'username' => $data['username'],
                                    'is_active' => $data['is_active'],
                                    'credits' => $data['credits'],
                                ]);
                            $this->formationResponse(0, 'User updated.', $d);
                            return $this->returnResponse();
                        }
                        $this->formationResponse(1, 'Error. Not all data was send.', $d);

                    }
                    $this->formationResponse(1, 'User name error.', $d);

                }
                $this->formationResponse(1, 'User email error.', $d);

            }
            $this->formationResponse(1, 'User id error.', $d);

        }
        $this->formationResponse(1, 'User update error.', $d);

        return $this->returnResponse();
    }

    public function userCreate()
    {
        $data   = json_decode(file_get_contents('php://input'), true);
        $d      = [];
        if ($data) {
            if ($data['username'] && $data['email']) {

                $check = User::where('email', $data['email'])->first();

                if (!$check) {
                    $user = new User();

                    $user->name     = $data['username'];
                    $user->username = $data['username'];
                    $user->email    = $data['email'];
                    $user->credits  = $data['credits'] ? $data['credits'] : 0;
                    $user->password = bcrypt($data['password']);
                    $user->save();

                    $u  = User::where('id', $user->id)
                        ->select('id', 'username', 'email', 'credits', 'is_active')
                        ->firstOrFail();

                    $u->projects    = 0;
                    $u->size        = 0;

                    $this->formationResponse(0, 'User created.', $u);

                } else {
                    $this->formationResponse(0, 'Error, this email exist.');
                }

                return $this->returnResponse();
            }
            $this->formationResponse(1, 'Data user error.');
            return $this->returnResponse();
        }
        $this->formationResponse(1, 'User create error.');
        return $this->returnResponse();
    }

    public function userDelete(){
        $data   = json_decode(file_get_contents('php://input'), true);
        $d      = [];

        if ($data['id']){
            User::where('id', $data['id'])
                ->update([ 'is_active' => '0']);

            $this->formationResponse(0, 'User was deleted (now is not active).');

            return $this->returnResponse();
        }
        $this->formationResponse(1, 'Error, wrong id.');

        return $this->returnResponse();
    }


    /**
     * Get user history by id.
     *
     * @param Request $request
     * @return string
     */
    public function userHistory(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id'  => 'required|integer',
            'page'  => 'integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $history = History::where('user_id', $request->id)->orderBy('id', 'DESC')->get();
        if (isset($request->page)) {
            $page = $request->page;
        } else {
            $page = 1;
        }

        if ($history->isEmpty()) {
            $this->formationResponse(0, '');
        } else {
            $response_data = [];

            if ($page * static::PER_PAGE < $history->count()) {
                $response_data['is_more'] = 1;
            } else {
                $response_data['is_more'] = 0;
            }

            $history = $history->slice(($page-1) * static::PER_PAGE, static::PER_PAGE)->all();

            foreach ($history as $item) {
                $res               = $item->toArray();
                $created_at        = strtotime($item->created_at);
                $created_at        = date('d M Y, h:i A', $created_at);
                $res['created_at'] = $created_at;

                $response_data['history'][] = $res;
            }

            $this->formationResponse(0, '', $response_data);
        }

        return $this->returnResponse();
    }

    public function userEmailCheck()
    {
        $data   = json_decode(file_get_contents('php://input'), true);
        if ($data['email']) {
            $user = User::where('email', $data['email'])->first();

            if ($user) {
                $this->formationResponse(0, '', true);
            } else {
                $this->formationResponse(0, '', false);
            }
            return $this->returnResponse();
        }
        $this->formationResponse(1, 'Wrong data');

        return $this->returnResponse();
    }

    public function getAllInformation(){

        $users = User::where('role_id', '!=', 1)->count();
        $projects = Project::count();
        $files = $this->getDiskSpace(File::sum('file_size'));

        $info = [
            'users'     => $users,
            'projects'  => $projects,
            'files'     => $files
        ];

        $this->formationResponse(0, '', $info);

        return $this->returnResponse();
    }

    public function getDiskSpace($fileSize)
    {
        //$dec_poin = ',';
        //$dec_thousands = ' ';
        if ($fileSize < 100000 && $fileSize != 0){
            return '0 MB';
        }
        if ($fileSize >= 1000000) {
            $num = $fileSize/1024/1024;
            return round($num).' MB';
        }
       /* if ($fileSize < 1000000 && $fileSize != 0) {
            $num    = $fileSize/1024/1024;
            $n      = number_format($num, 3, $dec_poin, $dec_thousands);
            return $n.' MB';
        } else if ($fileSize > 1000000 && $fileSize < 10000000 ) {
            $num    = $fileSize/1024/1024;
            $n      = number_format($num, 2, $dec_poin, $dec_thousands);
            return $n.' MB';
        } else if ($fileSize > 10000000) {
            $num    = $fileSize/1024/1024;
            $n      = number_format($num, 1, $dec_poin, $dec_thousands);
            return $n.' MB';
        }
       */ else if ($fileSize == 0) {
            return $fileSize. ' MB';
        }
    }
}

