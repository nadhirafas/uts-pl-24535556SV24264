<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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

        return response()->json(['data' => $book], 201);
    }

    public function update(Request $request, Book $book): JsonResponse
    {
        $book->update($request->validate($this->rules()));

        return response()->json(['data' => $book]);
    }

    public function destroy(Book $book): JsonResponse
    {
        $book->delete();

        return response()->json(['message' => 'Buku dihapus.']);
    }
}
