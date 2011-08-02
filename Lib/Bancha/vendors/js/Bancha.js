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
// TODO serverside form validation
// TODO selectboxes with serverside content (enum support also(?)) http://dev.sencha.com/deploy/ext-4.0.0/examples/form/combos.html

Ext.require([
    'Ext.data.*',
    'Ext.form.Panel',
    'Ext.grid.Panel',
]);

/**
 * @class Bancha.data.Model
 * @extends Ext.data.Model
 * 
 * 
 * This should only be used by Bancha internally, 
 * since it just has an additional flag to force consistency in Bancha.
 * 
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.Model', {
    extend: 'Ext.data.Model',
    /**
     * @cfg
     * If true the frontend forces consistency
     */
    forceConsistency: false
});


/**
 * @private
 * This should only be used by Bancha internally, 
 * it adds the consistent uid to all requests.
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.writer.ConsistentJson', { // TODO testen
    extend: 'Ext.data.writer.Json',
    alias: 'writer.consistent',
    
    /**
     * @config
     * the name of the field to send the consistent uid in
     */
    uidProperty: '__bcid',
    
    /**
     * @config {Bancha.data.Model} model
     * the model to write for, needed to determine the value of 
     * {@link Bancha.data.Model#forceConsistency model.forceConsistency}
     */
    model: undefined,
    //inherit docs
    writeRecords: function(request, data) {
        
        // IFDEBUG
        if(!this.model) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha.data.writer.ConsistentJson needs a reference to the model.'
            });
        }
        // ENDIF
        
        // set consistent uid if expected
        if(this.model && this.model.forceConsistency) {
            if (this.encode) {
                request.params[this.uidProperty] = Bancha.getConsistentUid();
            } else {
                // send as jsonData
                request.jsonData[this.uidProperty] = Bancha.getConsistentUid();
            }
        }
        
        // let the json writer do all the work:
        return this.superclass.writeRecords.apply(this,arguments);
    }
});


/*
 * Add some validation function for scaffolding
 */
(function() {
    var filenameHasExtension = function(filename,validExtensions) {
        if(!Ext.isDefined(validExtensions)) {
            return true;
        }
        var ext = filename.split('.').pop();
        return Ext.Array.contains(validExtensions,ext);
    };
    /**
     * @class Ext.form.field.VTypes
     * Custom VTypes for scaffolding support
     * @author Roland Schuetz <mail@rolandschuetz.at>
     * @docauthor Roland Schuetz <mail@rolandschuetz.at>
     */
    Ext.apply(Ext.form.field.VTypes, {
        /**
         * @method
         * Validates that the file extension is of one of field.validExtensions
         * or if field.validExtensions is undefined
         */
        fileExtension: function(val, field) {
            return filenameHasExtension(val,field.validExtensions);
        },
        /**
         * @property
         * The error text to display when the file extension validation function returns false. Defaults to: 'This file type is not allowed.'
         */
        fileExtensionText: 'This file type is not allowed.',
        /**
         * @property
         * The keystroke filter mask to be applied on alpha input. Defaults to: /[\^\r\n]/
         */
        fileExtensionMask: /[\^\r\n]/ // alow everything except new lines
    });
    
    /**
     * @class Ext.data.validations
     * Custom validations for scaffolding support
     * @author Roland Schuetz <mail@rolandschuetz.at>
     * @docauthor Roland Schuetz <mail@rolandschuetz.at>
     */
    Ext.apply(Ext.data.validations,{
        /**
         * @property
         * The default error message used when a numberformat validation fails.
         */
        numberformatMessage: "is not a number or not in the allowed range",
        /**
         * @property
         * The default error message used when a file validation fails.
         */
        fileMessage: "is not a valid file",
        /**
         * @method
         * Validates that the number is in the range of min and max.
         * Precision is not validated, but it is used for differenting int from float,
         * also it's metadata for scaffolding.
         * For example:
         *     {type: 'numberformat', field: 'euro', precision:2, min:0, max: 1000}
         */
        numberformat: function(config, value) {
            if(typeof value !== 'number') {
                value = (config.precision===0) ? parseInt(value,10) : parseFloat(value);
                if(typeof value !== 'number') {
                    return false; // could not be converted to a number
                }
            }
            if((Ext.isDefined(config.min) && config.min > value) || (Ext.isDefined(config.max) && value > config.max)) {
                return false; // not in the range
            }
            return true;
        },
        /**
         * @method
         * Validates that the given filename is of the configured extension. Also validates
         * if no extension are defined.
         * For example:
         *     {type: 'file', field: 'avatar', extension:['jpg','jpeg','gif','png']}
         */
        file: function(config, value) {
            return filenameHasExtension(value,config.extension);
        }
    });
    
    
    /**
     * @class Ext.grid.Panel
     * The Ext.grid.Panel is extended for scaffolding. For an usage example see 
     * {@link Bancha.scaffold.Grid}
     * @author Roland Schuetz <mail@rolandschuetz.at>
     * @docauthor Roland Schuetz <mail@rolandschuetz.at>
     */
    Ext.apply(Ext.grid.Panel, {
        /**
         * @property {Ext.data.Model|String|False} scaffold
         * Define a Bancha model or model name to build the config from Bancha metadata.  
         * Guesses are made by model field configs and validation rules.
         *
         * See {@link Bancha.scaffold.Grid} for an example.
         */
        /**
         * @property {String|False} scaffold
         * If this panel was scaffolded this is the name of the used model, otherwise False.
         */
        scaffold: false,
        /**
         * @cfg {Boolean} enableCreate
         * If true and scaffold is defined, a create button will be added to all scaffolded grids.  
         * See class descrition on how the fields are created.  
         * If undefined, the default from {@link Bancha.scaffold.Grid} is used.
         */
        /**
         * @cfg {Boolean} enableUpdate
         * If true and scaffold is defined, a editor field is added to all columns for scaffolded grids.  
         * See {@Bancha.scaffold.Grid} on how the fields are created.  
         * If undefined, the default from {@link Bancha.scaffold.Grid} is used.
         */
        /**
         * @cfg {Boolean} enableDestroy
         * If true and scaffold is defined, a delete button is added to all rows for scaffolded grids.  
         * If undefined, the default from {@link Bancha.scaffold.Grid} is used.
         */
        /**
         * @cfg {Boolean} enableReset
         * If true and scaffold is defined, a reset button will be added to all scaffolded grids
         * (only if enableCreate or enableUpdate is true).  
         * If undefined, the default from {@link Bancha.scaffold.Grid} is used.
         */
        /**
         * @cfg {Object} scaffoldConfig
         * If scaffold is defined this config will be used for building the scaffold grid, any 
         * property from {@link Bancha.scaffold.Grid} can be defined here.
         */
        scaffoldConfig: {}
    });
    
    // add scaffolding support
    Ext.override(Ext.grid.Panel, {
        initComponent : function() {
            if(this.scaffold) {
                // push all basic configs in the scaffoldConfig
                if(Ext.isDefined(this.enableCreate))  { this.scaffoldConfig.enableCreate  = this.enableCreate; }
                if(Ext.isDefined(this.enableUpdate))  { this.scaffoldConfig.enableUpdate  = this.enableUpdate; }
                if(Ext.isDefined(this.enableDestroy)) { this.scaffoldConfig.enableDestroy = this.enableDestroy; }
                if(Ext.isDefined(this.enableReset))   { this.scaffoldConfig.enableReset   = this.enableReset; }
                // scaffold
                Ext.apply(this,
                    Bancha.scaffold.Grid.buildConfig(this.scaffold,this.scaffoldConfig,this));
            }
            // continue with standard behaviour
            this.callOverridden();
        }
    });
    
    /**
     * @class Ext.form.Panel
     * The Ext.form.Panel is extended for scaffolding. For an usage example see 
     * {@link Bancha.scaffold.Form}
     * @author Roland Schuetz <mail@rolandschuetz.at>
     * @docauthor Roland Schuetz <mail@rolandschuetz.at>
     */
    Ext.apply(Ext.form.Panel, {
        /**
         * @cfg {Ext.data.Model|String|False} scaffold
         * Define a Bancha model or model name to build the config from Bancha metadata.  
         * Guesses are made by model field configs and validation rules.
         *
         * See {@link Bancha.scaffold.Form} for an example.
         */
        /**
         * @property {String|False} scaffold
         * If this panel was scaffolded this is the name of the used model, otherwise False.
         */
        scaffold: false,
        /**
         * @cfg {Boolean} enableReset
         * If true and scaffold is defined, a reset button will be added to all scaffolded grids
         * (only if enableCreate or enableUpdate is true).  
         * If undefined, the default from {@link Bancha.scaffold.Form} is used.
         */
        enableReset: undefined,
        /**
         * @cfg {String|Number|False} banchaLoadRecord
         * Define a record id here to autolaod this record for editing in this form, or choose
         * false to create a new record onSave (if default onSave is used).
         * (Default: false)
         */
        banchaLoadRecord: false,
        /**
         * @cfg {Object} scaffoldConfig
         * If scaffold is defined this config will be used for building the scaffold form, any
         * property from {@link Bancha.scaffold.Form} can be overridded here.
         */
        scaffoldConfig: {}
    });
    
    // add scaffolding support
    Ext.override(Ext.form.Panel, {
        initComponent : function() {
            if(this.scaffold) {
                // push all basic configs in the scaffoldConfig
                if(Ext.isDefined(this.enableReset))   { this.scaffoldConfig.enableReset   = this.enableReset; }
                // scaffold
                Ext.apply(this,
                    Bancha.scaffold.Form.buildConfig(this.scaffold,this.banchaLoadRecord,this.scaffoldConfig,this));
            }
            // continue with standard behaviour
            this.callOverridden();
        }
    });
}());

