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

/*
 * Bancha.ScriptTagInitializer
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

//<debug>
(function() { //closure over variables

    if(!Ext.Loader) {
        throw 'Bancha expects the Ext.Loaded to be loaded when starting the ScriptTagInitializer.js';
    }

    if(Ext.versions.extjs && Ext.versions.extjs.shortVersion<410) {
        throw 'Bancha Support for Sencha Architect requires at least Ext JS 4.1.0';
    }

    // Ext JS 4.1.0 has the loader disabled by default
    // newer versions already have it enabled
    Ext.Loader.setConfig('enabled', true);

    // This script was included via a script tag. So define the path to Bancha
    // for Ext.Loader.
    // this will only be used in the debug version, the production version
    // should be shipped in a packaged version. See our integration in
    // Sencha CMD for this feature.

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

    // find the path to Bancha
    var path = false;
    Ext.Array.forEach(Ext.DomQuery.select('script'), function(scriptTag) {
        var src = scriptTag.src;
        if(src.substr(src.length-24) === '/ScriptTagInitializer.js') {
            // this seems to be the current script
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
            path = src.substr(0, src.length-24);
        }
    });

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

    Ext.Loader.setPath('Bancha', path);

    Ext.syncRequire('Bancha.Initializer');

}()); //eo closure
//</debug>
