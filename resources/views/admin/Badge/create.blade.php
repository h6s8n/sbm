@extends('admin.layouts.app')

@section('page_name', 'افزودن نشان جدید')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('badge.store')}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">عنوان نشان </label>
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
                <label for="priority" class="col-md-4 col-form-label text-md-right">هزینه </label>
                <div class="col-md-6">
                    <input id="priority" type="text" class="form-control {{ $errors->has('priority') ? ' is-invalid' : '' }}"
                           name="priority" value="{{ old('priority') }}">
                    @if ($errors->has('priority'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('priority') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="icon" class="col-md-4 col-form-label text-md-right">آیکون</label>

                <div class="col-xs-9 col-md-6">
                    <input id="icon" type="file" class="form-control{{ $errors->has('icon') ? ' is-invalid' : '' }}" name="icon" >

                    @if ($errors->has('icon'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('icon') }}</strong>
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
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت نشان</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
