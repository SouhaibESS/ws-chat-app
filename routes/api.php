<?php

use App\Http\Resources\User as UserResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return response()->json(['user' => new UserResource($request->user())]);
});

Route::post('register', 'AuthController@register');
Route::post('login', 'AuthController@login');

Route::group(['middleware' => 'auth:api',], function ($router) {

    Route::put('user', 'AuthController@update');
    Route::post('logout', 'AuthController@logout');
    Route::post('refresh', 'AuthController@refresh');
    Route::post('me', 'AuthController@me');

    Route::get('conversations', 'ConversationController@index'); // get all the conversations
    Route::get('conversations/{conversation}', 'ConversationController@show'); // get the messages of a conversation
    Route::post('conversations/{conversation}', 'ConversationController@newMessage'); // add a new message to the conversation
    Route::put('conversations/{conversation}', 'ConversationController@messagesSeen'); // update messages seen to true and notify the sender

    Route::get('contacts', 'ContactController@index'); // get the list of the contacts
    Route::post('contacts', 'ContactController@store'); // add a new contact to the list 
    Route::put('contacts/{contact}', 'ContactController@update'); // update contact
    Route::delete('contacts/{contact}', 'ContactController@delete'); // delete contact

    Route::get('get-csrf-token', function() {
        return response()->json(['csrf-token' => csrf_token()]);
    });

});