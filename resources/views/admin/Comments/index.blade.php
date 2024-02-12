@extends('admin.layouts.app')

@section('page_name', 'کامنت ها')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>
            <form id="myForm" class="filter_list" method="get" style="direction: rtl">
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="flag_status">وضعیت</label>
                        <select id="flag_status" class="form-control" name="flag_status">
                            <option value="" {{$flag_status == -1 ? 'selected' : ''}}>همه</option>
                            <option value="0" {{$flag_status==0 ? 'selected' : ''}}>در انتظار بررسی</option>
                            <option value="1" {{$flag_status ==1 ? 'selected' : ''}}>تایید شده ها</option>
                            <option value="5" {{$flag_status ==5 ? 'selected' : ''}}>تایید امتیاز شده ها</option>
                            <option value="2" {{$flag_status ==2 ? 'selected' : ''}}>رد شده</option>
                            <option value="3" {{$flag_status ==3 ? 'selected' : ''}}>پاسخ داده شده</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="dr_name">نام دکتر</label>
                        <input type="text" class="form-control"
                               name="dr_name"
                               id="dr_name"
                               value="{{ @$_GET['dr_name'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="from"> از تاریخ</label>
                        <input type="text" class="form-control observer" id="from"
                               name="from"
                               value="{{ @$_GET['from'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="to"> تا تاریخ</label>
                        <input type="text" class="form-control observer"
                               id="to"
                               name="to"
                               value="{{ @$_GET['to'] }}">
                    </div>
                </div>
                <div class="clearfix"></div>
                <div class="col-sm-2 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
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
                            <th> پزشک</th>
                            <th> بیمار</th>
                            <th>کامنت</th>
                            <th>تاریخ</th>
                            <th> امتیاز</th>
                            <th> وضعیت</th>
                            <th>اعمال</th>
                        </tr>
                        @php $row_count =1 @endphp
                        @foreach($comments as $comment)
                            <tr role="row" class="filter">
                                <td>{{$row_count}}</td>
                                <td>{{$comment->votable()->first()->fullname}}</td>
                                <td>{{(trim($comment->user->fullname) ? $comment->user->fullname : 'بدون نام').' - '.$comment->user->mobile}}</td>
                                <td style="width: 40%">
                                    {{$comment->comment}}
                                    @if($comment->reply)
                                        <hr>
                                        {{'پاسخ: '}}
                                        {{$comment->reply}}
                                    @endif
                                </td>
                                <td>{{\Hekmatinasser\Verta\Verta::instance($comment->created_at)->format('Y-m-d , H:i:s')}} </td>
                                <td>{{$comment->overall}}</td>
                                <td>
                                    @switch($comment->flag)
                                        @case(0)
                                        در حال بررسی
                                        @break
                                        @case(1)
                                        تایید شده
                                        @break
                                        @case(2)
                                        رد شده
                                        @break
                                        @case(5)
                                        امتیاز تایید شده
                                        @break
                                    @endswitch
                                </td>
                                <td>
                                    <div>
                                        @if($comment->flag == 0)
                                            <div class="col-xs-6" style="margin-bottom: 5px">
                                                <a class="btn btn-block btn-success btn-rounded request_but"
                                                   href="{{route('comment.confirm',$comment->id)}}">تایید</a>
                                            </div>
                                            <div class="col-xs-6" style="margin-bottom: 5px">
                                                <a class="btn btn-block btn-success btn-rounded request_but"
                                                   href="{{route('comment.rate_confirm',$comment->id)}}">تایید امتیاز</a>
                                            </div>
                                            <div class="col-xs-6" style="margin-bottom: 5px">
                                                <a class="btn btn-block btn-danger btn-rounded request_but"
                                                   href="{{route('comment.reject',$comment->id)}}">رد</a>
                                            </div>
                                            <div class="col-xs-6" style="margin-bottom: 5px">
                                                <a class="btn btn-block btn-primary btn-rounded request_but"
                                                   href="{{route('comment.reply',$comment)}}">پاسخ</a>
                                            </div>
                                            <div class="col-xs-6" style="margin-bottom: 5px">
                                                <a class="btn btn-block btn-warning btn-rounded request_but"
                                                   href="{{route('comment.edit',$comment)}}">ویرایش</a>
                                            </div>

                                        @elseif($comment->flag == 1 || $comment->flag == 5)
                                            <div class="col-xs-12">
                                                <a class="btn btn-block btn-danger btn-rounded request_but"
                                                   href="{{route('comment.reject',$comment->id)}}">تغییر به رد شده</a>
                                            </div>
                                            @if($comment->reply)
                                                <div class="col-xs-12">
                                                    <a class="btn btn-block btn-primary btn-rounded request_but"
                                                       href="{{route('comment.reply',$comment)}}">ویرایش پاسخ</a>
                                                </div>
                                            @endif
                                        @elseif($comment->flag == 2)
                                            <div class="col-xs-12">
                                                <a class="btn btn-block btn-success btn-rounded request_but"
                                                   href="{{route('comment.confirm',$comment->id)}}">تغییر به تایید
                                                    شده</a>
                                            </div>
                                        @endif
                                            <div class="col-xs-6" style="margin-top: 5px">
                                                <a class="btn btn-block btn-success btn-rounded request_but" target="_blank"
                                                   href={{"https://web.whatsapp.com/send/?phone=".StandardNumber($comment->votable()->first()->mobile)."&text=".urlencode($comment->comment)."&app_absent=0"}}>
                                                    واتس اپ</a>
                                                <hr>
                                            </div>
                                    </div>
                                </td>
                            </tr>
                            @php $row_count = $row_count +1;@endphp
                        @endforeach
                        </tbody>
                    </table>
                </div>
                {!! $comments->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                <div class="clearfix"></div>

            </div>

        </div>
    </form>

@endsection
