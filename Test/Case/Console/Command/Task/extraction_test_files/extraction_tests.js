var x = function() {
	Bancha.t('Bancha supports simple strings.');

	return function() {
		Bancha.t("Bancha supports simple strings with double quotes.");
	};
};
Bancha.t("Bancha supports even ugly javascript code without a semincolon after the function call.");
Bancha.t('Can support sprintf statements with multiple values: %s. %s', 'input1', 'input2');


Bancha.t('Bancha recognizes multi-lines '+
	'strings');

Bancha.t(['Bancha even recognizes ',
	'joined multi-lines, a ',
	'best practice for multi-line strings.'].join(''));

Bancha.t(['Bancha recognizes',
	' joined multi-lines, a special join value.'].join(','));

Bancha.t(['Bancha recognizes ',
	'strange mixes between concatination '+
	'and joined strings.'].join(''));

Bancha.t(['Bancha recognizes ',
	'special joined multi-lines with sprintf like values, %s.'].join('x'), "la");


// duplicates
Bancha.t("Bancha supports simple with double.");

// ternary
Bancha.t(something ? "Bancha collect both strings for conditional strings" : "Yes, even the second");

// sometimes the translatable strings can have strange characters
Bancha.t('( bla');
Bancha.t('I can\'t go home!');
Bancha.t('() bla');
Bancha.t(') bla');
Bancha.t(':?{} bla');
Bancha.t('I quote "bla"');
Bancha.t("I quote 'bla'");
Bancha.t('I quote "bla"' + " and 'bla'");
