/*!
 *
 * Bancha Project : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://banchaproject.org)
 * Copyright 2011-2014 codeQ e.U.
 *
 * Test all additioanl Ext.data.validations
 *
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://banchaproject.org
 */

describe("Ext.data.validations tests", function() {

    var validations = Ext.data.validations;

    // since file validation uses the same internal function as vtype we don't need extensive tests
    it("should allow validate file extensions", function() {
        var config = {type: 'file', field: 'avatar', extension:['jpg','jpeg','gif','png']};
        expect(validations.file(config,'user.gif')).toBeTruthy();
        expect(validations.file(config,'user.doc')).toBeFalsy();
    });

    it("should pass ranges with no configs when they are numbers", function() {
        var config = {type: 'range', field: 'euro'};
        expect(validations.range(config,34)).toBeTruthy();
        expect(validations.range(config,3.4)).toBeTruthy();
        expect(validations.range(config,'3.4')).toBeTruthy();
    });

    it("should validate min of ranges", function() {
        var config = {type: 'range', field: 'euro', min:0};
        expect(validations.range(config,3.4)).toBeTruthy();
        expect(validations.range(config,0)).toBeTruthy();
        expect(validations.range(config,-3.4)).toBeFalsy();
    });

    it("should validate max of ranges", function() {
        var config = {type: 'range', field: 'euro', max: 10};
        expect(validations.range(config,2)).toBeTruthy();
        expect(validations.range(config,10)).toBeTruthy();
        expect(validations.range(config,11)).toBeFalsy();
    });

    it("should validate min and max of ranges", function() {
        var config = {type: 'range', field: 'euro', min:0, max: 10};
        expect(validations.range(config,-3.4)).toBeFalsy();
        expect(validations.range(config,0)).toBeTruthy();
        expect(validations.range(config,10)).toBeTruthy();
        expect(validations.range(config,11)).toBeFalsy();
    });

    // Tests below are for deprecated numberformat
    BanchaSpecHelper.init();
    Ext.Logger = Ext.Logger || {};
    var deprecate = Ext.Logger.deprecate;
    Ext.Logger.deprecate = function() {};

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
        var config = {type: 'numberformat', field: 'euro', max: 10};
        expect(validations.numberformat(config,2)).toBeTruthy();
        expect(validations.numberformat(config,10)).toBeTruthy();
        expect(validations.numberformat(config,11)).toBeFalsy();
    });

    it("should validate min and max of numberformats", function() {
        var config = {type: 'numberformat', field: 'euro', min:0, max: 10};
        expect(validations.numberformat(config,-3.4)).toBeFalsy();
        expect(validations.numberformat(config,0)).toBeTruthy();
        expect(validations.numberformat(config,10)).toBeTruthy();
        expect(validations.numberformat(config,11)).toBeFalsy();
    });
    Ext.Logger.deprecate = deprecate || Ext.emptyFn;
});
