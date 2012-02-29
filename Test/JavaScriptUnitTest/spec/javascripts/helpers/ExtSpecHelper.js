/*!
 * Additional neccessary functions for testing ExtJS Code
 * Copyright(c) 2011-2012 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011-2012 Roland Schuetz
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, describe, it, beforeEach, expect, fail, jasmine, Mock */


beforeEach(function() {
    
    // ext errors should be catched and result in an error
    if(Ext.Error) { // ext.error only exists in the debug version
        Ext.Error.handle = function(e) {
            throw 'Unexpected Ext.Error thrown: '+e.msg;
        };
    }
    
    
    
	/**
	 * used for matcher isModelClass
	 * Safely finds an object, used internally for getStubsNamespace and getRemoteApi
	 * (This function is tested in RS.util, not part of the package testing, but it is tested)
	 * @param {String} path A period ('.') separated path to the desired object (String).
	 * @param {String} lookIn optional: The object on which to perform the lookup.
	 * @return {Object} The object if found, otherwise undefined.
	 * @member Bancha
	 * @method objectFromPath
	 * @private
	 */
	var objectFromPath = function(path, lookIn) {
	    if (!lookIn) {
	        //get the global object so it don't use hasOwnProperty on window (IE incompatible)
	        var first = path.indexOf('.'),
	            globalObjName,
	            globalObj;
	        if (first === -1) {
	            // the whole path is only one object so eturn the result
	            return window[path];
	        }
	        // else the first part as global object name
	        globalObjName = path.slice(0, first);
	        globalObj = window[globalObjName];
	        if (typeof globalObj === 'undefined') {
	            // path seems to be false
	            return undefined;
	        }
	        // set the ne lookIn and the path
	        lookIn = globalObj;
	        path = path.slice(first + 1);
	    }
	    // get the object
	    return path.split('.').reduce(function(o, p) {
	        if(o && o.hasOwnProperty(p)) {
	            return o[p];
	        }
	    }, lookIn);
	};
	
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
        }, //eo toTrowExtErrorMsg
        
        // test if a function is of an specific ext class
        toBeOfClass: function(className) {
            return Ext.ClassManager.getName(this.actual) === className; // right class
        },
        
        // test if a function is an constructor of a model class
        toBeModelClass: function(className) {
            var modelClassName = Ext.ClassManager.getName(this.actual),
                modelExtendsClass = Ext.ClassManager.getName(objectFromPath('prototype.superclass',this.actual));
            
            return (
                typeof this.actual === 'function' && // constructor
                className === modelClassName &&      // correct class
                (modelExtendsClass==='Bancha.data.Model' || modelExtendsClass==='Ext.data.Model')); // is a model
        }
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