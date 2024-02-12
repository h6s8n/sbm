@extends('admin.layouts.app')

@section('header')
    <script src="https://cdn.ckeditor.com/4.14.1/standard/ckeditor.js"></script>
    <style>
        div#cke_1_contents {
            height: 400px !important;
        }
    </style>
@stop
@section('page_name', 'جزییات پزشکان')

@section('content')
    <div class="white-box">
        <form method="post" action="{{route('doctor.detail.store',$id)}}">
            {{csrf_field()}}
            <div class="form-group row">
                <label for="title" class="col-md-4 col-form-label text-md-right">عنوان</label>
                <div class="col-md-12">
                    <input id="title" type="text" class="form-control{{ $errors->has('title') ? ' is-invalid' : '' }}"
                           name="title" value="{{$request ? $request->title:''}}">
                    @if ($errors->has('title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('title') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="video_url" class="col-md-4 col-form-label text-md-right">لینک ویدیو</label>
                <div class="col-md-12">
                    <input id="video_url" type="text" class="form-control{{ $errors->has('video_url') ? ' is-invalid' : '' }}"
                           name="video_url" value="{{$request ? $request->video_url:''}}">
                    @if ($errors->has('video_url'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('video_url') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="description" class="col-md-4 col-form-label text-md-right">توضیحات</label>
                <div class="col-md-12">
                    <textarea rows="5" id="description" type="text"
                              class="form-control{{ $errors->has('description') ? ' is-invalid' : '' }}"
                              name="description">{{$request ? $request->description:''}}</textarea>
                    @if ($errors->has('description'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('description') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            <div class="form-group row">
                <label for="content" class="col-md-4 col-form-label text-md-right">محتوا</label>
                <div class="col-md-12">
                    <textarea name="content" id="content" class="cke_rtl">

                        {{$request ? $request->content :''}}
                    </textarea>
                    @if ($errors->has('content'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('content') }}</strong>
                        </span>
                    @endif
                </div>
            </div>
            @php $i=0; @endphp
            @if($faqs && !$faqs->isEmpty())
                @for($i = 0;$i<$faqs->count();$i++)
                    <div class="form-group row">
                        <label for="question[]" class="col-md-4 col-form-label text-md-right">سوال {{$i+1}}</label>
                        <div class="col-md-12">
                            <input id="question[]" type="text"
                                   class="form-control{{ $errors->has('question'.$i) ? ' is-invalid' : '' }}"
                                   name="question[]" value="{{$faqs[$i] ? $faqs[$i]->question:''}}">
                            @if ($errors->has('question'))
                                <span class="invalid-feedback">
                            <strong>{{ $errors->first('question'.$i) }}</strong>
                        </span>
                            @endif
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="answer[]" class="col-md-4 col-form-label text-md-right">جواب {{$i+1}}</label>
                        <div class="col-md-12">
                        <textarea id="answer[]" type="text"
                                  class="form-control{{ $errors->has('answer'.$i) ? ' is-invalid' : '' }}"
                                  name="answer[]">{{$faqs[$i]->answer}}</textarea>
                            @if ($errors->has('answer'.$i))
                                <span class="invalid-feedback">
                            <strong>{{ $errors->first('answer'.$i) }}</strong>
                        </span>
                            @endif
                        </div>
                    </div>
                @endfor
            @endif
            @for($i;$i<4;$i++)
                <div class="form-group row">
                    <label for="question[]" class="col-md-4 col-form-label text-md-right">سوال {{$i+1}}</label>
                    <div class="col-md-12">
                        <input id="question[]" type="text"
                               class="form-control{{ $errors->has('question'.$i) ? ' is-invalid' : '' }}"
                               name="question[]" value="">
                        @if ($errors->has('question'))
                            <span class="invalid-feedback">
                            <strong>{{ $errors->first('question'.$i) }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
                <div class="form-group row">
                    <label for="answer[]" class="col-md-4 col-form-label text-md-right">جواب {{$i+1}}</label>
                    <div class="col-md-12">
                        <textarea id="answer[]" type="text"
                                  class="form-control{{ $errors->has('answer'.$i) ? ' is-invalid' : '' }}"
                                  name="answer[]"></textarea>
                        @if ($errors->has('answer'))
                            <span class="invalid-feedback">
                            <strong>{{ $errors->first('answer') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>
            @endfor
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
