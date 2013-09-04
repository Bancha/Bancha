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
 * @private
 * @class Bancha.loader.Models
 *
 * Since Bancha creates models and stores dynamically from server models the
 * Sencha conventions does not apply for loading these classes.
 *
 * This loader handles all via Bancha loaded models.
 *
 * @since Bancha v 2.0.0
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.loader.Models', {
    extend: 'Bancha.loader.Interface',
    singleton: true,

    requires: [
        'Bancha.Main',
        'Bancha.REMOTE_API'
    ],

    /**
     * Return true to handle loading of stores and models in the Bancha namespace
     *
     * @param  {String} className
     * @return {Boolean} true to handle the loading
     */
    handles: function(className) {
        return className.substr(0,13) === 'Bancha.model.';
    },
    /**
     * Handle loading of Bancha models.
     *
     * @inheritdoc Bancha.loader.Interface#loadClass
     */
    loadClass: function(className, onLoad, onError, scope, syncEnabled) {
        // Bancha is used to handling model names without a namespace
        var unqualifiedName = className.substr(13);

        // in case everything is loaded
        if(Bancha.modelMetaDataIsLoaded(unqualifiedName)) {
            // metadata is already present, simply instanciate
            Bancha.getLoadedModel(unqualifiedName);
            // model is ready
            onLoad.call(scope);
            return;
        }

        // delegate both sync or async loading to the Bancha core
        Bancha.loadModelMetaData([unqualifiedName], function(success, errorMsg) {
            if(success) {
                // metadata is already present, simply instanciate
                Bancha.getLoadedModel(unqualifiedName);
                // model is ready
                onLoad.call(scope);
            } else {
                // error handling
                //<debug>
                // signature: errorMessage, isSynchronous
                var mode = syncEnabled ? 'synchronously via XHR' : 'asynchronously via Ext.Direct';
                onError.call(this, [
                    'Failed loading '+mode+': \'' + className + '\'; please ',
                    'verify that the CakePHP model '+unqualifiedName+' exists. ',
                    errorMsg
                ].join(''), syncEnabled);
                //</debug>
            }
        }, this, syncEnabled);
    }
});
