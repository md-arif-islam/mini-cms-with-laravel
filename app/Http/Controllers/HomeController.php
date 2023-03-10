<?php

namespace App\Http\Controllers;

use App\Models\Friend;
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

            $displayActions = false;
            if ( Auth::check() ) {
                if ( Auth::user()->id != $user->id ) {
                    $displayActions = true;
                }
            }

            $alreadyFriend = false;
            $userId = Auth::user()->id;
            $nickname = implode( '/', array_slice( request()->segments(), 1 ) );
            if ( $nickname ) {
                $friend = User::where( 'nickname', $nickname )->first();

                if ( Friend::where( 'user_id', $userId )->where( 'friend_id', $friend['id'] )->count() == 0 ) {

                } else {
                    $alreadyFriend = true;
                }

            }
            return view( "shoutpublic", array(
                'status' => $status,
                'avatar' => $avatar,
                'name' => $name,
                'displayActions' => $displayActions,
                'friendId' => $user->id,
                "alreadyFriend" => $alreadyFriend,
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

    public function makeFriend( $friendId ) {

        $userId = Auth::user()->id;
        if ( Friend::where( 'user_id', $userId )->where( 'friend_id', $friendId )->count() == 0 ) {
            $friendship = new Friend();
            $friendship->user_id = $userId;
            $friendship->friend_id = $friendId;
            $friendship->save();
        }

        if ( Friend::where( 'friend_id', $userId )->where( 'user_id', $friendId )->count() == 0 ) {
            $friendship = new Friend();
            $friendship->friend_id = $userId;
            $friendship->user_id = $friendId;
            $friendship->save();
        }

        $userFromId = User::where( 'id', $friendId )->first();
        $nicknameFromUser = $userFromId['nickname'];

        return redirect()->route( "shout.public", ["nickname" => $nicknameFromUser] );
    }

    public function unFriend( $friendId ) {
        $userId = Auth::user()->id;
        Friend::where( 'user_id', $userId )->where( 'friend_id', $friendId )->delete();
        Friend::where( 'friend_id', $userId )->where( 'user_id', $friendId )->delete();

        return redirect()->route( 'shout' );
    }

}
