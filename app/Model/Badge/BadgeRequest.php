<?php

namespace App\Model\Badge;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BadgeRequest extends Model
{
    protected $fillable = ['full_name','plan','phone','user_id','badge_id','token','status'];
    public function getPlanAttribute($plan)
    {
        switch ($plan) {
            case 'Bronze':
                $plan = 'برنزی';
                break;
            case 'Silver':
                $plan = 'نقره ای';
                break;
            case 'Gold':
                $plan = 'طلایی';
                break;
            default:
                $plan = 'برنزی';
        }
        return $plan;
    }

    public function getStatusAttribute($status)
    {
        switch ($status) {
            case 'PENDING':
                $status = 'در انتظار بررسی';
                break;
            case 'REGISTERED':
                $status = 'ثبت شده';
                break;
            default:
                $status = 'در انتظار بررسی';
        }
        return $status;
    }

    public function getPayStatusAttribute($status)
    {
        switch ($status) {
            case 'PENDING':
                $status = 'در انتظار پرداخت';
                break;
            case 'PAYED':
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
}
