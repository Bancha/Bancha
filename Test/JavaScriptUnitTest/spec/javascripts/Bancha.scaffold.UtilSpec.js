/*!
 * Bancha.scaffold.Util Tests
 * Copyright(c) 2011 Roland Schuetz
 * @author Roland Schuetz <mail@rolandschuetz.at>
 * @copyright (c) 2011 Roland Schuetz
 */
/*jslint browser: true, vars: true, plusplus: true, white: true, sloppy: true */
/*global Ext, Bancha, describe, it, beforeEach, expect, jasmine, Mock, BanchaSpecHelper */

describe("Bancha.scaffold.Util tests",function() {
        
    it("should pass all Bancha.scaffold.Util.toFirstUpper tests", function() {
        var util = Bancha.scaffold.Util;
        expect('User').toEqual(util.toFirstUpper('user'));
        expect('UserName').toEqual(util.toFirstUpper('userName'));
    });


    it("should pass all Bancha.scaffold.Util.humanize tests", function() {
        var util = Bancha.scaffold.Util;

        // first upper case
        expect('User').toEqual(util.humanize('user'));

        // ids
        expect('User').toEqual(util.humanize('user_id'));

        // underscores
        expect('User name').toEqual(util.humanize('user_name'));
        expect('Name with many spaces').toEqual(util.humanize('name_with_many_spaces'));

        // camel case
        expect('User name').toEqual(util.humanize('userName'));
        expect('Name with many spaces').toEqual(util.humanize('nameWithManySpaces'));

        // shouldn't change normal text
        expect('John Smith').toEqual(util.humanize('John Smith'));
        expect('This is a normal text with spaces, Upper case words and all UPPER CASE words!'
               ).toEqual(util.humanize('This is a normal text with spaces, Upper case words '+
               'and all UPPER CASE words!'));
    });
    
}); //eo scaffold util functions

//eof
