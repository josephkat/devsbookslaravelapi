<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Facades\Auth;
use App\Models\User;
use App\Models\UserRelation;
use Image;

class UserController extends Controller
{
    private $loggerdUser;

    public function __construct() {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function update(Request $request) {
        $array = ['error'=>''];

        $name = $request->input('name');
        $email = $request->input('email');
        $birthdate = $reques->input('birthdate');
        $city = $request->input('city');
        $work = $request->input('work');
        $password = $request->input('password');
        $password_confirm = $request->input('password_confirm');

        $user = User::find($this->loggedUser['id']);

        //NAME
        if($name) {
            $user->name = $name;
        }

        // E-MAIL
        if($email) {
            if($email != $user->email) {
                $emailExists = User::where('email', $email)->count();
                if($emailExists === 0) {
                    $user->email = $email;
                } else {
                    $array['error'] = 'E-mail já existe!';
                    return $array;
                }
            }
        }

        //BIRTHDATE
        if($birthdate) {
            if(strtotime($birthdate) === false) {
                $array['error'] = 'Data de nascimento inválida';
                return $array;
            }
            $user->birthdate = $birthdate;
        }

        //CITY
        if($city) {
            $user->city = $city;
        }

        //WORK
        if($work) {
            $user->work = $work;
        }

        //PASSWORD
        if($password && $password_confirm) {
            if($password === $password_confirm) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $user->password = $hash;
            } else {
                $array['error'] = 'As senhas não batem.';
                return $array;
            }
        }

        $user->save();

        return $array;
    }

    public function updateAvatar(Request $request) {
        $array = ['error' => ''];
        $allowedTypes = ['image/jpg', 'image/png', 'image/jpeg'];

        $image = $request->file('avatar');

        if($image) {
            if(in_array($image->getClientMimeType(), $allowedTypes)) {

                 $filename = md5(time().rand(0,9999)).'.jpg';

                 $desPath = public_path('/media/avatars');

                 $img = Image::make($image->path())
                    ->fit(200, 200)
                    ->save($desPath.'/'.$filename);

                $user = User::find($this->loggedUser['id']);
                $user->avatar = $filename;
                $user->save();

                $array['url'] = url('/media/avatars/'.$filename);

            } else {
                $array['error'] ='Arquivo não suportado!';
                return $array;
            }
        } else {
            $array['error'] = 'Arquivo não enviado!';
            return $array;
        }

        return $array;
    }

    public function updateCover(Request $request) {
        $array = ['error' => ''];
        $allowedTypes = ['image/jpg', 'image/png', 'image/jpeg'];

        $image = $request->file('cover');

        if($image) {
            if(in_array($image->getClientMimeType(), $allowedTypes)) {

                 $filename = md5(time().rand(0,9999)).'.jpg';

                 $desPath = public_path('/media/covers');

                 $img = Image::make($image->path())
                    ->fit(850, 310)
                    ->save($desPath.'/'.$filename);

                $user = User::find($this->loggedUser['id']);
                $user->cover = $filename;
                $user->save();

                $array['url'] = url('/media/covers/'.$filename);

            } else {
                $array['error'] ='Arquivo não suportado!';
                return $array;
            }
        } else {
            $array['error'] = 'Arquivo não enviado!';
            return $array;
        }

        return $array;
    }

    public function read($id) {
        $array = ['error' => ''];

        if($id) {
            $info = User::find($id);
            if(!$info) {
                $array['error'] = 'usuário inexistente!';
                return $array;
            }
        } else {
            $info = $this->loggedUser;
        }

        $info['avatar'] = url('media/avatars/'.$info['avatar']);
        $info['cover'] = url('media/covers/'.$info['cover']);
        
        $info['me'] = ($info['id'] == $this->loggedUser['id']) ? true : false;

        $array['data'] = $info;

        return $array;
    }

    public function follow($id) {
        $array = ['error' => ''];

        if($id = $this->loggedUser['id']) {
            $array['error'] = 'você não pode seguir a você mesmo.';
            return $array;
        }

        $userExists = User::find($id);
        if($userExists){

            $relation = UserRelation::where('user_from', $this->loggedUser['id'])
            ->where('user_to', $id)
            ->first();

            if($relation) {
                // parar de seguir
                $relation->delete();
            } else {
                // seguir
                $newRelation = new UserRelation();
                $newRelation->user_from = $this->loggedUser['id'];
                $newRelation->user_to = $id;
                $newRelation->save();
            }

        } else {
            $array['error'] = 'Usuário inexistente!';
            return $array;
        }

        return $array;
    }

    public function followers($id) {
        $array = ['error' => ''];

        $userExists = User::find($id);
        if($userExists){
            $followers = UserRelation::where('user_to', $id)->get();
            $followings = UserRelation::where('user_from', $id)->get();

            $array['followers'] = [];
            $array['followings'] = [];

            foreach($followers as $item) {
                $user = User::find($item['user_from']);
                $array['followers'][] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'avatar' => url('media/avatars/'.$user['avatar'])
                ];
            }

            foreach($followings as $item) {
                $user = User::find($item['user_from']);
                $array['followings'][] = [
                    'id' => $user['id'],
                    'name' => $user['name'],
                    'avatar' => url('media/avatars/'.$user['avatar'])
                ];
            }

        } else {
            $array['error'] = 'Usuário inexistente!';
            return $array;
        }

        return $array;
    }

    public function photos() {}
}
