/*
 @fileoverview A collection of common event functions

 @author    stephanie@bettercollective.com (Stephanie M. Jensen)
 @since     2013-05-28
 @version   2013-05-28
 */
var addEvent = (function () {

    if(window.addEventListener) {
        return function (el, ev, fn) {
            el.addEventListener(ev, fn, false);
        };
    }
    else if(window.attachEvent) {
        return function (el, ev, fn) {
            el.attachEvent('on' + ev, fn);
        };
    }
    else {
        return function (el, ev, fn) {
            el['on' + ev] = fn;
        };
    }

})();

var removeEvent = (function () {

    if(window.removeEventListener) {
        return function (el, ev, fn) {
            el.removeEventListener(ev, fn, false);
        };
    }
    else if(window.detachEvent) {
        return function (el, ev, fn) {
            el.detachEvent('on' + ev, fn);
        };
    }
    else {
        return function (el, ev, fn) {
            el['on' + ev] = '';
        };
    }

})();