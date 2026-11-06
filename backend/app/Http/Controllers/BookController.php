<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    private function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'author' => ['required', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'between:1000,2100'],
        ];
    }

    public function index(): JsonResponse
    {
        return response()->json(['data' => Book::latest('id')->get()]);
    }

    public function show(Book $book): JsonResponse
    {
        return response()->json(['data' => $book]);
    }

    public function store(Request $request): JsonResponse
    {
        $book = Book::create($request->validate($this->rules()));

        Log::info('book.created', [
            'user_id' => $request->user()->id,
            'action' => 'menyimpan buku baru',
            'book_id' => $book->id,
            'reason' => 'permintaan POST /api/books dari pengguna terautentikasi',
        ]);

        return response()->json(['data' => $book], 201);
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $book->update($request->validate($this->rules()));

        Log::info('book.updated', [
            'user_id' => $request->user()->id,
            'action' => 'mengubah data buku',
            'book_id' => $book->id,
            'changed_fields' => array_keys($book->getChanges()),
            'reason' => 'permintaan PUT /api/books/{id} dari pengguna terautentikasi',
        ]);

        return response()->json(['data' => $book]);
    }

    public function destroy(Request $request, Book $book): JsonResponse
    {
        $book->delete();

        Log::info('book.deleted', [
            'user_id' => $request->user()->id,
            'action' => 'menghapus buku',
            'book_id' => $book->id,
            'reason' => 'permintaan DELETE /api/books/{id} dari pengguna terautentikasi',
        ]);

        return response()->json(['message' => 'Buku dihapus.']);
    }
}
