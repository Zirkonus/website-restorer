<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', 'HomeController@index');

Route::post('/check-email', [
    'uses'  => 'AdminController@userEmailCheck',
    'as'    => 'check_email'
]);
Route::get('/licence/suspend', function (){return view('suspend');});
Route::group(['middleware' => ['auth', 'admin']], function(){
    Route::group(['prefix' => 'admin'], function() {

        Route::get('/', [
            'uses' => 'AdminController@index',
            'as' => 'admin'
        ]);

        Route::post('/general-info', [
            'uses'  => 'AdminController@getAllInformation',
            'as'    => 'get_all_stats'
        ]);

        Route::post('/user-list', [
            'uses'  => 'AdminController@userList',
            'as'    => 'user_list',
        ]);
        Route::get('/user-list', [
            'uses'  => 'AdminController@userList',
            'as'    => 'user_list',
        ]);
        Route::post('/user', [
            'uses'  => 'AdminController@userUpdate',
            'as'    => 'user_update',
        ]);
        Route::post('/user/update', [
            'uses'  => 'AdminController@userUpdatePost',
            'as'    => 'user_update_post',
        ]);
        Route::post('/user/create', [
            'uses'  => 'AdminController@userCreate',
            'as'    => 'user_create',
        ]);
        Route::post('/user/delete', [
            'uses'  => 'AdminController@userDelete',
            'as'    => 'user_delete',
        ]);
        Route::post('/user/history', [
            'uses'  => 'AdminController@userHistory',
            'as'    => 'user_history',
        ]);
    });
});

Route::get('/getpage={id}',  'PageController@getPage')->name('getpage');
Route::group(['middleware' => ['auth', 'user', 'licence']], function(){

    Route::get('/main',                        'DashboardController@index')->name('main');

    Route::group(['prefix' => 'projects'], function(){
        Route::post('/create',                 'ProjectController@save');
        Route::post('/list',                   'ProjectController@getList');
        Route::post('/edit',                   'ProjectController@update');
        Route::post('/get',                    'ProjectController@get');
        Route::post('/delete',                 'ProjectController@delete');
        Route::post('/check',                  'ProjectController@checkDomain');
    });

    Route::group(['prefix' => 'versions'], function(){
        Route::post('/download',               'VersionController@download');
        Route::post('/pages',                  'VersionController@getPages');
        Route::post('/list/{status?}',         'VersionController@getList')
             ->where('status', 'in_progress|restored|error');
        Route::post('/images',                 'VersionController@getImages');
        Route::post('/delete',                 'VersionController@delete');
        Route::post('/cancel',                 'VersionController@cancel');
        Route::post('/check',                  'VersionController@checkCancel');
        Route::get('/upload',                  'VersionController@upload');
        Route::post('/ftp-upload',             'VersionController@uploadToFtp');
        Route::get('/preview',                 'VersionController@getPreview');
    });

    Route::group(['prefix' => 'pages'], function(){
        Route::post('/create',                 'PageController@save');
        Route::post('/rename',                 'PageController@rename');
        Route::post('/edit',                   'PageController@update');
        Route::post('/delete',                 'PageController@delete');
    });

    Route::group(['prefix' => 'images'], function(){
        Route::post('/create',                 'PageController@addImages');
    });

    Route::group(['prefix' => 'history'], function(){
        Route::post('/list',                   'HistoryController@getList');
        Route::post('/check',                  'HistoryController@checkNew');
    });

    Route::group(['prefix' => 'user'], function(){
        Route::post('/get',                    'UserController@get');
        Route::post('/password',               'UserController@changePassword');
    });
});

Route::group(['prefix' => 'credits'], function(){
    Route::post('/',                           'CreditController@index');
});

Route::auth();
