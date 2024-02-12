@extends('admin.layouts.app')

@section('page_name', 'پرداخت جدید')

@section('content')

    <div class="white-box">

        <form method="post" class="avatar" style="direction: rtl">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">مبلغ به ریال </label>
                <div class="col-md-6">
                    <input id="amount" type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}"
                           name="amount" value="{{ old('amount')}}">
                    @if ($errors->has('amount'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('amount') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                    <a class="btn btn-danger waves-effect waves-light"
                       href="{{route('specialization.index')}}"> بازگشت</a>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>
@endsection
