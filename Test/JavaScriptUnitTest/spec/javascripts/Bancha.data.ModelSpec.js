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
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, spyOn, Mock, BanchaSpecHelper */

describe("Bancha.data.Model tests", function() {
    var rs = BanchaSpecHelper.SampleData.remoteApiDefinition, // remote sample
        h = BanchaSpecHelper; // helper shortcut

    beforeEach(h.reset);

    it("should apply the cake schema to all Bancha models defined in the Bancha.model namespace", function() {
        
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
    });

    it("should apply the cake schema to all models defined in the Bancha.model namespace", function() {
        // setup model metadata
        h.init('CreateModelUser');
        expect(
            Bancha.createModel('CreateModelUser', {
                additionalSettings: true
        })).toBeTruthy();
        
        // check if the model really got created
        var model = Ext.ClassManager.get('Bancha.model.CreateModelUser');
        expect(model).toBeModelClass('Bancha.model.CreateModelUser');
        
        // check if the additional config was used
        if(h.isExt) {
            // for ext it is directly applied
            expect(model.prototype.additionalSettings).toBeTruthy();
        } else {
            // for touch it is applied inside the 'config' config
            expect(model.prototype.config.additionalSettings).toBeTruthy();
        }

        // create a mock object for the proxy
        var mockProxy = Mock.Proxy();
        model.setProxy(mockProxy);
        
        // test if the model saves data through ext direct
        var user = Ext.create('Bancha.model.CreateModelUser',{
            firstname: 'Micky',
            lastname: 'Mouse'
        });

        // define expectations for remote stub calls
        // user.save() should result in one create action
        mockProxy.expect("create");

        // test
        user.save();
        
        //verify the expectations were met
        // TODO Not yet working in touch: http://www.sencha.com/forum/showthread.php?188764-How-to-mock-a-proxy
        if(h.isExt) {
            mockProxy.verify();
        }
    });

});

//eof
