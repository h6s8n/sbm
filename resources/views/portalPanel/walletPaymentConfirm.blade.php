@extends('portalPanel.layouts.app')

@section('page_name',  'تایید پرداخت ')

@section('content')
    <div class="white-box">
        <div class="portlet-body">
            <div class="clearfix"></div>
            <br>

                <div class="form-group row">
                    <label for="transId" class="col-md-4 col-form-label text-md-right">شناسه پرداخت ریالی</label>
                    <div class="col-md-6  text-md-right">
                        <input id="transId" type="text" value="{{ $wallets[0]->transId }}" disabled class="form-control {{ $errors->has('transId') ? ' is-invalid' : '' }}" required>
                    </div>
                </div>
                <div class="form-group row">
                    <label for="tether_current_price" class="col-md-4 col-form-label text-md-right"> قیمت روز تتر (ریال)</label>
                    <div class="col-md-6  text-md-right">
                        <input id="tether_current_price" type="number" class="form-control {{ $errors->has('tether_current_price') ? ' is-invalid' : '' }}"
                                required>
                        @if ($errors->has('tether_current_price'))
                            <span class="invalid-feedback">
                            <strong>{{ $errors->first('tether_current_price') }}</strong>
                        </span>
                        @endif
                    </div>
                </div>


                <div class="col-sm-4 col-xs-12">
                    <div
                        style="margin-top: 18px; font-weight: 500; line-height: 24px; font-size: 13px; padding: 0 10px; display: inline-block">
                        مجموعه تراکنش ها {{ isset($wallets) ? number_format($wallets->total()) : 0 }} عدد
                    </div>
                </div>

                <div class="clearfix"></div>

        </div>
    </div>

    <div class="white-box">
        <div class="table-container">

            <table class="table table-striped table-bordered table-hover">
                <tbody>
                <tr role="row" class="heading">
                    <th>آدرس کیف پول</th>
                    <th>مبلغ تسویه (ریال)</th>
                    <th>تعداد تتر ((مبلغ تسویه ÷ قیمت روز تتر) - ۲ تتر)</th>
                    <th>txID</th>
                    <th>QR code</th>
                    <th>عملیات</th>
                </tr>
                @if(count($wallets) > 0)
                    @foreach($wallets as $wallet)
                        <tr role="row" class="filter">
                        <form method="post" action="/cp-portal/walletPayment/{{$wallet->id}}" class="avatar" style="direction: rtl" enctype="multipart/form-data">
                            {{ csrf_field() }}
                            <input type="hidden" id="tether_current_price_{{$loop->index}}" name="tether_current_price">
                        <td>
                            <input id="account_id" type="text" value="{{ $wallet->account_id }}" disabled class="form-control {{ $errors->has('account_id') ? ' is-invalid' : '' }}" required>
                        </td>
                        <td>
                            <input id="transId" type="text" value="{{ abs($wallet->amount) }}" disabled class="form-control {{ $errors->has('transId') ? ' is-invalid' : '' }}" required>
                        </td>
                        <td>
                            <div class="col-md-4">
                                <button type="button" class="btn btn-block btn-success btn-rounded" onclick="calculateTetherCount{{$loop->index}}()">محاسبه</button>
                            </div>
                            <div class="col-md-8">
                            <input id="tether_count_{{$loop->index}}" type="text"
                                   class="form-control {{ $errors->has('tether_count') ? ' is-invalid' : '' }}"
                                   @if($wallet->tether_count) value="{{$wallet->tether_count}}" @endif
                                   name="tether_count" disabled required>
                            </div>
                        </td>
                        <td>
                            <input id="receipt_link" type="text"
                                   @if($wallet->receipt_link) value="{{$wallet->receipt_link}}" disabled @endif
                                   class="form-control {{ $errors->has('receipt_link') ? ' is-invalid' : '' }}"
                                   name="receipt_link" required>
                        </td>
                        <td>
                            <a target="_blank" @if($wallet->account_id_QR)href="{{ $wallet->account_id_QR }}"@endif>مشاهده QR</a>
                        </td>
                        <td>
                            @unless($wallet->receipt_link)
                            <button type="submit" class="btn btn-info waves-effect waves-light">ثبت </button>
                            @endunless
                        </td>
                        </form>


                        <script>
                            let calculateTetherCount{{$loop->index}} = () => {
                                let currentPrice = document.getElementById('tether_current_price').value;
                                if (currentPrice == "" || currentPrice <= 0) {
                                    alert("لطفا قیمت روز تتر را به درستی وارد کنید");
                                    return false;
                                }
                                let amount = {{ abs($wallet->amount) }};

                                document.getElementById("tether_current_price_{{$loop->index}}").value = currentPrice;
                                document.getElementById("tether_count_{{$loop->index}}").value = (amount / currentPrice) - 2;
                            }
                        </script>
                        </tr>
                    @endforeach
                @endif
                </tbody>
            </table>
        </div>
    </div>
    @if(count($wallets) > 0)
    <script>
        let calculateTetherCount = () => {
            let currentPrice = document.getElementById('tether_current_price').value;
            if (currentPrice == "" || currentPrice <= 0) {
                alert("لطفا قیمت روز تتر را به درستی وارد کنید");
                return false;
            }
            let amount = {{ abs($wallets[0]->amount) }};
            document.getElementById("tether_count").value = (amount / currentPrice) - 2;
        }
    </script>
    @endif
@endsection
