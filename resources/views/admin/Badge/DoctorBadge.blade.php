@extends('admin.layouts.app')

@section('page_name', 'الصاق نشان به دکتر '.$user->fullname)
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@section('content')

    <div class="white-box">

        <form method="post" action="" enctype="multipart/form-data" class="avatar" style="direction: rtl">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="badge_id" class="col-md-4 col-form-label text-md-right">نام نشان</label>
                <div class="col-md-6">
                    <select id="badge_id"
                            class="form-control js-example-basic-multiple{{ $errors->has('badge_id') ? ' is-invalid' : '' }}"
                            name="badge_id">
                        @foreach($badges as $badge)
                            <option value="{{ $badge->id }}"
                                {{ (old('badge_id') === $badge->id ? 'selected' : '') }}>{{ $badge->name }}</option>
                        @endforeach
                    </select>
                    @if ($errors->has('badge_id'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('badge_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="activation_time" class="col-md-4 col-form-label text-md-right">تاریخ فعال سازی	</label>
                <div class="col-md-6">
                    <input id="activation_time" type="text"
                           class="form-control{{ $errors->has('activation_time') ? ' is-invalid' : '' }}
                               observer"
                           name="activation_time">

                    @if ($errors->has('activation_time'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('activation_time') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="expiration_time" class="col-md-4 col-form-label text-md-right">تاریخ پایان	</label>
                <div class="col-md-6">
                    <input id="expiration_time" type="text"
                           class="form-control{{ $errors->has('expiration_time') ? ' is-invalid' : '' }}
                               observer"
                           name="expiration_time">

                    @if ($errors->has('expiration_time'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('expiration_time') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت نشان</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>
        <table class="table table-striped table-bordered table-hover">
            <tr role="row" class="heading">
                <th>نام </th>
                <th>نشان</th>
                <th>فعال سازی</th>
                <th>پایان</th>
                <th>وضعیت</th>
                <th>عملیات</th>
            </tr>
            @foreach($user->badges()->get() as $badge)
                <tr role="row" class="filter">
                    <td>{{$user->fullname}}</td>
                    <td>{{$badge->name}}</td>
                    <td>{{jdate('Y-m-d',strtotime($badge->pivot->activation_time))}}</td>
                    <td>{{jdate('Y-m-d',strtotime($badge->pivot->expiration_time))}}</td>
                    <td>{{$badge->flag ? 'فعال' : 'غیر فعال'}}</td>
                    <td>
                        <div class="col-xs-6">
                            <a class="btn btn-block btn-danger btn-rounded request_but"
                               href="{{ route('detach.badge', ['user'=>$user ,'badge'=>$badge] ) }}">حذف</a>
                        </div>
                    </td>
                </tr>
            @endforeach

        </table>

    </div>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.js-example-basic-multiple').select2();
        });
    </script>
@endsection

