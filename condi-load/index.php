<!DOCTYPE html>
<head>
    <meta charset="UTF-8">
    <base href="http://localhost/playground/<?php echo basename(__DIR__) ; ?>/">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CondiLoad</title>
    <link href="main.css" rel="stylesheet" />
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="scripts/modernizr.js"></script>
    <script type="text/javascript">
var logWindow = false
    , arrLog = []
    , intStartTime = new Date().getTime()
    , scripts
    , src
    , script
    , objPendingScripts = {}
    , objLoadedScripts = {'jquery':{}}
    , domReadyScripts = []
    , windowLoadScripts = []
    , firstScript = document.scripts[0];

function log(strMsg) {
    var intTimeNow = new Date().getTime()
        , strTime = ((intTimeNow - intStartTime) / 1000).toFixed(3)
        , strOutput = strTime + ": " + strMsg + "<br>";
    if(!logWindow) {
        arrLog.push(strOutput);
    }
    else {
        logWindow.innerHTML = logWindow.innerHTML + strOutput;
        window.scrollTo(0,document.body.scrollHeight);
    }
};
$(document).ready(function() {
    logWindow = document.getElementById('log');
    logWindow.innerHTML = logWindow.innerHTML + arrLog.join('');
    arrLog = [];
});

scripts = [
    {
        'wait': false
        , 'condition': true
        , 'src': 'scripts/main.js'
    },
    {
        'wait': false
        , 'condition': true
        , 'src': 'scripts/ajaxConnector.js'
    },
    {
        'wait': false
        , 'condition': true
        , 'src': 'scripts/domFunctions.js'
    },
    {
        'wait': 'DOM'
        , 'condition': '.dropdown'
        , 'src': 'scripts/dropdown.js'
    },
    {
        'wait': 'DOM'
        , 'condition': '.tabs'
        , 'src': 'scripts/tabs.js'
    },
    {
        'wait': false
        , 'condition': true
        , 'src': 'scripts/polyfills/classList.js'
    },
    {
        'wait': 'DOM'
        , 'condition': true
        , 'src': 'scripts/mustache.js'
    },
    {
        'wait': false
        , 'condition': true
        , 'src': 'scripts/polyfills/html5shiv.js'
    },
    {
        'wait': false
        , 'condition': (typeof(JSON) === 'undefined' || typeof(JSON.parse) !== 'function')
        , 'src': 'scripts/polyfills/json.js'
    },
];

function require(arrScripts, callback) {
    
    log('Require: ' + arrScripts.join(', '));

    var objTimer
        , arrLoadScripts = []
        , arrWaitingScripts = []
        , intScriptsToLoad = arrScripts.length;

    function waitForLoadingScripts() {

        function checkLoadingScripts() {
            
            var key
                , i = (arrWaitingScripts.length - 1);
            while(i >= 0) {
                key = arrWaitingScripts[i];
                if(!(key in objPendingScripts)) {
                    arrWaitingScripts.splice(i, 1);
                    intScriptsToLoad -= 1;
                    if(intScriptsToLoad === 0) {
                        //log('All required scripts loaded 2');
                        callback();
                        return;
                    }
                }
                i -= 1;
            }
            if(arrWaitingScripts.length > 0) {
                objTimer = window.setTimeout(checkLoadingScripts, 10);
            }
        };

        objTimer = window.setTimeout(checkLoadingScripts, 10);

    }

    while (src = arrScripts.shift()) {
        if(src in objLoadedScripts) { 
            //log(src + " has already been loaded");
            intScriptsToLoad -= 1;
            continue; 
        }
        if(src in objPendingScripts) { 
            //log(src + " is pending for load");
            arrWaitingScripts.push(src);
            continue; 
        }
        arrLoadScripts.push({
            'wait': false
            , 'condition': true
            , 'src': src
        });
    }

    //log('Scripts: ' + intScriptsToLoad);

    if(arrWaitingScripts.length > 0) {
        //log('Wait for: ' + arrWaitingScripts.length);
        waitForLoadingScripts();
    }

    if(arrLoadScripts.length > 0) {
        //log('Load: ' + arrLoadScripts.length);
        loadScripts(arrLoadScripts, function() {
            intScriptsToLoad -= 1;
            if(intScriptsToLoad === 0) {
                log('All required scripts loaded 1');
                clearInterval(objTimer);
                callback();
                return;
            }
        });
    }
    
    if(intScriptsToLoad === 0) {
        log('No scripts to load');
        clearInterval(objTimer);
        callback();
    }
};

