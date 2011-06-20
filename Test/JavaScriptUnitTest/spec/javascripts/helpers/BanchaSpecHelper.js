/** helpers */
BanchaSpecHelper = {};
BanchaSpecHelper.SampleData = {};
BanchaSpecHelper.SampleData.remoteApiDefinition = {
    url: 'Bancha/router.json',
    namespace: 'Bancha.RemoteStubs',
    "type":"remoting",
    "namespace": "Bancha.RemoteStubs",
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



BanchaSpecHelper.init = function() {
    Bancha.REMOTE_API = BanchaSpecHelper.SampleData.remoteApiDefinition;
    Bancha.init();
};
BanchaSpecHelper.initAndCreateSampleModel = function(modelName) {
    Bancha.REMOTE_API = Ext.clone(BanchaSpecHelper.SampleData.remoteApiDefinition);
    // setup fake model
    Bancha.REMOTE_API.metadata[modelName] = Ext.clone(Bancha.REMOTE_API.metadata.User);
    Bancha.REMOTE_API.actions[modelName] = Ext.clone(Bancha.REMOTE_API.actions.User);

    // create
    Bancha.init();
    assert.isTrue(Bancha.createModel(modelName),"Try to create fake model "+modelName);
};
BanchaSpecHelper.reset = function() {
   	// Bancha is a singleton, so get the class of
    // the singleton object and reinstanciate it
    //var className = Ext.getClassName(Ext.getClass(Bancha));
    //Bancha = Ext.create(className,{});
    delete Bancha.REMOTE_API;
    delete Bancha.RemoteStubs;
    Bancha.initialized = false;
}