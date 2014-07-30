/*!
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://bancha.io
 */

/**
 * @private
 * @class Bancha.Loader
 *
 * The Sencha class loading is a beautiful concept and brings an enormous power to
 * javascript. But the Sencha class loader does not allow any custom behavior.
 *
 * Since Bancha creates models and stores dynamically from server models the
 * Sencha conventions does not apply for loading these classes. We will utilize
 * Bancha.loader.Models to load these elements.
 *
 * This loader incorporates the Java class loader principles of Delegation and
 * Uniqueness, while still keeping all Visibility in the main Ext.Loader.
 *
 * This class now simply extends {@class Ext.Loader} to allow the usage of custom
 * loaders, while the actual Bancha loading logic lies in {@Bancha.loader.Models}.
 *
 * See also http://bancha.io/blog-entry/building-a-customer-class-loader-for-sencha.html
 *
 * @since Bancha v 2.0.0
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.Loader', {
    requires: 'Ext.Loader'
}, function() {

    // Ext.Loader is a singleton,
    // so we need to directly apply the overrides
    Ext.apply(Ext.Loader, {

        /**
         * @private
         * @member Bancha.Loader
         * @cfg {Bancha.loader.Interface} current default class loader
         */
        defaultLoader: null,

        /**
         * @private
         * @member Bancha.Loader
         *
         * Registers a new Loader as the default loader inside
         * the whole application.
         *
         * To create your own loader extend {class Bancha.loader.Interface}
         * and add your own logic. You might chain loaders.
         *
         * @param  {Bancha.loader.Interface} loader The loader to use
         * @return {void}
         */
        setDefaultLoader: function(loader) {
            this.defaultLoader = loader;
        },

        /**
         * @private
         * @member Bancha.Loader
         *
         * Returns the currently set up loader.
         *
         * @return {Bancha.loader.Interface|null} application class loader
         */
        getDefaultLoader: function() {
            return this.defaultLoader;
        },

        /**
         * @private // the override is private
         * @member Bancha.Loader
         *
         * The highjacked #loadScriptFile doesn't get the className as
         * argument, only the filePath. Inside #require #getPath is
         * called.
         * Therefore we keep track of the last used className for
         * #getPath to use it in our #loadScriptFile interceptor.
         * Yes, this is a dirty hack. But it prevents a lot of
         * code duplication and update issues.
         *
         */
        getPath: Ext.Function.createInterceptor(Ext.Loader.getPath, function(className) {
            this.getPathLastClassName = className;
        }, Ext.Loader),

        /**
         * @private
         * @member Bancha.Loader
         *
         * For Sencha Touch and Ext JS 4.
         * 
         * The original method Loads a script file, supports both asynchronous and
         * synchronous approaches.
         *
         * Bancha.Loader adds the logic to also use other loaders, which are set
         * using #setDefaultLoader.
         *
         * @param  {String}   url         The url to load data from, see also #getPath
         * @param  {Function} onLoad      To be executed when the class was successfully loaded.
         * @param  {Function} onError     To be executed is something went wrong.
         * @param  {Object}   scope       The scope to use for onLoad and onError
         * @param  {Boolean}  synchronous True is the file should be loaded synchronous.
         */
        loadScriptFile: Ext.Function.createInterceptor(Ext.Loader.loadScriptFile,
            function(url, onLoad, onError, scope, synchronous) {

            //<debug>
            if(Ext.versions.extjs && Ext.versions.extjs.major===5) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: [
                        'Bancha Internal Error: Ext.Loader.loadScriptFile was called in a Ext JS 5 application. ',
                        'This should never happen, please report this on https://github.com/Bancha/Bancha/issues. ',
                        'Thanks a lot!'
                    ].join()
                });
            }
            //</debug>

            // from original function
            if (this.isFileLoaded[url]) {
                return this;
            }
            this.isLoading = true;

            // see getPath override
            var className = this.getPathLastClassName,
                customClassLoader;

            // Ext JS 4.0.7 returns undefined instead of false, fix this
            synchronous = synchronous || false;

            // if we have a default class loader set, use it
            if(this.getDefaultLoader()) {
                // see if the child class loader wants to handle this
                customClassLoader = this.getCustomClassLoaderForClassName(className);

                if(customClassLoader) {
                    customClassLoader.loadClass(className, onLoad, onError, scope, synchronous);
                    return false; // don't call the original fn
                }
            }

            //ok, delegate to the original fn
            return true;
        }, Ext.Loader), //eo loadScriptFile


        /**
         * @private
         * @member Bancha.Loader
         * 
         * Returns the classloader which can handle loading of the class,
         * or null if it should be delegated to the Sencha one.
         *
         * @param {string} className The class name to load
         * @return {Bancha.loader.Interface|null} The loader handling this class name
         */
        getCustomClassLoaderForClassName: function(className) {
            // see if the child class loader wants to handle this
            var current = this.getDefaultLoader();
            if(current.handles(className)) {
                return current;
            }
            while(current.getParentLoader()) {
                current = current.getParentLoader();
                if(current.handles(className)) {
                    return current;
                }
            }

            return null;
        },

        /**
         * @private
         * @member Bancha.Loader
         *
         * For Ext JS 5.
         * 
         * The original method Loads a script file, supports both asynchronous and
         * synchronous approaches.
         *
         * Bancha.Loader adds the logic to also use other loaders, which are set
         * using #setDefaultLoader.
         *
         * 
         * This is an internal method that delegate content loading to the 
         * bootstrap layer.
         * @private
         * @param params
         */
        loadScripts: Ext.Function.createInterceptor(Ext.Loader.loadScripts,
            function(params) {

            //<debug>
            if(Ext.versions.extjs && Ext.versions.extjs.major===4) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: [
                        'Bancha Internal Error: Ext.Loader.loadScripts was called in a Ext JS 4 application. ',
                        'This should never happen, please report this on https://github.com/Bancha/Bancha/issues. ',
                        'Thanks a lot!'
                    ].join()
                });
            }
            if(Ext.versions.touch) {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    msg: [
                        'Bancha Internal Error: Ext.Loader.loadScripts was called in a Sencha Touch application. ',
                        'This should never happen, please report this on https://github.com/Bancha/Bancha/issues. ',
                        'Thanks a lot!'
                    ].join()
                });
            }
            //</debug>

            var me = this,
                i = 0,
                className,
                customClassLoader;

            // if we don't have a default class loader set, nothing to do
            if(!this.getDefaultLoader()) {
                return true;
            }

            // check for each class
            while(i < params._classNames.length) { // always re-evaluate the array size
                className = params._classNames[i];

                // see if the child class loader wants to handle this
                customClassLoader = this.getCustomClassLoaderForClassName(className);

                if(customClassLoader) {

                    // update the counter
                    ++me.scriptsLoading;

                    // verify the loading state, as this may have transitioned us from
                    // not loading to loading
                    me.checkReady();

                    // handle the loading
                    // the callbacks will then decrease Loader.scriptsLoading
                    customClassLoader.loadClass(
                        className, // class
                        function() {  // success callback
                            //<debug>
                            // the classname is enclosed, that's why we create the function inside the loop
                            if(me.classesLoading) { // guard against that Ext JS production version is used with Bancha debug version
                                Ext.Array.remove(me.classesLoading, className);
                            }
                            //</debug>

                            me.onLoadSuccess.apply(me, arguments);
                        },
                        me.extjs5LoadScriptsErrorHandlerWrapper, // failure callback
                        me, // scope
                        me.syncModeEnabled // sync mode
                    );

                    // remove this class from loading,
                    // therefore "i" will point to the next element
                    params._classNames.splice(i, 1);
                    params.url.splice(i, 1);
                } else {
                    // this class is handled by Ext.Loader,
                    // check next class name
                    i++;
                }
            }

            //ok, delegate to the original fn, if there are still classes to load
            return !!params._classNames.length;
        }, Ext.Loader), //eo loadScripts

        /**
         * @private
         * @member Bancha.Loader
         *
         * Output a custom error message with additional information,
         * before propagating the error to Ext.Loader
         *
         * @param {string} errorMsg The error message create by Bancha.loader.Models
         * @return {void}
         */
        extjs5LoadScriptsErrorHandlerWrapper: function(errorMsg) {
            // log what exactly failed to load
            //<debug>
            Ext.Logger.error(errorMsg);
            //</debug>

            this.onLoadFailure();
        }
    });
});
