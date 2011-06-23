/*!
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       Bancha
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0 // TODO vom Precompiler ausfuellen lassen
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 * @version       0.0.1 // TODO vom Precompiler ausfuellen lassen
 *
 * For more information go to http://banchaproject.org 
 */
/*jslint browser: true, vars: false, undef: true, nomen: true, eqeqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, Bancha, window */

// TODO Docu: Add Links in Format: {@link Ext#define}
// TODO Native support for Ext.data.TreeStore with server-side TreeBehaviour
// TODO Form Support: http://dev.sencha.com/deploy/ext-4.0.0/examples/direct/direct-form.html
// TODO serverside form validation
// TODO selectboxes with serverside content (enum support also(?)) http://dev.sencha.com/deploy/ext-4.0.0/examples/form/combos.html
// TODO Model.clientName: the clientside model name (serverseitig als $Model->exposeAs)

/**
 * @class Bancha.data.Model
 * @extends Ext.data.Model
 * 
 * currently model is doing not much, we expect to need some additional code here later
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 * 
 * @constructor
 * @param {Object} data An object containing keys corresponding to this model's fields, and their associated values
 * @param {Number} id Optional unique ID to assign to this model instance
 */
Ext.define('Bancha.data.Model', {
    extend: 'Ext.data.Model',
    // TODO forceConsistancy: use the consistant model
    forceConsistancy: false
});



