@extends('admin.layouts.app')

@section('page_name', 'ویرایش مهارت')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('skill.update',$skill)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">عنوان مهارت </label>
                <div class="col-md-6">
                    <input id="name" type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}"
                           name="name" value="{{ old('name') ?? $skill->name}}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="sience_name" class="col-md-4 col-form-label text-md-right">نامک </label>
                <div class="col-md-6">
                    <input id="sience_name" type="text" class="form-control {{ $errors->has('sience_name') ? ' is-invalid' : '' }}"
                           name="sience_name" value="{{ old('sience_name') ?? $skill->sience_name}}">
                    @if ($errors->has('sience_name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('sience_name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
{{--            <div class="form-group row">--}}
{{--                <label for="picture" class="col-md-4 col-form-label text-md-right">تصویر</label>--}}
{{--                <div class="col-xs-9 col-md-6">--}}
{{--                    <input id="svg_url" type="file" class="form-control{{ $errors->has('picture') ? ' is-invalid' : '' }}" name="picture" >--}}

{{--                    @if ($errors->has('picture'))--}}
{{--                        <span class="invalid-feedback">--}}
{{--                            <strong>{{ $errors->first('picture') }}</strong>--}}
{{--                        </span>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--                <div class="col-xs-3 col-md-2">--}}
{{--                    @if($skill->picture)--}}
{{--                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="{{ $skill->picture }}" target="_blank">مشاهده تصویر</a>--}}
{{--                    @else--}}
{{--                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="javascript:void(0)">تصویر موجود ندارد</a>--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            </div>--}}
            <div class="form-group row">
                <label for="info" class="col-md-4 col-form-label text-md-right">توضیحات </label>
                <div class="col-md-6">
                    <textarea id="info" type="text"
                              class="form-control {{ $errors->has('info') ? ' is-invalid' : '' }}"
                              name="description">{{ old('info',$skill->info)}}</textarea>
                    @if($errors->has('info'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('info') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت ویرایش</button>
                    <a class="btn btn-danger waves-effect waves-light"
                       href="{{route('skill.index')}}"> بازگشت</a>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>
@endsection
