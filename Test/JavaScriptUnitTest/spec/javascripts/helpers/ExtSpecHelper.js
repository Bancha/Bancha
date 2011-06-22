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
            fail('Ext.Error thrown: '+e.msg);
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
                    throw e.message;
                };
            }
        
            // now test the function, jasmine style
            var result = this.toThrow(msg);
        
            // reset error handling
            if(Ext.Error) {
                Ext.Error.handle = standardHandler;
            }
        
            return result;
        } //eo toTrowExtErrorMsg
    });
});


// Mock.Proxy extends the Mock object
Mock.Proxy = (function() {
    
    // expect Mock
    this.prototype = new Mock();
    
    
    // add an expectRPC method to the proxy
    this.prototype.expectRPC = function(method, /*Optional*/firstArg) {
        this.expectCall(method).withArguments(
            firstArg || Mock.Value.Object,
            Mock.Value.Function,
            Mock.Value.Object
        );
    };
    
    // fake proxy property for ext
    this.prototype.isProxy = true;
    
    var Contructor = function() {
        // setModel is always called when creating 
        // an Proxy from model/store
        this.expectCall("setModel");
        return this;
    };
    return function() {
        return new Contructor();
    };
}());