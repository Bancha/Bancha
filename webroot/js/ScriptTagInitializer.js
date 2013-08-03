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
 * Initializes all core features of Bancha. This script can
 * be included as script tag and is currently used for
 * Sencha Architect 2 integration.
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

// If the Ext.Loader is enabled, configure it
if(Ext.Loader && Ext.Loader.getConfig('enabled')) {

    // This script was included via a script tag. So define the path to Bancha
    // for Ext.Loader.
    // this will only be used in the debug version, the production version
    // should be shipped in a packaged version. See our integration in
    // Sencha CMD for this feature.

    //<debug>
    var logWarning = function(msg) {
        if(Ext.global.console && Ext.global.console.warn) {
            Ext.global.console.warn(msg);
        } else if(Ext.global.console && Ext.global.console.error) {
            Ext.global.console.error('Warning: '+msg);
        } else if(Ext.global.console && Ext.global.console.info) {
            Ext.global.console.info('Warning: '+msg);
        } else if(Ext.global.alert) {
            Ext.global.alert('Warning: '+msg);
        }
    };

    // Ext.Loader#getConfig is used instead of Ext.Loader#getPath to be sure
    // that it actually is set, not generated
    if(Ext.Loader.getConfig('paths').Bancha) {
        logWarning([
            'Bancha: You are using the ScriptTagInitializer, but the path to ',
            'Bancha is already set. This looks like you should include the ',
            'default Initializer. The script tag initializer is only thought ',
            'for Integration with Sencha Architect 2.'
        ].join(''));
    }
    //</debug>


    // find the path to Bancha
    var path = false;
    Ext.Array.forEach(Ext.DomQuery.select('script'), function(scriptTag) {
        var src = scriptTag.src;
        if(src.substr(src.length-24) === '/ScriptTagInitializer.js') {
            // this seems to be the current script
            //<debug>
            if(path!==false) {
                logWarning([
                    'Bancha: You seem to have included to different files with ',
                    'the name ScriptTagInitializer.js. Bancha is not able to ',
                    'auto-detect the path to all Bancha files. This is a very ',
                    'unlikely case, please make sure you have configured ',
                    'everything correct. If this is really an issue, please ',
                    'contact the Bancha support via support@banchaproject.org!'
                ].join(''));
            }
            //</debug>
            path = src.substr(0, src.length-24);
        }
    });

    //<debug>
    if(path === false) {
        Ext.Error.raise({
            plugin: 'Bancha',
            msg: [
                'Bancha: You included the ScriptTagInitializer, but there is ',
                'no such script tag in the DOM. please make sure you have ',
                'configured everything correct. If this is really an issue, ',
                'please contact the Bancha support via ',
                'support@banchaproject.org!'
            ].join('')
        });
    }
    //</debug>

    Ext.Loader.setPath('Bancha', path);
    Ext.Loader.setPath('Bancha.REMOTE_API', path+'/../../bancha-api-class/models/all.js');
    // Since CakePHP does not follow symlinks we need to setup a second path for Bancha Scaffold
    Ext.Loader.setPath('Bancha.scaffold', Ext.Loader.getPath('Bancha')+'/scaffold/src');
}

// to make sure that everything is loaded in the right order we force
// that Bancha.Initializer can be loaded right away
Ext.syncRequire([
    'Bancha.Loader',
    'Bancha.loader.Models',
    'Bancha.data.Model'
]);
Ext.define('Bancha.Initializer', {
    requires: [
        'Bancha.Loader',
        'Bancha.loader.Models'
    ]
}, function() {
        // initialize the Bancha model loader.
        Ext.Loader.setDefaultLoader(Bancha.loader.Models);
    }
);

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
        if(!prototype.isModel) {
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
