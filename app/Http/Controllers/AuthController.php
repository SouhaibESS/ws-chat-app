<?php

namespace App\Http\Controllers;

use App\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Controller;
use Validator;
use Illuminate\Support\Facades\Hash;
use App\User;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Conversation;

class AuthController extends Controller
{

    private $validationRules = [
        'name' => 'required|string|max:100',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|min:6|confirmed',
        'password_confirmation' => 'required|min:6'
    ];

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Get a JWT token via given credentials.
     *
     * @param  \Illuminate\Http\Request  $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if ($token = $this->guard()->attempt($credentials)) {
            return $this->respondWithToken($token);
        }

        return response()->json([
            'error' => 'Unauthorized',
            'success' => false
        ], 401);
    }

    public function register(Request $request)
    {
        $credentials = request()->json()->all();

        $validator = Validator::make($credentials, $this->validationRules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        $user = User::create([
            'name' => $request->json()->get('name'),
            'email' => $request->json()->get('email'),
            'password' => Hash::make($request->json()->get('password'))
        ]);

        // check if there's a contact with this email
        // modify 'is_registered' to true 
        $email = $request->json()->get('email');
        $contacts = Contact::where('email', $email)->get(); // we can find multiple that contact in associated with other users
        foreach ($contacts as $contact) {
            $contact->is_registered = 1;
            $contact->save();


            // create conversation m3a users li m9ydini f contacts dyalhom
            $otherUserId = $contact->user->id;
            $userId = $user->id;
            $conversation = Conversation::create();
            $conversation->users()->attach([$userId, $otherUserId]);
        }

        $token = JWTAuth::fromUser($user);

        return $this->respondWithToken($token);
    }


    public function update(Request $request, User $user)
    {
        $rules = [
            'name' => 'required|string|max:100',
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ];

        // return response()->json($request->all());
        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()
            ], 400);
        }

        // updating user credentials
        $user->name = $request->input('name');

        // moving the new avatar in the avatars folder
        if ( $image = $request['avatar'] )
        {
            $imagePointer = str_replace(env('IMAGES_FOLDER'), './../public/images', $user->avatar);
            $imagePointer = realpath($imagePointer);
            // if the user already have an old picture , delete the old one
            if(file_exists($imagePointer)) unlink($imagePointer);
            
            // replacing the user avatar
            $avatarsFolder = env('IMAGES_FOLDER') . '/users';
            $userAvatar = 'user_'. $user->id . '_avatar.' . $image->getClientOriginalExtension();
            $image->move(public_path('images/users') , $userAvatar);
            $user->avatar = $avatarsFolder . '/' . $userAvatar; 
        }

        $user->save(); 

        return response()->json([
            'success' => true, 
            'message' => 'user updated successfully'
        ], 200);
    }

    /**
     * Get the authenticated User
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function me()
    {
        return response()->json($this->guard()->user());
    }

    /**
     * Log the user out (Invalidate the token)
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $this->guard()->logout();

        return response()->json(['message' => 'Successfully logged out', 200]);
    }

    /**
     * Refresh a token.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function refresh()
    {
        return $this->respondWithToken($this->guard()->refresh());
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'success' => true,
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 60
        ], 200);
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard()
    {
        return Auth::guard();
    }
}
