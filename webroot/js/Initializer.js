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

if(Ext.Loader) {

    // this will only be used in the debug version, the production version should be shipped in a packaged version
    // See our integration in Sencha CMD for this feature.
    if(!Ext.Loader.getConfig('paths')['Bancha.REMOTE_API']) {
        Ext.Loader.setPath('Bancha.REMOTE_API', Ext.Loader.getPath('Bancha')+'/../../bancha-api-class/models/all.js');
    }
    if(!Ext.Loader.getConfig('paths')['Bancha.scaffold']) {
        // Since CakePHP does not follow symlinks we need to setup a second path for Bancha Scaffold
        Ext.Loader.setPath('Bancha.scaffold', Ext.Loader.getPath('Bancha')+'/../BanchaScaffold/src/');
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
