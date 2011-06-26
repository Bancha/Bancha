/*jslint browser: true, vars: true, undef: true, nomen: true, eqeqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, immed: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, BanchaSpecHelper */

/** helpers */
BanchaSpecHelper = {};
BanchaSpecHelper.SampleData = {};
BanchaSpecHelper.SampleData.remoteApiDefinition = {
    url: 'Bancha/router.json',
    namespace: 'Bancha.RemoteStubs',
    "type":"remoting",
    "actions":{
        "User":[{
            "name":"create",
            "len":3
        },{
            "name":"destroy",
            "len":1
        }]
    },
    metadata: {
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
                    {name:'height', type:'float'}
                ],
                validations: [
                    {type:'length', name:'name', min:4, max:64},
                    {type:'length', name:'login', min:3, max:64},
                    {type:'length', name:'email', min:5, max:64},
                    {type:'length', name:'avatar', max:64},
                    {type:'length', name:'weight', max:64}
                ],
                //associations: [
                    //{type:'hasMany', model:'Post', name:'posts'},
                //],
                sorters: [{
                    property: 'name',
                    direction: 'ASC'
                }]
      }
   }
};



BanchaSpecHelper.init = function(/*optional*/modelDefinitionsForName) {
    Bancha.REMOTE_API = Ext.clone(BanchaSpecHelper.SampleData.remoteApiDefinition);
    
    if(Ext.isString(modelDefinitionsForName)) {
        // setup fake model
        Bancha.REMOTE_API.metadata[modelDefinitionsForName] = Ext.clone(Bancha.REMOTE_API.metadata.User);
        Bancha.REMOTE_API.actions[modelDefinitionsForName] = Ext.clone(Bancha.REMOTE_API.actions.User);
    } else if(Ext.isDefined(modelDefinitionsForName)){
        throw 'modelDefinitionsFor is not a string';
    }
    Bancha.init();
};
BanchaSpecHelper.initAndCreateSampleModel = function(modelName) {
    this.init(modelName);
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