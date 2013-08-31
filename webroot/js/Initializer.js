/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

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
 * For more information see the {@link Bancha} singleton.
 *
 * @since Bancha v 2.0.0
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
//<debug>
if(Ext.Loader) {

    // this will only be used in the debug version, the production version should be shipped in a packaged version
    // See our integration in Sencha CMD for this feature.
    if(!Ext.Loader.getConfig('paths')['Bancha.REMOTE_API']) {
        Ext.Loader.setPath('Bancha.REMOTE_API', Ext.Loader.getPath('Bancha')+'/../../bancha-api-class/models/all.js');
    }
    if(!Ext.Loader.getConfig('paths')['Bancha.scaffold']) {
        // Since CakePHP does not follow symlinks we need to setup a second path for Bancha Scaffold
        Ext.Loader.setPath('Bancha.scaffold', Ext.Loader.getPath('Bancha')+'/scaffold/src');
    }
}
//</debug>

if(Ext.versions.extjs && Ext.versions.extjs.shortVersion<410) {
    Ext.syncRequire('Bancha.REMOTE_API'); // ext js: loader sees undefined as already loaded
}

Ext.define('Bancha.Initializer', {
    requires: [
        'Bancha.Loader',
        'Bancha.loader.Models'
    ]
}, function() {
        // initialize the Bancha model loader.
        Ext.Loader.setDefaultLoader(Bancha.loader.Models);

        // For Sencha Architect support
        // now that we are initialized, we want to inject Bancha schema in
        // all models with a config 'bancha' set to true
        if(Ext.versions.touch) {

            /*
             * For Sencha Touch:
             *
             * Every time a new subclass is created, this function will apply all Bancha
             * model configurations.
             *
             * In the debug version it will raise an Ext.Error if the model can't be
             * or is already created, in production it will only return false.
             */
            Ext.ClassManager.registerPostprocessor('banchamodel', function(name, cls, data) {
                var prototype = cls.prototype;
                if(!prototype || !prototype.isModel) {
                    return; // this is not a model instance
                }
                if(!prototype.getBancha || (prototype.getBancha()!==true && prototype.getBancha()!=='true')) {
                    return; // there is no bancha config set to true
                }

                // inject schema
                Bancha.data.Model.applyCakeSchema(cls);
            }, true);

        } else {

            /*
             * For Ext JS:
             *
             * Hook into Ext.data.Model.
             * We can't use the #onExtended method, since we need to be the first
             * preprocessor.
             *
             * In the debug version it will raise an Ext.Error if the model can't be
             * or is already created, in production it will only return false.
             */
            Ext.data.Model.$onExtended.unshift({
                fn: function(cls, data, hooks) {
                    if(data.bancha !== true && data.bancha !== 'true') {
                        return; // not a Bancha model
                    }

                    Bancha.data.Model.applyCakeSchema(cls, data);
                },
                scope: this
            });
        }
    }
); //eo define
