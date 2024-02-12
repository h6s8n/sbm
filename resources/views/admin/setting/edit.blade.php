@extends('admin.layouts.app')

@section('page_name', 'ویرایش تنظیمات')

@section('content')


    <div class="white-box">

        <form method="post" action="" class="avatar" style="direction: rtl">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="system_profits_type" class="col-md-4 col-form-label text-md-right">نوع کسر کارمزد 	</label>

                <div class="col-md-6">
                    <select id="system_profits_type" class="form-control{{ $errors->has('system_profits_type') ? ' is-invalid' : '' }}" name="system_profits_type">
                        <option value="percentage" {{ (old('doctor', $system_profits_type) == 'percentage') ? 'selected' : '' }}>درصد</option>
                        <option value="price" {{ (old('doctor', $system_profits_type) == 'price') ? 'selected' : '' }}>قیمت ثابت</option>
                    </select>

                    @if ($errors->has('system_profits_type'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('system_profits_type') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="system_profits" class="col-md-4 col-form-label text-md-right">قیمت یا درصد 	</label>

                <div class="col-md-6">
                    <input id="system_profits" type="number" class="form-control{{ $errors->has('system_profits') ? ' is-invalid' : '' }}" name="system_profits" value="{{ old('system_profits', $system_profits) }}" min="0">


                    @if ($errors->has('system_profits'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('system_profits') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت تنظیمات</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection