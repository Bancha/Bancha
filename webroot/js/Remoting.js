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
 * @class Bancha.Remoting
 *
 * This singleton is the core of Bancha's client-server-communication.
 * Currently it only has the necessary properties, in Bancha 2.1 all
 * communication logic from Bancha.Main will be refactored into this
 * class as well.
 *
 * To apply your own error handling please set different handlers
 * at any time in the life cycle. These values are accessed using a
 * facade pattern, therefore it is possible to replace them later
 * in the aplication life cycle and all changes have immediate
 * effects.
 *
 * @singleton
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.Remoting', {

    /* Begin Definitions */
    singleton: true,

    uses: [
        'Bancha.REMOTE_API'
    ],

    /**
     * Returns a function which will execute the at execution time
     * currently configured handler with all arguments given when
     * executed.
     *
     * Example:
     *
     *     var facade = Bancha.Remoting.getFacade('onRemoteException');
     *     Bancha.Remoting.onRemoteException = function() { console.error('remote exception'); }
     *     facade(); // outputs 'remote exception' in the console
     *
     * @since Bancha v 2.0.0
     * @param  {String} handlerName The handler name:
     * @return {[type]}             [description]
     */
    getFacade: function(handlerName) {
        var me = this;

        //<debug>
        if(handlerName!=='onAuthException' && handlerName!=='onError' && handlerName!=='onRemoteException') {
            Ext.Error.raise({
                plugin: 'Bancha',
                msg: [
                    'Bancha: Bancha.Remoting.getFacade(handlerName) expect the ',
                    'handler name to be one the following: "onAuthException", ',
                    '"onError", "onRemoteException". Instead got '+handlerName
                ].join('')
            });
        }
        //</debug>

        return function() {
            if(typeof me[handlerName] === 'function') {
                return me[handlerName].apply(me, arguments);
            }
        };
    },

    /**
     * @property {Function} onAuthException
     * You can define your custom authentification error handler. This function
     * is triggered every time the CakePHP AuthComponent prevented the
     * execution of a Bancha request.
     *
     * This function get's two string parameters:
     *  - exceptionType: This is either 'BanchaAuthLoginException' or 'BanchaAuthAccessRightsException'
     *  - message      : The exception message from the server-side
     *
     * @since Bancha v 2.0.0
     * @param {String} exceptionType This is either 'BanchaAuthLoginException' or 'BanchaAuthAccessRightsException'
     * @param {String} message       The exception message from the server-side
     * @return void
     */
    onAuthException: function(exceptionType, message) {
        var msg = [
            '<b>'+message+'</b><br />',
            'This is triggerd by your AuthComponent configuration. ',
            'You can add your custom authentification error handler ',
            'by setting <i>Bancha.onAuthException(exceptionType,message)</i>.<br />'
        ].join('');

        // Show the error and then throw an exception
        // (don't use Ext.Error.raise, that would trigger cyclic error handling)
        Ext.Msg.show({
            title: 'Bancha: AuthComponent prevented execution',
            message: msg, //touch
            msg: msg, //extjs
            icon: Ext.MessageBox.ERROR,
            buttons: Ext.Msg.OK
        });
        throw new Error(msg);
    },

    /**
     * @property {Function} onError
     * In production mode (or if errors occur when Bancha is not initialized) this function will be called.
     * This function will log the error to the server and then throw it.
     * You can overwrite this function with your own implementation at any time.
     *
     * This function get's one parameter of type Object:
     *  - stackInfo: an TraceKit error object, see [TraceKit](https://github.com/Bancha/TraceKit)
     *
     * @since Bancha v 2.0.0
     * @param {Object} stackInfo an TraceKit error object, see [TraceKit](https://github.com/Bancha/TraceKit)
     * @return void
     */
    onError: function(stackInfo) {

        // just log the error
        // depending on debug level this is logged
        // to the console or to the server
        Bancha.Logger.log(Ext.encode(stackInfo), 'error');
    },

    /**
     * @property {Function} onRemoteException
     * This function will be added to each model to handle execptions from the server.
     *
     * This function get's three parameters:
     *  - {Ext.data.proxy.Proxy} proxy Bancha Model Proxy
     *  - {Object} response The response from the Bancha request
     *  - {Ext.data.Operation} operation The operation that triggered request
     *
     * @param {Ext.data.proxy.Proxy} proxy Bancha Model Proxy
     * @param {Object} response The response from the Bancha request
     * @param {Ext.data.Operation} operation The operation that triggered request
     * @since Bancha v 2.0.0
     */
    onRemoteException: function(proxy, response, operation){
        Ext.Msg.show({
            title: 'REMOTE EXCEPTION',
            message: operation.getError(), //touch
            msg: operation.getError(), //extjs
            icon: Ext.MessageBox.ERROR,
            buttons: Ext.Msg.OK
        });
    }
});
