/*!
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * Tests for the main Bancha class
 *
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */
/*jslint browser: true, vars: true, undef: true, nomen: true, eqeq: false, plusplus: true, bitwise: true, regexp: true, newcap: true, sloppy: true, white: true */
/*jshint bitwise:true, curly:true, eqeqeq:true, forin:true, immed:true, latedef:true, newcap:true, noarg:true, noempty:true, regexp:true, undef:true, trailing:false */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, ExtSpecHelper, BanchaSpecHelper */

describe("Test that Bancha handles all date marshalling correctly", function() {
    var rs = BanchaSpecHelper.SampleData.remoteApiDefinition, // remote sample
        h = BanchaSpecHelper; // helper shortcut

    beforeEach(h.reset);
    
    it("The writer correctly formates dates in various forms accordingly to their dateFormat", function() {
        
        // prepare a test model
        var config = {
            idProperty:'id',
            fields:[
                {
                    name:'id',
                    type:'int'
                },{
                    name:'datetime',
                    type:'date',
                    dateFormat:'Y-m-d H:i:s'
                },{
                    name:'date',
                    type:'date',
                    dateFormat:'Y-m-d'
                },{
                    name:'nulldate',
                    type:'date',
                    dateFormat:'Y-m-d'
                }
            ]
        };

        // Sencha Touch and EXTJS have different structures here
        if(Ext.versions.touch) {
            config = {
                extend: 'Ext.data.Model',
                config: config
            };
        } else {
            config.extend ='Ext.data.Model';
        }

        // create it
        Ext.define('Bancha.model.JsonWithDateTimeTestModel', config);

        // create a writer for testing
        var writer = Ext.create('Bancha.data.writer.JsonWithDateTime');
        
        // sample record
        var record = Ext.create('Bancha.model.JsonWithDateTimeTestModel', {
            id       : 1,
            date     : '2012-11-30',
            datetime : '2012-11-30 10:00:05',
            nulldate : null
        });

        // test
        expect(writer.getRecordData(record)).property('date').toEqual('2012-11-30');
        expect(writer.getRecordData(record)).property('datetime').toEqual('2012-11-30 10:00:05');
        expect(writer.getRecordData(record).nulldate).toBeNull();
    });

}); //eo describe datetimewriter
    
//eof