/**
 * @class Bancha
 * // TODO docu here, especially about createModel, getModel, onModelReady, init
// spec http://www.sencha.com/products/extjs/extdirect



 * example:
 * <code>
 * <!-- include Bancha and the remote API -->
 * <script type="text/javascript" src="path/to/cakephp/Bancha/api.js"></script>
 * <script type="text/javascript" src="path/to/bancha-debug.js"></script>
 * <script>
 * <Ext.onReady(function() {
 *     // init the Bancha with remote information
 *     Bancha.init();
 * 
 *     // when Bancha is ready, the model meta data is 
 *  // loaded from the server and the model is created...
 *     Bancha.onModelReady('User', function() {
 *         // ... create a users grid
 *         Ext.create('Ext.grid.Panel', {
 *             store: {
 *                 model: 'User',
 *                 autoLoad: true
 *             },
 *             columns: Bancha.scarfold.buildColumns(userModel) // propably not suitable for production
 *         });
 * });
 * </script></code>
 * 
 * // TODO later exceptions fangen?
 * Please listen for exceptions on Ext.direct.Manager
 *
 * @singleton
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha', {
    
    /* Begin Definitions */
    singleton: true,
    
    requires: [
        'Bancha.data.Model',
        'Ext.direct.*'
    ],
    /* End Definitions */
    
    
    /**
     * Bancha Project version
     * @property debug
     * @type String
     */
    version: '0.0.1',
    /**
     * Only change this if you changed 'Bancha.remote_api' in the config, never change after Bancha.init()
     * @cfg {String} remoteApi The local path to the Bancha remote api (Default: Bancha.REMOTE_API)
     */
    remoteApi: 'Bancha.REMOTE_API',
    /**
     * @propterty The path of the RCP for loading model meta data from the server, without the namespace
     * @type {String}
     */
    metaDataLoadFunction: 'Bancha.loadMetaData',
    /**
     * @propterty apiRoute The absolute route to the Bancha API, will be loaded from the REMOTE_API configuration on Bancha.init()
     * @type {String}
     */
    apiRoute: null, //TODO server should to set the absolute path!
    /**
     * the namespace of Ext.Direct stubs, will be loaded from the REMOTE_API configuration on Bancha.init()
     * null means no namespace, this is not recommanded. The namespace can be set in CakePHP Configure:write('Bancha.namespace','Bancha.RemoteStubs'); 
     */
    namespace: null,
    /**
     * indicates that Bancha is initialized. Used for debugging.
     * @private
     * @property {Boolean} initialized
     */
    initialized: false,
    /**
     * Safely finds an object, used internally for getStubsNamespace and getRemoteApi
     * (This function is tested in RS.util, not part of the package testing, but it is tested)
     * @param {String} path A period ('.') separated path to the desired object (String).
     * @param {String} lookIn optional: The object on which to perform the lookup.
     * @return {Object} The object if found, otherwise undefined.
     * @member Bancha
     * @method objectFromPath
     * @private
     */
    objectFromPath: function(path, lookIn) {
        if (!lookIn) {
            //get the global object so it don't use hasOwnProperty on window (IE incompatible)
            var first = path.indexOf('.'),
                globalObjName,
                globalObj;
            if (first === -1) {
                // the whole path is only one object so eturn the result
                return window[path];
            }
            // else the first part as global object name
            globalObjName = path.slice(0, first);
            globalObj = window[globalObjName];
            if (typeof globalObj === 'undefined') {
                // path seems to be false
                return undefined;
            }
            // set the ne lookIn and the path
            lookIn = globalObj;
            path = path.slice(first + 1);
        }
        // get the object
        return path.split('.').reduce(function(o, p) {
            if(o && o.hasOwnProperty(p)) {
                return o[p];
            }
        }, lookIn);
    },
    /**
     * Returns the namespace of the remote stubs
     * @return {Object} The namespace if already instanciated, otherwise undefined
     * @member Bancha
     * @method getStubsNamespace
     */
    getStubsNamespace: function() {
        // IFDEBUG
        if(!this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha is not yet initalized, please init before using Bancha.getStubsNamespace().'
            });
        }
        // ENDIF
        return this.objectFromPath(this.namespace);
    },
    /**
     * Returns the remote api definition of Ext.direct
     * @return {Object} The api if already defined, otherwise undefined
     * @member Bancha
     * @method getRemoteApi
     */
    getRemoteApi: function() {    
        // IFDEBUG
        if(!Ext.isString(this.remoteApi)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha.remoteApi is not yet defined, please define the api before using Bancha.getRemoteApi().'
            });
        }
        if(!Ext.isObject(this.objectFromPath(this.remoteApi))) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: The remote api '+this.remoteApi+' is not yet defined, please define the api before using Bancha.getRemoteApi().'
            });
        }
        // ENDIF
        return this.objectFromPath(this.remoteApi);
    },
    /**
     * inits Bancha with the RemotingProvider, allways init before using Bancha
     */
    init: function() {
        var remoteApi;
        
        // IFDEBUG
        if(!Ext.isReady) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha should be initalized after the onReady event.'
            });
        }
        
        if(!Ext.isObject(this.objectFromPath(this.remoteApi))) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: The remote api '+this.remoteApi+' is not yet defined, please define the api before using Bancha.init().'
            });
        }
        
        if(this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha is initalized twice, please just initialize once.'
            });
        }
        // ENDIF
        
        remoteApi = this.getRemoteApi();
        
        // IFDEBUG
        if(Ext.isObject(remoteApi)===false) {
            Ext.Error.raise({
                plugin: 'Bancha',
                remoteApi: this.remoteApi,
                msg: 'Bancha: The Bancha.remoteApi config seems to be configured wrong. (see also CakePHPs Configure:write(\'Bancha.remote_api\'Bancha.REMOTE_API\');'
            });
        }
        // ENDIF
        
        this.apiRoute  = remoteApi.url;
        this.namespace = remoteApi.namespace || null;
        
        // init Provider
        Ext.direct.Manager.addProvider(remoteApi);
        
        this.initialized = true;
    },
    
    
    /**
     * for Dev mode?
     */
    initLoader: function() { // TODO
      var script = document.createElement("script");
      script.src = "https://www.google.com/jsapi?key=INSERT-YOUR-KEY&callback=loadMaps";
      script.type = "text/javascript";
      document.getElementsByTagName("head")[0].appendChild(script);
    },
    
    
    /**
     * Preloads the models metadata from the server to create a new model
     * 
     * When to use it: You should use this function is you don't want to load 
     * the metadata at startup, but want to load it before it is (eventually) 
     * used to have a more reactive interface.
     * 
     * Attention: In most cases it's best to load all model metadata on startup
     * when the api is loaded, please install guide for more inforamtion. This
     * is mostly usefull if you can guess that a user will need a model soon
     * which wasn't loaded at startup or if you want to load all not at startup
     * needed models right after startup with something like: 
     * <code>Ext.onReady(
     *        Ext.Function.createDelayed(
     *             function() { Bancha.preloadModelMetaData('all'); },
     *             100));
     * </code>
     *
     * @param {Array|String} models An array of the models to preload, one model name or 'all'
     * @param {Function} models Optional callback function
     * @param {Object} models Optional scope of the callback function
     * @member Bancha
     * @method preloadModelMetaData
     */
    preloadModelMetaData: function(models,callback,scope) {
        // get remote stub function
        var cb,
            fn = this.objectFromPath(this.metaDataLoadFunction,this.getStubsNamespace());
        
        // IFDEBUG
        if(!Ext.isFunction(fn)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                metaDataLoadFunction: this.metaDataLoadFunction,
                msg: 'Bancha: The Bancha.metaDataLoadFunction config seems to be configured wrong.'
            });
        }
        // ENDIF
        
        // IFDEBUG
        Ext.each(models, function(modelName) {
            if(Ext.isObject(Bancha.getModelMetaData(modelName))) {
                if(window.console && Ext.isFunction(console.warn)) {
                    window.console.warn(
                        'Bancha: The model '+modelName+' is already loaded, we will reload the meta data '+
                        'but this seems strange. Notice that this warning is only created in debug mode.');
                }
            }
        });
        // ENDIF
        
        // force models to be an array
        if(Ext.isString(models)) {
            models = [models];
        }
        
        // TODO lesen
        //Is it in php possible to force a type in the method deklaration, like function(Integer $int) ?
        // http://www.sencha.com/forum/showthread.php?134882-How-to-implement-Ext.Direct-in-MVC-architecture-with-ExtJs-4
        // http://www.sencha.com/forum/showthread.php?134505-Model-proxy-for-a-Store-doesn-t-seem-to-work-if-the-proxy-is-a-direct-proxy
        // Test samples.html
        // TODO probably should be done with Ext.Direct
        
        cb = function(result, event) { // TODO test this
            var data = Ext.decode(result);
            
            // IFDEBUG
            if(data===null) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    result: result,
                    event: event,
                    msg: 'Bancha: The Bancha.preloadModelMetaData('+models.toString()+') did expect to to get the metadata from the server, instead got: '+result
                });
            }
            // ENDIF
        
            // save result
            Ext.apply(Bancha.getRemoteApi().metadata,data);
            
            // IFDEBUG
            if(!Ext.isFunction(callback) && Ext.isDefined(callback)) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    callback: callback,
                    msg: 'Bancha: The for Bancha.preloadModelMetaData supplied callback is not a function.'
                });
            }
            if(!Ext.isObject(scope) && Ext.isDefined(scope)) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    callback: callback,
                    msg: 'Bancha: The for Bancha.preloadModelMetaData supplied scope is not a object.'
                });
            }
            // ENDIF
            // user callback
            if(typeof callback==='function') {
                if(!Ext.isDefined(scope)) {
                    scope = window;
                }
                callback.call(scope,data);
            }
        };
        
        // start ext.direct request
        fn(models,cb,Bancha);
    },
    /**
     * checks if the model is supported by the server
     * @param {String} modelName the name of the model
     * @return {Boolean} True is the model is remotable
     * @member Bancha
     * @method isRemoteModel
     */
    isRemoteModel: function(modelName) {
        return (
                Ext.isObject(this.getStubsNamespace()) && 
                Ext.isObject(this.getStubsNamespace()[modelName])
                ) ? true : false;
    },
    
    /**
     * checks if the metadata of a model is loaded
     * @param {String} modelName the name of the model
     * @return {Boolean} True is the metadata is present
     * @member Bancha
     * @method modelMetaDataIsLoaded
     */
    modelMetaDataIsLoaded: function(modelName) {
        var api = this.getRemoteApi();
        return (
                Ext.isObject(api) && 
                Ext.isObject(api.metadata) &&
                Ext.isObject(api.metadata[modelName])
                ) ? true : false;
    },
    
    /**
     * Loads and instanciates a model if not already done and then
     * calls the callback function.
     * 
     * If Bancha is not initialized already it will wait for Ext.isReady
     * and calls Bancha.init() first.
     * @param {String|Array} modelNames a name of the model or an array of model names
     * @param {Function} callback Optional callback function, the first argument is: //TODO OPTIMIZE keine Argumente?
                          - a model class if input modelNames was an string
                          - an object with model names as keys and models as arguments if an array was given
     * @param {Object} scope Optional scope of the callback function
     * @member Bancha
     * @method onModelReady
     */
    onModelReady: function(modelNames, callback, scope) {
        if(this.initialized) {
            this.onInitializedOnModelReady(modelNames, null, null, callback, scope);
        } else {
            Ext.onReady(function() {
                this.init();
                this.onInitializedOnModelReady(modelNames, null, null, callback, scope);
            },this);
        }
    },
    /**
     * Helper, Bancha.onModelReady will call this function in an Ext.onReady
     * !!!don't use this function directly!!!
     * @private
     */
    onInitializedOnModelReady: function(modelNamesToLoad, loadingModels, loadedModels, callback, scope) {
        var modelsToLoad  = [],
            modelName;
        loadingModels = loadingModels || {};
        loadedModels  = loadedModels  || {};
        
        // handle single model request (simple and different callback argument)
        modelName = (modelNamesToLoad.length===1) ? modelNamesToLoad[0] : modelNamesToLoad;
        if(Ext.isString(modelName)) {
            
            if(this.modelMetaDataIsLoaded(modelName)) {
                // all metadata already present, call callback with model
                callback.call(scope,this.getModel(modelName));
            } else {
                this.preloadModelMetaData(modelName, function() {
                    // all metadata already present, call callback with model
                    callback.call(scope,this.getModel(modelName));
                });
            }
            return;
        }
        
        if(!Ext.isArray(modelNamesToLoad)) {
            // IFDEBUG
            Ext.Error.raise({
                plugin: 'Bancha',
                modelNamesToLoad: modelNamesToLoad,
                msg: 'Bancha: The Bancha.onModelReady(modelNamesToLoad) expects either a string with one model oder an array of models, instead got'+modelNamesToLoad+' of type '+(typeof modelNamesToLoad)
            });
            // ENDIF
            return false;
        }
        
        // iterate trought the models to load
        Ext.forEach(modelNamesToLoad, function(modelName) {
            if(this.modelMetaDataIsLoaded(modelName)) {
                loadedModels[modelName] = this.getModel(modelName);
            } else {
                modelsToLoad.push(modelName);
            }
        });
        
        // iterate trought the loading models
        Ext.each(loadingModels, function(modelName) {
            if(this.modelMetaDataIsLoaded(modelName)) {
                // that was the model which triggered the function, so we are finished here
                loadedModels[modelName] = this.getModel(modelName);
                return false; // stop
            }
        });
        
        if(modelsToLoad.length===0) {
            // all metadata already present, call callback
            callback.call(scope,loadedModels);
        } else {
            // add all elements to the queue
            Ext.forEach(modelsToLoad, function(modelName) {
                // TODO OPTIMIZE not very performat for large arrrays
                this.preloadModelMetaData(modelName, function() {
                    // when model is loaded try again
                    this.onInitializedOnModelReady([], loadingModels, loadedModels, callback, scope);
                });
            }, this);
        }
    },
    /**
     * Get the metadata of an model
     * @private
     * @param {String} modelName the name of the model
     * @return {Object|null} Returns an objects with all metadata or null if not loaded yet.
     */
    getModelMetaData: function(modelName) {
        // IFDEBUG
        if(!this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Please inistalize Bancha before using it\'s getModelMetaData() method.'
            });
        }
        
        if(!Ext.isObject(this.getRemoteApi())) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha:The Bancha.remoteApi is configured wrong, this should be automatically refer to the REMOTE_API object, maybe due a misconfigured server.'
            });
        }
        if(!Ext.isObject(this.getRemoteApi().metadata)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: The server served a REMOTE_API object without a metadata property, maybe due a misconfigured server or a non-banacha backend system.'
            });
        }
        // ENDIF
        
        if(this.modelMetaDataIsLoaded(modelName)) {
            // metadata found, clone it to prevent unobvious errors (ext always uses the config object and modifies it
            return Ext.clone(
                this.getRemoteApi().metadata[modelName]);
            
            /* TODO should return metadata like this: {
                fields: [
                    // TODO loaded from server
                    // types: string, int, float, boolean, data
                    // {name:'name', type:'string', defaultValue:'', persist:false(for generated values), } // additionally isDouble? + text?
                ],
                validations: [
                    // TODO loaded from server
                    // {type:'length', name:'name', min:2}
                ]
                // TODO loaded from server
                associations: [
                    // TODO loaded from server
                    //{type:'hasMany', model:'Post', name:'posts'},
                ],
                sorters: [{ // TODO sort dir in cake bestimmen
                    property: 'name', // TODO name?
                    direction: 'ASC'
                }]
            }*/
        }
        
        // nothing found
        return null;
    },
    
    
    
    /**
     * This method creates a Bancha.data.Model with your additional model configs, 
     * if you don't have any additional configs just use the convienient method getModel
     * 
     * In the debug version it will raise an {@link #Ext.Error} if the model can't be 
     * or is already created, in production it will only return false.
     * 
     * @param {String} modelName the name of the model
     * @param {Object} modelConfig The config object, see Banacha.data.Model
     * @return {Boolean} Returns true is model was created successfully
     * @member Bancha
     * @method createModel
     */
    createModel: function(modelName, modelConfig) {
        var metaData,
            defaults,
            // IFDEBUG
            safeDirectFn,
            // ENDIF
            stub,
            idProperty;
        
        // IFDEBUG
        if(!this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha is not yet initalized, please init before using Bancha.createModel().'
            });
        }
        // ENDIF
        
        if(!this.isRemoteModel(modelName)) {
            // IFDEBUG
            Ext.Error.raise({
                plugin: 'Bancha',
                modelName: modelName,
                modelConfig: modelConfig,
                msg: 'Bancha: Couldn\'t create the model cause the model is not supported by the server.'
            });
            // ENDIF
            return false;
        }
        
        if(!this.modelMetaDataIsLoaded(modelName)) {
            // IFDEBUG
            Ext.Error.raise({
                plugin: 'Bancha',
                modelName: modelName,
                modelConfig: modelConfig,
                msg: 'Bancha: Couldn\'t create the model cause the metadata is not loaded yet, please use onModelReady instead.'
            });
            // ENDIF
            return false;
        }
        
        if(Ext.ClassManager.isCreated(modelName)) {
            // IFDEBUG
            Ext.Error.raise({
                plugin: 'Bancha',
                modelName: modelName,
                modelConfig: modelConfig,
                msg: 'Bancha: The model class '+modelName+' is already defined.'
            });
            // ENDIF
            return false;
        }
        
        // IFDEBUG
        if(!Ext.isDefined(this.getModelMetaData(modelName).idProperty)) {
            if(window.console && Ext.isFunction(console.warn)) {
                window.console.warn(
                    'Bancha: The model meta data for '+modelName+' seems strange, probably this was '+
                    'not created by Bancha, or an error occured on the server-side. Please notice '+
                    'that this warning is only created in debug mode.');
            }
        }
        // ENDIF
        
        // IFDEBUG
        safeDirectFn = function(stub,method,modelName) {
            if(Ext.isDefined(stub[method])) {
                return stub[method];
            }
            
            // function doesn't exit, create fake which will throw an error on first use
            var fakeFn = function() {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: 'Bancha: Tried to call '+modelName+'.'+method+'(...), but the server-side has not implemented '+modelName+'Controller->'+ map[method]+'(...).'
                });
            };
            
            // this is not part of the official Ext API!, but it seems to be necessary to do this for better bancha debugging
            fakeFn.directCfg = Ext.define("Ext.direct.RemotingMethod", {
                len: 1,
                name: method,
                formHandler: false
            });
            
            return fakeFn;
        }; 
        // ENDIF
        
        // TODO klappt das jetzt? http://www.sencha.com/forum/showthread.php?134505-Model-proxy-for-a-Store-doesn-t-seem-to-work-if-the-proxy-is-a-direct-proxy&p=606283&viewfull=1#post606283
    
        // create the metadata
        modelConfig = modelConfig || {};
        stub = this.getStubsNamespace()[modelName];
        idProperty = this.getModelMetaData(modelName).idProperty;
        defaults = {
            extend: 'Bancha.data.Model',
            idProperty: idProperty,
            proxy: {
                type: 'direct',
                api: {
                    /* IFPRODUCTION
                    // if method is not supported by remote it get's set to undefined
                    // TODO mapping auf server-side?
                    read    : stub.read,
                    create  : stub.create,
                    update  : stub.update,
                    destroy : stub.destroy
                    ENDIF */
                    // IFDEBUG
                    read    : safeDirectFn(stub,'read',modelName),
                    create  : safeDirectFn(stub,'create',modelName),
                    update  : safeDirectFn(stub,'update',modelName),
                    destroy : safeDirectFn(stub,'destroy',modelName)
                    // ENDIF
                },
                // because of an error in ext the following directFn def. has to be 
                // defined, which should be read from api.read instead...
                /* IFPRODUCTION
                directFn: stub.read,
                ENDIF */
                // IFDEBUG
                directFn: safeDirectFn(stub,'read',modelName), // TODO ticket erstellen
                // ENDIF
                reader: { // TODO does this work?
                    type: 'json',
                    root: 'data',
                    idProperty: idProperty
                },
                cacheString: '_dc' // TODO later
            }
        };
        metaData = Ext.clone(this.getModelMetaData(modelName));
        modelConfig = Ext.apply(metaData, modelConfig, defaults);
        
        // create the model
        Ext.define(modelName, modelConfig);
        return true;
    },
    /**
     * Get a bancha model by name
     * if it isn't already defined this function will define the model.
     * 
     * In the debug version it will raise an {@link #Ext.Error} if the model can't be created,
     * in production it will just return null.
     * @param {String} modelName the name of the model
     * @return {Ext.data.Model|null} Returns the model or null if this model doesn't exist or the metadata is not loaded
     * @member Bancha
     * @method getModel
     */
    getModel: function(modelName) {
        return (Ext.ClassManager.isCreated(modelName) || this.createModel(modelName)) ? Ext.ClassManager.get(modelName) : null;
    },
    
    /**
     * scarfolding functions for Bancha, mostly for rapid prototyping
     */
    scarfold: {
        /**
         * some scarfolding util function
         */
        util: {
            /**
             * make the first letter of an String upper case
             * @param {String} str
             * @return {String} str with first letter upper case
             * @member Bancha.scarfold.util
             * @method toFirstUpper
             * @static
             */
            toFirstUpper: function(str) {
                if(typeof str!=='string') {
                    return str;
                }
                if(str.length===1) {
                    return str.toUpperCase();
                } else {
                    return str[0].toUpperCase()+str.substr(1);
                }
            },
            /**
             * Capitalizes the first word and turns underscores into spaces and strips a trailing “_id”, if any.
             * Also it converts camel case by finding upper case letters right after lower case and repalceing the upper case with an space and lower case.
             * examples:
             * "user_name"  -> "User name"
             * "userName"   -> "User name"
             * "John Smith" -> "John Smith"
             *
             * @param {String} str
             * @return {String} str transformed string
             * @member Bancha.scarfold.util
             * @method humanize
             * @static
             */
            humanize: function(str) {
                str = str.replace(/_id/g,''); // delete _id from the string
                str = str.replace(/_/g,' '); // _ to spaces
                str = str.replace(/([a-z])([A-Z])/g, function(all,first,second) { return first + " " + second.toLowerCase(); }); // convert camel case (only)
                return this.toFirstUpper(str);
            }
        },
        
        /**
         * Maps column types and field types for prototyping
         * @property fieldToColumnTypes
         * @static
         * @type Object
         */
        fieldToColumnTypes: {
            'string'  : 'gridcolumn',
            'int'     : 'numbercolumn',
            'float'   : 'numbercolumn',
            'boolean' : 'booleancolumn',
            'date'    : 'datecolumn'
        },
        /**
         * Maps form field configs and field types for prototyping
         * @property fieldToFormFieldConfigs
         * @static
         * @type Object
         */
        fieldToFormFieldConfigs: {
            'string'  : {xtype:'textfield'},
            'int'     : {xtype:'numberfield', decimalPrecision:0},
            'float'   : {xtype:'numberfield'},
            'boolean' : {xtype:'checkboxfield'},
            'date'    : {xtype:'datefield'}
            // TODO OPTIMIZE add type upload field?
        },
        /**
         * function scarfolding for Ext.grid.Panels on-functions
         */
        gridFunction: {
            onCreate: function() { // scope is a config object
                var edit = this.cellEditing,
                    store = this.store,
                    model = store.getProxy().getModel(),
                    rec;
                
                // Cancel any active editing.
                edit.cancelEdit();
                 
                // create new entry
                rec = Ext.ClassManager.create(Ext.ClassManager.getName(model),rec);

                // add entry
                store.insert(0, rec);
                edit.startEditByPosition({
                    row: 0,
                    column: 0
                });
            },
            onSave: function() { // scope is the store
                var valid = true,
                    store = this,
                    changes = this.getUpdatedRecords();
                
                // check all changes
                Ext.forEach(changes,function(el) {
                    if(el.isValid()) {
                        valid = false;
                    }
                });
                
                if(valid) {
                    Ext.alert("One entry is not valid.");
                } else {
                    store.sync();
                }
            },
            onReset: function() { // scope is the store
                var changes = this.getUpdatedRecords();
                
                // reject all changes
                Ext.forEach(changes,function(el) {
                    el.reject();
                });
            },
            onDelete: function(grid, rowIndex, colIndex) {
                var rec = grid.getStore().getAt(rowIndex);
                rec.destroy({
                    success: function() {
                        Ext.alert('The User was destroyed!');
                    }
                });
            }
        },
        /**
         * Creates a Ext.form.Field config form an field type
         * @param {Sring} type the fields type
         * @method buildFormFieldConfig
         * @static
         * @type Object
         */
        buildFormFieldConfig: function(type) {
            return Ext.clone(this.fieldToFormFieldConfigs[type]);
        },
        /**
         * Builds grid config from the metadata, for scarfolding purposes
         * @param {Ext.data.Model|String} model the model class or model name
         * @param {Object} config optional an config object with:
         *                 - {Boolean} <i>create</i> true to add create button
         *                 - {Boolean} <i>update</i> true to allow changes
         *                 - {Boolean} <i>withReset</i> when updatable, true display a reset button as well
          *                - {Boolean} <i>destroy</i> true to add delete buttons
           *               - {Boolean} <i>autoLoad</i> false to don't set autoLoad on the store (default: true)
         * @param {Object} modelConfig some additional model configs which are applied to the config
          * @return {Object} an Ext.grid.Panel configuration object
         * @member Bancha.scarfold
         * @method buildGridPanelConfig
         * @property
         * @static
         */
        buildGridPanelConfig: function(model,config,modelConfig) {
            var gridConfig, modelName, buttons, cellEditing, store;
            config = config || {};
            
            // define model and modelName
            if(Ext.isString(model)) {
                modelName = model;
                model = Ext.ClassManager.get(modelName);
            } else {
                modelName = Ext.getClassName(model);
            }
            
            // basic config
            store = new Ext.data.Store({
                model: modelName,
                autoLoad: (config.autoLoad===false) ? false : true
            });
            
            gridConfig = {
                store: store,
                columns: this.buildColumns(model)
            };
            
            // add update configs
            if(config.update) {
                cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
                    clicksToEdit: 2
                });
                Ext.apply(gridConfig, {
                    selType: 'cellmodel',
                    plugins: [cellEditing]
                });
            }
            
            // add buttons
            if(config.create || config.update) {
                buttons = ['->'];
                
                if(config.create) {
                    buttons.push({
                        iconCls: 'icon-add',
                        text: 'Create',
                        scope: {
                            cellEditing: cellEditing,
                            store      : store
                        },
                        handler: this.gridFunction.onCreate
                    });
                }
                
                if(config.update) {
                    buttons.push({
                        iconCls: 'icon-save',
                        text: 'Save', //TODO OPTIMIZE disabled:true?
                        scope: store,
                        handler: this.gridFunction.onSave
                    });
                    if(config.withReset) {
                        buttons.push({
                            iconCls: 'icon-reset',
                            text: 'Reset',
                            scope: store,
                            handler: this.gridFunction.onReset
                        });
                    }
                }
                
                gridConfig.dockedItems = [{
                    xtype: 'toolbar',
                    dock: 'bottom',
                    ui: 'footer',
                    items: buttons
                }];
            }
            
            // apply user configs
            if(Ext.isObject(modelConfig)) {
                Ext.apply(config,modelConfig);
            }
            
            return gridConfig;
        },
        /**
         * Builds grid columns from the metadata, for scarfolding purposes
         * Please use buildGridPanelConfig function if you want support for 
         * create,update and/or delete!
         * 
         * @param {Ext.data.Model|String} model the model class or model name
         * @param {Object} config optional an config object with:
         *                 - {Boolean} <i>create</i> true to add create button
         *                 - {Boolean} <i>update</i> true to allow changes
         *                 - {Boolean} <i>destroy</i> true to add delete buttons
          * @return {Array} array of Ext.grid.Column configs
         * @member Bancha.scarfold
         * @method buildColumns
         * @property
         * @static
         */
        buildColumns: function(model, config) {
            var columns = [],
                scarfold;
            config = config || {};
            
            
            // IFDEBUG
            if(!Ext.isDefined(model)) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: 'Bancha: Bancha.scarfold.buildColumns() expected the model or model name as first argument, instead got undefined'
                });
            }
            // ENDIF

            if(Ext.isString(model)) {
                // IFDEBUG
                if(!Ext.isDefined(Ext.ModelManager.getModel(model))) {
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        model: model,
                        msg: 'Bancha: First argument for Bancha.scarfold.buildColumns() is the string "'+model+'", which  is not a valid model class name. Please define a model first (see Bancha.getModel() and Bancha.createModel())'
                    });
                }
                // ENDIF
                model = Ext.ModelManager.getModel(model);
            }
            
            scarfold = this;
            model.prototype.fields.each(function(field) {
                columns.push({
                    text     : scarfold.util.humanize(field.name),
                    dataIndex: field.name,
                    xtype: scarfold.fieldToColumnTypes[field.type.type],
                    editor: (config.update) ? scarfold.buildFormFieldConfig(field.type.type) : undefined
                });
            });
            
            if(config.destroy) {
                columns.push({
                    xtype:'actioncolumn', 
                    width:50,
                    items: [{
                        icon: 'images/delete.png',
                        tooltip: 'Delete',
                        handler: this.gridFunction.onDelete
                    }]
                });
            }
    
            return columns;
        }
    }
});

// eof
