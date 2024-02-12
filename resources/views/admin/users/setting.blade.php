@extends('admin.layouts.app')

@section('page_name', 'تنظیمات  کاربر')
<style>
    .setting-checkbox{
        display: flex; align-items: center; justify-content: space-between; flex-direction: row-reverse;
        flex-grow: 1; min-width: 210px; background-color: #eee; background-image: linear-gradient(
        45deg
        , #2e70f955, #2e70f933);
        color: #124abd;
        box-shadow: 0 0 1px 0 #fff7, 0 3px 7px -3px #2e70f966;
        padding: 10px; border-radius: 7px; margin: 5px
    }
    .div-setting{
        display: flex;justify-content: space-between; flex-wrap: wrap
    }
</style>
@section('content')
    <div class="white-box">

        <form method="post" action="" style="direction: rtl">
            {{ csrf_field() }}
            <div class="form-group row">
                <h4>
                تنظیمات دکتر:
                </h4>
                <div class="div-setting">
                @foreach($doctor_settings as $setting)
                    <label class="setting-checkbox">
                        <input type="checkbox" name="setting_doctor[]" value="{{$setting->id}}" style="margin-top: 0"
                            {{$user->hasSetting($setting->id) ? 'checked' : ''}}> {{$setting->display_name}}
                    </label>
                @endforeach
                </div>
            </div>

            <div class="form-group row">
                <h4>
                    تنظیمات منشی:
                </h4>
                <div class="div-setting">
                    @foreach($secretary_settings as $setting)
                        <label class="setting-checkbox">
                            <input type="checkbox" name="setting_secretary[]" value="{{$setting->id}}" style="margin-top: 0"
                                {{$user->hasSetting($setting->id) ? 'checked' : ''}}> {{$setting->display_name}}
                        </label>
                    @endforeach
                </div>
            </div>
            <div class="clearfix"></div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                </div>
            </div>
        </form>

    </div>


@endsection
