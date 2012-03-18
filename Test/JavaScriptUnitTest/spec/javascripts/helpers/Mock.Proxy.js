/*!
 * Additional neccessary functions for testing ExtJS and Sencha Touch Code
 * Copyright(c) 2011-2012 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011-2012 Roland Schuetz
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*global Ext, Mock */


/**
 * Mock.Proxy extends the Mock object and provides 
 * an easier interface for ExtJS testing
 */
Mock.Proxy = (function() {
    
    // extend Mock
    var proxyPrototype = new Mock();
    
    
    // add an expectRPC method to the proxy
    proxyPrototype.expectRPC = function(method, /*Optional*/firstArg) {
        // expect ext direct call
        this.expect(method).withArguments(
            firstArg || Mock.Value.Object,
            Mock.Value.Function,
            Mock.Value.Object
        );
    };
    
    // looks like the server has answered with some data
    proxyPrototype.callLastRPCCallback = function(method,args) {
        if(!this[method] || !this[method].mostRecentCall || !this[method].mostRecentCall.args) {
            throw "The mock was not called yet!";
        }
        
        // undefined as data is allowed
        args = args || [];
        
        var methodArgs     = this[method].mostRecentCall.args,
            callback = methodArgs[1],
            scope    = methodArgs[2];
        
        callback.apply(scope,args);
    };
    
    // fake proxy property for ext
    proxyPrototype.isProxy = true;
    
    // constructor
    return function() {
        var proxy = Object.create(proxyPrototype);
        
        // setModel is always called when creating 
        // an Proxy from model/store, totally unimportant for us
        proxy.setModel = function() {};
        return proxy;
    };
}());

//eof
