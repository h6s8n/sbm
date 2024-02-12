@extends('admin.layouts.app')

@section('page_name', ' لیست لاگ ها ')

@section('content')
    <form method="post">
        {{ csrf_field() }}

        <div class="white-box">

            <div class="portlet-body">

                @if(count($logs) > 0)
                    <div class="table-container">

                        <table class="table table-striped table-bordered table-hover">
                            <tbody>
                            <tr role="row" class="heading">
                                <th></th>
                                <th>نام پزشک</th>
                                <th>نام بیمار</th>
                                <th>تاریخ ویزیت</th>
                                <th>عامل</th>
                                <th>توضیحات</th>
                                <th>تاریخ</th>
                            </tr>
                            @php $row_count =1 @endphp
                            @foreach($logs as $log)
                                <tr role="row" class="filter">
                                    <td>{{$row_count}}</td>
                                    <td>{{$log->visit->doctor->fullname}}</td>
                                    <td>{{$log->visit->user->fullname}}</td>
                                    <td>{{jdate('Y-m-d',strtotime($log->visit->reserve_time))}}</td>
                                    <td>{{$log->user->fullname}}</td>
                                    <td>{{\App\Enums\VisitLogEnum::Message($log->action_type)}}</td>
                                    <td>{{jdate('d F Y ساعت H:i',strtotime($log->created_at))}}</td>
                                </tr>
                                @php $row_count = $row_count +1;@endphp
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    {!! $logs->appends(\Illuminate\Support\Facades\Input::except('page'))->links() !!}
                    <div class="clearfix"></div>

                @else

                    <div class="msg">
                        <div class="alert alert-warning alert-info" style="text-align: center">لاگی یافت نشد.</div>
                    </div>

                @endif


            </div>

        </div>
    </form>

    {{--    <script>--}}
    {{--        $('.pay-warning').click(function(e){--}}
    {{--            e.preventDefault();--}}
    {{--            var hreflink = $(this).attr('href');--}}
    {{--            var key = $(this).attr('data-key');--}}
    {{--            swal({--}}
    {{--                title: "",--}}
    {{--                text: "آیا از تسویه حساب با "+key+" مطمئن هستید ؟ ",--}}
    {{--                type: "warning",--}}
    {{--                showCancelButton: true,--}}
    {{--                confirmButtonColor: "#DD6B55",--}}
    {{--                cancelButtonText: "خیر نیازی نیست",--}}
    {{--                confirmButtonText: "بله اطمینان دارم",--}}
    {{--                closeOnConfirm: false--}}
    {{--            }, function(){--}}
    {{--                window.location.href = hreflink;--}}
    {{--            });--}}
    {{--        });--}}

    {{--        let exportList = () =>{--}}
    {{--            let form = document.getElementById('myForm');--}}
    {{--            form.action = '{{route('export.bill.list')}}';--}}
    {{--            form.submit();--}}
    {{--        }--}}

    {{--    </script>--}}

@endsection
