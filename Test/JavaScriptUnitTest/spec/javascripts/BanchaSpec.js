/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * Tests for the main Bancha class
 *
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

describe("Bancha Singleton - basic retrieval functions on the stubs and model metadata.", function() {
    var rs = BanchaSpecHelper.SampleData.remoteApiDefinition, // remote sample
        h = BanchaSpecHelper; // helper shortcut

    beforeEach(h.reset);

    it("internally uses objectFromPath to retrieve objects", function() {
        // simple unit tests
        /* global BanchaObjectFromPathTest */
        Ext.global.BanchaObjectFromPathTest = {
            object: {
                property: 2
            },
            array: ['a','b', {
                property: 3
            }]
        };

        // successfulles
        expect(Bancha.objectFromPath('BanchaObjectFromPathTest.object.property')).toEqual(2);
        expect(Bancha.objectFromPath('object.property', BanchaObjectFromPathTest)).toEqual(2);
        expect(Bancha.objectFromPath('property', BanchaObjectFromPathTest.object)).toEqual(2);

        expect(Bancha.objectFromPath('BanchaObjectFromPathTest.array.2.property')).toEqual(3);
        expect(Bancha.objectFromPath('2.property',BanchaObjectFromPathTest.array)).toEqual(3);
        expect(Bancha.objectFromPath('1',BanchaObjectFromPathTest.array)).toEqual('b');
        expect(Bancha.objectFromPath(1,BanchaObjectFromPathTest.array)).toEqual('b');

        expect(Bancha.objectFromPath('BanchaObjectFromPathTest')).toEqual(BanchaObjectFromPathTest);

        // can't find these pathes
        expect(Bancha.objectFromPath('')).toBeFalsy();
        expect(Bancha.objectFromPath('',BanchaObjectFromPathTest)).toBeFalsy();
        expect(Bancha.objectFromPath()).toBeFalsy();
        expect(Bancha.objectFromPath({})).toBeFalsy();
        expect(Bancha.objectFromPath(undefined)).toBeFalsy();
        expect(Bancha.objectFromPath(null)).toBeFalsy();
    });

    it("should return the stubs namespace on getStubsNamespace() if already instanciated", function() {
        h.init();

        var ns = Bancha.getStubsNamespace();

        expect(ns).toBeDefined();
        expect(ns.User).toBeDefined();
        expect(ns.User.create).toBeDefined(); // looks good
    });

    it("should return a stubs on getStub(), if defined", function() {
        h.init();

        // stub User exists
        expect(Bancha.getStub('User')).toEqual(Bancha.getStubsNamespace().User);

        // Ext JS and Sencha Touch create the stubs at a different place, so find it
        var stub;
        if(Ext.versions.touch) {
            stub = Bancha.getStubsNamespace()['TestPlugin.PluginTest'];
        } else {
            stub = Bancha.getStubsNamespace().TestPlugin.PluginTest;
        }

        // stub PluginTest in plugin TestPlugin exists
        expect(Bancha.getStub('TestPlugin.PluginTest')).toEqual(stub);

        // this stub doesn't exist
        var handle = spyOn(Ext.Error, 'handle');
        try {
            expect(Bancha.getStub('DoesntExist')).toBeUndefined();
        } catch(e) {
            // in debug mode it throws an error
            handle(e); // perfect
        }
        expect(handle).toHaveBeenCalled();
    });

    it("should initialize Bancha, if getStub() is used", function() {
        Bancha.REMOTE_API = Ext.clone(BanchaSpecHelper.SampleData.remoteApiDefinition);

        expect(Bancha.initialized).toBeFalsy();
        expect(Bancha.getStub('User')).toEqual(Bancha.getStubsNamespace().User);
        expect(Bancha.initialized).toBeTruthy();
    });

    it("should in debug mode return an expection when calling getRemoteApi() before init()", function() {
        // The RemoteApi to was already set during the startup to correctly load dependencies
        // so unset it first
        Bancha.REMOTE_API = undefined;
        // now test
        if(Bancha.debugVersion) {
            expect(function() {
                Bancha.getRemoteApi();
            }).toThrowExtErrorMsg("Bancha: The remote api Bancha.REMOTE_API is not yet defined, "+
                                  "please define the api before using Bancha.getRemoteApi().");
        }
    });

    it("should return the remote api if already defined in js with getRemoteApi()", function() {
        h.init();

        var api = Bancha.getRemoteApi();
        expect(api).property("type").toEqual("remoting");
    });

    it("should init all stubs on init()", function() {
        expect(Bancha.init).toBeAFunction();

        // setup test data
        Bancha.REMOTE_API = Ext.clone(rs);

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
        expect(Bancha.isRemoteModel('TestPlugin.PluginTest')).toBeTruthy(); // remote object exists
    });

    it("should return is a model is loaded with isRemoteModel after init", function() {
        h.init();

        expect(Bancha.isRemoteModel('Phantasy')).toBeFalsy(); // doesn't exist
        expect(Bancha.isRemoteModel('User')).toBeTruthy(); // remote object exists
        expect(Bancha.isRemoteModel('TestPlugin.PluginTest')).toBeTruthy(); // remote object exists
    });

    it("Check Bancha.getModelMetaData function", function() {
        h.init();

        expect(Bancha.getModelMetaData('Phantasy')).toBeNull(); // doesn't exist
        expect(Bancha.getModelMetaData('User')).property('fields.2.name').toEqual('login'); // it's really the metadata
        expect(Bancha.getModelMetaData('TestPlugin.PluginTest')).property('fields.0.name').toEqual('id');
    });

    it("should initialize Bancha, if loadModelMetaData() is used", function() {
        // prepare remote api for beeing initialized
        Bancha.REMOTE_API = Ext.clone(BanchaSpecHelper.SampleData.remoteApiDefinition);

        // make sure it is not yet
        expect(Bancha.initialized).toBeFalsy();

        // expect an Ajax request
        spyOn(Ext.Ajax, 'request').andCallFake(function(config) {
            var dispatcher = BanchaSpecHelper.SampleData.remoteApiDefinition.url;
            if(config.url.indexOf(dispatcher+'?setup-check=true') !== -1) {
                // this is a check of the bancha dispatcher, everything ok
                return {
                    status: 200,
                    responseText: '{"BanchaDispatcherIsSetup":true}'
                };
            }
            // for the model metadata loading
            return {
                status: 200,
                responseText: ''
            };
        });

        // trigger initialization
        Bancha.loadModelMetaData(['LoadMetaDataInitializationTest'],Ext.emptyFn,{},true); // use sync for ajax

        // check that it's initialized
        expect(Bancha.initialized).toBeTruthy();
    });

    it("should load model metadata using the direct stub in async mode.", function() {
        h.init();

        // create direct stub mock
        var mock = Mock.Proxy();
        mock.expectRPC("loadMetaData",['LoadTestUser','LoadTestArticle']);
        Bancha.RemoteStubs.Bancha = mock;

        // create callback
        var scope = {
            scopedExecution: false,
            callback: function() {
                this.scopedExecution = true;
            }
        };

        // execute test
        Bancha.loadModelMetaData(['LoadTestUser','LoadTestArticle'], scope.callback, scope, false);
        mock.verify();

        // now fake answer
        var result = {
            success: true,
            data: {
                LoadTestUser: {
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
                LoadTestArticle: {
                    fields: [
                        {name:'id', type:'int'},
                        {name:'name', type:'string'}
                    ]
                }
            }
        };
        mock.callLastRPCCallback("loadMetaData",[result]);

        // now see if it's available
        expect(Bancha.modelMetaDataIsLoaded('LoadTestUser')).toBeTruthy();
        expect(Bancha.modelMetaDataIsLoaded('LoadTestArticle')).toBeTruthy();

        // check callback
        expect(scope.scopedExecution).toBeTruthy();
    });

    it("should handle errors when loading model metadata using the direct stub.", function() {
        h.init();

        // create direct stub mock
        var mock = Mock.Proxy();
        mock.expectRPC("loadMetaData",['LoadTestImaginary']);
        Bancha.RemoteStubs.Bancha = mock;

        // create callback
        var callback = jasmine.createSpy();

        // execute test
        Bancha.loadModelMetaData(['LoadTestImaginary'], callback, {}, false);
        mock.verify();

        // now fake answer
        var result = {
            success: false,
            message: 'Model Imaginary could not be found.'
        };
        mock.callLastRPCCallback("loadMetaData",[result]);

        // check callback
        expect(callback).toHaveBeenCalledWith(false, 'Model Imaginary could not be found.');
    });

    it("should allow to just give a string as argument when loading only one model metadata.", function() {
        h.init();

        // create direct stub mock
        var mock = Mock.Proxy();
        mock.expectRPC("loadMetaData",['LoadSingleTestUser']);
        Bancha.RemoteStubs.Bancha = mock;

        // execute test
        Bancha.loadModelMetaData('LoadSingleTestUser');
        mock.verify();
    });

    it("should be able to translate model metadata requires into ajax urls", function() {
        // in the project root
        Bancha.REMOTE_API = {
            url: '/bancha-dispatcher.php'
        };
        expect(Bancha.getMetaDataAjaxUrl(['LoadTestUser'])).toEqual('/bancha-load-metadata/[LoadTestUser].js');
        expect(Bancha.getMetaDataAjaxUrl(['LoadTestUser','LoadTestArticle'])).
                toEqual('/bancha-load-metadata/[LoadTestUser,LoadTestArticle].js');

        // in a sub directory
        Bancha.REMOTE_API = {
            url: '/my/subdir/bancha-dispatcher.php'
        };
        expect(Bancha.getMetaDataAjaxUrl(['LoadTestUser'])).toEqual('/my/subdir/bancha-load-metadata/[LoadTestUser].js');
        expect(Bancha.getMetaDataAjaxUrl(['LoadTestUser','LoadTestArticle'])).
                toEqual('/my/subdir/bancha-load-metadata/[LoadTestUser,LoadTestArticle].js');

        // should also support plugin models
        expect(Bancha.getMetaDataAjaxUrl(['MyPlugin.LoadTestUser','LoadTestArticle'])).
                toEqual('/my/subdir/bancha-load-metadata/[MyPlugin.LoadTestUser,LoadTestArticle].js');
    });

    it("should load model metadata using ajax in syncEnabled mode.", function() {
        h.init();

        // create ajax spy
        var loadFn = spyOn(Ext.Ajax, 'request');

        // create callback
        var scope = {
            scopedExecution: false,
            callback: function() {
                this.scopedExecution = true;
            }
        };

        // execute test
        Bancha.loadModelMetaData(['LoadTestUser','LoadTestArticle'], scope.callback, scope, true);
        expect(loadFn).toHaveBeenCalled();
        expect(loadFn.mostRecentCall.args[0].url).toEqual('/bancha-load-metadata/[LoadTestUser,LoadTestArticle].js');
        expect(loadFn.mostRecentCall.args[0].async).toEqual(false);

        // now fake answer
        var response = {
            responseText: Ext.encode({
                LoadTestUser: {
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
                LoadTestArticle: {
                    fields: [
                        {name:'id', type:'int'},
                        {name:'name', type:'string'}
                    ]
                }
            }) //eo responseText
        }; //eo response

        // trigger the ajax onLoaded callback
        loadFn.mostRecentCall.args[0].success(response);

        // now see if it's available
        expect(Bancha.modelMetaDataIsLoaded('LoadTestUser')).toBeTruthy();
        expect(Bancha.modelMetaDataIsLoaded('LoadTestArticle')).toBeTruthy();

        // check custom callback
        expect(scope.scopedExecution).toBeTruthy();
    });

    it("should handle errors when loading model metadata using ajax in syncEnabled mode.", function() {
        h.init();

        // create ajax spy
        var loadFn = spyOn(Ext.Ajax, 'request'),
            callback = jasmine.createSpy();

        // execute test
        Bancha.loadModelMetaData(['LoadTestImaginary'], callback, {}, true);
        expect(loadFn).toHaveBeenCalled();

        // now fake answer
        var response = {
            status: 500,
            statusText: 'Internal Server Error'
        }; //eo response

        // trigger the ajax onLoaded callback
        loadFn.mostRecentCall.args[0].failure(response);

        // check custom callback
        expect(callback).toHaveBeenCalledWith(false, 'Server-side failure with status code 500');
    });


    it("should support (the already deprecated) preloadModelMetaData function.", function() {
        var loadFn = spyOn(Bancha, 'loadModelMetaData'),
            callback = function() {},
            scope = {};

        // test
        Bancha.preloadModelMetaData('LoadSingleTestUser', callback, scope);
        expect(loadFn).toHaveBeenCalled();
        expect(loadFn.mostRecentCall.args[0]).toEqual('LoadSingleTestUser');
        expect(loadFn.mostRecentCall.args[1]).toBe(callback);
        expect(loadFn.mostRecentCall.args[2]).toBe(scope);
        expect(loadFn.mostRecentCall.args[3]).toEqual(false);
    });

    // @deprecated since 2.0
    it("should create Models with Bancha#createModel", function() {

        // setup model metadata
        h.init('CreateModelUser');

        // should create a model defintion
        expect(
            Bancha.createModel('CreateModelUser', {
                additionalSettings: true
            })
        ).toBeTruthy();

        // check if the model really got created
        var model = Ext.ClassManager.get('Bancha.model.CreateModelUser');
        expect(model).toBeModelClass('Bancha.model.CreateModelUser');

        // check if the additional config was used
        if(ExtSpecHelper.isExt) {
            // for ext it is directly applied
            expect(model.prototype.additionalSettings).toBeTruthy();
        } else {
            // for touch it is applied inside the 'config' config
            expect(model.prototype.config.additionalSettings).toBeTruthy();
        }

        // create a mock object for the proxy
        var mockProxy = Mock.Proxy();
        model.setProxy(mockProxy);

        // test if the model saves data through ext direct
        var user = Ext.create('Bancha.model.CreateModelUser',{
            firstname: 'Micky',
            lastname: 'Mouse'
        });

        // define expectations for remote stub calls
        // user.save() should result in one create action
        mockProxy.expect("create");

        // test
        user.save();

        mockProxy.verify();
    });

    it("should create a model if not defined with Bancha.getModel", function() {

        // setup model metadata
        h.init("GetModelCreateTestUser");

        // should be delegated to syncRequire
        var syncRequire = spyOn(Ext, 'syncRequire').andCallFake(function() {
            Ext.define('Bancha.model.GetModelCreateTestUser', {
                extend: 'Ext.data.Model'
            });
        });

        // create model
        var model = Bancha.getModel('GetModelCreateTestUser');
        expect(model).toBeModelClass('Bancha.model.GetModelCreateTestUser');

        // check that the require is was called
        expect(syncRequire).toHaveBeenCalled();
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
        expect(model).toBeModelClass('Bancha.model.GetModelJustGetTestUser');

        // check if it has the correct config
        if(ExtSpecHelper.isExt) {
            // for ext configs are directly applied
            expect(model.prototype.createdWithCreate).toBeTruthy();
        } else {
            // for touch configs are applied inside the 'config' config
            expect(model.prototype.config.createdWithCreate).toBeTruthy();
        }
    });

    it("should initialize Bancha, if getModel() is used", function() {
        Bancha.REMOTE_API = Ext.clone(BanchaSpecHelper.SampleData.remoteApiDefinition);

        // prevent loading of the user model, we don't care
        spyOn(Ext, 'syncRequire');

        expect(Bancha.initialized).toBeFalsy();
        Bancha.getModel('User');
        expect(Bancha.initialized).toBeTruthy();
    });

    it("should synchronously load a model if no metadata is present with Bancha.getModel", function() {

        // don't setup any model metadata
        h.init();

        spyOn(Ext, 'syncRequire').andCallFake(function() {
            // now setup the model data
            Bancha.REMOTE_API.metadata.GetModelSyncLoadTest = Ext.clone(Bancha.REMOTE_API.metadata.User);
            Bancha.REMOTE_API.actions.GetModelSyncLoadTest = Ext.clone(BanchaSpecHelper.SampleData.remoteApiDefinition.actions.User);
            Bancha.getStubsNamespace().GetModelSyncLoadTest = Ext.clone(Bancha.getStubsNamespace().User);

            Ext.define('Bancha.model.GetModelSyncLoadTest', {
                extend: 'Bancha.data.Model' // expect Bancha model to be applied
            });
        });

        // create model
        var model = Bancha.getModel('GetModelSyncLoadTest');
        expect(model).toBeModelClass('Bancha.model.GetModelSyncLoadTest');
    });

    it("should load all needed models on onModelReady and fire functions after the model is ready for a single modelname input", function() {
        h.init();

        // create direct stub mock
        var mock = Mock.Proxy();
        Bancha.RemoteStubs.Bancha = mock;

        // should be called after model is loaded
        var onReadySpy = jasmine.createSpy();

        // if the model is not present it should load the model
        mock.expectRPC("loadMetaData",['OnModelReadyTestModel']);
        Bancha.onModelReady('OnModelReadyTestModel', onReadySpy);
        // model should be loading from server now
        mock.verify();

        // it should NOT execute the function before the model is loaded
        expect(onReadySpy.callCount).toEqual(0);

        // fake that the model is a remote model (supports CRUD operations to the server)
        Bancha.getStubsNamespace().OnModelReadyTestModel = Bancha.getStubsNamespace().User;

        // now fake answer
        var result = {
            success: true,
            data: {
                'OnModelReadyTestModel': {
                    idProperty: 'id',
                    fields: [
                        {name:'id', type:'int'},
                        {name:'name', type:'string'}
                    ]
                }
            }
        };
        mock.callLastRPCCallback("loadMetaData",[result]);

        // now the function should be called
        expect(onReadySpy.callCount).toEqual(1);

        // when the model is loaded it should just execute the function
        Bancha.onModelReady('OnModelReadyTestModel', onReadySpy);
        expect(onReadySpy.callCount).toEqual(2);

    });

    it("should load all needed models on onModelReady and fire functions after the model is ready for an array of modelnames as input", function() {
        h.init();

        // create direct stub mock
        var mock = Mock.Proxy();
        Bancha.RemoteStubs.Bancha = mock;

        // should be called after model is loaded
        var onReadySpy = jasmine.createSpy();

        // bancha should also expect multiple models
        mock.expectRPC("loadMetaData",['OnModelReadyMultipleModelsTestModel1','OnModelReadyMultipleModelsTestModel2']);
        Bancha.onModelReady(['OnModelReadyMultipleModelsTestModel1','OnModelReadyMultipleModelsTestModel2'], onReadySpy);
        // model should be loading from server now
        mock.verify();

        // it should NOT execute the function before the model is loaded
        expect(onReadySpy.callCount).toEqual(0);

        // fake that the model is a remote model (supports CRUD operations to the server)
        Bancha.getStubsNamespace().OnModelReadyMultipleModelsTestModel1 = Bancha.getStubsNamespace().User;
        Bancha.getStubsNamespace().OnModelReadyMultipleModelsTestModel2 = Bancha.getStubsNamespace().User;

        // now fake answer
        var result = {
            success: true,
            data: {
                'OnModelReadyMultipleModelsTestModel1': {
                    idProperty: 'id',
                    fields: [
                        {name:'id', type:'int'},
                        {name:'name', type:'string'}
                    ]
                },
                'OnModelReadyMultipleModelsTestModel2': {
                    idProperty: 'id',
                    fields: [
                        {name:'id', type:'int'},
                        {name:'name', type:'string'}
                    ]
                }
            }
        };
        mock.callLastRPCCallback("loadMetaData",[result]);

        // now the function should be called
        expect(onReadySpy.callCount).toEqual(1);
    });

    it("should have a shortcut to Bancha.Logger.log using Bancha.log", function() {

        spyOn(Bancha.Logger,'log');
        Bancha.log('message','error');
        expect(Bancha.Logger.log).toHaveBeenCalledWith('message','error');

    });

    it("should trigger the onError function if in production mode and there is a script error", function() {

        var isPhantomJS = (typeof phantom !== 'undefined' || typeof _phantom !== 'undefined');
        if(isPhantomJS || Ext.isIE6 || Ext.isIE7) { // no support for IE 6 and 7
            return; // This test works in the Browser, but fails using phantomjs test runner
        }

        /* global document, window */
        var loadScript = function(src, cb) {
            var script = document.createElement("script");
            script.type = "text/javascript";
            script.async = false;
            script.src = src;

            // prepare callback
            var isLoaded = false;

            // most browsers
            script.onload = function() {
                isLoaded = true;
                cb();
            };
            // IE 6 & 7
            script.onreadystatechange = function() {
                if (this.readyState === 'complete') {
                    isLoaded = true;
                    cb();
                }
            };
            setTimeout(function() {
                // if the file can not be laoded, fail
                if(!isLoaded) {
                    expect('Could not load file '+src).toEqual(false);
                }
            }, 500);

            // include in page
            var pageScript=document.getElementsByTagName("script")[0];
            pageScript.parentNode.insertBefore(script, pageScript);
        };

        // make an asyn test
        var testPrepared = false,
            onError = window.onerror,
            setTimeout = window.setTimeout,
            setInterval = window.setInterval;
        runs(function() {
            // load TraceKit before initializing Bancha
            loadScript('../../webroot/js/tracekit/tracekit.js', function() {
                // init the error listeners
                h.init();

                // setup environment
                spyOn(Bancha.Remoting, 'onError');
                Bancha.getRemoteApi().metadata._ServerDebugLevel = 0;

                // load an external js file with an error (via script tag, not ajax)
                loadScript('spec/javascripts/helpers/js-error-file.js', function() {
                    testPrepared = true;
                });
            });
        });

        waitsFor(function() {
            return testPrepared;
        }, 'Waiting to load external scripts', 10*100);

        runs(function() {
            // verify that the error was catched
            expect(Bancha.Remoting.onError).toHaveBeenCalled();
            expect(Bancha.Remoting.onError.mostRecentCall.args[0]).property('message').toBeDefined();

            // depending on execution this can has a different error message
            // if executed via file:// uri it create a script error.
            // if executed via http:// uri it creates a type error.
            var msg = Bancha.Remoting.onError.mostRecentCall.args[0].message;
            if(msg !== 'Script error.' &&
                msg !== 'Uncaught TypeError: Cannot read property \'This error should be thrown and will not be catched\' of undefined' && // Chrome
                msg !== 'undefined has no properties' && // old Firefox
                msg !== 'TypeError: undefined has no properties' && // current Firefox
                msg !== 'Uncaught exception: TypeError: Cannot convert \'undefined\' to object' && // Opera
                msg !== '\'This error should be thrown and will not be catched\' is null or not an object') { // IE 8
                expect(false).toBeTruthy(); // no error catched
            }

            // on, now that the test is over remove TraceKit
            window.onerror = onError;
            window.setTimeout = setTimeout;
            window.setInterval = setInterval;
        });
    });
});
