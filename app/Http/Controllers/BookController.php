<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class BookController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->middleware('auth:api');
        $this->user = $this->guard()->user();
    }

    public function list()
    {
        $books = $this->user->books()->get();
        return response()->json(['books' => $books->toArray()], 200);
    }

    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'genre' => 'required',
                'language' => 'required',
                'short_description' => 'required|between:2,255'
            ], [
                'title.required' => 'Campo Título é obrigatório',
                'genre.required' => 'Campo Gênero é obrigatório',
                'language.required' => 'Campo Língua é obrigatório',
                'short_description.required' => 'Campo Breve descrição é obrigatório',
                'short_description.between' => 'Campo Breve deve ter 2 à 255 caracteres',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => $validator->errors()
                ], 422);
            }

            $book = [
                'title' => $request->input('title'),
                'genre' => $request->input('genre'),
                'language' => $request->input('language'),
                'short_description' => $request->input('short_description'),
                'year_published' => $request->input('year_published'),
                'author_id' => $this->user->id,
            ];

            $saved_book = Book::create($book);

            return response()->json([
                'status' => true,
                'message' => 'Book created successfully',
                'book' => $saved_book
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }

    public function show($id)
    {
        try {
            $book = $this->user->books()->find($id);
            return response()->json([
                'book' => $book
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'required',
                'genre' => 'required',
                'language' => 'required',
                'short_description' => 'required|between:2,255'
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'status' => false,
                    'error' => $validator->errors()
                ], 422);
            }

            $book = [
                'title' => $request->input('title'),
                'genre' => $request->input('genre'),
                'language' => $request->input('language'),
                'short_description' => $request->input('short_description'),
                'year_published' => $request->input('year_published'),
                'author_id' => $this->user->id,
            ];

            $updated_book = DB::table('books')->where('id', $id)->update($book);

            $book = $this->user->books()->find($id);


            return response()->json([
                'status' => true,
                'message' => 'Book updated successfully',
                'book' => $book
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }

    public function delete($id)
    {
        try {
            DB::table('books')->where('id', $id)->delete();

            return response()->json([
                'status' => true,
                'message' => 'Book deleted successfully'
            ], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => $e], 400);
        }
    }

    protected function guard()
    {
        return Auth::guard();
    }
}
