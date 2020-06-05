<?php

namespace App\Http\Controllers;

use App\Conversation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Conversation as ConversationResource;
use App\Message;
use Illuminate\Support\Facades\Gate;
use Validator;

class ConversationController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }
    

    public function index()
    {
        $user = Auth::user();

        $conversations = $user->conversations;

        return response()->json([
            'success' => true, 
            'conversations' => ConversationResource::collection($conversations)
        ], 200);
    }

    public function show(Conversation $conversation)
    {

        if(Gate::allows('has-conversation', $conversation))
            return response()->json([
                'success' => true, 
                'conversations' => new ConversationResource($conversation)
            ], 200);

        return response()->json([
            'success' => false,
            'message' => 'permission denied'
        ]); 
    }

    public function newMessage(Conversation $conversation, Request $request)
    {
        $credentials = request()->json()->all();
        $rules = ['message' => 'required|min:3|max:500'];

        $validator = Validator::make($credentials, $rules);

        if($validator->fails())
            return response()->json([
                'success' => false, 
                'message' => $validator->errors() 
            ]);

        // add the message to the conversation
        $conversation->messages()
            ->create([
                'message' => $request->json()->get('message'),
                'user_id' => $request->json()->get('user_id'),
                'seen' => 0
                ]);

        // set the update date column to now
        $conversation->update(['updated_at' => date_create('now')->format('Y-m-d H:i:s')]);

        return response()->json([
            'sucess' => true,
            'message' => 'message sent succesfuly'
        ]);
    }
}
