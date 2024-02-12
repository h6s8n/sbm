@extends('admin.layouts.app')

@section('page_name', $title ?? 'خلاصه وضعیت درگاه سلامت ')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

            <form id="myForm" class="filter_list" method="get" style="direction: rtl">

                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_start_date">از تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_start_date" name="filter_start_date"
                               value="{{ @$_GET['filter_start_date'] }}">
                    </div>
                </div>
                <div class="col-md-3 col-xs-12">
                    <div class="form-group">
                        <label for="filter_end_date">تا تاریخ</label>
                        <input type="text" class="form-control observer" id="filter_end_date" name="filter_end_date"
                               value="{{ @$_GET['filter_end_date'] }}">
                    </div>
                </div>

                <div class="clearfix"></div>
                <div class="col-sm-4 col-xs-12" style="padding-top: 17px;">
                    <button type="submit" class="btn btn-block btn-success btn-rounded">جستجو</button>
                </div>

                <div class="clearfix"></div>

            </form>
        </div>

    </div>
<div class="white-box">
    <div class="portlet-body">

        <form  style="direction: rtl">
            <h2>خلاصه وضعیت کلی</h2>
            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="filter_name">مجموع واریز :</label>
                    <input type="text" class="form-control"
                           value="{{ number_format($overview['sumIncrease']). ' ریال ' }}" disabled>
                </div>
            </div>
            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="filter_name">مجموع برداشت :</label>
                    <input type="text" class="form-control"
                           value="{{ number_format(abs($overview['sumDecrease'])) . ' ریال ' }}" disabled>
                </div>
            </div>
            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="filter_name">مجموع کارمز خدمت :</label>
                    <input type="text" class="form-control"
                           value="{{ number_format($overview['sumServiceWage']) . ' ریال '}}" disabled>
                </div>
            </div>
            <div class="col-md-3 col-xs-12">
                <div class="form-group">
                    <label for="filter_name">مجموع کارمزد بانکی :</label>
                    <input type="text" class="form-control"
                           value="{{ number_format($overview['sumBankWage']) . ' ریال '}}" disabled>
                </div>
            </div>

        </form>

        <div class="clearfix"></div>

        <form action="" method="GET">
            <div class="col-md-6">
                <div class="form-group">
                <label for="doctor_id">انتخاب پزشک </label>
                <select id="doctor_id" class="form-control" name="doctor_id">
                    <option value="">لطفا انتخاب کنید</option>
                    @foreach($doctors as $doctor)
                    <option value="{{ $doctor->doctor_id }}" {{ (@$_GET['doctor_id'] == $doctor->doctor_id) ? 'selected' : '' }}>
                        {{ $doctor->doctor->fullname }}
                    </option>
                    @endforeach
                </select>

            </div>
            </div>
            <div class="col-md-3 col-xs-12" style="padding-top: 17px;">
                <div class="form-group">
                <button type="submit" class="btn btn-block btn-success btn-rounded" >جستجو</button>
                </div>
            </div>
        </form>
        <div class="clearfix"></div>
        @if(!is_null($account_balance))
            <div class="white-box">
                <div class="portlet-body">
                    <h2>خلاصه وضعیت درگاه پزشک</h2>
                    <form  style="direction: rtl">
                        <div class="col-md-3 col-xs-12">
                            <div class="form-group">
                                <label for="filter_name">مجموع واریز پزشک :</label>
                                <input type="text" class="form-control"
                                       value="{{ number_format($account_balance->sumIncrease). ' ریال ' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="form-group">
                                <label for="filter_name">مجموع برداشت پزشک :</label>
                                <input type="text" class="form-control"
                                       value="{{ number_format(abs($account_balance->sumDecrease)). ' ریال ' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="form-group">
                                <label for="filter_name">مجموع در انتظار واریز برای پزشک :</label>
                                <input type="text" class="form-control"
                                       value="{{ number_format(abs($account_balance->sumPendingIncrease)). ' ریال ' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="form-group">
                                <label for="filter_name">مانده حساب :</label>
                                <input type="text" class="form-control"
                                       value="{{ number_format($account_balance->account_balance). ' ریال ' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="form-group">
                                <label for="filter_name">قابل برداشت :</label>
                                <input type="text" class="form-control"
                                       value="{{ number_format($account_balance->account_accessible) . ' ریال ' }}" disabled>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="form-group">
                                <label for="filter_name">در انتظار برداشت :</label>
                                <input type="text" class="form-control"
                                       value="{{ number_format($account_balance->pending_decrease) . ' ریال '}}" disabled>
                            </div>
                        </div>
                        <div class="col-md-3 col-xs-12">
                            <div class="form-group">
                                <label for="filter_name">قابل برداشت غیر ریالی :</label>
                                <input type="text" class="form-control"
                                       value="{{ number_format($account_balance->non_rial_account_accessible) . ' ریال '}}" disabled>
                            </div>
                        </div>
                    </form>
                    <div class="clearfix"></div>

                </div>
            </div>
        @endif

    </div>
</div>
@endsection
