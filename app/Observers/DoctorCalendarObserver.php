<?php

namespace App\Observers;

class DoctorCalendarObserver
{
    public function saving($doctor_calender)
    {
		if (auth()->user())
        	if (auth()->user()->approve == 1 || auth()->user()->approve == 10)
           		 $doctor_calender->created_user_id = auth()->id();
		else
			$doctor_calender->created_user_id = $doctor_calender->created_user_id;
    }
}
