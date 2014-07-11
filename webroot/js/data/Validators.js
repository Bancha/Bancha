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
 * @private
 * @class Bancha.data.Validators
 *
 * This class adds additional validation rules used by Bancha.
 * 
 * For Ext JS 5 it adds a File validation class,
 * for Ext JS 4 and Sencha Touch it adds a range and 
 * file validation rule to Ext.data.validations.
 *
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.Validators', {
    alternateClassName: [
        'Bancha.scaffold.data.Validators' // Bancha.Scaffold uses the same class
    ]
}, function() {

    //<if ext>
    if(Ext.versions.extjs && Ext.versions.extjs.major === 5) {
        // Ext JS 5 doesn't have a validations class anymore, 
        // so use the range validator and add a file validator
        Ext.syncRequire('Bancha.data.validator.File');

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
        Ext.define('Ext.data.validator.override.Range', {
            override: 'Ext.data.validator.Range'
        }, function() {
            // for some reason setting via config doesn't work
            this.prototype.setNanMessage('Must be a number');
        });

    } else {
    //</if>
        Ext.syncRequire('Bancha.data.override.Validations');
    //<if ext>
    }
    //</if>
});
