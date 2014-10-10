/*!
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @since         Bancha v 0.0.2
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://bancha.io
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
 * The only point of this class is that the the require
 * is easier to write for scaffold apps then just requiring multiple
 * classes.
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.Validators', {
    alternateClassName: [
        'Bancha.scaffold.data.Validators' // Bancha.Scaffold uses the same class
    ],
    requires: [
        // Ext JS 5 doesn't have a validations class anymore, 
        // so use the range validator and add a file validator
        'Bancha.data.validator.File',
        'Bancha.data.validator.override.Bound',
        'Bancha.data.validator.override.Range',
        //Ext JS 4
        'Bancha.data.override.Validations'
    ]
});
