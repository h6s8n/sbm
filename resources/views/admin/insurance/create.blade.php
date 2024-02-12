@extends('admin.layouts.app')

@section('page_name', 'افزودن بیمه جدید')
@section('content')

    <div class="white-box">

        <form method="post" action="{{route('insurance.store')}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="name">نام</label>
                    <input id="name" type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}"
                           name="name" value="{{ old('name') }}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <div class="white-box">

        <div class="portlet-body">

            <div class="table-container">

                <table class="table table-striped table-bordered table-hover">
                    <tr role="row" class="heading">
                        <th>نام</th>
                        <th>تاریخ</th>
                    </tr>

                    @foreach($insurances as $k => $item)
                        <tr role="row" class="filter">
                            <td>{{ ($item['name'])}}</td>
                            <td>{{ jdate('Y/m/d ساعت H:i:s', strtotime($item['created_at'])) }}</td>
                        </tr>
                    @endforeach

                </table>
            </div>
            <div class="clearfix"></div>
        </div>

    </div>
@endsection
