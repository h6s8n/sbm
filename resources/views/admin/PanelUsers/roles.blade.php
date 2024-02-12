@extends('admin.layouts.app')

@section('page_name', 'نقش های کاربر')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <span>نقش های  {{$user->fullname}}</span>
            <br>
        </div>
    </div>

    <form method="post">
        {{ csrf_field() }}
        <div class="white-box">
            <div class="body">
                <div class="row">
                    @foreach($roles as $role)
                        <div class="col-sm-4 col-xs-12">
                            <label>
                                <input type="checkbox" name="role[]"
                                       {{$user->hasRole($role->name) ? 'checked' : ''}}
                                value="{{$role->name}}">{{' '.$role->display_name}}

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
