/*!
 * Bancha.scaffold.Form Tests
 * Copyright(c) 2011 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011 Roland Schuetz
 */
/*jslint browser: true, vars: true, plusplus: true, white: true, sloppy: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, BanchaSpecHelper */


describe("Bancha.scaffold.Form tests",function() {
    
    var h = BanchaSpecHelper, // shortcuts
        formScaf = Bancha.scaffold.Form; //shortcuf
        // take the defaults
        // (actually this is also copying all the function references, but it doesn't atter)
        testDefaults = Ext.clone(formScaf);

    beforeEach(function() {
        h.reset();
        // re-enforce defaults
        Ext.apply(formScaf, testDefaults);
    });
    
    it("should build field configs while considering the defined defaults", function() {
        // define some defaults
        formScaf.fieldDefaults = {
            forAllFields: 'added'
        };
        formScaf.textfieldDefaults = {
            justForText: true
        };
        formScaf.datefieldDefaults = {};
        
        expect(formScaf.buildFieldConfig('string','someName')).toEqual({
            forAllFields: 'added',
            justForText: true,
            xtype : 'textfield',
            fieldLabel: 'Some name',
            name: 'someName'
        });
        
        // now there should be just added the first one
        expect(formScaf.buildFieldConfig('date','someName')).toEqual({
            forAllFields: 'added',
            xtype : 'datefield',
            fieldLabel: 'Some name',
            name: 'someName'
        });
    });
    
    it("should build field configs while considering special defaults per call", function() {
        formScaf.fieldDefaults = {
            forAllFields: 'added'
        };
        formScaf.textfieldDefaults = {
            justForText: true
        };
        var defaults = {
            textfieldDefaults: {
                justForThisTextBuild: true
            }
        };
        
        expect(formScaf.buildFieldConfig('string','someName',defaults)).toEqual({
            forAllFields: 'added',
            justForThisTextBuild: true, // <-- old defaults got overrided
            xtype : 'textfield',
            fieldLabel: 'Some name',
            name: 'someName'
        });

        // now there should be just added the first one
        expect(formScaf.buildFieldConfig('date','someName'),defaults).toEqual({
            forAllFields: 'added',
            xtype : 'datefield',
            fieldLabel: 'Some name',
            name: 'someName'
        });
    });
    
    var getButtonConfig = function(id) {
        return [{
            text: 'Reset',
            iconCls: 'icon-reset',
            handler: formScaf.scopeButtonHandler(formScaf.onReset,id,'Reset'),
        }, {
            text: 'Save',
            iconCls: 'icon-save',
            formBind: true,
            handler: formScaf.scopeButtonHandler(formScaf.onSave,id,'Save')
        }];
    };
    
    it("should build a form config, where it recognizes the type from the field type, when no "+
       "validation rules are set in the model (component test)", function() {
        // prepare
        h.initAndCreateSampleModel('FormConfigTest');
        
        var expected = {
            id: 'FormConfigTest-id', // forced
            // configs for BasicForm
            api: {
                // The server-side method to call for load() requests
                load: Bancha.getStubsNamespace().FormConfigTest.read,
                // The server-side must mark the submit handler as a 'formHandler'
                submit: Bancha.getStubsNamespace().FormConfigTest.submit
            },
            paramOrder : [ 'data' ],
            items: [{
                xtype: 'hiddenfield',
                allowDecimals : false,
                fieldLabel: 'Id',
                name: 'id'
            },{
                xtype: 'textfield',
                fieldLabel: 'Name',
                name: 'name'
            },{
                xtype: 'textfield',
                fieldLabel: 'Login',
                name: 'login'
            },{
                xtype: 'datefield',
                fieldLabel: 'Created',
                name: 'created'
            },{
                xtype: 'textfield',
                fieldLabel: 'Email',
                name: 'email'
            }, {
                xtype: 'textfield', // an fileuploadfield is recognized through validation rules
                fieldLabel: 'Avatar',
                name: 'avatar'
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Weight',
                name: 'weight'
            }, {
                xtype: 'numberfield',
                allowDecimals : false,
                fieldLabel: 'Height',
                name: 'height'
            }],
            buttons: getButtonConfig('FormConfigTest-id')
        }; // eo expected
        
        expect(formScaf.buildConfig('FormConfigTest',false,false,{
            id: 'FormConfigTest-id'
        })).toEqual(expected);
    });
    
    it("should build a form config, where it recognizes the type from the field type, when no "+
       "validation rules are set in the model (component test)", function() {
        // prepare
        h.initAndCreateSampleModel('FormConfigWithValidationTest',{
            validations: [
                {type:'presence', name:'id'},
                {type:'presence', name:'name'},
                {type:'length', name:'name', min:3, max:64},
                {type:'presence', name:'login'},
                {type:'length', name:'login', min:3, max:64},
                {type:'format', name:'login', matcher: /^[a-zA-Z0-9_]+$/},
                {type:'presence', name:'email'},
                {type:'format', name:'email', matcher: /^(\w+)([\-+.][\w]+)*@(\w[\-\w]*\.){1,5}([A-Za-z]){2,6}$/},
                {type:'numberformat', name:'weight', precision:2},
                {type:'numberformat', name:'height', min:50, max:300},
                {type:'file', name:'avatar', extension:['gif', 'jpeg', 'png', 'jpg']},
            ]
        });
        
        expect(Bancha.getStubsNamespace().FormConfigWithValidationTest.submit).toBeAFunction();
        
        var expected = {
            id: 'FormConfigWithValidationTest-id', // forced
            // configs for BasicForm
            api: {
                load: Bancha.getStubsNamespace().FormConfigWithValidationTest.read,
                submit: Bancha.getStubsNamespace().FormConfigWithValidationTest.submit // TODO this should be a function!
            },
            paramOrder : [ 'data' ],
            items: [{
                xtype: 'hiddenfield',
                allowDecimals: false,
                fieldLabel: 'Id',
                name: 'id',
                allowBlank:false
            },{
                xtype: 'textfield',
                fieldLabel: 'Name',
                name: 'name',
                allowBlank:false,
                minLength: 3,
                maxLength: 64
            },{
                xtype: 'textfield',
                fieldLabel: 'Login',
                name: 'login',
                allowBlank:false,
                minLength: 3,
                maxLength: 64,
                vtype: 'alphanum' // use toString to compare
            },{
                xtype: 'datefield',
                fieldLabel: 'Created',
                name: 'created'
            },{
                xtype: 'textfield',
                fieldLabel: 'Email',
                name: 'email',
                allowBlank: false,
                vtype: 'email'
            }, {
                xtype: 'fileuploadfield',
                fieldLabel: 'Avatar',
                name: 'avatar',
                emptyText: 'Select an image',
                buttonText: '',
                buttonConfig: {
                    iconCls: 'icon-upload'
                },
                vtype: 'fileExtension',
                validExtensions: ['gif', 'jpeg', 'png', 'jpg']
            }, {
                xtype: 'numberfield',
                fieldLabel: 'Weight',
                name: 'weight',
                decimalPrecision: 2
            }, {
                xtype: 'numberfield',
                allowDecimals: false,
                fieldLabel: 'Height',
                name: 'height',
                minValue: 50,
                maxValue: 300
            }],
            buttons: getButtonConfig('FormConfigWithValidationTest-id')
        }; // eo expected
        
        expect(formScaf.buildConfig('FormConfigWithValidationTest',false,{
            fileuploadfieldDefaults: {
                emptyText: 'Select an image',
                buttonText: '',
                buttonConfig: {
                    iconCls: 'icon-upload'
                }
            }
        }, {
            id: 'FormConfigWithValidationTest-id'
        })).toEqual(expected);
        
        
        expect(formScaf.buildConfig('FormConfigWithValidationTest',false,false,{
            id: 'FormConfigWithValidationTest-id',
        }).buttons[0].handler).toEqual(expected.buttons[0].handler);
    });
    
    
    it("should use class interceptors when building a config (component test)", function() {
        // prepare
        h.initAndCreateSampleModel('FormConfigWithClassInterceptorsTest');
        
        // the same when defining them on the class
        Ext.apply(formScaf,{
            beforeBuild: function() {
                return {
                    interceptors: ['before'] // make sure that afterBuild only augemts
                };
            },
            afterBuild: function(config) {
                config.interceptors.push('after');
                return config;
            },
            guessFieldConfigs: function(config) {
                config.isAugmented = true;
                return config;
            }
        });
        result = formScaf.buildConfig('FormConfigWithClassInterceptorsTest');
        
        // beforeBuild, afterBuild
        expect(result.interceptors).toEqual(['before','after']);
        
        // guessFieldConfg
        expect(result.items).toBeAnObject();
        Ext.each(result.items, function(item) {
            expect(item.isAugmented).toEqual(true);
        });
    });
    
    
    it("should use config interceptors when building a config (component test)", function() {
        // prepare
        h.initAndCreateSampleModel('FormConfigWithConfigInterceptorsTest');
        
        var result = formScaf.buildConfig('FormConfigWithConfigInterceptorsTest',false,{
            beforeBuild: function() {
                return {
                    interceptors: ['before'] // make sure that afterBuild only augemts
                };
            },
            afterBuild: function(config) {
                config.interceptors.push('after');
                return config;
            },
            guessFieldConfigs: function(config) {
                config.isAugmented = true;
                return config;
            }
        });
        
        // beforeBuild, afterBuild
        expect(result.interceptors).toEqual(['before','after']);
        
        // guessFieldConfg
        expect(result.items).toBeAnObject();
        Ext.each(result.items, function(item) {
            expect(item.isAugmented).toEqual(true);
        });
    });
    
    it("should create a FormPanel using #createPanel", function() {
        // prepare
        h.initAndCreateSampleModel('FormPanelTest');
        
        // since this function is just a wrapper for #buildConfig,
        // just test that it returns an form panel

        expect(formScaf.createPanel('FormPanelTest')).toBeOfClass('Ext.form.Panel');
    });
    
}); //eo scaffold form functions

//eof
