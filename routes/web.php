<?php

use App\Book;
use Illuminate\Http\Request;
use

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
        echo "Hello Laravel!!";
    });

    Route::post('/book', function(Request $request) {
        //
    });

    Route::delete('/book/{book}', function(Request $book) {
        //
    });

});

