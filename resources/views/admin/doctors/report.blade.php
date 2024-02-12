@extends('admin.layouts.app')

@section('page_name', 'ویرایش اطلاعات پزشک')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />
@section('content')

    <div class="white-box">

        <form method="post" enctype="multipart/form-data" action="" class="avatar" style="direction: rtl">
            {{ csrf_field() }}

            <div class="form-group row">
                <div class="col-md-12">
{{--                    <span>--}}
                        پزشک <strong>{{$doctor->fullname}}</strong>
                        در تاریخ <strong>{{jdate('d-m-Y',strtotime($doctor->created_at))}}</strong>
                        عضو مجموعه شده و هم اکنون  ایشان در حالت <strong>{{$doctor->doctor_status=='active' ? 'فعال ' : 'غیر فعال '}}</strong> می باشند.
                    این پزشک مجموعا تا الان <strong>{{$doctor->calenders()->count()}}</strong> وقت با مجموع ظرفیت <strong>{{$doctor->calenders()->sum('capacity')}}</strong> نفر
                    تعریف کرده اند که از این تعداد  <strong>{{$doctor->DoctorEvents()->where('visit_status','end')->count()}}</strong>
                    <strong>ویزیت موفق، {{$doctor->DoctorEvents()->where('visit_status','cancel')->count()}}</strong>
                    <strong>ویزیت کنسل شده، {{$doctor->DoctorEvents()->where('visit_status','not_end')->count()}}</strong>
                    <strong>ویزیت پایان نیافته، و {{$doctor->DoctorEvents()->where('visit_status','refunded')->count()}}</strong>
                    بازگشت وجه داشتند.
                    <p></p>
                    اولین وقت برای تاریخ <strong>{{$doctor->calenders()->orderBy('fa_data')->first() ? jdate('d-m-Y',strtotime($doctor->calenders()->orderBy('fa_data')->first()->data)) : 'ندارد'}}</strong>
                    به مبلغ <strong>{{$doctor->calenders()->orderBy('fa_data')->first() ? $doctor->calenders()->orderBy('fa_data')->first()->price : 0}}</strong>
                    ریال و آخرین وقت تنظیم شده توسط ایشان <strong>{{$doctor->calenders()->orderBy('fa_data','DESC')->first() ? jdate('d-m-Y' , strtotime($doctor->calenders()->orderBy('fa_data','DESC')->first()->data)) : 'ندارد'}}</strong>
                    به مبلغ <strong>{{$doctor->calenders()->orderBy('fa_data','DESC')->first() ? $doctor->calenders()->orderBy('fa_data','DESC')->first()->price : 0}}</strong>
                    ریال می باشد.
                    <p></p>
                    تخصص های اصلی دکتر <strong>@foreach($doctor->specializations()->get() as $sp) {{$sp->name.'،'}}@endforeach</strong>
                    می باشد.
                    شماره همراه ایشان <strong>{{$doctor->mobile}}</strong>
                     و شماره منشی مطب ایشان <strong>{{$doctor->information()->first() ? ($doctor->information()->first()->office_secretary_mobile ? $doctor->information()->first()->office_secretary_mobile
 : 'وارد نشده') : 'وارد نشده'}}</strong> به نام <strong>{{$doctor->information()->first() ? ($doctor->information()->first()->office_secretary_name ? $doctor->information()->first()->office_secretary_name
 : 'وارد نشده') : 'وارد نشده'}}</strong> میباشد.
                    <p></p>
                    آدرس مطب: <strong>{{$doctor->address}}</strong>
                    <p></p>
                    لینک ایشان در سایت: <strong><a href="{{'https://sbm24.com/'.$doctor->username}}">{{'https://sbm24.com/'.$doctor->username}}</a></strong>
{{--                    </span>--}}
                </div>
            </div>

            <div class="clearfix"></div>
        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>

        $(document).ready(function () {
            $('.js-example-basic-multiple').select2();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            $(document).delegate('#state', 'change', function (e) {
                e.preventDefault();

                var liveitem = $(this);
                var liveId = $(this).val();
                $('.area').css('display', 'none');

                $('#city').html('<option value="">لطفا انتخاب کنید</option>');

                $.ajax({
                    method: 'POST',
                    url: '{{ url('cp-manager/city') }}',
                    data: {state : liveId}
                }).done(function(result){

                    if(result.status == 'success'){
                        result.data.forEach(function(element) {
                            $('#city').append('<option value="'+element['id']+'">'+element['city']+'</option>');
                        });

                    }

                });

            });

            $(document).delegate('#city', 'change', function (e) {
                e.preventDefault();

                var liveitem = $(this);
                var liveId = $(this).val();

                if(liveId === 'تهران'){
                    $('.area').css('display', 'block');
                }else{
                    $('.area').css('display', 'none');
                }

            });
        })
    </script>

@endsection
