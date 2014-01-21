/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * @package       Bancha.data.writer
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
 * @class Bancha.data.writer.TreeParentIdTransformedJson
 *
 * This should only be used by Bancha internally,
 * it transforms the Sencha Touch/Ext JS tree parentId into
 * the CakePHP parent_id.
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.writer.TreeParentIdTransformedJson', {
    extend: 'Bancha.data.writer.JsonWithDateTime',
    alias: 'writer.treeenabledjsonwriter',

    //inherit docs
    getRecordData: function(record, operation) {

        // let the json writer do all the work
        var data = this.callParent(arguments);

        if(record.isNode && data.parentId) {
            // this is a tree node and data needs transformation
            var field = record.fields.get('parentId').mapping; // get the CakePHP parentId field name
            data[field] = data.parentId;
            delete data.parentId;
        }

        return data;
    }
});
