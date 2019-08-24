<?php

use App\Book;
use Illuminate\Http\Request;

// ルート
/*
Route::get('/', function () {
    return view('books');
});
*/

/*
Web アプリケーションのエントリポイント。
ミドルウェアという仕組みを介して、インタフェースがブラウザの場合と、コマンドラインや API などでそれぞれ違う処理を返す、ということをしている。
*/
Route::group(['middleware' => ['web']], function () {
    Route::get('/', function() {
        // Book モデルを呼ぶと books テーブルを参照するように関連付けられている
        $books = Book::all();

        // resources/views/books.blade.php に紐付けられる
        return view('books', [
            'books' => $books
        ]);
    });

    Route::post('/book', function(Request $request) {
        $validator = Validator::make($request->all(), [
            'name' => 'required|max:255',
        ]);

        if ($validator->fails()) {
            return redirect('/')
                ->withInput()
                ->withErrors($validator);
        }

        $book = new Book; // ORM。 books テーブルと関連付ける。
        $book->title = $request->name;
        $book->save();

        return redirect('/');
    });

    // {} で囲むと自動的に ID 番号になり、その ID の Book が自動で $book に格納される
    Route::delete('/book/{book}', function(Book $book) {
        $book->delete();

        return redirect('/');
    });

});

