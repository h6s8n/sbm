<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'sbm24') }}</title>

    <!-- Bootstrap Core CSS -->
    <link href="{!! asset('assets/adminui/bootstrap/dist/css/bootstrap.min.css') !!}" rel="stylesheet">
    <link href="{!! asset('assets/adminui/bootstrap/dist/css/bootstrap.rtl.min.css') !!}" rel="stylesheet">
    <!-- animation CSS -->
    <link href="{!! asset('assets/adminui/assets/css/animate.css') !!}" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="{!! asset('assets/adminui/assets/css/style.css') !!}" rel="stylesheet">
    <!-- color CSS -->
    <link href="{!! asset('assets/adminui/assets/css/colors/default.css') !!}" id="theme"  rel="stylesheet">
    <link href="{!! asset('assets/adminui/assets/css/zm_style.css') !!}" id="theme"  rel="stylesheet">

    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
    <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->

</head>
<body>
<!-- Preloader -->
<div class="preloader">
    <div class="cssload-speeding-wheel"></div>
</div>
<section id="wrapper" class="login-register" style="background: url({{asset('statics-public/353.jpg')}}) no-repeat center center / cover !important;">
    <div class="login-box">
        <div class="login-white-box">
            <form class="form-horizontal form-material" id="loginform" method="post">
                <h3 class="box-title m-b-20">ورود به سامانه .</h3>
                <input name="_token" type="hidden" value="{{ csrf_token() }}">
                <div class="error clearfix">
                    @if(Session::has('error'))
                        <div class="alert alert-danger">{{ Session::get('error') }}</div>
                    @endif
                </div>
                <div class="clearfix"></div>
                <div class="form-group ">
                    <div class="col-xs-12">
                        <input class="form-control" type="text" placeholder="ایمیل" name="email" autocomplete="off" autofocus>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-xs-12">
                        <input class="form-control" type="password" placeholder="رمز عبور" name="password">
                    </div>
                </div>
                <div class="form-group text-center m-t-20">
                    <div class="col-xs-12">
                        <button class="btn btn-info btn-lg btn-block text-uppercase waves-effect waves-light" type="submit">ورود</button>
                    </div>
                </div>

            </form>
        </div>
    </div>
</section>
<!-- jQuery -->
<script src="{!! asset('assets/adminui/plugins/bower_components/jquery/dist/jquery.min.js') !!}"></script>
<!-- Bootstrap Core JavaScript -->
<script src="{!! asset('assets/adminui/bootstrap/dist/js/bootstrap.min.js') !!}"></script>
<!-- Menu Plugin JavaScript -->
<script src="{!! asset('assets/adminui/plugins/bower_components/sidebar-nav/dist/sidebar-nav.min.js') !!}"></script>

<!--slimscroll JavaScript -->
<script src="{!! asset('assets/adminui/assets/js/jquery.slimscroll.js') !!}"></script>
<!--Wave Effects -->
<script src="{!! asset('assets/adminui/assets/js/waves.js') !!}"></script>
<!-- Custom Theme JavaScript -->
<script src="{!! asset('assets/adminui/assets/js/custom.js') !!}"></script>
<!--Style Switcher -->
<script src="{!! asset('assets/adminui/plugins/bower_components/styleswitcher/jQuery.style.switcher.js') !!}"></script>
</body>

</html>
