<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\FrontReportController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\AdminClientController;
use App\Http\Controllers\Admin\SiteController;

use App\Http\Controllers\Client\EmployeeController;

use App\Http\Controllers\Kintai\FrontKintaiController;

use App\Http\Controllers\Api\ValidateController;

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
    // 社員マスタ
    Route::match(['get', 'post'], '/client/employee', [EmployeeController::class, 'index'])->name('client.employee.index');
    Route::get('/client/employee/edit/{id?}', [EmployeeController::class, 'edit'])->name('client.employee.edit');
    Route::post('/client/employee/update/{id?}', [EmployeeController::class, 'update'])->name('client.employee.update');
    Route::post('/client/employee/destroy/{id}', [EmployeeController::class, 'destroy'])->name('client.employee.destroy');
    Route::post('/client/employee/orderupdate', [EmployeeController::class, 'orderupdate'])->name('client.employee.orderupdate');
});

// API 作業証明書の重複チェック
Route::post('/api/validate/report', [ValidateController::class, 'report'])->name('api.validate.report');

require __DIR__.'/auth.php';
