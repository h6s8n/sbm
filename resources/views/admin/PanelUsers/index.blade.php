@extends('admin.layouts.app')

@section('page_name', ' کاربران پنل')

@section('content')

    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th></th>
                        <th>نام و نام خانوادگی</th>
                        <th>ایمیل</th>
                        <th>تاریخ عضویت</th>
                        <th>اعمال</th>
                    </tr>
                    @php $row_count =1 @endphp
                    @foreach($request as $k => $item)
                        <tr role="row" class="filter">
                            <td>{{$row_count}}</td>
                            <td>{{ ($item['fullname']) ? $item['fullname'] : '-' }}</td>
                            <td>{{ ($item['email']) ? $item['email'] : 'بدون ایمیل' }}</td>
                            <td>{{ jdate('Y/m/d ساعت H:i:s', strtotime($item['created_at'])) }}</td>
                            <td>
                                <div class="col-xs-12">
                                    <a class="btn btn-block btn-warning btn-rounded request_but"
                                       href="{{route('user.roles',$item['id'])}}">نقش ها</a>
                                </div>
                            </td>
                        </tr>
                        @php $row_count =$row_count+1 @endphp
                    @endforeach

                </table>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
