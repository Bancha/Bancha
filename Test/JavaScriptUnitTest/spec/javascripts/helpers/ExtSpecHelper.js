/*!
 * Additional neccessary functions for testing ExtJS Code
 * Copyright(c) 2011 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011 Roland Schuetz
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, describe, it, beforeEach, expect, fail, jasmine, Mock */


beforeEach(function() {
    
    // ext errors ishould be catched and result in an error
    if(Ext.Error) { // ext.error only exists in the debug version
        Ext.Error.handle = function(e) {
            throw 'Unexpected Ext.Error thrown: '+e.msg;
        };
    }
    
    
    
    this.addMatchers({
        // now add a custom matcher to test methods where ext errors get thrown
        toThrowExtErrorMsg: function(msg) {
            // if this is exts debug version handle ext errors
            var standardHandler;
            if(Ext.Error) {
                standardHandler = Ext.Error.handle;

                // change error handling inside this function
                Ext.Error.handle = function(e) {
                    throw e.msg;
                };
            }
        
            // now test the function, jasmine style
            expect(this.actual).toThrow(msg);
            
            // reset error handling
            if(Ext.Error) {
                Ext.Error.handle = standardHandler;
            }
            
            // if there was an error the expect() above already thrown it
            return true;
        } //eo toTrowExtErrorMsg
    });
});


// Mock.Proxy extends the Mock object
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
    proxyPrototype.callLastRPCCallback = function(method,arguments) {
        if(!this[method] || !this[method].mostRecentCall || !this[method].mostRecentCall.args) {
            throw "The mock was not called yet!";
        }
        
        // undefined as data is allowed
        arguments = arguments || [];
        
        var args     = this[method].mostRecentCall.args
            callback = args[1],
            scope    = args[2];
        
        callback.apply(scope,arguments);
    };
    
    // fake proxy property for ext
    proxyPrototype.isProxy = true;
    
    // constructor
    return function() {
        var proxy = Object.create(proxyPrototype);
        
        // setModel is always called when creating 
        // an Proxy from model/store
        proxy.expect("setModel");
        return proxy;
    };
}());