@extends('admin.layouts.app')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <form class="filter_list" method="post" style="direction: rtl" enctype="multipart/form-data">
                {{csrf_field()}}
                <div class="row form-group">
                    <label for="file" class="col-md-4 col-form-label text-md-right">فایل</label>

                    <div class="col-md-6">
                        <input type="file" class="form-control" id="file" name="file">
                    </div>
                </div>
                <div class="form-group row mb-0">
                    <div class="col-md-6 offset-md-4">
                        <button type="submit" class="btn btn-info waves-effect waves-light">انتقال</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection
