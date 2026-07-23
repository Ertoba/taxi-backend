<?php

use App\Http\Controllers\Api\Auth\TokenController;
use App\Http\Controllers\Api\V1\Admin\KeepzSettlementApiController;
use App\Http\Controllers\Api\V1\Admin\RideRequestController;
use App\Http\Controllers\Api\V1\Admin\SliderApiController;
use App\Http\Controllers\Front\PaymentFrontController;
use App\Strategies\PaypalStrategy;
use Illuminate\Support\Facades\Route;

Route::post('/paypal/ipn', [PaymentFrontController::class, 'handlePaypalIPN'])
    ->name('paypal.ipn');
Route::post('/paypal/webhook', [PaypalStrategy::class, 'handleWebhook'])
    ->name('paypal.webhook');

Route::post('/generateToken', [TokenController::class, 'issueSanctumToken'])
    ->name('token.generate');

Route::group(['prefix' => 'v1', 'as' => 'api.', 'namespace' => 'Api\V1\Admin', 'middleware' => ['auth:sanctum']], function () {
    Route::post('/ride-requests', [RideRequestController::class, 'createRide']);
    Route::get('/ride-requests', [RideRequestController::class, 'getRides']);
    Route::patch('/ride-requests/{id}/status', [RideRequestController::class, 'updateRideStatus']);
    Route::post('/notify-ride-accepted', [RideRequestController::class, 'notifyRideAccepted'])
        ->middleware('throttle:10,1');
    Route::post('/send-pickup-otp', [RideRequestController::class, 'sendPickupOtp'])
        ->middleware('throttle:5,1');
    Route::get('/sliders', [SliderApiController::class, 'sliders']);

    Route::post('/userRegister', 'AppUsersApiController@userRegister')->name('userRegister');
    Route::post('/otpVerification', 'AppUsersApiController@otpVerification');
    Route::post('/userLogin', 'AppUsersApiController@userLogin');
    Route::post('/userLogout', 'AppUsersApiController@userLogout');
    Route::post('/puthostRequest', 'AppUsersApiController@puthostRequest');
    Route::post('/gethostStatus', 'AppUsersApiController@gethostStatus');
    Route::post('/socialLogin', 'AppUsersApiController@socialLogin');
    Route::post('/userEmailLogin', 'AppUsersApiController@userEmailLogin')->name('userEmailLogin');
    Route::post('/forgotPassword', 'AppUsersApiController@forgotPassword');
    Route::post('/verifyResetToken', 'AppUsersApiController@verifyResetToken');
    Route::post('/ResendTokenEmailChange', 'AppUsersApiController@ResendTokenEmailChange');
    Route::post('/sendMobileLoginOtp', 'AppUsersApiController@sendMobileLoginOtp');
    Route::post('/userMobileLogin', 'AppUsersApiController@userMobileLogin');
    Route::post('/sendOnlyEmailLoginOtp', 'AppUsersApiController@sendOnlyEmailLoginOtp');
    Route::post('/userOnlyEmailLogin', 'AppUsersApiController@userOnlyEmailLogin');
    Route::post('/resetPassword', 'AppUsersApiController@resetPassword');
    Route::post('/emailcheck', 'AppUsersApiController@emailcheck');
    Route::post('/mobilecheck', 'AppUsersApiController@mobilecheck');
    Route::post('/ResendOtp ', 'AppUsersApiController@ResendOtp');
    Route::post('/ResendToken ', 'AppUsersApiController@ResendToken');
    Route::post('/updatePassword ', 'AppUsersApiController@updatePassword');
    Route::post('/getUserWallet ', 'AppUsersApiController@getUserWallet');
    Route::post('/getUserWalletTransactions ', 'AppUsersApiController@getUserWalletTransactions');
    Route::post('/getVendorWallet ', 'AppUsersApiController@getVendorWallet');
    Route::post('/getVendorWalletTransactions ', 'AppUsersApiController@getVendorWalletTransactions');
    Route::post('/insertPayout ', 'AppUsersApiController@insertPayout');
    Route::post('/getPayoutTransactions ', 'AppUsersApiController@getPayoutTransactions');

    Route::post('/update-payout-method', 'PayoutMethodApiController@updatePayoutMethod');
    Route::post('/get-payout-methods', 'PayoutMethodApiController@getPayoutMethods');
    Route::get('/get-payout-types', 'PayoutMethodApiController@getPayoutTypes');
    Route::post('/get-keepz-split-settlements', [KeepzSettlementApiController::class, 'index'])
        ->middleware('throttle:30,1');

    Route::post('/getDriverEarings ', 'DriverFinanceApiController@getDriverEarings');
    Route::post('/deleteAccount ', 'AppUsersApiController@deleteAccount');
    Route::post('/addEditVerificationDocuments', 'AppUsersApiController@addEditVerificationDocuments');
    Route::post('/getVerificationDocuments', 'AppUsersApiController@getVerificationDocuments');

    Route::post('/getUserProfile ', 'UserProfileController@getUserProfile');
    Route::post('/getUseritems ', 'UserProfileController@getUseritems');
    Route::post('/getVendorItemReviews ', 'UserProfileController@getVendorItemReviews');

    Route::get('/yourLocations', 'CitiesApiController@yourLocations');
    Route::post('/searchCities', 'CitiesApiController@searchCities');
    Route::get('/getAllCategories', 'ItemTypeApiController@getAllCategories');
    Route::get('service-types', 'ItemTypeApiController@getServiceTypes');
    Route::get('item-types/by-service-type', 'ItemTypeApiController@getItemTypesByServiceType');
    Route::post('/getItemsByItemType', 'ItemTypeApiController@getItemsByItemType');
    Route::post('/editItem', 'ItemsApiController@editItem');
    Route::post('/myItems', 'ItemsApiController@myItems');
    Route::post('/addEditItemImage', 'ItemsApiController@addEditItemImage');
    Route::post('/addEditItemImages', 'ItemsApiController@addEditItemImages');
    Route::get('/getItemRules', 'RentalItemRuleApiController@getItemRules');
    Route::get('/getMakes', 'MakeApiController@getMakes');
    Route::get('/getMakesModel', 'MakeApiController@getMakesModel');

    Route::get('/getCancelReasons', 'CancellationReasonController@getCancelReasons');
    Route::get('/getCancellationPolicies', 'BookingApiController@getCancellationPolicies');
    Route::post('/getItemReviews', 'ReviewApiController@getItemReviews');
    Route::post('/giveReviewByUser', 'ReviewApiController@giveReviewByUser');
    Route::post('/giveReviewByHost', 'ReviewApiController@giveReviewByHost');

    Route::post('/getItemPrices', 'BookingApiController@getItemPrices');
    Route::post('/bookItem', 'BookingApiController@bookItem');
    Route::post('/bookingRecord', 'BookingApiController@bookingRecord');
    Route::post('/vendorbookingRecord', 'BookingApiController@vendorBookingRecord');
    Route::post('/confirmBookingByHost', 'BookingApiController@confirmBookingByHost');
    Route::post('/updateBookingStatusByDriver', 'BookingApiController@updateBookingStatusByDriver');
    Route::post('/updatePaymentStatusByDriver', 'BookingApiController@updatePaymentStatusByDriver');
    Route::post('/updatePaymentStatusByUser', 'BookingApiController@updatePaymentStatusByUser');
    Route::post('/updateBookingStatusByUser', 'BookingApiController@updateBookingStatusByUser');

    Route::post('/editProfile', 'MyAccountController@editProfile');
    Route::post('/uploadProfileImage', 'MyAccountController@uploadProfileImage');
    Route::post('/checkMobileNumber', 'MyAccountController@checkMobileNumber');
    Route::post('/changeMobileNumber', 'MyAccountController@changeMobileNumber');
    Route::post('/checkEmail', 'MyAccountController@checkEmail');
    Route::post('/changeEmail', 'MyAccountController@changeEmail');
    Route::post('/getDriverDashboardStats', 'MyAccountController@getDriverDashboardStats');

    Route::post('static-pages/media', 'StaticPagesApiController@storeMedia')->name('static-pages.storeMedia');
    Route::apiResource('static-pages', 'StaticPagesApiController');
    Route::get('StaticPage', 'StaticPagesApiController@StaticPage');
    Route::post('all-packages/media', 'AllPackagesApiController@storeMedia')->name('all-packages.storeMedia');
    Route::apiResource('all-packages', 'AllPackagesApiController', ['except' => ['destroy']]);
    Route::get('getgeneralSettings', 'GeneralSettingApiController@getgeneralSettings')->name('getSettings');
    Route::post('/getTotalPayoutAmount', 'PayoutApiController@getTotalPayoutAmount');
    Route::post('fcmUpdate', 'AppUsersApiController@fcmUpdate');
    Route::post('emailSmsNotification', 'AppUsersApiController@emailSmsNotification');
    Route::post('/getCurrencyDetails', 'CurrencyApiController@index')->name('getCurrencyDetails');
    Route::get('/updateRates', 'CurrencyApiController@updateRates')->name('updateRates');
    Route::get('/sos', 'SOSController@index')->name('sos.index');
});
