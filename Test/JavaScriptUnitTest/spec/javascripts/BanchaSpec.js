/*!
 * Bancha Tests
 * Copyright(c) 2011 Roland Schütz
 * @author Roland Schütz <roland@banchaproject.org>
 * @copyright (c) 2011 Roland Schütz
 */
/*jslint browser: true, onevar: false, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, Bancha, YUITest, User */

describe("Bancha Singleton", function() {

    describe("Bancha's basic retrieval functions on the stubs and model meta data", function() {
		var rs = BanchaSpecHelper.SampleData.remoteApiDefinition, // remote sample
			h = BanchaSpecHelper; // helper shortcut
	
	befo	reEach(h.reset);

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
	        expect(api.type).toEqual("remoting");
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

	
		it("should return if a metadata is loaded with modelMetaDataIsLoaded()", function() {
			h.init();
		
	        expect(Bancha.modelMetaDataIsLoaded('Phantasy')).toBeFalsy(); // doesn't exist
	        expect(Bancha.modelMetaDataIsLoaded('User')).toBeTruthy(); // remote object exists
	  	});

		it("should return is a model is loaded with isRemoteModel after init", function() {
			h.init();

			expect(Bancha.isRemoteModel('Phantasy')).toBeFalsy(); // doesn't exist
			expect(Bancha.isRemoteModel('User')).toBeTruthy(); // remote object exists
		});
	 
		it("Check Bancha.getModelMetaData function", function() {
	        this.init();

			expect(Bancha.getModelMetaData('Phantasy')).toBeNull(); // doesn't exist
			expect(Bancha.getModelMetaData('User')).hasProperty('fields.2.name'); // remote api meadata object exists
			expect(Bancha.getModelMetaData('User').fields[2].name).toEqual('login'); // it's really the metadata
	    });
	
		it("should preload model meta data using the direct stub", function() {
			h.init();
      
			// create direct stub mock
			var fnMock = jasmine.createSpy();
			Bancha.RemoteStubs.Bancha = {
				loadMetaData: fnMock
			};
		
			// execute test
			Bancha.preloadModelMetaData(['PreloadTestUser','PreloadTestArticle']);
		
			expect(fnMock).toBeCalled();
			var directArgs = fnMock.mostRecentCall.args[0],
				callback = fnMock.mostRecentCall.args[1],
				scope = fnMock.mostRecentCall.args[2];
			expect(args).toEqual([['User','PreloadTestArticle']]);
			expect(callback).toBeAFunction();
			expect(scope).toBeAnObject();
		
			// now fake answer
			var result = [{
				PreloadTestUser: {
		               fields: [
	                    {name:'id', type:'int'},
	                    {name:'name', type:'string'},
	                    {name:'login', type:'string'},
	                    {name:'created', type:'date'},
	                    {name:'email', type:'string'},
	                    {name:'avatar', type:'string'},
	                    {name:'weight', type:'float'},
	                    {name:'height', type:'float'}
	                ],
	                validations: [
	                    {type:'length', name:'name', min:4, max:64},
	                    {type:'length', name:'login', min:3, max:64},
	                    {type:'length', name:'email', min:5, max:64},
	                    {type:'length', name:'avatar', max:64},
	                    {type:'length', name:'weight', max:64}
	                ],
	                sorters: [{
	                    property: 'name',
	                    direction: 'ASC'
	                }]
				},
				PreloadTestArticle: {
		            fields: [
	                    {name:'id', type:'int'},
	                    {name:'name', type:'string'}
	                ]
				}
			}];
			callback.apply(scope,[result]);
		
			// now see if is is available
			expect(Bancha.modelMetaDataIsLoaded('PreloadTestUser')).toBeTruthy();
			expect(Bancha.modelMetaDataIsLoaded('PreloadTestArticle')).toBeTruthy();
		
			// check model by sample field
			expect(Bancha.getModelMetaData('PreloadTestUser')).hasProperty('fields.2.name');
			expect(Bancha.getModelMetaData('PreloadTestUser').fields[2].name).toEqual('login');
		});
	
	
		it("should allow to just give a string as argument when preloading only one model meta data", function() {
			h.init();
      
			// create direct stub mock
			var fnMock = jasmine.createSpy();
			Bancha.RemoteStubs.Bancha = {
				loadMetaData: fnMock
			};
		
			// execute test
			Bancha.preloadModelMetaData('PreloadSingleTestUser');
		
			expect(fnMock).toBeCalled();
			expect(fnMock.mostRecentCall.args[0]).toEqual([['User']]); // direct arguments
		});
	
	
	}); //eo describe basic functions
	
	// TODO add more
	
}); //eo describe Bancha

//eof
