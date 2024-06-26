<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use RoyceLtd\LaravelBulkSMS\Http\Controllers\RoyceController;

Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/landing', [HomeController::class, 'index'])->name('landing');

Route::middleware(['auth'])->group(function () {
    Route::get('royceroute', [HomeController::class, 'index']);
    Route::get('landing', [HomeController::class, 'messages']);
    Route::get('/', [HomeController::class, 'messages']);
    Route::get('/message_dashboard', [HomeController::class, 'message_dashboard'])->name('message_dashboard');
    Route::get('/landing', [HomeController::class, 'landing'])->name('landing');
    Route::post('/send-bulk-sms', 'YourController@sendBulkSMS')->name('send.bulk.sms');

    Route::get('base', [HomeController::class, 'base']);
    Route::post('deliveryreport', [HomeController::class, 'deliveryReport']);
    Route::get('contacts', [HomeController::class, 'contacts']);
    Route::post('contacts', [HomeController::class, 'saveContacts']);
    Route::get('contacts-group', [HomeController::class, 'contactsGroup']);
    Route::post('contacts-group', [HomeController::class, 'saveContactsGroup']);
    Route::get('single-text', [HomeController::class, 'singleText']);
    Route::post('single-text', [HomeController::class, 'sendSingleText']);
    Route::get('contacts-text', [HomeController::class, 'contactsText']);
    Route::post('contacts-text', [HomeController::class, 'sendContactsText']);
    Route::get('/fetch-group-numbers', [HomeController::class, 'fetchGroupNumbers']);

    Route::get('group-text', [HomeController::class, 'groupText']);
    Route::post('group-text', [HomeController::class, 'sendGroupText']);
    Route::get('delivery-report', [HomeController::class, 'getDeliveryReport']);
    Route::post('delivery-report', [HomeController::class, 'pDeliveryReport']);
    Route::get('set-webhook', [HomeController::class, 'setWebhook']);
    Route::get('delete/{id}', [HomeController::class, 'deleteContact']);
    Route::get('edit-group/{id}', [HomeController::class, 'editGroup']);
    Route::post('edit-contact-group', [HomeController::class, 'editContactGroup']);
    Route::any('receive-delivery-report', [HomeController::class, 'receiveDeliveryReport']);
});


