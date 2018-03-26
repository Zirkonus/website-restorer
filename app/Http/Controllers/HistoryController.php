<?php

namespace App\Http\Controllers;

use App\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests;
use Validator;

class HistoryController extends DashboardController
{
    /**
     * The history type.
     */
    const PER_PAGE       = 10;

    /**
     * Get history list for dashboard
     *
     * @return string
     */
    public function getList(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'page'  => 'integer',
        ]);

        if ($validator->fails()) {
            $this->formationResponse(1, implode(' ', $validator->errors()->all()));
            return $this->returnResponse();
        }

        History::where('user_id', Auth::user()->id)->update(['is_view' => 1]);
        $history = History::where('user_id', Auth::user()->id)->orderBy('id', 'DESC')->get();
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

    /**
     * Check for new history events
     *
     * @return string
     */
    public function checkNew()
    {
        $new_message = History::where('user_id', Auth::user()->id)->where('is_view', 0)->count();

        if ($new_message > 0) {
            $this->formationResponse(0, '', 1);
        } else {
            $this->formationResponse(0, '', 0);
        }

        return $this->returnResponse();
    }

}
