@extends('admin.layouts.app')
@section('page_name', 'اظلاعات پزشکان')

@section('content')
    <div class="white-box">
        <form method="post" action="{{route('doctor.information.store',$user)}}">
            {{csrf_field()}}
            <div class="form-group row">
                <label for="office_secretary_name" class="col-md-4 col-form-label text-md-right">نام منشی مطب</label>
                <div class="col-md-12">
                    <input id="office_secretary_name" type="text" class="form-control{{ $errors->has('office_secretary_name') ? ' is-invalid' : '' }}"
                           name="office_secretary_name" value="{{$request ? $request->office_secretary_name:''}}">
                    @if ($errors->has('office_secretary_name'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('office_secretary_name') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="office_secretary_mobile" class="col-md-4 col-form-label text-md-right">شماره موبایل منشی</label>
                <div class="col-md-12">
                    <input id="office_secretary_mobile" type="text" class="form-control{{ $errors->has('office_secretary_mobile') ? ' is-invalid' : '' }}"
                           name="office_secretary_mobile" value="{{$request ? $request->office_secretary_mobile:''}}">
                    @if ($errors->has('office_secretary_mobile'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('office_secretary_mobile') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="permanent_comment" class="col-md-4 col-form-label text-md-right">توضیحات دایمی</label>
                <div class="col-md-12">
                    <textarea rows="5" id="permanent_comment" type="text"
                              class="form-control{{ $errors->has('permanent_comment') ? ' is-invalid' : '' }}"
                              name="permanent_comment">{{$request ? $request->permanent_comment:''}}</textarea>
                    @if ($errors->has('permanent_comment'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('permanent_comment') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="temporary_comment" class="col-md-4 col-form-label text-md-right">توضیحات موقتی</label>
                <div class="col-md-12">
                    <textarea rows="5" id="temporary_comment" type="text"
                              class="form-control{{ $errors->has('temporary_comment') ? ' is-invalid' : '' }}"
                              name="temporary_comment">{{$request ? $request->temporary_comment:''}}</textarea>
                    @if ($errors->has('temporary_comment'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('temporary_comment') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت</button>
                </div>
            </div>
        </form>
    </div>
    <script>
        // ClassicEditor.create( document.querySelector( '#content' ) )
        //     .catch( error => {
        //         console.error( error );
        //     } );
        CKEDITOR.replace( 'content', {
            contentsLangDirection: 'rtl'
        } );
        CKEDITOR.add
        CKEDITOR.config.contentsCss = '{{asset('css/my-editor.css')}}'

    </script>
@endsection
