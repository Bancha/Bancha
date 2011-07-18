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
/*jslint browser: true, vars: false, plusplus: true, white: true, sloppy: true */
/*global Ext, Bancha, window */

// TODO Native support for Ext.data.TreeStore with server-side TreeBehaviour
// TODO Form Support: http://dev.sencha.com/deploy/ext-4.0.0/examples/direct/direct-form.html
// TODO serverside form validation
// TODO selectboxes with serverside content (enum support also(?)) http://dev.sencha.com/deploy/ext-4.0.0/examples/form/combos.html
// TODO samples sollten "$javascript->link('script.js', false);" verwenden, da dies auch aus vendors (und plugins!?) ausliest


/**
 * @class Bancha.data.Model
 * @extends Ext.data.Model
 * 
 * This should only be used by Bancha internally, 
 * since it just has an additional flag to force consistency in Bancha.
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.Model', {
    extend: 'Ext.data.Model',
    // TODO forceConsistency: use the consistent model
    /**
     * @cfg
     * If true the frontend forces consistency
     */
    forceConsistency: false
});



/**
 * @class Bancha
 * 
 * This singleton is the core of Bancha on the client-side.  
 * For documentation on how to use it please look at the docs at banchaproject.org  
 * 
 * example usage:
 *     <!-- include Bancha and the remote API -->
 *     <script type="text/javascript" src="path/to/cakephp/Bancha/api.js"></script>
 *     <script type="text/javascript" src="path/to/bancha-debug.js"></script>
 *     <script>
 *         // when Bancha is ready, the model meta data is loaded
 *         // from the server and the model is created....
 *         Bancha.onModelReady('User', function(userModel) {
 *             // ... create a full featured users grid
 *             Ext.create('Ext.grid.Panel', 
 *                 Bancha.scarfold.buildGridPanelConfig('User', { //TODO grid functions richten
 *                     create: true,
 *                     update: true,
 *                     withReset: true,
 *                     destroy: true
 *                 }, {
 *                     height: 350,
 *                     width: 650,
 *                     frame: true,
 *                     title: 'User Grid',
 *                     renderTo: 'gridpanel'
 *                 })
 *             );
 *         }); //eo onModelReady
 *     </script>
 *   
 * If you want to listen for exceptions, please do this directly on {@link Ext.direct.Manager}
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
     * @property
     * Bancha Project version
     */
    version: '0.0.1',
    /**
     * @property
     * The local path to the Bancha remote api (Default: 'Bancha.REMOTE_API')  
     * Only change this if you changed 'Bancha.remote_api' in the CakePHP config, never change after {@link Ext#init}
     */
    remoteApi: 'Bancha.REMOTE_API',
    /**
     * @property
     * The path of the RCP for loading model meta data from the server, without the namespace. (Default: 'Bancha.loadMetaData')  
     * Only change this if you renamed the server-side BanchaController or it's method
     */
    metaDataLoadFunction: 'Bancha.loadMetaData',
    /**
     * @property
     * the namespace of Ext.Direct stubs, will be loaded from the REMOTE_API configuration on {@link Ext#init}  
     * null means no namespace, this is not recommanded. The namespace can be set in CakePHP: Configure:write('Bancha.namespace','Bancha.RemoteStubs'); 
     */
    namespace: null,
    /**
     * @private
     * @property
     * indicates that Bancha is initialized. Used for debugging.
     */
    initialized: false,
    /**
     * @private
     * Safely finds an object, used internally for getStubsNamespace and getRemoteApi
     * (This function is tested in RS.util, not part of the package testing, but it is tested)
     * @param {String} path A period ('.') separated path to the desired object (String).
     * @param {String} lookIn optional: The object on which to perform the lookup.
     * @return {Object} The object if found, otherwise undefined.
     */
    objectFromPath: function (path, lookIn) {
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
     * @private
     * Returns the namespace of the remote stubs
     * @return {Object} The namespace if already instanciated, otherwise undefined
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
     * @private
     * Returns the remote api definition of Ext.direct
     * @return {Object} The api if already defined, otherwise undefined
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
     * Inits Bancha with the RemotingProvider, always init before using Bancha
     * (when you use {@link Bancha#onReady} init is done automatically by Bancha)
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
        
        
        // if the server didn't send an metadata object in the api, create it
        if(!Ext.isDefined(remoteApi.metadata)) {
            remoteApi.metadata = {};
        }
        
        
        // IFDEBUG
        if(Ext.isObject(remoteApi)===false) {
            Ext.Error.raise({
                plugin: 'Bancha',
                remoteApi: this.remoteApi,
                msg: 'Bancha: The Bancha.remoteApi config seems to be configured wrong. (see also CakePHPs Configure:write(\'Bancha.remote_api\'Bancha.REMOTE_API\');'
            });
        }
        // ENDIF
        
        this.namespace = remoteApi.namespace || null;
        
        // init Provider
        Ext.direct.Manager.addProvider(remoteApi);
        
        this.initialized = true;
    },
    
    /**
     * Preloads the models metadata from the server to create a new model.  
     *  
     * When to use it: You should use this function is you don't want to load 
     * the metadata at startup, but want to load it before it is (eventually) 
     * used to have a more reactive interface.  
     * 
     * Attention: In most cases it's best to load all model metadata on startup
     * when the api is loaded, please install guide for more information. This
     * is mostly usefull if you can guess that a user will need a model soon
     * which wasn't loaded at startup or if you want to load all not at startup
     * needed models right after startup with something like: 
     *     Ext.onReady(
     *         Ext.Function.createDelayed(
     *             function() { Bancha.preloadModelMetaData('all'); },
     *             100
     *         )
     *     );
     *
     * @param {Array|String} models An array of the models to preload, one model name or 'all'
     * @param {Function} models callback function (optional)
     * @param {Object} models scope of the callback function (optional)
     */
    preloadModelMetaData: function(modelNames,callback,scope) {
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
        Ext.each(modelNames, function(modelName) {
            if(Ext.isObject(Bancha.getModelMetaData(modelName))) {
                if(window.console && Ext.isFunction(window.console.warn)) {
                    window.console.warn(
                        'Bancha: The model '+modelName+' is already loaded, we will reload the meta data '+
                        'but this seems strange. Notice that this warning is only created in debug mode.');
                }
            }
        });
        // ENDIF
        
        // force models to be an array
        if(Ext.isString(modelNames)) {
            modelNames = [modelNames];
        }
        
        cb = function(result, event) {
            var data = Ext.decode(result);
            
            // IFDEBUG
            if(data===null) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    result: result,
                    event: event,
                    msg: 'Bancha: The Bancha.preloadModelMetaData('+modelNames.toString()+') did expect to to get the metadata from the server, instead got: '+result
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
        fn(modelNames,cb,Bancha);
    },
    /**
     * Checks if the model is supported by the server
     * @param {String} modelName the name of the model
     * @return {Boolean} True is the model is remotable
     */
    isRemoteModel: function(modelName) {
        return (
                Ext.isObject(this.getStubsNamespace()) && 
                Ext.isObject(this.getStubsNamespace()[modelName])
                ) ? true : false;
    },
    
    /**
     * Checks if the metadata of a model is loaded
     * @param {String} modelName the name of the model
     * @return {Boolean} True is the metadata is present
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
     * If Bancha is not initialized already it will wait for {@link Ext#isReady}
     * and calls {@link Ext#init} before model creation.
     * @param {String|Array} modelNames a name of the model or an array of model names
     * @param {Function} callback (optional) callback function, the first argument is:
     * - a model class if input modelNames was an string
     * - an object with model names as keys and models as arguments if an array was given
     * @param {Object} scope scope of the callback function (optional)
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
     * @private
     * Helper, Bancha.onModelReady will call this function in an Ext.onReady
     * !!!don't use this function directly!!!
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
                msg: 'Bancha: The Bancha.onModelReady(modelNamesToLoad) expects either a string with one model oder an array of models, instead got'+modelNamesToLoad.toString()+' of type '+(typeof modelNamesToLoad)
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
                // TODO OPTIMIZE not very performant for large arrrays
                this.preloadModelMetaData(modelName, function() {
                    // when model is loaded try again
                    this.onInitializedOnModelReady([], loadingModels, loadedModels, callback, scope);
                });
            }, this);
        }
    },
    /**
     * @private
     * Get the metadata of an model
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
        }
        
        // nothing found
        return null;
    },
    
    
    
    /**
     * This method creates a {@link Bancha.data.Model} with your additional model configs, 
     * if you don't have any additional configs just use the convienient method getModel.  
     * 
     * In the debug version it will raise an {@link Ext.Error} if the model can't be 
     * or is already created, in production it will only return false.
     * 
     * @param {String} modelName the name of the model
     * @param {Object} modelConfig The config object, see Banacha.data.Model
     * @return {Boolean} Returns true is model was created successfully
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
            if(window.console && Ext.isFunction(window.console.warn)) {
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
            var map = {
                    create : 'add',
                    read   : 'view or index',
                    update : 'edit',
                    destroy: 'delete'
                },
                fakeFn = function() {
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
            // fake the execution method
            fakeFn.directCfg.method = function() {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: 'Bancha: Tried to call '+modelName+'.'+method+'(...), but the server-side has not implemented '+modelName+'Controller->'+ map[method]+'(...).'
                });
            };
            
            return fakeFn;
        }; 
        // ENDIF
        
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
                // see http://www.sencha.com/forum/showthread.php?134505-Model-proxy-for-a-Store-doesn-t-seem-to-work-if-the-proxy-is-a-direct-proxy&p=606283&viewfull=1#post606283
                /* IFPRODUCTION
                directFn: stub.read,
                ENDIF */
                // IFDEBUG
                directFn: safeDirectFn(stub,'read',modelName),
                // ENDIF
                reader: {
                    type: 'json',
                    root: 'data'
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
     * Get a bancha model by name.  
     * If it isn't already defined this function will define the model.
     * 
     * In the debug version it will raise an {@link Ext.Error} if the model can't be created,
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
     * scaffolding functions for Bancha, mostly for rapid prototyping
     */
    scaffold: {
        /**
         * @private
         * some scaffolding util function
         */
        util: {
            /**
             * @private
             * make the first letter of an String upper case
             * @param {String} str
             * @return {String} str with first letter upper case
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
             * @private
             * Capitalizes the first word and turns underscores into spaces and strips a trailing “_id”, if any.  
             * Also it converts camel case by finding upper case letters right after lower case and repalceing the upper case with an space and lower case.
             * examples:
             * "user_name"  -> "User name"
             * "userName"   -> "User name"
             * "John Smith" -> "John Smith"
             *
             * @param {String} str
             * @return {String} str transformed string
             */
            humanize: function(str) {
                str = str.replace(/_id/g,''); // delete _id from the string
                str = str.replace(/_/g,' '); // _ to spaces
                str = str.replace(/([a-z])([A-Z])/g, function(all,first,second) { return first + " " + second.toLowerCase(); }); // convert camel case (only)
                return this.toFirstUpper(str);
            }
        },
        
        /**
         * @private
         * @property
         * Maps column types and field types for prototyping
         */
        fieldToColumnConfigs: {
            'string'  : {xtype:'gridcolumn'},
            'int'     : {xtype:'numbercolumn',format:'0'},
            'float'   : {xtype:'numbercolumn'},
            'boolean' : {xtype:'booleancolumn'},
            'date'    : {xtype:'datecolumn'}
        },
        /**
         * @private
         * @property
         * Maps form field configs and field types for prototyping
         */
        fieldToFormFieldConfigs: {
            'string'  : {xtype:'textfield'},
            'int'     : {xtype:'numberfield', decimalPrecision:0},
            'float'   : {xtype:'numberfield'},
            'boolean' : {xtype:'checkboxfield'},
            'date'    : {xtype:'datefield'}
            // TODO add type upload field in backend and test
            // 'file'    : {xtype: 'filefield',buttonText: 'Select File...'}
        },
        /**
         * function scaffolding for {@link Ext.grid.Panel}s on-functions
         * To change the default scaffolding behaviour just replace the functions.  
         * You can do this at any time, the current declarations are always used
         */
        gridFunction: {
            /**
              * @private
               * @property
              * This enables the developer to change the default functions at any time
              * and Bancha will always use the current functions, since there are no references
              */
            createFacade: function(method) {
                // IFDEBUG
                /*
                 * totally stupid, but we need a singleton pattern in debug mode here, since
                 * jasmine provides us only with VERY little compare options
                 */
                this.singletonFns = this.singletonFns || {};
                this.singletonFns[method] = this.singletonFns[method] || function() {
                    return this[method].apply(this,arguments);
                };
                return this.singletonFns[method];
                // ENDIF
                
                /* IFPRODUCTION
                return function() {
                    return this[method].apply(this,arguments);
                };
                ENDIF */
            },
            /**
             * @property
             * Editable function to be called when the create button is pressed  
             * scope is an object: {  
             *  store:       the grids store  
             *  cellEditing: the grids cell editing plugin  
             * }
             */
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
            /**
             * @property
             * Editable function to be called when the save button is pressed
             * scope is the store
             */
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
            /**
             * @property
             * Editable function to be called when the reset button is pressed  
             * scope is the store
             */
            onReset: function() { // scope is the store
                var changes = this.getUpdatedRecords();
                
                // reject all changes
                Ext.forEach(changes,function(el) {
                    el.reject();
                });
            },
            /**
             * @property
             * Editable function to be called when the delete button is pressed
             */
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
          * @private
         * Creates a {@link Ext.form.Field} config form an field type
         * @param {Sring} type the fields type
         */
        buildFormFieldConfig: function(type) {
            return Ext.clone(this.fieldToFormFieldConfigs[type]);
        },
        /**
         * Builds a grid config from the metadata, for scaffolding purposes.
         * @param {Ext.data.Model|String} model the model class or model name
         * @param {Object} config (optional) config object with:  
         *  - {Boolean} _create_ true to add create button  
         *  - {Boolean} _update_ true to allow changes  
         *  - {Boolean} _withReset_ when updatable, true display a reset button as well  
         *  - {Boolean} _destroy_ true to add delete buttons 
         *  - {Boolean} _autoLoad_ false to don't set autoLoad on the store (default: true)
         * @param {Object} additionalGridConfig some additional configs which are applied to the config
         * @return {Object} an Ext.grid.Panel configuration object
         */
        buildGridPanelConfig: function(model,config,additionalGridConfig) {
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
            store = Ext.create("Ext.data.Store",{
                model: modelName,
                autoLoad: (config.autoLoad===false) ? false : true
            });
            
            gridConfig = {
                store: store,
                columns: this.buildColumns(model,config)
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
                        handler: this.gridFunction.createFacade('onCreate')
                    });
                }
                
                if(config.update) {
                    buttons.push({
                        iconCls: 'icon-save',
                        text: 'Save', //TODO OPTIMIZE disabled:true?
                        scope: store,
                        handler: this.gridFunction.createFacade('onSave')
                    });
                    if(config.withReset) {
                        buttons.push({
                            iconCls: 'icon-reset',
                            text: 'Reset',
                            scope: store,
                            handler: this.gridFunction.createFacade('onReset')
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
            if(Ext.isObject(additionalGridConfig)) {
                gridConfig = Ext.apply(gridConfig,additionalGridConfig);
            }
            
            return gridConfig;
        },
        /**
         * Builds grid columns from the metadata, for scaffolding purposes.  
         * Please use buildGridPanelConfig function if you want support for 
         * create,update and/or delete!
         * 
         * @param {Ext.data.Model|String} model the model class or model name
         * @param {Object} config (optional) config object with:
         *  - {Boolean} _create_ true to add create button
         *  - {Boolean} _update_ true to allow changes
         *  - {Boolean} _destroy_ true to add delete buttons
         * @return {Array} array of Ext.grid.Column configs
         */
        buildColumns: function(model, config) {
            var columns = [],
                scaffold;
            config = config || {};
            
            
            // IFDEBUG
            if(!Ext.isDefined(model)) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: 'Bancha: Bancha.scaffold.buildColumns() expected the model or model name as first argument, instead got undefined'
                });
            }
            // ENDIF

            if(Ext.isString(model)) {
                // IFDEBUG
                if(!Ext.isDefined(Ext.ModelManager.getModel(model))) {
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        model: model,
                        msg: 'Bancha: First argument for Bancha.scaffold.buildColumns() is the string "'+model+'", which  is not a valid model class name. Please define a model first (see Bancha.getModel() and Bancha.createModel())'
                    });
                }
                // ENDIF
                model = Ext.ModelManager.getModel(model);
            }
            
            scaffold = this;
            model.prototype.fields.each(function(field) {
                columns.push(Ext.apply({
                    flex     : 1, // foreFit the columns
                    text     : scaffold.util.humanize(field.name),
                    dataIndex: field.name,
                    field: (config.update) ? scaffold.buildFormFieldConfig(field.type.type) : undefined
                },scaffold.fieldToColumnConfigs[field.type.type])); // add xtype
            });
            
            if(config.destroy) {
                columns.push({
                    xtype:'actioncolumn', 
                    width:50,
                    items: [{
                        icon: 'img/icons/delete.png',
                        tooltip: 'Delete',
                        handler: this.gridFunction.createFacade('onDelete')
                    }]
                });
            }
    
            return columns;
        }
    }
});

// eof
