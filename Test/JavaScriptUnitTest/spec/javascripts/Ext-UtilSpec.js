/*!
 * Bancha.scaffold.Util Tests
 * Copyright(c) 2011 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011 Roland Schuetz
 */
/*jslint browser: true, vars: true, plusplus: true, white: true, sloppy: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, BanchaSpecHelper */

describe("Ext.form.field.VTypes tests",function() {
    
    var vtype = Ext.form.field.VTypes;
        
    it("should allow valid file extensions when testing with #fileExtension", function() {
        expect(vtype.fileExtension('user.jpg',{validExtensions:['jpg']})).toBeTruthy();
        expect(vtype.fileExtension('user.jpg',{validExtensions:['jpg','jpeg','gif']})).toBeTruthy();
        expect(vtype.fileExtension('user.with.points.jpg',{validExtensions:['jpeg','jpg','gif']})).toBeTruthy();
    });        
    it("should pass #fileExtension validation if no validExtensions property is undefined", function() {
        expect(vtype.fileExtension('user.jpg',{})).toBeTruthy();
    });
    it("should not allow wrong file extensions when testing with #fileExtension", function() {
        expect(vtype.fileExtension('user.jpg',{validExtensions:[]})).toBeFalsy();
        expect(vtype.fileExtension('user.doc',{validExtensions:['jpg','jpeg','gif']})).toBeFalsy();
        expect(vtype.fileExtension('user.jpg.txt',{validExtensions:['jpeg','jpg','gif']})).toBeFalsy();
    });

}); //eo vtype tests

describe("Ext.data.validations tests", function() {
    
    var validations = Ext.data.validations;
    
    // since file validation uses the same internal function as vtype we don't need extensive tests
    it("should allow validate file extensions", function() {
        var config = {type: 'file', field: 'avatar', extension:['jpg','jpeg','gif','png']};
        expect(validations.file(config,'user.gif')).toBeTruthy();
        expect(validations.file(config,'user.doc')).toBeFalsy();
    });
    
    it("should pass numberformats with no configs when they are numbers", function() {
        var config = {type: 'numberformat', field: 'euro'};
        expect(validations.numberformat(config,34)).toBeTruthy();
        expect(validations.numberformat(config,3.4)).toBeTruthy();
        expect(validations.numberformat(config,'3.4')).toBeTruthy();
    });
    
    it("should validate min of numberformats", function() {
        var config = {type: 'numberformat', field: 'euro', min:0};
        expect(validations.numberformat(config,3.4)).toBeTruthy();
        expect(validations.numberformat(config,0)).toBeTruthy();
        expect(validations.numberformat(config,-3.4)).toBeFalsy();
    });
        
    it("should validate max of numberformats", function() {
        config = {type: 'numberformat', field: 'euro', max: 10};
        expect(validations.numberformat(config,2)).toBeTruthy();
        expect(validations.numberformat(config,10)).toBeTruthy();
        expect(validations.numberformat(config,11)).toBeFalsy();
    });
        
    it("should validate min and max of numberformats", function() {
        config = {type: 'numberformat', field: 'euro', min:0, max: 10};
        expect(validations.numberformat(config,-3.4)).toBeFalsy();
        expect(validations.numberformat(config,0)).toBeTruthy();
        expect(validations.numberformat(config,10)).toBeTruthy();
        expect(validations.numberformat(config,11)).toBeFalsy();
    });
})
//eof
