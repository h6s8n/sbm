<?php

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

use App\Model\Visit\EventReserves;
use App\SendSMS;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Ixudra\Curl\Facades\Curl;
use App\Model\Visit\Message;
use App\Services\Gateways\src\PayStar;
use App\Services\Gateways\src\PayStarWallet;
use App\Http\Controllers\Api\v1\Doctor;

//require base_path('app/jdf.php');
Route::get('/', function () {

//    dd(\Illuminate\Support\Facades\Hash::make('SBM2020SBM'));
//     phpinfo();
    return redirect()->route('admin.dashboard');
});

Route::prefix('webhooks')->namespace('Webhook')->group(function () {
    Route::prefix('financial')->namespace('Financial')->group(function () {
        Route::post('cod/increase', 'CODController@increase');
    });
});

Route::get('/statics-public/{file}','Api\v2\FileManager\FileManagerController@show')->where('file','.*');

Route::get('/test', function () {
    dd(rand(111111, 999999).time());
//    $messages = Message::where('file','like','%.php')->get();
//    dd($messages);
//    try {
//
//        $count = \App\User::select(DB::raw('count(id)'))
//            ->whereDate('created_at','>=',Carbon::now()->subWeeks(1)->format('Y-m-d'))
//            ->where('approve',2)
////            ->whereHas('UserEvents', function ($query) {
////                $query->whereDate('reserve_time', '>=', Carbon::now()->subWeeks(1)->format('Y-m-d'));
////            })
//            ->get()->toArray();
//    }catch (Exception $exception){dd($exception->getMessage());}
//    dd($count);
//    $vandar = new \App\Http\Controllers\Api\v2\vandar\VandarController();
//    $token = $vandar->return_token();
//
//    $headers = array(
//        "Accept: application/json",
//        "Authorization: Bearer ".$token,
//    );
//    $ch_ = curl_init();
//    curl_setopt($ch_, CURLOPT_HTTPHEADER, $headers);
//    curl_setopt($ch_, CURLOPT_URL,
//        'https://api.vandar.io/v2.1/business/Sbm/settlement/85a77c00-d772-11eb-bd9b-091312da3105');
//    curl_setopt($ch_, CURLOPT_RETURNTRANSFER, true);
//
//    $result = curl_exec($ch_);
//    curl_close($ch_);
//
//    $result = json_decode($result, true);
//    dd($result);

});
//Route::get('/test/top',function (){
//    $data['Amount']='50000';
//    $data['OrderId']=6553646327;
//
//    $top = new \App\Http\Controllers\Api\v2\top\TopController();
//    return $top->pay($data);
//
//});
//Route::get('/bb/create/users',function (){
//    dispatch_now(new \App\Jobs\AddBBUsers());
//});
//Route::get('/user_import', 'DeveloperController@userimport');
//Route::get('/user_mdical_history', 'DeveloperController@user_mdical_history');
//Route::get('/user_confirms', 'DeveloperController@user_confirms');
//Route::get('/doctor_calenders', 'DeveloperController@doctor_calenders');
//Route::get('/event_reserves', 'DeveloperController@event_reserves');
//Route::get('/messages', 'DeveloperController@messages');
//Route::get('/prescriptions', 'DeveloperController@prescriptions');
//Route::get('/transaction_reserves', 'DeveloperController@transaction_reserves');
//Route::get('/transaction_doctors', 'DeveloperController@transaction_doctors');
//Route::get('/update_image', 'DeveloperController@update_image');
//Route::get('/users_skills', 'DeveloperController@users_skills');
//Route::get('/dossiers', 'DeveloperController@dossiers');
//Route::get('/edit_doctor_calenders', 'DeveloperController@edit_doctor_calenders');
//Route::get('/edit_event_reserves', 'DeveloperController@edit_event_reserves');
//Route::get('/chane_user_image', 'DeveloperController@chane_user_image');
//Route::get('/change_visit_hours', 'DeveloperController@change_visit_hours');
//Route::get('/transaction_change_event', 'DeveloperController@transaction_change_event');
//Route::get('/event_reserves_change_finish', 'DeveloperController@event_reserves_change_finish');
//Route::get('/TransactionReserve', 'DeveloperController@TransactionReserveApp');
Route::get('/image_resize', 'DeveloperController@image_resize');

