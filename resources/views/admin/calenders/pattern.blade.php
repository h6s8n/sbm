@extends('admin.layouts.app')

@section('page_name', 'برنامه پزشکان')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@section('content')
    <div class="white-box">
        <form method="get">
        <div class="portlet-body">
            <div class="form-group row">
                <label for="doctor" class="col-md-2 col-xl-2">نام پزشک </label>
                <div class="col-md-4 col-xl-4">
                    <select id="doctor"
                            class="js-example-basic-multiple form-control{{ $errors->has('doctor') ? ' is-invalid' : '' }}"
                            name="doctor">
                        @foreach($users as $doctor)
                            <option value="{{ $doctor['id'] }}"
                                {{ (old('doctor') == $doctor['id']) ? 'selected' : '' }}>{{ $doctor['fullname'] }}</option>
                        @endforeach
                    </select>

                    @if ($errors->has('doctor'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('doctor') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-md-6 offset-md-4 col-xl-6">
                    <button type="submit" class="btn btn-info waves-effect waves-light">جستجو</button>
                </div>
            </div>
        </div>
        </form>
    </div>

    @if($patterns)
    <div class="white-box">
        <div class="portlet-body">
            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>نام و نام خانوادگی</th>
                        <th>روزها</th>
                        <th>ساعت ها</th>
                        <th>مبلغ</th>
                        <th>به مدت</th>
                        <th>نوع ویزیت</th>
                        <th>ظرفیت</th>
                        <th>اعمال</th>
                    </tr>
                    {{--                    {{dd(\Carbon\Carbon::getDays())}}--}}
                    @foreach($patterns as $pattern)
                        <tr role="row" class="filter">
                            <td>{{$pattern->doctor->fullname}}</td>
                            <td>
                                @php $days = json_decode($pattern->selectedWeekDays,true)@endphp
                                @foreach($days as $day)
                                    {{$day['label'].', '}}
                                @endforeach
                            </td>
                            <td>
                                @php $days = json_decode($pattern->selectedTime,true)@endphp
                                @foreach($days as $day)
                                    {{$day['label'].', '}}
                                @endforeach
                            </td>
                            <td>{{number_format($pattern->price)}}</td>
                            <td>{{$pattern->duration.' هفته'}}</td>
                            <td>{{\App\Enums\VisitTypeEnum::name($pattern->type)}}</td>
                            <td>{{($pattern->selectedCapacity)}}</td>
                        </tr>
                    @endforeach

                </table>
            </div>
        </div>
    </div>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script>
        $(document).ready(function () {
            $('.js-example-basic-multiple').select2();
        });
    </script>
@endsection
