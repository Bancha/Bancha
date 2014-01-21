/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * Ext JS and Sencha Touch specific helper functions
 *
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/* global ExtSpecHelper:true, window, alert */

beforeEach(function() {

    // ext errors should be catched and result in an error
    if(Ext.Error) { // ext.error only exists in the debug version
        Ext.Error.handle = function(e) {
            throw 'Unexpected Ext.Error thrown: '+e.msg;
        };
    }

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
                modelExtendsClass;

            // for Ext JS 4
            if(Ext.versions.extjs) {
                modelExtendsClass = Ext.ClassManager.getName(objectFromPath('prototype.superclass',this.actual));
            } else if(Ext.versions.touch) {
                modelExtendsClass = Ext.ClassManager.getName(objectFromPath('superclass',this.actual));
            } else {
                alert('Could not recognize if this is Ext JS 4 or Sencha Touch 2. This comes from '+
                        'Test/JavaScriptUnitTests/spec/helpers/ExtSpecHelper.js.');
            }

            return (
                typeof this.actual === 'function' && // constructor
                className === modelClassName &&      // correct class
                (modelExtendsClass==='Bancha.data.Model' || modelExtendsClass==='Ext.data.Model')); // is a model
        }
    });
});

ExtSpecHelper = {
    /**
     * This helps to regonize if this is a Sencha Touch or Ext JS test.
     */
    isTouch: !!Ext.versions.touch,
    /**
     * This helps to regonize if this is a Sencha Touch or Ext JS test.
     */
    isExt: !!Ext.versions.extjs
};
