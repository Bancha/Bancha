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
        Ext.define('Bancha.test.model.JsonWithDateTimeTestModel', config);

        // create a writer for testing
        var writer = Ext.create('Bancha.data.writer.JsonWithDateTime');
        console.info('Test 1:');
        
        var expect2 = function(value) {
            return {
                property: function(property) {
                    return expect2(value.property);
                },
                toEqual: function(expected) {
                    if(value === expected) {
                        console.info('Seems fine: '+value+expected);
                    } else {
                        console.info('!!!ERROR!!!: '+value+expected);
                    }
                },
                toBeNull: function() {
                    if(value === null) {
                        console.info('Seems fine: '+value+expected);
                    } else {
                        console.info('!!!ERROR!!!: '+value+expected);
                    }
                }
            }
        }
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
        console.info('a');
        expect2(writer.getRecordData(record)).property('date').toEqual('2012-11-30');
        console.info('b');
        expect2(writer.getRecordData(record)).property('datetime').toEqual('2012-11-30 10:00:05');
        console.info('c');
        // Sencha Touch (and ExtJS 4.0) returns timestamps as numbers, ExtJS 4.1+  casts them to strings.
        // But this doesn't matter, since our backend can handle both cases. So both cases are valid
        if(typeof writer.getRecordData(record).timestamp === 'number') {
            expect2(writer.getRecordData(record)).property('timestamp').toEqual(1373584360);
            expect2(writer.getRecordData(record)).property('time').toEqual(1373584360035);
        } else {
            expect2(writer.getRecordData(record)).property('timestamp').toEqual('1373584360');
            expect2(writer.getRecordData(record)).property('time').toEqual('1373584360035');
        }
        console.info('d');
        expect2(writer.getRecordData(record).nulldate).toBeNull();
        console.info('e');
        expect2(writer.getRecordData(record).nullts).toBeNull();
        console.info('f');
        expect2(writer.getRecordData(record).nulltime).toBeNull();
        console.info('g');
        expect2(writer.getRecordData(record).undefineddate).toBeNull();
        console.info('h');

console.info('Test 2:');
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
console.info(Ext.Date.parse('2013-07-12 01:28:46', 'Y-m-d H:i:s'));
console.info(Ext.encode(record));
        expect2(writer.getRecordData(record)).property('date').toEqual('2013-07-12');
        console.info('b');
        expect2(writer.getRecordData(record)).property('datetime').toEqual('2013-07-12 01:28:46');
        console.info('c');
        // Sencha Touch (and ExtJS 4.0) returns timestamps as numbers, ExtJS 4.1+  casts them to strings.
        // But this doesn't matter, since our backend can handle both cases. So both cases are valid
        if(typeof writer.getRecordData(record).timestamp === 'number') {
            expect2(writer.getRecordData(record)).property('timestamp').toEqual(1373585326);
            expect2(writer.getRecordData(record)).property('time').toEqual(1373585326000);
        } else {
            expect2(writer.getRecordData(record)).property('timestamp').toEqual('1373585326');
            expect2(writer.getRecordData(record)).property('time').toEqual('1373585326000');
        }
        console.info('d');
        expect2(writer.getRecordData(record).nulldate).toBeNull();
        expect2(writer.getRecordData(record).nullts).toBeNull();
        console.info('e');
        expect2(writer.getRecordData(record).nulltime).toBeNull();
        expect2(writer.getRecordData(record).undefineddate).toBeNull();
    });

}); //eo describe datetimewriter
    
//eof
