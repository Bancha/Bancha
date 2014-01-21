/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * Ext JS and Sencha Touch specific extension of the jasmin Mock helper
 *
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

// Replacing proxies makes problems, it sure why
// For more detaIls see http://www.sencha.com/forum/showthread.php?188764-How-to-mock-a-proxy&p=981245#post981245
// This is a quick fix
Ext.define('Ext.data.proxy.override.Direct', {
    override: 'Ext.data.proxy.Direct',
    destroy: Ext.emptyFn
});

/**
 * Mock.Proxy extends the Mock object and provides
 * an easier interface for Ext JS testing
 */
Mock.Proxy = (function() {

    // extend Mock
    var proxyPrototype = new Mock();

    // Sencha Touch checks if this is already a class with the property
    // See http://www.sencha.com/forum/showthread.php?188764-How-to-mock-a-proxy
    proxyPrototype.isInstance = true;

    // setModel is always called when creating
    // an Proxy from model/store, totally unimportant for us
    proxyPrototype.setModel = function(model) {
        // make sure that Sencha Touch does not use Caching
        if(model.setUseCache) { model.setUseCache(false); }
    };

    // provide a destroy function if the mock is replace by another proxy
    proxyPrototype.destroy = Ext.emptyFn;

    // add an expectRPC method to the proxy
    proxyPrototype.expectRPC = function(method, /*Optional*/firstArg) {
        // expect ext direct call
        this.expect(method).withArguments(
            firstArg || Mock.Value.Object,
            Mock.Value.Function,
            Mock.Value.Object
        );
    };

    // looks like the server has answered with some data
    proxyPrototype.callLastRPCCallback = function(method,args) {
        if(!this[method] || !this[method].mostRecentCall || !this[method].mostRecentCall.args) {
            throw 'The mock was not called yet!';
        }

        // undefined as data is allowed
        args = args || [];

        var methodArgs     = this[method].mostRecentCall.args,
            callback = methodArgs[1],
            scope    = methodArgs[2];

        callback.apply(scope,args);
    };

    // fake proxy property for ext
    proxyPrototype.isProxy = true;

    // constructor
    return function() {
        return Object.create(proxyPrototype);
    };
}());
