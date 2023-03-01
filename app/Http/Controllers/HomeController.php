<?php

namespace App\Http\Controllers;

use App\Models\Status;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HomeController extends Controller {
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct() {
        $this->middleware( 'auth' );
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index() {
        return view( 'home' );
    }

    public function shoutHome() {
        $user = Auth::user();
        $userId = Auth::id();
        $status = Status::where( "user_id", $userId )->orderBy( "id", "DESC" )->get();

        $avatar = empty( $user->avatar ) ? asset( 'images/avatar.jpg' ) : $user->avatar;
        return view( "shouthome", ["status" => $status, "avatar" => $avatar] );
    }

    public function publicTimeline( $nickname ) {
        $user = User::where( 'nickname', $nickname )->first();
        if ( $user ) {
            $status = Status::where( 'user_id', $user->id )->orderBy( 'id', 'desc' )->get();
            $avatar = empty( $user->avatar ) ? asset( 'images/avatar.jpg' ) : $user->avatar;
            $name = $user->name;

            return view( "shoutpublic", array(
                'status' => $status,
                'avatar' => $avatar,
                'name' => $name,
            ) );
        } else {
            return redirect( '/' );
        }

    }

    public function saveStatus( Request $request ) {
        if ( Auth::check() ) {
            $status = $request->post( "status" );
            $userId = Auth::id();

            $statusModel = new Status();
            $statusModel->status = $status;
            $statusModel->user_id = $userId;
            $statusModel->save();

            return redirect()->route( "shout" );
        }
    }

    public function profile() {
        return view( "profile" );
    }

    public function saveProfile( Request $request ) {
        if ( Auth::check() ) {
            $user = Auth::user();
            $user->name = $request->name;
            $user->email = $request->email;
            $user->nickname = $request->nickname;

            $profileImage = 'user-' . $user->id . '.' . $request->image->extension();
            $request->image->move( public_path( 'images' ), $profileImage );
            $user->avatar = asset( "images/{$profileImage}" );

            /** @var \App\Models\User $user **/
            $user->save();
            return redirect()->route( 'shout.profile' );
        }
    }

}
