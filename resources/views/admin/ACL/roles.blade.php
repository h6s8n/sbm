
@extends('admin.layouts.app')

@section('page_name', 'نقش ها')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" action="{{route('role.store')}}"  method="post" style="direction: rtl">
                {{csrf_field()}}
                <div class="col-md-3 col-xs-6">
                    <div class="form-group">
                        <label for="name">نام</label>
                        <input type="text" class="form-control"
                               id="filter_user" name="name" >
                    </div>
                </div>
                <div class="col-md-3 col-xs-6">
                    <div class="form-group">
                        <label for="name">توضیحات</label>
                        <input type="text" class="form-control"
                               id="filter_user" name="display_name" >
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">ثبت</button>
                </div>
                <div class="clearfix"></div>

            </form>
        </div>
    </div>

    <form method="post">
        {{ csrf_field() }}
        <div class="white-box">

            <div class="portlet-body">
                <div class="table-container">

                    <table class="table table-striped table-bordered table-hover">
                        <tbody>
                        <tr role="row" class="heading">
                            <th></th>
                            <th>نام</th>
                            <th>توضیحات</th>
                            <th> گارد</th>
                            <th> تاریخ ثبت</th>
                            <th></th>
                        </tr>
                        @php $row_count =1 @endphp
                        @foreach($roles as $role)
                            <tr role="row" class="filter">
                                <td>{{$row_count}}</td>
                                <td>{{$role->name}}</td>
                                <td>{{$role->display_name}}</td>
                                <td>{{$role->guard_name}}</td>
                                <td>{{jdate('Y-m-d' , strtotime($role->created_at))}}</td>
                                <td>
                                    <div class="col-xs-12">
                                        <a class="btn btn-block btn-warning btn-rounded request_but"
                                           href="{{route('role.permissions.index',$role->id)}}">دسترسی ها </a>
                                    </div>
                                </td>
                            </tr>
                            @php $row_count = $row_count +1;@endphp
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {!! $roles->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                <div class="clearfix"></div>

            </div>

        </div>
    </form>

@endsection
