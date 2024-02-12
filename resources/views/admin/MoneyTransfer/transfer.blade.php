@extends('admin.layouts.app')

@section('page_name', 'انتقال وجه')

@section('content')
    <div class="white-box">
        <div class="col-lg-12">
            <div class="alert alert-success">
                <ul>
                    <li>{{'مبلغ اعتبار کاربر '.number_format($user->credit).' ریال معادل '.number_format($user->credit/10).' تومان می باشد.'}}</li>
                </ul>
            </div>
        </div>
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>
            <form class="filter_list" method="post" style="direction: rtl">
                {{csrf_field()}}
                <div class="row form-group">
                    <div class="col-lg-6">
                        <label for="name">نام: </label>
                        <input type="text" class="form-control" id="name" value="{{$user->name.' '.$user->family}}"
                               disabled>
                    </div>
                    <div class="col-lg-6">
                        <label for="account_sheba">شماره شبا : </label>
                        <input type="text" class="form-control" id="account_sheba" value="{{$user->account_sheba}}"
                               disabled>
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-lg-6">
                        <label for="credit">مبلغ اعتبار: </label>
                        <input type="text" class="form-control" id="credit" value="{{$user->credit.' ریال '}}"
                               disabled>
                    </div>
                    <div class="col-lg-6">
                        <label for="account_sheba">مبلغ مورد نظر جهت انتقال (ریال): </label>
                        <input name="amount" type="text" class="form-control" id="account_sheba" placeholder="مبلغ به ریال وارد شود">
                    </div>
                </div>
                <div class="row form-group">
                    <div class="col-lg-12">
                        <label for="description">توضیحات: </label>
                        <textarea name="description" type="text" class="form-control" id="description" placeholder="توضیحات: "></textarea>
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-info waves-effect waves-light">انتقال</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
