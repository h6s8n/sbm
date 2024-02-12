@extends('admin.layouts.app')

@section('page_name', 'ثبت واریزی به اپراتور درگاه سلامت ')

@section('content')
    <div class="white-box">
        <form method="post" action="" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="transId" class="col-md-4 col-form-label text-md-right">شناسه پرداخت</label>
                <div class="col-md-6  text-md-right">
                    <input id="transId" type="text" class="form-control {{ $errors->has('transId') ? ' is-invalid' : '' }}"
                           name="transId" required>
                    @if ($errors->has('transId'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('transId') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="fullname" class="col-md-4 col-form-label text-md-right">نام و نام خانوادگی اپراتور</label>
                <div class="col-md-6  text-md-right">
                    <input id="fullname" type="text" class="form-control {{ $errors->has('fullname') ? ' is-invalid' : '' }}"
                           name="fullname" required>
                    @if ($errors->has('fullname'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('fullname') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="amount" class="col-md-4 col-form-label text-md-right">مبلغ پرداختی (ریال)</label>
                <div class="col-md-6  text-md-right">
                    <input id="amount" type="text" class="form-control {{ $errors->has('amount') ? ' is-invalid' : '' }}"
                           name="amount" required>
                    @if ($errors->has('amount'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('amount') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="email" class="col-md-4 col-form-label text-md-right">ایمیل اپراتور در سیستم</label>
                <div class="col-md-6  text-md-right">
                    <input id="email" type="email" class="form-control {{ $errors->has('email') ? ' is-invalid' : '' }}"
                           name="email" required>
                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('email') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>


    </div>
@endsection
