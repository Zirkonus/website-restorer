<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Validator;

class UserController extends DashboardController
{
    /**
     * Get auth user data
     *
     * @return string
     */
    public function get()
    {
        $user = Auth::user();
        $this->formationResponse(0, '', $user->toArray());

        return $this->returnResponse();
    }

    /**
     * Change password
     *
     * @param Request $request
     * @return string
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password'         => 'required|min:4|confirmed',
            'current_password' => 'required|min:4',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        $user = User::find(auth()->user()->id);
        if (Hash::check($request->current_password, $user->password)) {
            $user->password = bcrypt($request->password);
            if ($user->save()) {
                $this->formationResponse(0, 'Password changed successfully.');
            } else {
                $this->formationResponse(1, 'Error storing password.');
            }
        } else {
            $this->formationResponse(1, 'Current password is not correct.');
        }

        return $this->returnResponse();
    }
}
