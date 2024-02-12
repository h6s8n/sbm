
        <div class="calendar" id="calendar_sbm">
            <div id="loader_bo">
                <div class="lds-roller">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>    
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
            <div id="setTimeCt">
                <div class="calendar_time_title">
                    <div class="label">انتخاب تاریخ مشاوره</div>
                </div>
                <div class="bookingcalendar">
                    <div class="bookingcalendar_inner">
                        <div class="bookingcalendar_slider" id="booking_calendar_slider">
                        </div>
                    </div>
                </div>
                <div class="calendar_time_title">
                    <div class="label">انتخاب ساعت مشاوره</div>
                </div>
                <div class="bookingcalendar_selection">
                    <div class="calendar_time_set" id="calendar_time_set">
                    </div>
                </div>
                <div class="get_visit_time">
                    <a href="#" class="but_sbm desctop" id="rez_moshavere" onclick="register_event(event)">
                        رزرو وقت مشاوره
                    </a>
                </div>
            </div>
            <div id="similar_box" style="display: none">
                <h3>پزشکان مشابه</h3>
                <div id="empty_time_dr" class="similar_dr">
                </div>
            </div>
            <script>
                var flkty;
                var ActiveIndex = 0;
                var ActiveSelect = 0;
                var search_value = [];
                var price = '';
                var xhttp = new XMLHttpRequest();
                xhttp.onreadystatechange = function () {
                    if (this.readyState === 4 && this.status === 200) {
                        var req = JSON.parse(this.responseText);
                        search_value = req.data;
                        document.getElementById("loader_bo").style.display = "none";
                        if (req.data.length > 0) {
                            document.getElementById("setTimeCt").style.display = "block";
                            setTimeout(function () {
                                RenderCalenders(ActiveIndex, req.data);
                                RenderCalenderTime(ActiveIndex);
                            }, 100);
                        } else {
                            document.getElementById("setTimeCt").style.display = "none";
                            document.getElementById("rez_moshavere_ct").style.display = "none";
                            document.querySelector(".but_ac_dr.dek a.but_sbm ").style.display = "none";
                            document.querySelector(".but_ac_dr.dek .bellbell ").style.display = "flex";
                            document.querySelector(".but_sbm.fix_min_bottom.bellbell ").style.display = "block";
                            document.getElementById("similar_box").style.display = "block";
                            similar();
                        }
                    }
                };
                {{ $hospital = ($_GET['hospital']) ? '&hospital=' . $_GET['hospital'] : '' }}
                xhttp.open("GET", "{{ cp_base_URL . 'search/doctor/calender/visit/' . $username . '?limit=true' . $hospital }}", true);
                xhttp.send();

                function RenderCalenders(select, search) {
                    var count_key = -1;
                    search.map(item => {
                        count_key++;
                        var active_detail = (select === count_key) ? 'active' : '';
                        var CapacityCount = (item["CapacityCount"] > 0) ? '<div class="calendar_detail capacity">ظرفیت :' + item["CapacityCount"] + ' نفر</div>' : '<div class="calendar_detail empty">وقت خالی وجود ندارد</div>';
                        var e = document.createElement('div');
                        e.innerHTML = '<div class="bookingcalendar__item ' + active_detail + '">\n' +
                            '                <div class="bookingcalendar__iteminner"><div class="bookingcalendar__detail bookingcalendar__weekday">' + item["WeekDay"] + '</div>' + CapacityCount + '</div>\n' +
                            '            </div>';
                        while (e.firstChild) {
                            document.getElementById("booking_calendar_slider").appendChild(e.firstChild);
                        }
                    });
                    var flkty = new Flickity('.bookingcalendar_slider', {
                        cellAlign: 'right',
                        contain: true,
                        pageDots: false,
                        prevNextButtons: true,
                        rightToLeft: true
                    });
                    flkty.on('staticClick', (event, pointer, cellElement, cellIndex) => {
                        var active_cellIndex = cellIndex + 1;
                        var items = document.getElementsByClassName('bookingcalendar__item');
                        if (items) {
                            for (var x = 0; x < items.length; x++) {
                                items[x].classList.remove('active');
                                if (cellIndex === x) {
                                    items[x].classList.add('active');
                                }
                            }
                        }
                        ActiveSelect = 0;
                        ActiveIndex = cellIndex;
                        RenderCalenderTime(ActiveIndex);
                    });
                }

                function RenderCalenderTime(select) {
                    var search = search_value[select];
                    if (search) {
                        if (search['CapacityCount']) {
                            var select_times = '';
                            document.getElementById("calendar_time_set").innerText = '';
                            search['Visits'].map(item => {
                                if ((item["capacity"] - item["reservation"]) > 0) {
                                    if (!price) {
                                        price = item['price'];
                                        var lb_price = Number(price).toLocaleString("us-US", {minimumFractionDigits: 0});
                                    }
                                    if (!ActiveSelect) {
                                        ActiveSelect = item.id;
                                    }
                                    var active_detail = (ActiveSelect === item.id) ? 'active' : '';
                                    select_times +=
                                        `<div class="item_calender_time">
                                            <div class="item_calender_time_main ${active_detail}" onclick="selectTime(event)" data-key="${item.id}">
                                           <div class="label">${item.short_name ? item.short_name : item.display_top_label}</div>
                                        ساعت ${item['time']}:00
                                            <div  class="calendar_detail capacity">${Number(item.price).toLocaleString("us-US", {minimumFractionDigits: 0})} ریال</div>
                                            <div class="time-to-wait">${item.display_bottom_label}</div>
                                            ${item.has_prescription == 1 ? '<div class="noskhe">با نسخه الکترونیک</div>' : ''}
                                            </div>
                                        </div>`;
                                }
                            });
                            var e = document.createElement('div');
                            e.innerHTML = '<div class="full_times"><div class="calender_select_times">' + select_times + '<div class="clearfix"></div></div></div>';
                            while (e.firstChild) {
                                document.getElementById("calendar_time_set").appendChild(e.firstChild);
                            }
                        }
                    }
                }

                function selectTime(evt) {
                    evt.preventDefault();
                    var items = document.getElementsByClassName('item_calender_time_main');
                    if (items) {
                        for (var x = 0; x < items.length; x++) {
                            items[x].classList.remove('active');
                        }
                    }
                    ActiveSelect = evt.currentTarget.getAttribute("data-key");
                    evt.currentTarget.className += " active";
                }

                function register_event(evt = null) {
                    if (evt) evt.preventDefault();
                    if (getCookie('access_token_sbm')) {
                        if (getCookie('approve') === '1') {
                            Toastify({
                                text: "شما نمیتوانید به عنوان پزشک از یک پزشک دیگر وقت بگیرید.",
                                duration: 5000,
                                destination: "https://github.com/apvarun/toastify-js",
                                newWindow: true,
                                close: false,
                                gravity: "bottom", // `top` or `bottom`
                                position: 'right', // `left`, `center` or `right`
                                backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                stopOnFocus: true, // Prevents dismissing of toast on hover
                                onClick: function () {
                                } // Callback after click
                            }).showToast();
                        } else {
                            window.location = '{{ cp_url . 'user/reserve/SB' }}' + ActiveSelect
                        }
                    } else {
                        document.getElementById("login_sbm").style.display = "flex";
                        document.getElementById("login_sbm").setAttribute('data-run', 'register_event');
                    }
                }

                function reserve_event() {
                    if (getCookie('access_token_sbm')) {
                        if (getCookie('approve') === '1') {
                            Toastify({
                                text: "شما نمیتوانید به عنوان پزشک از یک پزشک دیگر وقت بگیرید.",
                                duration: 5000,
                                destination: "https://github.com/apvarun/toastify-js",
                                newWindow: true,
                                close: false,
                                gravity: "bottom", // `top` or `bottom`
                                position: 'right', // `left`, `center` or `right`
                                backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                stopOnFocus: true, // Prevents dismissing of toast on hover
                                onClick: function () {
                                } // Callback after click
                            }).showToast();
                        } else {
                            var xhttp = new XMLHttpRequest();
                            xhttp.onreadystatechange = function () {
                                if (this.readyState === 4 && this.status === 200) {
                                    var req = JSON.parse(this.responseText);
                                    if (req.status === 'success') {
                                        Toastify({
                                            text: "درخواست شما ثبت شد و بعد از اولین وقت به شما اطلاع رسانی میشود.",
                                            duration: 5000,
                                            destination: "https://github.com/apvarun/toastify-js",
                                            newWindow: true,
                                            close: false,
                                            gravity: "bottom", // `top` or `bottom`
                                            position: 'right', // `left`, `center` or `right`
                                            backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                            stopOnFocus: true, // Prevents dismissing of toast on hover
                                            onClick: function () {
                                            } // Callback after click
                                        }).showToast();
                                    } else {
                                        const Errors = req.data.message;
                                        Toastify({
                                            text: Errors,
                                            duration: 5000,
                                            destination: "https://github.com/apvarun/toastify-js",
                                            newWindow: true,
                                            close: false,
                                            gravity: "bottom", // `top` or `bottom`
                                            position: 'right', // `left`, `center` or `right`
                                            backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                            stopOnFocus: true, // Prevents dismissing of toast on hover
                                            onClick: function () {
                                            } // Callback after click
                                        }).showToast();
                                    }
                                } else if (this.readyState === 4 && this.status === 401) {
                                    run_logout();
                                    reserve_event();
                                } else if (this.readyState === 4 && this.status === 422) {
                                    var req = JSON.parse(this.responseText);
                                    const Errors = req.errors;
                                    Object.keys(Errors).forEach(function (key) {
                                        Toastify({
                                            text: Errors[key][0],
                                            duration: 5000,
                                            destination: "https://github.com/apvarun/toastify-js",
                                            newWindow: true,
                                            close: false,
                                            gravity: "bottom", // `top` or `bottom`
                                            position: 'right', // `left`, `center` or `right`
                                            backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                            stopOnFocus: true, // Prevents dismissing of toast on hover
                                            onClick: function () {
                                            } // Callback after click
                                        }).showToast();
                                    });
                                }
                            };
                            xhttp.open("POST", "https://sandbox.sbm24.net/api/v2/user/set-time-notification/create?doctor_id={{ $user->id }}", true);
                            xhttp.setRequestHeader("Authorization", "Bearer " + getCookie('access_token_sbm'));
                            xhttp.setRequestHeader("Accept", "application/json");
                            xhttp.setRequestHeader("Content-type", "application/json");
                            xhttp.send();
                        }
                    } else {
                        document.getElementById("login_sbm").style.display = "flex";
                        document.getElementById("login_sbm").setAttribute('data-run', 'reserve_event');
                    }
                }

                function similar() {
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function () {
                        if (this.readyState === 4 && this.status === 200) {
                            var req = JSON.parse(this.responseText);
                            req.data.map(obj => {
                                var name = (obj.doctor_nickname) ? obj.doctor_nickname + ' ' + obj.fullname : obj.fullname;
                                var default_image = "{{ get_template_directory_uri() . '/assets/images/default.jpg' }}";
                                var picture = (obj.picture) ? obj.picture : default_image;
                                var e = document.createElement('div');
                                e.innerHTML = '\n' +
                                    '        <div class="carousel-dr-list">\n' +
                                    '\n' +
                                    '            <div class="col_dr">\n' +
                                    '\n' +
                                    '                <div class="dr_item">\n' +
                                    '                    <div class="bx_sbm_dr">\n' +
                                    '                        <a href="{{ site_url('/') }}' + obj.username + '" title="دریافت نوبت ویزیت و مشاوره پزشکی از ' + name + '">\n' +
                                    '                            <div class="img_cover" style="background-image: url(' + picture + ')">\n' +
                                    '                                <img src="' + picture + '" class="hidden" alt="مشاوره پزشکی آنلاین با ' + name + '" title="مشاوره پزشکی آنلاین با ' + name + '">\n' +
                                    '                            </div>\n' +
                                    '\n' +
                                    '                            <h3>' + name + '</h3>\n' +
                                    //'                            <p class="time_visit">بیش از '+diffDays_lb+' مشاوره در هفته گذشته </p>\n' +
                                    '<br>' +
                                    '                            <hr />\n' +
                                    '                            <h4>' + obj.job_title + '</h4>\n' +
                                    '                        </a>\n' +
                                    '                        <div class="but_get_visit">\n' +
                                    '                            <a href="{{ site_url('/') }}' + obj.username + '" title="دریافت نوبت ویزیت آنلاین از ' + name + '" class="but_sbm">مشاوره بگیر</a>\n' +
                                    '                        </div>\n' +
                                    '                    </div>\n' +
                                    '                </div>\n' +
                                    '\n' +
                                    '            </div>\n' +
                                    '\n' +
                                    '        </div>';
                                while (e.firstChild) {
                                    document.getElementById('empty_time_dr').appendChild(e.firstChild);
                                }
                            });
                            var dsdsdd = new Flickity('.similar_dr', {
                                cellAlign: 'right',
                                initialIndex: 0,
                                contain: true,
                                prevNextButtons: true,
                                pageDots: false,
                                rightToLeft: true
                            });
                        }
                    };
                    var url = "https://sandbox.sbm24.net/api/v2/search/similar?user-limit=10&id={{ $user->id }}";
                    xhttp.open("POST", url, true);
                    xhttp.send();
                }
            </script>
        </div>
        
            
            
        <div class="section_single_dr comment_box">
            <a href="#" class="comment_insert" onclick="open_review(event)">
                ثبت نظر و امتیاز
                <svg height="512" viewBox="0 0 512 512" width="512"
                     xmlns="http://www.w3.org/2000/svg">
                    <g>
                        <path d="m503.22 186.828-25.041-25.041c-11.697-11.698-30.729-11.696-42.427 0l-52.745 52.745v-169.532c0-24.813-20.187-45-45-45h-293c-24.813 0-45 20.187-45 45v422c0 24.813 20.187 45 45 45h293c24.813 0 45-20.187 45-45v-119.033l120.213-118.712c11.697-11.697 11.697-30.73 0-42.427zm-179.399 177.423-45.122 21.058 21.037-45.078 111.975-111.975 24.757 24.756zm29.186 102.749c0 8.271-6.729 15-15 15h-293c-8.271 0-15-6.729-15-15v-422c0-8.271 6.729-15 15-15h293c8.271 0 15 6.729 15 15v199.532c-83.179 83.179-77.747 77.203-79.34 80.616l-39.598 84.854c-2.667 5.717-1.474 12.49 2.986 16.95 4.46 4.461 11.236 5.653 16.95 2.986l84.854-39.599c3.111-1.452 3.623-2.354 14.148-12.748zm104.806-235.067-24.89-24.89 24.043-24.043 25.033 25.049z"/>
                        <path d="m80.007 127h224c8.284 0 15-6.716 15-15s-6.716-15-15-15h-224c-8.284 0-15 6.716-15 15s6.716 15 15 15z"/>
                        <path d="m80.007 207h176c8.284 0 15-6.716 15-15s-6.716-15-15-15h-176c-8.284 0-15 6.716-15 15s6.716 15 15 15z"/>
                        <path d="m208.007 257h-128c-8.284 0-15 6.716-15 15s6.716 15 15 15h128c8.284 0 15-6.716 15-15s-6.716-15-15-15z"/>
                    </g>
                </svg>
            </a>
            <h2>نظرات کاربران درباره پزشک</h2>
            <div class="loader_bo_app" id="comment_box_sc">
                <div class="lds-roller">
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                    <div></div>
                </div>
            </div>
            <div class="comment_list" id="comment_list"></div>
            <script>
                var quality_rate = 0;
                var cost_rate = 0;
                var behaviour_rate = 0;
                rate_load();

                function rate_load() {
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function () {
                        if (this.readyState === 4 && this.status === 200) {
                            var req = JSON.parse(this.responseText);
                            document.getElementById("comment_box_sc").style.display = "none";
                            document.getElementById('comment_list').innerText = '';
                            if (req.data.overall) {
                                var rate = 100 - (((5 - req.data.overall.avg_overall) * 100) / 5);
                            }
                            if (req.data.stars.length > 0) {
                                req.data.stars.map(obj => {
                                    var rate = 100 - (((5 - obj.overall) * 100) / 5);
                                    var user_image = obj.picture;
                                    var default_image = "{{ get_template_directory_uri() . '/assets/images/default_user.png' }}";
                                    if (!user_image || user_image === 'null') {
                                        user_image = default_image;
                                    }
                                    var name = (obj.fullname && obj.fullname !== '  ') ? obj.fullname : 'بدون نام';
                                    var e = document.createElement('div');
                                    e.innerHTML = `<div class="carousel-cell">
                                                            <div class="comment_item">
                                                                <div class="header_comment">
                                                                    <div class="photo" style="background-image: url('${user_image}')">
                                                                        <img src="${user_image}" class="hidden" />
                                                                    </div>
                                                                    <div class="title">
                                                                        <h4>${name}</h4>
                                                                        <div class="comment-rate comment">
                                                                            <div class="MuiRating-readOnly" role="img" aria-label="${obj.overall} Stars">
                                                                                <div class="MuiRating-cont-readOnly" style="width: ${rate + '%'}"></div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="clearfix"></div>
                                                                </div>
                                                                <div class="comment_body">${obj.comment}</div>
                                                                  ${
                                        obj.reply ? `  <div class="comment_item2">
                                                                            <div class="header_comment">
                                                                                <div class="title">
                                                                                    <h4>پاسخ پشتیبان</h4>
                                                                                </div>
                                                                                <div class="clearfix"></div>
                                                                            </div>
                                                                            <div class="comment_reply">${obj.reply}</div>
                                                                        </div>` : ''
                                    }
                                                            </div>
                                                        </div>`;
                                    while (e.firstChild) {
                                        document.getElementById('comment_list').appendChild(e.firstChild);
                                    }
                                });
                                var flkty2 = new Flickity('.comment_list', {
                                    cellAlign: 'right',
                                    contain: true,
                                    pageDots: false,
                                    prevNextButtons: true,
                                    rightToLeft: true
                                });
                            } else {
                                var e = document.createElement('div');
                                e.innerHTML = '<div class="carousel-empty">هیچ نظری ثبت نشده است</div>';
                                while (e.firstChild) {
                                    document.getElementById('comment_list').appendChild(e.firstChild);
                                }
                            }
                        }
                    };
                    var url = "{{ cp_base_URL . "site/doctor/get-star-rate/" . $user->id }}";
                    xhttp.open("GET", url, true);
                    xhttp.send();
                }

                function open_review(evt = null) {
                    if (evt) evt.preventDefault();
                    if (getCookie('access_token_sbm')) {
                        if (getCookie('approve') === '1') {
                            Toastify({
                                text: "شما نمیتوانید به عنوان پزشک برای یک پزشک دیگر نظر ثبت کنید.",
                                duration: 5000,
                                destination: "https://github.com/apvarun/toastify-js",
                                newWindow: true,
                                close: false,
                                gravity: "bottom", // `top` or `bottom`
                                position: 'right', // `left`, `center` or `right`
                                backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                stopOnFocus: true, // Prevents dismissing of toast on hover
                                onClick: function () {
                                } // Callback after click
                            }).showToast();
                        } else {
                            document.getElementById("review_sbm").style.display = "flex";
                        }
                    } else {
                        document.getElementById("login_sbm").style.display = "flex";
                        document.getElementById("login_sbm").setAttribute('data-run', 'open_review');
                    }
                }

                function close_modal_review(evt) {
                    evt.preventDefault();
                    document.getElementById("review_sbm").style.display = "none";
                };

                function set_rate(evt, type) {
                    evt.preventDefault();
                    var items = evt.currentTarget.parentElement.getElementsByClassName('rat_item');
                    if (items) {
                        for (var x = 0; x < items.length; x++) {
                            items[x].classList.remove('active');
                            items[x].classList.remove('isset');
                        }
                    }
                    var rate = evt.currentTarget.getAttribute("data-rate");
                    evt.currentTarget.className += ' isset active';
                    if (type === 'quality') {
                        quality_rate = rate;
                    } else if (type === 'cost') {
                        cost_rate = rate;
                    } else if (type === 'behaviour') {
                        behaviour_rate = rate;
                    }
                };

                function rate_hover(evt) {
                    evt.preventDefault();
                    var items = evt.currentTarget.getElementsByClassName('rat_item');
                    if (items) {
                        for (var x = 0; x < items.length; x++) {
                            items[x].classList.remove('active');
                        }
                    }
                };

                function rate_out_hover(evt) {
                    evt.preventDefault();
                    var items = evt.currentTarget.getElementsByClassName('rat_item');
                    if (items) {
                        for (var x = 0; x < items.length; x++) {
                            for (var i = 0; i < items[x].classList.length; i++) {
                                if (items[x].classList[i] === 'isset') {
                                    items[x].classList.add('active');
                                }
                            }
                        }
                    }
                };

                function review_app(evt) {
                    evt.preventDefault();
                    var review_text = document.getElementById("review_text");
                    var xhttp = new XMLHttpRequest();
                    xhttp.onreadystatechange = function () {
                        if (this.readyState === 4 && this.status === 200) {
                            var req = JSON.parse(this.responseText);
                            if (req.status === 'success') {
                                document.getElementById("review_sbm").style.display = "none";
                                review_text.innerText = '';
                                var items = document.getElementsByClassName('rat_item');
                                if (items) {
                                    for (var x = 0; x < items.length; x++) {
                                        items[x].classList.remove('active');
                                        items[x].classList.remove('isset');
                                    }
                                }
                                rate_load();
                            } else {
                                const Errors = req.data.message;
                                Toastify({
                                    text: Errors,
                                    duration: 5000,
                                    destination: "https://github.com/apvarun/toastify-js",
                                    newWindow: true,
                                    close: false,
                                    gravity: "bottom", // `top` or `bottom`
                                    position: 'right', // `left`, `center` or `right`
                                    backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                    stopOnFocus: true, // Prevents dismissing of toast on hover
                                    onClick: function () {
                                    } // Callback after click
                                }).showToast();
                            }
                        } else if (this.readyState === 4 && this.status === 401) {
                            run_logout();
                            reserve_event();
                        } else if (this.readyState === 4 && this.status === 422) {
                            var req = JSON.parse(this.responseText);
                            const Errors = req.errors;
                            Object.keys(Errors).forEach(function (key) {
                                Toastify({
                                    text: Errors[key][0],
                                    duration: 5000,
                                    destination: "https://github.com/apvarun/toastify-js",
                                    newWindow: true,
                                    close: false,
                                    gravity: "bottom", // `top` or `bottom`
                                    position: 'right', // `left`, `center` or `right`
                                    backgroundColor: "linear-gradient(to right, #944dff, #7747c0)",
                                    stopOnFocus: true, // Prevents dismissing of toast on hover
                                    onClick: function () {
                                    } // Callback after click
                                }).showToast();
                            });
                        }
                    };
                    var user_id = getCookie('user_id');
                    xhttp.open("POST", "https://sandbox.sbm24.net/api/v1/site/doctor/set-star-rate?user_id=" + user_id + "&doctor_id={{ $user->id }}&quality=" + quality_rate + "&cost=" + cost_rate + "&behaviour=" + behaviour_rate + "&comment=" + review_text.value, true);
                    xhttp.setRequestHeader("Authorization", "Bearer " + getCookie('access_token_sbm'));
                    xhttp.setRequestHeader("Accept", "application/json");
                    xhttp.setRequestHeader("Content-type", "application/json");
                    xhttp.send();
                };
            </script>
        </div>

        
        <div class="section_single_dr faq">
            <h2>سوالات پر تکرار</h2>
            <div class="faq_list">
                <ul class="faq">
                    @if ($faq && isset($faq[0]) && $faq[0] && $faq[0]->question) )
                        <li onclick="openFaq(event)">
                            <div class="title"> {{ $faq[0]->question }} 🤵</div>
                            <div class="body_faq">{{ $faq[0]->answer }}</div>
                        </li>
                    @else
                        <li onclick="openFaq(event)">
                            <div class="title">آیا می توانم با {{ $doctor_name }} به صورت آنلاین مشاوره بگیرم و توسط
                                ایشان ویزیت شوم؟ 🤵
                            </div>
                            <div class="body_faq">بله، در سلامت بدون مرز می توانید در کوتاه ترین زمان ممکن با
                                دکتر {{ $doctor_name }} ارتباط برقرار کنید، ویزیت شوید و بدون مراجعه حضوری مشاوره
                                بگیرید.
                            </div>
                        </li>
                    @endif
                    @if ($faq && isset($faq[1]) && $faq[1] && $faq[1]->question)
                        <li onclick="openFaq(event)">
                            <div class="title">{{ $faq[1]->question }} 🤵</div>
                            <div class="body_faq">{{ $faq[1]->answer }}</div>
                        </li>
                    @else
                        <li onclick="openFaq(event)">
                            <div class="title">در چه زمینه هایی می توانم توسط {{ $doctor_name }} به صورت آنلاین ویزیت
                                شوم؟ 🤵
                            </div>
                            <div class="body_faq">{{ $doctor_name }} {{ $user->job_title }} می باشند.</div>
                        </li>
                    @endif
                    @if ($faq && isset($faq[2]) && $faq[2] && $faq[2]->question)
                        <li onclick="openFaq(event)">
                            <div class="title">{{ $faq[2]->question }} 🤵</div>
                            <div class="body_faq">{{ $faq[2]->answer }}</div>
                        </li>
                    @else
                        <li onclick="openFaq(event)">
                            <div class="title">آدرس و شماره تلفن های مطب {{ $doctor_name }} 🤵</div>
                            <div class="body_faq">آدرس مطب {{ $doctor_name }}: {{ $user->address }}</div>
                        </li>
                    @endif
                    @if ($faq && isset($faq[3]) && $faq[3] && $faq[3]->question)
                        <li onclick="openFaq(event)">
                            <div class="title">{{ $faq[3]->question }} 🤵</div>
                            <div class="body_faq">{{ $faq[3]->answer }}</div>
                        </li>
                    @else
                        <li onclick="openFaq(event)">
                            <div class="title">ویزیت حضوری یا غیر حضوری با {{ $doctor_name }} چگونه است؟ چقدر زمان می
                                برد؟ 🤵
                            </div>
                            <div class="body_faq">برای ویزیت حضوری باید به مطب ایشان به آدرس {{ $user->address }}مراجعه
                                فرمایید اما در سلامت بدون مرز می توانید در کوتاه ترین زمان ممکن، بدون مراجعه حضوری و از
                                هرکجای ایران توسط {{ $doctor_name }} ویزیت شوید و مشاوره تصویری کنید.
                            </div>
                        </li>
                    @endif
                </ul>
                <script>
                    function openFaq(evt) {
                        if (evt.currentTarget.className.search("active") !== -1) {
                            evt.currentTarget.className = "";
                        } else {
                            evt.currentTarget.className = "active";
                        }
                    }
                </script>
            </div>
        </div>