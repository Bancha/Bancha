/*!
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 2.4.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://bancha.io
 */

/**
 * @private
 * @class Bancha.data.validator.override.Bound
 * 
 * Fixes issues with the current Range validator
 *
 * See http://www.sencha.com/forum/showthread.php?288168
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.validator.override.Bound', {
    override: 'Ext.data.validator.Bound',
    /**
     * @class Ext.data.validator.Bound
     *
     * To normalize the CakePHP, Ext JS 4 and Ext JS 5 validation
     * handling Bancha adds an additional check to the Ext JS 5
     * Bound validation rules.
     *
     * The effect is that non-numeric values are invalid using
     * the Range validation rule. For the error message an 
     * additional config is introduced.
     */
    config: {
        /**
         * @cfg {String} nanMessage
         * The error message to return when the value is not a number.
         */
        nanMessage: 'Must be a number'
    },
    validate: function(value) {
        if(isNaN(this.getValue(value))) {
            return this._nanMessage;
        }
        return this.callParent(arguments);
    },
    getValue: function(value) {
        return parseFloat(value);
    }
});
