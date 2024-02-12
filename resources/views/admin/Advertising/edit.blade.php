@extends('admin.layouts.app')

@section('page_name', 'ویرایش درخواست ')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('advertising.update',$request)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="title" class="col-md-4 col-form-label text-md-right">عنوان تبلیغ </label>
                <div class="col-md-6">
                    <input id="title" type="text" class="form-control {{ $errors->has('title') ? ' is-invalid' : '' }}" name="title" value="{{ old('title',$request->title)}}">
                    @if ($errors->has('title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('title') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="link" class="col-md-4 col-form-label text-md-right">لینک تبلیغ </label>
                <div class="col-md-6">
                    <input id="link" type="text" class="form-control {{ $errors->has('link') ? ' is-invalid' : '' }}" name="link" value="{{ old('link',$request->link)}}">
                    @if ($errors->has('link'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('link') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="picture" class="col-md-4 col-form-label text-md-right">تصویر</label>

                <div class="col-xs-9 col-md-6">
                    <input id="picture" type="file" class="form-control{{ $errors->has('picture') ? ' is-invalid' : '' }}" name="picture" >

                    @if ($errors->has('picture'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('picture') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="plan" class="col-md-4 col-form-label text-md-right">عنوان خدمت / جایگاه نمایش </label>
                <div class="col-md-6">
                    <input id="plan" required type="text" class="form-control {{ $errors->has('plan') ? ' is-invalid' : '' }}" name="plan" value="{{ old('plan',$request->plan)}}">
                    @if ($errors->has('plan'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('plan') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="amount" class="col-md-4 col-form-label text-md-right">هزینه (ریال)</label>
                <div class="col-md-6">
                    <input id="amount" type="number" class="form-control {{ $errors->has('amount') ? ' is-invalid' : '' }}"
                           name="amount" required value="{{ old('amount',$request->amount) }}"
                    @if($request->payment_status == 'پرداخت شده') disabled @endif
                    >
                    @if ($errors->has('amount'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('amount') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="fullname" class="col-md-4 col-form-label text-md-right">نام و نام خانوادگی</label>
                <div class="col-md-6">
                    <input id="fullname" type="text" class="form-control {{ $errors->has('fullname') ? ' is-invalid' : '' }}"
                           name="fullname" value="{{ old('fullname',$request->fullname) }}">
                    @if ($errors->has('fullname'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('fullname') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="mobile" class="col-md-4 col-form-label text-md-right">موبایل</label>
                <div class="col-md-6">
                    <input id="mobile" required type="number" class="form-control {{ $errors->has('mobile') ? ' is-invalid' : '' }}"
                           name="mobile" value="{{ old('mobile',$request->mobile) }}">
                    @if ($errors->has('mobile'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('mobile') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="start_at" class="col-md-4 col-form-label text-md-right">تاریخ فعال سازی	</label>
                <div class="col-md-6">
                    <input id="start_at" type="text"
                           class="form-control{{ $errors->has('start_at') ? ' is-invalid' : '' }}
                               observer"
                           name="start_at">

                    @if ($errors->has('start_at'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('start_at') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="end_at" class="col-md-4 col-form-label text-md-right">تاریخ پایان	</label>
                <div class="col-md-6">
                    <input id="end_at" type="text"
                           class="form-control{{ $errors->has('end_at') ? ' is-invalid' : '' }}
                               observer"
                           name="end_at">

                    @if ($errors->has('end_at'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('end_at') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="status"> وضعیت درخواست</label>
                <select id="status" class="form-control" name="status">
                    <option value="pending" {{$request->status =='در انتظار بررسی' ? 'selected' : ''}}>در انتظار بررسی</option>
                    <option value="active" {{$request->status =='ثبت شده' ? 'selected' : ''}}>ثبت شده</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="payment_status"> وضعیت پرداخت</label>
                <select id="payment_status" class="form-control" name="payment_status"
                        @if($request->payment_status == 'پرداخت شده') disabled @endif
                >
                    <option value="pending" {{$request->payment_status == 'در انتظار بررسی' ? 'selected' : ''}}>در انتظار بررسی</option>
                    <option value="paid" {{$request->payment_status == 'پرداخت شده' ? 'selected' : ''}}>پرداخت شده</option>
                </select>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ویرایش درخواست</button>
                    @if($request->payment_status != 'پرداخت شده')
                    <button type="submit" name="send" value="sms" class="btn btn-info waves-effect waves-light">ارسال پیامک لینک پرداخت</button>
                    @endif
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
