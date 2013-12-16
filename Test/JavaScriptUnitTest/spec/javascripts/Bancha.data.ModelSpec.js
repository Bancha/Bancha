/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * Tests for the bancha model
 *
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

describe("Bancha.data.Model tests", function() {
    var h = BanchaSpecHelper; // helper shortcut

    beforeEach(h.reset);

    it("should apply the cake schema to all Bancha models defined in the Bancha.model namespace", function() {
        // prepare
        var ns = Bancha.modelNamespace;

        // expect the apply function to be called
        var spy = spyOn(Ext.ClassManager.get('Bancha.data.Model'), 'applyCakeSchema');

        // ExtJS handles this by simply beeing registered the onClassExtended
        // Sencha Touch recognizes this via the namespace
        Ext.define('Bancha.model.ModelTestCreateUser1', {extend: 'Bancha.data.Model'});
        expect(spy.callCount).toEqual(1);
        Ext.define('Bancha.model.ModelTestCreateUser2', {extend: 'Bancha.data.Model'});
        expect(spy.callCount).toEqual(2);
        Ext.define('MyApp.model.ModelTestCreateUser2', {extend: 'Ext.data.Model'});
        expect(spy.callCount).toEqual(2);
        Bancha.modelNamespace = 'Lala.model';
        Ext.define('Lala.model.ModelTestCreateUser3', {extend: 'Bancha.data.Model'});
        expect(spy.callCount).toEqual(3);

        // tear down
        Bancha.modelNamespace = ns;
    });


    it("should set a Bancha proxy on subclass models (integrative test)", function() {
        // setup model metadata
        h.init('ModelTestSchema1');

        // and create the model
        Ext.define('Bancha.model.ModelTestSchema1', {
            extend: 'Bancha.data.Model'
        });

        // check if the model really got created
        var model = Ext.ClassManager.get('Bancha.model.ModelTestSchema1');
        expect(model).toBeModelClass('Bancha.model.ModelTestSchema1');

        // test that a correct proxy is set
        if(Ext.versions.extjs) {
            // for Ext JS
            expect(model.getProxy()).property('type').toEqual('direct');
            expect(model.getProxy()).property('reader.type').toEqual('json');
            expect(model.getProxy()).property('writer.type').toEqual('consitentjson');
        } else {
            // For Sencha Touch
            expect(model.getProxy().alias).toEqual(['proxy.direct']);
            expect(model.getProxy().getReader().alias).toEqual(['reader.json']);
            expect(model.getProxy().getWriter().alias).toEqual(['writer.consitentjson']);
        }
    });

    Ext.define('Ext.data.override.Model', {
        override: 'Ext.data.Model',
        /**
         * #getFields behaves very differently on different versions, see below.
         * getFieldNames provides a way to get all field names in a normalized manner.
         *
         * Support for Ext JS 4.0
         * #getFields does not exist.
         *
         * Support for Ext JS 4.1+
         * In Ext JS this method is only available as static method, and returns an array
         *
         * Support for Sench Touch
         * Returns a collection of fields, where getName() must be executed to get the name.
         *
         * @return {String[]} The defined field names for this Model.
         */
        getFieldNames: function() {
            var result = [];
            if(Ext.versions.touch) {
                this.getFields().each(function(field) {
                    result.push(field.getName());
                });
                return result;
            }

            // for Ext JS, all versions
            Ext.each(this.fields.items, function(field) {
                result.push(field.name);
            });
            return result;
        }
    });

    it("should behave like a normal model, check normal model behavior", function() {
        // Since the behavior of Ext JS model changes between release this one jsut makes sure
        // that a normal model would behave as expected
        // This tests our assumptions about the behavior of the current Ext JS/sencha Touch-
        // But no Bancha code

        // create a model with all the configs the Bancha model should have as well
        if(Ext.versions.extjs) {

            Ext.define('Bancha.test.model.ModelTestSchema_PreTest', {
                extend: 'Ext.data.Model',
                idProperty: 'login', // <-- for testing the idProperty value
                fields: [
                    {name:'id', type:'int'},
                    {name:'name', type:'string'},
                    {name:'login', type:'string'},
                    {name:'created', type:'date'},
                    {name:'email', type:'string'},
                    {name:'avatar', type:'string'},
                    {name:'weight', type:'float'},
                    {name:'height', type:'int'},
                    {name:'country_id', type:'int'}
                ],
                associations: [
                    {type:'hasMany', model:'Bancha.test.model.Post', name:'posts'}, // these models need to exist
                    {type:'belongsTo', model:'Bancha.test.model.Country', name:'country'}
                ],
                validations: [
                    { type:"numberformat", field:"id", precision:0},
                    { type:"presence",     field:"name"},
                    { type:"length",       field:'name', min: 2},
                    { type:"length",       field:"name", max:64},
                    { type:"format",       field:"login", matcher:/^[a-zA-Z0-9_]+$/} // <-- Bancha validation rules use matcher:'banchaAlphanum'
                ]
            }); //eo define
        } else {

            // For Sencha Touch
            Ext.define('Bancha.test.model.ModelTestSchema_PreTest', {
                extend: 'Ext.data.Model',
                config: {
                    idProperty: 'login', // <-- for testing the idProperty value
                    fields: [
                        {name:'id', type:'int'},
                        {name:'name', type:'string'},
                        {name:'login', type:'string'},
                        {name:'created', type:'date'},
                        {name:'email', type:'string'},
                        {name:'avatar', type:'string'},
                        {name:'weight', type:'float'},
                        {name:'height', type:'int'},
                        {name:'country_id', type:'int'}
                    ],
                    associations: [
                        {type:'hasMany', model:'Bancha.test.model.Post', name:'posts'}, // these models need to exist
                        {type:'belongsTo', model:'Bancha.test.model.Country', name:'country'}
                    ],
                    validations: [
                        { type:"numberformat", field:"id", precision:0},
                        { type:"presence",     field:"name"},
                        { type:"length",       field:'name', min: 2},
                        { type:"length",       field:"name", max:64},
                        // the match regex below matches the Bancha validation rules 'banchaAlphanum'
                        { type:"format",       field:"login", matcher:/^[a-zA-Z0-9_]+$/}
                    ]
                }
            }); //eo define
        }

        // Create a test record
        var rec = Ext.create('Bancha.test.model.ModelTestSchema_PreTest', {
            id: 23,
            login: 'bad-sign',
            name: 'Micky Mouse'
        });

        // Ext JS returns a fields array, Sencha Touch a collection


        // expect a getFields method and the value should be an array of fields
        expect(rec.getFieldNames().length).toEqual(9);
        expect(rec.getFieldNames()).property('0').toEqual('id');
        expect(rec.getFieldNames()).property('1').toEqual('name');
        expect(rec.getFieldNames()).property('2').toEqual('login');

        // expect the idProperty to be set on the model prototype
        expect(rec.idProperty || rec.getIdProperty()).toEqual('login');

        // expect the validation rules to be applied using a validate method
        expect(rec.validate().getCount()).toEqual(1);
        rec.set('login', 'mickymouse');
        expect(rec.validate().getCount()).toEqual(0);

        // the associations are invisible till the associated models are
        // loaded as well
        h.initAssociatedModels();

        // expect the associations to be set on the model prototype
        // and the value as a mixed collection
        expect(rec.associations.getCount()).toEqual(2);

        // expect that associations create a store of related data
        var posts = rec.posts();
        expect(posts.isStore).toEqual(true);
        expect((posts.model || posts.getModel()).getName()).toEqual('Bancha.test.model.Post');
    });

    it("should set the fields and idProperty on Bancha models (integrative test)", function() {
        // setup model metadata
        h.init('ModelTestSchema2');

        // use a non-default id property
        Bancha.getRemoteApi().metadata.ModelTestSchema2.idProperty = 'login';

        // and create the model
        Ext.define('Bancha.model.ModelTestSchema2', {
            extend: 'Bancha.data.Model'
        });

        // Test that a record can be created without errors
        var rec = Ext.create('Bancha.model.ModelTestSchema2', {
            id: 23,
            login: 'mickymouse',
            name: 'Micky Mouse'
        });

        // test that the fields are set correctly
        expect(rec.getFieldNames().length).toEqual(9);
        expect(rec.getFieldNames()).property('0').toEqual('id');
        expect(rec.getFieldNames()).property('1').toEqual('name');
        expect(rec.getFieldNames()).property('2').toEqual('login');

        // test that the id property is set correctly
        expect(rec.idProperty || rec.getIdProperty()).toEqual('login');
    });


    it("should set the validation rules on Bancha models (integrative test)", function() {
        // setup model metadata
        h.init('ModelTestSchema3');

        // and create the model
        Ext.define('Bancha.model.ModelTestSchema3', {
            extend: 'Bancha.data.Model'
        });

        // create a test record for validating
        var rec = Ext.create('Bancha.model.ModelTestSchema3', {
            id: 'a', // validation error, because not numeric
            login: 'mickymouse' // this is fine
            // name: validation error, because not present
        });

        // test that the validation rules are applied
        expect(rec.validate().getCount()).toEqual(2);
        rec.set('login', 'bad-sign');
        expect(rec.validate().getCount()).toEqual(3);

        // create a test record for validating
        rec = Ext.create('Bancha.model.ModelTestSchema3', {
            id: 23,
            login: 'mickymouse',
            name: 'Micky Mouse'
        });

        // test that the validation rules are applied
        expect(rec.validate().getCount()).toEqual(0);
    });

    it("should set the associations on Bancha models (integrative test)", function() {
        // setup model metadata
        h.init('ModelTestSchema4');

        // and create the model
        Ext.define('Bancha.model.ModelTestSchema4', {
            extend: 'Bancha.data.Model'
        });

        // create a test record
        var rec = Ext.create('Bancha.model.ModelTestSchema4', {
            id: 23,
            login: 'mickymouse',
            name: 'Micky Mouse'
        });

        // the associations are invisible till the associated models are
        // loaded as well
        h.initAssociatedModels();

        // expect that Bancha set the associations
        expect(rec.associations.getCount()).toEqual(2);

        // expect that associations create a store of related data
        var posts = rec.posts();
        expect(posts.isStore).toEqual(true);
        expect((posts.model || posts.getModel()).getName()).toEqual('Bancha.test.model.Post');
    });


    it("should handle this simple integration test", function() {
        // setup model metadata
        h.init('ModelTestSchema4');

        // and create the model
        Ext.define('Bancha.model.ModelTestSchema4', {
            extend: 'Bancha.data.Model'
        });

        // check if the model really got created
        var model = Ext.ClassManager.get('Bancha.model.ModelTestSchema4');
        expect(model).toBeModelClass('Bancha.model.ModelTestSchema4');


        // create a mock object for the proxy
        var mockProxy = Mock.Proxy();
        model.setProxy(mockProxy);

        // Test 1:
        // create a test record
        var rec = Ext.create('Bancha.model.ModelTestSchema4', {
            login: 'mickymouse',
            name: 'Micky Mouse'
        });

        // define expectations for remote stub calls
        // user.save() should result in one create action
        mockProxy.expect("create");

        // test
        rec.save();

        // verify the expectations were met
        mockProxy.verify();

        // Test 2:
        // create a test record
        rec = Ext.create('Bancha.model.ModelTestSchema4', {
            id: 22, // record already exists (use a different id, since Sencha Touch 2.0.1.1 has an bug in useCache:false)
            login: 'mickymouse',
            name: 'Micky Mouse'
        });

        // define expectations for remote stub calls
        // user.save() should result in one create action
        mockProxy.expect("update");

        // test
        rec.save();

        // verify the expectations were met
        mockProxy.verify();
    });

    it("should allow to set the forceConsistency flag on a per-model bases", function() {
        // setup model metadata
        h.init(['ModelTestConsistencyConfig1','ModelTestConsistencyConfig2']);

        // and create the models
        Ext.define('Bancha.model.ModelTestConsistencyConfig1', {
            extend: 'Bancha.data.Model'
        });
        Ext.define('Bancha.model.ModelTestConsistencyConfig2', {
            extend: 'Bancha.data.Model'
        });

        // check that function exists
        expect(Bancha.model.ModelTestConsistencyConfig1.setForceConsistency).toBeAFunction();

        // set the config
        Bancha.model.ModelTestConsistencyConfig1.setForceConsistency(false);
        Bancha.model.ModelTestConsistencyConfig2.setForceConsistency(true);

        // check
        expect(Bancha.model.ModelTestConsistencyConfig1.getForceConsistency()).toBeFalsy();
        expect(Bancha.model.ModelTestConsistencyConfig2.getForceConsistency()).toBeTruthy();
    });
});
