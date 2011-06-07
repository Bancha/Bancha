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
var suite = new YUITest.TestSuite("Bancha JS Tests");


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
    
    
    "Bancha.preloadModelMetaData loads the model metadata from the server": function() {
        this.init();
        
        // Catch request
        var request;
        Bancha.RemoteStubs.Bancha = {
            loadModel: function() {
                // catch request
                request = Ext.toArray(arguments);
            }
        };
        
        // create callback check
        var callbackIsFine = false;
        var scope = {
            result: true
        };
        var callback = function() {
            // did the scope work out?
            callbackIsFine = this.result;
        };
        
        
        // execute
        Bancha.preloadModelMetaData('User',callback,scope);
        assert.isTrue(callbackIsFine);
        
        // TODO expected arguments and result
        var expected = [
        // TODO 
        ];
        
        // check preloaded data
        // TODO arrayAssert.itemsAreEqual(expected, Bancha.REMOTE_API.metadata.User);
        
        // check request
        // TODO arrayAssert.itemsAreEqual(expected, request);
    },
    
    "Check Bancha.isRemoteModel function": function() {
        this.init();
        
        assert.isFalse(Bancha.isRemoteModel('Phantasy')); // doesn't exist
        assert.isTrue(Bancha.isRemoteModel('User')); // remote object exists
    },
    
    "Check Bancha.modelMetaDataIsLoaded function": function() {
        this.init();
        
        assert.isFalse(Bancha.modelMetaDataIsLoaded('Phantasy')); // doesn't exist
        assert.isTrue(Bancha.modelMetaDataIsLoaded('User')); // user object exists
    },

    "Check Bancha.getModelMetaData function": function() {
        this.init();
        
        assert.isObject(Bancha.getModelMetaData('User')); // exists
        assert.isNull(Bancha.getModelMetaData('Phantasy')); // doesn't exist
    },
    
    "Bancha.createModel creates models": function() {
    
        // should throw an error, no metadata
        extAssert.throwsExtError(
            "Bancha: Bancha is not yet initalized, please init before using Bancha.createModel().",
            function() { Bancha.createModel('User'); });
                
        this.init();
        
        
        // create a yui mock object for the proxy
        var mockProxy = ExtTest.mock.Proxy();
        
        // should create a user defintion
        assert.isTrue(
            Bancha.createModel('User', {
                additionalSettings: true,
                proxy: mockProxy
        }));
        
        var model = Ext.ClassManager.get('User');
        assert.isObject(model);
        assert.isTrue(model.prototype.additionalSettings);
        
        var user = new User({
            firstname: 'Micky',
            lastname: 'Mouse'
        });
        
        // define expectations for remote stub calls
        // user.save() should result in one create action
        mockProxy.expect("create");
        
        // test
        user.save();
        
        //verify the expectations were met
        Y.Mock.verify(mockProxy);    
    },
    
    "Bancha.getModel return and eventually creates models": function() {
            
         this.init();
         
         // create model
         var model = Bancha.getModel('User');
         assert.isObject(model);
         assert.areEqual('User',Ext.ClassManager.getName(model));
    
        // only get model
        model.alreadyCreated = true;
        model = Bancha.getModel('User');
        assert.isObject(model);
        assert.isTrue(model.alreadyCreated);
    }
});
//add test cases
suite.add(banchaTests);



var scarfoldUtilTests = new YUITest.TestCase({

    name: "Scarfold util functions",

    //---------------------------------------------
    // Setup and tear down
    //---------------------------------------------
    setUp : function () {
    },
    tearDown : function () {
    },
    
    
    "Bancha.scarfold.util.toFirstUpper test": function() {
        var util = Bancha.scarfold.util;
        assert.areEqual('User',util.toFirstUpper('user'));
        assert.areEqual('UserName',util.toFirstUpper('userName'));
    },
    
    "Bancha.scarfold.util.humanize test": function() {
        var util = Bancha.scarfold.util;
        
        // first upper case
        assert.areEqual('User',util.humanize('user'));
        
        // ids
        assert.areEqual('User',util.humanize('user_id'));
        
        // underscores
        assert.areEqual('User name',util.humanize('user_name'));
        assert.areEqual('Name with many spaces',util.humanize('name_with_many_spaces'));
        
        // camel case
        assert.areEqual('User name',util.humanize('userName'));
        assert.areEqual('Name with many spaces',util.humanize('nameWithManySpaces'));
        
        // shouldn't change normal text
        assert.areEqual('John Smith',util.humanize('John Smith'));
        assert.areEqual('This is a normal text with spaces, Upper case words and all UPPER CASE words!',util.humanize('This is a normal text with spaces, Upper case words and all UPPER CASE words!'));
    }
});
//add test cases
suite.add(scarfoldUtilTests);   


