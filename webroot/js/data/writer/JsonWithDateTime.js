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
 * @class Bancha.data.writer.JsonWithDateTime
 *
 * This should only be used by Bancha internally.
 *
 * For Sencha Touch it fixes a bug inside writeDate.
 *
 * For ExtJS 4.1.1a+ it adds support date fields with
 * value null.
 *
 * For ExtJS 4.1.0 and below it adds support for date
 * conversions.
 *
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.writer.JsonWithDateTime', {
    extend: 'Ext.data.writer.Json',
    alias: 'writer.jsondate',

    /**
     * Add support for null dates to ExtJS
     */
    getRecordData: function(record, operation) {
        // let the json writer do the real work
        var data = this.superclass.getRecordData.apply(this,arguments),
            nameProperty = this.nameProperty,
            fields = record.fields,
            fieldItems = fields.items,
            me = this;

        // Sencha Touch is augmented in the writeDate fucntion, so we are done here
        // ExtJS doesn't have a writeDate function yet, so we need to augment below
        if(Ext.versions.touch) {
            return data;
        }

        // for ExtJS 4.1.1+ versions add support for null
        if(parseInt(Ext.versions.extjs.shortVersion,10) >= 411) {
            Ext.each(fieldItems, function(field) {
                var name = field[nameProperty] || field.name;
                if (field.type === Ext.data.Types.DATE && field.dateFormat && record.get(field.name)===null) {
                    // add support for null dates
                    data[name] = null;
                } else if (field.type === Ext.data.Types.DATE && field.dateFormat==='timestamp') {
                    // Ext JS 4.1.1 does not convert timestamps correctly, fix this
                    data[name] = Math.round(record.get(field.name).getTime()/1000);
                } else if (field.type === Ext.data.Types.DATE && field.dateFormat==='time') {
                    // Ext JS 4.1.1 does not convert times correctly, fix this
                    data[name] = Math.round(record.get(field.name).getTime());
                }
            });

        // for older ExtJS versions add full date conversion support
        } else {
            Ext.each(fieldItems, function(field) {
                var name = field[nameProperty] || field.name;
                if (field.type === Ext.data.Types.DATE && field.dateFormat) {
                    data[name] = me.writeDate(field, record.get(field.name));
                }
            });
        }

        return data;
    },

    /**
     * Fix Sencha Touch 2.1.1 and below to use the
     * dateFormat and add support for null dates.
     *
     * Since ExtJS doesn't have a function called
     * writeDate but is also buggy prior to Ext 4.1.1
     * we call this function from getRecordData
     *
     * Bug Report:
     * http://www.sencha.com/forum/showthread.php?249288-Ext.data.writer.Json-doesn-t-use-dateFormat
     */
    writeDate: function(field, date) {
        if(date===null || !Ext.isDefined(date)) { // <-- added support for null and undefined
            return date;
        }

        // fixed the single-next line below
        var dateFormat = field.dateFormat || (field.getDateFormat ? field.getDateFormat() : false) || 'timestamp';
        switch (dateFormat) {
        case 'timestamp':
            return date.getTime()/1000;
        case 'time':
            return date.getTime();
        default:
            return Ext.Date.format(date, dateFormat);
        }
    }
});
