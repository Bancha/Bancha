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

/**
 * @singleton
 * @class Bancha.Logger
 *
 * This class encalpsulates some logging features.
 *
 * For more see {@link Bancha.Logger#log}
 *
 * example usage:
 *
 *     Bancha.log('My error message');
 *     Bancha.log('My info message','info');
 *     Bancha.Logger.info('My info message');
 *     Bancha.Logger.warn('My warning message');
 *     Bancha.Logger.error('My error message');
 *
 */
Ext.define('Bancha.Logger', {
    singleton: true,
    requires: [
        'Bancha.Main'
    ],
    /**
     * This function logs a message.
     *
     * If the CakePHP debug level is 0 (production mode) or forceServerlog is
     * true, and the type is not 'info', then the error will be send to
     * the server and logged to app/tmp/logs/js_error.log (for types
     * 'error' and 'warn') and app/tmp/logs/missing_translation.log
     * (for type 'missing_translation').
     *
     * If the debug level is bigger then zero and a console is availabe
     * it is written to the console. If no console is available it is
     * instead displayed as a Ext.Msg.alert.
     *
     * @param  {String}  message        The error message
     * @param  {String}  type           (optional) Either 'error', 'warn' or 'missing_translation' (default is 'error')
     * @param  {Boolean} forceServerlog (optional) True to write the error to the server, even in debug mode (default to false)
     * @return void
     */
    log: function(message, type, forceServerlog) {
        type = type || 'error';

        if(Bancha.getDebugLevel(0)===0 || forceServerlog) {
            if(type === 'info') {
                return; // don't log info messages
            }

            // log the error to the server

            // if not yet initalized try to initialize
            if(!Bancha.initialized) {
                try {
                    Bancha.init();
                } catch(error) {
                    // Bancha could not be initialized and we are in production mode
                    // suppress everything, and try to write to the console
                    Bancha.Logger.logToConsole(message, type);
                    return;
                }
            }

            // ok, now log it to the server
            var serverType = type==='missing_translation' ? type : 'js_error';
            message = (type==='warn' ? 'WARNING: ' : '') + message;
            Bancha.getStub('Bancha').logError(message, serverType);
            return;
        }

        // in debug mode
        Bancha.Logger.logToBrowser(message, type);

    },
    /**
     * Convenience function for logging with type 'info', see {@link Bancha.Logger#log}
     *
     * @param  {String}  message        The info message
     * @param  {Boolean} forceServerlog (optional) True to write the error to the server, even in debug mode (default to false)
     * @return void
     */
    info: function(message, forceServerlog) {
        Bancha.Logger.log(message, 'info', forceServerlog || false);
    },
    /**
     * Convenience function for logging with type 'warn', see {@link Bancha.Logger#log}
     *
     * @param  {String}  message        The warn message
     * @param  {Boolean} forceServerlog (optional) True to write the error to the server, even in debug mode (default to false)
     * @return void
     */
    warn: function(message, forceServerlog) {
        Bancha.Logger.log(message, 'warn', forceServerlog || false);
    },
    /**
     * Convenience function for logging with type 'error', see {@link Bancha.Logger#log}
     *
     * @param  {String}  message        The error message
     * @param  {Boolean} forceServerlog (optional) True to write the error to the server, even in debug mode (default to false)
     * @return void
     */
    error: function(message, forceServerlog) {
        Bancha.Logger.log(message, 'error', forceServerlog || false);
    },
    /**
     * @private
     * This function writes logging information to the console or browser window.
     *
     * If the console is availabe writes it there. If no console is available
     * it is instead displayed as a Ext.Msg.alert.
     *
     * @param  {String} message The logging message
     * @param  {String} type    Either 'error', 'warn', 'info' or 'missing_translation' (default is 'error')
     * @return void
     */
    logToBrowser: function(message, type) {
        type = type || 'error';
        var typeText = type.replace(/_/,' ').toUpperCase();

        if(Ext.global.console && typeof Ext.global.console.log === 'function') {
            // just use the console
            Bancha.Logger.logToConsole(message, type);
        } else {
            // There is no console, use alert
            Ext.Msg.alert(typeText, message);
        }
    },
    /**
     * @private
     * A wrapper for the window.console method
     *
     * @param  {String} message The logging message
     * @param  {String} type    Either 'error', 'warn', 'info' or 'missing_translation' (default is 'error')
     * @return void
     */
    logToConsole: function(message, type) {
        type = type || 'error';
        var typeText = type.replace(/_/,' ').toUpperCase();

        if(type==='error' && typeof Ext.global.console.error === 'function') {
            Ext.global.console.error(message);
            return;
        }
        if((type==='warn' || type==='missing_translation') && typeof Ext.global.console.warn === 'function') {
            Ext.global.console.warn((type==='missing_translation' ? 'MISSING TRANSLATION: ' : '') + message);
            return;
        }
        if(type==='info' && typeof Ext.global.console.info === 'function') {
            Ext.global.console.info(message);
            return;
        }

        // there is no specific log function, use default log
        Ext.global.console.log(typeText+': '+message);
    }
});
