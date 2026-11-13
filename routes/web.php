<?php

use App\Http\Controllers\BasketController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\FeedbackController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RenewalController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/sujets/{subject}', [HomeController::class, 'show'])->name('public.subjects.show');
Route::get('/calendrier', [\App\Http\Controllers\CampaignCalendarController::class, 'index'])->name('calendar');
Route::get('/panier', [BasketController::class, 'index'])->name('basket');
Route::post('/panier/{subject}', [BasketController::class, 'add'])->name('basket.add')->block();
Route::delete('/panier/{subject}', [BasketController::class, 'remove'])->whereNumber('subject')->name('basket.remove')->block();
Route::get('/about', [HomeController::class, 'about'])->name('about');

Route::get('/affectation-en-attente', function () {
    abort_unless(auth()->user()->is_active && auth()->user()->role === \App\Enums\UserRole::Staff, 403);
    return view('staff.unassigned');
})->middleware('auth')->name('staff.unassigned');

Route::get('/mes-fonctions', [\App\Http\Controllers\StaffWorkspaceController::class, 'index'])->middleware('auth')->name('staff.workspace');

Route::get('/mes-fonctions/dossiers/{application}', [\App\Http\Controllers\StaffWorkspaceController::class, 'application'])
    ->middleware('auth')->name('staff.application');

Route::get('/dashboard', function () {
    return redirect()->route(auth()->user()->dashboardRouteName());
})->middleware(['auth'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::post('/mes-fonctions/sujets/{subject}/accord/{function}', [\App\Http\Controllers\StaffDecisionController::class, 'approveSubject'])->name('staff.subject.approve');
    Route::patch('/mes-fonctions/dossiers/{application}/decision', [\App\Http\Controllers\StaffDecisionController::class, 'review'])->name('staff.application.review');
    Route::get('/reinscriptions/{renewal}/justificatif', [RenewalController::class, 'attachment'])->name('renewals.attachment');
    Route::get('/reinscriptions', [RenewalController::class, 'index'])->name('renewals.index');
    Route::get('/inscriptions/{application}/pieces', [\App\Http\Controllers\EnrolmentDocumentController::class, 'show'])->name('enrolment-documents.show');
    Route::post('/inscriptions/{application}/pieces/{kind}', [\App\Http\Controllers\EnrolmentDocumentController::class, 'upload'])->name('enrolment-documents.upload');
    Route::get('/inscriptions/{application}/pieces/{document}', [\App\Http\Controllers\EnrolmentDocumentController::class, 'preview'])->name('enrolment-documents.preview');
    Route::patch('/inscriptions/{application}/pieces/{document}', [\App\Http\Controllers\EnrolmentDocumentController::class, 'review'])->name('enrolment-documents.review');
    Route::post('/inscriptions/{application}/confirmer', [RenewalController::class, 'confirmEnrolment'])->name('enrolments.confirm');
    Route::post('/inscriptions/{enrolment}/reinscription', [RenewalController::class, 'store'])->name('renewals.store');
    Route::post('/reinscriptions/{renewal}/avis', [RenewalController::class, 'review'])->name('renewals.review');
    Route::post('/reinscriptions/{renewal}/accord/{function}', [RenewalController::class, 'approve'])->name('renewals.approve');
    Route::post('/reinscriptions/{renewal}/complement', [RenewalController::class, 'requestCorrection'])->name('renewals.correction');
    Route::post('/reinscriptions/{renewal}/corriger', [RenewalController::class, 'resubmit'])->name('renewals.resubmit');
    Route::post('/reinscriptions/{renewal}/prolongation', [RenewalController::class, 'decideExtension'])->name('renewals.extension.decide');
    Route::post('/reinscriptions/{renewal}/decision', [RenewalController::class, 'decide'])->name('renewals.decide');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::post('/documents/{document}/complement', [\App\Http\Controllers\ApplicationDocumentCorrectionController::class, 'requestCorrection'])->name('application-documents.correction');
    Route::patch('/documents/{document}/remplacement', [\App\Http\Controllers\ApplicationDocumentCorrectionController::class, 'replace'])->name('application-documents.replace');
    Route::get('/documents/{document}/preview', [DocumentController::class, 'preview'])
        ->name('documents.preview');

    Route::get('/feedback', [FeedbackController::class, 'index'])->name('feedback.index');
    Route::post('/feedback', [FeedbackController::class, 'store'])->name('feedback.store');
});

require __DIR__.'/auth.php';
require __DIR__.'/candidate.php';
require __DIR__.'/professor.php';
require __DIR__.'/admin.php';
