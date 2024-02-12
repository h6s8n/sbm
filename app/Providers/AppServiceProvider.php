<?php

namespace App\Providers;

use App\Http\Controllers\Api\v2\User\DoctorController;
use App\Model\Doctor\Specialization;
use App\Model\Visit\DoctorCalender;
use App\Observers\DoctorCalendarObserver;
use App\Repositories\v1\Doctor\Specialization\SpecializationInterface;
use App\Repositories\v1\Doctor\Specialization\SpecializationRepository;
use App\Repositories\v1\MoneyTransfer\GateWayInterface;
use App\Repositories\v1\MoneyTransfer\PayPing;
use App\Repositories\v1\Rate\RateInterface;
use App\Repositories\v1\Rate\RateRepository;
use App\Repositories\v2\Doctor\DoctorInterface;
use App\Repositories\v2\Doctor\DoctorRepository;
use App\Repositories\v2\File\FileInterface;
use App\Repositories\v2\File\FileRepository;
use App\Repositories\v2\Logs\UserActivity\UserActivityLogInterface;
use App\Repositories\v2\Logs\UserActivity\UserActivityLogRepository;
use App\Repositories\v2\Profile\Doctor\ProfileInterface;
use App\Repositories\v2\Profile\Doctor\ProfileRepository;
use App\Repositories\v2\ShortMessageService\ShortMessageInterface;
use App\Repositories\v2\ShortMessageService\ShortMessageServiceRepository;
use App\Repositories\v2\User\UserInterface;
use App\Repositories\v2\User\UserRepository;
use App\Repositories\v2\Visit\VisitLogInterface;
use App\Repositories\v2\Visit\VisitLogRepository;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {


        /**
         * Paginate a standard Laravel Collection.
         *
         * @param int $perPage
         * @param int $total
         * @param int $page
         * @param string $pageName
         * @return array
         */
        Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

            return new LengthAwarePaginator(
                $this->forPage($page, $perPage),
                $total ?: $this->count(),
                $perPage,
                $page,
                [
                    'path' => LengthAwarePaginator::resolveCurrentPath(),
                    'pageName' => $pageName,
                ]
            );
        });




        //
        Schema::defaultStringLength(191);

        $this->app->bind(
            RateInterface::class,
            RateRepository::class
        );
        $this->app->bind(
            SpecializationInterface::class,
            SpecializationRepository::class
        );
        $this->app->bind(UserInterface::class,
            UserRepository::class);
        $this->app->bind(ShortMessageInterface::class,
            ShortMessageServiceRepository::class);
        $this->app->bind(DoctorInterface::class,
            DoctorRepository::class);
        $this->app->bind(FileInterface::class,
            FileRepository::class);
        $this->app->bind(ProfileInterface::class,
            ProfileRepository::class);
        $this->app->bind(VisitLogInterface::class,
            VisitLogRepository::class);
        $this->app->bind(UserActivityLogInterface::class,
            UserActivityLogRepository::class);
//        $this->app->bind(
//            GateWayInterface::class,
//            PayPing::class
//        );
        DoctorCalender::observe(DoctorCalendarObserver::class);
    }
}
