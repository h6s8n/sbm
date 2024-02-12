@extends('admin.layouts.app')

@section('page_name', 'افزودن مهارت جدید')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('skill.store')}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">عنوان مهارت </label>
                <div class="col-md-6">
                    <input id="name" type="text" class="form-control {{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}">
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
                    <input id="sience_name" type="text" class="form-control {{ $errors->has('sience_name') ? ' is-invalid' : '' }}" name="sience_name" value="{{ old('sience_name') }}">
                    @if ($errors->has('sience_name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('sience_name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="info" class="col-md-4 col-form-label text-md-right">توضیحات </label>
                <div class="col-md-6">
                    <textarea id="info" type="text"
                              class="form-control {{ $errors->has('info') ? ' is-invalid' : '' }}"
                              name="info">{{ old('info') }}</textarea>
                    @if($errors->has('info'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('info') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت مهارت</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
