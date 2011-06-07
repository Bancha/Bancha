(function(){

    var Assert          = YUITest.Assert,
        ObjectAssert    = YUITest.ObjectAssert;
    
    //-------------------------------------------------------------------------
    // Base Test Suite
    //-------------------------------------------------------------------------
    
    var suite = new YUITest.TestSuite("TestCase Tests");
    
 
    
    //-------------------------------------------------------------------------
    // Test Case for asserts
    //-------------------------------------------------------------------------
    
    suite.add(new YUITest.TestCase({
    
        name: "Assertion Tests",
        
        _should: {
            fail: {
                "Test should fail when there are no asserts": true
            }
        },        
        
        "Test should fail when there are no asserts": function(){
            //noop
        }
    }));        

    //-------------------------------------------------------------------------
    // Test Case for init()
    //-------------------------------------------------------------------------
    
    var initValue = 0;
    suite.add(new YUITest.TestCase({
    
        name: "Init Tests",
        
        init: function(){
            initValue++;
        },     
        
        "Verify that initValue is 1": function(){
            Assert.areEqual(1, initValue);
        },
        
        "Verify that initValue is still 1": function(){
            Assert.areEqual(1, initValue);
        }        
        
    }));        

    //-------------------------------------------------------------------------
    // Test Case for async:init()
    //-------------------------------------------------------------------------
    
    var asyncInitValue = 0;
    suite.add(new YUITest.TestCase({
    
        name: "Async Init Tests",
        
        "async:init": function(){
            var that = this;
            asyncInitValue = 1;
            setTimeout(function(){
                asyncInitValue = 3;
                that.callback("myVal")(25);
            },100);
        },     
        
        "Verify that context data has a property named 'myVal'": function(data){
            ObjectAssert.hasKey("myVal", data);
            Assert.areEqual(25, data.myVal);
        },
        
        "Verify that asyncInitValue is 3": function(){
            Assert.areEqual(3, asyncInitValue);
        },
        
        "Verify that initValue is still 3": function(){
            Assert.areEqual(3, asyncInitValue);
        }        
        
    }));        

    //-------------------------------------------------------------------------
    // Test Case for destroy()
    //-------------------------------------------------------------------------
    
    var destroyValue = 0;
    suite.add(new YUITest.TestCase({
    
        name: "Destroy Tests",
        
        
        tearDown: function(){
            destroyValue++;
        },
        
        destroy: function(){
            destroyValue--;
        },  
         
        "Verify that destroyValue is 0": function(){
            Assert.areEqual(0, destroyValue);
        },
        
        "Verify that initValue is 1": function(){
            Assert.areEqual(1, destroyValue);
        }        
        
    }));        

    //this case verifies the result of the last case
    suite.add(new YUITest.TestCase({
    
        name: "Destroy Verification Tests",
         
        "Verify that destroyValue is 1": function(){
            Assert.areEqual(1, destroyValue);
        }    
        
    }));        
    
    //-------------------------------------------------------------------------
    // Test Case for visitor data object
    //-------------------------------------------------------------------------
    
    
    var visitorSuite = new YUITest.TestSuite({
        name: "Visitor data tests",
        
        setUp: function(data){
            data.foo = "bar";
        }
        
    });
    
    visitorSuite.add(new YUITest.TestCase({
    
        name: "First Test Case",
        
        init: function(data){
            data.first = 1;
        },
        
        setUp: function(data){
            data.test = true;
        },
        
        tearDown: function(data){
            delete data.test;
        },
         
        "The property 'foo' should be passed from the suite and be 'bar'": function(data){
            Assert.areEqual("bar", data.foo);
        },
        
        "The property 'test' should be passed from setUp and be true": function(data){
            Assert.isTrue(data.test);
        },        
        
        "The property 'first' should be passed from init and be 1": function(data){
            Assert.areEqual(1, data.first);
        }        
        
    }));    

    visitorSuite.add(new YUITest.TestCase({
    
        name: "Second Test Case",
    
       "The property 'foo' should be passed from the suite and be 'bar'": function(data){
            Assert.areEqual("bar", data.foo);
        },
        
        "The property 'first' should be passed from init and be 1": function(data){
            Assert.areEqual(1, data.first);
        }     
    }));
    
    suite.add(visitorSuite);
    
   //-------------------------------------------------------------------------
    // Test Case for wait/resume()
    //-------------------------------------------------------------------------
    
    suite.add(new YUITest.TestCase({
    
        name: "wait()/resume() Tests",
        
        _should: {
            fail: {
                "wait() without resume() should fail": true
            }
        },        
        
        "wait() with a function to execute should work": function(){
            this.wait(function(){
                Assert.isTrue(true);  //FIXME
            }, 100);
        },
        
        "wait() without a function to execute followed by resume() should work": function(){
            var that = this;
            setTimeout(function(){
                that.resume(function(){
                    Assert.isTrue(true);  //FIXME
                });
            });
            
            this.wait(100);
        },
        
        "wait() without resume() should fail": function () {
            Assert.isTrue(true);
            this.wait(100);
        },
        
        "resume() without wait() should throw an error": function () {
            var that = this;
            Assert.throwsError("resume() called without wait().", function(){
                that.resume(function(){});
            });
        }
    }));            

    //add it to be run
    YUITest.TestRunner.add(suite);

})();