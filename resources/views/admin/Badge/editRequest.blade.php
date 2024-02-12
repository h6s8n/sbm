@extends('admin.layouts.app')

@section('page_name', ' تغییر وضعیت ')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('badge-request.update',$request)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group col-md-6">
                <label for="status"> وضعیت درخواست</label>
                <select id="status" class="form-control" name="status">
                    <option value="PENDING" {{$request->status =='در انتظار بررسی' ? 'selected' : ''}}>در انتظار بررسی</option>
                    <option value="REGISTERED" {{$request->status =='ثبت شده' ? 'selected' : ''}}>ثبت شده</option>
                </select>
            </div>
            <div class="form-group col-md-6">
                <label for="pay_status"> وضعیت پرداخت</label>
                <select id="pay_status" class="form-control" name="pay_status">
                    <option value="PENDING" {{$request->pay_status == 'در انتظار بررسی' ? 'selected' : ''}}>در انتظار بررسی</option>
                    <option value="PAYED" {{$request->pay_status == 'ثبت شده' ? 'selected' : ''}}>ثبت شده</option>
                </select>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
