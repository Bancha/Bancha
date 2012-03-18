/*!
 * Bancha Tests
 * Copyright(c) 2011-2012 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011-2012 Roland Schuetz
 */
/*jslint browser: true, vars: true, plusplus: true, white: true, sloppy: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, BanchaSpecHelper */

describe("Bancha Singleton - for Sencha Touch 2.0", function() {
        var rs = BanchaSpecHelper.SampleData.remoteApiDefinition, // remote sample
            h = BanchaSpecHelper; // helper shortcut
    
        beforeEach(h.reset);
        
        it("should create a valid Model for Sencha Touch", function() {
            
            
            /*
             * Sencha Touch seems to have a small bug here
             * See http://www.sencha.com/forum/showthread.php?188759-Ext.isReady-needs-to-be-triggered-by-Ext.onReady&p=758542#post758542
             */
            Ext.onReady(Ext.emptyFn);
            
            
            // setup model metadata
            h.init('CreateTouchModelUser');

            // create a mock object for the proxy
            var mockProxy = Mock.Proxy();

            // should create a user defintion
            expect(
                Bancha.createModel('CreateTouchModelUser', {
                    additionalSettings: true,
                    proxy: mockProxy
            })).toBeTruthy();

            // check if the model really got created
            var model = Ext.ClassManager.get('Bancha.model.CreateTouchModelUser');
            expect(model).toBeModelClass('Bancha.model.CreateTouchModelUser');
            
            // check if the additional config was used in the right place
            expect(model.prototype.additionalSettings).toBeTruthy();

            // test if the model saves data through ext direct
            var user = Ext.create('Bancha.model.CreateTouchModelUser',{
                firstname: 'Micky',
                lastname: 'Mouse'
            });

            // define expectations for remote stub calls
            // user.save() should result in one create action
            mockProxy.expect("create");

            // test
            user.save();

            //verify the expectations were met
            mockProxy.verify();
            
            
            // verify all fields are reciognized
            expect(model.getFields()).toEqual(8);
            
            // verify all validation rules are reciognized
            expect(model.getValidations()).toEqual(5);
            
            // verify all associations rules are reciognized
            expect(model.getAssociations()).toEqual(2);
        });
}); //eo describe basic functions for sencha touch
    
//eof
