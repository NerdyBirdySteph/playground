var listEvents = {

    'objEventListeners': {
            'totals': {}
            , 'elements': []
        },


    'getEventListenersForObject': function(obj) {

        var eventListeners = getEventListeners(obj)
            , objEvents = {}
            , arrEventNames = []
            , blnEventFound = false;

        for(var strEvent in eventListeners) {
            if(!eventListeners.hasOwnProperty(strEvent)) { continue; }
            if(!listEvents.objEventListeners['totals'][strEvent]) {
                listEvents.objEventListeners['totals'][strEvent] = 1;
            }
            else {
                listEvents.objEventListeners['totals'][strEvent] += 1;
            }
            objEvents[strEvent] = eventListeners[strEvent][0];
            arrEventNames.push(strEvent);
            blnEventFound = true;
        }
        if(blnEventFound) {
            listEvents.objEventListeners['elements'].push({
                'element': obj
                , 'html': (obj.outerHTML ? obj.outerHTML.substring(0,500) : 'undefined')
                , 'eventlist': arrEventNames.join(', ')
                , 'events': objEvents
            });
        }
    },

    'init': function() {

        var domNodes = document.getElementsByTagName('*');

        listEvents.getEventListenersForObject(document);

        for(var i = 0, intTotalNodes = domNodes.length; i < intTotalNodes; i += 1) {
            listEvents.getEventListenersForObject(domNodes[i]);
        }

        console.log('Total nodes: ' + domNodes.length);
        console.log(listEvents.objEventListeners);
    }

};
listEvents.init();