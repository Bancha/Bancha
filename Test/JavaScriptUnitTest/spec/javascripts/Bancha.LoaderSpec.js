/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * Tests for the Bancha.loader.Proxy class
 *
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, spyOn, runs, waitsFor, Mock, ExtSpecHelper, BanchaSpecHelper, BanchaObjectFromPathTest */

describe("Bancha.Loader", function() {
    // prepare test loader
    var loader = Ext.Loader,
        myLoader = Ext.create('Bancha.loader.Interface');

    it("should extend Ext.Loader to allow setting own class loaders.", function() {
        expect(loader.getDefaultLoader).toBeAFunction();
        expect(loader.setDefaultLoader).toBeAFunction();

        var obj = {};
        loader.setDefaultLoader(obj);

        expect(loader.getDefaultLoader()).toBe(obj);

        // cleanup
        loader.setDefaultLoader(null);
    });

    it("should allow using own class loaders for sync requires in the whole application.", function() {
        // use test loader
        loader.setDefaultLoader(myLoader);

        // prepare spys
        var handlesFn = spyOn(myLoader, 'handles').andReturn(true),
            loadClassFn = spyOn(myLoader, 'loadClass'),
            callback  = jasmine.createSpy('require-callback');

        // test async Ext.require
        Ext.require('Bancha.loader.TestClass1', callback);
        expect(handlesFn).toHaveBeenCalledWith('Bancha.loader.TestClass1');
        expect(loadClassFn).toHaveBeenCalled();
        expect(loadClassFn.mostRecentCall.args[0]).toEqual('Bancha.loader.TestClass1'); // check that the className was correctly injected
        expect(loadClassFn.mostRecentCall.args[1]).toBeAFunction();
        expect(loadClassFn.mostRecentCall.args[2]).toBeAFunction();
        expect(loadClassFn.mostRecentCall.args[3]).toBeAnObject();
        expect(loadClassFn.mostRecentCall.args[4]).toEqual(false);
        expect(callback.callCount).toEqual(0);

        // now trigger loaded loader onLoad callback with scope
        Ext.define('Bancha.loader.TestClass1', {});
        loadClassFn.mostRecentCall.args[1].call(loadClassFn.mostRecentCall.args[3]);

        // check if user callback was triggered
        expect(callback).toHaveBeenCalled();

        // cleanup
        loader.setDefaultLoader(null);
    });


    it("should allow using own class loaders for sync requires in the whole application.", function() {
        // use test loader
        loader.setDefaultLoader(myLoader);

        // prepare spys
        var handlesFn = spyOn(myLoader, 'handles').andReturn(true),
            loadClassFn = spyOn(myLoader, 'loadClass');

        // test async Ext.require
        Ext.syncRequire('Bancha.loader.TestClass2');
        expect(handlesFn).toHaveBeenCalledWith('Bancha.loader.TestClass2');
        expect(loadClassFn).toHaveBeenCalled();
        expect(loadClassFn.mostRecentCall.args[0]).toEqual('Bancha.loader.TestClass2'); // check that the className was correctly injected
        expect(loadClassFn.mostRecentCall.args[1]).toBeAFunction();
        expect(loadClassFn.mostRecentCall.args[2]).toBeAFunction();
        expect(loadClassFn.mostRecentCall.args[3]).toBeAnObject();
        expect(loadClassFn.mostRecentCall.args[4]).toEqual(true); //syncEnabled!

        // we do now check if the request is syncronous, since this is 
        // part of the logger implementation

        // cleanup
        loader.setDefaultLoader(null);
    });

}); //eo describe loader proxy functions