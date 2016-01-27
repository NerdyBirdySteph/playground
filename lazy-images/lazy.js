var Lazy = {
    'init': function() {
        var arrLazyImages = $('.lazy');
        $.each(arrLazyImages, function(i, objLazyImage) {
            Lazy.load(objLazyImage);
        });
    }
    , 'load': function(obj) {
        var strSrc = $(obj).attr('data-src'),
            objImg;
        if(typeof(strSrc) === 'undefined' || false === strSrc) { return; }
        objImg = new Image();
        objImg.onload = (function() { Lazy.loaded(obj, strSrc); });
        objImg.src = strSrc;
    }
    , 'loaded': function(obj, strSrc) {
        $(obj).removeAttr('data-src').attr('src', strSrc).removeClass('lazy').addClass('lazy-loaded');
    }
};
Lazy.init();