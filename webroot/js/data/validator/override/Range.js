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
 * @class Bancha.data.validator.override.Range
 * 
 * Fixes issues with the current Range validator
 *
 * See http://www.sencha.com/forum/showthread.php?288168
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.validator.override.Range', {
    override: 'Ext.data.validator.Range',
    requires: [
        'Bancha.data.validator.override.Bound'
    ]
    // all the logic can be found in Bancha.data.validator.override.Bound
}, function() {
    // for some reason setting via config doesn't work
    this.prototype.setNanMessage('Must be a number');
});
