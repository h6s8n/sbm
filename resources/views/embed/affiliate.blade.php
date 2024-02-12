<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width,height=device-height,initial-scale=1,maximum-scale=1,user-scalable=no,shrink-to-fit=no">
    <title>لیست پزشکانsbm </title>

    <link rel='stylesheet' id='zm-bootstrap-css'  href='https://sbm24.com/wp-content/themes/sbm24/static/bootstrap/dist/css/bootstrap.min.css?ver=4.9.11' type='text/css' media='all' />
    <link rel='stylesheet' id='zm-bootstrap-rtl-css'  href='https://sbm24.com/wp-content/themes/sbm24/static/css/bootstrap.rtl.min.css?ver=4.9.11' type='text/css' media='all' />
    <link rel='stylesheet' id='zm-flickity-css'  href='https://sbm24.com/wp-content/themes/sbm24/static/css/flickity.css?ver=4.9.11' type='text/css' media='all' />
    <link rel='stylesheet' id='font-awesome-css'  href='https://sbm24.com/wp-content/themes/sbm24/static/css/font-awesome.css?ver=4.9.11' type='text/css' media='all' />
    <link rel='stylesheet' id='zm-select2-css'  href='https://sbm24.com/wp-content/themes/sbm24/static/css/select2.min.css?ver=4.9.11' type='text/css' media='all' />

    <link rel='stylesheet' id='sbm-style-css'  href='https://sbm24.com/wp-content/cache/asset-cleanup/css/item/sbm-style-v0aa7f974175841354dc0da4ed5e997dc849dc6ec.css' type='text/css' media='all' />
    <link rel='stylesheet' id='sbm-responsive-css'  href='https://sbm24.com/wp-content/cache/asset-cleanup/css/item/sbm-responsive-v2078cd85642f9ec153d0ae1ac42abc8172608e5d.css' type='text/css' media='all' />

    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/jquery-3.2.1.min.js'></script>
    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/bootstrap.min.js'></script>
    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/flickity.pkgd.min.js'></script>
    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/select2.min.js'></script>
    <script type='text/javascript' src='https://sbm24.com/wp-content/themes/sbm24/static/js/script.js'></script>

</head>
<body style="padding: 0; margin: 0;">


<div id="similar_box">
    <div id="empty_time_dr" class="similar_dr">

    </div>
</div>


<script>



    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState === 4 && this.status === 200) {

            var req = JSON.parse(this.responseText);

            req.data.map(obj => {

                var name  = (obj.doctor_nickname) ? obj.doctor_nickname + ' ' + obj.fullname : obj.fullname;
                var default_image = "<?= 'https://sbm24.com/wp-content/themes/sbm_luxery/assets/images/default.jpg' ?>";
                var picture  = (obj.picture) ? obj.picture : default_image;


                var e = document.createElement('div');
                e.innerHTML = '\n' +
                    '        <div class="carousel-dr-list">\n' +
                    '\n' +
                    '            <div class="col_dr">\n' +
                    '\n' +
                    '                <div class="dr_item">\n' +
                    '                    <div class="bx_sbm_dr">\n' +
                    '                        <a href="<?= 'https://sbm24.com/' ?>' + obj.username + '" title="دریافت نوبت ویزیت و مشاوره پزشکی از '+name+'">\n' +
                    '                            <div class="img_cover" style="background-image: url('+picture+')">\n' +
                    '                                <img src="'+picture+'" class="hidden" alt="مشاوره پزشکی آنلاین با '+name+'" title="مشاوره پزشکی آنلاین با '+name+'">\n' +
                    '                            </div>\n' +
                    '\n' +
                    '                            <h3>'+name+'</h3>\n' +
                    //'                            <p class="time_visit">بیش از '+diffDays_lb+' مشاوره در هفته گذشته </p>\n' +
                    '<br>' +
                    '                            <hr />\n' +
                    '                            <h4>'+obj.job_title+'</h4>\n' +
                    '                        </a>\n' +
                    '                        <div class="but_get_visit">\n' +
                    '                            <a href="<?= 'https://sbm24.com/' ?>' + obj.username + '" title="دریافت نوبت ویزیت آنلاین از '+name+'" class="but_sbm">مشاوره بگیر</a>\n' +
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


            var dsdsdd = new Flickity( '.similar_dr', {
                cellAlign: 'right',
                initialIndex: 0,
                contain: true,
                prevNextButtons: true,
                pageDots: false,
                rightToLeft: true
            });

        }
    };

    var url = "https://sandbox.sbm24.net/api/v1/search/?search=<?= $tag ?>";
    xhttp.open("GET", url, true);
	   console.log("this is log : "xhttp.open("GET", url, true));

    xhttp.send();


</script>

</body>
</html>
