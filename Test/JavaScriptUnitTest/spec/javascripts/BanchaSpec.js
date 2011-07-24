/*!
 * Bancha Tests
 * Copyright(c) 2011 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011 Roland Schuetz
 */
/*jslint browser: true, vars: true, plusplus: true, white: true, sloppy: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, BanchaSpecHelper */

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
            expect(function() {
                Bancha.getRemoteApi();
            }).toThrowExtErrorMsg("Bancha: The remote api Bancha.REMOTE_API is not yet defined, "+
                                  "please define the api before using Bancha.getRemoteApi().");
        });
    
    
        it("should return the remote api if already defined in js with getRemoteApi()", function() {
            h.init();
        
            var api = Bancha.getRemoteApi();
            expect(api).property("type").toEqual("remoting");
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
            expect(Bancha.RemoteStubs).property("User.create").toBeAFunction(); //"The RemoteStub User supports create"
            expect(Bancha.RemoteStubs).property("User.destroy").toBeAFunction(); //"The RemoteStub User supports create"
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
            expect(Bancha.getModelMetaData('User')).property('fields.2.name').toEqual('login'); // it's really the metadata
        });
    
    
        it("should preload model meta data using the direct stub", function() {
            h.init();
      
            // create direct stub mock
            var mock = Mock.Proxy();
            mock.expectRPC("loadMetaData",['PreloadTestUser','PreloadTestArticle']);
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
            expect(Bancha.getModelMetaData('PreloadTestUser')).property('fields.2.name').toEqual('login');
        });
    
    
        it("should allow to just give a string as argument when preloading only one model meta data", function() {
            h.init();
      
            // create direct stub mock
            var mock = Mock.Proxy();
            mock.expectRPC("loadMetaData",['PreloadSingleTestUser']);
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
                    throw 'Bancha: Couldn\'t create the model cause the metadata is not loaded yet, '+
                          'please use onModelReady instead.';
                }
            }).toThrowExtErrorMsg('Bancha: Couldn\'t create the model cause the metadata is not loaded yet, '+
                                  'please use onModelReady instead.');
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
            var user = Ext.create("CreateModelUser",{
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


        // TODO test functiosn for onModelReady
        
    }); //eo describe basic functions
    


    describe("Bancha scaffold util functions",function() {
        
        it("should pass all Bancha.scaffold.Util.toFirstUpper tests", function() {
            var util = Bancha.scaffold.Util;
            expect('User').toEqual(util.toFirstUpper('user'));
            expect('UserName').toEqual(util.toFirstUpper('userName'));
        });


        it("should pass all Bancha.scaffold.Util.humanize tests", function() {
            var util = Bancha.scaffold.Util;

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
            expect('This is a normal text with spaces, Upper case words and all UPPER CASE words!'
                   ).toEqual(util.humanize('This is a normal text with spaces, Upper case words '+
                   'and all UPPER CASE words!'));
        });
        
    }); //eo scaffold util functions
    

    describe("Bancha scaffold grid functions",function() {
        
        var h = BanchaSpecHelper, // shortcuts
            gridScaf = Bancha.scaffold.GridConfig,
            // take the defaults
            // (actually this is also copying all the function references, but it doesn't atter)
            testDefaults = Ext.clone(gridScaf);
        
        // force easiert defaults for unit testing
        testDefaults = Ext.apply(testDefaults,{
            enableCreate:  false,
            enableUpdate:  false,
            enableDestroy: false,
            enableReset:   false,
            storeDefaults: {
                autoLoad: false // since we only want to unit-test and not laod data
            }
        });
        
        beforeEach(function() {
            h.reset();
            // re-enforce defaults
            Ext.apply(gridScaf, testDefaults);
        });
        
        

        it("should build column configs while considering the defined defaults", function() {
            // define some defaults
            gridScaf.columnDefaults = {
                forAllFields: 'added'
            };
            gridScaf.gridcolumnDefaults = {
                justForText: true
            };
            gridScaf.datecolumnDefaults = {};

            expect(gridScaf.buildColumnConfig('string','someName')).toEqual({
                forAllFields: 'added',
                justForText: true,
                xtype : 'gridcolumn',
                text: 'Some name',
                dataIndex: 'someName'
            });

            // now there should be just added the first one
            expect(gridScaf.buildColumnConfig('date','someName')).toEqual({
                forAllFields: 'added',
                xtype : 'datecolumn',
                text: 'Some name',
                dataIndex: 'someName'
            });
        });

        it("should build column configs while considering special defaults per call", function() {
            gridScaf.columnDefaults = {
                forAllFields: 'added'
            };
            gridScaf.gridcolumnDefaults = {
                justForText: true
            };
            var defaults = {
                gridcolumnDefaults: {
                    justForThisTextBuild: true
                }
            };
            
            expect(gridScaf.buildColumnConfig('string','someName',defaults)).toEqual({
                forAllFields: 'added',
                justForThisTextBuild: true, // <-- old defaults got overrided
                xtype : 'gridcolumn',
                text: 'Some name',
                dataIndex: 'someName'
            });

            // now there should be just added the first one
            expect(gridScaf.buildColumnConfig('date','someName',defaults)).toEqual({
                forAllFields: 'added',
                xtype : 'datecolumn',
                text: 'Some name',
                dataIndex: 'someName'
            });
        });
        
        it("should build a grid column config with Bancha.scaffold.GridConfig.buildColumns (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridColumnsConfigTest');

            // expected columns
            var expected = [{
                flex     : 1,
                xtype    : 'numbercolumn',
                format   : '0',
                text     : 'Id',
                dataIndex: 'id',
                hidden   : true
            }, {
                flex     : 1,
                xtype   : 'gridcolumn',
                text     : 'Name',
                dataIndex: 'name'
            }, {
                flex     : 1,
                xtype    : 'gridcolumn',
                text     : 'Login',
                dataIndex: 'login'
            }, {
                flex     : 1,
                xtype    : 'datecolumn',
                text     : 'Created',
                dataIndex: 'created'
            }, {
                flex     : 1,
                xtype    : 'gridcolumn',
                text     : 'Email',
                dataIndex: 'email'
            }, {
                flex     : 1,
                xtype    : 'gridcolumn',
                text     : 'Avatar',
                dataIndex: 'avatar'
            }, {
                flex     : 1,
                xtype    : 'numbercolumn',
                text     : 'Weight',
                dataIndex: 'weight'
            }, {
                flex     : 1,
                xtype    : 'numbercolumn',
                format   : '0',
                text     : 'Height',
                dataIndex: 'height'
            }];

            // test
            var result = Bancha.scaffold.GridConfig.buildColumns('GridColumnsConfigTest');

            // compare
            expect(result).toEqual(expected);
        });
        
        
        it("should build a grid column config with Bancha.scaffold.GridConfig.buildColumns with update "+
            "and delete functions (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridColumnsConfigWithUpdateDeleteTest');

            // expected columns
            var expected = [{
                flex     : 1,
                xtype    : 'numbercolumn',
                format   : '0',
                text     : 'Id',
                dataIndex: 'id',
                field    : undefined,
                hidden   : true
            }, {
                flex     : 1,
                xtype    : 'gridcolumn',
                text     : 'Name',
                dataIndex: 'name',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                xtype    : 'gridcolumn',
                text     : 'Login',
                dataIndex: 'login',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                xtype    : 'datecolumn',
                text     : 'Created',
                dataIndex: 'created',
                field    : {xtype:'datefield'}
            }, {
                flex     : 1,
                xtype    : 'gridcolumn',
                text     : 'Email',
                dataIndex: 'email',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                xtype    : 'gridcolumn',
                text     : 'Avatar',
                dataIndex: 'avatar',
                field    : {xtype:'textfield'}
            }, {
                flex     : 1,
                xtype    : 'numbercolumn',
                text     : 'Weight',
                dataIndex: 'weight',
                field    : {xtype:'numberfield'}
            }, {
                flex     : 1,
                xtype    : 'numbercolumn',
                format   : '0',
                text     : 'Height',
                dataIndex: 'height',
                field    : {xtype:'numberfield', allowDecimals : false}
            }, {
                xtype:'actioncolumn', 
                width:50,
                items: [{
                    icon: 'img/icons/delete.png',
                    tooltip: 'Delete',
                    handler: Bancha.scaffold.GridConfig.createFacade('onDelete')
                }]
            }];

            // test
            var result = Bancha.scaffold.GridConfig.buildColumns('GridColumnsConfigWithUpdateDeleteTest', {
                enableUpdate  : true,
                enableDestroy : true
            });

            // compare
            expect(result).toEqual(expected);
        });
        
        
        it("should build a grid panel config with Bancha.scaffold.GridConfig.buildConfig (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridPanelConfigTest');

            // test
            var result = Bancha.scaffold.GridConfig.buildConfig('GridPanelConfigTest');

            // should have a store
            expect(result.store.getProxy().getModel()).toBeModelClass("GridPanelConfigTest");
            
            // just a simple column check, buildColumns is already tested above
            expect(result.columns.length).toEqual(8);
        });
        
        
        it("should build a grid panel config with update and delete support with "+
            "Bancha.scaffold.GridConfig.buildConfig (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridPanelConfigWithUpdateDeleteTest');

            // test
            var result = Bancha.scaffold.GridConfig.buildConfig('GridPanelConfigWithUpdateDeleteTest', {
                enableUpdate  : true,
                enableDestroy : true
            });

            // should have a store
            expect(result.store.getProxy().getModel()).toBeModelClass("GridPanelConfigWithUpdateDeleteTest");
            
            // just a simple column check, buildColumns is already tested above
            expect(result.columns.length).toEqual(9);

            // should have all columns editable
            // (the first is the id-field and therefore is guessed to don't have an editorfield)
            expect(result.columns[1].field.xtype).toEqual("textfield");
            
            // should be editable
            expect(result.selType).toEqual('cellmodel');
            // expect a celleditor plugin for update support
            expect(result).property("plugins.0").toBeOfClass("Ext.grid.plugin.CellEditing");
            // standardwise two clicks are expected for update start
            expect(result).property("plugins.0.clicksToEdit").toEqual(2);
            
            // should have an update button
            expect(result).property("dockedItems.0.items.1.iconCls").toEqual("icon-save");
        });
        
        
        it("should build a grid panel config with full crud support with "+
            "Bancha.scaffold.GridConfig.buildConfig (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('GridPanelConfigWithCRUDTest');

            // test
            var result = Bancha.scaffold.GridConfig.buildConfig('GridPanelConfigWithCRUDTest', {
                enableCreate    : true,
                enableUpdate    : true,
                enableReset : true,
                enableDestroy   : true
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
    }); //eo scaffold grid functions


    describe("Bancha scaffold form functions",function() {
        
        var h = BanchaSpecHelper, // shortcuts
            formScaf = Bancha.scaffold.FormConfig; //shortcuf
            // take the defaults
            // (actually this is also copying all the function references, but it doesn't atter)
            testDefaults = Ext.clone(formScaf);
    
        beforeEach(function() {
            h.reset();
            // re-enforce defaults
            Ext.apply(formScaf, testDefaults);
        });
        
        it("should build field configs while considering the defined defaults", function() {
            // define some defaults
            formScaf.fieldDefaults = {
                forAllFields: 'added'
            };
            formScaf.textfieldDefaults = {
                justForText: true
            };
            formScaf.datefieldDefaults = {};
            
            expect(formScaf.buildFieldConfig('string','someName')).toEqual({
                forAllFields: 'added',
                justForText: true,
                xtype : 'textfield',
                fieldLabel: 'Some name',
                name: 'someName'
            });
            
            // now there should be just added the first one
            expect(formScaf.buildFieldConfig('date','someName')).toEqual({
                forAllFields: 'added',
                xtype : 'datefield',
                fieldLabel: 'Some name',
                name: 'someName'
            });
        });
        
        it("should build field configs while considering special defaults per call", function() {
            formScaf.fieldDefaults = {
                forAllFields: 'added'
            };
            formScaf.textfieldDefaults = {
                justForText: true
            };
            var defaults = {
                textfieldDefaults: {
                    justForThisTextBuild: true
                }
            };
            
            expect(formScaf.buildFieldConfig('string','someName',defaults)).toEqual({
                forAllFields: 'added',
                justForThisTextBuild: true, // <-- old defaults got overrided
                xtype : 'textfield',
                fieldLabel: 'Some name',
                name: 'someName'
            });

            // now there should be just added the first one
            expect(formScaf.buildFieldConfig('date','someName'),defaults).toEqual({
                forAllFields: 'added',
                xtype : 'datefield',
                fieldLabel: 'Some name',
                name: 'someName'
            });
        });
        
        var getButtonConfig = function(id) {
            return [
                Bancha.scaffold.FormConfig.buildButton({
                    text: 'reset',
                    iconCls: 'icon-reset',
                },Bancha.scaffold.FormConfig.onReset,id),
            Bancha.scaffold.FormConfig.buildButton({
                    text: 'Save',
                    iconCls: 'icon-save',
                    formBind: true,
                },Bancha.scaffold.FormConfig.onSave,id)
            ];
        };
        
        it("should build a form config, where it recognizes the type from the field type, when no validation rules are set in the model (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('FormConfigTest');
            
            var expected = {
                id: 'FormConfigTest-id', // forced
                // configs for BasicForm
                api: {
                    // The server-side method to call for load() requests
                    load: Bancha.getStubsNamespace().User.load,
                    // The server-side must mark the submit handler as a 'formHandler'
                    submit: Bancha.getStubsNamespace().User.submit
                },
                items: [{ //TODO use scaffolding
                    xtype: 'hiddenfield',
                    allowDecimals : false,
                    fieldLabel: 'Id',
                    name: 'id'
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Name',
                    name: 'name'
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Login',
                    name: 'login'
                },{
                    xtype: 'datefield',
                    fieldLabel: 'Created',
                    name: 'created'
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Email',
                    name: 'email'
                }, {
                    xtype: 'textfield', // an fileuploadfield is recognized through validation rules
                    fieldLabel: 'Avatar',
                    name: 'avatar'
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Weight',
                    name: 'weight'
                }, {
                    xtype: 'numberfield',
                    allowDecimals : false,
                    fieldLabel: 'Height',
                    name: 'height'
                }],
                buttons: getButtonConfig('FormConfigTest-id')
            }; // eo expected
            
            expect(Bancha.scaffold.FormConfig.buildConfig('FormConfigTest',false,{
                id: 'FormConfigTest-id'
            })).toEqual(expected);
        });
        
        it("should build a form config, where it recognizes the type from the field type, when no validation rules are set in the model (component test)", function() {
            // prepare
            h.initAndCreateSampleModel('FormConfigWithValidationTest',{
                validations: [
                    {type:'presence', name:'id'},
                    {type:'presence', name:'name'},
                    {type:'length', name:'name', min:3, max:64},
                    {type:'presence', name:'login'},
                    {type:'length', name:'login', min:3, max:64},
                    {type:'format', name:'login', matcher: /^[a-zA-Z0-9_]+$/},
                    {type:'presence', name:'email'},
                    {type:'format', name:'email', matcher: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/},
                    {type:'numberformat', name:'weight', precision:2},
                    {type:'numberformat', name:'height', min:50, max:300},
                    {type:'file', name:'avatar', extension:['gif', 'jpeg', 'png', 'jpg']},
                ]
            });
            
            expect(Bancha.getStubsNamespace().User.load).toBeAFunction();
            
            var expected = {
                id: 'FormConfigWithValidationTest-id', // forced
                // configs for BasicForm
                api: {
                    load: Bancha.getStubsNamespace().User.load, // TODO this should be a function!
                    submit: Bancha.getStubsNamespace().User.submit
                },
                items: [{ 
                    xtype: 'hiddenfield',
                    allowDecimals: false,
                    fieldLabel: 'Id',
                    name: 'id',
                    allowBlank:false
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Name',
                    name: 'name',
                    allowBlank:false,
                    minLength: 3,
                    maxLength: 64
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Login',
                    name: 'login',
                    allowBlank:false,
                    minLength: 3,
                    maxLength: 64,
                    vtype: 'alphanum' // use toString to compare
                },{
                    xtype: 'datefield',
                    fieldLabel: 'Created',
                    name: 'created'
                },{
                    xtype: 'textfield',
                    fieldLabel: 'Email',
                    name: 'email',
                    allowBlank: false,
                    vtype: 'email'
                }, {
                    xtype: 'fileuploadfield',
                    fieldLabel: 'Avatar',
                    name: 'avatar',
                    emptyText: 'Select an image',
                    buttonText: '',
                    buttonConfig: {
                        iconCls: 'icon-upload'
                    },
                    vtype: 'fileExtension',
                    validExtensions: ['gif', 'jpeg', 'png', 'jpg']
                }, {
                    xtype: 'numberfield',
                    fieldLabel: 'Weight',
                    name: 'weight',
                    decimalPrecision: 2
                }, {
                    xtype: 'numberfield',
                    allowDecimals: false,
                    fieldLabel: 'Height',
                    name: 'height',
                    minValue: 50,
                    maxValue: 300
                }],
                buttons: getButtonConfig('FormConfigWithValidationTest-id')
            }; // eo expected
            
            expect(Bancha.scaffold.FormConfig.buildConfig('FormConfigWithValidationTest',false,{
                id: 'FormConfigWithValidationTest-id',
                fileuploadfieldDefaults: {
                    emptyText: 'Select an image',
                    buttonText: '',
                    buttonConfig: {
                        iconCls: 'icon-upload'
                    }
                }
            })).toEqual(expected);
        });
        
    }); //eo scaffold form functions
    
}); //eo describe Bancha

//eof
