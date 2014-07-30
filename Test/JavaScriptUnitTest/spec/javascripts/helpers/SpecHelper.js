/*!
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * General spec helper functions
 *
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://bancha.io
 */
/* global window */

/*
 * Add some basic matcher functions for type checks
 */
beforeEach(function() {
    this.addMatchers({
        toBeAFunction: function() {
            return (typeof this.actual === 'function');
        },
        toBeAnObject: function() {
            return typeof this.actual === 'object';
        },
        toBeAnArray: function() {
            var isArray = function(testObject) {
                return testObject && !(testObject.propertyIsEnumerable('length')) &&
                        typeof testObject === 'object' && typeof testObject.length === 'number';
            };
            return isArray(this.actual);
        },
        toBeAString: function() {
            return typeof this.actual === 'string';
        },
        toBeANumber: function() {
            return typeof this.actual === 'number';
        }
    });
});


(function() {

    /**
     * Add Array.reduce for ES3 implementations (IE 6-8)
     * Uses in objectFromPath below
     * See http://www.sencha.com/forum/showthread.php?273799
     */
    /* jshint bitwise:false */
    if ('function' !== typeof Array.prototype.reduce) {
        Array.prototype.reduce = function(callback, optInitialValue){
            'use strict';
            if (null === this || 'undefined' === typeof this) {
                // At the moment all modern browsers, that support strict mode, have
                // native implementation of Array.prototype.reduce. For instance, IE8
                // does not support strict mode, so this check is actually useless.
                throw new TypeError(
                    'Array.prototype.reduce called on null or undefined');
            }
            if ('function' !== typeof callback) {
                throw new TypeError(callback + ' is not a function');
            }
            var index, value,
                length = this.length >>> 0,
                isValueSet = false;
            if (1 < arguments.length) {
                value = optInitialValue;
                isValueSet = true;
            }
            for (index = 0; length > index; ++index) {
                if (this.hasOwnProperty(index)) {
                    if (isValueSet) {
                        value = callback(value, this[index], index, this);
                    }
                    else {
                        value = this[index];
                        isValueSet = true;
                    }
                }
            }
            if (!isValueSet) {
                throw new TypeError('Reduce of empty array with no initial value');
            }
            return value;
        };
    }
    /* jshint bitwise:true */
    
    /**
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
    /*
     * Test if this property exists and if so return an
     * expect-object to make additional tests on
     */
    jasmine.Matchers.prototype.property = function(path) {
        var property = objectFromPath(path,this.actual);

        // if the property doesn't exist fail
        if(property===null) {
            this.fail("Property "+path+" doesn't exist.");
        }

        // return expect for property
        return expect(property);
    };
}());
