@extends('admin.layouts.app')

@section('page_name', 'افزودن کاربر')

@section('content')

    <div class="white-box">

        <form method="post" action="" enctype="multipart/form-data" class="avatar" style="direction: rtl">
            {{ csrf_field() }}

            <div class="form-group row">
                <label for="name" class="col-md-4 col-form-label text-md-right">نام	</label>

                <div class="col-md-6">
                    <input id="name" type="text" class="form-control{{ $errors->has('name') ? ' is-invalid' : '' }}" name="name" value="{{ old('name') }}" autofocus>

                    @if ($errors->has('name'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('name') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="family" class="col-md-4 col-form-label text-md-right">نام خانوادگی</label>

                <div class="col-md-6">
                    <input id="family" type="text" class="form-control{{ $errors->has('family') ? ' is-invalid' : '' }}" name="family" value="{{ old('family') }}">

                    @if ($errors->has('family'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('family') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="email" class="col-md-4 col-form-label text-md-right">ایمیل</label>

                <div class="col-md-6">
                    <input id="email" type="text" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}" name="email" value="{{ old('email') }}" >

                    @if ($errors->has('email'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('email') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="mobile" class="col-md-4 col-form-label text-md-right">شماره موبایل</label>

                <div class="col-md-6">
                    <input id="mobile" type="text" class="form-control{{ $errors->has('mobile') ? ' is-invalid' : '' }}" name="mobile" value="{{ old('mobile') }}" >

                    @if ($errors->has('mobile'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('mobile') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="nationalcode" class="col-md-4 col-form-label text-md-right">کد ملی</label>

                <div class="col-md-6">
                    <input id="nationalcode" type="text" class="form-control{{ $errors->has('nationalcode') ? ' is-invalid' : '' }}" name="nationalcode" value="{{ old('nationalcode') }}" >

                    @if ($errors->has('nationalcode'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('nationalcode') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="picture" class="col-md-4 col-form-label text-md-right">تصویر</label>

                <div class="col-md-6">
                    <input id="picture" type="file" class="form-control{{ $errors->has('picture') ? ' is-invalid' : '' }}" name="picture" >

                    @if ($errors->has('picture'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('picture') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group">
                <hr>
            </div>

            <div class="form-group row">
                <label for="status" class="col-md-4 col-form-label text-md-right">وضعیت</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="status_active" name="status" value="active" {{ ( old('status') == 'active') ? "checked" : "" }}>
                            <label for="status_active">فعال</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="status_inactive" name="status" value="inactive" {{ ( old('status') == 'inactive') ? "checked" : "" }}>
                            <label for="status_inactive">غیر فعال</label>
                        </div>
                    </div>
                    @if ($errors->has('status'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('status') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="status" class="col-md-4 col-form-label text-md-right">جنسیت</label>

                <div class="col-md-6">
                    <div class="radio radio-info">
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="gender_active" name="gender" value="0" {{ ( old('gender') == '0') ? "checked" : "" }}>
                            <label for="gender_active">مرد</label>
                        </div>
                        <div style="display: inline-block; margin-right: 15px">
                            <input type="radio" id="gender_inactive" name="gender" value="1" {{ ( old('gender') == '1') ? "checked" : "" }}>
                            <label for="gender_inactive">زن</label>
                        </div>
                    </div>
                    @if ($errors->has('gender'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('gender') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="state" class="col-md-4 col-form-label text-md-right">استان</label>

                <div class="col-md-6">
                    <select id="state" class="form-control{{ $errors->has('state') ? ' is-invalid' : '' }}" name="state">
                        <option value="">لطفا انتخاب کنید</option>
                        @if($province)
                            @foreach($province as $pr)
                                <option value="{{ $pr['id'] }}" {{ (old('state') == $pr['id']) ? 'selected' : '' }}>{{ $pr['state'] }}</option>
                            @endforeach
                        @endif
                    </select>

                    @if ($errors->has('state'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('state') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="city" class="col-md-4 col-form-label text-md-right">شهر</label>

                <div class="col-md-6">
                    <select id="city" class="form-control{{ $errors->has('city') ? ' is-invalid' : '' }}" name="city">
                        <option value="">لطفا انتخاب کنید</option>
                    </select>

                    @if ($errors->has('city'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('city') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="address" class="col-md-4 col-form-label text-md-right">آدرس</label>

                <div class="col-md-6">
                    <input id="address" type="text" class="form-control{{ $errors->has('address') ? ' is-invalid' : '' }}" name="address" value="{{ old('address') }}" >

                    @if ($errors->has('address'))
                        <span class="invalid-feedback">
                                        <strong>{{ $errors->first('address') }}</strong>
                                    </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="job_title" class="col-md-4 col-form-label text-md-right">شغل</label>

                <div class="col-md-6">
                    <input id="job_title" type="text" class="form-control{{ $errors->has('job_title') ? ' is-invalid' : '' }}" name="job_title" value="{{ old('job_title') }}" >

                    @if ($errors->has('job_title'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('job_title') }}</strong>
                        </span>
                    @endif
                </div>
            </div>

            <div class="form-group row">
                <label for="bio" class="col-md-4 col-form-label text-md-right">بیو گرافی</label>

                <div class="col-md-6">
                    <input id="bio" type="text" class="form-control{{ $errors->has('bio') ? ' is-invalid' : '' }}" name="bio" value="{{ old('bio') }}" >

                    @if ($errors->has('bio'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('bio') }}</strong>
                        </span>
                    @endif
                </div>
            </div>


            <div class="form-group row">
                <label for="password" class="col-md-4 col-form-label text-md-right">تغییر رمز عبور</label>

                <div class="col-md-6">
                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}" name="password" >

                    @if ($errors->has('password'))
                        <span class="invalid-feedback">
                            <strong>{{ $errors->first('password') }}</strong>
                        </span>
                    @endif
                </div>
            </div>


            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت کاربر</button>
                </div>
            </div>
            <div class="clearfix"></div>
        </form>

    </div>


    <script>
        $(document).ready(function () {

            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });


            $(document).delegate('#state', 'change', function (e) {
                e.preventDefault();

                var liveitem = $(this);
                var liveId = $(this).val();
                $('.area').css('display', 'none');

                $('#city').html('<option value="">لطفا انتخاب کنید</option>');

                $.ajax({
                    method: 'POST',
                    url: '{{ url('cp-manager/city') }}',
                    data: {state : liveId}
                }).done(function(result){

                    if(result.status == 'success'){
                        result.data.forEach(function(element) {
                            $('#city').append('<option value="'+element['id']+'">'+element['city']+'</option>');
                        });

                    }

                });

            });

            @if(old('state'))

                var liveId = '{{ old('state') }}';
                var city = '{{ old('city') }}';

                console.log('sds');
                $.ajax({
                    method: 'POST',
                    url: '{{ url('cp-manager/city') }}',
                    data: {state : liveId}
                }).done(function(result){


                    if(result.status == 'success'){
                        result.data.forEach(function(element) {
                            $('#city').append('<option value="'+element['id']+'">'+element['city']+'</option>');
                        });

                        $('#city option[value="'+city+'"]').prop('selected', 'selected');
                    }

                });

            @endif

            $(document).delegate('#city', 'change', function (e) {
                e.preventDefault();

                var liveitem = $(this);
                var liveId = $(this).val();

                if(liveId === 'تهران'){
                    $('.area').css('display', 'block');
                }else{
                    $('.area').css('display', 'none');
                }

            });


        })
    </script>
@endsection