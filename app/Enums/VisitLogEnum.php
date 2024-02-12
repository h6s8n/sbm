<?php


namespace App\Enums;


abstract  class VisitLogEnum
{
    const AbsenceOfDoctor = 1;
    const RejectRefundRequest = 2;
    const Reactivating = 3;
    const CancelByAdmin = 4;
    const FinishByDoctor = 5;
    const CancelByDoctor = 6;
    const RefundRequest = 7;
    const DoctorEnter = 8;
    const DoctorExit = 9;
    const PatientEnter = 10;
    const ReactivateFinishedVisit = 11;
    const SafeCall = 12;
    const CancellationVisit = 13;
    const FinishByAdmin = 14;
    const AdminCreateCall = 15;
    const PatientInRoom = 16;
    const VideoCall = 17;
    const AdminPatientInRoom = 18;
    const OpenRefund = 19;
    const BellMeet = 20;



    static function Message($id)
    {
        $data =[
            1=>'User credit has been increased by admin due to absence of doctor',
            2=>'Visit has been reactivated by admin due to rejection of refund request',
            3=>'Canceled Visit has been reactivated by admin',
            4=>'Visit has been canceled by admin',
            5=>'Visit has been finished by doctor',
            6=>'Visit has been canceled by doctor',
            7=>'Refund request has been created',
            8=>'Doctor joined the room',
            9=>'Doctor left the room',
            10=>'Patient joined the room',
            11=>'Finished Visit has been reactivated by admin',
            12=>'Safe call request has been sent by doctor',
            13=>'User credit has been increased by admin due to cancellation of doctor',
            14 => 'Visit has been finished by admin',
            15=>'Safe call has been connected by Admin',
            16=>'Patient wants to send SMS to doctor',
            17=>'Video call request has been sent by doctor',
            18=>'Patient in room SMS has been sent by Admin',
            19=>'Reopen Canceled (Refunded) Visit By Admin',
            20=>'Video call request has been sent by doctor',

        ];
        return $data[$id];
    }
}
