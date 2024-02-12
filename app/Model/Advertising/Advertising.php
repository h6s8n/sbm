<?php

namespace App\Model\Advertising;

use Illuminate\Database\Eloquent\Model;

class Advertising extends Model
{
    protected $table = 'advertising';
    protected $guarded=['id'];

    public function getPlanAttribute($plan)
    {
        switch ($plan) {
            case 'patient':
                $plan = 'داشبورد بیمار';
                break;
            case 'doctor':
                $plan = 'داشبورد پزشک';
                break;
            case 'specialties':
                $plan = 'تخصص ها';
                break;
            case 'vaccine_centers':
                $plan = 'مراکز واکسن کرونا';
                break;
            case 'test_centers':
                $plan = 'مراکز تست کرونا';
                break;
        }
        return $plan;
    }

    public function getStatusAttribute($status)
    {
        switch ($status) {
            case 'pending':
                $status = 'در انتظار بررسی';
                break;
            case 'active':
                $status = 'ثبت شده';
                break;
            default:
                $status = 'در انتظار بررسی';
        }
        return $status;
    }

    public function getPaymentStatusAttribute($status)
    {
        switch ($status) {
            case 'pending':
                $status = 'در انتظار پرداخت';
                break;
            case 'paid':
                $status = 'پرداخت شده';
                break;
            default:
                $status = 'در انتظار پرداخت';
        }
        return $status;
    }

    public function getCreatedAtAttribute($date)
    {
        return jdate('Y/m/d ساعت H:i:s', strtotime($date));
    }

    public function getPaidAtAttribute($date)
    {
        return $date ? jdate('Y/m/d ساعت H:i:s', strtotime($date)) : '-';
    }
}
