<?php

use App\Http\Controllers\HomeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use RoyceLtd\LaravelBulkSMS\Http\Controllers\RoyceController;

Route::get('/', function () {
    return view('welcome');
});

// Route::group(['namespace' => 'RoyceLtd\LaravelBulkSMS\Http\Controllers'], function () {
//     Route::get('royceroute', ['uses' => 'RoyceController@index']);
//     Route::get('bulksms/dashboard', ['uses' => 'RoyceController@messages']);
//     Route::get('bulksms/', ['uses' => 'RoyceController@messages']);

//     Route::get('base', ['uses' => 'RoyceController@base']);
//     Route::post('deliveryreport', ['uses' => 'RoyceController@deliveryReport']);
//     Route::get('bulksms/contacts', ['uses' => 'RoyceController@contacts']);
//     Route::post('bulksms/contacts', ['uses' => 'RoyceController@saveContacts']);
//     Route::get('bulksms/contacts-group', ['uses' => 'RoyceController@contactsGroup']);
//     Route::post('bulksms/contacts-group', ['uses' => 'RoyceController@saveContactsGroup']);
//     Route::get('bulksms/single-text', ['uses' => 'RoyceController@singleText']);
//     Route::post('bulksms/single-text', ['uses' => 'RoyceController@sendSingleText']);
//     Route::get('bulksms/contacts-text', ['uses' => 'RoyceController@contactsText']);
//     Route::post('bulksms/contacts-text', ['uses' => 'RoyceController@sendContactsText']);

//     Route::get('bulksms/group-text', ['uses' => 'RoyceController@groupText']);
//     Route::post('bulksms/group-text', ['uses' => 'RoyceController@sendGroupText']);
//     Route::get('bulksms/delivery-report', ['uses' => 'RoyceController@getDeliveryReport']);
//     Route::post('bulksms/delivery-report', ['uses' => 'RoyceController@pDeliveryReport']);
//     Route::get('bulksms/set-webhook', ['uses' => 'RoyceController@setWebhook']);
//     Route::get('bulksms/delete/{id}', ['uses' => 'RoyceController@deleteContact']);
//     Route::get('bulksms/edit-group/{id}', ['uses' => 'RoyceController@editGroup']);
//     Route::post('bulksms/edit-contact-group', ['uses' => 'RoyceController@editContactGroup']);
//     Route::any('bulksms/receive-delivery-report', ['uses' => 'RoyceController@receiveDeliveryReport']);

// });


Auth::routes();

Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('royceroute', [HomeController::class, 'index']);
    Route::get('dashboard', [HomeController::class, 'messages']);
    //Route::get('bulksms/', [HomeController::class, 'messages']);

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


