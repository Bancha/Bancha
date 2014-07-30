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
 * @class Bancha.data.writer.ConsistentJson
 * @extends Bancha.data.writer.TreeParentIdTransformedJson
 *
 * This should only be used by Bancha internally,
 * it adds the consistent uid to all requests.
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.writer.ConsistentJson', {
    extend: 'Bancha.data.writer.TreeParentIdTransformedJson',
    alias: 'writer.consitentjson',

    /**
     * @private
     * @cfg
     * the name of the field to send the consistent uid in
     */
    uidProperty: '__bcid',

    //inherit docs
    getRecordData: function(record, operation) {

        // let the json writer do all the work:
        var data = this.callParent(arguments);

        // now simply add the client id
        if(Ext.ClassManager.getClass(record).getForceConsistency()) {
            data[this.uidProperty] = Bancha.getConsistentUid();
        }

        return data;
    }
});
