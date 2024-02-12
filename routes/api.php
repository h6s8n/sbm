<?php

use App\Model\Notification\UserDoctorNotification;
use App\Repositories\v2\ShortMessageService\SMSRepository;
use App\SendSMS;
use App\Services\Gateways\src\Zibal;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
//Route::post('/bb','Api\v2\BB\BigBlueController@updateUserId');
Route::middleware('auth:api')->post('/broadcasting/auth', 'Api\v1\Pusher\PusherController@webauth');



Route::prefix('v1')->namespace('Api\v1')->group(function () {
    /* /api/v1/doctor/specialists?special-limit=5&user-limit=0 */
    Route::get('site/specializations', 'Site\HomeController@getSpecializations');
    Route::get('doctor/specialists', 'Doctor\SpecializationController@withUsers');
    Route::get('doctor/specialists-calender', 'Doctor\SpecializationController@withCalender');
    Route::get('doctor/top', 'Doctor\DoctorController@top');
    Route::get('doctor-specialization/top', 'Doctor\DoctorController@specializationsTop');
    Route::get('doctor-suggest', 'Doctor\DoctorController@suggestDoctor');

    Route::post('/site/doctor/set-star-rate', 'Doctor\DoctorController@setStar');
    Route::get('/site/doctor/get-star-rate/{id}', 'Doctor\DoctorController@getStar');

    Route::get('/site/doctor/details/{id}', 'Doctor\DoctorController@detail');

    Route::get('/site/home', 'Site\HomeController@index');

    Route::get('/site/vaccination-centers', 'Site\HomeController@vaccinationCenters');

    Route::get('/site/specialties', 'Site\HomeController@getSpecialties');

    Route::post('/site/get-city', 'Site\HomeController@getCity');

    Route::get('/site/get-all-city', 'Site\HomeController@getAllCities');

    Route::get('/site/get-all-state', 'Site\HomeController@getAllStates');

    Route::get('pusher/twilio', 'Pusher\PusherController@TwilioTools');

    Route::post('/login', 'UserController@LoginAccount');

    Route::group(['prefix' => 'rate'], function () {
        Route::post('/store', 'Visit\RateController@store');
    });
    Route::prefix('register')->group(function () {
        Route::post('/confirm', 'UserController@registerConfirm');
        Route::post('/confirm/set', 'UserController@actionConfirm');
        Route::post('/confirm/resend', 'UserController@confirmResend');

        Route::post('/nationalcode', 'UserController@actionNationalcode');

        Route::post('/create-account', 'UserController@createAccount');
    });

    Route::prefix('password/reset')->group(function () {

        Route::post('/confirm', 'UserController@ForgetPasswordConfirm');
        Route::post('/change', 'UserController@ForgetPassword');
    });

    Route::prefix('search')->namespace('Search')->group(function () {

        Route::get('/', 'SearchController@search');
        Route::get('/2', 'SearchController@search2');
        Route::get('/quick', 'SearchController@quickSearch');
        Route::get('/quick2', 'SearchController@quickSearch2');
        Route::get('/specialties-doctors', 'SearchController@specialties_doctors');
        Route::get('/doctors', 'SearchController@doctors');
        //Route::get('/doctors/find', 'SearchController@doctorsFind');
        Route::get('/doctor/{username}', 'SearchController@profile');
        Route::get('/doctor/calender/visit/{username}', 'SearchController@GetCalender');

        Route::get('/doctors/introduction', 'SearchController@introduction_doctors');
    });

    Route::prefix('doctor/cod')->namespace('Doctor')->group(function () {
        Route::get('graphql/{type}/{data?}','CODController@report2')->name('graphql');
    });
	
	
	Route::post('financial-management/GetSetToken', "\App\Http\Controllers\FinancialManagementController@GetSetToken");

    Route::middleware('auth:api')->group(function () {
        //Route::get('financial-management/getdata', [FinancialManagementController::class, 'index']);
        Route::post('doctor/open-room', 'Pusher\PusherController@OpenRoomDoctor');
        Route::get('financial-management/getdata', "\App\Http\Controllers\FinancialManagementController@index");
		Route::post('financial-management/add-sheba', "\App\Http\Controllers\FinancialManagementController@addSheba");
		Route::post('financial-management/remove-sheba', "\App\Http\Controllers\FinancialManagementController@removeSheba");
		Route::post('financial-management/withdraw', "\App\Http\Controllers\FinancialManagementController@withdrawMoney");
        Route::post('financial-management/checkout-visit', "\App\Http\Controllers\FinancialManagementController@checkoutVisit");

        Route::prefix('user')->group(function () {
			
            Route::post('/city', 'User\ProfileController@getCity');

            Route::get('/states', 'User\ProfileController@getStates');

            Route::post('pusher/auth', 'Pusher\PusherController@Authenticate');


            Route::post('open-room', 'Pusher\PusherController@OpenRoomUser');


            Route::post('broadcasting/auth', 'Pusher\PusherController@auth');

            Route::get('chat', 'Pusher\PusherController@fetchMessages');

            Route::post('chat', 'Pusher\PusherController@sendMessage');

            Route::post('chat/seen', 'Pusher\PusherController@seenMessage');

            Route::delete('chat/delete/{message}', 'Pusher\PusherController@destroy');

            Route::get('get_start', 'UserController@start');

            Route::namespace('User')->group(function () {

                Route::prefix('medical-history')->group(function () {

                    Route::get('/info', 'MedicalHistoryController@getInfo');

                    Route::post('/set', 'MedicalHistoryController@save');
                });

                Route::get('/profile', 'ProfileController@getInfo');

                Route::post('/search', 'ProfileController@search');

                Route::post('/profile', 'ProfileController@save');

                Route::post('/profile/avatar', 'ProfileController@saveAvatar');


                Route::post('/increase/credit', 'CreditController@NewPay');

                Route::get('/transaction/reserve', 'TransactionsController@reserve');

            });


            Route::namespace('User\Visit')->group(function () {

                Route::prefix('visit')->group(function () {

                    Route::post('/left-the-room/{event}', 'MeetingController@leftRoom');

                    Route::get('/reserve', 'ReserveController@GetCalender');
                    Route::post('/reserve', 'ReserveController@ReservePay');

                    Route::get('/meeting', 'MeetingController@MeetingList');

                    Route::get('/get-meeting', 'MeetingController@getMeeting');

                    Route::post('/meeting/absence-of-doctor', 'MeetingController@absenceOfDoctor');

                    Route::get('/dossier', 'DossiersController@fetchFiles');
                    Route::post('/dossier', 'DossiersController@AddNew');
                    Route::delete('/dossier', 'DossiersController@delete');

                    Route::post('/video/connect', 'VideoController@ConnectChanel');
                    Route::post('/video/rate', 'VideoController@rate');
                    Route::post('/video/request', 'VideoController@request');
                });
            });
        });
        Route::prefix('doctor')->namespace('Doctor')->group(function () {

            Route::get('profile/info', 'ProfileController@GetDoctorInfo');
            Route::post('profile/info', 'ProfileController@DoctorInfoSave');
            Route::post('profile/avatar', 'ProfileController@saveAvatar');
            Route::get('/get-address','ProfileController@getAddress');
            Route::post('/set-address','ProfileController@setAddress');

            Route::post('profile/create_skill', 'ProfileController@create_skill');

            Route::get('calender', 'CalendarController@getInfo');
            Route::post('calender', 'CalendarController@Create');
            Route::delete('calender', 'CalendarController@delete');
            Route::get('calender/times', 'CalendarController@getTimes');
            Route::post('calender/times', 'CalendarController@setTimes');
            Route::post('calender/online', 'CalendarController@online');
            Route::post('calender/offline', 'CalendarController@offline');

            Route::post('contract/sign','ContractController@sign');
            Route::resource('contract','ContractController');

            Route::prefix('wallet')->group(function () {
                Route::get('invoice','WalletController@showInvoice');
                Route::get('overview/{status}','WalletController@overview');
                Route::post('invoice','WalletController@updateInvoice');
                Route::post('increase','WalletController@increase');
                Route::post('decrease','WalletController@decrease');
                Route::post('update/{wallet}','WalletController@updateWallet');
                Route::post('shebaInquiry','WalletController@shebaInquiry');
                Route::get('shebaList','WalletController@shebaList');
                Route::get('account_balance', 'WalletController@accountBalance');
                Route::prefix('exchange')->group(function (){
                    Route::get('stats' , 'WalletController@exchangeStats');
                    Route::post('store' , 'WalletController@exchangeStore');
                });
            });
            Route::prefix('cod')->group(function () {
                Route::get('invoice','CODController@showInvoice');
                Route::get('overview/{status}','CODController@overview');
                Route::get('report','CODController@report');
                Route::post('invoice','CODController@updateInvoice');
                Route::post('increase','CODController@increase');
                Route::post('decrease','CODController@decrease');
                Route::post('update/{cod}','CODController@updateCOD');
                Route::post('shebaInquiry','CODController@shebaInquiry');
                Route::get('shebaList','CODController@shebaList');
                Route::get('account_balance', 'CODController@accountBalance');
                Route::prefix('exchange')->group(function (){
                    Route::get('stats' , 'CODController@exchangeStats');
                    Route::post('store' , 'CODController@exchangeStore');
                });
            });

            Route::get('/profile', 'ProfileController@getInfo');
            Route::post('/profile', 'ProfileController@save');

            Route::get('/transaction/overview', 'TransactionsController@overview');
            Route::post('/transaction/overview', 'TransactionsController@overview2');
            Route::get('/transaction/reserve', 'TransactionsController@reserve');
            Route::post('/transaction/reserve', 'TransactionsController@reserve2');
            Route::get('/transaction/recent', 'TransactionsController@recent');
            Route::get('/transaction/wallet', 'TransactionsController@wallet');
            Route::get('/walletList', 'TransactionsController@walletList');
            Route::post('/transaction/wallet', 'TransactionsController@wallet2');
            Route::post('/transaction/cod', 'TransactionsController@cod');
            Route::post('/transaction/export/all', 'TransactionsController@export_all');
            Route::post('/transaction/wallet/export', 'TransactionsController@export_wallet');
            Route::post('/transaction/cod/export', 'TransactionsController@export_cod');
            Route::get('/transaction/chart', 'TransactionsController@chart');
            Route::post('/transaction/chart', 'TransactionsController@chart2');


            Route::namespace('Visit')->prefix('visit')->group(function () {

                Route::post('/left-the-room/{event}', 'MeetingController@leftRoom');

                Route::get('/duration', 'MeetingController@duration');

                Route::get('/meeting', 'MeetingController@MeetingList');
                Route::get('/user/medical-history', 'MedicalHistoryController@getUserInfo');

                Route::get('/dossier', 'DossiersController@fetchFiles');

                Route::get('/prescription', 'PrescriptionController@fetchFiles');
                Route::post('/prescription', 'PrescriptionController@AddNew');
                Route::delete('/prescription', 'PrescriptionController@delete');

                Route::post('/finish', 'MeetingController@finish');
                Route::post('/cancel', 'MeetingController@cancel');

                Route::post('/video/create', 'VideoController@CreateChanel');
                Route::post('/video/rate', 'VideoController@rate');
            });
        });
        Route::post('visit-room/voice/{token_room}', 'Visit\VoiceCallController@createRoom');
        Route::post('visit-room/create/{token_room}', 'Visit\SkyroomController@createRoom');
//        Route::post('visit-room/create/{token_room}', [\App\Http\Controllers\Api\v2\Binjoo\BinjooController::class, 'request']);
        Route::post('visit-room/join/{token_room}', 'Visit\SkyroomController@joinRoom');
    });


    Route::get('visit-room/delete-all', 'Visit\SkyroomController@deleteAllRoom');
    Route::get('visit-user/delete-all', 'Visit\SkyroomController@deleteAllUsers');
});

