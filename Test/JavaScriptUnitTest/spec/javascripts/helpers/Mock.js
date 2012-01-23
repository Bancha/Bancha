/*!
 * Mock object for easier testing
 * Copyright(c) 2011-2012 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011-2012 Roland Schuetz
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, describe, it, beforeEach, expect, jasmine, Mock */

/**
 * create an mock
 * a mock encapsulates way more functionality then a simple spy
 * 1. define expectations and verify them later
 * 2. check of ONLY the expectation are met, no other functions got called
 */
(function() {
    /*
     * helper functions for verify
     */
    var isArray = function(testObject) {   
        return testObject && !(testObject.propertyIsEnumerable('length')) && typeof testObject === 'object' && typeof testObject.length === 'number';
    };
    var getType = function(obj) {
        if(typeof obj==='object') {
            return (isArray(obj)) ? 'array' : 'object';
        } else {
            return typeof obj;
        }
    };
    var toString = function(obj) {
        if(typeof obj==='string') {
            // add slashes for objings
            return '"'+obj+'"';
        } else if(getType(obj)==='array') {
            // arrays should have brackets
            return "["+obj+"]";
        } else {
            return obj;
        }
    };
    // we need a own expect for usefull error messages
    var expectEqual = function(expected,actual,path,msg) {
        if(expected !== actual) {
            if(msg) {
                throw (path+msg);
            } else {
                throw (path+": Expected "+toString(actual)+"("+getType(actual)+") to be equal to "+toString(expected)+"("+getType(expected)+")");
            }
        }
    };
    var verifyArguments = function(expected,actual,path) {
        var i, len, name;
        
        switch(expected) {
            case Mock.Value.Any: return; // everything done
            case Mock.Value.Function: 
                expectEqual(true,(typeof actual==='function'),path,": Expected "+toString(actual)+"("+getType(actual)+") to be a function.");
                return;
            case Mock.Value.Object: 
                expectEqual(true,(getType(actual)==='object'),path,": Expected "+toString(actual)+"("+getType(actual)+") to be an object.");
                return;
            case Mock.Value.Array: 
                expectEqual(true,isArray(actual),path,": Expected "+toString(actual)+"("+getType(actual)+") to be an array.");
                return;
            case Mock.Value.String: 
                expectEqual(true,(typeof actual==='string'),path,": Expected "+toString(actual)+"("+getType(actual)+") to be a string.");
                return;
            case Mock.Value.Number: 
                expectEqual(true,(typeof actual==='number'),path,": Expected "+toString(actual)+"("+getType(actual)+") to be a number.");
                return;
            case Mock.Value.Boolean: 
                expectEqual(true,(typeof actual==='boolean'),path,": Expected "+toString(actual)+"("+getType(actual)+") to be a boolean.");
                return;
        }
        
        // really compare elements
        if(getType(expected) !== getType(actual)) {
            // output the error
            expectEqual(expected,actual,path);
            return;
        }
        if(isArray(expected)) {
            if(expected.length!==actual.length) {
                throw (path+": Expected an array of length "+expected.length+", but got "+actual.length);
            }
            for(i=0,len=expected.length; i<len; i++) {
                verifyArguments(expected[i],actual[i],path+"["+i+"]");
            }
            return;
        }
        if(getType(expected)==='object') {
            //  check all properties on both sides
            for (name in expected) {
                if (expected.hasOwnProperty(name)) {
                    verifyArguments(expected[name],actual[name],path+"."+name);
                }
            }
            for (name in actual) {
                if (actual.hasOwnProperty(name)) {
                    verifyArguments(expected[name],actual[name],path+"."+name);
                }
            }
            return;
        }

        // it's a primitiv type
        expectEqual(expected,actual,path);
        return; // we're done
    };
    var verifySpy = function(expectations,spy,path) {
        var i, len, spyArgs;

        // first of all the spy should be called exactly as often as the expectations where
        expectEqual(spy.callCount,expectations.length,"","Expected "+path+" to be called "+expectations.length+"x instead of "+spy.callCount+"x");

        // so now check each calls arguments
        for(i=0,len=expectations.length;i<len;i++) {
            spyArgs = Array.prototype.slice.call(spy.argsForCall[i]);
            verifyArguments(expectations[i],spyArgs,path+"'s call "+i+". Argument");
        }
    };
    
    var mockPrototype = {}; // used in the Mock() fn
    
    /**
     * define a new expectation
     */
    mockPrototype.expect = function(method) {
        var expectations = this.expectations,
            result,
            current; // queue reference nr. for nested function withArguments
        
        if(this[method] && this[method].isSpy) {
            // there's already an method, add expectation to the queue
            current = expectations[method].length;
            result = this[method];
        } else {
            // hock spy to the mock
            this[method] = result = jasmine.createSpy();
            
            // create expectations queue
            expectations[method] = [];
            current = 0; // it's the first expectation
        }
        
        // create expectation, just that it got called
        expectations[method][current] = Mock.Value.Any;
        
        // the spy already support functions like andCallThrough()
        // add a function for arguments validation
        result.withArguments = function(arg1,arg2,arg3) {
            // bind arguments to the expectation
            expectations[method][current] = arguments;
        };
        return result;
    };
    
    /**
     * checks if all expectations are met and ONLY those
     */
    mockPrototype.verify = function() {
        var method;
        
        // go through all spy functions
        for (method in this) {
            if (this.hasOwnProperty(method) && this[method].isSpy) {
                verifySpy(this.expectations[method],this[method],"mock function \""+method+'"');
            }
        }
    };
    
    
    // ES5 15.2.3.5
    // https://github.com/kriskowal/es5-shim/blob/master/es5-shim.js
    if (!Object.create) {
        Object.create = function create(prototype, properties) {
            var object;
            if (prototype === null) {
                object = { "__proto__": null };
            } else {
                if (typeof prototype !== "object")
                    throw new TypeError("typeof prototype["+(typeof prototype)+"] != 'object'");
                var Type = function () {};
                Type.prototype = prototype;
                object = new Type();
                // IE has no built-in implementation of `Object.getPrototypeOf`
                // neither `__proto__`, but this manually setting `__proto__` will
                // guarantee that `Object.getPrototypeOf` will work as expected with
                // objects created using `Object.create`
                object.__proto__ = prototype;
            }
            if (typeof properties !== "undefined")
                Object.defineProperties(object, properties);
            return object;
        };
    }
    
    Mock = function() {
        var mock = Object.create(mockPrototype);
        
        mock.isMock = true;
        mock.expectations = {};
        /* expectations structure: {
            method1: [ // this is a queue
                [arg1,arg2] // method1 is expected to get called once with this args
            ]
        } */
        return mock;
    };
    Mock.prototype = mockPrototype; // for Mocks functions to be available
}());


Mock.Value = {
    /**
     * expect Any value
     */
    'Any': -9007199254740900, // very unlikely numbers
    /**
     * expect Any object
     */
    'Function': -9007199254740901,
    /**
     * expect Any object
     */
    'Object': -9007199254740902,
    /**
     * expect Any array
     */
    'Array': -9007199254740903,
    /**
     * expect Any string
     */
    'String': -9007199254740904,
    /**
     * expect Any number
     */
    'Number': -9007199254740905,
    /**
     * expect Any boolean
     */
    'Boolean': -9007199254740906
};