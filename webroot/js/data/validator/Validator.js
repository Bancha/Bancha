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
 * @class Bancha.data.validator.Validator
 * 
 * Sencha CMD is looking for a Ext.data.validations class since
 * it is required in a a class which Ext JS 5 would actually never 
 * touch.
 *
 * To fake this class for Ext JS 5 and make Sencha CMD happy this
 * is the useless Ext JS 5 Ext.data.validations class.
 */
Ext.define('Ext.data.validator.Validator', {});
