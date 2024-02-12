@extends('admin.layouts.app')

@if($partner)
    @section('page_name', 'افزودن برنامه برای ' . $partner->name)
@else
    @section('page_name', 'افزودن برنامه پزشک')
@endif
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>
@section('content')

    <div class="white-box">

        <form method="post" action="" enctype="multipart/form-data" class="avatar" style="direction: rtl">
            {{ csrf_field() }}


            <div class="form-group row">
                <label for="doctor" class="col-md-4 col-form-label text-md-right">نام پزشک </label>

                <div class="col-md-6">
                    <select id="doctor"
                            class="js-example-basic-multiple form-control{{ $errors->has('doctor') ? ' is-invalid' : '' }}"
                            name="doctor">
                        @foreach($doctors as $doctor)
                            <option
                                value="{{ $doctor['id'] }}" {{ (old('doctor') == $doctor['id']) ? 'selected' : '' }}>{{ $doctor['fullname'] }}</option>
                        @endforeach
                    </select>

                    @if ($errors->has('doctor'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('doctor') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="type" class="col-md-4 col-form-label text-md-right">نوع ویزیت </label>
                <div class="col-md-6" style="display: flex;justify-content: space-between;">
                    <label>
                        <input type="radio" class="form-control" name="type" id="type" value="1" checked>ویزیت
                        معمولی</label>
                    <label>
                        <input type="radio" class="form-control" name="type" id="type" value="2">ویزیت فوری</label>
                    <label>
                        <input type="radio" class="form-control" name="type" id="type" value="3">ویزیت آفلاین</label>
                    <label>
                        <input type="radio" class="form-control" name="type" id="type" value="4">تفسیر آزمایش</label>
                    <label>
                        <input type="radio" class="form-control" name="type" id="type" value="5">حضوری</label>
                    @if ($errors->has('type'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('type') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="price" class="col-md-4 col-form-label text-md-right">قیمت ویزیت (ریال) </label>

                <div class="col-md-6">
                    <input id="price" type="number" class="form-control{{ $errors->has('price') ? ' is-invalid' : '' }}"
                           name="price" value="{{ old('price', 0) }}" min="0">


                    @if ($errors->has('price'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('price') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label class="col-xl-3 col-xs-3 col-form-label text-md-right"> تاریخ شروع ویزیت (شمسی)</label>

                <div class="form-group col-xl-3 col-xs-3">
                    <select id="day" class="form-control{{ $errors->has('day') ? ' is-invalid' : '' }}" name="day">
                        <option value="">روز</option>
                        @for($d = 1 ; $d <= 31; $d++)
                            <option
                                value="{{ $d }}" {{ (old('day', jdate('d')) == $d) ? 'selected' : '' }}>{{ $d }}</option>
                        @endfor
                    </select>

                    @if ($errors->has('day'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('day') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group col-xl-3 col-xs-3">
                    <select id="month" class="form-control{{ $errors->has('month') ? ' is-invalid' : '' }}"
                            name="month">
                        <option value="">ماه</option>
                        @php
                            $MonthList = [
                                1 => "فروردین",
                                2 => "اردیبهشت",
                                3 => "خرداد" ,
                                4 => "تیر",
                                5 => "مرداد",
                                6 => "شهریور",
                                7 => "مهر",
                                8 => "آبان",
                                9 => "آذر",
                                10 => "دی",
                                11 => "بهمن",
                                12 => "اسفند"
                            ];
                        @endphp
                        @foreach($MonthList as $key => $day)
                            <option
                                value="{{ $key }}" {{ (old('month', jdate('m')) == $key) ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>

                    @if ($errors->has('month'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('month') }}</strong>
                        </span>
                    @endif
                </div>

                <div class="form-group col-xl-3 col-xs-3">
                    <select id="year" class="form-control{{ $errors->has('year') ? ' is-invalid' : '' }}" name="year">
                        <option value="">سال</option>
                        <option value="1399"
                            {{ (old('year', jdate('Y')) == 1399) ? 'selected' : '' }}>{{ 1399 }}</option>
                        <option value="1400"
                            {{ (old('year', jdate('Y')) == 1400) ? 'selected' : '' }}>{{ 1400 }}</option>
                        <option value="1401"
                            {{ (old('year', jdate('Y')) == 1401) ? 'selected' : '' }}>{{ 1401 }}</option>
                    </select>

                    @if ($errors->has('month'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('month') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="sum_date" class="col-md-4 col-form-label text-md-right"> به مدت</label>

                <div class="col-md-6">
                    <select id="sum_date" class="form-control{{ $errors->has('sum_date') ? ' is-invalid' : '' }}"
                            name="sum_date">
                        @for($d = 1 ; $d <= 31; $d++)
                            <option value="{{ $d }}" {{ (old('sum_date') == $d) ? 'selected' : '' }}>{{ $d }}روز
                            </option>
                        @endfor
                        @for($d = 2 ; $d <= 12; $d++)
                            <option value="{{ $d * 30 }}" {{ (old('sum_date') == $d * 30) ? 'selected' : '' }}>{{ $d}}
                                ماه
                            </option>
                        @endfor
                    </select>

                    @if ($errors->has('sum_date'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('sum_date') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="date_time" class="col-md-4 col-form-label text-md-right"> روز های مورد نظر</label>

                <div class="col-md-6">
                    @php
                        $dateTimeList = [
                            "شنبه",
                            "یکشنبه",
                            "دوشنبه",
                            "سه شنبه",
                            "چهارشنبه",
                            "پنجشنبه",
                            "جمعه"
                        ];
                    @endphp
                    <select id="date_time" multiple
                            class="form-control{{ $errors->has('date_time') ? ' is-invalid' : '' }}" name="date_time[]">
                        @foreach($dateTimeList as $day)
                            <option
                                value="{{ $day }}" {{ (old('date_time') == $day) ? 'selected' : '' }}>{{ $day }}</option>
                        @endforeach
                    </select>

                    @if ($errors->has('date_time'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('date_time') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-2">
                    <p>توجه داشته باشید که شما میتوانید چندین ساعت را انتخاب کنید.</p>
                </div>
            </div>

            <div class="form-group row">
                <label for="time" class="col-md-4 col-form-label text-md-right"> ساعت های مجاز</label>

                <div class="col-md-6">
                    <select id="time" multiple class="form-control{{ $errors->has('time') ? ' is-invalid' : '' }}"
                            name="time[]">
                        <option value="24" {{ (old('time') == 24) ? 'selected' : '' }}> 00 {{ ' الی ' . 1 }}</option>
                        @for($d = 1 ; $d <= 23; $d++)
                            <option
                                value="{{ $d }}" {{ (old('time') == $d) ? 'selected' : '' }}>{{ $d . ' الی ' . ($d + 1) }}</option>
                        @endfor
                    </select>

                    @if ($errors->has('time'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('time') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-2">
                    <p>توجه داشته باشید که شما میتوانید چندین ساعت را انتخاب کنید.</p>
                </div>
            </div>

            <div class="form-group row">
                <label for="capacity" class="col-md-4 col-form-label text-md-right"> ظرفیت مجاز در هر ساعت</label>

                <div class="col-md-6">
                    <select id="capacity" class="form-control{{ $errors->has('capacity') ? ' is-invalid' : '' }}"
                            name="capacity">
                        @for($d = 1 ; $d <= 10; $d++)
                            <option
                                value="{{ $d }}" {{ (old('time') == $d) ? 'selected' : '' }}>{{ $d . ' نفر' }}</option>
                        @endfor
                    </select>

                    @if ($errors->has('capacity'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('capacity') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="has_prescription" class="col-md-4 col-form-label text-md-right">نسخه الکترونیک</label>
                <div class="col-md-1">
                        <label>
                            <input type="radio" class="form-control" name="has_prescription" id="has_prescription"
                                   value="0" checked>ندارد</label>
                </div>
                <div class="col-md-1">
                    <label>
                        <input type="radio" class="form-control"
                               name="has_prescription" id="has_prescription" value="1">دارد</label>
                </div>
            </div>

            <input type="hidden" name="partner" value="{{ ($partner) ? $partner->id : 0 }}">

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت برنامه</button>
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
