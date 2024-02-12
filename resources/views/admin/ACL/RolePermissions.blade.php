@extends('admin.layouts.app')

@section('page_name', 'دسترسی های هر نقش')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <span>دسترسی های نقش {{$role->name}}</span>
            <br>
        </div>
    </div>

    <form method="post">
        {{ csrf_field() }}
        <div class="white-box">
            <div class="body">
                <div class="row">
                @foreach($permissions as $permission)
                    <div class="col-sm-4 col-xs-12">
                    <label>
                        <input type="checkbox"
                               name="permission_name[]"
                               {{$role->hasPermissionTo($permission->name,'web') ? 'checked' : ''}}
                               value="{{$permission->name}}"> {{$permission->display_name}}
                    </label>
                    </div>
                @endforeach
                </div>

            </div>
            <div class="form-group row mb-0">
                <div class="col-md-6 offset-md-4">
                    <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                </div>
            </div>
        </div>
    </form>

@endsection
