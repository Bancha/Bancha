/*!
 * Additional functions for testing ExtJS Code
 * Copyright(c) 2011 Roland Schütz
 * @author Roland Schütz <roland@banchaproject.org>
 * @copyright (c) 2011 Roland Schütz
 */
/*jslint browser: true, onevar: false, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, Bancha, YUITest, User */


ExtTest = {
    /*
     * inits ext-specific code, should be executed after YUITest is loaded
     */
    init: function() {
        // errors should result in failures
        Ext.Error.handle = function(e) {
            YUITest.Assert.fail('Ext.Error thrown: '+e.msg);
        };


    },
    
    /**
     * helper method for creating a mock object which behaves like a proxy
     */
    mock: {
        Proxy: function() {
        
            var Y = YUITest,
                mockProxy = {
                /**
                 * override with additional functionality
                 */
                expect: function(method) {

                    // proxy expect method
                    if(method==='create') {
                        this.expectCreate();
                    } else {
                        // TODO Test
                        this.prototype.expect.apply(this,arguments);
                    }
                },
                expectCreate: function() {
                    Y.Mock.expect(mockProxy, {
                        method: "create",
                        args: [
                            Y.Mock.Value.Object,
                            Y.Mock.Value.Function,
                            Y.Mock.Value.Object
                        ]                            
                    });
                }
            };
            
            // inherit from YUITest.Mock
            mockProxy.prototype = Y.Mock();
            
            // fake proxy property for ext
            mockProxy.isProxy = true;
            
            // setModel is always called when creating 
            // an Proxy from model/store
            Y.Mock.expect(mockProxy, {
                method: "setModel", 
                args: [Y.Mock.Value.Any]                           
            });
            
            return mockProxy;
        }
    },
    
    Assert: {
        throwsExtError: function(msg,fn,scope,args) {
            var standardHandler = Ext.Error.handle;
    
            // change error handling inside this function
            Ext.Error.handle = function(e) {
                throw new Error(e.msg);
            };
    
            // use yui test to check
            YUITest.Assert.throwsError(msg,function() {
                // since YUITest doesn't support scoping, we do
                fn.apply(scope,args);
            });
    
            // reset error handling
            Ext.Error.handle = standardHandler;
        }
    }
};

ExtTest.init();


// Fixture for YUITest.ArrayAssert.itemsAreEqual
// see: http://yuilibrary.com/projects/yuitest/ticket/43
YUITest.ArrayAssert.itemsAreEqual = function(a,b,msg) {
    var assert = YUITest.Assert,
        fail = function(errorMsg) {
            if(msg) {
               errorMsg = msg+"Error: "+errorMsg;
            }
            assert.fail(errorMsg);
        },
        checkRecursive = function(a,b,property) {
            if(typeof a !== typeof b) {
                fail(property+" Elements are of different types: "+(typeof a)+", "+(typeof b));
            }
            if(typeof a === 'string' || typeof a === 'boolean' || typeof a === 'function' || typeof a === 'number') {
                assert.areEqual(a,b,property+" should be the same");
            }
            if(Ext.isArray(a)) {
                if(a.length!==b.length) {
                    fail(property+" Arrays are of different length: "+a.length+", "+b.length);
                }
                for(var i=0,len=a.length; i<len; i++) {
                    checkRecursive(a[i],b[i],property+"["+i+"]");
                }
            }
            if(typeof a === 'object') {
                // fast and dirty, TODO clean arrayassert
                for (name in a) {
                    if (a.hasOwnProperty(name)) {
                        checkRecursive(a[name],b[name],property+"."+name);
                    }
                }
                for (name in b) {
                    if (b.hasOwnProperty(name)) {
                        checkRecursive(a[name],b[name],property+"."+name);
                    }
                }
            }
        };
    
    checkRecursive(a,b,"");
}; 