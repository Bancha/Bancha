/*jslint browser: true, vars: true, undef: true, nomen: true, eqeqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, describe, it, beforeEach, expect, fail, jasmine, Mock */



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



describe("Mock.Proxy",function() {
    
    it("should be able to expect RCP calls",function() {
        var callback = function() {},
            scope = {},
            mock;
        
        // success
        mock = Mock.Proxy();
        mock.expectRPC('create',[{id:1,name:'juhu'},'secondDirectArgument']);
        mock.create([{id:1,name:'juhu'},'secondDirectArgument'],callback,scope);
        mock.verify();
        
        // error
        mock = Mock.Proxy();
        mock.expectRPC('create',[{id:1,name:'juhu'},'secondDirectArgument']);
        mock.create([{id:1,name:'juhu'},'secondDirectArgument'],'nocallback',scope);
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "create"\'s call 0. Argument[1]: Expected "nocallback"(string) to be a function.');
    });
    
    it("should be able to call the last rpc's callback", function() {
        var mock, callback, scope;
        
        // successfull without data
        mock = Mock.Proxy();
        callback = function() { this.success=true;};
        scope = { success:false};
        mock.expectRPC('create',[{id:1,name:'juhu'},'secondDirectArgument']);
        mock.create([{id:1,name:'juhu'},'secondDirectArgument'],callback,scope);
        mock.verify(); // just to keep clean
        
        // now test callLastRPCCallback
        mock.callLastRPCCallback("create");
        // verify
        expect(scope.success).toBeTruthy();
        
        
        
        // successfull with data
        mock = Mock.Proxy();
        callback = function(data) { this.success=data.successProperty;};
        scope = { success:false};
        mock.expectRPC('create',[{id:1,name:'juhu'},'secondDirectArgument']);
        mock.create([{id:1,name:'juhu'},'secondDirectArgument'],callback,scope);
        mock.verify(); // just to keep clean
        
        // now test callLastRPCCallback
        mock.callLastRPCCallback("create",[{
            successProperty: true
        }]);
        // verify
        expect(scope.success).toBeTruthy();
    });
});