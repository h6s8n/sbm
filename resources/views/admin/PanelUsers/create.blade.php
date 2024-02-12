@extends('admin.layouts.app')

@section('page_name', ' افزودن کاربر پنل')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('panel.user.store')}}" enctype="multipart/form-data" class="avatar" style="direction: rtl">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">نام	</label>

                <div class="col-md-6">
                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" autofocus>

                    @if ($errors->has('name'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="family" class="col-md-4 col-form-label text-md-right">نام خانوادگی</label>

                <div class="col-md-6">
                    <input id="family" type="text" class="form-control{{ $errors->has('family') ? ' is-invalid' : '' }}" name="family" value="{{ old('family') }}">

                    @if ($errors->has('family'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('family') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="email" class="col-md-4 col-form-label text-md-right">ایمیل</label>

                <div class="col-md-6">
                    <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email">

                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>
            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right"> رمز عبور</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" >

                    @if ($errors->has('password'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
            </div>


            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت کاربر</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>
@endsection
