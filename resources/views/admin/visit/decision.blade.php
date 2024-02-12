@extends('admin.layouts.app')

@section('page_name', 'تصمیم گیری درخواست ')

@section('content')
    <div class="white-box">
        <form method="post" action="" class="avatar" style="direction: rtl" enctype="multipart/form-data">
            {{ csrf_field() }}
            <div class="form-group row">
                <div class="radio radio-info">
                    <div style="display: inline-block; margin-right: 30px">
                        <input type="radio" id="decision-0" name="decision" value="0"
                            {{ ( old('status', $action->decision) == '0') ? "checked" : "" }}>
                        <label for="decision-0">در انتظار تصمیم گیری</label>
                    </div>
                    <div style="display: inline-block; margin-right: 20px">
                        <input type="radio" id="decision-1" name="decision" value="1"
                            {{ ( old('status', $action->decision) == '1') ? "checked" : "" }}>
                        <label for="decision-1">موافقت</label>
                    </div>
                    <div style="display: inline-block; margin-right: 20px">
                        <input type="radio" id="decision-2" name="decision" value="2"
                            {{ ( old('status', $action->decision) == '2') ? "checked" : "" }}>
                        <label for="decision-2">عدم موافقت</label>
                    </div>
                </div>
                @if ($errors->has('status'))
                    <span class="invalid-feedback">
                            <strong>{{ $errors->first('status') }}</strong>
                        </span>
                @endif
            </div>

            <div class="form-group row">
                <label for="description" class="col-md-4 col-form-label text-md-right">توضیحات</label>
                <div class="col-md-12">
                    <textarea id="description" type="text" class="form-control {{ $errors->has('description') ? ' is-invalid' : '' }}"
                              name="description" maxlength="250" required></textarea>
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
