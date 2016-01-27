var console=console||{"log":function(){}};

var objLoadedScripts = {};

var Main = function() {

    // Load a script depending on jquery:
    if(objLoadedScripts['jquery']) {
        var objScript = document.createElement('script');
        objScript.src = "lazy.js";
        objScript.async = true;
        document.body.appendChild(objScript);
    }

};

var loadScripts = function() {

    // Load jquery
    var objScriptJquery = document.createElement('script');
    objScriptJquery.onload = function() { 
        objLoadedScripts['jquery'] = true; 
        Main();
    };
    objScriptJquery.onreadystatechange = function() {
        if(this.readyState == 'loaded') {
            objLoadedScripts['jquery'] = true; 
            Main();
        }
    };
    objScriptJquery.src = "//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js";
    objScriptJquery.async = true;
    document.body.appendChild(objScriptJquery);

    // Load event functions:
    /*var objScriptEvents = document.createElement('script');
    objScriptEvents.onload = function() { 
        objLoadedScripts['eventFunctions'] = true; 
        Main();
    };
    objScriptEvents.src = "../general/js/eventFunctions.js";
    objScriptEvents.async = true;
    document.body.appendChild(objScriptEvents);*/

    // Load DOM functions:
    /*var objScriptDom = document.createElement('script');
    objScriptDom.onload = function() { 
        objLoadedScripts['domFunctions'] = true; 
        Main();
    };
    objScriptDom.src = "../general/js/domFunctions.js";
    objScriptDom.async = true;
    document.body.appendChild(objScriptDom);*/

}

if (window.addEventListener)
    window.addEventListener("load", loadScripts, false);
else if (window.attachEvent)
    window.attachEvent("onload", loadScripts);
else window.onload = loadScripts;