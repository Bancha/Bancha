/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * General spec helper functions
 *
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine */


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
        var isArray = function(testObject) { return testObject && !(testObject.propertyIsEnumerable('length')) && typeof testObject === 'object' && typeof testObject.length === 'number'; };
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