// Watch scripts load in IE
function stateChange() {
    // Execute as many scripts in order as we can
    var pendingScript, key;
    //while (objPendingScripts[0] && objPendingScripts[0]['script'].readyState == 'loaded') {
    for (key in objPendingScripts) {
        pendingScript = objPendingScripts[key];
        if(pendingScript['script'].readyState == 'loaded') {
            delete objPendingScripts[key];
            // avoid future loading events from this script (eg, if src changes)
            pendingScript['script'].onreadystatechange = null;
            // can't just appendChild, old IE bug if element isn't closed
            firstScript.parentNode.insertBefore(pendingScript['script'], firstScript);
            log('Ready: ' + key);
            //objLoadedScripts.push(pendingScript['script'].src);
            objLoadedScripts[key] = {};
            if(typeof(pendingScript['callback']) === 'function') {
                pendingScript['callback']();
            }
        }
    }
}

function scriptLoaded(e) {

    var key = this.getAttribute('src') 
        , pendingScript;
    if(key in objPendingScripts) {
        pendingScript = objPendingScripts[key];
        delete objPendingScripts[key];
        log('Ready: ' + key);
        objLoadedScripts[key] = {};
        if(typeof(pendingScript['callback']) === 'function') {
            pendingScript['callback']();
        }
    }

}

function loadScripts(scripts, callback) {
    // loop through our script urls
    while (objScript = scripts.shift()) {
        if('DOM' == objScript.wait) {
            objScript.wait = false;
            domReadyScripts.push(objScript);
            continue;
        }
        else if('window' == objScript.wait) {
            objScript.wait = false;
            windowLoadScripts.push(objScript);
            continue;
        }
        if('string' === typeof(objScript.condition) && $(objScript.condition).length < 1) {
            continue;
        }
        else if('boolean' === typeof(objScript.condition) && false === objScript.condition) {
            //console.log('Don\'t load ' + objScript.src);
            continue;
        }
        log('Pending: ' + objScript.src);
        if ('async' in firstScript) { // modern browsers
            script = document.createElement('script');
            script.async = false;
            //objPendingScripts.push({'script': script, 'callback': callback});
            objPendingScripts[objScript.src] = {'script': script, 'callback': callback};
            script.onload = scriptLoaded;
            script.src = objScript.src;
            document.head.appendChild(script);
        }
        else if (firstScript.readyState) { // IE<10
            // create a script and add it to our todo pile
            script = document.createElement('script');
            script.type = 'text/javascript';
            //objPendingScripts.push({'script': script, 'callback': callback});
            objPendingScripts[objScript.src] = {'script': script, 'callback': callback};
            // listen for state changes
            script.onreadystatechange = stateChange;
            // must set src AFTER adding onreadystatechange listener
            // else we’ll miss the loaded event for cached scripts
            script.src = objScript.src;
        }
        else { // fall back to defer
            //objPendingScripts.push({'script': script, 'callback': callback});
            objPendingScripts[objScript.src] = {'script': script, 'callback': callback};
            document.write('<script src="' + objScript.src + '" type="text/javascript" onload="scriptLoaded();" defer></'+'script>');
        }
        
    }
}
loadScripts(scripts);
$(document).ready(function() { loadScripts(domReadyScripts); });
$(window).load(function() { loadScripts(windowLoadScripts); });
    </script>
</head>
<body>
    <p><a href="http://www.html5rocks.com/en/tutorials/speed/script-loading/" target="_blank">Jake Archibald is genius!</a></p>
    <h2>Log:</h2>
    <div class="dropdown">DropDown module</div>
    <div class="tabs">Tabs module</div>
    <pre id="log"></pre>
</body>