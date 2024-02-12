@extends('admin.layouts.app')

@section('page_name', 'ویرایش تخصص')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('specialization.update',$sp)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">عنوان تخصص </label>
                <div class="col-md-6">
                    <input id="name" type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}"
                           name="name" value="{{ old('name') ? old('name') : $sp->name}}">
                    @if ($errors->has('name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="slug" class="col-md-4 col-form-label text-md-right">نامک </label>
                <div class="col-md-6">
                    <input id="slug" type="text" class="form-control {{ $errors->has('slug') ? ' is-invalid' : '' }}"
                           name="slug" value="{{ old('slug') ? old('slug') : $sp->slug}}">
                    @if ($errors->has('slug'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('slug') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="svg_url" class="col-md-4 col-form-label text-md-right">آیکون</label>
                <div class="col-xs-9 col-md-6">
                    <input id="svg_url" type="file" class="form-control{{ $errors->has('svg_url') ? ' is-invalid' : '' }}" name="svg_url" >

                    @if ($errors->has('svg_url'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('svg_url') }}</strong>
                        </span>
                    @endif
                </div>
                <div class="col-xs-3 col-md-2">
                    @if($sp->svg_url)
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="{{ $sp->svg_url }}" target="_blank">مشاهده تصویر</a>
                    @else
                        <a style="margin-top: 11px; display: inline-block; font-size: 14px;" href="javascript:void(0)">تصویر موجود ندارد</a>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="priority" class="col-md-4 col-form-label text-md-right">اولویت </label>
                <div class="col-md-6">
                    <input id="priority" type="text"
                           class="form-control {{ $errors->has('priority') ? ' is-invalid' : '' }}"
                           name="priority" value="{{ old('priority',$sp->priority)}}">
                    @if($errors->has('priority'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('priority') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="brief" class="col-md-4 col-form-label text-md-right">توضیح کوتاه </label>
                <div class="col-md-6">
                    <input id="brief" type="text"
                              class="form-control {{ $errors->has('brief') ? ' is-invalid' : '' }}"
                              name="brief" value="{{ old('brief',$sp->brief)}}">
                    @if($errors->has('brief'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('brief') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-md-4 col-form-label text-md-right">توضیحات </label>
                <div class="col-md-6">
                    <textarea id="description" type="text"
                              class="form-control {{ $errors->has('description') ? ' is-invalid' : '' }}"
                              name="description">{{ old('description',$sp->description)}}</textarea>
                    @if($errors->has('description'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('description') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="items" class="col-md-4 col-form-label text-md-right">موارد جستجو </label>
                <div class="col-md-6">
                    <input id="items" type="text" rows="5" data-role="tagsinput"
                              class="form-control {{ $errors->has('items') ? ' is-invalid' : '' }}"
                              name="items" value="{{ old('items',$sp->SearchArea()->first() ? $sp->SearchArea()->first()->items : '')}}">
                    @if($errors->has('items'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('items') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت ویرایش</button>
                    <a class="btn btn-danger waves-effect waves-light"
                       href="{{route('specialization.index')}}"> بازگشت</a>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>
@endsection
