/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 StudioQ OG
 *
 * ExtJS and Sencha Touch specific extension of the jasmin Mock helper
 *
 * @copyright     Copyright 2011-2013 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Mock */


/**
 * Mock.Proxy extends the Mock object and provides 
 * an easier interface for ExtJS testing
 */
Mock.Proxy = (function() {
    
    // extend Mock
    var proxyPrototype = new Mock();
    
    
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
            throw "The mock was not called yet!";
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
        var proxy = Object.create(proxyPrototype);
        
        // setModel is always called when creating 
        // an Proxy from model/store, totally unimportant for us
        proxy.setModel = function() {};
        return proxy;
    };
}());

//eof
