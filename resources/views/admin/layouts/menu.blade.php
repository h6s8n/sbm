<ul class="nav" id="side-menu">

    <li><a href="{{ url('cp-manager/dashboard') }}" class="waves-effect"><i class="icon-home" data-icon="v"></i> <span
                class="hide-menu"> داشبورد </span> </a></li>

    @can('manage')
        <li><a href="{{ url('cp-manager/ACL') }}" class="waves-effect"><i class="icon-magnet" data-icon="v"></i> <span
                    class="hide-menu"> مدیریت <span class="fa arrow"></span></span> </a>
            <ul class="nav nav-second-level">
                <li><a href="{{ route('panel.user.create') }}">ثبت کاربر پنل</a></li>
                <li><a href="{{ route('panel.user.index') }}">مشاهده کاربر پنل</a></li>
                <li><a href="{{ route('permission.index') }}">دسترسی ها</a></li>
                <li><a href="{{ route('role.index') }}">نقش ها</a></li>
            </ul>
        </li>
    @endcan
    @can('users')
        <li><a href="{{ url('cp-manager/users') }}" class="waves-effect"><i class="icon-user" data-icon="v"></i> <span
                    class="hide-menu"> کاربران <span class="fa arrow"></span></span> </a>
            <ul class="nav nav-second-level">
                @can('users-index')
                    <li><a href="{{ url('cp-manager/users') }}">نمایش همه کاربر</a></li>
                @endcan
                @can('users-store')
                    <li><a href="{{ url('cp-manager/user/add') }}">افزودن کاربر</a></li>
                @endcan
                    <li><a href="{{route('refund.index')}}">درخواست برداشت وجه</a></li>
            </ul>
        </li>
    @endcan
    @can('visits')
        <li><a href="{{ url('cp-manager/visit') }}" class="waves-effect"><i class="icon-list" data-icon="v"></i> <span
                    class="hide-menu">ویزیت ها <span class="fa arrow"></span></span> </a>
            <ul class="nav nav-second-level">
                @can('visit-management')
                    <li><a href="{{route('list.of.visits')}}">مدیریت ویزیت ها</a></li>
                @endcan
                @can('visit-rates')
                    <li><a href="{{route('visit.rate.index')}}">امتیاز ویزیت ها</a></li>
                @endcan
{{--                @can('visit-absence')--}}
{{--                    <li><a href="{{ route('absence.of.doctor') }}">پزشکان غایب ویزیت ها</a></li>--}}
{{--                @endcan--}}
                    <li><a href="{{ route('manage.visits') }}">مدیریت ورود و خروج</a></li>
                    <li><a href="{{ route('visit.action') }}"> درخواست ها </a></li>
            </ul>
        </li>
    @endcan
    @can('doctors')
        <li><a href="{{ url('cp-manager/doctors') }}" class="waves-effect"><i class="icon-user-following"
                                                                              data-icon="v"></i>
                <span class="hide-menu"> پزشکان <span class="fa arrow"></span></span> </a>
            <ul class="nav nav-second-level">
                @can('doctors-index')
                    <li><a href="{{ url('cp-manager/doctors') }}">نمایش همه پزشکان</a></li>
                @endcan
                @can('doctors-store')
                    <li><a href="{{ url('cp-manager/doctor/add') }}">افزودن پزشک</a></li>
                @endcan
                @can('doctor-information')
                    <li><a href="{{ route('doctor.information.index')}}">اطلاعات پزشکان</a></li>
                @endcan
                @can('doctor-information')
                    <li><a href="{{ route('doctors.contract.index')}}">قرارداد پزشکان</a></li>
                @endcan
            </ul>
        </li>
    @endcan

        <li><a href="{{ url('cp-manager/doctors') }}" class="waves-effect"><i class="icon-money"
                                                                              data-icon="v"></i>
                <span class="hide-menu"> فروش <span class="fa arrow"></span></span> </a>
            <ul class="nav nav-second-level">
                <li><a href="{{ route('doctors.underemployed')}}">پزشکان کم کار</a></li>
                <li><a href="{{route('waiting.index')}}">افراد در انتظار </a></li>
{{--                <li><a href="{{route('doctors.top')}}">پزشکان فعال هفته </a></li>--}}
            </ul>

    @can('specializations')
        <li><a href="{{ url('cp-manager/specializations') }}" class="waves-effect"><i class="icon-magnet"
                                                                                      data-icon="v"></i>
                <span class="hide-menu"> تخصص ها <span class="fa arrow"></span></span> </a>
            <ul class="nav nav-second-level">
                @can('specializations-index')
                    <li><a href="{{ route('specialization.index') }}">لیست تخصص ها</a></li>
                @endcan
                @can('specializations-store')
                    <li><a href="{{ route('specialization.create') }}">افزودن تخصص جدید</a></li>
                @endcan
            </ul>
        </li>
        <li><a href="{{ url('cp-manager/skills') }}" class="waves-effect"><i class="icon-magnet"
                                                                                      data-icon="v"></i>
                <span class="hide-menu"> مهارت ها <span class="fa arrow"></span></span> </a>
            <ul class="nav nav-second-level">
                @can('specializations-index')
                    <li><a href="{{ route('skill.index') }}">لیست مهارت ها</a></li>
                @endcan
                @can('specializations-store')
                    <li><a href="{{ route('skill.create') }}">افزودن مهارت جدید</a></li>
                @endcan
            </ul>
        </li>
    @endcan
    <li><a href="{{ url('cp-manager/badges') }}" class="waves-effect"><i class="icon-badge"
                                                                                  data-icon="v"></i>
            <span class="hide-menu"> نشان ها <span class="fa arrow"></span></span> </a>
        <ul class="nav nav-second-level">
                <li><a href="{{ route('badge.index') }}">لیست نشان ها</a></li>
                <li><a href="{{ route('badge.create') }}">افزودن نشان جدید</a></li>
                <li><a href="{{ route('badge-requests.index') }}">درخواست ها</a></li>
        </ul>
    </li>

    <li><a href="{{ url('cp-manager/advertising') }}" class="waves-effect"><i class="icon-rocket"
                                                                         data-icon="v"></i>
            <span class="hide-menu"> تبلیغات <span class="fa arrow"></span></span> </a>
        <ul class="nav nav-second-level">
            <li><a href="{{ route('advertising.index') }}">درخواست ها</a></li>
            <li><a href="{{ route('advertising.paymentForm') }}">فرم پرداخت</a></li>
        </ul>
    </li>
    @can('calendars')
        <li><a href="{{ url('cp-manager/calenders') }}" class="waves-effect"><i class="icon-wallet" data-icon="v"></i>
                <span
                    class="hide-menu"> برنامه پزشکان </span> </a></li>
    @endcan
    <li><a href="{{ url('cp-manager/bill/charge') }}" class="waves-effect"><i class="icon-printer" data-icon="v"></i>
            <span class="hide-menu"> صورت حساب مالی <span class="fa arrow"></span></span> </a>
        <ul class="nav nav-second-level">
            <li><a href="{{ url('cp-manager/bill/repo') }}">گزارش مالی<span style="color: red ; font-size:10px"> نسخه آزمایشی </span></a></li>
			
            <li><a href="{{ url('cp-manager/bill/doctor/final') }}">پرداختی های پزشکان</a></li>
            <li><a href="{{ url('cp-manager/bill/doctor/no_end') }}">صورت حساب همه نوبت ها</a></li>
            <li><a href="{{ url('cp-manager/bill/charge') }}">صورت حساب افزایش اعتبار</a></li>
            <li><a href="{{ url('cp-manager/bill/doctor/partners') }}">صورت حساب بیمارستان ها</a></li>
            <li><a href="{{ route('advertising.index') }}">تبلیغات</a></li>
            <li><a href="{{ url('cp-manager/bill/doctor/transaction') }}">تراکنش ها</a></li>
{{--        @if(auth()->id() == 928 || auth()->id() == 341)--}}
            <li><a href="{{ url('cp-manager/bill/doctor/wallet') }}">درگاه سلامت
                    <span class="badge badge-danger">
                        {{ \App\Model\Wallet\DoctorWallet::where('status','pending_decrease')->
                        where('settlement_type','!=','rial')->count() }}
                    </span>
                </a></li>

                <li><a href="{{ url('cp-manager/bill/doctor/cod') }}">پرداخت در محل
                        <span class="badge badge-danger">
                        {{ \App\Model\Wallet\DoctorWallet::where(['status' => 'pending_decrease' , 'payment_type' => 'COD'])->
                        where('settlement_type','!=','rial')->count() }}
                    </span>
                    </a></li>
                <li><a href="{{ url('cp-manager/bill/doctor/total') }}">گزارش سرجمع</a></li>
            <li><a href="{{ url('cp-manager/bill/doctor/wallet/transactions') }}">واریزی به اپراتور درگاه سلامت</a></li>
            <li><a href="{{ url('cp-manager/bill/doctor/wallet/overview') }}">خلاصه وضعیت درگاه سلامت</a></li>
            <li><a href="{{ url('cp-manager/bill/doctor/cod/overview') }}">خلاصه وضعیت پرداخت در محل</a></li>
{{--            @endauth--}}
            <li><a href="{{ url('cp-manager/bill/reserves') }}">
                    تراکنش ها
                    <span class="badge badge-danger">
                    @php $count = \App\Model\Visit\TransactionReserve::join('users as doctors', 'doctors.id', '=', 'transaction_reserves.doctor_id')
                            ->join('users','users.id','transaction_reserves.user_id')
                            ->join('doctor_calenders', 'doctor_calenders.id', '=', 'transaction_reserves.calender_id')
                            ->where('transaction_reserves.status','pending')
                            ->whereDate('transaction_reserves.created_at', '>=', \Carbon\Carbon::now()->format('Y-m-d') )
                            ->whereNotIn('transaction_reserves.user_id',function ($q){
                            $q->select('tr.user_id')->from('transaction_reserves as tr')
                                ->where('tr.status', 'paid')
                                ->whereDate('tr.created_at', '=', \Carbon\Carbon::now()->format('Y-m-d') );
                            })->groupBy('transaction_reserves.user_id')->get() @endphp
                        {{ count($count) }}
                </span></a></li>
            <li><a href="{{route('arzpaya.transactions.index')}}">صورت حساب ارزپایا</a></li>
        </ul>
    </li>

    <li><a href="{{route('triage.index')}}" class="waves-effect"><i class="icon-basic-helm" data-icon="v"></i> <span
                class="hide-menu"> تریاژ <span class="badge badge-danger">
                    {{\App\Model\Triage::where('called',0)->count()}}
                </span><span class="fa arrow"></span></span> </a>
        <ul class="nav nav-second-level">
            <li><a href="{{route('triage.index',['called'=>0])}}">لیست درخواست ها</a></li>
            {{--            <li><a href="">تماس گرفته شده ها</a></li>--}}
        </ul>
    </li>
    <li><a href="{{route('partner.create')}}" class="waves-effect"><i class="icon-software-box-oval" data-icon="v"></i>
            <span class="hide-menu"> بیمارستان ها
                </span><span class="fa arrow"></span></a>
        <ul class="nav nav-second-level">
            <li><a href="{{route('partner.create')}}">ثبت بیمارستان جدید</a></li>
            <li><a href="{{route('partner.index')}}">لیست بیمارستان ها</a></li>
            <li><a href="{{route('insurance.create')}}">ثبت بیمه جدید</a></li>
            <li><a href="{{route('service.create')}}">ثبت سرویس جدید</a></li>
            <li><a href="{{route('registration-request.index')}}">درخواست های ثبت مراکز</a></li>

            {{--            <li><a href="">تماس گرفته شده ها</a></li>--}}
        </ul>
    </li>
    <li><a href="{{route('comment.index',['flag_status'=>0])}}" class="waves-effect"><i class="icon-note" data-icon="v"></i> <span
                class="hide-menu">نظرات سایت </span> </a></li>
    <li><a href="{{route('user.codes')}}" class="waves-effect"><i class="icon-login" data-icon="v"></i> <span
                class="hide-menu">کدهای ورود</span> </a></li>
    <li><a href="{{ url('cp-manager/setting') }}" class="waves-effect"><i class="icon-settings" data-icon="v"></i> <span
                class="hide-menu"> تنظیمات </span> </a></li>

    <li role="separator" class="divider"></li>
    <li><a href="{{ url('cp-manager/logout') }}"><i class="fa fa-power-off"></i> خروج از حساب کاربری</a></li>

</ul>
