/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.0.2
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

/*
 * Polyfill for IE 6,7 and partially 8
 * Add support for ECMAScript 5 Array.reduce
 * Currently used in Bancha.objectFromPath
 * From https://developer.mozilla.org/en/JavaScript/Reference/Global_Objects/Array/Reduce
 */
 /*jsl:ignore*/
/*jshint bitwise:false, curly:false, maxcomplexity:20 */
if (!Array.prototype.reduce) {
    Array.prototype.reduce = function reduce(accumulator){
        if (this===null || this===undefined) throw new TypeError('Object is null or undefined');
        var i = 0, l = this.length >> 0, curr;

        if(typeof accumulator !== 'function') {
            // ES5 : 'If IsCallable(callbackfn) is false, throw a TypeError exception.'
            throw new TypeError('First argument is not callable');
        }

        if(arguments.length < 2) {
            if (l === 0) throw new TypeError('Array length is 0 and no second argument');
            curr = this[0];
            i = 1; // start accumulating at the second element
        }
        else
            curr = arguments[1];

        while (i < l) {
            if(i in this) curr = accumulator.call(undefined, curr, this[i], i, this);
            ++i;
        }

        return curr;
    };
}
/*jshint bitwise:true, curly:true, maxcomplexity:6 */
/*jsl:end*/


//<debug>
if(Ext.versions.touch) {
    Ext.ClassManager.setAlias('Ext.MessageBox', 'Ext.window.MessageBox');
}
if(Ext.versions.extjs) {
    Ext.ClassManager.setAlias('Ext.window.MessageBox', 'Ext.MessageBox');
}
//</debug>

