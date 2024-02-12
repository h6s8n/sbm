@extends('admin.layouts.app')
@section('header')
    {{--    <script src="https://cdn.ckeditor.com/4.16.2/standard-all/ckeditor.js"></script>--}}
    <script src="https://cdn.ckeditor.com/4.16.2/full-all/ckeditor.js"></script>
    <style>
        div#cke_1_contents {
            height: 400px !important;
        }
    </style>
@stop
@section('page_name', 'ثبت قرارداد جدید برای دکتر ' . $doctor->fullname)
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet"/>

@section('content')

    <div class="white-box">

        <form method="post" enctype="multipart/form-data" action="" class="avatar" style="direction: rtl">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="doctor_nickname" class="col-md-4 col-form-label text-md-right">شماره ثبت</label>

                <div class="col-md-6">
                    <input id="registration_id" type="text"
                           class="form-control{{ $errors->has('registration_id') ? ' is-invalid' : '' }}"
                           name="registration_id" value="{{ old('registration_id') }}">

                    @if ($errors->has('registration_id'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('registration_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="terminal_id" class="col-md-4 col-form-label text-md-right">شماره ترمینال(مخصوص کارتخوان)</label>

                <div class="col-md-6">
                    <input id="terminal_id" type="text"
                           class="form-control{{ $errors->has('terminal_id') ? ' is-invalid' : '' }}"
                           name="terminal_id" value="{{ old('terminal_id') }}">

                    @if ($errors->has('terminal_id'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('terminal_id') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="contract_type" class="col-md-4 col-form-label text-md-right">نوع قرارداد</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="real" name="contract_type"
                                   value="real" {{ ( old('contract_type') == 'real') ? "checked" : "" }}>
                            <label for="real">حقیقی</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="legal" name="contract_type"
                                   value="legal" {{ ( old('contract_type') == 'legal') ? "checked" : "" }}>
                            <label for="legal">حقوقی</label>
                        </div>
                    </div>
                    @if ($errors->has('contract_type'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('contract_type') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="category" class="col-md-4 col-form-label text-md-right">دسته بندی</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="wallet" name="category"
                                   value="wallet" {{ ( old('category') == 'wallet') ? "checked" : "" }}>
                            <label for="wallet">کیف پول</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="visit" name="category"
                                   value="visit" {{ ( old('category') == 'visit') ? "checked" : "" }}>
                            <label for="visit">ویزیت</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="cod" name="category"
                                   value="cod" {{ ( old('category') == 'cod') ? "checked" : "" }}>
                            <label for="cod">کارتخوان</label>
                        </div>
                    </div>
                    @if ($errors->has('category'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('category') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="start_at" class="col-md-4 col-form-label text-md-right">تاریخ شروع	</label>
                <div class="col-md-6">
                    <input id="start_at" type="text"
                           class="form-control{{ $errors->has('start_at') ? ' is-invalid' : '' }}
                               observer"
                           name="start_at">

                    @if ($errors->has('start_at'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('start_at') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="expire_at" class="col-md-4 col-form-label text-md-right">تاریخ پایان	</label>
                <div class="col-md-6">
                    <input id="expire_at" type="text"
                           class="form-control{{ $errors->has('expire_at') ? ' is-invalid' : '' }}
                               observer"
                           name="expire_at">

                    @if ($errors->has('expire_at'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('expire_at') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="percent" class="col-md-4 col-form-label text-md-right">کارمزد خدمت (درصد) </label>

                <div class="col-md-6">
                    <input id="percent" type="text" placeholder="مثال: 0.01 برای یک درصد" required class="form-control{{ $errors->has('percent') ? ' is-invalid' : '' }}"
                           name="percent" value="{{ old('percent') }}" autofocus>

                    @if ($errors->has('percent'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('percent') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

{{--            <div class="form-group row">--}}
{{--                <label for="wallet_secretary_name" class="col-md-4 col-form-label text-md-right">نام منشی</label>--}}

{{--                <div class="col-md-6">--}}
{{--                    <input id="wallet_secretary_name" type="text"--}}
{{--                           class="form-control{{ $errors->has('wallet_secretary_name') ? ' is-invalid' : '' }}" name="wallet_secretary_name"--}}
{{--                           value="{{ old('wallet_secretary_name' }}">--}}

{{--                    @if ($errors->has('wallet_secretary_name'))--}}
{{--                        <span class="invalid-feedback">--}}
{{--                            <strong>{{ $errors->first('wallet_secretary_name') }}</strong>--}}
{{--                        </span>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}
{{--            <div class="form-group row">--}}
{{--                <label for="wallet_secretary_mobile" class="col-md-4 col-form-label text-md-right">شماره موبایل منشی</label>--}}

{{--                <div class="col-md-6">--}}
{{--                    <input id="wallet_secretary_mobile" type="text"--}}
{{--                           class="form-control{{ $errors->has('wallet_secretary_mobile') ? ' is-invalid' : '' }}" name="wallet_secretary_mobile"--}}
{{--                           value="{{ old('wallet_secretary_mobile') }}">--}}

{{--                    @if ($errors->has('wallet_secretary_mobile'))--}}
{{--                        <span class="invalid-feedback">--}}
{{--                            <strong>{{ $errors->first('wallet_secretary_mobile') }}</strong>--}}
{{--                        </span>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="picture" class="col-md-4 col-form-label text-md-right">تصویر مدرک</label>

                <div class="col-xs-9 col-md-4">
                    <div class="form-group">
                        <input id="picture" type="file"
                               class="form-control{{ $errors->has('picture') ? ' is-invalid' : '' }}" name="picture">

                    </div>
                    @if ($errors->has('picture'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('picture') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="sign_picture" class="col-md-4 col-form-label text-md-right">تصویر امضاء
                    </label>

                <div class="col-xs-9 col-md-6">
                    <input id="sign_picture" type="file"
                           class="form-control{{ $errors->has('sign_picture') ? ' is-invalid' : '' }}"
                           name="sign_picture">

                    @if ($errors->has('sign_picture'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('sign_picture') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت قرارداد</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>

    <script>
        // ClassicEditor.create( document.querySelector( '#content' ) )
        //     .catch( error => {
        //         console.error( error );
        //     } );
        CKEDITOR.replace( 'body', {
            contentsLangDirection: 'rtl',
        });
        CKEDITOR.config.contentsCss = '{{asset('css/my-editor.css')}}'

    </script>
@endsection
