@extends('admin.layouts.app')

@section('page_name', 'ثبت شناسه پرداخت ')

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
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>


    </div>
@endsection