Route::get('/profile_doctor/{value}', 'Api\v2\User\profile\Doctor\ProfileController@SinglePage');

Route::get('/get-embed/{username}/{param}', 'Api\v1\Doctor\EmbedController@getembed');
Route::get('/embed/{username}', 'Api\v1\Doctor\EmbedController@GetCalender');

Route::get('/get-affiliate/{param}/{code}/{tag}', 'Api\v1\Doctor\EmbedController@getAffiliateCode');
Route::get('/affiliate/{code}/{tag}', 'Api\v1\Doctor\EmbedController@GetAffiliate');

Route::prefix('payment')->namespace('Api\v1\User')->group(function () {

    Route::get('reserve/{token}', 'Visit\ReserveController@gateway');

    Route::get('/verify/reserve/{token}', 'Visit\ReserveController@verify')->name('reserve.verify');

    Route::get('/credit/{token}', 'CreditController@gateway');


});

Route::prefix('payment')->group(function () {

    Route::namespace('Api\v1\Doctor')->group(function (){
        Route::get('/service/{token}', 'WalletController@gateway');
        Route::get('/increase_service/{token}', 'WalletController@verify');
        Route::post('/increase_service/{token}', 'WalletController@verify');
    });
    Route::namespace('Api\v2\vandar')->group(function (){
        Route::get('/advertising/{token}', 'AdvertisingController@gateway');
        Route::get('/verify/advertising/{token}', 'AdvertisingController@adVerify');
        Route::post('/verify/advertising/{token}', 'AdvertisingController@adVerify');
    });

});

Route::prefix('payment')->namespace('Api\v2\payment')->group(function (){
    Route::get('/paystar/verify','PaystarController@verify')->name('paystar.verify');
    Route::get('/nextpay/verify','NextPayController@verify')->name('nextpay.verify');
});

Route::prefix('condition_pay')->namespace('Api\v1\User')->group(function () {

    Route::get('/reserve/{token}', 'Visit\ReserveController@ConditionPay');
    Route::post('/reserve/{token}', 'Visit\ReserveController@ConditionPay');

    Route::get('/increase_credit/{token}', 'CreditController@verify');
    Route::post('/increase_credit/{token}', 'CreditController@verify');
    Route::get('/credit/{token}', 'CreditController@ConditionPay');
    Route::post('/credit/{token}', 'CreditController@ConditionPay');


});

Route::prefix('top')->namespace('Api\v2\top')->group(function () {

    Route::post('/confirm', 'TopController@confirm')
        ->name('top.confirm');
});

Route::prefix('vandar')->namespace('Api\v2\vandar')->group(function () {

    Route::get('/verify', 'VandarController@verify')
        ->name('vandar.verify');

    Route::get('/advertising/verify', 'AdvertisingController@verify')
        ->name('vandar.advertising.verify');
});

Route::prefix('zibal')->namespace('Api\v2\Zibal')->group(function () {

    Route::get('/verify', 'ZibalController@verify')
        ->name('zibal.verify');

    Route::get('/verify2', 'ZibalController@verify2')
        ->name('zibal.verify2');
});


Route::prefix('yek-pay')->namespace('Api\v2\yekPay')->group(function () {
    Route::post('/verify', 'YekPayController@verify')
        ->name('yek-pay.verify');
});

