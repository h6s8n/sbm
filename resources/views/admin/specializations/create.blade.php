@extends('admin.layouts.app')

@section('page_name', 'افزودن تخصص جدید')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('specialization.store')}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="language_id" class="col-md-4 col-form-label text-md-right">زبان </label>
                <div class="col-md-6">
                    <select id="language_id" type="text" class="form-control {{ $errors->has('language_id') ? ' is-invalid' : '' }}"
                            name="language_id">
                        @foreach($languages as $language)
                            <option value="{{$language->id}}">{{$language->name}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">عنوان تخصص </label>
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
                <label for="slug" class="col-md-4 col-form-label text-md-right">نامک </label>
                <div class="col-md-6">
                    <input id="slug" type="text" class="form-control {{ $errors->has('slug') ? ' is-invalid' : '' }}" name="slug" value="{{ old('slug') }}">
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
            </div>
            <div class="form-group row">
                <label for="description" class="col-md-4 col-form-label text-md-right">توضیحات </label>
                <div class="col-md-6">
                    <textarea id="description" type="text"
                              class="form-control {{ $errors->has('description') ? ' is-invalid' : '' }}"
                              name="description">{{ old('description') }}</textarea>
                    @if($errors->has('description'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('description') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت تخصص</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
