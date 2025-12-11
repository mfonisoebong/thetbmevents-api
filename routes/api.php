<?php

use App\Http\Controllers\admin\CommisionsController;
use App\Http\Controllers\admin\EventsController as AdminEventsController;
use App\Http\Controllers\admin\FeaturesController;
use App\Http\Controllers\admin\FinancesController;
use App\Http\Controllers\admin\NewslettersController as AdminNewsletterController;
use App\Http\Controllers\admin\OrderHistoryController;
use App\Http\Controllers\admin\OverviewController;
use App\Http\Controllers\admin\PaymentMethodController;
use App\Http\Controllers\admin\SlidersController;
use App\Http\Controllers\admin\TestimoniesController;
use App\Http\Controllers\admin\UsersController;
use App\Http\Controllers\users\AuthController;
use App\Http\Controllers\users\ContactMessagesController;
use App\Http\Controllers\users\CouponController;
use App\Http\Controllers\users\EventsController;
use App\Http\Controllers\users\HomePageController;
use App\Http\Controllers\users\NewsletterController;
use App\Http\Controllers\users\NotificationsController;
use App\Http\Controllers\users\OrganizerBankDetailsController;
use App\Http\Controllers\users\PaymentController;
use App\Http\Controllers\users\PaymentMethodController as UserPaymentMethodController;
use App\Http\Controllers\users\PaymentWebhook;
use App\Http\Controllers\users\ProfileController;
use App\Http\Controllers\users\SalesController;
use App\Http\Controllers\users\TicketsController;
use Illuminate\Support\Facades\Route;


// User routes

Route::get('/homepage', [HomePageController::class, 'getHomePageData']);

Route::post('/newsletter', [NewsletterController::class, 'store']);

Route::prefix('payment-methods')->group(function () {
    Route::get('/', [UserPaymentMethodController::class, 'getPaymentMethod']);
});

Route::prefix('events')->group(function () {
    Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('user')->group(function () {
            Route::get('/', [EventsController::class, 'getUserEvents']);
            Route::get('/{event}', [EventsController::class, 'getUserEvent']);
        });

        Route::middleware(['auth:sanctum', 'role:organizer', 'account_state:active'])->group(function () {
            Route::post('/store', [EventsController::class, 'store']);
            Route::post('/send-blast-email', [EventsController::class, 'sendBlastEmail']);
            Route::post('/export', [EventsController::class, 'exportCsv']);
            Route::post('/{event}/export/attendees', [EventsController::class, 'exportAttendeesCsv']);
            Route::post('/{event}', [EventsController::class, 'update']);
            Route::delete('/{event}', [EventsController::class, 'destroy']);
        });
    });
    Route::get('/category', [EventsController::class, 'getEventsInCategory']);
    Route::get('/categories', [EventsController::class, 'getCategories']);
    Route::get('/latest', [EventsController::class, 'getLatestEvents']);
    Route::get('/filter', [EventsController::class, 'filterEvents']);
    Route::get('/location', [EventsController::class, 'getEventsByLocation']);
    Route::get('/slugs', [EventsController::class, 'getEventsSlugs']);
    Route::get('/{alias}', [EventsController::class, 'getEvent']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('sales')->group(function () {
        Route::get('/', [SalesController::class, 'getSales']);
        Route::post('/{sale}/resend-purchased-tickets', [SalesController::class, 'resendPurchasedTickets']);
    });

    Route::prefix('tickets')->group(function () {
        Route::post('/', [TicketsController::class, 'store']);
        Route::patch('/{event}', [TicketsController::class, 'update']);
        Route::delete('/{ticket}', [TicketsController::class, 'destroy']);
        Route::post('/verify/{ticket}', [TicketsController::class, 'verifyTicket']);
        //        Route::get('/purchased', [PurhcasedTicketsController::class, 'getPurhcasedTickets']);
        //        Route::get('/purchased/{ticket}', [PurhcasedTicketsController::class, 'getTicket']);
        //        Route::get('/qrcode/{ticket}', [PurhcasedTicketsController::class, 'getQrCode']);
    });

    Route::prefix('coupons')->group(function () {
        Route::get('/', [CouponController::class, 'viewAll']);
        Route::get('/{coupon}', [CouponController::class, 'view']);
        Route::post('/', [CouponController::class, 'store']);
        Route::delete('/{coupon}', [CouponController::class, 'destroy']);
        Route::patch('/{coupon}', [CouponController::class, 'update']);
    });
});


