/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.0.2
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

/**
 * @class Bancha.data.validator.File
 * @extends Ext.data.validator.Validator
 *
 * For Ext JS 5:
 * Validates that the filename is one of given {@link #extension}.
 *
 * For Ext JS 4 see {@class Ext.data.validations}
 */
Ext.define('Bancha.data.validator.File', {
    extend: 'Ext.data.validator.Validator',
    alias: 'data.validator.file',
    alternateClassName: [
        'Bancha.scaffold.data.validator.File' // Bancha.Scaffold uses the same class
    ],
    
    type: 'file',
    
    config: {
        /**
         * @cfg {String} message
         * The error message to return when the value is not a valid email
         */
        message: 'is not a valid file',
        /**
         * @cfg {Array} extensions
         * The allowed filename extensions.
         */
        extensions: [],
        /**
         * @cfg {Array} extension
         * @deprecated Please use #extensions instead
         * Backwards compatibility with Bancha before 2.3
         */
        extension: false
    },

    /**
     * Validates that the given filename is of the configured extension. Also validates
     * if no extension are defined and empty values.
     * 
     * @param {Object} value The value
     * @param {Ext.data.Model} record The record
     * @return {Boolean/String} `true` if the value is valid. A string may be returned if the value 
     * is not valid, to indicate an error message. Any other non `true` value indicates the value
     * is not valid.
     */
    validate: function(filename, record) {
        var validExtensions = this.getExtension() || this.getExtensions();
        if(!filename) {
            return true; // no file defined (emtpy string or undefined)
        }
        if(!Ext.isDefined(validExtensions)) {
            return true;
        }
        var ext = filename.split('.').pop();
        return Ext.Array.contains(validExtensions,ext) || this.getMessage();
    }
});