Route::prefix('cp-manager')->namespace('Admin')->group(function () {

    Route::prefix('transactions')->namespace('Transaction')->group(function () {
        Route::get('/{user}', 'TransactionsController@index')->name('transactions.users.index');
    });

    Route::get('/', 'AdminController@home')->name('cp.index');
    Route::get('/login', 'AdminController@login');
    Route::post('/login', 'AdminController@ActionLogin');

    Route::get('/logout', 'AdminController@logout');

    Route::post('/city', 'AdminController@getCity')->name('get.cities');

    Route::middleware('admin')->group(function () {

        Route::prefix('vandar')->namespace('Vandar')->group(function (){
            Route::get('pay','VandarController@index')->name('admin.vandar.index');
            Route::post('pay','VandarController@pay');

            Route::get('verify','VandarController@verify')->name('admin.vandar.verify');
        });



        Route::get('/dashboard', 'AdminController@dashboard')->name('admin.dashboard');

        Route::get('/setting', 'AdminController@setting');

        Route::prefix('voice-call')->group(function () {

            Route::get('/create-call', 'VoiceCallController@CreateCall')
                ->name('admin.create.call');

            Route::get('/change-number/{id}', 'VoiceCallController@lists')
                ->name('admin.change.number');

            Route::post('/change-number/{id}', 'VoiceCallController@change')
                ->name('admin.change.number.change');
        });

        Route::prefix('badge')->namespace('Badge')->group(function () {

            Route::get('/index', 'BadgeController@index')
                ->name('badge.index');

            Route::get('/requests', 'BadgeController@requests')
                ->name('badge-requests.index');

            Route::get('/create', 'BadgeController@create')
                ->name('badge.create');

            Route::post('/create', 'BadgeController@store')
                ->name('badge.store');

            Route::get('/edit/{badge}', 'BadgeController@edit')
                ->name('badge.edit');

            Route::get('/edit-request/{request}', 'BadgeController@editRequest')
                ->name('badge-request.edit');

            Route::post('/edit/{badge}', 'BadgeController@update')
                ->name('badge.update');

            Route::post('/edit-request/{request}', 'BadgeController@updateRequest')
                ->name('badge-request.update');

            Route::get('/assign-to/{user}', 'BadgeController@assign')
                ->name('assign.badge');
            Route::get('{badge}/detach/{user}', 'BadgeController@detach')
                ->name('detach.badge');
            Route::post('/assign-to/{user}', 'BadgeController@storeAssign')
                ->name('store.assign.badge');
        });
        Route::namespace('User')->group(function () {

            Route::get('/users', 'UserController@users');
            Route::get('/user/changing-approve/{user}','UserController@ChangingApprove')
                ->name('user.change.approve');
            Route::get('/support', 'UserController@support');

            Route::get('/user/export-users', 'UserController@exportUsers');
            Route::get('/user/export-inactive', 'UserController@exportUsersDiActive');

            Route::get('/user/add', 'UserController@userAdd');
            Route::post('/user/add', 'UserController@ActionUserAdd');

            Route::get('/user/edit/{user}', 'UserController@userEdit')->name('user.edit');
            Route::post('/user/edit/{user}', 'UserController@ActionUserEdit');

            Route::prefix('triage')->group(function () {
                Route::get('/index', 'TriageController@index')->name('triage.index');

                Route::get('/edit/{id}', 'TriageController@edit')->name('triage.edit');
                Route::post('/edit/{triage}', 'TriageController@update')->name('triage.update');
            });

            Route::get('/user/codes', 'UserController@codes')
                ->name('user.codes');

            Route::get('/user/codes/delete', 'UserController@unblock')
                ->name('user.delete.code');

            Route::get('/user/setting/{user}', 'UserSettingController@edit')
                ->name('user.setting');

            Route::post('/user/setting/{user}', 'UserSettingController@update')
                ->name('user.setting.update');

            Route::get('/user/refund', 'RefundController@index')
                ->name('refund.index');

        });

        Route::namespace('Doctor')->group(function () {

            Route::prefix('pattern')->group(function (){
                Route::get('index','PatternController@index')
                ->name('pattern.index');
            });
            Route::prefix('comments')->group(function () {
                Route::get('/', 'StarRateController@index')->name('comment.index');

                Route::get('/confirm/{comment}', 'StarRateController@confirm')
                    ->name('comment.confirm');

                Route::get('/rate_confirm/{comment}', 'StarRateController@rate_confirm')
                    ->name('comment.rate_confirm');

                Route::get('/reject/{comment}', 'StarRateController@reject')
                    ->name('comment.reject');

                Route::get('/reply/{comment}', 'StarRateController@reply')
                    ->name('comment.reply');
                Route::post('/update/{comment}', 'StarRateController@update')
                    ->name('comment.update');
                Route::get('/edit/{comment}', 'StarRateController@edit')
                    ->name('comment.edit');
            });

            Route::get('/doctor/underemployed', 'DoctorController@underemployed')
                ->name('doctors.underemployed');

            Route::get('/doctor/export/underemployed', 'DoctorController@exportUnderemployed')
                ->name('export.underemployed');

            Route::get('/doctors', 'DoctorController@users')->name('doctors.index');
            Route::get('/doctors/contracts', 'DoctorController@contracts')->name('doctors.contract.index');
            Route::get('/doctors/contract/{doctor_id}', 'DoctorController@contract')->name('doctors.contract.create');
            Route::post('/doctors/contract/{doctor_id}', 'DoctorController@storeContract')->name('doctors.contract.store');

            Route::get('/doctor/export-doctors', 'DoctorController@exportUsers');
            Route::get('/doctor/export-inactive', 'DoctorController@exportUsersDiActive');
            Route::get('/doctor/export-status/{doctor_status}', 'DoctorController@exportUsersStatus');

            Route::get('/doctor/add', 'DoctorController@userAdd');
            Route::post('/doctor/add', 'DoctorController@ActionUserAdd');

            Route::get('/doctor/edit/{user}', 'DoctorController@userEdit')->name('doctor.edit');
            Route::post('/doctor/edit/{user}', 'DoctorController@ActionUserEdit');

            Route::get('/calenders', 'CalenderController@info');
            Route::get('/calender/delete/{id}', 'CalenderController@delete');

            Route::get('/calender/delete-all/{user}/{partner?}', 'CalenderController@DeleteAll')
                ->name('delete.all.calendars');

            Route::get('/calender/add', 'CalenderController@add')
                ->name('calendar.create');
            Route::post('/calender/add', 'CalenderController@Create');

        });

        Route::namespace('Bill')->group(function () {

            Route::prefix('bill/arzpaya')->group(function () {
                Route::get('/', 'ArzpayaController@index')
                    ->name('arzpaya.transactions.index');
            });


            Route::get('/bill/done/{event?}', 'BillController@done')->name('bill.done');
            Route::post('/bill/done', 'BillController@done')->name('bill.done.group');

            Route::get('/bill/charge', 'BillController@charge');
            Route::get('/bill/repo', 'RepoController@total');
			
            Route::get('/bill/reserves/edit/{bill}',
                'BillController@editTransactionReserve')->name('transactionReserve.edit');

            Route::put('bill/reserves/update/{transactionReserve}',
                'BillController@updateTransactionReserve')->name('transactionReserve.update');

            Route::get('/bill/reserves',
                'BillController@reserves')->name('transactionReserve.index');

            Route::get('/bill/doctors', 'BillController@doctors');
            Route::post('/bill/doctors', 'BillController@ActionDoctor');

            Route::get('/bill/doctor/recent', 'DrBillController@recent');
            Route::get('/bill/doctor/total', 'DrBillController@total');
            Route::get('/total/export', 'DrBillController@exportTotal')->name('export.bill.total');
            Route::get('/bill/doctor/walletExport', 'DrBillController@walletExport')->name('export.bill.wallet');
            Route::get('/bill/doctor/wallet', 'DrBillController@wallet')->name('doctor.wallet.list');
            Route::get('/bill/doctor/cod', 'DrBillController@cod')->name('doctor.cod.list');
            Route::get('/bill/doctor/wallet/transactions', 'DrBillController@walletTransactions')->name('doctor.wallet.transactions');
            Route::get('/bill/doctor/wallet/overview', 'DrBillController@walletOverview')->name('doctor.wallet.overview');
            Route::get('/bill/doctor/cod/overview', 'DrBillController@codOverview')->name('doctor.cod.overview');
            Route::get('/bill/doctor/wallet/transactions/create', 'DrBillController@createWalletTransactions')->name('doctor.wallet.create.transactions');
            Route::post('/bill/doctor/wallet/transactions/create', 'DrBillController@storeWalletTransactions')->name('doctor.wallet.store.transactions');
            Route::get('/bill/doctor/walletPaymentConfirm/{wallet}', 'DrBillController@showWallet')->name('doctor.wallet.confirmPay');
            Route::get('/bill/doctor/walletPay/{wallet}', 'DrBillController@payWallet')->name('doctor.wallet.pay');
            Route::get('/bill/doctor/walletCancel/{wallet}', 'DrBillController@cancelWallet')->name('doctor.wallet.cancel');
            Route::post('/bill/doctor/walletPaymentConfirm/{wallet}', 'DrBillController@updateWallet');
            Route::get('/bill/doctor/partners', 'DrBillController@partners');
            Route::get('/bill/doctor/{status_list}', 'DrBillController@list');

            Route::get('/bill/pdf', 'DrBillController@pdfInvoice');

            Route::get('/bill/doctor/pay/{event_id}', 'DrBillController@pay');

            Route::get('/bill/doctor/pay/{event_id}', 'DrBillController@pay');

            Route::get('/export', 'DrBillController@export')->name('export.bill.list');


        });

        Route::prefix('doctor')->namespace('Doctor')->group(function () {

            Route::get('/doctors/underemployed', 'DoctorController@underemployed')
                ->name('doctors.underemployed');

            Route::get('/changing-approve/{user}', 'DoctorController@ChangingApprove')
                ->name('doctor.change.approve');
            Route::get('/report/{doctor}', 'DoctorController@report')->name('doctor.report');

            Route::prefix('tags')->group(function () {
                Route::get('/{doctor}', 'TagController@index')->name('tag.index');
                Route::post('/{doctor}', 'TagController@update')->name('tag.update');
            });

            Route::prefix('information')->group(function () {
                Route::get('/', 'DoctorInformationController@index')
                    ->name('doctor.information.index');
                Route::get('/create/{user}', 'DoctorInformationController@create')
                    ->name('doctor.information.create');
                Route::post('/store/{user}', 'DoctorInformationController@store')
                    ->name('doctor.information.store');
            });

            Route::prefix('details')->group(function () {
                Route::get('/create/{id}', 'DoctorDetailController@create')
                    ->name('doctor.detail.create');
                Route::post('/create/{id}', 'DoctorDetailController@store')
                    ->name('doctor.detail.store');
            });

            Route::prefix('specialization')->group(function () {
                Route::get('/index', 'SpecializationController@index')
                    ->name('specialization.index');

                Route::get('/edit/{sp}', 'SpecializationController@edit')
                    ->name('specialization.edit');

                Route::post('/edit/{sp}', 'SpecializationController@update')
                    ->name('specialization.update');

                Route::get('/delete/{sp}', 'SpecializationController@destroy')
                    ->name('specialization.destroy');

                Route::get('create', 'SpecializationController@create')
                    ->name('specialization.create');

                Route::post('store',
                    'SpecializationController@store')
                    ->name('specialization.store');

                Route::post('assign-doctor-specializations',
                    'DoctorController@assignSpecialization')
                    ->name('assign.doctor.specializations');
            });
            Route::prefix('skill')->group(function () {
                Route::get('/index', 'SkillController@index')
                    ->name('skill.index');

                Route::get('/edit/{skill}', 'SkillController@edit')
                    ->name('skill.edit');

                Route::post('/edit/{skill}', 'SkillController@update')
                    ->name('skill.update');

                Route::get('/delete/{skill}', 'SkillController@destroy')
                    ->name('skill.destroy');

                Route::get('create', 'SkillController@create')
                    ->name('skill.create');

                Route::post('store',
                    'SkillController@store')
                    ->name('skill.store');

                Route::post('assign-doctor-skills',
                    'DoctorController@assignSkill')
                    ->name('assign.doctor.skills');
            });

        });

        Route::prefix('visit')->group(function () {
            Route::namespace('Visit')->group(function () {
                Route::get('/actions', 'VisitActionController@index')
                    ->name('visit.action');
                Route::get('/actions/decision/{action}', 'VisitActionController@decision')
                    ->name('visit.action.decision');
                Route::post('/actions/decision/{action}', 'VisitActionController@update')
                    ->name('visit.action.update');
            });

            Route::get('/send-patient-in-room/{event}', 'VisitController@sendPatientInroom')
                ->name('admin.sent.patient.sms');
            Route::get('/manage', 'VisitController@manage')
                ->name('manage.visits');

            Route::get('/logs/{event}', 'VisitController@logs')
                ->name('visit.logs');
            Route::get('/list', 'VisitController@listOfVisits')
                ->name('list.of.visits');

            Route::get('/open-again/{event}', 'VisitController@OpenAgain')
                ->name('visit.open.again');

            Route::get('/cancel-refund/{event}', 'VisitController@openRefund')
                ->name('visit.cancel_refund');

            Route::get('/finished-open-again/{event}', 'VisitController@FinishedOpenAgain')
                ->name('finished.visit.open.again');

            Route::get('/cancel/{event}', 'VisitController@cancel')
                ->name('visit.cancel.admin');

            Route::get('/absences', 'VisitController@listsOfAbsences')
                ->name('absence.of.doctor');

            Route::post('/refund-absence/{user}/{er}', 'VisitController@refund')
                ->name('absence.refund');

            Route::get('/cancel-refund/{er}', 'VisitController@cancelRefund')
                ->name('cancel.refund');

            Route::get('/refund/{user}/{er}', 'VisitController@FullyCanceled')
                ->name('admin.visit.refund');

            Route::get('/finish/{event}', 'VisitController@finish')
                ->name('admin.finish.visit');

            Route::prefix('rates')->namespace('Visit')->group(function () {
                Route::get('/', 'RateController@index')->name('visit.rate.index');
            });
        });

        Route::prefix('transfer-money')->namespace('MoneyTransfer')->group(function () {
            Route::get('/user/{user}', 'TransferController@initialize')->name('user.initialize.money');
            Route::post('/user/{user}', 'TransferController@transfer')->name('user.transfer.money');
        });

        Route::prefix('secretary')->namespace('Secretary')->group(function () {
            Route::get('/create/{user}', 'SpecialSecretaryController@create')->name('secretary.create');
            Route::post('/store/{user}', 'SpecialSecretaryController@store')->name('secretary.store');
            Route::get('/edit/{user}', 'SpecialSecretaryController@edit')->name('secretary.edit');
            Route::post('/update/{id}', 'SpecialSecretaryController@update')->name('secretary.update');
        });

        Route::prefix('waiting-people')->namespace('Waiting')->group(function () {
            Route::get('/', 'WaitingPeopleController@index')
                ->name('waiting.index');

            Route::get('/details/{id}', 'WaitingPeopleController@details')
                ->name('waiting.details');

            Route::get('/details/{id}/export', 'WaitingPeopleController@DetailExport')
                ->name('waiting.details.export');

            Route::get('/specialization/details/export', 'WaitingPeopleController@SpecializationDetailExport')
                ->name('waiting.specialization.details.export');

            Route::get('/send-manual-sms/{doctor}', 'WaitingPeopleController@ManualSendSms')
                ->name('manual.send.sms');

            Route::get('/export', 'WaitingPeopleController@export')->name('waiting.export');
        });

        Route::prefix('advertising')->namespace('Advertising')->group(function (){
            Route::get('/index', 'AdvertisingController@index')->name('advertising.index');
            Route::get('/edit/{ad}', 'AdvertisingController@edit')->name('advertising.edit');
            Route::post('/update/{ad}', 'AdvertisingController@update')->name('advertising.update');
            Route::get('/payment-form', 'AdvertisingController@paymentForm')->name('advertising.paymentForm');
            Route::post('/payment-form', 'AdvertisingController@submitPaymentForm')->name('advertising.submitPaymentForm');
        });

        Route::prefix('ACL')->namespace('ACL')->group(function () {
            Route::prefix('panel-users')->namespace('PanelUsers')->group(function () {

                Route::get('/index', 'PanelUserController@index')
                    ->name('panel.user.index');
                Route::get('/create', 'PanelUserController@create')
                    ->name('panel.user.create');
                Route::post('/store', 'PanelUserController@store')
                    ->name('panel.user.store');
                Route::get('/{user}/roles', 'PanelUserController@roles')
                    ->name('user.roles');
                Route::post('/{user}/roles', 'PanelUserController@AssignRoles');
            });

            Route::prefix('permission')->group(function () {
                Route::get('/', 'PermissionController@index')
                    ->name('permission.index');
                Route::post('/store', 'PermissionController@store')
                    ->name('permissions.store');
            });
            Route::prefix('roles')->group(function () {
                Route::get('/', 'RoleController@index')
                    ->name('role.index');
                Route::post('/store', 'RoleController@store')
                    ->name('role.store');
                Route::get('/{role}/permissions', 'RoleController@permissions')
                    ->name('role.permissions.index');
                Route::post('/{role}/permissions', 'RoleController@AssignPermissions')
                    ->name('role.permissions.store');
            });
        });

        Route::namespace('Partner')->group(function () {
            Route::prefix('partner')->group(function () {

                Route::get('/registration-request', 'RegistrationRequestController@index')
                    ->name('registration-request.index');

                Route::put('/registration-request/update/{registrationRequest}',
                    'RegistrationRequestController@update')->name('registration-request.update');

                Route::get('/registration-request/delete/{id}',
                    'RegistrationRequestController@destroy')->name('registration-request.destroy');

                Route::get('/registration-request/edit/{id}',
                    'RegistrationRequestController@edit')->name('registration-request.edit');


                Route::get('/batch-calendar/{partner}', 'PartnerController@batchCalendar')
                    ->name('partner.batch.calendar');

                Route::post('/batch-calendar/{partner}', 'PartnerController@storeBatchCalendar');

                Route::get('/batch-finish/{partner}', 'PartnerController@batchFinish')
                    ->name('partner.batch.finish');

                Route::post('/batch-finish/{partner}', 'PartnerController@batchFinishStore');

                Route::get('/index', 'PartnerController@index')
                    ->name('partner.index');

                Route::get('/create', 'PartnerController@create')
                    ->name('partner.create');

                Route::get('/edit/{partner}', 'PartnerController@edit')
                    ->name('partner.edit');

                Route::post('/edit/{partner}', 'PartnerController@update')
                    ->name('partner.update');

                Route::post('/store', 'PartnerController@store')
                    ->name('partner.store');

                Route::get('/doctors/{partner}', 'CalendersController@doctors')
                    ->name('partner.doctors');
            });

            Route::prefix('insurance')->group(function () {
                Route::get('/create', 'InsuranceController@create')
                    ->name('insurance.create');
                Route::post('/store', 'InsuranceController@store')
                    ->name('insurance.store');
            });

            Route::prefix('service')->group(function () {
                Route::get('/create', 'ServiceController@create')
                    ->name('service.create');
                Route::post('/store', 'ServiceController@store')
                    ->name('service.store');
            });

        });

        // /cp-manager/log-viewer
        Route::get('log-viewer', [Rap2hpoutre\LaravelLogViewer\LogViewerController::class, 'index']);
    });

    Route::get('/insert-excel-into-mysql', 'ImportController@index');
    Route::post('/insert-excel-into-mysql', 'ImportController@store');

    Route::get('/insert-comment-into-mysql', 'ImportController@CommentIndex');
    Route::post('/insert-comment-into-mysql', 'ImportController@CommentStore');

    Route::prefix('sitemap')->namespace('Site')->group(function () {
        Route::get('/', 'SitemapController@index');
        Route::post('/make', 'SitemapController@make')->name('sitemap.make');
        Route::get('/download/{id}', 'SitemapController@download')->name('sitemap.download');

        Route::get('/reindex', function () {
            return view('admin.Sitemap.reindex');
        });
        Route::post('/reindex', 'SitemapController@reindex');
    });
});


