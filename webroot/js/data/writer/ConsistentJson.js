/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 0.0.2
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

/**
 * @private
 * @class Bancha.data.writer.ConsistentJson
 *
 * This should only be used by Bancha internally,
 * it adds the consistent uid to all requests.
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.writer.ConsistentJson', {
    extend: 'Bancha.data.writer.JsonWithDateTime',
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
