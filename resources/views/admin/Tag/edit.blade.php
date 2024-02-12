
@extends('admin.layouts.app')

@section('page_name', 'تگ های دکتر')

@section('content')

    <div class="white-box">
        <div class="alert alert-success">
            <ul>
                <li>
                  در این قسمت فقط حالات مختلف نام دکتر و موارد اختصاصی این دکتر وارد شود
                </li>
                <li>
                    لطفا فقط کلمه کلیدی مد نظر را وارد کنید
                </li>
                <li>
                   علایم بیماری در تگ های تخصص ها وارد شوند
                </li>
            </ul>
        </div>
        <form method="post" action="{{route('tag.update',$doctor)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <label for="items" class="col-md-4 col-form-label text-md-right">موارد جستجو </label>
                <div class="col-md-6">
                    <input id="items" type="text" rows="5" data-role="tagsinput"
                           class="form-control {{ $errors->has('items') ? ' is-invalid' : '' }}"
                           name="items" value="{{ old('items',$doctor->SearchArea()->first() ? $doctor->SearchArea()->first()->items : '')}}">
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
                       href="{{route('doctors.index')}}"> بازگشت</a>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>
@endsection