Route::prefix('cp-partner')->namespace('PartnerAdmin')->group(function () {

    Route::get('/', 'AdminController@home')->name('cp.index');
    Route::get('/login', 'AdminController@login');
    Route::post('/login', 'AdminController@ActionLogin');

    Route::get('/logout', 'AdminController@logout');

    Route::middleware('partner')->group(function () {

        Route::get('/dashboard', 'AdminController@dashboard');

        Route::get('/doctors', 'AdminController@doctors');

        Route::get('/bill/transactions', 'CalenderController@transactions');

        Route::get('/bill/export', 'CalenderController@export')->name('export.partner.bill.list');

        Route::get('/bill/{status_list}', 'CalenderController@bill');

        Route::get('/calenders', 'CalenderController@info');

        Route::get('/calender/delete-all/{user}/{partner?}', 'CalenderController@DeleteAll')
            ->name('partner.delete.all.calendars');

        Route::get('/calender/delete/{id}', 'CalenderController@delete');

        Route::get('/calender/add', 'CalenderController@add')
            ->name('partner.calendar.create');

        Route::post('/calender/add', 'CalenderController@Create');

    });

});

Route::prefix('cp-portal')->namespace('PortalAdmin')->group(function (){
    Route::get('/', 'AdminController@home')->name('cp.index');
    Route::get('/login', 'AdminController@login');
    Route::post('/login', 'AdminController@ActionLogin');

    Route::get('/logout', 'AdminController@logout');
    Route::get('/dashboard', 'AdminController@dashboard');
    Route::get('/transactions', 'AdminController@transactions');
    Route::get('/walletPaymentConfirm/{wallet}', 'AdminController@showWallet')->name('digital.wallet.confirmPay');
    Route::post('/walletPayment/{wallet}', 'AdminController@updateWallet');

});


//Route::get('test-w', function (){
 //   $result = (new PayStarWallet())->pay(10000, 'IR320170000000115850654000', '', '');
 //   dd($result);
//});






