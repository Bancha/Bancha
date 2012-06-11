/*!
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Bancha specific helper functions
 *
 * @copyright     Copyright 2011-2012 Roland Schuetz
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock:true */


describe("Mock tests", function() {
    
    it("Expects the ExtJS Library to be present (in Bancha/Test/JavaScriptUnitTest/lib/ext-all-debug-w-comments.js)", function() {
        expect(Ext).toBeDefined();
    });
    
    
    it("should validate if there was no expectations", function() {
        // simple
        var mock = new Mock();
        mock.verify();
    });
    
    
    it("should trow an error if a expected method is not called", function() {
        var mock = new Mock();
        mock.expect("notCalled");
        
        expect(function() {
            mock.verify();
        }).toThrow('Expected mock function "notCalled" to be called 1x instead of 0x');
    });
    
    
    it("should trow an error if a method is called to less or to often", function() {
        var mock = new Mock();
        mock.expect("callOnce");
        mock.callOnce(1);
        mock.callOnce(2);
        
        expect(function() {
            mock.verify();
        }).toThrow('Expected mock function "callOnce" to be called 1x instead of 2x');
        
        mock = new Mock();
        mock.expect("callTwice");
        mock.expect("callTwice");
        mock.callTwice(1);
        
        expect(function() {
            mock.verify();
        }).toThrow('Expected mock function "callTwice" to be called 2x instead of 1x');
    });
    
    
    it("should be able to test multiple functions in one test", function() {
       
        // success case
        var mock = new Mock();
        mock.expect("fnOne");
        mock.fnOne(1);
        mock.expect("fnTwo");
        mock.fnTwo(1);
        
        // no error expected
        mock.verify();
        
        // error case
        mock = new Mock();
        mock.expect("fnOne");
        mock.fnOne(1);
        mock.expect("fnTwo");
        
        expect(function() {
            mock.verify();
        }).toThrow('Expected mock function "fnTwo" to be called 1x instead of 0x');
    });
    
    
    it("should be able to test function arguments", function() {
       var mock;
       
        // success case
        mock = new Mock();
        mock.expect("fnWithArguments").withArguments("a",2,{a:true,b:['array content string']});
        mock.fnWithArguments("a",2,{a:true,b:['array content string']});
        
        // no error expected
        mock.verify();
        
        // error case
        mock = new Mock();
        mock.expect("fnWithArguments").withArguments("a",2,{a:true,b:['array content string']});
        mock.fnWithArguments("a",2,{a:true,b:['array string with wrong content']});
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithArguments"\'s call 0. Argument[2].b[0]: Expected "array string with wrong content"(string) to be equal to "array content string"(string)');
    });
    
    
    it("should be able to test expect different values for each functions call", function() {
        var mock;
       
        // success case
        mock = new Mock();
        mock.expect("fnOne").withArguments(1);
        mock.fnOne(1);
        mock.expect("fnOne").withArguments("string");
        mock.fnOne("string");
        
        // no error expected
        mock.verify();
        
        // error case
        mock = new Mock();
        mock.expect("fnOne").withArguments(1);
        mock.fnOne(1);
        mock.expect("fnOne").withArguments("string");
        mock.fnOne(true); // wrong
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnOne"\'s call 1. Argument[0]: Expected true(boolean) to be equal to "string"(string)');
    });
    
    
    it("should be able to recognize value types string,number,boolean from Mock.Value", function() {
       var mock;
       
        // success case
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.String,Mock.Value.Number,Mock.Value.Boolean);
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.String,Mock.Value.Number,Mock.Value.Boolean);
        mock.fnWithSpecialArguments("a",2,true);
        mock.fnWithSpecialArguments("",-43,false);
        
        // no error expected
        mock.verify();
        
        // error case string
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.String,Mock.Value.Number,Mock.Value.Boolean);
        mock.fnWithSpecialArguments(1,2,true);
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[0]: Expected 1(number) to be a string.');
        
        
        // error case number
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.String,Mock.Value.Number,Mock.Value.Boolean);
        mock.fnWithSpecialArguments("a","2",true);
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[1]: Expected "2"(string) to be a number.');
        
        // error case boolean
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.String,Mock.Value.Number,Mock.Value.Boolean);
        mock.fnWithSpecialArguments("a",2,3);
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[2]: Expected 3(number) to be a boolean.');
    });
    
    
    it("should be able to recognize value types from Mock.Value", function() {
        var mock;
        
        // success case
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.Any,Mock.Value.Function,Mock.Value.Object,Mock.Value.Array);
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.Any,Mock.Value.Function,Mock.Value.Object,Mock.Value.Array);
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.Any,Mock.Value.Function,Mock.Value.Object,Mock.Value.Array);
        mock.fnWithSpecialArguments("a",function() {},{},[]);
        mock.fnWithSpecialArguments(3,function(a,b) {return a+b;},{a:'whatever'},['lala']);
        mock.fnWithSpecialArguments(function() {},function() {},{a:function() {}},[1,2,3]);
        
        // no error expected
        mock.verify();
        
        // error case function
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.Function,Mock.Value.String);
        mock.fnWithSpecialArguments({},"whatever");
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[0]: Expected [object Object](object) to be a function.');
        
        
        // error case object
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.Object,Mock.Value.String);
        mock.fnWithSpecialArguments("a","whatever");
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[0]: Expected "a"(string) to be an object.');
        
        
        // error case object NOT array
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.Object,Mock.Value.String);
        mock.fnWithSpecialArguments([1],"whatever");
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[0]: Expected [1](array) to be an object.');
        
        
        // error case array
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments(Mock.Value.Array,Mock.Value.String);
        mock.fnWithSpecialArguments(2,"whatever");
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[0]: Expected 2(number) to be an array.');
    });
    
    
    
    it("should be able to recognize value types from Mock.Value recursive", function() {
       var mock;
       
        // success case
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments({string:Mock.Value.String,number:Mock.Value.Number});
        mock.expect("fnWithSpecialArguments").withArguments({string:Mock.Value.String,number:Mock.Value.Number});
        mock.fnWithSpecialArguments({string:"ab",number:5});
        mock.fnWithSpecialArguments({string:"",number:0});
        
        // no error expected
        mock.verify();
        
        // error case string
        mock = new Mock();
        mock.expect("fnWithSpecialArguments").withArguments({string:Mock.Value.String,number:Mock.Value.Number});
        mock.fnWithSpecialArguments({string:"ab",number:"wrongTypeString"});
        
        expect(function() {
            mock.verify();
        }).toThrow('mock function "fnWithSpecialArguments"\'s call 0. Argument[0].number: Expected "wrongTypeString"(string) to be a number.');
    });
    
});

//eof
