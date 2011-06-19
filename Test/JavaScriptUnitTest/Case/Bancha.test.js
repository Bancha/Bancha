/*!
 * Bancha Tests
 * Copyright(c) 2011 Roland Schütz
 * @author Roland Schütz <roland@banchaproject.org>
 * @copyright (c) 2011 Roland Schütz
 */
/*jslint browser: true, onevar: false, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, Bancha, YUITest, User */


(function(){

var assert = YUITest.Assert, // shortcuts
    arrayAssert = YUITest.ArrayAssert,
    extAssert = ExtTest.Assert,
    Y = YUITest;


//create the test suite
var suite = new Y.TestSuite("Bancha JS Tests");


/** helpers */
var remoteApiDefinition = {
    url: 'Bancha/router.json',
    namespace: 'Bancha.RemoteStubs',
    "type":"remoting",
    "namespace": "Bancha.RemoteStubs",
    "actions":{
        "User":[{
            "name":"create",
            "len":3
        },{
            "name":"destroy",
            "len":1
        }]
    },
   metadata: {
      User: {
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
                //associations: [
                    //{type:'hasMany', model:'Post', name:'posts'},
                //],
                sorters: [{
                    property: 'name',
                    direction: 'ASC'
                }]
      }
   }
};
var init = function() {
    Bancha.REMOTE_API = remoteApiDefinition;
    Bancha.init();
};
var initAndCreateSampleModel = function(modelName) {
    Bancha.REMOTE_API = Ext.clone(remoteApiDefinition);
    // setup fake model
    Bancha.REMOTE_API.metadata[modelName] = Ext.clone(Bancha.REMOTE_API.metadata.User);
    Bancha.REMOTE_API.actions[modelName] = Ext.clone(Bancha.REMOTE_API.actions.User);

    // create
    Bancha.init();
    assert.isTrue(Bancha.createModel(modelName),"Try to create fake model "+modelName);
};

var banchaTests = new YUITest.TestCase({

    name: "Bancha",

    //---------------------------------------------
    // Setup and tear down
    //---------------------------------------------
    setUp : function () {
        // Bancha is a singleton, so get the class of
        // the singleton object and reinstanciate it
        //var className = Ext.getClassName(Ext.getClass(Bancha));
        //Bancha = Ext.create(className,{});
        delete Bancha.REMOTE_API;
        delete Bancha.RemoteStubs;
        Bancha.initialized = false;
    },
    tearDown : function () {
    },
    
    /** helpers */
    remoteApiDefinition: remoteApiDefinition,
    init: init,
    
    
    // test functions
    
    "Bancha.getStubsNamespace() returns the namespace if already instanciated":function() {
        this.init();
        
        assert.isObject(Bancha.getStubsNamespace());
        assert.isObject(Bancha.getStubsNamespace().User);
        assert.isFunction(Bancha.getStubsNamespace().User.create); // looks good
    },
    
    "Bancha.getRemoteApi() returns the remote api if already defined":function() {
        extAssert.throwsExtError("Bancha: The remote api Bancha.REMOTE_API is not yet defined, please define the api before using Bancha.getRemoteApi().", Bancha.getRemoteApi,Bancha);
        
        this.init();
        assert.isObject(Bancha.getRemoteApi());
        assert.isTrue(Bancha.getRemoteApi().type==="remoting");
    },
    
    
    "Init instanciates all stubs": function() {
        assert.isFunction(Bancha.init);
        
        Bancha.REMOTE_API = this.remoteApiDefinition;
        Bancha.init();
        assert.isTrue(Bancha.initialized);
        
        //var expected = {
        //    User: {
        //        "create":fn,
        //        "delete":fn
        //    }
        //};
        
        // check created stubs
        assert.areEqual(1, Object.getOwnPropertyNames(Bancha.RemoteStubs).length, "There is exactly one RemoteStub (User)");
        assert.isObject(Bancha.RemoteStubs.User, "There is a RemoteStub User");
        assert.areEqual(2, Object.getOwnPropertyNames(Bancha.RemoteStubs.User).length, "The RemoteStub User supports 2 functions");
        assert.isFunction(Bancha.RemoteStubs.User.create, "The RemoteStub User supports create");
        assert.isFunction(Bancha.RemoteStubs.User.destroy, "The RemoteStub User supports destroy");
    },
    
    
    "Bancha.preloadModelMetaData loads single metadata from the server": function() {
        this.init();
        
		// create direct stub mock
        var mock = Y.Mock();
		ExtTest.mock.DirectStub.expectRPC(mock,"loadMetaData",['User']);
		Bancha.RemoteStubs.Bancha = mock;
		
        // execute
        Bancha.preloadModelMetaData('User');
        
		// test
		Y.Mock.verify(mock);
    },
	
});
//add test cases
suite.add(banchaTests);


// add to test runner
YUITest.TestRunner.add(suite);


}());

//eof
