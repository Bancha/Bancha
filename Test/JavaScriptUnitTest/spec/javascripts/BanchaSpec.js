/*!
 * Bancha Tests
 * Copyright(c) 2011 Roland Schütz
 * @author Roland Schütz <roland@banchaproject.org>
 * @copyright (c) 2011 Roland Schütz
 */
/*jslint browser: true, onevar: false, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, Bancha, YUITest, User */

describe("Bancha Singleton", function() {

    var rs = BanchaSpecHelper.SampleData.remoteApiDefinition, // remote sample
		h = BanchaSpecHelper; // helper shortcut
	
	beforeEach(h.reset);

	it("should return the stubs namespace on getStubsNamespace() if already instanciated", function() {
    	h.init();
    
		var ns = Bancha.getStubsNamespace();
		
    	expect(ns).toBeDefined();
    	expect(ns.User).toBeDefined();
    	expect(ns.User.create).toBeDefined(); // looks good
	});
	
	it("should return an expection when calling getRemoteApi() before init()", function() {
		// TODO extAssert.throwsExtError("Bancha: The remote api Bancha.REMOTE_API is not yet defined, please define the api before using Bancha.getRemoteApi().", Bancha.getRemoteApi,Bancha);
	});
	
	it("should return the remote api if already defined in js with getRemoteApi()", function() {
        h.init();
        
        var api = Bancha.getRemoteApi();
        expect(api).hasProperty("type");
        expect(api.type).toBeEquals("remoting");
    });
	
	
	it("should init all stubs on init()", function() {
		expect(Bancha.init).toBeAFunction();

	    // setup test data
		Bancha.REMOTE_API = rs;
	    
	    // test
		Bancha.init();
	    
		expect(Bancha.initialized).toBeTruthy();

	    //var expected = {
        //    User: {
        //        "create":fn,
        //        "delete":fn
        //    }
        //};

	    //check created stubs
		expect(Bancha.RemoteStubs).hasProperty("User.create")
		expect(Bancha.RemoteStubs.User.create).toBeAFunction(); //"The RemoteStub User supports create"
		expect(Bancha.RemoteStubs).hasProperty("User.destroy")
		expect(Bancha.RemoteStubs.User.destroy).toBeAFunction(); //"The RemoteStub User supports create"
  });



}); //eo describe

//eof
