<!doctype html>
<html lang="fa">

<head>
    <meta charset="UTF-8">
    <title>Invoice - #{{\Carbon\Carbon::now()->format('dYm')}}</title>

    <style type="text/css">
        @page {
            header: page-header;
            footer: page-footer;
            margin: 100pt 0px 80pt;
            margin-footer: 18pt;
        }

        body {
            margin: 0px;
            font-family: 'fa';
            direction: rtl;
        }

        a {
            color: #fff;
            text-decoration: none;
        }

        table {
            font-size: x-small;
        }

        tfoot tr td {
            font-weight: bold;
            font-size: x-small;
        }
        td{
            text-align: center;
        }
        .invoice table {
            margin: 15px;
        }

        .invoice h3 {
            margin-left: 15px;
        }

        .information {
            background-color: #60A7A6;
            color: #FFF;
            width: 100%;
            box-sizing: border-box;

        }
        .footer {
            background-color: #60A7A6;
            color: #FFF;
            position: absolute;
            bottom: 0;
            direction: rtl;
            width: 100%;
            color: #000000;
        }
    </style>

</head>
<body>
<htmlpageheader name="page-header">
    <div class="information" style="position: absolute;top: 0;color: #000000">
        <div >
            <h3 style="text-align: center"> سلامت بدون مرز</h3>
        </div>
        <div style="margin-right: 10px">
            <p>دکتر: {{$drname}}</p>
            <p>تاریخ: {{\Hekmatinasser\Verta\Verta::now()->format('d F Y')}}</p>
        </div>
    </div>
</htmlpageheader>

<div class="invoice">
    <table>
        <tr>
            <th>نام بیمار</th><hr>
            <th>تاریخ ویزیت</th><hr>
            <th>مبلغ</th><hr>
            <th>تاریخ پرداخت</th><hr>
            <th>توضیحات</th><hr>
        </tr>
        <tbody>
        @foreach($invoices as $in)
            <tr>
                <td style="width: 25%">{{$in->us_fullname}}</td>
                <td style="width: 15%">{{$in->fa_data}}</td>
                <td style="width: 15%">{{$in->DoctorTransaction('paid')->first()->amount}}</td>
                <td style="width: 20%">{{\Hekmatinasser\Verta\Verta::instance($in->DoctorTransaction('paid')->first()->updated_at)->format('Y-m-d')}}</td>
                <td style="width: 30%">{{$in->DoctorTransaction('paid')->first()->message}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div>

<htmlpagefooter name="page-footer">
    <div class="footer">
    <h3 style="text-align: center"> این فایل فاقد ارزش قانونی بوده و صرفا جهت اطلاع رسانی میباشد</h3>
    <h4 style="text-align: center">سلامت بدون مرز</h4>
    <h4 style="text-align: center">https://sbm24.com</h4>
    </div>
</htmlpagefooter>
</body>
</html>
