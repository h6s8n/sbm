<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width,height=device-height,initial-scale=1,maximum-scale=1,user-scalable=no,shrink-to-fit=no">
    <title>{{ $user->doctor_nickname . ' ' . $user->fullname }}</title>
    <link rel='stylesheet' id='zm-bootstrap-css'
          href='https://sbm24.com/wp-content/themes/sbm24/static/bootstrap/dist/css/bootstrap.min.css?ver=4.9.11'
          type='text/css' media='all'/>
    <link rel='stylesheet' id='zm-bootstrap-rtl-css'
          href='https://sbm24.com/wp-content/themes/sbm24/static/css/bootstrap.rtl.min.css?ver=4.9.11' type='text/css'
          media='all'/>
    <link rel='stylesheet' id='zm-flickity-css'
          href='https://sbm24.com/wp-content/themes/sbm24/static/css/flickity.css?ver=4.9.11' type='text/css'
          media='all'/>
    <link rel='stylesheet' id='font-awesome-css'
          href='https://sbm24.com/wp-content/themes/sbm24/static/css/font-awesome.css?ver=4.9.11' type='text/css'
          media='all'/>
    <link rel='stylesheet' id='zm-select2-css'
          href='https://sbm24.com/wp-content/themes/sbm24/static/css/select2.min.css?ver=4.9.11' type='text/css'
          media='all'/>
    <link rel='stylesheet' id='zm-custom-css'
          href='https://sbm24.com/wp-content/themes/sbm24/static/css/app_v1.css' type='text/css'
          media='all'/>
    <link rel='stylesheet' id='zm-responsive-css'
          href='https://sbm24.com/wp-content/themes/sbm24/static/css/responsive_v1.css?ver=4.9.11' type='text/css'
          media='all'/>
    <script type='text/javascript'
            src='https://sbm24.com/wp-content/themes/sbm24/static/js/jquery-3.2.1.min.js'></script>
    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/bootstrap.min.js'></script>
    <script type='text/javascript'
            src='https://sbm24.com/wp-content/themes/sbm24/static/js/flickity.pkgd.min.js'></script>
    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/select2.min.js'></script>
    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/script.js'></script>
</head>
<body style="overflow: hidden; padding: 0; margin: 0;">
    
    @php   
    function test_capacity($value) {
        return($value['CapacityCount'] > 0);
    }
    $nearest_time_index = array_key_first(array_filter($full_times,"test_capacity"));
    $nearest_time_value = $full_times[$nearest_time_index]
    
    @endphp 

