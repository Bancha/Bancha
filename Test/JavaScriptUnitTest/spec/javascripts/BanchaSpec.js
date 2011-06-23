/*!
 * Bancha Tests
 * Copyright(c) 2011 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011 Roland Schuetz
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeqeq: true, plusplus: false, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, BanchaSpecHelper */

describe("Bancha Singleton", function() {

    describe("Bancha's basic retrieval functions on the stubs and model meta data", function() {
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
			expect(Bancha.RemoteStubs).hasProperty("User.create");
			expect(Bancha.RemoteStubs.User.create).toBeAFunction(); //"The RemoteStub User supports create"
			expect(Bancha.RemoteStubs).hasProperty("User.destroy");
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
	        h.init();

			expect(Bancha.getModelMetaData('Phantasy')).toBeNull(); // doesn't exist
			expect(Bancha.getModelMetaData('User')).hasProperty('fields.2.name'); // remote api meadata object exists
			expect(Bancha.getModelMetaData('User').fields[2].name).toEqual('login'); // it's really the metadata
	    });
	
		it("should preload model meta data using the direct stub", function() {
			h.init();
      
			// create direct stub mock
			var mock = Mock.Proxy();
			mock.expectRPC("loadMetaData",['PreloadTestUser','PreloadTestArticle'])
			Bancha.RemoteStubs.Bancha = mock;
		
			// execute test
			Bancha.preloadModelMetaData(['PreloadTestUser','PreloadTestArticle']);
		    mock.verify();
		    
			// now fake answer
			var result = Ext.encode({
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
			});
			mock.callLastRPCCallback("loadMetaData",[result]);
		
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
			var mock = Mock.Proxy();
			mock.expectRPC("loadMetaData",['PreloadSingleTestUser'])
			Bancha.RemoteStubs.Bancha = mock;

			// execute test
			Bancha.preloadModelMetaData('PreloadSingleTestUser');
		    mock.verify();
		});
	
	
	    it("should throw an error in debug mode / returns false in prodiction mode "+
	        "when Bancha#createModel is called for a not yet loaded metadata of a model", function() {
	        
	        // prepare a remote api without the user metadata
	        Bancha.REMOTE_API = Ext.clone(BanchaSpecHelper.SampleData.remoteApiDefinition);
	        delete Bancha.REMOTE_API.metadata.User;
	        
	        // init
	        Bancha.init();
	        
	        // now test
	        expect(function() {
	            
	            // in debug mode this should throw an error
	            var result = Bancha.createModel('User');
	            
	            // in production mode var result should be false, so 
	            // throw an error to pass the test for production code
	            if(result===false) {
	                throw 'Bancha: Couldn\'t create the model cause the metadata is not loaded yet, please use onModelReady instead.';
	            }
	        }).toThrowExtErrorMsg('Bancha: Couldn\'t create the model cause the metadata is not loaded yet, please use onModelReady instead.');
	    });
        
        it("should create Models with Bancha#createModel", function() {
            
            // setup model metadata
            h.init('CreateModelUser');

            // create a mock object for the proxy
            var mockProxy = Mock.Proxy();

            // should create a user defintion
            expect(
                Bancha.createModel('CreateModelUser', {
                    additionalSettings: true,
                    proxy: mockProxy
            })).toBeTruthy();

            // check if the model really got created
            var model = Ext.ClassManager.get('CreateModelUser');
            expect(model).toBeModelClass('CreateModelUser');
            
            // check if the additional config was used
            expect(model.prototype.additionalSettings).toBeTruthy();

            // test if the model saves data through ext direct
            var user = new CreateModelUser({
                firstname: 'Micky',
                lastname: 'Mouse'
            });

            // define expectations for remote stub calls
            // user.save() should result in one create action
            mockProxy.expect("create");

            // test
            user.save();

            //verify the expectations were met
            mockProxy.verify();    
        });

        it("should create a model if not defined with Bancha.getModel", function() {
        
            // setup model metadata
             h.init("GetModelCreateTestUser");
         
             // create model
             var model = Bancha.getModel('GetModelCreateTestUser');
             expect(model).toBeModelClass('GetModelCreateTestUser');
             
        });
        
        it("should just return the already defined models with Bancha.getModel", function() {
        
            // setup model metadata
             h.init("GetModelJustGetTestUser");
         
             // create model
             var created = Bancha.createModel('GetModelJustGetTestUser',{
                 createdWithCreate: true
             });
             expect(created).toBeTruthy();
             
             // now test getModel
             var model = Bancha.getModel('GetModelJustGetTestUser');
             expect(model).toBeModelClass('GetModelJustGetTestUser');
             expect(model.prototype.createdWithCreate).toBeTruthy();
             
        });

    }); //eo describe basic functions
	


    describe("Bancha scarfold util functions",function() {
        
        it("should pass all Bancha.scarfold.util.toFirstUpper tests", function() {
            var util = Bancha.scarfold.util;
            expect('User').toEqual(util.toFirstUpper('user'));
            expect('UserName').toEqual(util.toFirstUpper('userName'));
        });

        it("should pass all Bancha.scarfold.util.humanize tests", function() {
            var util = Bancha.scarfold.util;

            // first upper case
            expect('User').toEqual(util.humanize('user'));

            // ids
            expect('User').toEqual(util.humanize('user_id'));

            // underscores
            expect('User name').toEqual(util.humanize('user_name'));
            expect('Name with many spaces').toEqual(util.humanize('name_with_many_spaces'));

            // camel case
            expect('User name').toEqual(util.humanize('userName'));
            expect('Name with many spaces').toEqual(util.humanize('nameWithManySpaces'));

            // shouldn't change normal text
            expect('John Smith').toEqual(util.humanize('John Smith'));
            expect('This is a normal text with spaces, Upper case words and all UPPER CASE words!').toEqual(util.humanize('This is a normal text with spaces, Upper case words and all UPPER CASE words!'));
        });
	}); //eo scarfold util functions
	

    describe("Bancha scarfold grid functions",function() {
        
		var rs = BanchaSpecHelper.SampleData.remoteApiDefinition, // remote sample
			h = BanchaSpecHelper; // helper shortcut

		beforeEach(h.reset);
		
		
		it("should build a grid column config with Bancha.scarfold.buildColumns (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridColumnsConfigTest');

            // expected columns
            var expected = [{
                flex     : 1,
                text     : 'Id',
                dataIndex: 'id',
                xtype    : 'numbercolumn',
                format   : '0'
            }, {
                flex     : 1,
                text     : 'Name',
                dataIndex: 'name',
                xtype   : 'gridcolumn'
            }, {
                flex     : 1,
                text     : 'Login',
                dataIndex: 'login',
                xtype    : 'gridcolumn'
            }, {
                flex     : 1,
                text     : 'Created',
                dataIndex: 'created',
                xtype    : 'datecolumn'
            }, {
                flex     : 1,
                text     : 'Email',
                dataIndex: 'email',
                xtype    : 'gridcolumn'
            }, {
                flex     : 1,
                text     : 'Avatar',
                dataIndex: 'avatar',
                xtype    : 'gridcolumn'
            }, {
                flex     : 1,
                text     : 'Weight',
                dataIndex: 'weight',
                xtype    : 'numbercolumn'
            }, {
                flex     : 1,
                text     : 'Height',
                dataIndex: 'height',
                xtype    : 'numbercolumn'
            }];

            // test
            var result = Bancha.scarfold.buildColumns('GridColumnsConfigTest');

            // compare
            expect(result).toEqual(expected);
        });
        
		it("should build a grid column config with Bancha.scarfold.buildColumns with update and delete functions (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridColumnsConfigWithUpdateDeleteTest');

            // expected columns
            var expected = [{
                flex     : 1,
                text     : 'Id',
                dataIndex: 'id',
                xtype    : 'numbercolumn',
                format   : '0',
                field    : {xtype:'numberfield', decimalPrecision:0}
            }, {
                flex     : 1,
                text     : 'Name',
                dataIndex: 'name',
                xtype    : 'gridcolumn',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                text     : 'Login',
                dataIndex: 'login',
                xtype    : 'gridcolumn',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                text     : 'Created',
                dataIndex: 'created',
                xtype    : 'datecolumn',
                field    : {xtype:'datefield'}
            }, {
                flex     : 1,
                text     : 'Email',
                dataIndex: 'email',
                xtype    : 'gridcolumn',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                text     : 'Avatar',
                dataIndex: 'avatar',
                xtype    : 'gridcolumn',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                text     : 'Weight',
                dataIndex: 'weight',
                xtype    : 'numbercolumn',
                field    : {xtype:'numberfield'}
            }, {
                flex     : 1,
                text     : 'Height',
                dataIndex: 'height',
                xtype    : 'numbercolumn',
                field    : {xtype:'numberfield'}
            }, {
                xtype:'actioncolumn', 
                width:50,
                items: [{
                    icon: 'img/icons/delete.png',
                    tooltip: 'Delete',
                    handler: Bancha.scarfold.gridFunction.onDelete
                }]
            }];

            // test
            var result = Bancha.scarfold.buildColumns('GridColumnsConfigWithUpdateDeleteTest', {
                update  : true,
                destroy : true
            });

            // compare
            expect(result).toEqual(expected);
        });
        
        it("should build a grid panel config with Bancha.scarfold.buildGridPanelConfig (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridPanelConfigTest');

            // test
            var result = Bancha.scarfold.buildGridPanelConfig('GridPanelConfigTest', {
                autoLoad: false
            });

            // should have a store
            expect(result.store.getProxy().getModel()).toBeModelClass("GridPanelConfigTest");
            
            // just a simple column check, buildColumns is already tested above
            expect(result.columns.length).toEqual(8);
        });
        
        
        it("should build a grid panel config with update and delete support with Bancha.scarfold.buildGridPanelConfig (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridPanelConfigWithUpdateDeleteTest');

            // test
            var result = Bancha.scarfold.buildGridPanelConfig('GridPanelConfigWithUpdateDeleteTest', {
                autoLoad: false,
                update  : true,
                destroy : true
            });

            // should have a store
            expect(result.store.getProxy().getModel()).toBeModelClass("GridPanelConfigWithUpdateDeleteTest");
            
            // just a simple column check, buildColumns is already tested above
            expect(result.columns.length).toEqual(9);

            // should have all columns editable
            expect(result.columns[0].field.xtype).toEqual("numberfield");
            
            // should be editable
            expect(result.selType).toEqual('cellmodel');
            // expect a celleditor plugin for update support
            expect(result).hasProperty("plugins[0]");
            expect(result.plugins[0]).toBeOfClass("Ext.grid.plugin.CellEditing");
            // standardwise two clicks are expected for update start
            expect(result.plugins[0].clicksToEdit).toEqual(2);
            
            // should have an update button
            expect(result).hasProperty("dockedItems[0].items[1].iconCls");
            expect(result.dockedItems[0].items[1].iconCls).toEqual("icon-save");
        });
        
        
        it("should build a fgrid panel config with full crud support with Bancha.scarfold.buildGridPanelConfig (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridPanelConfigWithCRUDTest');

            // test
            var result = Bancha.scarfold.buildGridPanelConfig('GridPanelConfigWithCRUDTest', {
                autoLoad  : false,
                create    : true,
                update    : true,
                withReset : true,
                destroy   : true,
            },{
                additionalGridConfig: true
            });

            // should have a store
            expect(result.store.getProxy().getModel()).toBeModelClass("GridPanelConfigWithCRUDTest");
            
            // just a simple column check, buildColumns is already tested above
            expect(result.columns.length).toEqual(9);

            // should be editable (simple check)
            expect(result.selType).toEqual('cellmodel');
            expect(result.plugins[0]).toBeOfClass("Ext.grid.plugin.CellEditing");
            
            // should have an create button
            var buttons = result.dockedItems[0].items;
            expect(buttons[1].iconCls).toEqual('icon-add');
            
            // should have an update button
            expect(buttons[2].iconCls).toEqual("icon-save");
            
            // should have an reset button
            expect(buttons[3].iconCls).toEqual('icon-reset');
            
            // should have added the additional grid config
            expect(result.additionalGridConfig).toBeTruthy();
        });
    }); //eo scarfold grid functions
    // TODO add more

}); //eo describe Bancha

//eof
