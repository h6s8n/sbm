@extends('admin.layouts.app')

@section('page_name', 'ویرایش بیمارستان ')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('partner.update',$partner)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="slug" class="col-md-4 col-form-label text-md-right">نامک</label>
                <div class="col-md-6">
                    <input id="slug" type="text" class="form-control {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                           name="slug" value="{{ old('slug',$partner->slug) }}" readonly>
                    @if ($errors->has('slug'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('slug') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">نام</label>
                <div class="col-md-6">
                    <input id="name" type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}"
                           name="name" value="{{ old('name',$partner->name) }}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">نام کوتاه</label>
                <div class="col-md-6">
                    <input id="short_name" type="text" class="form-control {{ $errors->has('short_name') ? ' is-invalid' : '' }}"
                           name="short_name" value="{{ old('short_name',$partner->short_name) }}">
                    @if ($errors->has('short_name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('short_name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="phone" class="col-md-4 col-form-label text-md-right">تلفن </label>
                <div class="col-md-6">
                    <input id="phone" type="text" class="form-control {{ $errors->has('phone') ? ' is-invalid' : '' }}"
                           name="phone" value="{{ old('phone',$partner->phone) }}">
                    @if ($errors->has('phone'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('phone') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="sheba" class="col-md-4 col-form-label text-md-right">شماره شبا </label>
                <div class="col-md-6">
                    <input id="sheba" type="text" class="form-control {{ $errors->has('sheba') ? ' is-invalid' : '' }}"
                           name="sheba" value="{{ old('phone',$partner->sheba) }}" maxlength="24" minlength="24">
                    @if ($errors->has('sheba'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('sheba') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="logo" class="col-md-4 col-form-label text-md-right">لوگو</label>
                <div class="col-xs-9 col-md-6">
                    <input id="logo" type="file" class="form-control{{ $errors->has('logo') ? ' is-invalid' : '' }}" name="logo">
                    @if ($errors->has('logo'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('logo') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-xs-3 col-md-2">
                    @if($partner->logo)
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="{{ $partner->logo }}" target="_blank">مشاهده تصویر</a>
                    @else
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="#" target="_blank">تصویر موجود ندارد</a>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="location" class="col-md-4 col-form-label text-md-right">موقعیت جغرافیایی </label>
                <div class="col-md-6">
                    <input id="location" type="text"
                           class="form-control {{ $errors->has('location') ? ' is-invalid' : '' }}" name="location"
                           value="{{ old('location',$partner->location) }}">
                    @if ($errors->has('location'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('location') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="times" class="col-md-4 col-form-label text-md-right">بازه زمانی </label>
                <div class="col-md-6">
                    <input id="times" type="text" class="form-control {{ $errors->has('times') ? ' is-invalid' : '' }}"
                           name="times" value="{{ old('times',$partner->times) }}">
                    @if ($errors->has('times'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('times') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="doctor_percent" class="col-md-4 col-form-label text-md-right">درصد دکتر </label>
                <div class="col-md-6">
                    <input id="doctor_percent" type="text"
                           class="form-control {{ $errors->has('doctor_percent') ? ' is-invalid' : '' }}"
                           name="doctor_percent" value="{{ old('doctor_percent',$partner->doctor_percent) }}">
                    @if ($errors->has('doctor_percent'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('doctor_percent') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="partner_percent" class="col-md-4 col-form-label text-md-right">درصد بیمارستان </label>
                <div class="col-md-6">
                    <input id="partner_percent" type="text"
                           class="form-control {{ $errors->has('partner_percent') ? ' is-invalid' : '' }}"
                           name="partner_percent" value="{{ old('partner_percent',$partner->partner_percent) }}">
                    @if ($errors->has('partner_percent'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('partner_percent') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="service_id" class="col-md-4 col-form-label text-md-right">خدمات قابل ارایه</label>
                <div class="col-md-6">
                    <select id="service_id"
                            class="form-control js-example-basic-multiple{{ $errors->has('service_id') ? ' is-invalid' : '' }}"
                            name="service_id[]" multiple>
                        {{--                        <option value="">لطفا انتخاب کنید</option>--}}
                        @foreach($services as $item)
                            <option value="{{$item->id}}" {{ ((old('service_id') == $item->id) ||
$partner->services()
->where(\Illuminate\Support\Facades\DB::raw('partner_services.service_id'),$item->id)
->first()) ? 'selected' : '' }}>{{ $item->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('service_id'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('service_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="insurance_id" class="col-md-4 col-form-label text-md-right">بیمه های طرف قرارداد</label>

                <div class="col-md-6">
                    <select id="insurance_id"
                            class="form-control js-example-basic-multiple{{ $errors->has('insurance_id') ? ' is-invalid' : '' }}"
                            name="insurance_id[]" multiple>
                        {{--                        <option value="">لطفا انتخاب کنید</option>--}}
                        @foreach($insurances as $item)
                            <option value="{{$item->id}}" {{ ((old('insurance_id') == $item->id) ||
$partner->insurances()
->where(\Illuminate\Support\Facades\DB::raw('partner_insurances.insurance_id'),$item->id)
->first()) ? 'selected' : '' }}>{{ $item->name }}</option>                        @endforeach
                    </select>
                    @if ($errors->has('insurance_id'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('insurance_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="biography" class="col-md-4 col-form-label text-md-right">بیوگرافی </label>
                <div class="col-md-6">
                    <textarea id="biography" type="text"
                              class="form-control {{ $errors->has('biography') ? ' is-invalid' : '' }}"
                              name="biography">{{ old('biography',$partner->biography) }}</textarea>
                    @if($errors->has('biography'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('biography') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="address" class="col-md-4 col-form-label text-md-right">آدرس </label>
                <div class="col-md-6">
                    <textarea id="address" type="text"
                              class="form-control {{ $errors->has('address') ? ' is-invalid' : '' }}"
                              name="address">{{ old('address',$partner->address) }}</textarea>
                    @if($errors->has('address'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('address') }}</strong>
                        </span>
                    @endif
                </div>
            </div>



            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="email" class="col-md-4 col-form-label text-md-right">ایمیل پنل مدیریت</label>

                <div class="col-md-6">
                    <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" value="{{ old('email',$support ? $support->email : '') }}" name="email">

                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right"> رمز عبور  پنل مدیریت</label>

                <div class="col-md-6">
                    <p>در صورتی که نیاز به تغییر رمز عبور دارید این بخش را پر کنید.</p>
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" value="{{ old('password') }}" name="password" >

                    @if ($errors->has('password'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
            </div>




            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.js-example-basic-multiple').select2();
        });
    </script>
@endsection