/**
 * @class Bancha
 *
 * This singleton is the core of Bancha on the client-side.
 * For documentation on how to use it please look at the docs at
 * banchaproject.org
 *
 * Usage:
 *
 *     // load Bancha
 *     Ext.Loader.setPath('Bancha','/Bancha/js');
 *     Ext.syncRequire('Bancha.Initializer');
 *
 *     // Simply require Bancha models
 *     // Bancha will load the model meta data from the server,
 *     // create the model definition and then trigger the execution
 *     Ext.define('MyApp.view.MyGrid', {
 *         requires: [
 *             'Bancha.model.Article'
 *         ]
 *
 *         ... your code ...
 *
 *     }); //eo define
 *
 * To handling all kind of exceptions, please see {@link Bancha.Remoting}.
 *
 * @singleton
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha', {

    /* Begin Definitions */
    singleton: true,

    uses: [
        'Bancha.REMOTE_API'
    ],

    requires: [
        'Ext.data.*',
        'Ext.direct.*',
        // The Ext JS and Sencha Touch namespace for MessageBox differs
        // Compability for the development version is done above
        // Here we make sure it compiles correctly with Sencha Cmd
        //<if touch>
        'Ext.MessageBox',
        //</if>
        //<if ext>
        'Ext.window.MessageBox',
        //</if>
        'Bancha.data.override.Validations'
    ],

    // If you want to include Bancha using the Microloader use this class name
    // instead of simply 'Bancha', sine this is also a namespace
    alternateClassName: [
        'Bancha.Main'
    ],

    // hacky solution to keep references in all possible loading orders
    // this should be removed after the Bancha singleton is refactored into
    // multiple, independent classes
    // To hinder Sencha Cmd from detecting dpendencies we son't use the dot
    // notation here
    /* jshint sub:true */
    data       : window.Bancha ? Bancha['data'] : undefined,
    loader     : window.Bancha ? Bancha['loader'] : undefined,
    Loader     : window.Bancha ? Bancha['Loader'] : undefined,
    Logger     : window.Bancha ? Bancha['Logger'] : undefined,
    Initializer: window.Bancha ? Bancha['Initializer'] : undefined,
    Remoting   : window.Bancha ? Bancha['Remoting'] : undefined,
    /* jshint sub:false */
    /* End Definitions */

    //<debug>
    /**
     * @private
     * @property
     * This property only exists in the debug version to indicate
     * to jasmine tests that this is a debug version
     */
    debugVersion: true,
    //</debug>

    /**
     * @private
     * @export
     * @property
     * The internal reference to the Remote API.
     *
     * Export annotation is for Google Closure compiler.
     *
     * Default: If the remote api is already loaded, keep it.
     * Otherwise set to undefined.
     * */
    REMOTE_API: (typeof Bancha !== 'undefined') ? Bancha.REMOTE_API : undefined,

    /**
     * @property
     * Bancha Project version
     */
    version: 'PRECOMPILER_ADD_RELEASE_VERSION',
    /**
     * @property
     * The local path to the Bancha remote api (Default: 'Bancha.REMOTE_API')
     *
     * Only change this if you changed 'Bancha.remote_api' in the CakePHP
     * config, never change after {@link Bancha#init}
     */
    remoteApi: 'Bancha.REMOTE_API',
    /**
     * @private
     * @property
     * This function should normally not be changed, it corresponds to the
     * server-side BanchaController method.
     *
     * The path of the RCP for loading model meta data from the server,
     * without the namespace. (Default: 'Bancha.loadMetaData')
     */
    metaDataLoadFunction: 'Bancha.loadMetaData',
    /**
     * @private
     * @property
     * The name of uid property in the metadata array
     */
    uidPropertyName: '_UID',
    /**
     * @property {null|String}
     * The namespace of Ext.Direct stubs, will be loaded from the REMOTE_API
     * configuration on {@link Bancha#init}.
     *
     * null means no namespace, this is not recommanded. The namespace can be
     * set in CakePHP:
     *     Configure:write('Bancha.namespace','Bancha.RemoteStubs');
     */
    namespace: null,
    /**
     * @private
     * @property
     * The namespace in which all Bancha models are initialized to. Please only
     * change BEFORE for creation of any Bancha model.
     * There's normally no need to change this. (Default: 'Bancha.model')
     */
    modelNamespace: 'Bancha.model',
    /**
     * @private
     * @property
     * Indicates that Bancha is initialized. Used for debugging.
     */
    initialized: false,
    /**
     * @private
     * Safely finds an object, used internally for getStubsNamespace and
     * getRemoteApi.
     *
     * (This function is tested in RS.util, not part of the package testing,
     * but it is tested)
     *
     * @param {String} path A period ('.') separated path to the desired object (String).
     * @param {String} lookIn (optional) The object on which to perform the lookup.
     * @param {String} prototypes (optional) False to not look in prototypes (to be tested)
     * @return {Object} The object if found, otherwise undefined.
     */
    objectFromPath: function (path, lookIn, prototypes) {
        if(typeof path === 'number') { // for array indexes
            path = path+''; // to string
        }
        if(typeof path !== 'string') {
            return undefined;
        }
        prototypes = (typeof prototypes === 'undefined') ? true : prototypes; // true is default

        if (!lookIn) {
            //get the global object so it don't use hasOwnProperty on window (IE incompatible)
            var first = path.indexOf('.'),
                globalObjName,
                globalObj;
            if (first === -1) {
                // the whole path is only one object, so return the object
                return Ext.global[path];
            }
            // else use the first part as global object name
            globalObjName = path.slice(0, first);
            globalObj = Ext.global[globalObjName];
            if (typeof globalObj === 'undefined') {
                // path seems to be false
                return undefined;
            }
            // set the new lookIn and the path
            lookIn = globalObj;
            path = path.slice(first + 1);
        }
        // get the object
        return path.split('.').reduce(function(o, p) {
            if(o && (prototypes || o.hasOwnProperty(p))) {
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
        //<debug>
        if(!this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha is not yet initalized, please init before using Bancha.getStubsNamespace().'
            });
        }
        //</debug>
        return this.objectFromPath(this.namespace);
    },
    /**
     * Returns remote stubs for a given cake controller name in singular
     * @param {String} stubName the cakephp controller name in singular
     * @return {Object} The stub if already defined, otherwise undefined
     */
    getStub: function(stubName) {
        if(!Bancha.initialized) {
            Bancha.init();
        }
        //<debug>
        if(!Ext.isObject(Bancha.getStubsNamespace()[stubName])) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: The Stub '+stubName+' doesn\'t exist'
            });
        }
        //</debug>
        return Bancha.getStubsNamespace()[stubName] || undefined;
    },
    /**
     * @private
     * Returns the remote api definition of Ext.direct
     * @return {Object} The api if already defined, otherwise undefined
     */
    getRemoteApi: function() {
        //<debug>
        if(!Ext.isString(this.remoteApi)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    'Bancha: Bancha.remoteApi is not yet defined, ',
                    'please define the api before using Bancha.getRemoteApi().'
                ].join('')
            });
        }
        if(!Ext.isObject(this.objectFromPath(this.remoteApi))) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    'Bancha: The remote api '+this.remoteApi+' is not yet defined, ',
                    'please define the api before using Bancha.getRemoteApi().'
                ].join('')
            });
        }
        //</debug>
        return this.objectFromPath(this.remoteApi);
    },
    /* jshint maxstatements: 50, maxcomplexity: 20 */ /* don't optimize this anymore, it's already deprecated */
    /**
     * Inits Bancha with the RemotingProvider, always init before using Bancha.
     * ({@link Bancha#onModelReady} will init automatically)
     *
     * @deprecated Bancha internally calls this function, you don't need to explicitly use it anymore
     * @return {undefined}
     */
    init: function() {
        var remoteApi,
            defaultErrorHandle,
            apiPath,
            response,
            result;

        //<debug>

        // show all initialize errors as message
        defaultErrorHandle = Ext.Error.handle;
        Ext.Error.handle = function(err) {
            try {
                Ext.Msg.alert('Bancha Error', err.msg);
            } catch(e) {
                // this migh have been triggered before domready
                // so it's possible that Ext.Msg fail, in these
                // cases show an simply alert and let the
                // exception bubble up
                Ext.global.alert(err.msg);
            }
        };

        if(!Ext.isObject(this.objectFromPath(this.remoteApi))) {

            // the remote api is not available, check if this is because of
            // an error on the bancha-api.js or because it is not included
            Ext.syncRequire('Bancha.REMOTE_API');

            if(Ext.isObject(this.objectFromPath(this.remoteApi)) && Ext.Logger && typeof Ext.Logger.warn==='function') {
                Ext.Logger.warn([
                    '[Bancha.init] Synchronously loading \'Bancha.REMOTE_API\'; This is a Bug in Bancha, please ',
                    'report this on https://github.com/Bancha/Bancha/issues. Thanks!'
                ].join());
            }

            // load the api
            response = Ext.Ajax.request({
                url: Ext.Loader.getPath('Bancha.REMOTE_API'),
                async: false
            });

            if(response.status === 404) {
                //the api is included, but there seems to be an error
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: [
                        '<b>Bancha Configuration Error:</b><br />',
                        'You have an error in your <a href="'+apiPath+'">Bancha API</a>, please fix it:<br /><br />',

                        response.responseText.search(/<h2>Not Found<\/h2>/)!==-1 ?
                        '<b>Note: You might have to turn ob debug mode to get a usefull error message!</b><br/><br/>' :
                        '',

                        response.responseText.substr(0,31) === '<script type="text/javascript">' ? // remove script tags
                            response.responseText.substr(31,response.responseText.length-31-9) :
                            response.responseText
                    ].join('')
                });
            }

            if(response.responseText.search(/Parse error/)!==-1 || response.responseText.search(/cake-error/)!==-1) {
                // there is an php error in cake
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: [
                        '<b>CakePHP Error:</b><br />',
                        'You have an php error in your code:<br /><br />',

                        response.responseText.substr(0,31) === '<script type="text/javascript">' ? // remove script tags
                            response.responseText.substr(31,response.responseText.length-31-9) :
                            response.responseText
                    ].join('')
                });
            }

            // general error message
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    '<b>Unknown Error in Bancha API:</b><br />',
                    'You have an error in your <a href="'+apiPath+'">Bancha API</a>, ',
                    'please open the API for details.<br />',
                    'Note: You might have to turn ob debug mode to get a usefull error message!<br />'
                ].join('')
            });
        }

        if(this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Bancha is initalized twice, please just initialize once.'
            });
        }
        //</debug>

        // set the flag to true now
        this.initialized = true;

        remoteApi = this.getRemoteApi();

        //<debug>
        if(remoteApi && remoteApi.metadata && remoteApi.metadata._ServerError) {
            // there is an cake error
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    '<b>CakePHP Error:</b><br />',
                    'You have an error in your cakephp code:<br /><br />',
                    Ext.isString(remoteApi.metadata._ServerError) ?
                        remoteApi.metadata._ServerError :
                        'Please turn the cakephp debug mode on to see the error message!'
                ].join('')
            });
        }
        //</debug>

        // init error logging in production mode
        if(Bancha.getDebugLevel()===0 && (typeof TraceKit !== 'undefined') &&
            TraceKit.report && Ext.isFunction(TraceKit.report.subscribe)) {
            TraceKit.report.subscribe(Bancha.Remoting.getFacade('onError'));
        }

        // if the server didn't send an metadata object in the api, create it
        if(!Ext.isDefined(remoteApi.metadata)) {
            remoteApi.metadata = {};
        }

        this.decodeMetadata(remoteApi);

        //<debug>
        if(Ext.isObject(remoteApi)===false) {
            Ext.Error.raise({
                plugin: 'Bancha',
                remoteApi: this.remoteApi,
                msg: [
                    'Bancha: The Bancha.remoteApi config seems to be configured wrong. ',
                    'See also CakePHPs Configure:write(\'Bancha.remote_api\'Bancha.REMOTE_API\');'
                ].join('')
            });
        }
        //</debug>

        this.namespace = remoteApi.namespace || null;

        // init Provider
        Ext.direct.Manager.addProvider(remoteApi);

        //<debug>
        // test if the bancha dispatcher is available
        response = Ext.Ajax.request({
            url: remoteApi.url+'?setup-check=true',
            async: false
        });
        try {
            result = Ext.decode(response.responseText, true);
        } catch(e) {
            // handle below
        }
        if(response.status!==200 || !Ext.isObject(result) || !result.BanchaDispatcherIsSetup) {

            // this might be just an update issue
            // check if the old name (bancha.php) still works
            response = Ext.Ajax.request({
                url: remoteApi.url.replace(/bancha-dispatcher\.php/, 'bancha.php')+'?setup-check=true',
                async: false
            });
            try {
                result = Ext.decode(response.responseText, true);
            } catch(e) {
                // There are many errors, let the user first rename the Bancha dispatcher and then fix the other
            }
            if(response.status===200 && Ext.isObject(result) && result.BanchaDispatcherIsSetup) {
                // old bancha dispatcher is still available
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: [
                        '<b>Bancha Update Error:</b><br />',
                        'Since 1.1.0 the Bancha Dispatcher got renamed from ',
                        '"bancha.php" to "bancha-dispatcher.php".<br /><br />',
                        '<b>Please rename the file <i>app/webroot/bancha.php</i> ',
                        'to <i>bancha-dispatcher.php</i><br />'
                    ].join('')
                });
            }

            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    '<b>Bancha Configuration Error:</b><br />',
                    'Bancha expects the Bancha Dispatcher to be reachable at ',
                    '<a href="'+remoteApi.url+'">'+remoteApi.url+'</a>.',
                    '<br /><br />',
                    '<b>Probably you just forgot to copy the file ',
                    '<i>Bancha/_app/webroot/bancha-dispatcher.php</i> into your ',
                    'app at <i>app/webroot/bancha-dispatcher.php</i><br />',
                    'Please do this and then reload the page.</b>'
                ].join('')
            });
        }

        // reset to default error handler
        Ext.Error.handle = defaultErrorHandle;

        //</debug>

        // In Cake Debug mode set up all default error handlers
        if(this.getDebugLevel()!==0) { // this works only when this.initialized===true
            this.setupDebugErrorHandler();
        }
    },
    /* jshint maxstatements: 25, maxcomplexity: 10 */
    /**
     * @private
     * If you are using Bancha when CakePHP is in debug mode this
     * function will be set up during initializiation to setup
     * debugging error handlers.
     *
     * In production mode this function will be empty. This
     * function is only triggered when cakes debug level is
     * greater then zero.
     */
    setupDebugErrorHandler: function() {

        //<debug>
        // catch every debug exception thrown from either ExtJS or Bancha
        Ext.Error.handle = function(err) {
            Ext.Msg.alert('Error', err.msg);
        };

        // catch server-side errors
        Ext.direct.Manager.on('exception', function(err){
            // normalize ExtJS and Sencha Touch
            var title,
                msg,
                data = (typeof err.getCode === 'function') ? {
                code: err.getCode(),
                message: err.getMessage(),
                data: {
                    msg: err.getData()
                },

                // bancha-specific
                exceptionType: err.config.exceptionType,
                where: err.config.where,
                trace: err.config.trace
            } : err;

            // handle error
            if(data.code==='parse') {
                // parse error
                title = 'Bancha: Server-Response can not be decoded';
                msg = data.data.msg;
            } else if(data.code==='xhr') {
                // connection error
                title = 'Connection Error: '+data.message;
                msg = 'Please make sure your internet connection is working and your server is running.';
            } else if(data.exceptionType === 'BanchaAuthLoginException' ||
                    data.exceptionType === 'BanchaAuthAccessRightsException') {
                // CakePHP AuthComponent prevented loading
                Bancha.Remoting.onAuthException(data.exceptionType,data.message);
                return;
            } else {
                // exception from server
                title = 'Bancha: Exception from Server';
                msg = [
                    '<b>'+(data.exceptionType || 'Exception')+': '+data.message,
                    '</b><br /><br />',
                    ((data.where) ?
                        data.where+'<br /><br />Trace:<br />'+data.trace :
                        '<i>Turn on the debug mode in cakephp to see the trace.</i>')
                ].join('');
            }

            // Show the error and then throw an exception
            // (don't use Ext.Error.raise to not trigger the handler above)
            Ext.Msg.show({
                title: title,
                message: msg, //touch
                msg: msg, //extjs
                icon: Ext.MessageBox.ERROR,
                buttons: Ext.Msg.OK
            });
            throw new Error(msg);
        });
        //</debug>
    },
    /**
     * @private
     * @method
     * Decodes all stuff that can not be directly send in the right format from the server
     * Directly applies the changes
     */
    decodeMetadata: (function() {

        // since json doesn't support regex and json_encode fucks excaping up, transform bancha strings to real reggex
        var regex = {
            Alpha: /^[a-zA-Z_]+$/,
            Alphanum: /^[a-zA-Z0-9_]+$/,
            Email: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/,
            Url: /(((^https?)|(^ftp)):\/\/([\-\w]+\.)+\w{2,3}(\/[%\-\w]+(\.\w{2,})?)*(([\w\-\.\?\\\/+@&#;`~=%!]*)(\.\w{2,})?)*\/?)/i
        };

        return function(remoteApi) {
            Ext.Object.each(remoteApi.metadata, function(key,model) {
                Ext.Object.each(model.validations, function(key,rule) {
                    if(rule.type==='format' &&
                        Ext.isString(rule.matcher) &&
                        rule.matcher.substr(0,6)==='bancha' &&
                        regex[rule.matcher.substr(6)]) {

                        rule.matcher = regex[rule.matcher.substr(6)];
                    }
                });
            });
        };
    }()), //eo decodeMetadata

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
     * @deprecated Instead of preloading dependencies imperatively, use the uses
     * property on classes to load optional classes. Description below is from
     * Bancha 1.3
     * @param {Array|String} models    An array of the models to preload or a string with one model name
     * @param {Function}     callback  (optional) A callback function
     * @param {Object}       scope     (optional) The scope of the callback function
     */
    preloadModelMetaData: function(modelNames,callback,scope) {
        //<debug>
        if(Ext.Logger && Ext.Logger.deprecate) {
            Ext.Logger.deprecate([
                'Bancha.preloadModelMetaData is deprecated and will be removed soon. ',
                'Please simply define your dependencies in Sencha requires configs. ',
                'For performance in production mode please use Sencha Cmd, so you ',
                'won\'t need this function anymore.'
            ].join(''), 1);
        }
        //</debug>

        this.loadModelMetaData(modelNames,callback,scope,false);
    },
    /**
     * @private
     * Returns the url to load the metadata. Simple separation of concerns for
     * better debugging and testing.
     *
     * @since Bancha v 2.0.0
     * @param  {Array}  modelNames An array of model names to load
     * @return {String}            The url to load model metadata via ajax
     */
    getMetaDataAjaxUrl: function(modelNames) {
        //<debug>
        if(!Bancha.REMOTE_API) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: The Bancha.getMetaDataAjaxUrl requires the Bancha.REMOTE_API to be loaded to get the url.'
            });
        }
        //</debug>

        var directUrl = Bancha.REMOTE_API.url,
            baseUrl = directUrl.substr(0, directUrl.lastIndexOf('/')+1 || 0);
        baseUrl = baseUrl || '/'; // if the direct url does not contain an slash
        return baseUrl+'bancha-load-metadata/['+modelNames+'].js';
    },
    /**
     * @private
     * This function loads model metadata from the server to create a new model definition.
     *
     * This function should not be directly used, instead require model classed in your class
     * definitions.
     *
     * @since Bancha v 2.0.0
     * @param {Array|String}  models            An array of the models to preload or a string with one model name
     * @param {Function}      callback          (optional) A callback function
     * @param {Boolean}       callback.success  True is successful, otherwise false.
     * @param {String}        callback.errorMsg If an error occured, this is the reeason.
     * @param {Object}        scope             (optional) The scope of the callback function
     * @param {Boolean}       syncEnabled       (optional) To to load synchronously (Defualt: false)
     * @return {void}
     */
    loadModelMetaData: function(modelNames, callback, scope, syncEnabled) {

        // make sure Bancha is initialized
        if(!Bancha.initialized) {
            Bancha.init();
        }

        // get remote stub function
        var fn, cb;

        //<debug>
        if(!Bancha.REMOTE_API) { // with syncEnable needed for getMetaDataAjaxUrl, otherwise for Ext.Direct
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: The Bancha.loadModelMetaData requires the Bancha.REMOTE_API to be loaded.'
            });
        }

        Ext.each(modelNames, function(modelName) {
            if(Ext.isObject(Bancha.getModelMetaData(modelName))) {
                if(Ext.global.console && Ext.isFunction(Ext.global.console.warn)) {
                    Ext.global.console.warn(
                        'Bancha: The model '+modelName+' is already loaded, we will reload the meta data '+
                        'but this seems strange. Notice that this warning is only created in debug mode.');
                }
            }
        });
        //</debug>

        // force models to be an array
        if(Ext.isString(modelNames)) {
            modelNames = [modelNames];
        }

        // create the callback
        cb = Ext.Function.pass(this.onModelMetaDataLoaded, [callback, scope, modelNames], this);

        if(syncEnabled) {
            // start synchronous ajax request (Ext.Direct does not support synchronous requests)
            Ext.Ajax.request({
                url: this.getMetaDataAjaxUrl(modelNames),
                async: false,
                success: function(response) {
                    // prepare a Ext.Direct-like result
                    var result = {
                        success: true,
                        data: Ext.decode(response.responseText)
                    };

                    // trigger callback
                    cb(result);
                },
                failure: function(response, opts) {

                    // error, tell user
                    callback = callback || Ext.emptyFn;
                    scope = scope || Ext.global;
                    callback.call(scope, false, 'Server-side failure with status code '+response.status);
                }
            });
        } else {
            // start async Ext.Direct request
            fn = this.objectFromPath(this.metaDataLoadFunction,this.getStubsNamespace());

            //<debug>
            if(!Ext.isFunction(fn)) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    metaDataLoadFunction: this.metaDataLoadFunction,
                    msg: 'Bancha: The Bancha.metaDataLoadFunction config seems to be configured wrong.'
                });
            }
            //</debug>

            fn(modelNames,cb,Bancha);
        }
    },
    /**
     * @private
     * This function is triggered when the server returned the metadata, loaded via #loadModelMetaData.
     *
     * @since Bancha v 2.0.0
     * @param {Array|String}  models            An array of the models to preload or a string with one model name
     * @param {Function|null} callback          A callback function
     * @param {Boolean}       callback.success  True is successful, otherwise false.
     * @param {String}        callback.errorMsg If an error occured, this is the reeason.
     * @param {Object|null}   scope             The scope of the callback function
     * @param {Object|null}   result            A result object, containing a success and data property
     * @return {void}
     */
    onModelMetaDataLoaded: function(callback, scope, modelNames, result) {
        //<debug>
        if(result===null || result===undefined) {
            Ext.Error.raise({
                plugin: 'Bancha',
                result: result,
                msg: [
                    'Bancha: The Bancha.loadModelMetaData('+modelNames.toString(),
                    ',..) expected to get the metadata from the server, ',
                    'instead got: '+Ext.encode(result)
                ].join('')
            });
        }
        //</debug>

        // error handling
        if(!result.success) {
            callback = callback || Ext.emptyFn;
            scope = scope || Ext.global;
            callback.call(scope, false, result.message);
        }
        // save result
        Ext.apply(Bancha.getRemoteApi().metadata, result.data);

        // decode new stuff
        this.decodeMetadata(Bancha.getRemoteApi());

        //<debug>
        if(!Ext.isFunction(callback) && !Ext.isNull(callback)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                callback: callback,
                msg: 'Bancha: The for Bancha.loadModelMetaData supplied callback is not a function.'
            });
        }
        if(!Ext.isObject(scope) && !Ext.isNull(scope)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                scope: scope,
                msg: 'Bancha: The for Bancha.loadModelMetaData supplied scope is not a object.'
            });
        }
        //</debug>

        // user callback
        callback = callback || Ext.emptyFn;
        scope = scope || Ext.global;
        callback.call(scope, true, 'Successfully loaded '+modelNames.toString());
    },
    /**
     * Checks if the model is supported by the server
     * Todo: This currently doesn't check if the exposed Object is an Controller method or an exposed model
     * @param {String} modelName The name of the model
     * @return {Boolean} True is the model is remotable
     */
    isRemoteModel: function(modelName) { // TODO refactor to isRemotableModel
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
     * If Bancha is not already initialized it will wait for
     * [Ext.isReady](http://docs.sencha.com/ext-js/4-1/#!/api/Ext-property-isReady)
     * and calls {@link Bancha#init} before model creation.
     *
     * See {@link Bancha Bancha class explaination} for an example.
     *
     * @deprecated Bancha 2 allows to load dependencies through the normal Ext.Loader,
     * therefore please simply define your required models in your Ext.application
     * config instead of using this function. This function will be removed soon.
     * @param {String|Array} modelNames A name of the model or an array of model names
     * @param {Function} callback (optional) A callback function, the first argument is:
     *  - a model class if input modelNames was an string
     *  - an object with model names as keys and models as arguments if an array was given
     * @param {Object} scope (optional) The scope of the callback function
     */
    onModelReady: function(modelNames, callback, scope) {
        //<debug>
        if(Ext.Logger && Ext.Logger.deprecate) {
            Ext.Logger.deprecate([
                'Bancha.onModelReady is deprecated and will be removed soon. ',
                'Bancha 2 allows to load dependencies through the normal Ext.Loader, ',
                'therefore please simply define your required models in your Ext.application ',
                'config instead of using this function. '
            ].join(''), 1);
        }
        //</debug>

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

        //<debug>
        if(!Ext.isFunction(callback)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Please define a callback as the second param for Bancha.onModelReady(modelName,callback).'
            });
        }
        //</debug>

        // defaults
        var modelsToLoad  = [],
            modelName,
            me = this;
        callback = callback || Ext.emptyFn;
        scope = scope || {}; // sandbox is no scope is set, way easier to debug then window
        loadingModels = loadingModels || {};
        loadedModels  = loadedModels  || {};

        // handle single model request (simple and different callback argument)
        modelName = (modelNamesToLoad.length===1) ? modelNamesToLoad[0] : modelNamesToLoad;
        if(Ext.isString(modelName)) {

            if(this.modelMetaDataIsLoaded(modelName)) {
                // all metadata already present, call callback with model
                callback.call(scope,this.getLoadedModel(modelName));
            } else {
                this.preloadModelMetaData(modelName, function() {
                    // all metadata already present, call callback with model
                    callback.call(scope,this.getLoadedModel(modelName));
                },this);
            }
            return;
        }

        if(!Ext.isArray(modelNamesToLoad)) {
            //<debug>
            Ext.Error.raise({
                plugin: 'Bancha',
                modelNamesToLoad: modelNamesToLoad,
                msg: [
                    'Bancha: The Bancha.onModelReady(modelNamesToLoad) expects ',
                    'either a string with one model oder an array of models, ',
                    'instead got'+modelNamesToLoad.toString(),
                    ' of type '+(typeof modelNamesToLoad)
                ].join('')
            });
            //</debug>
            return false;
        }

        // iterate trought the models to load
        Ext.Array.forEach(modelNamesToLoad, function(modelName) {
            if(me.modelMetaDataIsLoaded(modelName)) {
                loadedModels[modelName] = me.getLoadedModel(modelName);
            } else {
                modelsToLoad.push(modelName);
            }
        });

        // iterate trought the loading models
        Ext.each(loadingModels, function(modelName) {
            if(me.modelMetaDataIsLoaded(modelName)) {
                // that was the model which triggered the function, so we are finished here
                loadedModels[modelName] = me.getLoadedModel(modelName);
                return false; // stop
            }
        });

        if(modelsToLoad.length===0) {
            // all metadata already present, call callback
            callback.call(scope,loadedModels);
        } else {
            // add all elements to the queue
            me.preloadModelMetaData(modelsToLoad, function() {
                // when model is loaded try again
                me.onInitializedOnModelReady([], loadingModels, loadedModels, callback, scope);
            },me);
        }
    },
    /**
     * @private
     * Get the metadata of an model
     * @param {String} modelName The name of the model
     * @return {Object|null} Returns an objects with all metadata or null if not loaded yet.
     */
    getModelMetaData: function(modelName) {
        //<debug>
        if(!this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Please inistalize Bancha before using it\'s getModelMetaData() method.'
            });
        }

        if(!Ext.isObject(this.getRemoteApi())) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    'Bancha:The Bancha.remoteApi is configured wrong, ',
                    'this should be automatically refer to the ',
                    'Bancha.REMOTE_API object, maybe due a misconfigured server.'
                ].join('')
            });
        }
        if(!Ext.isObject(this.getRemoteApi().metadata)) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    'Bancha: The server served a Bancha.REMOTE_API object ',
                    'without a metadata property, maybe due a misconfigured ',
                    'server or a non-Bancha backend system.'
                ].join('')
            });
        }
        //</debug>

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
        //<debug>
        if(!this.initialized) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: Please inistalize Bancha before using it\'s getConsistentUid() method.'
            });
        }

        if(!(
             Ext.isObject(api) &&
             Ext.isObject(api.metadata) &&
             Ext.isString(api.metadata[this.uidPropertyName])
             )) {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Bancha: There is no Bancha consistent model uid defined in the metadata. '+
                     'Maybe you use a non-Bancha backend or forgot to include the remote api on this site.'
            });
        }
        //</debug>

        return (api && api.metadata && api.metadata[this.uidPropertyName]) ? api.metadata[this.uidPropertyName] : false;
    },
    /**
     * Returns the current CakePHP debug level
     *
     * @param {Number} defaultValue (optional) The number to return if the Remote API is not yet initialized (Default: undefined)
     * @return the current debug level, or if not available the default
     */
    getDebugLevel: function(defaultValue) {
        if(!this.initialized) {
            return defaultValue;
        }

        var api = this.getRemoteApi();
        return (api && api.metadata && Ext.isDefined(api.metadata._ServerDebugLevel)) ? api.metadata._ServerDebugLevel : defaultValue;
    },
    /**
     * This is a synonym for {Bancha.Logger.log}
     *
     * @param  {String}  message        The error message
     * @param  {String}  type           (optional) Either 'error', 'warn' or 'missing_translation' (default is 'error')
     * @param  {Boolean} forceServerlog (optional) True to write the error to the server, even in debug mode (default to false)
     * @return void
     */
    log: function(message, type, forceServerlog) {
        if(!Bancha.Logger) {
            //<debug>
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: 'Tried to use Bancha.log, but the necessarry class Bancha.Logger is not present'
            });
            //</debug>
            if(Ext.global.console && Ext.global.console.error) {
                Ext.global.console.error('Tried to use Bancha.log, but the necessarry class Bancha.Logger is not present');
            }
            return;
        }
        return Bancha.Logger.log.apply(Bancha.Logger,arguments);
    },

    /**
     * Checks if a Bancha model is already created (convinience function)
     *
     * @param {String} modelName The model name (without any namespace)
     * @return {Boolean} True if the model exists
     */
    isCreatedModel: function(modelName) {
        return Ext.ClassManager.isCreated(Bancha.modelNamespace+'.'+modelName);
    },

    /**
     * This method creates a {@link Bancha.data.Model} with your additional model configs,
     * if you don't have any additional configs just use the convienience method {@link #getModel}.
     *
     * In the debug version it will raise an Ext.Error if the model can't be
     * or is already created, in production it will only return false.
     *
     * @deprecated Please only define your model on the backend to have a clean separation of concerns.
     * This function will be removed soon.
     * @param {String} modelName The name of the model
     * @param {Object} modelConfig A standard Ext.data.Model config object
                                   In ExtJS this will be directly applied.
                                   In Sencha Touch this iwll be applied to the config property.
     * @return {Boolean} Returns true is model was created successfully
     */
    createModel: function(modelName, modelConfig) {

        //<debug>
        if(Ext.Logger && Ext.Logger.deprecate) {
            Ext.Logger.deprecate([
                'Bancha.createModel is deprecated and will be removed soon. ',
                'Please only define your model on the backend to have a clean ',
                'separation of concerns.'
            ].join(''), 1);
        }
        //</debug>

        // Sencha Touch puts all configs in a config object,
        // ExtJS places it directly in the model.
        // Adopt correctly.
        modelConfig = (Ext.versions.touch) ? {config:modelConfig} : modelConfig;

        // create the model
        this._createModel(modelName, modelConfig);

        return true;
    },
    /**
     * @private
     * This function simply defines a Bancha model.
     *
     * Internal usage, because from getLoadedModel should not trigger deprecated warnings.
     *
     * @inheritdoc #createModel
     */
    _createModel: function(modelName, modelConfig) {
        // create the model
        Ext.define(Bancha.modelNamespace+'.'+modelName, Ext.apply(modelConfig || {}, {
            extend: 'Bancha.data.Model'
        }));
    },
    /**
     * Get a Bancha model by name.
     * If it isn't already defined this function will define the model.
     *
     * In the debug version it will raise an Ext.Error if the model can't
     * be created, in production it will just return null.
     *
     * If the model definition is not yet loaded, it will synchronously
     * load the definition before returning.
     *
     * @deprecated Bancha allows to load dependencies through the normal Ext.Loader,
     * therefore please use the require property in your class definitions, or
     * Ext.syncRequire('Bancha.model.SomeModel') to synchronously require a model.
     * @param {String} modelName The name of the model
     * @return {Ext.data.Model|null} Returns the model or null if this model doesn't exist
     * @member Bancha
     * @method getModel
     */
    getModel: function(modelName) {
        if(!Bancha.initialized) {
            Bancha.init();
        }

        Ext.syncRequire(Bancha.modelNamespace+'.'+modelName);
        return Ext.ClassManager.get(Bancha.modelNamespace+'.'+modelName);
    },
    /**
     * @private
     * Get a Bancha model by name.
     * If it isn't already defined this function will define the model.
     *
     * In the debug version it will raise an Ext.Error if the model can't
     * be created, in production it will just return null.
     *
     * This function will not try to load metadata, instead it will fail!
     *
     * @param {String} modelName The name of the model
     * @return {Ext.data.Model|null} Returns the model or null if this model doesn't exist
     * @member Bancha
     * @method getLoadedModel
     */
    getLoadedModel: function(modelName) {
        return (this.isCreatedModel(modelName) || this._createModel(modelName)) ?
                Ext.ClassManager.get(Bancha.modelNamespace+'.'+modelName) : null;
    },

    /**
     * @singleton
     * @class Bancha.Localizer
     * Language support for Bancha.
     */
    Localizer: {
        /**
         * @property
         * The default language code for translations.
         * Use the getter and setter methods!
         * (Default: 'eng')
         */
        currentLang: 'eng',
        /**
         * Returns the default language for {@link Bancha.Localizer#getLocalizedString},
         * {@link Bancha.Localizer#getLocalizedStringWithReplacements} and {@link Bancha#t}.
         *
         * @return {String} The three letter code of the current language, as in cakephp, e.g. 'eng'
         */
        getCurrentLanguage: function() {
            return this.currentLang;
        },
        /**
         * Sets a new default language for {@link Bancha.Localizer#getLocalizedString},
         * {@link Bancha.Localizer#getLocalizedStringWithReplacements} and {@link Bancha#t}.
         *
         * @param {String} lang The three letter code of the new language, as in cakephp, e.g. 'eng'
         */
        setCurrentLanguage: function(lang) {
            this.currentLang = lang;
        },
        /**
         * You can use this function to preload translations.
         * @param {String} langCode A three letter language code, same as in cakephp
         *                          (Default is {@link #currentLang} property)
         */
        preloadLanguage: function(langCode) {
            if (!this.locales) {
                this.locales = new Ext.util.HashMap();
            }
            this.loadLocaleStrings(langCode || this.currentLang, true);
        },
        /**
         * @private
         * @param {String} langCode A three letter language code, same as in cakephp
         * @param {Boolean} asnyc False to block while loading (Default: false)
         * @return {Array} the loaded array of translations
         */
        loadLocaleStrings: function(locale, async) {
            var me = this, localeStrings;
            Ext.Ajax.request({
                url: '/bancha/bancha/translations/' + locale + '.js',
                async: async || false,
                success: function(response) {
                    var entries = Ext.decode(response.responseText);
                    localeStrings = new Ext.util.HashMap();
                    Ext.each(entries, function(entry) {
                        localeStrings.add(entry.key, entry.value);
                    });
                    me.locales.add(locale, localeStrings);
                },
                failure: function() {
                    me.locales.add(locale, false);
                    localeStrings = false;
                }
            });
            return localeStrings;
        },
        /**
         * @private
         * @param {String} langCode A three letter language code, same as in cakephp
         * @return {Array} the loaded array of translations
         */
        getLocaleStrings: function(locale) {
            var me = this, localeStrings;
            if (!me.locales) {
                me.locales = new Ext.util.HashMap();
            }
            if (!me.locales.get(locale)) {
                localeStrings = me.loadLocaleStrings(locale);
            } else {
                localeStrings = me.locales.get(locale);
            }
            if (Ext.isBoolean(localeStrings) && !localeStrings) {
                // If locale key contains "false" we
                // tried to load the locale file before
                // but failed.
                return;
            }
            return localeStrings;
        },
        /**
         * Translates an given string to the given language.
         *
         * If no locale is defined the language is taken from {@link #currentLang}.
         *
         * @param {String} key The string to translate
         * @param {String} langCode A three letter language code, same as in cakephp (Default from {@link #currentLang})
         * @return {String} The translated string
         */
        getLocalizedString: function(key, locale) {
            var me = this,
                localeStrings,
                localized;

            key = key + ''; // string conversion

            locale = locale || this.currentLang;
            if (!key || !locale) {
                return key;
            }
            localeStrings = me.getLocaleStrings(locale);
            if (!localeStrings) {
                return key;
            }
            localized = localeStrings.get(key);
            // empty strings are intentional, so just return if it's undefined
            if (!Ext.isString(localized)) {
                return key;
            }
            return localized;
        },
        /**
         * Translates an given string the current language
         * (see {@link #currentLang}).
         *
         * Additional arguments are used to replace %s (for string) and %d (for number).
         *
         * @param {String}    key          The string to translate
         * @param {String...} replacements An arbitrary number of additional strings
         * used to replace %s (for string) and %d (for number) in the key string.
         * @return {String}                The translated string
         */
        getLocalizedStringWithReplacements: function(key, replacement1, replacement2, replacement3) {
            // translate
            key = this.getLocalizedString(key);

            // replace %s and %d
            var bits = key.split('%'),
                result = bits[0],
                i, len;

            //<debug>
            if(bits.length !== arguments.length) { // replacements+first substr should equal key+replacements
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: [
                        'Bancha.Localizer expected for the string "'+key+'" ',
                        (bits.length-1)+' replacement(s), instead got ',
                        (arguments.length-1)+'.'
                    ].join('')
                });
            }
            //</debug>

            for(i=1, len=bits.length; i<len; i++) {
                switch(bits[i].substr(0,1)) {
                case 'd':
                    result += parseInt(arguments[i], 10);
                    break;
                case 's':
                    result += arguments[i];
                    break;
                default:
                    //<debug>
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        msg: 'Bancha.Localizer does not know how to replace %'+bits[i].substr(0,1)+' in string "'+key+'".'
                    });
                    //</debug>
                    result += '%'+bits[i].substr(0,1);
                }
                result += bits[i].substr(1);
            }

            return result;
        }
    },
    /**
     * Translates an given string the current language
     * (see {@link Bancha.Localizer#currentLang}).
     *
     * Additional arguments are used to replace %s (for string) and %d (for number).
     *
     * This is a convenience function for {@link Bancha.Localizer#getLocalizedStringWithReplacements}.
     *
     * @member Bancha
     * @param {String}    key          The string to translate
     * @param {String...} replacements An arbitrary number of additional strings
     * used to replace %s (for string) and %d (for number) in the key string.
     * @return {String}                The translated string
     */
    t: function(key,  replacement1, replacement2, replacement3) {
        return Bancha.Localizer.getLocalizedStringWithReplacements.apply(Bancha.Localizer, arguments);
    }
}, function() {
    /*
     * The Bancha class callback
     * Create deprecated warnings for old Bancha.log.*
     */

    // Ext.Logger might not be available
    var markDeprecated = function(msg) {
        if(Ext.Logger) {
            Ext.Logger.deprecate(msg);
        } else if(Bancha.Logger) {
            Bancha.Logger.warn('[DEPRECATE]'+msg);
        } else if(Ext.global.console && Ext.global.console.warn) {
            Ext.global.console.warn('[DEPRECATE]'+msg);
        }
    };

    // now initialize all deprecated log functions

    /**
     * @class  Bancha.log
     * Deprecated class in favor of {@link Bancha.Logger}
     *
     * @deprecated This will be removed in Bancha 2.1, use Bancha.Logger instead.
     */
    /**
     * {@link Bancha.log#info} is an alias for {@link Bancha.Logger#info}
     *
     * @deprecated This will be removed in Bancha 2.1, use Bancha.Logger instead.
     * @inheritdoc Bancha.Logger#info
     */
    Bancha.log.info = function(msg) {
        markDeprecated('Bancha.log.info is deprecated in favor of Bancha.Logger.info', 1);
        if(Bancha.Logger) {
            Bancha.Logger.info.apply(Bancha.Logger, arguments);
        } else {
            Ext.Logger.info(msg);
        }
    };
    /**
     * {@link Bancha.log#warn} is an alias for {@link Bancha.Logger#warn}
     *
     * @deprecated This will be removed in Bancha 2.1, use Bancha.Logger instead.
     * @inheritdoc Bancha.Logger#warn
     */
    Bancha.log.warn = function(msg) {
        markDeprecated('Bancha.log.warn is deprecated in favor of Bancha.Logger.warn', 1);
        if(Bancha.Logger) {
            Bancha.Logger.warn.apply(Bancha.Logger, arguments);
        } else {
            Ext.Logger.warn(msg);
        }
    };
    /**
     * {@link Bancha.log#error} is an alias for {@link Bancha.Logger#error}
     *
     * @deprecated This will be removed in Bancha 2.1, use Bancha.Logger instead.
     * @inheritdoc Bancha.Logger#error
     */
    Bancha.log.error = function(msg) {
        markDeprecated('Bancha.log.error is deprecated in favor of Bancha.Logger.error', 1);
        if(Bancha.Logger) {
            Bancha.Logger.error.apply(Bancha.Logger, arguments);
        } else {
            Ext.Logger.error(msg);
        }
    };
});
