<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\Contact as ContactResource;
use App\Conversation;
use App\User;
use Illuminate\Support\Facades\Gate;
use Validator;

class ContactController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function index()
    {
        $contacts = Auth::user()->contacts()->orderBy('name')->get();

        return response()->json([
            'success' => true,
            'contacts' => ContactResource::collection($contacts)
        ]);
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email'
        ];
        $credentials = request()->json()->all();

        $validator = Validator::make($credentials, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $contactEmail = $request->json()->get('email');
        // checking if the email is already set to an existing contact
        $emailCheck = Auth::user()->contacts()->where('email', $contactEmail)->first();
        if ($emailCheck) {
            return response()->json([
                'success' => false,
                'message' => [
                    'email' => 'this email is already been taken, use another email'
                ]
            ]);
        }

        // checkin if the contact is already registred in the app
        $isRegistered = 0;
        $user = User::where('email', $contactEmail)->first();

        if ($user) {
            $isRegistered = 1;

            // create a conversation with no messages
            $authUserId = Auth::id();
            $otherUserId = $user->id;
            $conversation = Conversation::create();
            $conversation->users()->attach([$authUserId, $otherUserId]);
        }

        $contact = Auth::user()->contacts()->create([
            'name' => $request->json()->get('name'),
            'email' => $request->json()->get('email'),
            'is_registered' => $isRegistered
        ]);

        return response()->json([
            'success' => true,
            'contact' => new ContactResource($contact)
        ]);
    }

    public function update(Request $request, Contact $contact)
    {
        if (Gate::denies('has-contact', $contact)) {
            return response()->json([
                'success' => false,
                'message' => 'you can not update this contact'
            ]);
        }
        $rules = [
            'name' => 'required|min:3'
        ];

        $credentials = request()->json()->all();
        $validator = Validator::make($credentials, $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $contact->update(['name' => $request->json()->get('name')]);

        return response()->json([
            'success' => true,
            'contact' => new ContactResource($contact)
        ]);
    }

    public function delete(Contact $contact)
    {
        if (Gate::denies('has-contact', $contact)) {
            return response()->json([
                'success' => false,
                'message' => 'you can\' delete this user'
            ]);
        }

        $contact->delete();

        return response()->json([
            'success' => true,
            'message' => 'contact deleted succesfuly'
        ]);
    }
}
