/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: false, plusplus: true, white: true, sloppy: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false, strict:false */
/*global Ext:false, Bancha:true */

/**
 * @private
 * @class Bancha.loader.Interface
 * 
 * A base class for implementing alternative loaders for 
 * {@class Ext.Loader}. These can be chained.  
 *
 * Defining your own loader requires you to implement 
 * #handles and #loadClass. Also set the #singleton 
 * property to true.  
 *
 * Then call Ext.Loader.setDefaultLoader(loader);  
 *
 * For more information see {@class Bancha.Loader}.
 * 
 * @since Bancha v 2.0.0
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.loader.Interface', {
    
    config: {
		/**
         * @cfg {Bancha.loader.Interface|null}
         * Define from which loader this one is the child. This property
         * is optional and allows chaining of loaders.
         */
        parentLoader: null
    },
    /**
     * This function will be called to find the correct loader 
     * for handling the class loading. Return true to handle 
     * loading of this class. Otherwise is will be delegated
     * to the parent loader.
     * 
     * @param  {String} className
     * @return {Boolean} true to handle the loading
     */
    handles: function(className) {
        return false;
    },
    /**
     * This function will be called every time a class needs to be loaded
     * and the #handles(classname) returned true.  
     * 
     * Make sure that your implementation supports both asynchronous and
     * synchronous approaches.  
	 *
	 * @param  {String}   className   The class name to load
	 * @param  {Function} onLoad      To be executed when the class was successfully loaded.
	 * @param  {Function} onError     To be executed is something went wrong.
	 * @param  {Object}   scope       The scope to use for onLoad and onError
	 * @param  {Boolean}  syncEnabled True is the file should be loaded synchronous.
     */
    loadClass: function(className, onLoad, onError, scope, syncEnabled) {}
});