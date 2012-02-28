/*!
 * Bancha.scaffold.Util Tests
 * Copyright(c) 2011-2012 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011-2012 Roland Schuetz
 */
/*jslint browser: true, vars: true, plusplus: true, white: true, sloppy: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, BanchaSpecHelper */

describe("Ext.form.field.VTypes tests",function() {
    
    var vtype = Ext.form.field.VTypes;
        
    it("should allow undefined file extensions when testing with #fileExtension", function() {
        expect(vtype.fileExtension('',{validExtensions:['jpg']})).toBeTruthy();
    });	
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

//eof
