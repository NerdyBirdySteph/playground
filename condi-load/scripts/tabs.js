log('Hello from tabs.js');
require(['scripts/domFunctions.js', 'scripts/lazy.js', 'scripts/mustache.js', 'jquery'], function() {
    log('tabs init()');
    console.log('Test Lazy:');
    console.log(Lazy);
    console.log('Test Mustache:');
    Mustache.print();
    var myObject = {'string':'test','number':7};
    console.log(myObject);
    console.dir(myObject);
    console.log(JSON.stringify(myObject));
    console.log(typeof(myObject));
    console.log(myObject['string']);
    console.log(myObject['number']);
});