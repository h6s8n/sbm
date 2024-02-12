@extends('admin.layouts.app')

@section('page_name', 'فرم پرداخت تبلیغات')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('advertising.submitPaymentForm')}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="plan" class="col-md-4 col-form-label text-md-right">عنوان خدمت </label>
                <div class="col-md-6">
                    <input id="plan" type="text" class="form-control {{ $errors->has('plan') ? ' is-invalid' : '' }}" name="plan" value="{{ old('plan') }}">
                    @if ($errors->has('plan'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('plan') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="amount" class="col-md-4 col-form-label text-md-right">مبلغ (ریال)</label>
                <div class="col-md-6">
                    <input id="amount" type="number" class="form-control {{ $errors->has('amount') ? ' is-invalid' : '' }}"
                           name="amount" value="{{ old('amount') }}">
                    @if ($errors->has('amount'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('amount') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="fullname" class="col-md-4 col-form-label text-md-right">نام و نام خانوادگی </label>
                <div class="col-md-6">
                    <input id="fullname" type="text" class="form-control {{ $errors->has('fullname') ? ' is-invalid' : '' }}"
                           name="fullname" value="{{ old('fullname') }}">
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
                    <input id="mobile" type="number" class="form-control {{ $errors->has('mobile') ? ' is-invalid' : '' }}"
                           name="mobile" value="{{ old('mobile') }}">
                    @if ($errors->has('mobile'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('mobile') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ارسال</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
