/*jslint browser: true, vars: true, undef: true, nomen: true, eqeqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, describe, it, beforeEach, expect, fail, jasmine, Mock */



// test expect().toThrowExtErrorMsg()
describe("ExtSpecHelper", function() {
    
    it("should be able to catch ext errors (just the right ones)", function() {
       
        // success 
        expect(function() {
            Ext.Error.raise('this is a ext errror');
        }).toThrowExtErrorMsg('this is a ext errror');
        
        // error no exceptiong
        var fn = jasmine.Matchers.prototype.toThrowExtError,
            scope = {
                actual: function() {}
            },
            result = fn.apply(scope,'expecting error');
        
        expect(result).toBeFalse();
        
        
        // error wrong exception
        scope = {
            actual: function() {
                // this function will throw a false error
                Ext.Error.raise('this is a wrong ext errror');
            }
        };
        result = fn.apply(scope,'mismatching error');
        
        expect(result).toBeFalse();
        
    });
    
});



describe("Mock.Proxy",function() {
    
    it("should be able to expect RCP calls",function() {
        var callback = function() {},
            scope = {};
        
        // success
        var mock = Mock.Proxy();
        mock.setModel();// fake setModel call
        mock.expectRPC('create',[{id:1,name:'juhu'},'secondDirectArgument']);
        mock.create([{id:1,name:'juhu'},'secondDirectArgument'],callback,scope);
        mock.verify();
        
        // error
        var mock = Mock.Proxy();
        mock.setModel();// fake setModel call
        mock.expectRPC('create',[{id:1,name:'juhu'},'secondDirectArgument']);
        mock.create([{id:1,name:'juhu'},'secondDirectArgument'],'nocallback',scope);
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "create"\'s call 0. Argument[1]: Expected "nocallback"(string) to be a function.');
    });
    
    it("should be able to call the last rpc's callback", function() {
        
        // successfull without data
        var mock = Mock.Proxy();
        mock.setModel();// fake setModel call
        var callback = function() { this.success=true;},
            scope = { success:false};
        mock.expectRPC('create',[{id:1,name:'juhu'},'secondDirectArgument']);
        mock.create([{id:1,name:'juhu'},'secondDirectArgument'],callback,scope);
        mock.verify(); // just to keep clean
        
        // now test callLastRPCCallback
        mock.callLastRPCCallback("create");
        // verify
        expect(scope.success).toBeTruthy();
        
        
        
        // successfull with data TODO
        var mock = Mock.Proxy();
        mock.setModel();// fake setModel call
        var callback = function(data) { this.success=data.successProperty;},
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