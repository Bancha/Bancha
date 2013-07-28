/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with ExtJS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * Bancha specific helper functions
 *
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/* global BanchaSpecHelper:true */

/** helpers */
BanchaSpecHelper = {};
BanchaSpecHelper.SampleData = {};
BanchaSpecHelper.SampleData.remoteApiDefinition = {
    url: 'bancha-dispatcher-mock.js',
    namespace: 'Bancha.RemoteStubs',
    'type':'remoting',
    'actions':{
        'User':[{
            'name':'submit',
            'formHandler': true,
            'len':1
        },{
            'name':'read',
            'len':1
        },{
            'name':'create',
            'len':1
        },{
            'name':'update',
            'len':1
        },{
            'name':'destroy',
            'len':1
        }]
    },
    metadata: {
        _UID: '550e8400e29b11d4a7164466554400004',
        _ServerDebugLevel: 0, // set the debug level to zero to suppress Banchas debug error handling
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
                {name:'height', type:'int'},
                {name:'country_id', type:'int'}
            ],
            associations: [
                {type:'hasMany', model:'Bancha.test.model.Post', name:'posts'}, // these models need to exist
                {type:'belongsTo', model:'Bancha.test.model.Country', name:'country'}
            ],
            validations: [
                { type:'numberformat', field:'id', precision:0},
                { type:'presence',     field:'name'},
                { type:'length',       field:'name', min: 2},
                { type:'length',       field:'name', max:64},
                { type:'format',       field:'login', matcher:'banchaAlphanum'}
            ],
            sorters: [{
                property: 'name',
                direction: 'ASC'
            }]
        } //eo User
    }
};


var testErrorHandler = function(err) {
    expect(false).toEqual('Ext.Error.handle was triggered with following message:'+err.msg);
};

BanchaSpecHelper.init = function(/*optional*/modelDefinitionsForName,/*optional*/additionalConfigs) {
    var api = BanchaSpecHelper.SampleData.remoteApiDefinition;
    Bancha.REMOTE_API = Ext.clone(api);

    // catch all errors thrown in the init
    var alert = Ext.Msg.alert;
    Ext.Msg.alert = function(title, msg) {
        testErrorHandler({msg: msg});
    };

    if(Ext.isString(modelDefinitionsForName)) {
        // setup fake model
        Bancha.REMOTE_API.metadata[modelDefinitionsForName] = Ext.apply(Ext.clone(Bancha.REMOTE_API.metadata.User),additionalConfigs);
        Bancha.REMOTE_API.actions[modelDefinitionsForName] = Ext.clone(api.actions.User);
    } else if(Ext.isDefined(modelDefinitionsForName)){
        throw 'modelDefinitionsFor is not a string';
    }
    Bancha.init();

    // errors should create a fail (overwrite the one defined in init)
    Ext.Error.handle = testErrorHandler;
    Ext.Msg.alert = alert;
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

// initializes the from User associated model once
BanchaSpecHelper.initAssociatedModels = function() {
    Ext.each(BanchaSpecHelper.SampleData.remoteApiDefinition.metadata.User.associations, function(association) {
        if(!Ext.ClassManager.get(association.model)) {
            Ext.define(association.model, {
                extend: 'Ext.data.Model'
            });
        }
    });
};

// Bancha tries to connect to the api in debug mode, mimik that is works
// but there should never be any other Ajax requests
Ext.Ajax.requestExceptionHasAlreadyBeenThrownOnce = false;
Ext.Ajax.request = function(config) {
    var dispatcher = BanchaSpecHelper.SampleData.remoteApiDefinition.url,
        to;
    if(config.url.indexOf(dispatcher+'?setup-check=true') !== -1) {
        // this is a check of the bancha dispatcher, everything ok
        return {
            status: 200,
            responseText: '{"BanchaDispatcherIsSetup":true}'
        };
    } else if(!Ext.Ajax.requestExceptionHasAlreadyBeenThrownOnce) {
        if(config.url.indexOf(dispatcher) === -1) {
            to = config.url;
        } else {
            to = 'CakePHP: '+config.jsonData.action+'.'+config.jsonData.method+'('+Ext.encode(config.jsonData.data).slice(1,-1)+')';
        }

        Ext.Ajax.requestExceptionHasAlreadyBeenThrownOnce = true;
        throw new Error('Unexpected usage of Ext.Ajax.request to '+to);
    }
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
