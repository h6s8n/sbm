a(function(d) {

var responsiveStartStr = '<style>.h_iframe-zibatran_embed_frame{position:relative;} .h_iframe-zibatran_embed_frame .ratio {display:block;width:100%;height:auto;} .h_iframe-zibatran_embed_frame iframe {position:absolute;top:0;left:0;width:100%; height:100%;}</style>';
responsiveStartStr += '<div class="h_iframe-zibatran_embed_frame"> <span style="display: block;padding-top: 56.2%"></span><div style="display:none"><h2><a href="https://sbm24.com/دکتر-میلاد-مصطفایی">‌دکتر میلاد مصطفایی</a></h2></div>';
    var responsiveEndStr = '</div>';

var newiframe = document.createElement('iframe');
newiframe.setAttribute('width','100%');
newiframe.setAttribute('height','100%');
newiframe.setAttribute('allowFullScreen','true');
newiframe.setAttribute('webkitallowfullscreen','true');
newiframe.setAttribute('mozallowfullscreen','true');a
newiframe.setAttribute('src','{{ url('affiliate/' . $code . '/' . $tag) }}');
setTimeout(function(){

document.getElementById('<?php echo $param ?>').innerHTML = responsiveStartStr+responsiveEndStr;
document.getElementById('<?php echo $param ?>').getElementsByClassName('h_iframe-zibatran_embed_frame')[0].appendChild(newiframe);

}, 200);

})();