Route::group(['prefix' => 'payments'], function () {

    Route::post('/paystack', [PaymentController::class, 'paystackRedirectToGateway']);
    Route::post('/flutterwave', [PaymentController::class, 'flutterwaveRedirectToGateway']);
    Route::post('/free', [PaymentController::class, 'freePayment']);

    Route::get('/callback/{reference}', [PaymentController::class, 'callback']);
});


Route::prefix('/contact-messages')->group(function () {
    Route::post('/', [ContactMessagesController::class, 'store'])
        ->middleware('validaterecaptcha');
    ;
});

Route::prefix('auth')->group(function () {

    Route::group(['middleware' => 'auth:sanctum'], function () {
        Route::patch('/user', [AuthController::class, 'update']);
        Route::patch('/user/password', [AuthController::class, 'updatePassword']);
        Route::get('/user', [AuthController::class, 'getUser']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/resend-otp', [AuthController::class, 'resendOtpCode']);
        Route::post('/verify-otp', [AuthController::class, 'verifyOtpCode']);
        Route::post('/login-with-id', [AuthController::class, 'loginAdminWithId']);
    });

    Route::group(['middleware' => 'guest'], function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->name('login');
        // ->middleware('validaterecaptcha');
        Route::post('/send-password-reset', [AuthController::class, 'sendPasswordReset']);
        Route::get('/password-reset/{token}', [AuthController::class, 'getResetTokenUser']);
        Route::post('/password-reset', [AuthController::class, 'resetPasswordWithToken']);
        Route::post('/register', [AuthController::class, 'register']);
        // ->middleware('validaterecaptcha');
        Route::get('/login/google', [AuthController::class, 'googleLogin']);
        // ->middleware('validaterecaptcha');

        Route::get('/register/google', [AuthController::class, 'googleRegister']);
        // ->middleware('validaterecaptcha');

        Route::get('/google/callback')
            ->middleware('googleregister')
            ->middleware('googlelogin');
    });
});

Route::group(['prefix' => 'profile', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/overview', [ProfileController::class, 'getOverview']);
    Route::get('/finance/overview', [ProfileController::class, 'getFinanceOverview']);
    Route::post('/avatar/upload', [ProfileController::class, 'uploadAvatar']);
    Route::post('/avatar/remove', [ProfileController::class, 'removeAvatar']);
});

Route::group(['prefix' => 'notifications', 'middleware' => 'auth:sanctum'], function () {
    Route::get('/', [NotificationsController::class, 'getNotifications']);
    Route::patch('/read/{notification}', [NotificationsController::class, 'readNotification']);
    Route::patch('/read-all', [NotificationsController::class, 'readAll']);
    Route::delete('/all', [NotificationsController::class, 'destroyAll']);
});


// Webhooks

Route::group(['prefix' => 'webhooks'], function () {
    Route::post('/paystack', [PaymentWebhook::class, 'paystackWebhook']);
});

Route::group(['prefix' => 'bank-details', 'middleware' => ['auth:sanctum', 'role:organizer']], function () {
    Route::get('/', [OrganizerBankDetailsController::class, 'getBankDetails']);
    Route::post('/', [OrganizerBankDetailsController::class, 'store']);
    Route::patch('/', [OrganizerBankDetailsController::class, 'update']);
    Route::delete('/', [OrganizerBankDetailsController::class, 'destroy']);
});

