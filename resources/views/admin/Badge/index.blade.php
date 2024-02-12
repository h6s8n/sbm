@extends('admin.layouts.app')

@section('page_name', 'لیست نشان ها')

@section('content')
    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>نام</th>
                        <th>قیمت</th>
                        <th>لینک آیکون</th>
                        <th>اعمال</th>
                    </tr>
                    @foreach($badges as $badge)
                        <tr role="row" class="filter">
                            <td>{{$badge->name}}</td>
                            <td>{{$badge->priority}}</td>
                            <td>
                                <a href="{{$badge->icon}}" target="_blank">مشاهده تصویر</a>
                            </td>
                            <td>
                                <div class="col-xs-6">
                                    <a class="btn btn-block btn-primary btn-rounded request_but"
                                       href="{{route('badge.edit',$badge)}}"> ویرایش</a>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </table>
            </div>
            {!! $badges->render() !!}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
