@extends('admin.layouts.app')

@section('page_name', 'ویرایش نظر')

@section('content')

    <div class="white-box">
        <form method="post" action="{{route('comment.update',$comment)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <label for="description" class="col-md-4 col-form-label text-md-right">نظر </label>
            <div class="col-md-12" style="margin-bottom: 5px">
                    <textarea id="comment" type="text"
                              class="form-control {{ $errors->has('comment') ? ' is-invalid' : '' }}"
                              name="comment">{{ old('comment',$comment->comment) }}</textarea>
                @if($errors->has('comment'))
                    <span class="invalid-feedback">
                            <strong>{{ $errors->first('comment') }}</strong>
                        </span>
                @endif
            </div>
            <div class="row">
                <div class="form-group col-md-3">
                    <label for="flag_status">وضعیت</label>
                    <select id="flag_status" class="form-control" name="flag">
                        <option value="0" {{$comment->flag ==0 ? 'selected' : ''}}>در انتظار بررسی</option>
                        <option value="1" {{$comment->flag ==1 ? 'selected' : ''}}>تایید شده ها</option>
                        <option value="2" {{$comment->flag ==2 ? 'selected' : ''}}>رد شده</option>
                        <option value="3" {{$comment->flag ==3 ? 'selected' : ''}}>پاسخ داده شده</option>
                    </select>
{{--                </div>--}}

{{--                <div class="form-group col-md-6 mb-0">--}}
                    <div style="margin-top: 5px">
                        <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                    </div>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>

@endsection
