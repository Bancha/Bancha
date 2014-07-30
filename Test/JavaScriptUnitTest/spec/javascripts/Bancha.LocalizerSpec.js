/*!
 *
 * Bancha : Seamlessly integrates CakePHP with Ext JS and Sencha Touch (http://bancha.io)
 * Copyright 2011-2014 codeQ e.U.
 *
 * Tests for the main Bancha class
 *
 * @copyright     Copyright 2011-2014 codeQ e.U.
 * @link          http://bancha.io Bancha
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @version       Bancha v PRECOMPILER_ADD_RELEASE_VERSION
 *
 * For more information go to http://bancha.io
 */

describe("Bancha localizer functions for i18n support", function() {

    it("The Localizer translates strings", function() {

        // add some fake data
        Bancha.Localizer.locales = new Ext.util.HashMap();
        Bancha.Localizer.locales.add('eng', new Ext.util.HashMap()); // no translations
        var deu = new Ext.util.HashMap();
        deu.add('translatable string', 'Übersetzbarer String');
        deu.add('translatable %s', 'Übersetzbarer %s');
        deu.add('fun', 'Spaß');
        Bancha.Localizer.locales.add('deu', deu);


        // unit tests
        expect(Bancha.Localizer.getLocalizedString('translatable string')).toEqual('translatable string'); // no translation in english
        expect(Bancha.Localizer.getLocalizedString(1)).toEqual('1'); // always return a string
        expect(typeof Bancha.Localizer.getLocalizedString(1)).toEqual('string'); // always return a string

        // no translation in english
        expect(Bancha.Localizer.getLocalizedStringWithReplacements('translatable string')).toEqual('translatable string');
        // no translation, with str replace
        expect(Bancha.Localizer.getLocalizedStringWithReplacements('translatable %s', 'fun')).toEqual('translatable fun');

        Bancha.Localizer.currentLang = 'deu';

        expect(Bancha.Localizer.getLocalizedStringWithReplacements('translatable string')).toEqual('Übersetzbarer String');
        // second argument is expected to be data, so it's not translated
        expect(Bancha.Localizer.getLocalizedStringWithReplacements('translatable %s', 'fun')).toEqual('Übersetzbarer fun');
        expect(Bancha.Localizer.getLocalizedStringWithReplacements('translatable %s', Bancha.Localizer.getLocalizedString('fun'))).
                toEqual('Übersetzbarer Spaß');

        // Bancha.t should be a shortcut
        expect(Bancha.t('translatable string')).toEqual('Übersetzbarer String');
    });

    it("The Localizer allows the usage of multiple %s always replacing just one per argument", function() {
        expect(Bancha.Localizer.getLocalizedStringWithReplacements('I have many %s and all are %s and should be written to the %s.',
            'many replacements', 'very important', 'correct place')).
            toEqual('I have many many replacements and all are very important and should be written to the correct place.');
    });

});
