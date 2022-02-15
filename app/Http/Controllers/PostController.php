<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Facades\Auth;
use App\Models\PostLike;
use App\Models\Post;
use App\Models\PostComment;

class PostController extends Controller
{
    private $loggerdUser;

    public function __construct() {
        $this->middleware('auth:api');
        $this->loggedUser = auth()->user();
    }

    public function like($id) {
        $array = ['error' => ''];

        // 1. verificar se o post existe
        $postExists = Post::find($id);
        if($postExists) {
            // 2. verificar se eu já dei like nesse post
            $isLiked = PostLike::where('id_post', $id)
            ->where('id_user', $this->loggedUser['id'])
            ->count();

            // 2.1 se sim, remover
            if($isLiked > 0) {
                $p1 = PostLike::where('id_post', $id)
                ->where('id_user', $this->loggedUser['id'])
                ->first();
                $p1->delete();

                $array['isLiked'] = false;
            } else {
                // 2.2 se não, adicionar
                $newPostLike = new PostLike();
                $newPostLike->id_post = $id;
                $newPostLike->id_user = $this->loggedUser['id'];
                $newPostLike->created_at = date('Y-m-d H:i:s');
                $newPostLike->save();

                $array['isLiked'] = true;
            }

            $likeCount = PostLike::where('id_post', $id)->count();
            $array['likeCount'] = $likeCount;

        } else {
            $array['error'] = 'Post não existe!';
            return $array;
        }

        return $array;
    }

    public function comment(Request $request) {
        $array = ['error' => ''];

        $txt = $request->input('txt');

        $postExists = Post::find($id);
        if($postExists) {
            if($txt) {

                $newComment = new PostComment();
                $newComment->id_post = $id;
                $newComment->id_user = $this->loggedUser['id'];
                $newComment->created_at = date('Y-m-d H:i:s');
                $newComment->body = $txt;
                $newComment->save();
                
            } else {
                $array['error'] = 'não enviou mensagem';
                return $array;
            }
        } else {
            $array['error'] = 'post não existe!'; 
            return $array;
        }

        return $array;
    }

}
