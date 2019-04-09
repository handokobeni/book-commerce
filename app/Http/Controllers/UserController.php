<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $users = User::paginate(10);
        $filterKeyword = $request->keyword;
        $status = $request->status;

        if ($filterKeyword || $status) {
            if ($status) {
                $users = User::where('email', 'LIKE', "%$filterKeyword%")
                    ->where('status', $status)
                    ->paginate(10);
            } else {
                $users = User::where('email', 'LIKE', "%$filterKeyword%")->paginate(10);
            }
        } 

        return view('users.index', ['users' => $users]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view("users.create");
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'min:5', 'max:100'],
            'email' => ['required', 'email', 'unique:users'],
            'username' => ['required', 'min:5', 'max:30', 'unique:users'],
            'roles' => ['required'],
            'address' => ['required', 'min:10', 'max:200'],
            'phone' => ['required', 'digits_between:10,12'],
            'avatar' => ['required'],
            'password' => ['required', 'min:6', 'confirmed'],
        ]);

        $user = new User([
            'name' => $request->name,
            'email' => $request->email,
            'username' => $request->username,
            'roles' => json_encode($request->roles),
            'address' => $request->address,
            'phone' => $request->phone,
            'password' => bcrypt($request->password),
            'status' => "ACTIVE"
        ]);

        if ($request->file('avatar')) {
            $file = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $file;
        } 

        $user->save();

        return redirect()->route('users.create')->with('status', 'User successfully created');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user = User::findOrFail($id);

        return view('users.show', ['user' => $user]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);

        return view('users.edit', ['user' => $user]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $user->name = $request->name;
        $user->roles = json_encode($request->roles);
        $user->address = $request->address;
        $user->phone = $request->phone;
        $user->status = $request->status;

        if($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))){
            if($request->file('avatar') != null) {
                \Storage::delete('public/' . $user->avatar);

                $file = $request->file('avatar')->store('avatars', 'public');
                $user->avatar = $file;
            }
        } 
        else {
            $file = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $file;
        }

        $user->save();
        
        return redirect()->route('users.edit', ['id' => $id])->with(
            'status',
            'User succesfully updated'
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if ($user->avatar && file_exists(storage_path('app/public/' . $user->avatar))) {
            \Storage::delete('public/' . $user->avatar);
        }


        $user->delete();
        return redirect()->route('users.index')->with('status', 'User successfully deleted');
    }
}
