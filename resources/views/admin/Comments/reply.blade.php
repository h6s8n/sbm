@extends('admin.layouts.app')

@section('page_name', 'پاسخ نظرات')

@section('content')

    <div class="white-box">
        <form method="post" action="{{route('comment.update',$comment)}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
                <label for="description" class="col-md-4 col-form-label text-md-right">نظر </label>
                <div class="col-md-12">
                    <textarea id="comment" type="text"
                              class="form-control {{ $errors->has('comment') ? ' is-invalid' : '' }}"
                              name="comment">{{ old('comment',$comment->comment) }}</textarea>
                    @if($errors->has('comment'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('comment') }}</strong>
                        </span>
                    @endif
                </div>
            <label for="description" class="col-md-4 col-form-label text-md-right">پاسخ </label>
            <div class="col-md-12">
                    <textarea id="reply" type="text"
                              class="form-control {{ $errors->has('reply') ? ' is-invalid' : '' }}"
                              name="reply">{{ old('reply',$comment->reply) }}</textarea>
                @if($errors->has('reply'))
                    <span class="invalid-feedback">
                            <strong>{{ $errors->first('reply') }}</strong>
                        </span>
                @endif
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
