@extends('admin.layouts.app')

@section('page_name', 'بازگشت وجه')

@section('content')

    <div class="white-box">

        <form method="post" action="" enctype="multipart/form-data" class="avatar" style="direction: rtl">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="doctor_nickname" class="col-md-4 col-form-label text-md-right">عنوان پزشکی	</label>

                <div class="col-md-6">
                    <input id="doctor_nickname" type="text" class="form-control{{ $errors->has('doctor_nickname') ? ' is-invalid' : '' }}" name="doctor_nickname" value="{{ old('doctor_nickname') }}">

                    @if ($errors->has('doctor_nickname'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('doctor_nickname') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

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
                    <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" >

                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="mobile" class="col-md-4 col-form-label text-md-right">شماره موبایل</label>

                <div class="col-md-6">
                    <input id="mobile" type="text" class="form-control{{ $errors->has('mobile') ? ' is-invalid' : '' }}" name="mobile" value="{{ old('mobile') }}" >

                    @if ($errors->has('mobile'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('mobile') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="nationalcode" class="col-md-4 col-form-label text-md-right">کد ملی</label>

                <div class="col-md-6">
                    <input id="nationalcode" type="text" class="form-control{{ $errors->has('nationalcode') ? ' is-invalid' : '' }}" name="nationalcode" value="{{ old('nationalcode') }}" >

                    @if ($errors->has('nationalcode'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('nationalcode') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="sp_gp" class="col-md-4 col-form-label text-md-right">گروه پزشکی</label>

                <div class="col-md-6">

                    <select id="sp_gp" class="form-control{{ $errors->has('sp_gp') ? ' is-invalid' : '' }}" name="sp_gp">
                        <option value="">لطفا انتخاب کنید</option>
                        @foreach($specialties as $item)
                            <option value="{{ $item }}" {{ (old('sp_gp') == $item) ? 'selected' : '' }}>{{ $item }}</option>
                        @endforeach
                    </select>

                    @if ($errors->has('sp_gp'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('sp_gp') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="username" class="col-md-4 col-form-label text-md-right">عنوان صفحه اختصاصی (انگلیسی)</label>

                <div class="col-md-6">
                    <input id="username" type="text" class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username" value="{{ old('username') }}" >

                    @if ($errors->has('username'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="job_title" class="col-md-4 col-form-label text-md-right">عنوان تخصص روی تابلو</label>

                <div class="col-md-6">
                    <input id="job_title" type="text" class="form-control{{ $errors->has('job_title') ? ' is-invalid' : '' }}" name="job_title" value="{{ old('job_title') }}" >

                    @if ($errors->has('job_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('job_title') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="specialcode" class="col-md-4 col-form-label text-md-right">شماره نظام پزشکی یا نظام صنفی</label>

                <div class="col-md-6">
                    <input id="specialcode" type="text" class="form-control{{ $errors->has('specialcode') ? ' is-invalid' : '' }}" name="specialcode" value="{{ old('specialcode') }}" >

                    @if ($errors->has('specialcode'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('specialcode') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="account_number" class="col-md-4 col-form-label text-md-right">شماره حساب بانکی</label>

                <div class="col-md-6">
                    <input id="account_number" type="text" class="form-control{{ $errors->has('account_number') ? ' is-invalid' : '' }}" name="account_number" value="{{ old('account_number') }}" >

                    @if ($errors->has('account_number'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('account_number') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="account_sheba" class="col-md-4 col-form-label text-md-right">شماره شبا</label>

                <div class="col-md-6">
                    <input id="account_sheba" type="text" class="form-control{{ $errors->has('account_sheba') ? ' is-invalid' : '' }}" name="account_sheba" value="{{ old('account_sheba') }}" >

                    @if ($errors->has('account_sheba'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('account_sheba') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="picture" class="col-md-4 col-form-label text-md-right">تصویر پزشک</label>

                <div class="col-md-6">
                    <input id="picture" type="file" class="form-control{{ $errors->has('picture') ? ' is-invalid' : '' }}" name="picture" >

                    @if ($errors->has('picture'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('picture') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="passport_image" class="col-md-4 col-form-label text-md-right">تصویر شناسنامه یا پاسپورت</label>

                <div class="col-md-6">
                    <input id="passport_image" type="file" class="form-control{{ $errors->has('passport_image') ? ' is-invalid' : '' }}" name="passport_image" >

                    @if ($errors->has('passport_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('passport_image') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="national_cart_image" class="col-md-4 col-form-label text-md-right">تصویر کارت ملی</label>

                <div class="col-md-6">
                    <input id="national_cart_image" type="file" class="form-control{{ $errors->has('national_cart_image') ? ' is-invalid' : '' }}" name="national_cart_image" >

                    @if ($errors->has('national_cart_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('national_cart_image') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="special_cart_image" class="col-md-4 col-form-label text-md-right">تصویر کارت نظام پزشکی</label>

                <div class="col-md-6">
                    <input id="special_cart_image" type="file" class="form-control{{ $errors->has('special_cart_image') ? ' is-invalid' : '' }}" name="special_cart_image" >

                    @if ($errors->has('special_cart_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('special_cart_image') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="education_image" class="col-md-4 col-form-label text-md-right">تصویر مدرک تحصیلی یا پروانه مطب</label>

                <div class="col-md-6">
                    <input id="education_image" type="file" class="form-control{{ $errors->has('education_image') ? ' is-invalid' : '' }}" name="education_image" >

                    @if ($errors->has('education_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('education_image') }}</strong>
                        </span>
                    @endif
                </div>

            </div>


            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="status" class="col-md-4 col-form-label text-md-right">وضعیت</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="status_active" name="status" value="active" {{ ( old('status') == 'active') ? "checked" : "" }}>
                            <label for="status_active">فعال</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="status_inactive" name="status" value="inactive" {{ ( old('status') == 'inactive') ? "checked" : "" }}>
                            <label for="status_inactive">غیر فعال</label>
                        </div>
                    </div>
                    @if ($errors->has('status'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('status') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="doctor_status_active" class="col-md-4 col-form-label text-md-right">وضعیت پنل</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="doctor_status_active" name="doctor_status" value="active" {{ ( old('doctor_status') == 'active') ? "checked" : "" }}>
                            <label for="doctor_status_active">تایید</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="doctor_status_inactive" name="doctor_status" value="inactive" {{ ( old('doctor_status') == 'inactive') ? "checked" : "" }}>
                            <label for="doctor_status_inactive">معلق</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="doctor_status_failed" name="doctor_status" value="failed" {{ ( old('doctor_status') == 'failed') ? "checked" : "" }}>
                            <label for="doctor_status_failed">رد شده</label>
                        </div>
                    </div>
                    @if ($errors->has('status'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('status') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="status" class="col-md-4 col-form-label text-md-right">جنسیت</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="gender_active" name="gender" value="0" {{ ( old('gender') == '0') ? "checked" : "" }}>
                            <label for="gender_active">مرد</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="gender_inactive" name="gender" value="1" {{ ( old('gender') == '1') ? "checked" : "" }}>
                            <label for="gender_inactive">زن</label>
                        </div>
                    </div>
                    @if ($errors->has('gender'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('gender') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="state" class="col-md-4 col-form-label text-md-right">استان</label>

                <div class="col-md-6">
                    <select id="state" class="form-control{{ $errors->has('state') ? ' is-invalid' : '' }}" name="state">
                        <option value="">لطفا انتخاب کنید</option>
                        @if($province)
                            @foreach($province as $pr)
                                <option value="{{ $pr['id'] }}" {{ (old('state') == $pr['id']) ? 'selected' : '' }}>{{ $pr['state'] }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ($errors->has('state'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('state') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="city" class="col-md-4 col-form-label text-md-right">شهر</label>

                <div class="col-md-6">
                    <select id="city" class="form-control{{ $errors->has('city') ? ' is-invalid' : '' }}" name="city">
                        <option value="">لطفا انتخاب کنید</option>
                    </select>

                    @if ($errors->has('city'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('city') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="address" class="col-md-4 col-form-label text-md-right">آدرس</label>

                <div class="col-md-6">
                    <input id="address" type="text" class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}" name="address" value="{{ old('address') }}" >

                    @if ($errors->has('address'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="bio" class="col-md-4 col-form-label text-md-right">بیو گرافی و معرفی مختصر از خودتان</label>

                <div class="col-md-6">
                    <input id="bio" type="text" class="form-control{{ $errors->has('bio') ? ' is-invalid' : '' }}" name="bio" value="{{ old('bio') }}" >

                    @if ($errors->has('bio'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('bio') }}</strong>
                        </span>
                    @endif
                </div>
            </div>


            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">تغییر رمز عبور</label>

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


    <script>
        $(document).ready(function () {
        })
    </script>
@endsection
