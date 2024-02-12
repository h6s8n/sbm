
@extends('admin.layouts.app')

@section('page_name', 'لیست تماس ها')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" action="{{route('admin.change.number.change',$id)}}" method="post">
                {{csrf_field()}}
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="mobile">شماره فعلی</label>
                        <input type="text" class="form-control" value="{{$event->safe_call_mobile}}" disabled>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="safe_call_mobile">شماره جدید</label>
                        <input type="text" class="form-control" name="safe_call_mobile" id="safe_call_mobile" >
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">تغییر</button>
                </div>
                <div class="clearfix"></div>

            </form>
        </div>
    </div>
    @if(!$item->isEmpty())
        <div class="white-box">
            <div class="portlet-body">
                <div class="table-container">
                    <table class="table table-striped table-bordered table-hover">
                        <tbody>
                        <tr role="row" class="heading">
                            <th>موبایل</th>
                            <th>تاریخ</th>
                            <th>طول مکالمه</th>
                        </tr>
                        @foreach($item as $it)
                            <tr role="row" class="filter">
                                <td>{{$it->mobile}}</td>
                                <td>{{jdate('d F Y ساعت H:i:s' , strtotime($it->created_at))}}</td>
                                <td>{{$it->duration.' دقیقه'}}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="clearfix"></div>

            </div>

        </div>
    @endif
@endsection
