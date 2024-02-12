@extends('admin.layouts.app')

@section('page_name', 'لیست تخصص ها')

@section('content')
    <form id="myForm" class="filter_list" method="get" action="{{route('triage.index')}}" style="direction: rtl">

        <div class="col-md-3 col-xs-12">
            <div class="form-group">
                <label for="called">وضعیت </label>
                <select id="called" class="form-control" name="called">
                    <option value="-1">همه</option>
                    <option value="0" {{ (@$_GET['called'] == '0') ? 'selected' : '' }}>تماس گرفته نشده</option>
                    <option value="1" {{ (@$_GET['called'] == '1') ? 'selected' : '' }}>تماس گرفته شده</option>
                </select>
            </div>
        </div>
        <div class="col-sm-2 col-xs-12" style="margin-top: 25px;">
            <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
        </div>
        <div class="clearfix"></div>

    </form>

    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th></th>
                        <th>شماره تماس</th>
                        <th>تاریخ درخواست</th>
                        <th>وضعیت</th>
                        <th>نتیجه</th>
                        <th>اعمال</th>
                    </tr>
                    @php $i = 1; @endphp
                    @foreach($triages as $tr)
                        <tr role="row" class="filter">
                            <td>{{$i}}</td>
                            <td>{{$tr->mobile}}</td>
                            <td>{{(jdate('d F Y ساعت H:i', strtotime($tr->created_at)))}}</td>
                            <td>{{$tr->called ? 'تماس گرفته شده' : 'تماس گرفته نشده'}}</td>
                            <td>{{$tr->description ? $tr->description : '---'}}</td>
                            <td>
                                @if(!$tr->called)
                                    <div class="col-xs-6">
                                        <a class="btn btn-block btn-primary btn-rounded request_but"
                                           href="{{route('triage.edit',$tr->id)}}">تماس برقرار شد</a>
                                    </div>
                                @endif
                            </td>
                        </tr>
                        @php $i = $i +1;@endphp
                    @endforeach

                </table>
            </div>
            {{--            {!! $specializations->render() !!}--}}
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
