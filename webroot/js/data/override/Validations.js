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
 * @class Bancha.data.override.Validations
 *
 * Ext JS 4 and Sencha Touch range and file 
 * validation rules.
 *
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.override.Validations', {
    requires: ['Ext.data.validations'],
    alternateClassName: [
        'Bancha.scaffold.data.override.Validations' // Bancha.Scaffold uses the same class
    ]
}, function() {

    // helper function for Bancha.data.override.validations
    var filenameHasExtension = function(filename,validExtensions) {
        if(!filename) {
            return true; // no file defined (emtpy string or undefined)
        }
        if(!Ext.isDefined(validExtensions)) {
            return true;
        }
        var ext = filename.split('.').pop();
        return Ext.Array.contains(validExtensions,ext);
    };

    // Ext.Logger might not be available
    var markDeprecated = function(msg) {
        if(Ext.Logger && Ext.Logger.deprecate !== Ext.emptyFn) {
            Ext.Logger.deprecate(msg);
        } else if(Bancha.Logger) {
            Bancha.Logger.warn('[DEPRECATE]'+msg);
        } else if(Ext.global.console && Ext.global.console.warn) {
            Ext.global.console.warn('[DEPRECATE]'+msg);
        }
    };

    /**
     * @class Ext.data.validations
     *
     * For Sencha Touch and Ext JS 4:
     * Bancha extends Ext.data.validations with two new validation rules:
     * *range* and *file*.
     *
     * These custom validations are mapped from CakePHP.
     *
     * For Ext JS 5 see {@class Bancha.data.validator.File}
     *
     * @author Roland Schuetz <mail@rolandschuetz.at>
     * @docauthor Roland Schuetz <mail@rolandschuetz.at>
     */
    Ext.apply(Ext.data.validations, { // this is differently called in Ext JS 4 and Sencha Touch, but work by alias just fine

        /**
         * @property
         * @deprecated In favor of the validation rule range.
         * 
         * The default error message used when a range validation fails.
         */
        numberformatMessage: 'is not a number or not in the allowed range',

        /**
         * @property
         * The default error message used when a range validation fails.
         */
        rangeMessage: 'is not a number or not in the allowed range',

        /**
         * @property
         * The default error message used when a file validation fails.
         */
        fileMessage: 'is not a valid file',

        /**
         * @method
         * @deprecated In favor of the validation rule range.
         * 
         * Validates that the number is in the range of min and max.
         * Precision is not validated, but it is used for differenting int from float,
         * also it's metadata for scaffolding.
         *
         * For example:
         *     {type: 'numberformat', field: 'euro', precision:2, min:0, max: 1000}
         */
        numberformat: function(config, value) {
            markDeprecated('Bancha: Validation rules "numberformat" is deprecated in favor of "range" for Ext JS 5 compatibility.');
            if(typeof value !== 'number') {
                value = (config.precision===0) ? parseInt(value, 10) : parseFloat(value);
                if(typeof value !== 'number') {
                    return false; // could not be converted to a number
                }
            }
            if((Ext.isDefined(config.min) && config.min > value) || (Ext.isDefined(config.max) && value > config.max)) {
                return false; // not in the range
            }
            return true;
        },

        /**
         * @method
         * Validates that the number is in the range of min and max.
         * Precision is not validated, but it is used for differenting int from float,
         * also it's metadata for scaffolding.
         *
         * For example:
         *     {type: 'range', field: 'euro', precision:2, min:0, max: 1000}
         */
        range: function(config, value) {
            if(typeof value !== 'number') {
                value = (config.precision===0) ? parseInt(value, 10) : parseFloat(value);
                if(typeof value !== 'number') {
                    return false; // could not be converted to a number
                }
            }
            if((Ext.isDefined(config.min) && config.min > value) || (Ext.isDefined(config.max) && value > config.max)) {
                return false; // not in the range
            }
            return true;
        },

        /**
         * @method
         * Validates that the given filename is of the configured extension. Also validates
         * if no extension are defined and empty values.
         *
         * For example:
         *     {type: 'file', field: 'avatar', extension:['jpg','jpeg','gif','png']}
         */
        file: function(config, value) {
            return filenameHasExtension(value,config.extension);
        }
    }); //eo apply
});
