@extends('admin.layouts.app')

@section('page_name', 'افزودن منشی جدید')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('secretary.store',$user->id)}}" class="avatar" style="direction: rtl">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="full_name" class="col-md-4 col-form-label text-md-right">نام کامل منشی</label>
                <div class="col-md-6">
                    <input id="full_name" type="text" class="form-control {{ $errors->has('full_name') ? ' is-invalid' : '' }}" name="full_name" value="{{ old('full_name') }}">
                    @if ($errors->has('full_name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('full_name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="mobile" class="col-md-4 col-form-label text-md-right">موبایل </label>
                <div class="col-md-6">
                    <input id="mobile" type="text" class="form-control {{ $errors->has('mobile') ? ' is-invalid' : '' }}" name="mobile" value="{{ old('mobile') }}">
                    @if ($errors->has('mobile'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('mobile') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-md-4 col-form-label text-md-right">یوزرنیم دکتر</label>
                <div class="col-md-6">
                    <input id="username" name="username" type="text"
                              class="form-control" value="{{$user->mobile}}" readonly/>
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-md-4 col-form-label text-md-right">پسورد ورود</label>
                <div class="col-md-6">
                    <input id="password" type="text"
                              class="form-control {{ $errors->has('password') ? ' is-invalid' : '' }}"
                              name="password" />
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت منشی</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