var scarfoldGridTests = new YUITest.TestCase({

    name: "Scarfold grid functions",

    //---------------------------------------------
    // Setup and tear down
    //---------------------------------------------
    setUp : function () {
        delete Bancha.REMOTE_API;
        delete Bancha.RemoteStubs;
        Bancha.initialized = false;

    },
    tearDown : function () {
    },

    
    // helpers
    initAndCreateSampleModel: initAndCreateSampleModel,
    
    "Bancha.scarfold.buildColumns build column configs (component test)": function() {
        // prepare
        this.initAndCreateSampleModel('GridColumnsTest');
        
        // expected columns
        var expected = [{
            text     : 'Id',
            dataIndex: 'id',
            xtype: 'numbercolumn',
            editor: {xtype:'numberfield', decimalPrecision:0}
        }, {
            text     : 'Name',
            dataIndex: 'name',
            xtype: 'gridcolumn',
            editor: {xtype:'textfield'}
        }, {
            text     : 'Login',
            dataIndex: 'login',
            xtype: 'gridcolumn',
            editor: {xtype:'textfield'}
        }, {
            text     : 'Created',
            dataIndex: 'created',
            xtype: 'datecolumn',
            editor: {xtype:'datefield'}
        }, {
            text     : 'Email',
            dataIndex: 'email',
            xtype: 'gridcolumn',
            editor: {xtype:'textfield'}
        }, {
            text     : 'Avatar',
            dataIndex: 'avatar',
            xtype: 'gridcolumn',
            editor: {xtype:'textfield'}
        }, {
            text     : 'Weight',
            dataIndex: 'weight',
            xtype: 'numbercolumn',
            editor: {xtype:'numberfield'}
        }, {
            text     : 'Height',
            dataIndex: 'height',
            xtype: 'numbercolumn',
            editor: {xtype:'numberfield'}
        }, {
            xtype:'actioncolumn', 
            width:50,
            items: [{
                icon: 'images/delete.png',
                tooltip: 'Delete',
                handler: Bancha.scarfold.gridFunction.onDelete
            }]
        }];
        
        // test
        var result = Bancha.scarfold.buildColumns('GridColumnsTest', {
            update  : true,
            destroy : true
        });
        
        // TODO ohne UD
        // compare
        arrayAssert.itemsAreEqual(expected, result);
    },

    "Bancha.scarfold.buildGridPanelConfig should create grid configs (component test)": function() {
        // prepare
        this.initAndCreateSampleModel('GridPanelConfigTest');
        
        // test
        var result = Bancha.scarfold.buildColumns('GridPanelConfigTest', {
            update  : true,
            destroy : true
        });
        
        // check store
        var expectedModelName = 'GridPanelConfigTest',
            resultModelName   = Ext.ClassManager.getName(result.store.getProxy().getModel());
        assert.areEqual(expectedModelName, resultModelName, "The grid config has an store of the right model");
        
        // just a simple column check, buildColumns is already tested above
        assert.areEqual(9, result.columns.length);
        
        
        // TODO ohne UD
        // compare
        arrayAssert.itemsAreEqual(expected, result);
        
                
                                
        var result = Bancha.scarfold.buildGridPanelConfig('GridPanelTest');
        assert.areEqual('User',result);
        
        assert.fail();
        
        // TODO test whole CRUD
    }
});
//add test cases
suite.add(scarfoldGridTests);   


// add to test runner
YUITest.TestRunner.add(suite);


}());

//eof
