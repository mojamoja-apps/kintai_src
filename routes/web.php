<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Report\FrontReportController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\AdminClientController;
use App\Http\Controllers\Admin\SiteController;

use App\Http\Controllers\Client\EmployeeController;
use App\Http\Controllers\Client\ClientKintaiController;

use App\Http\Controllers\Kintai\FrontKintaiController;

use App\Http\Controllers\Api\ClientValidateController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    //return view('welcome');
    return redirect('/client');
});

// 勤怠入力画面
Route::get('/kintai/{hash}', [FrontKintaiController::class, 'index'])->name('kintai.index')->where('hash', '[A-Za-z0-9]+');
Route::post('/kintai/{hash}/dakoku', [FrontKintaiController::class, 'dakoku'])->name('kintai.dakoku')->where('hash', '[A-Za-z0-9]+');


// 運営管理画面
Route::middleware('basicauth')->group(function () {
    Route::get('/admin', [AdminClientController::class, 'dashbord'])->name('admin.index');
    Route::match(['get', 'post'], '/admin/client', [AdminClientController::class, 'index'])->name('admin.client.index');
    Route::get('/admin/client/edit/{id?}', [AdminClientController::class, 'edit'])->name('admin.client.edit');
    Route::post('/admin/client/update/{id?}', [AdminClientController::class, 'update'])->name('admin.client.update');
    Route::post('/admin/client/destroy/{id}', [AdminClientController::class, 'destroy'])->name('admin.client.destroy');
});

// クライアント管理画面
Route::middleware( ['auth'])->group(function () {
    Route::get('/client', function () {
        return view('client/index');
    })->name('client');
    // 従業員マスタ
    Route::match(['get', 'post'], '/client/employee', [EmployeeController::class, 'index'])->name('client.employee.index');
    Route::get('/client/employee/edit/{id?}', [EmployeeController::class, 'edit'])->name('client.employee.edit');
    Route::post('/client/employee/update/{id?}', [EmployeeController::class, 'update'])->name('client.employee.update');
    Route::post('/client/employee/destroy/{id}', [EmployeeController::class, 'destroy'])->name('client.employee.destroy');
    Route::post('/client/employee/orderupdate', [EmployeeController::class, 'orderupdate'])->name('client.employee.orderupdate');

    // 勤怠管理
    Route::match(['get', 'post'], '/client/kintai', [ClientKintaiController::class, 'index'])->name('client.kintai.index');
    Route::get('/client/kintai/edit/{id?}', [ClientKintaiController::class, 'edit'])->name('client.kintai.edit');
    Route::post('/client/kintai/update/{id?}', [ClientKintaiController::class, 'update'])->name('client.kintai.update');
    Route::post('/client/kintai/destroy/{id}', [ClientKintaiController::class, 'destroy'])->name('client.kintai.destroy');

    // 勤怠データDL
    Route::get('/client/dl', [ClientKintaiController::class, 'dlindex'])->name('client.kintai.dlindex');
    Route::post('/client/dl/excel', [ClientKintaiController::class, 'excel'])->name('client.kintai.excel');
    Route::post('/client/dl/smilecsv', [ClientKintaiController::class, 'smilecsv'])->name('client.kintai.smilecsv');

    // 打刻画面を開くリンク
    Route::get('/client/open', [ClientKintaiController::class, 'open'])->name('client.kintai.open');


});

// API 作業証明書の重複チェック
Route::post('/client/api/validate/kintai', [ClientValidateController::class, 'kintai'])->name('client.api.validate.kintai');


require __DIR__.'/auth.php';
