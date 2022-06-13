<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Report\FrontReportController;

use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\SiteController;
use App\Http\Controllers\Admin\WorkerController;
use App\Http\Controllers\Admin\DeduraController;
use App\Http\Controllers\Admin\KintaiController;

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
    return redirect('/report');
});

// テストサンプル 勤怠入力画面
Route::get('/kintai', [FrontReportController::class, 'kintai'])->name('kintai');

// 作業証明書入力画面
Route::middleware('basicauth')->group(function () {
    Route::match(['get', 'post'], '/report', [FrontReportController::class, 'index'])->name('report.index');
    Route::get('/report/edit/{id?}', [FrontReportController::class, 'edit'])->name('report.edit');
    Route::post('/report/update/{id?}', [FrontReportController::class, 'update'])->name('report.update');
    Route::post('/report/destroy/{id}', [FrontReportController::class, 'destroy'])->name('report.destroy');
});

// 管理画面
Route::middleware('auth')->group(function () {
    Route::get('/admin', function () {
        return view('admin/index');
    })->name('admin');

    // 作業証明書
    Route::match(['get', 'post'], '/admin/report', [ReportController::class, 'index'])->name('admin.report.index');
    Route::get('/admin/report/view/{id?}', [ReportController::class, 'view'])->name('admin.report.view');
    Route::get('/admin/report/output/{id?}', [ReportController::class, 'output'])->name('admin.report.output');

    // 出面集計表
    Route::get('/admin/dedura', [DeduraController::class, 'index'])->name('admin.dedura.index');
    Route::post('/admin/dedura/output', [DeduraController::class, 'output'])->name('admin.dedura.output');

    // 勤怠管理表
    Route::get('/admin/kintai', [KintaiController::class, 'index'])->name('admin.kintai.index');
    Route::post('/admin/kintai/output', [KintaiController::class, 'output'])->name('admin.kintai.output');

    // 管理者
    Route::match(['get', 'post'], '/admin/user', [UserController::class, 'index'])->name('admin.user.index');
    Route::get('/admin/user/edit/{id?}', [UserController::class, 'edit'])->name('admin.user.edit');
    Route::post('/admin/user/update/{id?}', [UserController::class, 'update'])->name('admin.user.update');
    Route::post('/admin/user/destroy/{id}', [UserController::class, 'destroy'])->name('admin.user.destroy');

    // 元請け
    Route::match(['get', 'post'], '/admin/company', [CompanyController::class, 'index'])->name('admin.company.index');
    Route::get('/admin/company/edit/{id?}', [CompanyController::class, 'edit'])->name('admin.company.edit');
    Route::post('/admin/company/update/{id?}', [CompanyController::class, 'update'])->name('admin.company.update');
    Route::post('/admin/company/destroy/{id}', [CompanyController::class, 'destroy'])->name('admin.company.destroy');

    // 作業所
    Route::match(['get', 'post'], '/admin/site', [SiteController::class, 'index'])->name('admin.site.index');
    Route::get('/admin/site/edit/{id?}', [SiteController::class, 'edit'])->name('admin.site.edit');
    Route::post('/admin/site/update/{id?}', [SiteController::class, 'update'])->name('admin.site.update');
    Route::post('/admin/site/destroy/{id}', [SiteController::class, 'destroy'])->name('admin.site.destroy');

    // 作業員
    Route::match(['get', 'post'], '/admin/worker', [WorkerController::class, 'index'])->name('admin.worker.index');
    Route::get('/admin/worker/edit/{id?}', [WorkerController::class, 'edit'])->name('admin.worker.edit');
    Route::post('/admin/worker/update/{id?}', [WorkerController::class, 'update'])->name('admin.worker.update');
    Route::post('/admin/worker/destroy/{id}', [WorkerController::class, 'destroy'])->name('admin.worker.destroy');


});

// API 作業証明書の重複チェック
Route::post('/api/validate/report', [ValidateController::class, 'report'])->name('api.validate.report');

require __DIR__.'/auth.php';
