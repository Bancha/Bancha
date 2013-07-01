/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * Tests for all ExtJS and Sencha Touch specific helper functions
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
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock */



// test expect().toThrowExtErrorMsg()
var origEnv;
describe("ExtSpecHelpers toThrowExtError matcher", function() {
    var env, spec;

    // TODO
    /*
    origEnv = jasmine.getEnv();
    / from MatchersSpec /
    beforeEach(function() {
        // create a new env and suite to test in
        env = new jasmine.Env();
        env.updateInterval = 0;

        var suite = env.describe("suite", function() {
              spec = env.it("spec", function() {
              });
        });
        spyOn(spec, 'addMatcherResult');

        this.addMatchers({
              toPass: function() {
                return lastResult().passed();
              },
              toFail: function() {
    // console.info(lastResult());
                return !lastResult().passed();
              }
        });

        // add all custom matchers
        origEnv.currentRunner().before_.forEach(function(el) { el.apply(spec); });
      });

      function match(value) {
        return spec.expect(value);
      }

      function lastResult() {
        return spec.addMatcherResult.mostRecentCall.args[0];
      }
    / eo from MatchersSpec /


    it("should be able to catch right ext errors", function() {
        // success
        var exceptionFn = function() {
            Ext.Error.raise('this is a ext error');
        };
        //console.info(match(exceptionFn));
        expect(match(exceptionFn).toThrowExtErrorMsg('this is a ext error')).toPass();
    });
    
    it("should be able to recognize when no error was thrown", function() {
        var emptyFn = function() {};
        expect(match(emptyFn).toThrowExtErrorMsg('ext error')).toFail();
    });
     
    it("should be able to recognize when the wrong error is trown", function() {
        // error wrong exception
        var exceptionFn = function() {
            Ext.Error.raise('this is a ext error');
        };
        expect(match(exceptionFn).toThrowExtErrorMsg('this is a wrong ext error')).toFail();
        
    });
    */
    // TODO test toBeModelClass
});

//eof
