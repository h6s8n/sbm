@extends('admin.layouts.app')

@section('page_name', 'درخواست های ثبت مراکز')

@section('content')

    <div class="white-box">

        <form method="post" action="{{route('registration-request.update',$request)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            {{ method_field('PUT') }}
            <div class="form-group row">
                <label for="description" class="col-md-4 col-form-label text-md-right">توضیحات</label>
                <div class="col-md-12">
                    <textarea id="description" type="text" class="form-control {{ $errors->has('description') ? ' is-invalid' : '' }}"
                              name="description" maxlength="250"></textarea>
                    @if ($errors->has('description'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('description') }}</strong>
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

@endsection
