/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
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

    if(!window.Ext) {
        // Sencha Architect should exclude scripts marked with x-bootstrap from production builds
        // Currently this works fine in Sencha Touch, but does not work in Ext JS.
        // Since at this point in time not even Ext JS is ready we can simply make the 
        // ScriptTagInitialier do nothing by adding this if clause. There is a minimal overhead,
        // which is ok until Sencha fixed this bug.
        return;
    }

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
    // if a spacific path for each class is set, this is Sencha Architect 3
    var paths = Ext.Loader.getConfig('paths');
    if(paths.Bancha && !paths['Bancha.Initializer']) {
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
                'no such script tag in the DOM. Please make sure you have ',
                'configured everything correct. Probably you are using Sencha ',
                'Architect, and forgot to check the x-bootstrap config for the ',
                'ScriptTagInitializer. If not, please contact the Bancha ',
                'support via support@banchaproject.org!'
            ].join('')
        });
    }

    Ext.Loader.setPath('Bancha', path);

    if(paths['Bancha.Initializer']) {
        // Sencha Cmd 4 (probably with Sencha Architect 3)
        // Since there is a specific path for each file, Sencha Architect 3 with Sencha Cmd 4 is used
        // The new Sencha Cmd 4 writes all class pathes in bootstrap.js. Since Bancha files are outside
        // the webroot, the filesystem and dynamic web-loaded pathes don't match.
        // So reset all those pathes for dynamic loading.
        for (var cls in paths) {
            if (paths.hasOwnProperty(cls) && cls.substr(0,7)==='Bancha.') {
                // for dynamic loading remove static class path definitions
                delete paths[cls];
            }
        }

        // otherwise the class path aliases use the basepath to load Bancha
        delete Ext.ClassManager.maps.alternateToName['Bancha.Main'];
    }

    Ext.syncRequire('Bancha.Initializer');

}()); //eo closure
//</debug>
