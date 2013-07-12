/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
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
/*jslint browser: true, vars: false, plusplus: true, white: true, sloppy: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false, strict:false */
/*global Ext, Bancha */

/**
 * @private
 * @class Bancha.data.Model
 * @extends Ext.data.Model
 * 
 * This should only be used by Bancha internally, 
 * since it just has an additional flag to force consistency in Bancha.
 * 
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @docauthor Roland Schuetz <mail@rolandschuetz.at>
 */
Ext.define('Bancha.data.Model', {
    extend: 'Ext.data.Model',

    requires: [
        'Ext.direct.Manager',
        'Bancha.data.writer.JsonWithDateTime',
        'Bancha.data.writer.ConsistentJson'
    ],
    uses: [
       'Bancha.Main'
    ],

    /**
     * @cfg
     * If true the frontend forces consistency.
     * This is not yet supported! See http://docs.banchaproject.org/resources/Roadmap.html
     */
    forceConsistency: false,

    /**
     * For Ext JS:
     * Every time a new subclass is created, this function will apply all Bancha model configurations.
     * 
     * In the debug version it will raise an Ext.Error if the model can't be 
     * or is already created, in production it will only return false.
     *
     * Reasons why it can't work like Sencha Touch:
     *  - Since Ext JS does not have setters for associations we need to set it as data before.
     *  - Since Ext JS's onBeforeClassCreated retrieved the proxy data BEFORE the postprocessor 
     *    is executed, but applied AFTER it the proxy can't be set there. So we need to set the 
     *    proxy this way.
     */
    onClassExtended: function(cls, data, hooks) {
        if(Ext.versions.touch) {
            return; // // nothing to do for Sencha Touch, see Ext.ClassManager.registerPostprocessor below
        }

        // Support for ExtJS 4.0.7
        var me = this;
        if(typeof me.applyCakeSchema !== 'function') {
            me = Ext.ClassManager.get('Bancha.data.Model');
        }

        me.applyCakeSchema(cls, data);
    },
    statics: {
        /**
         * @private
         * This function applies all the Bancha model configurations from the
         * cakephp models.
         * 
         * In the debug version it will raise an Ext.Error if the model can't be 
         * or is already created, in production it will only return false.
         * 
         * @param {String} modelCls The model to augment
         * @param {Object} extJsOnClassExtendedData If this is executed from an Ext JS context 
         *                                          this is the data argument from onClassExtended
         * @return void
         */
        applyCakeSchema: function(modelCls, extJsOnClassExtendedData) {
            var modelName = modelCls.getName().split('.').pop(), // CakePHP model name, e.g. "User"
                config;

            if(!Bancha.initialized) {
                Bancha.init();
            }
            
            if(!Bancha.isRemoteModel(modelName)) {
                // IFDEBUG
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: 'Bancha: Couldn\'t create the model "'+modelName+'" cause the model is not supported by the server (no remote model).'
                });
                // ENDIF
                return false;
            }
            
            if(!Bancha.modelMetaDataIsLoaded(modelName)) {
                // IFDEBUG
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: 'Bancha: Couldn\'t create the model cause the metadata is not loaded yet, please use onModelReady instead.'
                });
                // ENDIF
                return false;
            }
            
            // IFDEBUG
            if(!Ext.isDefined(Bancha.getModelMetaData(modelName).idProperty)) {
                if(Ext.global.console && Ext.isFunction(Ext.global.console.warn)) {
                    Ext.global.console.warn(
                        'Bancha: The model meta data for '+modelName+' seems strange, probably this was '+
                        'not created by Bancha, or an error occured on the server-side. Please notice '+
                        'that this warning is only created in debug mode.');
                }
            }
            // ENDIF
            
            // configure the new model
            config = Bancha.getModelMetaData(modelName);

            // Support for ExtJS 4.0.7
            if(typeof modelCls.setFields !== 'function') {
                // setFields only exists in ExtJS 4.1+
                var fields,
                    i = 0, len = config.fields.length;
                fields = new Ext.util.MixedCollection(false, function(field) {
                    return field.name;
                });
                for (; i < len; i++) {
                    fields.add(new Ext.data.Field(fields[i]));
                }
                extJsOnClassExtendedData.fields = fields;
            } else {
                // default case for ExtJS and Sencha Touch
                modelCls.setFields(config.fields);
            }

            if(Ext.versions.touch) {
                modelCls.setAssociations(config.associations);
                modelCls.setIdProperty(config.idProperty);
                modelCls.setValidations(config.validations);
            } else {
                extJsOnClassExtendedData.associations = config.associations;
                extJsOnClassExtendedData.idProperty = config.idProperty;
                extJsOnClassExtendedData.validations = config.validations;
            }

            // set the Bancha proxy
            modelCls.setProxy(this.createBanchaProxy(modelCls));
        },
        // IFDEBUG
        /**
         * To display nicer debugging messages this is used in debug mode.
         * @param  {Object} stub      Ext.Direct stub
         * @param  {String} method    Sencha method name
         * @param  {String} modelName The CakePHP model name
         * @return {Function}
         */
        createSafeDirectFn: function(stub, method, modelName) {
            if(Ext.isDefined(stub[method] && typeof stub[method] === 'function')) {
                return stub[method];
            }
            
            // function doesn't exit, create fake which will throw an error on first use
            var map = {
                    create : 'add',
                    read   : 'view or index',
                    update : 'edit',
                    destroy: 'delete'
                },
                fakeFn = function() {
                    Ext.Error.raise({
                        plugin: 'Bancha',
                        modelName: modelName,
                        msg: 'Bancha: Tried to call '+modelName+'.'+method+'(...), but the server-side has not implemented '+modelName+'sController->'+ map[method]+'(...). (If you have special inflection rules, the serverside is maybe looking for a different controller name, this is jsut a guess)'
                    });
                };
            
            // this is not part of the official Ext API!, but it seems to be necessary to do this for better bancha debugging
            fakeFn.directCfg = { // TODO testen
                len: 1,
                name: method,
                formHandler: false
            };
            // fake the execution method
            fakeFn.directCfg.method = function() {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: 'Bancha: Tried to call '+modelName+'.'+method+'(...), but the server-side has not implemented '+modelName+'sController->'+ map[method]+'(...). (If you have special inflection rules, the serverside is maybe looking for a different controller name)'
                });
            };
            
            return fakeFn;
        },
        // ENDIF
        createBanchaProxy: function(model) {
            var modelName = model.getName().split('.').pop(), // CakePHP model name, e.g. "User"
                stub,
                configWithRootPropertySet;

            // Sencha Touch uses the new rootProperty property for configuring the reader and writer
            // ExtJS still uses root. 
            // This all would be fine, but now Sencha Touch throws deprecated warning for using the old
            // ExtJS syntax, so we can't just assign both anymore, instead we need to create a config
            // prototype here
            if(Ext.versions.touch) {
                configWithRootPropertySet = {
                    rootProperty: 'data'
                };
            } else {
                configWithRootPropertySet = {
                    root: 'data'
                };
            }

            // create the metadata
            stub = Bancha.getStubsNamespace()[modelName];
            return { // the proxy configuration
                type: 'direct', // TODO batch requests: http://www.sencha.com/forum/showthread.php?156917
                batchActions: false, // don't batch requests on the store level, they will be batched batched by Ext.Direct on the application level
                api: {
                    /* IFPRODUCTION
                    // if method is not supported by remote it get's set to undefined
                    read    : stub.read,
                    create  : stub.create,
                    update  : stub.update,
                    destroy : stub.destroy
                    ENDIF */
                    // IFDEBUG
                    read    : this.createSafeDirectFn(stub,'read',modelName),
                    create  : this.createSafeDirectFn(stub,'create',modelName),
                    update  : this.createSafeDirectFn(stub,'update',modelName),
                    destroy : this.createSafeDirectFn(stub,'destroy',modelName)
                    // ENDIF
                },
                // because of an error in ext the following directFn def. has to be 
                // defined, which should be read from api.read instead...
                // see http://www.sencha.com/forum/showthread.php?134505-Model-proxy-for-a-Store-doesn-t-seem-to-work-if-the-proxy-is-a-direct-proxy&p=606283&viewfull=1#post606283
                /* IFPRODUCTION
                directFn: stub.read,
                ENDIF */
                // IFDEBUG
                directFn: this.createSafeDirectFn(stub,'read',modelName),
                // ENDIF
                reader: Ext.apply({
                    type: 'json',
                    messageProperty: 'message'
                }, configWithRootPropertySet),
                writer: (model.forceConsistency) ? Ext.apply({
                    type: 'consitentjson',
                    writeAllFields: false
                }, configWithRootPropertySet) : Ext.apply({
                    type: 'jsondate',
                    writeAllFields: false
                }, configWithRootPropertySet),
                listeners: {
                    exception: Bancha.onRemoteException || Ext.emptyFn
                }
            };
        } //eo getBanchaProxy
    } //eo statics
}, function() {
    var me = this;

    if(!Ext.versions.touch) {
        return; // nothing to do for Ext JS, see onClassExtended
    }

    /*
     * For Sencha Touch:
     * Every time a new subclass is created, this function will apply all Bancha model configurations.
     * 
     * In the debug version it will raise an Ext.Error if the model can't be 
     * or is already created, in production it will only return false.
     */
    Ext.ClassManager.registerPostprocessor('banchamodel', function(name, cls, data) {
        var ns = (Bancha.modelNamespace+'.' || 'Bancha.model.');
        if(name.substr(0, ns.length) !== ns) {
            return true; // not a Bancha model
        }
        me.applyCakeSchema(cls);
    }, true);
});
