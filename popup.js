jQuery(document).ready(function($){
    var $overlay = $('#age-verification-overlay');
    if (!$overlay.length) return;

    function getCookie(name){
        var nameEQ = name + "=";
        var ca = document.cookie.split(';');
        for(var i=0;i<ca.length;i++){
            var c = ca[i].trim();
            if(c.indexOf(nameEQ)==0) return c.substring(nameEQ.length,c.length);
        }
        return "";
    }
    function setCookie(name,value,days){
        var d = new Date();
        d.setTime(d.getTime() + (days*24*60*60*1000));
        document.cookie = name + "=" + value + ";expires=" + d.toUTCString() + ";path=/";
    }

    if(!getCookie('age_verified')) $overlay.css('display','flex').hide().fadeIn(200);

    $('#age-btn-enter').on('click', function(){
        setCookie('age_verified', 'true', 30);
        $overlay.fadeOut(200);
    });

    $('#age-btn-exit').on('click', function(){
        window.location.href = 'https://www.google.com';
    });
});
