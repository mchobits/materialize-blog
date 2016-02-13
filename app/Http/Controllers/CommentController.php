<?php

namespace App\Http\Controllers;

use App\Http\Requests\CommentRequest;
use App\Repositories\CommentRepository;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{

    /**
     * CommentRepository object.
     *
     * @var CommentRepository $comment
     */
    protected $comment;

    /**
     * CommentController constructor.
     *
     * @param CommentRepository $comment
     */
    public function __construct(CommentRepository $comment)
    {
        $this->comment = $comment;

        $this->middleware('auth', ['only' => ['index', 'destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $comments = $this->comment->all(10);
        $links = $comments->links();
        return view('back.comments.index', compact('comments', 'links'));
    }

    /**
     * Store a newly created comment in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        $validator = Validator::make($input, [
            'post_id' => 'required|numeric',
            'slug' => 'required|max:100|exists:posts,slug,id,' . $input['post_id'],
            'parent' => 'required|numeric',
            'name' => 'required|max:30',
            'email' => 'required|email|max:100',
            'blog' => 'required|url|max:100',
            'content' => 'required'
        ]);

        if($validator->fails()){
            return redirect('/posts/' . $input['slug'] . '#comment-form')
                    ->withErrors($validator, 'comment')
                    ->withInput($input);
        }


        $this->comment->store($input);

        return redirect('/posts/' . $input['slug'] . '#comments')->with('ok', 'comment successfully.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $this->comment->destroy($id);

        return back()->with('ok', 'Delete comment successfully');
    }

    /**
     * Toggle comment valid status.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function valid(Request $request, $id)
    {
        $this->comment->toggleStatus($id, 'valid', $request->input('valid'));

        return response()->json();
    }

    /**
     * Toggle comment seen status.
     *
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function seen(Request $request, $id)
    {
        $this->comment->toggleStatus($id, 'seen', $request->input('seen'));

        return response()->json();
    }
}
