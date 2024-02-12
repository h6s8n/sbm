<ul class="nav" id="side-menu">

    <li><a href="{{ url('cp-partner/dashboard') }}" class="waves-effect"><i class="icon-home" data-icon="v"></i> <span
                class="hide-menu"> داشبورد </span> </a></li>


<!--    <li>
        <a href="{{ url('cp-partner/doctors') }}" class="waves-effect">
            <i class="icon-user-following" data-icon="v"></i><span class="hide-menu"> پزشکان <span class="fa arrow"></span></span>
        </a>
        <ul class="nav nav-second-level">
            <li><a href="{{ url('cp-partner/doctors') }}">نمایش همه پزشکان</a></li>
            <li><a href="{{ url('cp-partner/doctor/add') }}">افزودن پزشک</a></li>
        </ul>
    </li>-->


    <li><a href="{{ url('cp-partner/doctors') }}" class="waves-effect"><i class="icon-user-following" data-icon="v"></i>
            <span
                class="hide-menu"> پزشکان </span> </a></li>

{{--    <li><a href="{{ url('cp-partner/calenders') }}" class="waves-effect"><i class="icon-wallet" data-icon="v"></i>--}}
{{--            <span--}}
{{--                class="hide-menu"> برنامه پزشکان </span> </a></li>--}}


    <li><a href="{{ url('cp-partner/bill/no_end') }}" class="waves-effect"><i class="icon-list" data-icon="v"></i> <span
                class="hide-menu"> مدیریت ویزیت ها </span> </a></li>

{{--    <li><a href="{{ url('cp-partner/bill/transactions') }}" class="waves-effect"><i class="icon-printer" data-icon="v"></i> <span--}}
{{--                class="hide-menu"> صورت حساب مالی </span> </a></li>--}}
    <?php

    $user = auth()->user();
    $partner = \App\Model\Partners\Partner::where('support_id', $user->id)->orderBy('id','ASC')->first();

    ?>

    <li><a target="_blank" href="{{ 'https://sbm24.com/h/'. $partner->slug }}" class="waves-effect"><i class="icon-link" data-icon="v"></i> <span
                class="hide-menu"> مشاهده صفحه اختصاصی </span> </a></li>

    <li role="separator" class="divider"></li>
    <li><a href="{{ url('cp-partner/logout') }}"><i class="fa fa-power-off"></i> خروج از حساب کاربری</a></li>

</ul>