{{-- <div class="profile_page">
    <section class="section_app profile_page_sc">
        <div class="box-doctor">
            <div class="cr_animation">
                <div class="over_cr"></div>
                <div class="over_cr"></div>
            </div>
            <div class="header_profile">
                <div class="photo_box">
                    <div class="dr_photo"
                         style="background-image: url({{ ($user->picture) ? $user->picture : 'https://sbm24.com/wp-content/themes/sbm_luxery/assets/images/default_user.png' }})">
                        <img src="{{ ($user->picture) ? $user->picture : 'https://sbm24.com/wp-content/themes/sbm_luxery/assets/images/default_user.png' }}"
                             class="hidden"/>
                    </div>
                </div>
                <div class="title_box">
                    <div class="comment-rate single" style="min-height: 29px"><p>({{ $user->number_of_votes }} رای)</p>
                        <p>{{ number_format((float)$user->avg_overall, 1, '.', '') }} امتیاز </p>
                        <div class="MuiRating-readOnly" role="img" aria-label="{{ $user->avg_overall }} Stars">
                            <div class="MuiRating-cont-readOnly" style="width: {{ $user->rate }}%">
                            </div>
                        </div>
                    </div>
                    <div class="dr_title">
                        <div class="dr_name">
                            <h1>{{ $user->doctor_nickname . ' ' . $user->fullname }}</h1>
                        </div>
                        <div class="dr_nezam_id">
                            <p>{{ $user->code_title }}: {{ $user->specialcode }}</p>
                        </div>
                    </div>
                    <div class="specialties">
                        {{ $user->job_title }}
                    </div>

                    <div class="tage_sbm">
                        @php
                            if ( $user->visit_condition) {
                                $user->visit_condition = json_decode($user->visit_condition);
                                if ($user->visit_condition && $user->visit_condition->consultation_type) {
                                    echo 'ویزیت به صورت: <span class="tg_item_color">';
                                    $array = [];
                                    if ($user->visit_condition->consultation_type->videoConsultation == 'true') $array[] = 'تماس تصویری';
                                    if ($user->visit_condition->consultation_type->voiceConsultation == 'true') $array[] = 'تماس تلفنی';
                                    if ($user->visit_condition->consultation_type->textConsultation == 'true') $array[] = 'چت';
                                    echo join('، ', $array);
                                    echo '</span> می باشد.';
                                }
                            }
                        @endphp
                        <div id="nearest_time"></div>
                    </div>
                    @if ($nearest_time_index)
                        <div class="price_dr" id="price_dr_visit" style="font-size: 17px;">
                            <div class="single-doctor-price">
                                <div class="icon-box" title="تضمین بازگشت وجه در صورت نارضایتی از ویزیت">
                                    <img src="https://sbm24.com/wp-content/themes/sbm_luxery/assets/images/info-box2.svg"
                                         alt="">
                                </div>
                                <span style="margin: 0; color: #7747c0">هزینه ویزیت:</span>
                                <span style="margin: 0; color: #7747c0"> از {{ number_format($nearest_time_value['Visits'][0]['price']) }} ریال</span>
                            </div>
                        </div>
                    @endif
                    <div class="access_dr">
                        <div class="but_ac_dr dek">
                            <a href="#" class="but_sbm " onclick="scrollToTop(event, 'calendar_sbm');">مشاوره بگیر</a>
                            <button type="button" class="but_sbm bellbell" style="display: none;"
                                    onclick="reserve_event (event)">
                                <img class="bell"
                                     src="https://sbm24.com/wp-content/themes/sbm_luxery/assets/images/bell-button.svg"
                                     alt="وقت خالی را به من خبر بده">
                                نوبت جدید را به من اطلاع بده
                            </button>
                        </div>
                        <div class="but_ac_dr">
                            <a href="#" class="but_sbm dis" onclick="scrollToTop(event, 'bio_dr');">درباره پزشک</a>
                        </div>
                    </div>
                    @if ($user->special_point)
                        <div class="center-setting">
                       <span class="infoText">
                            نکته : {{ $user->special_point }}
                       </span>
                        </div>
                    @endif
                </div>
                <div class="clearfix"></div>
                @if ($nearest_time_index)
                    <script>
                        var diffDays_lb = "";
                        let output_new_time = {{ strtotime(date('j F Y')) * 1000 }};
                        let output_end_time = {{ strtotime($nearest_time_value['DateTime']) * 1000 }};
                        const diffTime = Math.abs(output_end_time - output_new_time);
                        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                        diffDays_lb = (diffDays) ? 'اولین زمان خالی ' + diffDays + ' روز دیگر' : 'اولین زمان خالی امروز';
                        document.getElementById('nearest_time').innerText = diffDays_lb;
                    </script>
                @elseif
                    <script>
                        var diffDays_lb = 'این پزشک درحال حاضر نوبتی  ندارد';
                        document.getElementById('nearest_time').innerText = diffDays_lb;
                    </script>
                @endif
            </div>
        </div>
        <div class="box-info" id="bio_dr">
            <div class="tab">
                <button class="tablinks active" onclick="openCity(event, 'bio')">درباره پزشک</button>
                @if (@json_decode($user->special_json))
                    <button class="tablinks" onclick="openCity(event, 'specialties')">تخصص های پزشک</button>
                @endif
            </div>
            <div id="bio" class="tabcontent" style="display: block">
                {!! ($user->bio) ? '<p>' . $user->bio . '</p>' : '<p class="text-center">متاسفانه اطلاعات حساب کاربری این پزشک تکمیل نشده است.</p>' !!}
                {!! ($user->address) ? '<div class="address"><p>آدرس مطب :  ' . $user->address . '</p></div>' : '' !!}
            </div>
            <div id="specialties" class="tabcontent">
                @php $special_json = @json_decode($user->special_json) @endphp
                @if ($special_json)
                     <div class="section_tx">
                        <h2>تخصص ها</h2>
                        <ul>
                            @foreach ($special_json as $item)
                                <li>{{ $item->label }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @php $skill_json = @json_decode($user->skill_json); @endphp
                @if ($skill_json)
                    <div class="section_tx">
                        <h2>مهارت ها</h2>
                        <ul>
                            @foreach ($skill_json as $item)
                                <li>{{ $item->label }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        
            
            <script>
                function openCity(evt, cityName) {
                    var i, tabcontent, tablinks;
                    tabcontent = document.getElementsByClassName("tabcontent");
                    for (i = 0; i < tabcontent.length; i++) {
                        tabcontent[i].style.display = "none";
                    }
                    tablinks = document.getElementsByClassName("tablinks");
                    for (i = 0; i < tablinks.length; i++) {
                        tablinks[i].className = tablinks[i].className.replace(" active", "");
                    }
                    document.getElementById(cityName).style.display = "block";
                    evt.currentTarget.className += " active";
                }
            </script>
        </div>
    </section>
</div>


<div class="calendar">
    <div class="loader_bo">
        <div class="cssload-speeding-wheel"></div>
    </div>
    <div class="bookingcalendar">
        <div class="bookingcalendar_inner">
            <div class="bookingcalendar_slider">
            </div>
        </div>
    </div>
    <div class="bookingcalendar_selection">
        <div class="calendar_time_set">
        </div>
    </div>
</div>
<script>
    var flkty;
    var search_value = [];
    $.ajax({
        method: 'get',
        url: '{{ url('api/v1/search/doctor/calender/visit/' . $user->username) }}',
        data: {}
    }).done(function (result) {
        $('.loader_bo').addClass('hidden');
        search_value = result.data;
        new RenderCalenders({{$nearest_time_index}}, result.data);
        new RenderCalenderTime({{$nearest_time_index}});
    });

    function RenderCalenders(select, search) {
        var count_key = -1;
        for (let i = 0; i < search.length; ++i) {
            var item = search[i];
            count_key++;
            var active_detail = '';
            if ((select === count_key)) {
                active_detail = 'active';
            }
            var CapacityCount = "";
            if (item["CapacityCount"] > 0) {
                CapacityCount = '<div>\n' +
                    '                    <div class="bookingcalendar__detail bookingcalendar__Capacity">' + item["CapacityCount"] + '</div>\n' +
                    '                    <div class="bookingcalendar__detail bookingcalendar__cur">نفر ظرفیت</div>\n' +
                    '                </div>'
                /*} else {
                    CapacityCount = '<div class="bookingcalendar__detail bookingcalendar__empty">وقت خالی وجود ندارد</div>';
                }*/
                $('.bookingcalendar_slider').append('<div data-index=' + i + ' class="bookingcalendar__item ' + active_detail + '">\n' +
                    '                <div class="bookingcalendar__iteminner"><div class="bookingcalendar__detail bookingcalendar__weekday">' + item["WeekDay"] + '</div>' + CapacityCount + '</div>\n' +
                    '            </div>');
            }
        }
        var flkty = new Flickity('.bookingcalendar_slider', {
            cellAlign: 'right',
            contain: true,
            pageDots: false,
            prevNextButtons: false,
            rightToLeft: true
        });
        flkty.on('staticClick', (event, pointer, cellElement, cellIndex) => {
            var active_cellIndex = cellIndex + 1;
            $('.flickity-slider .bookingcalendar__item').removeClass('active');
            $('.flickity-slider .bookingcalendar__item:nth-child(' + active_cellIndex + ')').addClass('active');
            new RenderCalenderTime(cellElement.getAttribute('data-index'));
        });
    }

    function RenderCalenderTime(select) {
        var search = search_value[select];
        var Disabled_Prev = '';
        if ((select === 0)) {
            Disabled_Prev = 'disabled';
        }
        var search_length = search_value.length - 1;
        var Disabled_Next = '';
        if ((select === search_length)) {
            Disabled_Next = 'disabled';
        }
        if (search) {
            if (search['CapacityCount']) {
                var select_times = '';
                for (let i = 0; i < search['Visits'].length; ++i) {
                    var item = search['Visits'][i];
                    var capacity = (item["capacity"] - item["reservation"]);
                    var CapacityCount = "";
                    if (capacity > 0) {
                        CapacityCount = '<div>\n' +
                            '                    <a href="https://cp.sbm24.com/user/reserve/SB' + item.id + '" target="_blank" class="btn btn-sbm reservation">رزرو وقت مشاوره آنلاین</a>\n' +
                            '                    <p>ظرفیت ' + capacity + ' نفر </p>\n' +
                            '                </div>'
                    } else {
                        CapacityCount = '<div class="capacity_empty">ظرفیت تکمیل است</div>';
                    }
                    var price = '';
                    if (item['original_price'] !== item['price']) {
                        var off_price_number = Number(item['original_price']).toLocaleString("us-US", {minimumFractionDigits: 0});
                        price += '<span class="old_price">' + off_price_number + ' ریال' + '</span>';
                    }
                    var price_number = Number(item['price']).toLocaleString("us-US", {minimumFractionDigits: 0});
                    price += price_number + ' ریال';
                    var time = parseInt(item['time']) + 1;
                    if (parseInt(item['time']) === 0) {
                        time = 24 + ' الی ' + time;
                    } else if (parseInt(item['time']) === 24) {
                        time = 24 + ' الی ' + 1;
                    } else {
                        time = parseInt(item['time']) + ' الی ' + time;
                    }
                    select_times += '<div class="item_calender_time">\n' +
                        '        <div class="main_item_time">\n' +
                        '            <div class="select_calender">' + CapacityCount + '</div>\n' +
                        '            <div class="info_calender">\n' +
                        '                <h3>مشاوره آنلاین برای تاریخ <span>' + item['fa_data'] + '</span></h3>\n' +
                        '                <h4>ساعت: ' + time + '</h4>\n' +
                        '                <div class="price">' + price + '</div>\n' +
                        '            </div>\n' +
                        '            <div class="clearfix" />\n' +
                        '        </div>\n' +
                        '    </div>';
                }
                $('.calendar_time_set').html('<div class="full_times">\n' +
                    '                        <div class="header_timers" style="display: none;">\n' +
                    '                            <div class="adjacentDays-col">\n' +
                    '                                <div class="adjacent-days basic-style">\n' +
                    '                                    <button type="button" class="adjacent-days__prev" ' + Disabled_Prev + '>\n' +
                    '                                        روز قبل\n' +
                    '                                    </button>\n' +
                    '                                    <span class="adjacent-days__date">' + search['DateTimeFa'] + '</span>\n' +
                    '                                    <button type="button" class="adjacent-days__next" ' + Disabled_Next + '>\n' +
                    '                                        روز بعد\n' +
                    '                                    </button>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                        <div class="calender_select_times">' + select_times + '</div>\n' +
                    '                    </div>');
            } else {
                $('.calendar_time_set').html('<div class="header_timers" style="display: none;">\n' +
                    '                            <div class="adjacentDays-col">\n' +
                    '                                <div class="adjacent-days basic-style">\n' +
                    '                                    <button type="button" class="adjacent-days__prev" ' + Disabled_Prev + '>\n' +
                    '                                        روز قبل\n' +
                    '                                    </button>\n' +
                    '                                    <span class="adjacent-days__date">' + search['DateTimeFa'] + '</span>\n' +
                    '                                    <button type="button" class="adjacent-days__next" ' + Disabled_Next + '>\n' +
                    '                                        روز بعد\n' +
                    '                                    </button>\n' +
                    '                                </div>\n' +
                    '                            </div>\n' +
                    '                            <div class="clearfix"></div>\n' +
                    '                        </div>\n' +
                    '                        <div class="empty-availables">\n' +
                    '                        <div>\n' +
                    '                            <div class="text-center">\n' +
                    '                                <figure>\n' +
                    '                                    <img src="https://sbm24.com/wp-content/themes/sbm24/static/images/svg/calender_empty.svg" alt="نتیجه ای یافت نشد" />\n' +
                    '                                    <figcaption>\n' +
                    '                                        <p class="not-found-hero">\n' +
                    '                                            پزشک مورد نظر برای تاریخ  ' + search['DateTimeFa'] + ' وقت مشاوره تعیین ننموده است.\n' +
                    '                                        </p>\n' +
                    '                                        <p class="not-found-title">\n' +
                    '                                            اگر مایل هستید تاریخ دیگری را جستجو کنید\n' +
                    '                                        </p>\n' +
                    '                                    </figcaption>\n' +
                    '                                </figure>\n' +
                    '                            </div>\n' +
                    '                        </div>\n' +
                    '                    </div>');
            }
        }
    }
</script> --}}
</body>
</html>