/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * Tests for the main Bancha class
 *
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, spyOn, runs, waitsFor, Mock, ExtSpecHelper, BanchaSpecHelper, BanchaObjectFromPathTest */

describe("Bancha Logger", function() {
    var h = BanchaSpecHelper; // helper shortcut

    beforeEach(h.reset);
    
    it("should write to the console using logToConsole", function() {

        // this may make problems if console is native to the browser
        var console = window.console;
        window.console = {
            log: jasmine.createSpy()
        };

        // everything goes to the log now
        Bancha.Logger.logToBrowser('My error');
        expect(window.console.log.callCount).toEqual(1);
        expect(window.console.log.mostRecentCall.args).toEqual(['ERROR: My error']);
        Bancha.Logger.logToBrowser('My warning','warn');
        expect(window.console.log.callCount).toEqual(2);
        expect(window.console.log.mostRecentCall.args).toEqual(['WARN: My warning']);
        Bancha.Logger.logToBrowser('untranslatable','missing_translation');
        expect(window.console.log.callCount).toEqual(3);
        expect(window.console.log.mostRecentCall.args).toEqual(['MISSING TRANSLATION: untranslatable']);

        window.console = {
            log: jasmine.createSpy(),
            error: jasmine.createSpy(),
            warn: jasmine.createSpy()
        };

        // now use the specific functions
        Bancha.Logger.logToBrowser('My error2','error');
        Bancha.Logger.logToBrowser('My warning2','warn');
        Bancha.Logger.logToBrowser('untranslatable2','missing_translation');

        expect(window.console.log.callCount).toEqual(0);

        expect(window.console.error.callCount).toEqual(1);
        expect(window.console.error.mostRecentCall.args).toEqual(['My error2']);

        expect(window.console.warn.callCount).toEqual(2);
        expect(window.console.warn.calls[0].args).toEqual(['My warning2']);
        expect(window.console.warn.mostRecentCall.args).toEqual(['MISSING TRANSLATION: untranslatable2']);

        // tear down
        window.console = console;
    });

    it("should log to the browser window or console depending on the support", function() {

        // this may make problems if console is native to the browser
        var console = window.console;
        window.console = {}; // deleting doesn't work in Chrome

        // setup the browser alert
        var alert = Ext.Msg.alert;
        Ext.Msg.alert = jasmine.createSpy();


        // there is no console, so use Ext.Msg.alert
        Bancha.Logger.logToBrowser('My error');
        expect(Ext.Msg.alert.callCount).toEqual(1);
        expect(Ext.Msg.alert.mostRecentCall.args).toEqual(['ERROR','My error']);

        window.console = {
            log: jasmine.createSpy(),
            error: jasmine.createSpy()
        };

        // console is back, use it
        Bancha.Logger.logToBrowser('My error');
        expect(Ext.Msg.alert.callCount).toEqual(1);
        expect(window.console.log.callCount).toEqual(0);
        expect(window.console.error.callCount).toEqual(1);
        expect(window.console.error.mostRecentCall.args).toEqual(['My error']);

        // tear down
        window.console = console;
        Ext.Msg.alert = alert;
    });

    it("should log to the server in production mode and to console in debug mode", function() {
        h.init();

        // setup functions
        var log = Bancha.Logger.logToBrowser;
        Bancha.Logger.logToBrowser = jasmine.createSpy();
        var serverlog = jasmine.createSpy();
        Bancha.getStubsNamespace().Bancha = { logError: serverlog};

        // test logging to the browser
        Bancha.getRemoteApi().metadata._ServerDebugLevel = 2;
        Bancha.Logger.log('My error');
        expect(serverlog.callCount).toEqual(0);
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(1);
        expect(Bancha.Logger.logToBrowser.mostRecentCall.args).toEqual(['My error','error']);

        // test logging to the server with forceServerlog
        Bancha.Logger.log('My error',null,true);
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(1);
        expect(serverlog.callCount).toEqual(1);
        expect(serverlog.mostRecentCall.args).toEqual(['My error','js_error']);

        // test logging to the server
        Bancha.getRemoteApi().metadata._ServerDebugLevel = 0;
        Bancha.Logger.log('My error');
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(1);
        expect(serverlog.callCount).toEqual(2);
        expect(serverlog.mostRecentCall.args).toEqual(['My error','js_error']);

        Bancha.Logger.log('untranslatable','missing_translation');
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(1);
        expect(serverlog.callCount).toEqual(3);
        expect(serverlog.mostRecentCall.args).toEqual(['untranslatable','missing_translation']);

        // type info should not be logged to the server
        Bancha.Logger.log('My info','info');
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(1);
        expect(serverlog.callCount).toEqual(3);

        delete Bancha.getRemoteApi().Bancha;
        Bancha.Logger.logToBrowser = log;
    });

    it("should have convenience functions for logging", function() {
        h.init();

        // setup functions
        var log = Bancha.Logger.logToBrowser;
        Bancha.Logger.logToBrowser = jasmine.createSpy();
        var serverlog = jasmine.createSpy();
        Bancha.getStubsNamespace().Bancha = { logError: serverlog};
        Bancha.getRemoteApi().metadata._ServerDebugLevel = 2;

        // test convenience functions
        Bancha.Logger.info('My info');
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(1);
        expect(Bancha.Logger.logToBrowser.mostRecentCall.args).toEqual(['My info','info']);

        Bancha.Logger.warn('My warn');
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(2);
        expect(Bancha.Logger.logToBrowser.mostRecentCall.args).toEqual(['My warn','warn']);

        Bancha.Logger.error('My error');
        expect(Bancha.Logger.logToBrowser.callCount).toEqual(3);
        expect(Bancha.Logger.logToBrowser.mostRecentCall.args).toEqual(['My error','error']);


        // test convenience functions with forceServerLog
        Bancha.Logger.info('My info',true);
        // info is not logged to the server
        expect(serverlog.callCount).toEqual(0);

        Bancha.Logger.warn('My warn',true);
        expect(serverlog.callCount).toEqual(1);
        expect(serverlog.mostRecentCall.args).toEqual(['WARNING: My warn','js_error']);

        Bancha.Logger.error('My error',true);
        expect(serverlog.callCount).toEqual(2);
        expect(serverlog.mostRecentCall.args).toEqual(['My error','js_error']);
    });

}); //eo describe logging functions
    
//eof
