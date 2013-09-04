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
        'Bancha.data.writer.ConsistentJson',
        'Bancha.Remoting'
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

        // only apply this for ExtJS, see Ext.ClassManager.registerPostprocessor below for Sencha Touch
        if(Ext.versions.extjs) {

            // Support for ExtJS 4.0.7
            var me = this;
            if(typeof me.applyCakeSchema !== 'function') {
                // In Ext JS 4.1+ the scope is the Bancha.data.Model,
                // In Ext JS 4.0 the scope is the newly created class, fix this
                me = Ext.ClassManager.get('Bancha.data.Model');
            }

            me.applyCakeSchema(cls, data);
        }

        // Legacy Support for Ext JS 4.0
        // Ext JS 4.1+ applies onClassExtended methods of superclasses and super-superclasses and so on,
        // the whole inheritance chain up.
        // Ext JS 4.0 applies the method only to the immediate subclasses, but not child-child classes.
        // Normalize to the new behavior
        if(Ext.versions.extjs && Ext.versions.extjs.shortVersion < 410) {
            // If we wouldn't call it here the Ext.data.Model#onClassExtended would only be applied to
            // Bancha.data.Model, but not to it's childs. With the sequence we get the expeceted behavior.
            Ext.data.Model.prototype.$onExtended.apply(this, arguments);
        }
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
                //<debug>
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: [
                        'Bancha: Couldn\'t create the model "'+modelName+'" ',
                        'cause the model is not supported by the server ',
                        '(no remote model).'
                    ].join('')
                });
                //</debug>
                return false;
            }

            if(!Bancha.modelMetaDataIsLoaded(modelName)) {
                //<debug>
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: 'Bancha: Couldn\'t create the model cause the metadata is not loaded yet, please use onModelReady instead.'
                });
                //</debug>
                return false;
            }

            //<debug>
            if(!Ext.isDefined(Bancha.getModelMetaData(modelName).idProperty)) {
                if(Ext.global.console && Ext.isFunction(Ext.global.console.warn)) {
                    Ext.global.console.warn(
                        'Bancha: The model meta data for '+modelName+' seems strange, probably this was '+
                        'not created by Bancha, or an error occured on the server-side. Please notice '+
                        'that this warning is only created in debug mode.');
                }
            }
            //</debug>

            // configure the new model
            config = Bancha.getModelMetaData(modelName);

            if(!Ext.versions.touch) {
                // this is used for two cases:
                // - Support for ExtJS 4.0.7
                // - Ext JS Support for ScriptTagInitializer, where we hook into Ext.data.Model extend
                extJsOnClassExtendedData.fields = config.fields;
            }
            // default case for ExtJS and Sencha Touch
            if(typeof modelCls.setFields === 'function') {
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
        /**
         * To display nicer debugging messages, i debug mode this returns
         * a fake function if the stub method doesn't exist.
         *
         * In production mode it simply returns the original function or null.
         *
         * @param  {Object} stub      Ext.Direct stub
         * @param  {String} method    Sencha method name
         * @param  {String} modelName The CakePHP model name
         * @return {Function|null}
         */
        getStubMethod: function(stub, method, modelName) {
            if(Ext.isDefined(stub[method] && typeof stub[method] === 'function')) {
                return stub[method];
            }

            var fakeFn = null;

            //<debug>
            // function doesn't exit, create fake which will throw an error on first use
            var map = {
                create : 'add',
                read   : 'view or index',
                update : 'edit',
                destroy: 'delete'
            };
            fakeFn = function() {
                Ext.Error.raise({
                    plugin: 'Bancha',
                    modelName: modelName,
                    msg: [
                        'Bancha: Tried to call '+modelName+'.'+method+'(...), ',
                        'but the server-side has not implemented ',
                        modelName+'sController->'+ map[method]+'(...). ',
                        '(If you have special inflection rules, the server-side ',
                        'is maybe looking for a different controller name, ',
                        'this is just a guess)'
                    ].join('')
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
                    msg: [
                        'Bancha: Tried to call '+modelName+'.'+method+'(...), ',
                        'but the server-side has not implemented ',
                        modelName+'sController->'+ map[method]+'(...). ',
                        '(If you have special inflection rules, the server-side ',
                        'is maybe looking for a different controller name)'
                    ].join('')
                });
            };
            //</debug>

            return fakeFn;
        },
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
                // don't batch requests on the store level, they will be batched
                // by Ext.Direct on the application level
                batchActions: false,
                api: {
                    read    : this.getStubMethod(stub,'read',modelName),
                    create  : this.getStubMethod(stub,'create',modelName),
                    update  : this.getStubMethod(stub,'update',modelName),
                    destroy : this.getStubMethod(stub,'destroy',modelName)
                },
                // because of an error in ext the following directFn def. has to be
                // defined, which should be read from api.read instead...
                // see http://www.sencha.com/forum/showthread.php?134505&p=606283&viewfull=1#post606283
                directFn: this.getStubMethod(stub,'read',modelName),
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
                    exception: Bancha.Remoting.getFacade('onRemoteException')
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
     *
     * Every time a new subclass is created, this function will apply all
     * Bancha model configurations.
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
