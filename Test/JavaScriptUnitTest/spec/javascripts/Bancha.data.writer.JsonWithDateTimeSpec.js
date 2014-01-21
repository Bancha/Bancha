/*!
 *
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2013 codeQ e.U.
 *
 * Tests for the main Bancha class
 *
 * @copyright     Copyright 2011-2013 codeQ e.U.
 * @link          http://banchaproject.org Bancha Project
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

describe("Test that Bancha handles all date marshalling correctly", function() {
    var h = BanchaSpecHelper; // helper shortcut

    beforeEach(h.reset);

    it("The writer correctly formates dates in various forms accordingly to their dateFormat", function() {

        // This test fails in PhantomJS, because the date, datetime and timestamp strings
        // are set to undefined in PhantomJS
        var isPhantomJS = (typeof phantom !== 'undefined' || typeof _phantom !== 'undefined');
        if(isPhantomJS) {
            return; // This test works in the Browser, but fails using phantomjs test runner
        }

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
                    name:'timestamp',
                    type:'date',
                    dateFormat:'timestamp'
                },{
                    name:'time',
                    type:'date',
                    dateFormat:'time'
                },{
                    name:'nulldate',
                    type:'date',
                    dateFormat:'Y-m-d'
                },{
                    name:'nullts',
                    type:'date',
                    dateFormat:'timestamp'
                },{
                    name:'nulltime',
                    type:'date',
                    dateFormat:'time'
                },{
                    name:'undefineddate',
                    type:'date',
                    dateFormat:'Y-m-d'
                }
            ]
        };

        // Sencha Touch and Ext JS have different structures here
        if(Ext.versions.touch) {
            config = {
                extend: 'Ext.data.Model',
                config: config
            };
        } else {
            config.extend ='Ext.data.Model';
        }

        // create it
        Ext.define('Bancha.test.model.JsonWithDateTimeTestModel', config);

        // create a writer for testing
        var writer = Ext.create('Bancha.data.writer.JsonWithDateTime');

        // sample record
        var record = Ext.create('Bancha.test.model.JsonWithDateTimeTestModel', {
            id       : 1,
            date     : '2012-11-30',
            datetime : '2012-11-30 10:00:05',
            timestamp: 1373584360,
            time     : 1373584360035, // javascript time in miliseconds
            nulldate : null,
            nullts   : null,
            nulltime : null
        });

        // test
        expect(writer.getRecordData(record)).property('date').toEqual('2012-11-30');
        expect(writer.getRecordData(record)).property('datetime').toEqual('2012-11-30 10:00:05');
        // Sencha Touch (and Ext JS 4.0) returns timestamps as numbers, Ext JS 4.1+  casts them to strings.
        // But this doesn't matter, since our backend can handle both cases. So both cases are valid
        if(typeof writer.getRecordData(record).timestamp === 'number') {
            expect(writer.getRecordData(record)).property('timestamp').toEqual(1373584360);
            expect(writer.getRecordData(record)).property('time').toEqual(1373584360035);
        } else {
            expect(writer.getRecordData(record)).property('timestamp').toEqual('1373584360');
            expect(writer.getRecordData(record)).property('time').toEqual('1373584360035');
        }
        expect(writer.getRecordData(record).nulldate).toBeNull();
        expect(writer.getRecordData(record).nullts).toBeNull();
        expect(writer.getRecordData(record).nulltime).toBeNull();
        expect(writer.getRecordData(record).undefineddate).toBeNull();


        // sample record
        record = Ext.create('Bancha.test.model.JsonWithDateTimeTestModel', {
            id       : 1,
            date     : Ext.Date.parse('2013-07-12 01:28:46', 'Y-m-d H:i:s'),
            datetime : Ext.Date.parse('2013-07-12 01:28:46', 'Y-m-d H:i:s'),
            timestamp: Ext.Date.parse('2013-07-12 01:28:46', 'Y-m-d H:i:s'),
            time     : Ext.Date.parse('2013-07-12 01:28:46', 'Y-m-d H:i:s'),
            nulldate : null,
            nullts   : null,
            nulltime : null
        });

        // test
        expect(writer.getRecordData(record)).property('date').toEqual('2013-07-12');
        expect(writer.getRecordData(record)).property('datetime').toEqual('2013-07-12 01:28:46');
        // Sencha Touch (and Ext JS 4.0) returns timestamps as numbers, Ext JS 4.1+  casts them to strings.
        // But this doesn't matter, since our backend can handle both cases. So both cases are valid
        if(typeof writer.getRecordData(record).timestamp === 'number') {
            expect(writer.getRecordData(record)).property('timestamp').toEqual(1373585326);
            expect(writer.getRecordData(record)).property('time').toEqual(1373585326000);
        } else {
            expect(writer.getRecordData(record)).property('timestamp').toEqual('1373585326');
            expect(writer.getRecordData(record)).property('time').toEqual('1373585326000');
        }
        expect(writer.getRecordData(record).nulldate).toBeNull();
        expect(writer.getRecordData(record).nullts).toBeNull();
        expect(writer.getRecordData(record).nulltime).toBeNull();
        expect(writer.getRecordData(record).undefineddate).toBeNull();
    });

}); //eo describe datetimewriter
