/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * @package       Bancha
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 2.0.0
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

/**
 * @private
 * @class Bancha.data.override.NodeInterface
 *
 * This fixes a bug in Ext JS 4.2.0 and 4.2.1.
 *
 * See also http://www.sencha.com/forum/showthread.php?258913
 *
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.override.NodeInterface', {
    override: 'Ext.data.NodeInterface',
    statics: {
        getPrototypeBody: function() {
            var result = this.callParent(arguments),
                updateInfo = result.updateInfo;

            // This applies only for Ext JS 4.2.0 and 4.2.1
            if(!Ext.versions.extjs || Ext.versions.extjs.lt('4.2.0') || Ext.versions.extjs.gtEq('4.2.2')) {
                return result; // this bug doesn't exist in here
            }

            // batch update function
            result.updateInfo = function(commit, info) {
                var me = this,
                    dataObject = me[me.persistenceProperty],
                    propName, newValue, field;

                // Set the passed field values into the modified object.
                // Otherwise a writer with writeAllFields:false missed these changes
                for (propName in info) {
                    if(info.hasOwnProperty(propName)) {
                        field = me.fields.get(propName);
                        newValue = info[propName];
                        
                        // Update modified value, everything else is done by override method
                        if (field && field.persist && !me.isEqual(dataObject[propName], newValue)) {
                            me.modified[propName] = newValue;
                        }
                    }
                }

                return updateInfo.apply(this, arguments);
            };
            return result;
        }
    }
});
