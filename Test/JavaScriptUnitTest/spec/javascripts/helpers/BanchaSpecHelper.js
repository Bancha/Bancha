/*!
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Bancha specific helper functions
 *
 * @copyright     Copyright 2011-2012 Roland Schuetz
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, BanchaSpecHelper:true */

/** helpers */
BanchaSpecHelper = {};
BanchaSpecHelper.SampleData = {};
BanchaSpecHelper.SampleData.remoteApiDefinition = {
    url: 'Bancha/router.json',
    namespace: 'Bancha.RemoteStubs',
    "type":"remoting",
    "actions":{
        "User":[{
            "name":"submit",
            "formHandler": true,
            "len":1
        },{
            "name":"read",
            "len":1
        },{
            "name":"create",
            "len":1
        },{
            "name":"update",
            "len":1
        },{
            "name":"destroy",
            "len":1
        }]
    },
    metadata: {
        _UID: '550e8400e29b11d4a7164466554400004',
        User: {
                idProperty: 'id',
                fields: [
                    {name:'id', type:'int'},
                    {name:'name', type:'string'},
                    {name:'login', type:'string'},
                    {name:'created', type:'date'},
                    {name:'email', type:'string'},
                    {name:'avatar', type:'string'},
                    {name:'weight', type:'float'},
                    {name:'height', type:'int'}
                ],
                associations: [
                    {type:'hasMany', model:'Bancha.model.Post', name:'posts'}, // these models need to exist
                    {type:'belongsTo', model:'Bancha.model.Country', name:'country'}
                ],
                validations: [
                    { type:"numberformat", field:"id", precision:0},
                    { type:"presence",     field:"name"},
                    { type:"length",       field:'name', min: 2},
                    { type:"length",       field:"name", max:64},
                    { type:"format",       field:"login", matcher:"banchaAlphanum"}
                ],
                sorters: [{
                    property: 'name',
                    direction: 'ASC'
                }]
      }
   }
};



BanchaSpecHelper.init = function(/*optional*/modelDefinitionsForName,additionalConfigs) {
    var api = BanchaSpecHelper.SampleData.remoteApiDefinition;
    Bancha.REMOTE_API = Ext.clone(api);
    
    if(Ext.isString(modelDefinitionsForName)) {
        // setup fake model
        Bancha.REMOTE_API.metadata[modelDefinitionsForName] = Ext.apply(Ext.clone(Bancha.REMOTE_API.metadata.User),additionalConfigs);
        Bancha.REMOTE_API.actions[modelDefinitionsForName] = Ext.clone(api.actions.User);
    } else if(Ext.isDefined(modelDefinitionsForName)){
        throw 'modelDefinitionsFor is not a string';
    }
    Bancha.init();
};
BanchaSpecHelper.initAndCreateSampleModel = function(modelName,additionalConfigs) {
    this.init(modelName,additionalConfigs);
    expect(Bancha.createModel(modelName)).toBeTruthy(); // Try to create fake model
};
BanchaSpecHelper.reset = function() {
    // Bancha is a singleton, so get the class of
    // the singleton object and reinstanciate it
    //var className = Ext.getClassName(Ext.getClass(Bancha));
    //Bancha = Ext.create(className,{});
    delete Bancha.REMOTE_API;
    delete Bancha.RemoteStubs;
    Bancha.initialized = false;
};


beforeEach(function() {
    this.addMatchers({
        toEqualConfig: function(expected) {
            var config = Ext.clone(this.actual);
            delete config.scaffold;
            delete config.scaffoldLoadRecord;
            delete config.scaffold;
            delete config.scaffoldConfig; // deprecated
            delete config.enableCreate;
            delete config.enableUpdate;
            delete config.enableDestroy;
            delete config.enableReset;
            // test
            expect(config).toEqual(expected);
            // test is already done above
            return true;
        }
    });
});

//eof
