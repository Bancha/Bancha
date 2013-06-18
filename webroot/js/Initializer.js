
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
/*global Ext:false, Bancha:true, TraceKit:false, window:false */

/**
 * @class Bancha.Initializer
 *
 * Initializes all core features of Bancha.  
 * 
 * This needs to be loaded synchronously to make sure that 
 * all required Bancha models are loaded using the Bancha 
 * loader.  
 *
 * This class does not require any method, all necesarry 
 * adjustments are done during initialization.  
 *
 * @since Bancha v 2.0.0
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */

if(Ext.Loader) {
    // these lines should be removed after some refactoring
    Ext.Loader.setPath('Bancha.Main', Ext.Loader.getPath('Bancha')+'/Bancha.js');
    Ext.Loader.setPath('Bancha.Logger', Ext.Loader.getPath('Bancha')+'/Bancha/js/Bancha.js');
    Ext.Loader.setPath('Bancha.data.Model', Ext.Loader.getPath('Bancha')+'/Bancha/js/Bancha.js');
    Ext.Loader.setPath('Bancha.data.writer.JsonWithDateTime', Ext.Loader.getPath('Bancha')+'/Bancha/js/Bancha.js');
    Ext.Loader.setPath('Bancha.data.override.Validations', Ext.Loader.getPath('Bancha')+'/Bancha/js/Bancha.js');

    // this will only be used in the debug version, the production version should be shipped in a packaged version
    // See our integration in Sencha CMD for this feature.
    if(!Ext.Loader.getConfig('paths')['Bancha.REMOTE_API']) {
        Ext.Loader.setPath('Bancha.REMOTE_API', '/bancha-api-class/models/all.js');
    }
}

Ext.define('Bancha.Initializer', {
    requires: [
        'Bancha.Loader',
        'Bancha.loader.Models'
    ]
}, function() {
        // initialize the Bancha model loader.
        Ext.Loader.setDefaultLoader(Bancha.loader.Models);
    }
); //eo define