//Route::prefix('v1')->namespace('Api\v2')->middleware('auth:api')group(function (){
//   Route::post('visit-room/create/{token_room}', 'Binjoo\BinjooController@request');
//});

Route::prefix('v2')->namespace('Api\v2')->group(function () {

    Route::prefix('binjoo')->namespace('Binjoo')->group(function () {
    Route::post('/request','BinjooController@request');
    });


    Route::prefix('/yek-pay')->namespace('yekPay')->group(function () {
        Route::post('/verify', 'YekPayController@verify')
            ->name('yekpay.verify');
    });

    Route::get('/bb/update-users', 'BB\BigBlueController@updateUsers');

    Route::prefix('organize')->namespace('User')->group(function () {
        Route::get('/get-users', 'UserController@all');
    });
    Route::prefix('partner')->namespace('Partner')->group(function () {
        Route::post('/registration_request', 'RegistrationRequestController@store');
        Route::get('/index/{id?}', 'PartnerController@index');
    });
    Route::prefix('safe-call')->namespace('SafeCall')->group(function () {
        Route::post('/store', 'SafeCallController@store');
    });
    Route::prefix('search')->namespace('Search')->group(function () {
        Route::post('/similar', 'SearchController@SimilarDoctors');
    });
    Route::prefix('secretary')->namespace('Secretary')->group(function () {
        Route::post('/auth', 'SpecialSecretaryController@authentication');
    });
    Route::post('/triage/store', 'TriageController@store');

    Route::prefix('doctor')->namespace('User')->group(function () {
        Route::post('/badge-request', 'DoctorController@badgeRequest');

        Route::prefix('profile')->namespace('profile\Doctor')->group(function () {
            Route::get('/{value}', 'ProfileController@SinglePage');
        });
    });

    Route::prefix('advertising')->group(function (){
        Route::post('/register','vandar\AdvertisingController@register');
        Route::get('/{plan?}','vandar\AdvertisingController@index');
    });
//    Route::get('calendar/store','Calendars\CalendarController@store');


    Route::get('/online-doctors', 'Calendars\CalendarController@getOnlineDoctors');

    Route::get('/get-interpretation-doctors', 'Calendars\CalendarController@getInterpretationDoctors');

    Route::get('/get-in-person-doctors', 'Calendars\CalendarController@getInPersonDoctors');

    Route::get('/get-surgery-doctors', 'Calendars\CalendarController@getSurgeryDoctors');

    Route::get('/get-sponsor-doctors', 'Calendars\CalendarController@getSponsorDoctors');

    Route::get('/get-prescriptions-doctors', 'Calendars\CalendarController@getPrescriptionsDoctors');

    Route::prefix('pulsyno')->namespace('Affiliate')->group(function (){
        Route::get('/menu','AffiliateController@pulsynoMenu');
    });

    Route::prefix('authenticate')->namespace('Authenticate')->group(function () {

        Route::post('/mellat/auth', 'AuthenticateController@MellatLogin');

        Route::get('/jiring/auth', 'AuthenticateController@jiringLogin');

        Route::get('/pulsyno/auth', 'AuthenticateController@pulsynoLogin');

        Route::get('/hikish/auth', 'AuthenticateController@hikishLogin');

        Route::post('/register', 'AuthenticateController@register');

        Route::post('/login', 'AuthenticateController@login');

        Route::post('/send-confirmation-code', 'AuthenticateController@SendConfirmationCode');
//        ->middleware('throttle:2,1');

        Route::post('/validate-code', 'AuthenticateController@ValidateCode');

        Route::post('/forget', 'AuthenticateController@forget');

        Route::post('/logout', 'AuthenticateController@logout')
            ->middleware('auth:api');
    });
    Route::prefix('notification')->namespace('Notification')->group(function () {
            Route::get('sms', function (){

            });
    });

    Route::prefix('pulsyno')->namespace('Affiliate')->group(function (){
        Route::get('/get-link','AffiliateController@pulsynoGetToken');
    });

    Route::middleware('auth:api')->group(function () {

        Route::prefix('authenticate')->namespace('Authenticate')->group(function () {
            Route::post('/send-validation-code', 'AuthenticateController@SendValidationCode');
        });

            Route::prefix('pulsyno')->namespace('Affiliate')->group(function (){
            Route::get('/get-token','AffiliateController@pulsynoGetToken');
        });

        Route::get('notifications', function () {

            $message=null;
            if(auth()->user()->approve == 1){
                $message = '
                پزشک گرامی باتوجه به اعمال تعرفه های جدید
                در صورت نیاز به تغییر تعرفه ی ویزیت های خود با واحد پشتیبانی در ارتباط باشید.

                ';
            }else
//            elseif (strlen(auth()->user()->password) < 7)
            {
                $message = '
                مشاوره های آنلاین فقط در بستر خود سایت انجام می شود لطفا اتاق مشاوره خود را در ساعت ویزیت چک کنید.
                ';
            }
            return success_template(['data' => $message]);
        });
        Route::prefix('user')->namespace('User')->group(function () {
            Route::post('/set-time-notification/create',
                'UserController@SetTimeNotification_Store');

            Route::prefix('profile')->namespace('profile\User')->group(function () {
                Route::post('/store', 'ProfileController@store');
                Route::patch('/update', 'ProfileController@update');
            });

            Route::prefix('refund')->group(function () {
                Route::post('/store', 'RefundController@store');
            });

            Route::post('/setting', 'UserSettingController@update');
            Route::get('/get-settings', 'UserSettingController@getSettings');
        });
        Route::prefix('doctor')->namespace('User')->group(function () {
            Route::get('office/list','DoctorOfficeController@list');
            Route::resource('office','DoctorOfficeController');
//            Route::prefix('office')->group(function (){});
            Route::patch('/update', 'DoctorController@update');
            Route::get('partners', 'DoctorController@getPartners');
            Route::prefix('profile')->namespace('profile\Doctor')->group(function () {
                Route::patch('/update', 'ProfileController@update');
            });
        });
        Route::prefix('calendar')->namespace('Calendars')->group(function () {
            Route::patch('/update', 'CalendarController@update');

            Route::post('/datagrid','CalendarController@datagrid');
            Route::post('/increase','CalendarController@increase');
            Route::post('/decrease','CalendarController@decrease');

            Route::post('/store/in-person','CalendarController@inPersonStore');
            Route::post('/store/online','CalendarController@onlineStore');
            Route::post('/store','CalendarController@store');
            Route::post('/extend-pattern','CalendarController@extendPattern');

            Route::get('/get-extend-pattern','CalendarController@getExtendPattern');
            Route::get('/{id}', 'CalendarController@show');

        });
        Route::prefix('visit')->namespace('Visit')->group(function () {
            Route::prefix('doctor')->namespace('Doctor')->group(function () {
                Route::post('/secretary_finish', 'VisitController@secretary_finish');
                Route::get('/secretary_list/{status?}', 'VisitController@secretaryMeetingList');
                Route::get('/oldList/{status?}', 'VisitController@MeetingList3');
                Route::get('/list/{status?}', 'VisitController@MeetingList');
                Route::prefix('dossier')->namespace('Dossier')->group(function () {
                    Route::get('/', 'DossierController@index');
                    Route::post('/', 'DossierController@store');
                    Route::delete('/', 'DossierController@delete');

                });
            });
            Route::prefix('actions')->group(function () {
                Route::post('/store', 'VisitActionController@store');
            });
        });
        Route::prefix('affiliate')->namespace('Affiliate')->group(function () {
            Route::get('affiliate-transactions', 'AffiliateController@groups');
            Route::get('affiliate-token', 'AffiliateController@getToken');
        });
        Route::prefix('message')->namespace('Message')->group(function () {
            Route::post('/send', 'MessageController@send');
            Route::post('/initial', 'MessageController@initial_');
            Route::post('/initial2', 'MessageController@initial_2');
            Route::get('/get-initial', 'MessageController@get_initial');
        });
        Route::prefix('bb')->namespace('BB')->group(function () {
            Route::post('/create', 'BigBlueController@create');
        });
        Route::prefix('/yek-pay')->namespace('yekPay')->group(function () {
            Route::post('/pay', 'YekPayController@pay');
        });
    });
});

Route::prefix('admin')->middleware('auth:api')->namespace('Api\admin')->group(function (){
    Route::prefix('users')->namespace('User')->group(function (){
        Route::resource('/','UserController');
        Route::post('/datagrid','UserController@datagrid');
    });
});


/*Route::group(["perfix"=>"a"],function () {
	Route::get('test-ali', function (){
    return collection([1,2,3,4]);
		dd(auth(), auth()->user());
});
	
    Route::get('financial-management/getdata', "FinancialManagementController@index");

   // Route::post('financial-management/withdraw', [FinancialManagementController::class, 'withdrawMoney']);

    //Route::post('financial-management/exchange', [FinancialManagementController::class, 'exchange']);
});
*/



