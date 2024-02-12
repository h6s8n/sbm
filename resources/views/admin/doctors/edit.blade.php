@extends('admin.layouts.app')
@section('header')
{{--    <script src="https://cdn.ckeditor.com/4.16.2/standard-all/ckeditor.js"></script>--}}
    <script src="https://cdn.ckeditor.com/4.16.2/full-all/ckeditor.js"></script>
    <style>
        div#cke_1_contents {
            height: 400px !important;
        }
    </style>
@stop
@section('page_name', 'ویرایش اطلاعات پزشک')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@section('content')

    <div class="white-box">

        <form method="post" enctype="multipart/form-data" action="" class="avatar" style="direction: rtl">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="doctor_nickname" class="col-md-4 col-form-label text-md-right">عنوان پزشکی </label>

                <div class="col-md-6">
                    <input id="doctor_nickname" type="text"
                           class="form-control{{ $errors->has('doctor_nickname') ? ' is-invalid' : '' }}"
                           name="doctor_nickname" value="{{ old('doctor_nickname', $request->doctor_nickname) }}">

                    @if ($errors->has('doctor_nickname'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('doctor_nickname') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">نام </label>

                <div class="col-md-6">
                    <input id="name" type="text" required class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}"
                           name="name" value="{{ old('name', $request->name) }}" autofocus>

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
                    <input id="family" type="text" required class="form-control{{ $errors->has('family') ? ' is-invalid' : '' }}"
                           name="family" value="{{ old('family', $request->family) }}">

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
                    <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" value="{{ old('email', $request->email) }}">

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
                    <input id="mobile" type="text" class="form-control{{ $errors->has('mobile') ? ' is-invalid' : '' }}"
                           name="mobile" value="{{ old('mobile', $request->mobile) }}">

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
                    <input id="nationalcode" type="text"
                           class="form-control{{ $errors->has('nationalcode') ? ' is-invalid' : '' }}"
                           name="nationalcode" value="{{ old('nationalcode', $request->nationalcode) }}">

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
                    <select id="sp_gp"
                            class="form-control js-example-basic-multiple{{ $errors->has('sp_gp') ? ' is-invalid' : '' }}"
                            name="sp_gp[]" multiple>
                        @foreach($specializations as $item)
                            <option value="{{ $item->id }}"
                                    {{ ((old('sp_gp', $request->sp_gp) == $item->id) || $request->hasSpecialization((array)$item->id)) ? 'selected' : '' }}>{{ $item->name }}</option>
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
                <label for="special_json" class="col-md-4 col-form-label text-md-right">تخصص ها</label>
                <div class="col-md-6">
                    <select id="special_json"
                            class="form-control js-example-basic-multiple{{ $errors->has('special_json') ? ' is-invalid' : '' }}"
                            name="special_json[]" multiple>
                        @foreach($specialties as $item)
                            <option value="{{ "{".'"value":'.$item->value.',"label":"'.$item->label."\"}" }}"
                                {{ $request->hasSpecialties($item->value) ? 'selected' : '' }}
                            >{{ $item->label }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('special_json'))
                        <span class="invalid-feedback">
                        <strong>{{ $errors->first('special_json') }}</strong>
                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="skill_json" class="col-md-4 col-form-label text-md-right">مهارت ها</label>
                <div class="col-md-6">
                    <select id="skill_json"
                            class="form-control js-example-basic-multiple{{ $errors->has('skill_json') ? ' is-invalid' : '' }}"
                            name="skill_json[]" multiple>
                        @foreach($skills as $item)
                            <option value="{{ "{".'"value":'.$item->value.',"label":"'.$item->label."\"}" }}"
                                {{ $request->hasSkill($item->value) ? 'selected' : '' }}
                            >{{ $item->label }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('skill_json'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('skill_json') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="partner_id" class="col-md-4 col-form-label text-md-right">ییمارستان ها</label>

                <div class="col-md-6">
                    <select id="partner_id"
                            class="form-control js-example-basic-multiple{{ $errors->has('partner_id') ? ' is-invalid' : '' }}"
                            name="partner_id[]" multiple>
                        @foreach($partners as $item)
                            <option value="{{$item->id}}" {{ ((old('partner_id') == $item->id) ||
$request->partners()
->where(\Illuminate\Support\Facades\DB::raw('partner_doctors.partner_id'),$item->id)
->first()) ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('partner_id'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('partner_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="special_point" class="col-md-4 col-form-label text-md-right">نکته</label>

                <div class="col-md-6">
                    <textarea id="special_point" type="text"
                              class="form-control{{ $errors->has('special_point') ? ' is-invalid' : '' }}"
                              name="special_point">{{ old('special_point', $request->special_point) }}</textarea>

                    @if ($errors->has('special_point'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('special_point') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="in_person_special_point" class="col-md-4 col-form-label text-md-right">نکته حضوری</label>

                <div class="col-md-6">
                    <textarea id="in_person_special_point" type="text"
                              class="form-control{{ $errors->has('in_person_special_point') ? ' is-invalid' : '' }}"
                              name="in_person_special_point">{{ old('in_person_special_point', $request->in_person_special_point) }}</textarea>

                    @if ($errors->has('in_person_special_point'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('in_person_special_point') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="username" class="col-md-4 col-form-label text-md-right">عنوان صفحه اختصاصی (فارسی)</label>

                <div class="col-md-6">
                    <input id="username" type="text"
                           class="form-control{{ $errors->has('username') ? ' is-invalid' : '' }}" name="username"
                           value="{{ old('username', $request->username) }}">

                    @if ($errors->has('username'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('username') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="en_url" class="col-md-4 col-form-label text-md-right">عنوان صفحه اختصاصی (انگلیسی)</label>

                <div class="col-md-6">
                    <input id="en_url" type="text" class="form-control{{ $errors->has('en_url') ? ' is-invalid' : '' }}"
                           name="en_url" value="{{ old('en_url', $request->en_url) }}">

                    @if ($errors->has('en_url'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('en_url') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="job_title" class="col-md-4 col-form-label text-md-right">عنوان تخصص روی تابلو</label>

                <div class="col-md-6">
                    <input id="job_title" type="text"
                           class="form-control{{ $errors->has('job_title') ? ' is-invalid' : '' }}" name="job_title"
                           value="{{ old('job_title', $request->job_title) }}">

                    @if ($errors->has('job_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('job_title') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="office_secretary_name" class="col-md-4 col-form-label text-md-right">نام منشی</label>

                <div class="col-md-6">
                    <input id="office_secretary_name" type="text"
                           class="form-control{{ $errors->has('office_secretary_name') ? ' is-invalid' : '' }}" name="office_secretary_name"
                           value="{{ old('office_secretary_name', optional($request->secretaries->last())->office_secretary_name) }}">

                    @if ($errors->has('office_secretary_name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('office_secretary_name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="office_secretary_mobile" class="col-md-4 col-form-label text-md-right">شماره موبایل منشی</label>

                <div class="col-md-6">
                    <input id="office_secretary_mobile" type="text"
                           class="form-control{{ $errors->has('office_secretary_mobile') ? ' is-invalid' : '' }}" name="office_secretary_mobile"
                           value="{{ old('office_secretary_mobile', optional($request->secretaries->last())->office_secretary_mobile) }}">

                    @if ($errors->has('office_secretary_mobile'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('office_secretary_mobile') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="specialcode" class="col-md-4 col-form-label text-md-right">شماره نظام پزشکی یا نظام
                    صنفی</label>

                <div class="col-md-6">
                    <input id="specialcode" type="text"
                           class="form-control{{ $errors->has('specialcode') ? ' is-invalid' : '' }}" name="specialcode"
                           value="{{ old('specialcode', $request->specialcode) }}">

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
                    <input id="account_number" type="text"
                           class="form-control{{ $errors->has('account_number') ? ' is-invalid' : '' }}"
                           name="account_number" value="{{ old('account_number', $request->account_number) }}">

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
                    <input id="account_sheba" type="text"
                           class="form-control{{ $errors->has('account_sheba') ? ' is-invalid' : '' }}"
                           name="account_sheba" value="{{ old('account_sheba', $request->account_sheba) }}">

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

                <div class="col-xs-9 col-md-4">
                    <div class="form-group">
                    <input id="picture" type="file"
                           class="form-control{{ $errors->has('picture') ? ' is-invalid' : '' }}" name="picture">

                    </div>
                    @if ($errors->has('picture'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('picture') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="col-xs-3 col-md-4">
                    @if($request->picture)
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;"
                           href="{{ $request->picture }}" target="_blank">مشاهده تصویر</a>
                        <label style="margin-top: 11px; display: inline-block; font-size: 14px;" for="delete-picture" class="col-md-4 col-form-label text-md-right">حذف تصویر

                        <input id="delete-picture" type="checkbox" name="delete-picture">
                        </label>

                    @else
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="#" target="_blank">تصویر
                            موجود ندارد</a>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="passport_image" class="col-md-4 col-form-label text-md-right">تصویر شناسنامه یا
                    پاسپورت</label>

                <div class="col-xs-9 col-md-6">
                    <input id="passport_image" type="file"
                           class="form-control{{ $errors->has('passport_image') ? ' is-invalid' : '' }}"
                           name="passport_image">

                    @if ($errors->has('passport_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('passport_image') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="col-xs-3 col-md-2">
                    @if($request->passport_image)
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;"
                           href="{{ $request->passport_image }}" target="_blank">مشاهده تصویر</a>
                    @else
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="#" target="_blank">تصویر
                            موجود ندارد</a>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="national_cart_image" class="col-md-4 col-form-label text-md-right">تصویر کارت ملی</label>

                <div class="col-xs-9 col-md-6">
                    <input id="national_cart_image" type="file"
                           class="form-control{{ $errors->has('national_cart_image') ? ' is-invalid' : '' }}"
                           name="national_cart_image">

                    @if ($errors->has('national_cart_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('national_cart_image') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="col-xs-3 col-md-2">
                    @if($request->national_cart_image)
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;"
                           href="{{ $request->national_cart_image }}" target="_blank">مشاهده تصویر</a>
                    @else
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="#" target="_blank">تصویر
                            موجود ندارد</a>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="special_cart_image" class="col-md-4 col-form-label text-md-right">تصویر کارت نظام
                    پزشکی</label>

                <div class="col-xs-9 col-md-6">
                    <input id="special_cart_image" type="file"
                           class="form-control{{ $errors->has('special_cart_image') ? ' is-invalid' : '' }}"
                           name="special_cart_image">

                    @if ($errors->has('special_cart_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('special_cart_image') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="col-xs-3 col-md-2">
                    @if($request->special_cart_image)
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;"
                           href="{{ $request->special_cart_image }}" target="_blank">مشاهده تصویر</a>
                    @else
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="#" target="_blank">تصویر
                            موجود ندارد</a>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="education_image" class="col-md-4 col-form-label text-md-right">تصویر مدرک تحصیلی یا پروانه
                    مطب</label>

                <div class="col-xs-9 col-md-6">
                    <input id="education_image" type="file"
                           class="form-control{{ $errors->has('education_image') ? ' is-invalid' : '' }}"
                           name="education_image">

                    @if ($errors->has('education_image'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('education_image') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="col-xs-3 col-md-2">
                    @if($request->education_image)
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;"
                           href="{{ $request->education_image }}" target="_blank">مشاهده تصویر</a>
                    @else
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="#" target="_blank">تصویر
                            موجود ندارد</a>
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
                            <input type="radio" id="status_active" name="status" value="active"
                                    {{ ( old('status', $request->status) == 'active') ? "checked" : "" }}>
                            <label for="status_active">فعال</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="status_active_imported" name="status" value="imported"
                                    {{ ( old('status', $request->status) == 'imported') ? "checked" : "" }}>
                            <label for="status_active_imported">ایمپورت شده - فعال</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="status_inactive" name="status"
                                   value="inactive" {{ ( old('status', $request->status) == 'inactive') ? "checked" : "" }}>
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
                            <input type="radio" id="doctor_status_active" name="doctor_status"
                                   value="active" {{ ( old('doctor_status', $request->doctor_status) == 'active') ? "checked" : "" }}>
                            <label for="doctor_status_active">تایید</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="doctor_status_inactive" name="doctor_status"
                                   value="inactive" {{ ( old('doctor_status', $request->doctor_status) == 'inactive') ? "checked" : "" }}>
                            <label for="doctor_status_inactive">معلق</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="doctor_status_failed" name="doctor_status"
                                   value="failed" {{ ( old('doctor_status', $request->doctor_status) == 'failed') ? "checked" : "" }}>
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
            <label for="code_title" class="col-md-4 col-form-label text-md-right">تیتر شماره: </label>
            <div class="form-group row" style="display: flex;flex-grow: 1;flex-wrap: wrap">
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_doctor" name="code_title"
                                   value="شماره نظام پزشکی" {{ ( old('code_title', $request->code_title) == 'شماره نظام پزشکی') ? "checked" : "" }}>
                            <label for="code_title_doctor">شماره نظام پزشکی</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_nurse" name="code_title"
                                   value="شماره نظام پرستاری" {{ ( old('code_title', $request->code_title) == 'شماره نظام پرستاری') ? "checked" : "" }}>
                            <label for="code_title_nurse">شماره نظام پرستاری</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_midwifery" name="code_title"
                                   value="شماره نظام مامایی" {{ ( old('code_title', $request->code_title) == 'شماره نظام مامایی') ? "checked" : "" }}>
                            <label for="code_title_midwifery">شماره نظام مامایی</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_physiotherapy" name="code_title"
                                   value="شماره نظام فیزیوتراپی" {{ ( old('code_title', $request->code_title) == 'شماره نظام فیزیوتراپی') ? "checked" : "" }}>
                            <label for="code_title_physiotherapy">شماره نظام فیزیوتراپی</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_nutrition" name="code_title"
                                   value=" شماره نظام تغذیه" {{ ( old('code_title', $request->code_title) == ' شماره نظام تغذیه') ? "checked" : "" }}>
                            <label for="code_title_nutrition"> شماره نظام تغذیه</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_treatment" name="code_title"
                                   value="شماره نظام  کاردرمانی" {{ ( old('code_title', $request->code_title) == 'شماره نظام  کاردرمانی') ? "checked" : "" }}>
                            <label for="code_title_treatment">شماره نظام کاردرمانی</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_psychology" name="code_title"
                                   value="شماره نظام روانشناسی" {{ ( old('code_title', $request->code_title) == 'شماره نظام روانشناسی') ? "checked" : "" }}>
                            <label for="code_title_psychology">شماره نظام روانشناسی</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_psychology" name="code_title"
                                   value="شماره نظام داروسازی" {{ ( old('code_title', $request->code_title) == 'شماره نظام داروسازی') ? "checked" : "" }}>
                            <label for="code_title_psychology">شماره نظام داروسازی</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-3">
                    <div class="radio radio-info">
                        <div>
                            <input type="radio" id="code_title_psychology" name="code_title"
                                   value="شماره نظام شنوایی سنجی" {{ ( old('code_title', $request->code_title) == 'شماره نظام شنوایی سنجی') ? "checked" : "" }}>
                            <label for="code_title_psychology">شماره نظام شنوایی سنجی</label>
                        </div>
                    </div>
                    @if ($errors->has('code_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('code_title') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="status" class="col-md-4 col-form-label text-md-right">جنسیت</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="gender_active" name="gender"
                                   value="0" {{ ( old('gender', $request->gender) == '0') ? "checked" : "" }}>
                            <label for="gender_active">مرد</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="gender_inactive" name="gender"
                                   value="1" {{ ( old('gender', $request->gender) == '1') ? "checked" : "" }}>
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
                    <select id="state" class="form-control{{ $errors->has('state') ? ' is-invalid' : '' }}"
                            name="state">
                        <option value="">لطفا انتخاب کنید</option>
                        @if($province)
                            @foreach($province as $pr)
                                <option value="{{ $pr['id'] }}" {{ (old('state', $request->state_id) == $pr['id']) ? 'selected' : '' }}>{{ $pr['state'] }}</option>
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
                    <input id="address" type="text"
                           class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}" name="address"
                           value="{{ old('address', $request->address) }}">

                    @if ($errors->has('address'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="phone" class="col-md-4 col-form-label text-md-right">تلفن</label>

                <div class="col-md-6">
                    <input id="phone" type="text"
                           class="form-control{{ $errors->has('phone') ? ' is-invalid' : '' }}" name="phone"
                           value="{{ old('phone', $request->phone) }}">

                    @if ($errors->has('phone'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('phone') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>


            <div class="form-group row">
                <label for="bio" class="col-md-4 col-form-label text-md-right">بیو گرافی و معرفی مختصر از خودتان</label>

                @php $temp_bio =
'دكتر
<br>
<br>
<strong>
تحصیلات:
</strong>
<br>
<br>
🟣
<br>
🟣
<br>
<br>
<br>
<strong>
سوابق علمی و کاری:
</strong>
<br>
<br>
🟢
<br>
🟢
<br>
<br>
<br>
<strong>
خدمات قابل ارائه و مهارت های درمانی :
</strong>
<br>
<br>
🟡
<br>
🟡
<br>
🟡
<br>
<br>
<br>
<strong>
مقالات  و سایر :
</strong>
<br>
<br>
🟤

<br>
<br>
<br>
دکتر...... متخصص ..... می باشند. مطب دکتر ..... در ...... می باشد. شما میتوانید از طریق سامانه سلامت بدون مرز یا SBM24 در زمینه ی ...... جهت مشاوره به صورت آنلاین (متنی، تماس صوتی و تصویری) و به صورت حضوری (داخل مطب) در روزها و ساعاتی که دکتر ....... تعیین نموده اند، نوبت آنلاین خود را دریافت کنید.
شما می توانید برای دریافت نوبت از دکتر ....... متخصص ....... وارد سامانه سلامت بدون مرز شوید و نوبت خود را ثبت نمایید و همچنین از طریق سامانه سلامت بدون مرز آدرس، شماره تلفن، بیوگرافی و نظرات بیماران دکتر ....... را مشاهده کنید. نوبت دهی آنلاین دکتر ....... فعال می باشد، درصورت فعال نبودن سرویس نوبت دهی آنلاین میتوانید روی گزینه به من اطلاع بده بزنید
تا هر زمان دکتر ....... نوبت ایجاد کردند به شما پیامک اطلاع رسانی ارسال شود .
' @endphp

                <div class="col-md-6">
                    <textarea rows="4" id="bio" type="text"
                              class="form-control{{ $errors->has('bio') ? ' is-invalid' : '' }} cke_rtl"
                              name="bio">{{ old('bio', $request->bio) ?? $temp_bio }}</textarea>

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
                    <p>در صورتی که نیاز به تغییر رمز عبور دارید این بخش را پر کنید.</p>
                    <input id="password" type="password"
                           class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password">

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
        <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
           href={{"https://sbm24.com/".$request->username}}>
            پروفایل در سایت</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>

        $(document).ready(function () {
            $('.js-example-basic-multiple').select2();
            $(document).delegate('#state', 'change', function (e) {
                e.preventDefault();

                var liveitem = $(this);
                var liveId = $(this).val();
                $('.area').css('display', 'none');

                $('#city').html('<option value="">لطفا انتخاب کنید</option>');

                $.ajax({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    method: 'POST',
                    url: '{{ route('get.cities') }}',
                    data: {state: liveId}
                }).done(function (result) {

                    if (result.status == 'success') {
                        result.data.forEach(function (element) {
                            $('#city').append('<option value="' + element['id'] + '">' + element['city'] + '</option>');
                        });

                    }

                });

            });

            @if(old('state', $request->state_id))

            var liveId = '{{ old('state', $request->state_id) }}';
            var city = '{{ old('city', $request->city_id) }}';

            console.log('sds');
            console.log('sdse');
            $.ajax({
                headers: {
                    'X-CSRF-TOKEN': '{{csrf_token()}}'
                },
                method: 'POST',
                url: '{{ route('get.cities')}}',
                data: {state: liveId}
            }).done(function (result) {


                if (result.status == 'success') {
                    result.data.forEach(function (element) {
                        $('#city').append('<option value="' + element['id'] + '">' + element['city'] + '</option>');
                    });

                    $('#city option[value="' + city + '"]').prop('selected', 'selected');
                }

            });
            console.log('wwww');

            @endif

            $(document).delegate('#city', 'change', function (e) {
                e.preventDefault();

                var liveitem = $(this);
                var liveId = $(this).val();

                if (liveId === 'تهران') {
                    $('.area').css('display', 'block');
                } else {
                    $('.area').css('display', 'none');
                }

            });
        })
    </script>
    <script>
        // ClassicEditor.create( document.querySelector( '#content' ) )
        //     .catch( error => {
        //         console.error( error );
        //     } );
        CKEDITOR.replace( 'bio', {
                contentsLangDirection: 'rtl',
        });
        CKEDITOR.config.contentsCss = '{{asset('css/my-editor.css')}}'

    </script>
@endsection