/**
 * @class Bancha
 * 
 * This singleton is the core of Bancha on the client-side.  
 * For documentation on how to use it please look at the docs at banchaproject.org  
 * 
 * example usage:
 *     <!-- include Bancha and the remote API -->
 *     <script type="text/javascript" src="path/to/cakephp/Bancha/api.js"></script>
 *     <script type="text/javascript" src="path/to/bancha.js"></script>
 *     <script>
 *         // when Bancha is ready, the model meta data is loaded
 *         // from the server and the model is created....
 *         Bancha.onModelReady('User', function(userModel) {
 *             // ... create a full featured users grid
 *             Ext.create('Ext.grid.Panel',{
 *                 scaffold: 'User',
 *                 title: 'User Grid',
 *                 renderTo: 'gridpanel'
 *             );
 *         }); //eo onModelReady
 *     </script>
 *   
 * If you want to listen for exceptions, please do this directly on Ext.direct.Manager
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
     * Only change this if you changed 'Bancha.remote_api' in the CakePHP config, never change after {@link Bancha#init}
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
     * The namespace of Ext.Direct stubs, will be loaded from the REMOTE_API configuration on {@link Bancha#init}  
     * null means no namespace, this is not recommanded. The namespace can be set in CakePHP: Configure:write('Bancha.namespace','Bancha.RemoteStubs'); 
     */
    namespace: null,
    /**
     * @private
     * @property
     * Indicates that Bancha is initialized. Used for debugging.
     */
    initialized: false,
    /**
     * @private
     * Safely finds an object, used internally for getStubsNamespace and getRemoteApi
     * (This function is tested in RS.util, not part of the package testing, but it is tested)
     * @param {String} path A period ('.') separated path to the desired object (String).
     * @param {String} lookIn (optional) The object on which to perform the lookup.
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
     * Inits Bancha with the RemotingProvider, always init before using Bancha.  
     * ({@link Bancha#onModelReady} will init automatically)
     */
    init: function() {
        var remoteApi,
            regex;
        
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
        
        // since json doesn't support regex and json_encode fucks excaping up, transform bancha strings to real reggex
        regex = {
            Alpha: /^[a-zA-Z_]+$/,
            Alphanum: /^[a-zA-Z0-9_]+$/,
            Email: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/,
            Url: /(((^https?)|(^ftp)):\/\/([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)/i
        };
        Ext.Object.each(remoteApi.metadata, function(key,model) {
            Ext.Object.each(model.validations, function(key,rule) {
                if(rule.type==='format' && Ext.isString(rule.matcher) && rule.matcher.substr(0,6)==='bancha' && regex[rule.matcher.substr(6)]) {
                    rule.matcher = regex[rule.matcher.substr(6)];
                }
            });
        });
        
        
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
     * __When to use it:__ You should use this function if you don't want to load 
     * the metadata at startup, but want to load it before it is (eventually) 
     * used to have a more reactive interface.  
     * 
     * __Attention:__ In most cases it's best to load all model metadata on startup
     * when the api is loaded, please see the Bancha CakePHP install guide for more 
     * information. This is mostly usefull if you can guess that a user will need a 
     * model soon which wasn't loaded at startup or if you want to load all needed
     * models right after startup with something like: 
     *     Ext.onReady(
     *         Ext.Function.createDelayed(
     *             function() { Bancha.preloadModelMetaData(['User','Article','Post']); },
     *             100 // after 100ms the whole ui should be already ready
     *         )
     *     );
     *
     * @param {Array|String} models An array of the models to preload or a string with one model name
     * @param {Function} callback  (optional) A callback function
     * @param {Object} scope  (optional) The scope of the callback function
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
     * @param {String} modelName The name of the model
     * @return {Boolean} True is the model is remotable
     */
    isRemoteModel: function(modelName) {
        return (
                Ext.isObject(this.getStubsNamespace()) && 
                Ext.isObject(this.getStubsNamespace()[modelName])
                ) ? true : false;
    },
    
    /**
     * Checks if the metadata of a model is loaded.
     * @param {String} modelName The name of the model
     * @return {Boolean} True is the metadata is present for _modelName_
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
     * If Bancha is not already initialized it will wait for link Ext.isReady
     * and calls {@link Bancha#init} before model creation.  
     * 
     * See {@link Bancha Bancha class explaination} for an example.
     * @param {String|Array} modelNames A name of the model or an array of model names
     * @param {Function} callback (optional) A callback function, the first argument is:  
     *  - a model class if input modelNames was an string  
     *  - an object with model names as keys and models as arguments if an array was given
     * @param {Object} scope (optional) The scope of the callback function
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
        Ext.Array.forEach(modelNamesToLoad, function(modelName) {
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
            Ext.Array.forEach(modelsToLoad, function(modelName) {
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
     * @param {String} modelName The name of the model
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
     * @private
     * Returns the UID of this instance or false in an error case.
     * In debug mode it throws an error if no UID is defined.
     */
    getConsistentUid: function() {
        var api = this.getRemoteApi();
        // IFDEBUG
        if(!this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Please inistalize Bancha before using it\'s getConsistentUid() method.'
            });
        }
        
        if(!(
             Ext.isObject(api) && 
             Ext.isObject(api.metadata) &&
             Ext.isObject(api.metadata[_UID])
             )) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: There is no Bancha consistent model uid defined in the metadata. '+
                     'Maybe you use a non-Bancha backend or forgot to include the remote api on this site.'
            });
         }
         // ENDIF
         return (api && api.metadata && api.metadata[_UID]) ? api.metadata[_UID] : false;
    },
    /**
     * @property {Function|False} onRemoteException
     * This function will be added to each model to handle remote errors.
     * (modelConfig.listeners.exception).  
     * Use false to don't have exception handling on models.
     */
     onRemoteException: function(proxy, response, operation){
         Ext.MessageBox.show({
             title: 'REMOTE EXCEPTION',
             msg: operation.getError(),
             icon: Ext.MessageBox.ERROR,
             buttons: Ext.Msg.OK
         });
     },
    
    /**
     * This method creates a {@link Bancha.data.Model} with your additional model configs, 
     * if you don't have any additional configs just use the convienient method {@link #getModel}.  
     * 
     * In the debug version it will raise an Ext.Error if the model can't be 
     * or is already created, in production it will only return false.
     * 
     * @param {String} modelName The name of the model
     * @param {Object} modelConfig A standard Ext.data.Model config object
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
            if(Ext.isDefined(stub[method] && typeof stub[method] === 'function')) {
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
            fakeFn.directCfg = { // TODO testen
                len: 1,
                name: method,
                formHandler: false
            };
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
                writer: (modelConfig.forceConsistency) ? {
                    type: 'consitentjson',
                    writeAllFields: false,
                    root: 'data'
                } : {
                    type: 'json',
                    writeAllFields: false,
                    root: 'data'
                },
                listeners: {
                    exception: this.onRemoteException || Ext.emptyFn
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
     * In the debug version it will raise an Ext.Error if the model can't be created,
     * in production it will just return null.
     * @param {String} modelName The name of the model
     * @return {Ext.data.Model|null} Returns the model or null if this model doesn't exist or the metadata is not loaded
     * @member Bancha
     * @method getModel
     */
    getModel: function(modelName) {
        return (Ext.ClassManager.isCreated(modelName) || this.createModel(modelName)) ? Ext.ClassManager.get(modelName) : null;
    },
    
    /*
     * Scaffolding functions for Bancha, mostly for rapid prototyping
     */
    scaffold: {
        /**
         * @private
         * @singleton
         * @class Bancha.scaffold.Util
         * Some scaffolding util function
         * 
         * @author Roland Schuetz <mail@rolandschuetz.at>
         * @docauthor Roland Schuetz <mail@rolandschuetz.at>
         */
        Util: {
            /**
             * make the first letter of an String upper case
             * @param {String} str
             * @return {String} str with first letter upper case
             * @member Bancha.scaffold.Util
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
             * @member Bancha.scaffold.Util
             */
            humanize: function(str) {
                str = str.replace(/_id/g,''); // delete _id from the string
                str = str.replace(/_/g,' '); // _ to spaces
                str = str.replace(/([a-z])([A-Z])/g, function(all,first,second) { return first + " " + second.toLowerCase(); }); // convert camel case (only)
                return this.toFirstUpper(str);
            },
            /**
             * DEPRECATED - CURRENTLY NOT USED  
             * This enables the developer to change the default scaffolding functions at any time
             * and Bancha will always use the current functions, since there are no references
             * @member Bancha.scaffold.Util
             */
            createFacade: function(scopeName,scope,method) {
                // IFDEBUG
                /*
                 * totally stupid, but we need a singleton pattern in debug mode here, since
                 * jasmine provides us only with VERY little compare options
                 */
                this.singletonFns = this.singletonFns || {};
                this.singletonFns[scopeName] = this.singletonFns[scopeName] || {};
                this.singletonFns[scopeName][method] = this.singletonFns[scopeName][method] || function() {
                    return scope[method].apply(this,arguments);
                };
                return this.singletonFns[scopeName][method];
                // ENDIF

                /* IFPRODUCTION
                return function() {
                    return scope[method].apply(scope,arguments);
                };
                ENDIF */
            }
        },
        /**
         * @class Bancha.scaffold.Grid
         * @singleton
         * 
         * This class is a factory for creating Ext.grid.Panel's. It uses many data from
         * the given model, including field configs and validation rules. 
         * 
         * In most cases you will use our configurations on {@link Ext.grid.Panel}.
         * Here's an example usage:
         *     Ext.create("Ext.grid.Panel", {
         *         scaffold: 'User', // the model name
         *
         *         // basic scaffold configs can be set directly
         *         enableCreate : true,
         *         enableUpdate : true,
         *         enableReset  : true,
         *         enableDestroy: true,
         *     
         *         // advanced configs can be set here:
         *         scaffoldConfig: {
         *             columnDefaults: {
         *                 width: 200
         *             },
         *             datecolumnDefaults: {
         *                 format: 'm/d/Y'
         *             },
         *             // use the same store for all grids
         *             oneStorePerModel: true,
         *             // custom onSave function
         *             onSave: function() {
         *                 Ext.MessageBox.alert("Tada","You've pressed the save button");
         *             }
         *         },
         *     
         *         // and add some styling
         *         height   : 350,
         *         width    : 650,
         *         frame    : true,
         *         title    : 'User Grid',
         *         renderTo : 'gridpanel'
         *     });
         *    
         * If enableCreate or enableUpdate is true, this class will use 
         * {@link Bancha.scaffold.Form} to create the editor fields.
         *
         * You have three possible interceptors:  
         *  - beforeBuild      : executed before {@link #buildGrid}  
         *  - guessColumnConfig: executed after a column config is created, see {@link #guessColumnConfig}   
         *  - afterBuild       : executed after {@link #buildGrid} created the config
         * 
         * @author Roland Schuetz <mail@rolandschuetz.at>
         * @docauthor Roland Schuetz <mail@rolandschuetz.at>
         */
        Grid: {  
             /**
              * @private
              * DEPRECATED - CURRENTLY NOT USED  
              * Shorthand for {@llink Bancha.scaffold.Util#createFacade}
              */
             createFacade: function(method) {
                 return Bancha.scaffold.Util.createFacade('Grid',this,method);
             },
            /**
             * @private
             * @property
             * Maps model types with column types and additional configs for prototyping
             */
            fieldToColumnConfigs: {
                'string'  : {xtype:'gridcolumn'},
                'int'     : {xtype:'numbercolumn',format:'0'},
                'float'   : {xtype:'numbercolumn'},
                'boolean' : {xtype:'booleancolumn'},
                'date'    : {xtype:'datecolumn'}
            },
            /**
             * @property
             * This config is applied to each scaffolded column config
             */
            columnDefaults: { 
                flex: 1 // foreFit the columns to take the whole available space
            },
            /**
             * @property
             * This config is applied to each scaffolded Ext.grid.column.Grid
             */
            gridcolumnDefaults: {},
            /**
             * @property
             * This config is applied to each scaffolded Ext.grid.column.Number
             */
            numbercolumnDefaults: {},
            /**
             * @property
             * This config is applied to each scaffolded Ext.grid.column.Boolean
             */
            booleancolumnDefaults: {},
            /**
             * @property
             * This config is applied to each scaffolded Ext.grid.column.Column
             */
            datecolumnDefaults: {},
            /**
             * @property
             * The defaults class to create an store for grid scaffolding. (Default: "Ext.data.Store")
             */
            storeDefaultClass: "Ext.data.Store",
            /**
             * @property
             * Defaults for all grid stores created with this scaffolding.  
             * Default:
             *    { 
             *      autoLoad: true
             *    }
             */
            storeDefaults: { 
                autoLoad: true
            },
            /**
             * @property
             * True to use only one store per model (singleton), 
             * false to create a new store each time.
             */
            oneStorePerModel: true,
            /**
             * @private
             * for separation of concerns, gets/create a store for the grid
             */
            getStore: (function(model,config) {
                this.stores = {};
                var stores = this.stores;
                
                return function(model,config) {
                    var modelName = Ext.ClassManager.getName(model),
                        store;
                    if(config.oneStorePerModel && stores[modelName]) {
                        return stores[modelName];
                    }
                
                    store = Ext.create(config.storeDefaultClass,
                        Ext.apply({
                            model: modelName
                        },Ext.clone(config.storeDefaults))
                    );
                
                    if(config.oneStorePerModel) {
                        stores[modelName] = store;
                    }
                
                    return store;
                };
            }()),
            /**
             * @property {Function|False} guessFieldConfigs Writable function used to guess some default behaviour.
             * Can be set to false to don't guess at all.
             * Default function just hides id columns and makes it uneditable.
             * @param {Object} configs A column config
             * @param {String} modelType This is either a standard model field type like 'string' or our in Bancha added 'file'
             * @return {Object} Returns an Ext.grid.column.* configuration object
             */
            guessColumnConfigs: function(configs,modelType) {
                if(configs.dataIndex==='id') {
                    configs.hidden = true;
                    configs.field = undefined;
                }

                return configs;
            },
            /**
             * @private
             * Builds a column with all defaults defined here
             * @param {Sring} type The model field type
             * @param {Object} defaults (optional) Defaults like numbercolumnDefaults as property of this config. 
             * See {@link #buildConfig}'s config property
             * @return {Object} Returns an Ext.grid.column.* configuration object
             */
            buildDefaultColumnFromModelType: function(type,defaults) {
                defaults = defaults || {};
                var column = this.fieldToColumnConfigs[type],
                    columnDefaults     = Ext.clone(defaults.columnDefaults || this.columnDefaults), // make a new object of defaults
                    columnTypeDefaults = defaults[column.xtype+'Defaults'] || this[column.xtype+'Defaults'];
                return Ext.apply(columnDefaults,column,columnTypeDefaults); 
            },
            /**
             * @private
             * Creates a Ext.grid.Column config from an model field type
             * @param {Sring} type The model field type
             * @param {String} columnName (optional) The name of the column
             * @param {Object} defaults (optional) Defaults like numbercolumnDefaults as property of this config. 
             * See {@link #buildConfig}'s config property
             * @return {Object} Returns an Ext.grid.column.* configuration object
             */
            buildColumnConfig: function(type,columnName,defaults) { 
                defaults = defaults || {};
                var column = this.buildDefaultColumnFromModelType(type,defaults),
                    enableUpdate;

                // infer name
                if(columnName) {
                    column.text      = Bancha.scaffold.Util.humanize(columnName);
                    column.dataIndex = columnName;
                }
                
                // add an editor
                enableUpdate = (typeof defaults.enableUpdate !== 'undefined') ? defaults.enableUpdate : this.enableUpdate;
                if(enableUpdate) {
                    column.field = Bancha.scaffold.Form.buildFieldConfig(type,undefined,defaults.formConfig); // we don't need name definition in here
                }
                
                // now make some crazy guesses ;)
                if(typeof defaults.guessColumnConfigs === 'function') {
                    column = defaults.guessColumnConfigs(column,type);
                }

                return column;
            },
            /**
             * @property
             * Editable function to be called when the create button is pressed.  
             * To change the default scaffolding behaviour just replace this function.  
             *   
             * Default scope is following object:
             *     {  
             *      store:       the grids store  
             *      cellEditing: the grids cell editing plugin  
             *     }
             */
            onCreate: function() { // scope is a config object
                var edit = this.cellEditing,
                    grid = edit.grid,
                    store = this.store,
                    model = store.getProxy().getModel(),
                    rec,
                    visibleColumn = false;
                
                // Cancel any active editing.
                edit.cancelEdit();
                 
                // create new entry
                rec = Ext.create(Ext.ClassManager.getName(model),{});

                // add entry
                store.insert(0, rec);
                
                // find first visible column
                Ext.each(grid.columns,function(el,i) {
                    if(el.hidden !== true) {
                        visibleColumn = i;
                        return false;
                    }
                });
                
                // start editing
                if (visibleColumn) {
                    edit.startEditByPosition({
                        row: 0,
                        column: visibleColumn
                    });
                }
            },
            /**
             * @property
             * Editable function to be called when the save button is pressed.  
             * To change the default scaffolding behaviour just replace this function.  
             *   
             * Default scope is the store.
             */
            onSave: function() { // scope is the store
                var valid = true,
                    store = this;
                
                // check if all changes are valid
                // Ext.Array.forEach(changes,function(el) {
                store.each(function(el) {
                    if(!el.isValid()) {
                        valid = false;
                    }
                });
                
                if(!valid) {
                    Ext.MessageBox.show({
                        title: 'Invalid Data',
                        msg: 'At least one record is not valid, please make sure that all inputs are correct.',
                        icon: Ext.MessageBox.ERROR,
                        buttons: Ext.Msg.OK
                    });
                } else {
                    // commit create and update
                    store.sync();
                }
            },
            /**
             * @property
             * Editable function to be called when the reset button is pressed.  
             * To change the default scaffolding behaviour just replace this function.  
             *   
             * Default scope is the store.
             */
            onReset: function() { // scope is the store
                // reject all changes
                var store = this;
                store.each(function(rec) {
                    if (rec.modified) {
                        rec.reject();
                    }
                    if(rec.phantom) {
                        store.remove(rec);
                    }
                });
            },
            /**
             * @property
             * Editable function to be called when the delete button is pressed.  
             * To change the default scaffolding behaviour just replace this function.  
             *   
             * Scope can be defined in destroyButtonConfig.items[0].scope, but normally 
             * you don't need a scope here, since the arguments already provide everything.
             */
            onDestroy: function(grid, rowIndex, colIndex) {
                var store = grid.getStore(),
                    rec = store.getAt(rowIndex),
                    name = Ext.getClassName(rec);
                
                // instantly remove vom ui
                store.remove(rec);
                
                // delete on server
                if (!rec.phantom) {
                    rec.destroy({
                        success: function() {
                            Ext.MessageBox.show({
                                title: name + ' record deleted',
                                msg: name + ' record was successfully deleted.',
                                icon: Ext.MessageBox.INFO,
                                buttons: Ext.Msg.OK
                            });
                        }
                    });
                }
            },
            /**
             * @property
             * If true a create button will be added to all scaffolded grids.  
              * See class descrition on how the fields are created.
             */
            enableCreate: true,
            /**
             * @property
             * If true a editor field is added to all columns for scaffolded grids.  
             * See class descrition on how the fields are created.
             */
            enableUpdate: true,
            /**
             * @property
             * If true a delete button is added to all rows for scaffolded grids.
             */
            enableDestroy: true,
            /**
             * @property
             * If true a reset button will be added to all scaffolded grids
             * (only if enableCreate or enableUpdate is true).
             */
            enableReset: true,
             /**
              * @property
              * Default create button config, used if enableCreate is true.  
              * If not defined scope and handler properties will be set by 
              * the build function.
              */
             createButtonConfig:  {
                 iconCls: 'icon-add',
                 text: 'Create'
             },
             /**
              * @property
              * Default save button config, used if enableCreate and/or 
              * enableUpdate are true.  
              * If not defined scope and handler properties will be set by 
              * the build function.
              */
             saveButtonConfig: { 
                  iconCls: 'icon-save',
                  text: 'Save'
             },
             /**
              * @property
              * Default reset button config, used if enableReset is true.  
              * If not defined scope and handler properties will be set by 
              * the build function.
              */
             resetButtonConfig: {
                 iconCls: 'icon-reset',
                 text: 'Reset'
             },
             /**
              * @property
              * Default last column config, used if enableDestroy is true to render a destroy 
              * button at the end of the line.  
              * The button handler is expected at destroyButtonConfig.items[0].handler, if it is 
              * equal Ext.emptyFn it will be replace, otherwise the custom config is used.
              */
             destroyButtonConfig: {
                xtype:'actioncolumn', 
                width:50,
                items: [{
                    icon: 'img/icons/delete.png',
                    tooltip: 'Delete',
                    handler: Ext.emptyFn // will be replaced by button handler
                }]
            },
            /**
             * Builds grid columns from the Bancha metadata, for scaffolding purposes.  
             * Please use {@link #createPanel} or {@link #buildConfig} if you want 
             * support for create,update and/or destroy!
             * 
             * @param {Ext.data.Model|String} model The model class or model name
             * @param {Object} config (optional) Any applicable property of 
             * Bancha.scaffold.Grid can be overrided for this call by declaring it
             * here. E.g.:
             *     {
             *         enableDestroy: true
             *     }
             * @return {Array} Returns an array of Ext.grid.column.* configs
             */
            buildColumns: function(model, config) {
                var columns = [],
                    button;
                config = Ext.apply({},config,Ext.clone(this)); // get all defaults for this call
            
            
                // IFDEBUG
                if(!Ext.isDefined(model) || ( (Ext.isString(model) && !Ext.ModelManager.isRegistered(model)) && !model.isModel)) {
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        msg: 'Bancha: Bancha.scaffold.Grid.buildColumns() expected the model or model name as first argument, instead got '+model+'(of type'+(typeof model)+')'
                    });
                }
                // ENDIF

                if(Ext.isString(model)) {
                    // IFDEBUG
                    if(!Ext.isDefined(Ext.ModelManager.getModel(model))) {
                        Ext.Error.raise({
                            plugin: 'Bancha',
                            model: model,
                            msg: 'Bancha: First argument for Bancha.scaffold.Grid.buildColumns() is the string "'+model+'", which  is not a valid model class name. Please define a model first (see Bancha.getModel() and Bancha.createModel())'
                        });
                    }
                    // ENDIF
                    model = Ext.ModelManager.getModel(model);
                }
            
                model.prototype.fields.each(function(field) {
                    columns.push(
                        Bancha.scaffold.Grid.buildColumnConfig(field.type.type,field.name,config)
                    );
                });
            
                if(config.enableDestroy) {
                    button = Ext.clone(config.destroyButtonConfig);
                    if(button.items[0].handler===Ext.emptyFn) {
                        button.items[0].handler = config.onDestroy;
                    }
                    columns.push(button);
                }
    
                return columns;
            },
            /**
             * @method
             * You can replace this function! The function will be executed before each 
             * {@link #buildConfig} as interceptor. For params see {@link #buildConfig}
             * @return {Object|undefined} object with initial Ext.form.Panel configs
             */
            beforeBuild: function(model, config, additionalGridConfig) {
            },
            /**
             * @method
             * You can replace this fucntion! This function will be executed after each 
             * {@link #buildConfig} as interceptor.
             * @param {Object} columnConfig just build grid panel config
             * @param {Object} {Ext.data.Model|String} model see {@link #buildConfig}
             * @param {Object} {Object} config (optional) see {@link #buildConfig}
             * @param {Object} additionalGridConfig (optional) see {@link #buildConfig}
             * @return {Object|undefined} object with final Ext.grid.Panel configs
             */
            afterBuild: function(columnConfig, model, config, additionalGridConfig) {
            },
            /**
             * Builds a grid config from Bancha metadata, for scaffolding purposes.  
             * Guesses are made by model field configs and validation rules.
             *
             * @param {Ext.data.Model|String} model The model class or model name
             * @param {Object|False} config (optional) Any property of 
             * {@link Bancha.scaffold.Grid} can be overrided for this call by declaring 
             * it in this config. E.g
             *      {
             *          columnDefaults: {
             *              width: 200, // force a fixed with
             *          },
             *          onSave: function() {
             *              Ext.MessageBox.alert("Wohoo","You're pressed the save button :)");
             *          },
             *          enableUpdate: true,
             *          formConfig: {
             *              textfieldDefaults: {
             *                  minLength: 3
             *              }
             *          }
             *      }
             *  
             * You can add editorfield configs to the property formConfig, which will then used as standard
             * {@link Bancha.scaffold.Form} properties for this call.
             * @param {Object} additionalGridConfig (optional) Some additional grid configs which are applied to the config.
             * @return {Object} Returns an Ext.grid.Panel configuration object
             */
            buildConfig: function(model, config, additionalGridConfig) {
                var gridConfig, modelName, buttons, cellEditing, store;
                config = Ext.apply({},config,Ext.clone(this)); // get all defaults for this call
            
                // define model and modelName
                if(Ext.isString(model)) {
                    modelName = model;
                    model = Ext.ClassManager.get(modelName);
                } else {
                    modelName = Ext.getClassName(model);
                }
            
                // basic config
                store = config.getStore(model,config);
            
                gridConfig = config.beforeBuild.apply(this,arguments) || {};
                Ext.apply(gridConfig,{
                    store: store,
                    columns: this.buildColumns(model,config)
                });
            
                // add update configs
                if(config.enableUpdate) {
                    cellEditing = Ext.create('Ext.grid.plugin.CellEditing', {
                        clicksToEdit: 2
                    });
                    Ext.apply(gridConfig, {
                        selType: 'cellmodel',
                        plugins: [cellEditing]
                    });
                }
            
                // add buttons
                if(config.enableCreate || config.enableUpdate) {
                    buttons = ['->'];
                
                    if(config.enableCreate) {
                        button = Ext.apply(config.createButtonConfig, {
                            scope: {
                                cellEditing: cellEditing,
                                store      : store
                            },
                            handler: config.onCreate
                        });
                        buttons.push(button);
                    }
                
                    if(config.enableReset) {
                        button = Ext.apply(config.resetButtonConfig, {
                            scope: store,
                            handler: config.onReset
                        });
                        buttons.push(button);
                    }
                    
                    // svae is used for create and update
                    button = Ext.apply(config.saveButtonConfig, {
                        scope: store,
                        handler: config.onSave
                    });
                    buttons.push(button);
                
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
                
                // the scaffold config of the grid is saved as well
                gridConfig.scaffoldConfig = config;
            
                // allways force that the basic scaffold configs are set on the grid config
                gridConfig.scaffold = Ext.ClassManager.getName(model);
                gridConfig.enableCreate = config.enableCreate;
                gridConfig.enableUpdate = config.enableUpdate;
                gridConfig.enableReset = config.enableReset;
                gridConfig.enableDestroy = config.enableDestroy;
                
                // return after interceptor
                return config.afterBuild(gridConfig,model,config,additionalGridConfig) || gridConfig;
            }
        }, //eo Grid 
        /**
         * @class Bancha.scaffold.Form
         * @singleton
         * 
         * This class is a factory for creating Ext.form.Panel's. It uses many data from
         * the given model, including field configs and validation rules.  
         * 
         * In most cases you will use our configurations on {@link Ext.form.Panel}.
         * Here's an example usage:
         *     Ext.create("Ext.form.Panel", {
         *         scaffold: 'User', // the model name
         *
         *         // basic scaffold configs can be set directly
         *         enableReset: true,
         *         // you can also define which record should be loaded for editing
         *         banchaLoadRecord: 3,
         *     
         *         // advanced configs can be set here:
         *         scaffoldConfig: {
         *             textfieldDefaults: {
         *                 emptyText: 'Please fill this out'
         *             },
         *             datefieldDefaults: {
         *                 format: 'm/d/Y'
         *             },
         *             onSave: function() {
         *                 Ext.MessageBox.alert("Tada","You've pressed the form save button");
         *             }
         *         },
         *     
         *         // and add some styling
         *         height: 350,
         *         width: 650,
         *         frame:true,
         *         title: 'Form Panel',
         *         renderTo: 'formpanel',
         *         bodyStyle:'padding:5px 5px 0',
         *         fieldDefaults: {
         *             msgTarget: 'side',
         *             labelWidth: 75
         *         },
         *         defaults: {
         *             anchor: '100%'
         *         }
         *     });
         *
         * It's recognizing following validation rules on the model to add validations
         * to the form fields:
         *  - format
         *  - file
         *  - length
         *  - numberformat
         *  - presence
         *
         * You have three possible interceptors:
         *  - beforeBuild     : executed before {@link #buildGrid}
         *  - guessFieldConfig: executed after a field config is created, see {@link #guessFieldConfig} 
         *  - afterBuild      : executed after {@link #buildGrid} created the config
         * 
         * @author Roland Schuetz <mail@rolandschuetz.at>
         * @docauthor Roland Schuetz <mail@rolandschuetz.at>
         */
        Form: {
            /**
             * @private
             * @property
             * Maps model field configs with field types and additional configs
             */
            fieldToFieldConfigs: {
                'string'  : {xtype:'textfield'},
                'int'     : {xtype:'numberfield', allowDecimals:false},
                'float'   : {xtype:'numberfield'},
                'boolean' : {xtype:'checkboxfield'},
                'date'    : {xtype:'datefield'}
                // TODO OPTIMIZE Add combobox support
            },
            /**
             * @property
             * This config is applied to each scaffolded form field
             */
            fieldDefaults: {},
            /**
             * @property
             * This config is applied to each scaffolded Ext.form.field.Date
             */
            datefieldDefaults: {},
            /**
             * @property
             * This config is applied to each scaffolded Ext.form.field.File
             */
            fileuploadfieldDefaults: {
                emptyText: 'Select an file'
            },
            /**
             * @property
             * This config is applied to each scaffolded Ext.form.field.Text
             */
            textfieldDefaults: {},
            /**
             * @property
             * This config is applied to each scaffolded Ext.form.field.Number
             */
            numberfieldDefaults: {},
            /**
             * @property
             * This config is applied to each scaffolded Ext.form.field.Checkbox
             */
            checkboxfieldDefaults: {
                uncheckedValue: false
            },
            /**
             * @property {Function|False} guessFieldConfigs Writable function used to guess some default behaviour.
             * Can be set to false to don't guess at all.  
              * Default function just hides id fields.
             * @param {Object} configs a form field config
             * @param {String} modelType this is either a standard model field type like 'string' or our in Bancha added 'file'
             * @return {Object} Returns a field config
             */
            guessFieldConfigs: function(configs,modelType) {
                if(configs.name==='id') {
                    configs.xtype = 'hiddenfield';
                }
                
                return configs;
            },
            /**
             * @private
             * Analysis the validation rules for a field and adds validation rules to the field config.
             * For what is supported see {@link Bancha.scaffold.Form}
             * @param {Object} field A Ext.form.field.* config
             * @param {Array} validations An array of Ext.data.validations of the model
             * @param {Object} config A Bancha.scaffold.Form config
             * @return {Object} Returns a Ext.form.field.* config
             */
            addValidationRuleConfigs: (function() {
                /*
                 * closure these in so they are only created once.
                 * we first create the regex and then get the string of them to not have to delete the backslashes 
                 * have a bit cleaner code. It doesn't matter for performance cause it's done only once
                 */
                var alpha = /^[a-zA-Z_]+$/.toString(),
                    alphanum = /^[a-zA-Z0-9_]+$/.toString(),
                    email = /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/.toString(),
                    url = /(((^https?)|(^ftp)):\/\/([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)/i.toString();
                        
                return function(field,validations,config) {
                    var name = field.name, // it's used so often, make a shortcut
                        msgAddition;
                
                    Ext.Array.forEach(validations,function(rule) {
                        if(rule.name !== name) {
                            return;
                        }
                        switch(rule.type) {
                            case 'presence':
                                field.allowBlank = false;
                                break;
                            case 'length':
                                // IFDEBUG
                                // length validation works only only on textfields
                                if(field.xtype!=='textfield') {
                                    msgAddition = (field.xtype==='numberfield') ? 'Use the rule numberformat to force minimal and maximal values.' : '';
                                    Ext.Error.raise({
                                        plugin: 'Bancha',
                                        msg: 'Bancha: The model has a validation rule length for the field '+name+', but this field is of type '+field.xtype+
                                             ', so the rule makes no sense. '+msgAddition
                                    });
                                }
                                // ENDIF
                                if(field.xtype==='textfield') {
                                    if(Ext.isDefined(rule.min)) {
                                        field.minLength = rule.min;
                                    }
                                    if(Ext.isDefined(rule.max)) {
                                        field.maxLength = rule.max;
                                    }
                                }
                                break;
                            case 'format':
                                // IFDEBUG
                                // length validation works only only on textfields
                                if(field.xtype!=='textfield') {
                                    Ext.Error.raise({
                                        plugin: 'Bancha',
                                        msg: 'Bancha: The model has a validation rule format for the field '+name+', but this field is of type '+field.xtype+
                                             ', so the rule makes no sense.'
                                    });
                                }
                                // ENDIF
                                switch(rule.matcher.toString()) {
                                    case alpha:
                                        field.vtype = 'alpha';
                                        break;
                                    case alphanum:
                                        field.vtype = 'alphanum';
                                        break;
                                     case email:
                                        field.vtype = 'email';
                                        break;
                                    case url:
                                        field.vtype = 'url';
                                        break;
                                    default:
                                        // IFDEBUG
                                        if(window.console && Ext.isFunction(window.console.warn)) {
                                            window.console.warn(
                                                'Bancha: Currently Bancha.scaffold.Form only recognizes the model Ext.data.validations format rules '+
                                                 'with the matcher regex of Ext.form.field.VType alpha, alphanum, email and url. This rule with matcher '+
                                                 rule.matcher.toString()+' will just be ignored.');
                                        }
                                        // ENDIF
                                        break;
                                }
                                break;
                            case 'numberformat':    
                                // numberformat validation works only only on numberfields
                                if(field.xtype!=='numberfield') {
                                    Ext.Error.raise({
                                        plugin: 'Bancha',
                                        msg: 'Bancha: The model has a validation rule numberformat for the field '+name+', but this field is of type '+field.xtype+
                                             ', so the rule makes no sense. A numberfield is expected.'
                                    });
                                }
                                // ENDIF
                                if(field.xtype==='numberfield') {
                                    if(Ext.isDefined(rule.min)) {
                                        field.minValue = rule.min;
                                    }
                                    if(Ext.isDefined(rule.max)) {
                                        field.maxValue = rule.max;
                                    }
                                    if(Ext.isDefined(rule.precision)) {
                                        field.decimalPrecision = rule.precision;
                                    }
                                }
                                break;
                            case 'file':
                                // make the field a fileuploadfield
                                field.xtype = 'fileuploadfield';
                                Ext.apply(field,config.fileuploadfieldDefaults);
                            
                                // add validation rules
                                if(Ext.isString(rule.extension)) {
                                    rule.extension = [rule.extension];
                                }
                                if(Ext.isArray(rule.extension)) {
                                    field.vtype = 'fileExtension';
                                    field.validExtensions = rule.extension;
                                }
                                break;
                            default:    
                                // IFDEBUG
                                if(window.console && Ext.isFunction(window.console.warn)) {
                                    window.console.warn(
                                        "Bancha: Could not recognize rule "+Ext.encode(rule)+' when trying to create a form field field.');
                                }
                                // ENDIF
                                break;
                        }
                        // TODO OPTIMIZE Also include inclusion and exclusion
                    }); //eo forEach
                
                    return field;
                }; //eo return fn
            }()),
            /**
             * @private
             * Builds a field with all defaults defined here
             * @param {Sring} type The model field type
             * @param {Object} defaults (optional) Defaults like textfieldDefaults as property of this config. 
             * See {@link #buildConfig}'s config property
             * @return {Object} Returns a Ext.form.field.* config
             */
            buildDefaultFieldFromModelType: function(type,defaults) {
                defaults = defaults || {};
                var field               = Ext.clone(this.fieldToFieldConfigs[type]),
                    fieldDefaults       = Ext.clone(defaults.fieldDefaults || this.fieldDefaults), // make a new object of defaults
                    fieldTypeDefaults   = Ext.clone(defaults[field.xtype+'Defaults'] || this[field.xtype+'Defaults']);
                return Ext.apply(fieldDefaults,field,fieldTypeDefaults);
            },
            /**
             * @private
             * Creates a Ext.form.Field config from an model field type
             * @param {Sring} type The model field type
             * @param {String} fieldName (optional) the name of the field
             * @param {Object} defaults (optional) Defaults like textfieldDefaults as 
             * property of this config. See {@link #buildConfig}'s config property
             * @param {Array} validations An array of Ext.data.validations of the model
             * @return {Object} Returns a field config
             */
            buildFieldConfig: function(type,fieldName,defaults,validations) {
                defaults = Ext.applyIf({}, defaults, Ext.clone(this));
                var field = this.buildDefaultFieldFromModelType(type,defaults);
                
                // infer name
                if(fieldName) {
                    field.fieldLabel = Bancha.scaffold.Util.humanize(fieldName);
                    field.name = fieldName;
                }
                
                // add some additional validation rules from model validation rules
                if(Ext.isDefined(validations) && validations.length) {
                    field = this.addValidationRuleConfigs(field,validations,defaults);
                }
                
                // now make some crazy guesses ;)
                if(typeof defaults.guessFieldConfigs === 'function') {
                    field = defaults.guessFieldConfigs(field,type);
                }
                
                return field;
            },
            /**
             * @property
             * Editable function to be called when the save button is pressed.  
             * To change the default scaffolding behaviour just replace this function.  
             *   
             * The default scope provides two functions:  
             *  - this.getPanel() to get the form panel  
             *  - this.getForm() to get the basic form
             */
            onSave: function(){
                var form = this.getForm(),
                    msg;
                if(form.isValid()){
                    msg = form.hasUpload() ? 'Uploading files...' : 'Saving data..';
                    form.submit({
                        waitMsg: msg,
                        success: function(form, action) {
                            Ext.MessageBox.alert('Success', action.result.msg);
                        },
                        failure: function(form, action) {
                            Ext.MessageBox.alert('Failed', action.result.msg);
                        }
                    });
                }
            },
            /**
             * @property
             * Editable function to be called when the reset button is pressed.  
             * To change the default scaffolding behaviour just replace this function.  
             *   
             * The default scope provides two functions:  
             *  - this.getPanel() to get the form panel  
             *  - this.getForm() to get the basic form
             */
            onReset:  function() {
                this.getForm().reset();
            },
            /**
             * @property
             * If true a reset button will be added to all scaffolded form (Default: true)
             */
            enableReset: true,
            /**
             * @property
             * Default save button config.  
             * If not defined scope and handler properties will be set by 
             * the build function.
             */
            saveButtonConfig: {
                iconCls: 'icon-save',
                text: 'Save',
                formBind: true,
            },
            /**
             * @property
             * Default reset button config, used if enableReset is true.
             * If not defined scope and handler properties will be set by 
             * the build function.
             */
            resetButtonConfig: {
                iconCls: 'icon-reset',
                text: 'Reset'
            },
            /**
             * @private
             * build the form api config, used only by buildConfig()
             * just for separation of concern, since this is the only 
             * part which deals with Bancha's RCP
             */
            buildApiConfig: function(model) {
                 // IFDEBUG
                 if(!Bancha.initialized) {
                     Ext.Error.raise({
                         plugin: 'Bancha',
                         msg: 'Bancha: Bancha is not yet initalized, please init before using Bancha.scaffold.Form.buildConfig().'
                     });
                 }
                 // ENDIF
                 
                var modelName = Ext.ClassManager.getName(model),
                    stub = Bancha.getStubsNamespace()[modelName];
                
                // IFDEBUG
                if(!Ext.isDefined(stub)) {
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        msg: 'Bancha: Bancha.scaffold.Form.buildConfig() expects an remotable bancha model, but got an "normal" model or something else'
                    });
                }
                // ENDIF
                
                return {
                    // The server-side method to call for load() requests
                    load: stub.read, // as first and only param you must add data: {id: id} when loading
                    // The server-side must mark the submit handler as a 'formHandler'
                    submit: stub.submit
                };
            },
            /**
             * You can replace this function! The function will be executed before each 
             * {@link #buildConfig} as interceptor. For params see {@link #buildConfig}
             * @return {Object|undefined} object with initial Ext.form.Panel configs
             */
            beforeBuild: function(model, recordId, config, additionalFormConfig) {
            },
            /**
             * You can replace this function! This function will be executed after each 
             * {@link #buildConfig} as interceptor
             * @param {Object} formConfig just build form panel config
             * @param {Object} {Ext.data.Model|String} model see {@link #buildConfig}
             * @param {Object} {Number|String} recordId (optional) see {@link #buildConfig}
             * @param {Object} {Object} config (optional) see {@link #buildConfig}
             * @param {Object} additionalFormConfig (optional) see {@link #buildConfig}
             * @return {Object|undefined} object with final Ext.form.Panel configs
             */
            afterBuild: function(formConfig, model, recordId, config, additionalFormConfig) {
            },
            /**
             * You only need this is you're adding additional buttoms to the form inside the
             * afterBuild function.  
             * Since the form panel doesn't give us an useful scope to get the form panel,
             * this function will create an proper scope. The scope provides two functions:  
             *  - this.getPanel() to get the form panel  
             *  - this.getForm() to get the basic form  
             * 
             * @param {Function} handler A button handler function to apply the scope to
             * @param {Number|String} id The form panel id
             */
            buildButtonScope: (function() {
                var scopePrototype = {
                    getPanel: function() {
                        return this.panel || Ext.ComponentManager.get(this.id);
                    },
                    getForm: function() {
                        return this.form || this.getPanel().getForm();
                    }
                };
                
                return function(id) {    
                    return Ext.apply({id:id},scopePrototype);
                };
            }()),
            /**
             * Builds form configs from the metadata, for scaffolding purposes.  
             * By default data is loaded from the server if an id is supplied and 
             * onSvae it pushed the data to the server.  
             *  
             * Guesses are made by model field configs and validation rules. 
             * @param {Ext.data.Model|String} model the model class or model name
             * @param {Number|String|False} recordId (optional) Record id of an row to load 
             * data from server, false to don't load anything (for creating new rows)
             * @param {Object|False} config (optional) Any property of 
             * {@link Bancha.scaffold.Form} can be overrided for this call by declaring it 
             * here. E.g.:
             *      {
             *          fieldDefaults: {
             *              disabled: true; // disable all fields by default
             *          },
             *          onSave: function() {
             *              Ext.MessageBox.alert("Wohoo","You're pressed the save button :)");
             *          }
             *      }
             *
             * If you don't define an id here it will be created and can not be changed anymore afterwards.
             *
             * @param {Object} additionalFormConfig (optional) Some additional Ext.form.Panel 
             * configs which are applied to the config
             * @return {Object} object with Ext.form.Panel configs
             */
            buildConfig: function(model, recordId, config, additionalFormConfig) {
                var fields = [],
                    formConfig,
                    id,
                    validations,
                    buttonScope,
                    button,
                    buttons,
                    loadFn;
                config = Ext.apply({},config,Ext.clone(this)); // get all defaults for this call
                additionalFormConfig = additionalFormConfig || {};
                
                // build initial config
                formConfig = config.beforeBuild.apply(this,arguments) || {};

                // IFDEBUG
                if(!Ext.isDefined(model)) {
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        msg: 'Bancha: Bancha.scaffold.Form.buildConfig() expected the model or model name as first argument, instead got undefined'
                    });
                }
                // ENDIF

                if(Ext.isString(model)) {
                    // IFDEBUG
                    if(!Ext.isDefined(Ext.ModelManager.getModel(model))) {
                        Ext.Error.raise({
                            plugin: 'Bancha',
                            model: model,
                            msg: 'Bancha: First argument for Bancha.scaffold.Form.buildConfig() is the string "'+model+'", which  is not a valid model class name. Please define a model first (see Bancha.getModel() and Bancha.createModel())'
                        });
                    }
                    // ENDIF
                    model = Ext.ModelManager.getModel(model);
                }
                // IFDEBUG
                if(!model.prototype || !model.prototype.isModel) {
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        model: model,
                        msg: 'Bancha: First argument for Bancha.scaffold.Form.buildConfig() is the string "'+model+'", which  is not a valid model class name. Please define a model first (see Bancha.getModel() and Bancha.createModel())'
                    });
                }
                // ENDIF

                // create all fields
                validations = model.prototype.validations;
                model.prototype.fields.each(function(field) {
                    fields.push(
                        Bancha.scaffold.Form.buildFieldConfig(field.type.type, field.name, config, validations)
                    );
                });

                // for scoping reason we have to force an id here
                id = additionalFormConfig.id || Ext.id(null,'formpanel-');
                formConfig.id = id;
                
                // build buttons
                buttons = [];
                buttonScope = this.buildButtonScope(id);
                // reset button
                if(config.enableReset) {
                    button = Ext.apply(config.resetButtonConfig, {
                        scope: buttonScope,
                        handler: config.onReset
                    });
                    buttons.push(button);
                }
                // save button
                button = Ext.apply(config.saveButtonConfig, {
                    scope: buttonScope,
                    handler: config.onSave
                });    
                buttons.push(button);
                // TODO OPTIMIZE find and replace '[scaffold-buttons]' in the formConfig!?
                
                // extend formConfig
                Ext.apply(formConfig,additionalFormConfig,{
                    id: id,
                    api: this.buildApiConfig(model),
                    paramOrder: ['data'],
                    items: fields,
                    buttons: buttons
                });
                
                // the scaffold config of the grid is saved as well
                formConfig.scaffoldConfig = config;

                // allways force that the basic scaffold configs are set on the grid config
                formConfig.scaffold = Ext.ClassManager.getName(model);
                formConfig.enableReset = config.enableReset;
                formConfig.banchaLoadRecord = config.recordId;
                
                
                // autoload the record
                if(Ext.isDefined(recordId) && recordId!==false) {
                    formConfig.listeners = formConfig.listeners || {};
                    // if there's already a function, batch them
                    loadFn = function(component,options) {
                        component.load({
                            params: {
                                id: recordId
                            }
                        });
                    };
                    if(formConfig.listeners.afterrender) {
                        formConfig.listeners.afterrender = Ext.Function.createSequence(formConfig.listeners.afterrender,loadFn);
                    } else {
                        formConfig.listeners.afterrender = loadFn;
                    }
                }
                
                // return after interceptor
                return config.afterBuild(formConfig, model, recordId, config, additionalFormConfig) || formConfig;
            }
        } //eo Form
    } //eo scaffold
});

// eof