// Admin routes
Route::group(['prefix' => 'admin', 'middleware' => 'auth:sanctum'], function () {

    Route::prefix('overview')->group(function () {
        Route::get('/', [OverviewController::class, 'getOverview']);
        Route::get('/revenue', [OverviewController::class, 'getNetRevenue']);
        Route::get('/events', [OverviewController::class, 'getEventsOverview']);
    })->middleware('role:admin,manager');


    Route::prefix('users')->group(function () {
        Route::middleware('role:admin,super_admin')->group(function () {
            Route::get('/staffs', [UsersController::class, 'getAdmins']);
            Route::post('/staffs', [UsersController::class, 'store']);
        });


        Route::middleware('role:admin,support')->group(function () {
            Route::get('/', [UsersController::class, 'getUsers']);
            Route::get('/organizers', [UsersController::class, 'getOrganizers']);
            Route::get('/organizers/{organizer}', [UsersController::class, 'getOrganizer']);
            Route::patch('/organizers/settings', [UsersController::class, 'updateOrganizerSettings']);
            Route::patch('/organizers/activate/{organizer}', [UsersController::class, 'activateOrganizer']);
            Route::patch('/organizers/deactivate/{organizer}', [UsersController::class, 'deactivateOrganizer']);
            Route::post('/organizers/login-as/{organizer}', [UsersController::class, 'loginAs']);
            Route::post('/export', [UsersController::class, 'exportUsersCSV']);
            Route::get('/{user}', [UsersController::class, 'getUser']);
            Route::delete('/{user}', [UsersController::class, 'destroyUser']);
        });
    });

    Route::prefix('events')->group(function () {

        Route::get('/', [AdminEventsController::class, 'getAllEvents']);
        Route::get('/sliders', [SlidersController::class, 'getSliders']);
        Route::patch('/sliders', [SlidersController::class, 'update']);

        Route::prefix('categories')->group(function () {
            Route::get('/', [AdminEventsController::class, 'getCategories']);
            Route::post('/', [AdminEventsController::class, 'store']);
            Route::post('/{category}', [AdminEventsController::class, 'update']);
            Route::delete('/{category}', [AdminEventsController::class, 'destroy']);
            Route::delete('/{category}/icon', [AdminEventsController::class, 'removeIcon']);
        });
    })->middleware('role:admin,manager');

    Route::prefix('newsletters')->group(function () {
        Route::get('/', [AdminNewsletterController::class, 'getNewsletterSignups']);
    })
        ->middleware('role:admin,manager');

    Route::prefix('commision')->group(function () {
        Route::post('/', [CommisionsController::class, 'store']);
        Route::patch('/{commision}', [CommisionsController::class, 'update']);
        Route::delete('/{commision}', [CommisionsController::class, 'destroy']);
    })
        ->middleware('role:admin,support');

    Route::prefix('/features')->group(function () {
        Route::post('/', [FeaturesController::class, 'store']);
        Route::get('/', [FeaturesController::class, 'getFeatures']);
        Route::post('/update', [FeaturesController::class, 'update']);
    })
        ->middleware('role:admin,manager');


    Route::prefix('order-history')->group(function () {
        Route::get('/', [OrderHistoryController::class, 'getOrderHistory']);
        Route::get('/export', [OrderHistoryController::class, 'exportAsCSV']);
    })
        ->middleware('role:admin,support');

    Route::prefix('/testimonies')->group(function () {
        Route::get('/', [TestimoniesController::class, 'getTestimonies']);
        Route::post('/', [TestimoniesController::class, 'store']);
        Route::post('/update', [TestimoniesController::class, 'update']);
    })
        ->middleware('role:admin,support');

    Route::prefix('finances')->group(function () {
        Route::get('/revenue-commisions-overview', [FinancesController::class, 'getRevenueOverview']);
        Route::get('/all-through-the-year', [FinancesController::class, 'getFinancesAllThroughTheYear']);
        Route::get('/top-customers', [FinancesController::class, 'getTopCustomers']);
        Route::get('/top-organizers', [FinancesController::class, 'getTopOrganizers']);
    })
        ->middleware('role:admin,manager');

    Route::prefix('payment-methods')->group(function () {
        Route::patch('/vella', [PaymentMethodController::class, 'updateVellaPaymentMethod']);
        Route::patch('/paystack', [PaymentMethodController::class, 'updatePaystackPaymentMethod']);
    })
        ->middleware('role:admin,manager');
});


// Mobile routes
require_once('mobile_api.php');
