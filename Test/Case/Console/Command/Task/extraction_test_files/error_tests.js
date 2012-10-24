
// with variable
Bancha.t(str);
Bancha.t("Bancha ignore strings with "+variables+' attached');

// non-translations
Bancha.tt("Bancha supports simple with double.");
Bancha.t = 'lala';
__('standard cake string');

// TODO: don't recognize this as normal string: Bancha.t('<?php echo $lala; ?>');
// TODO Bancha.t("Bancha recognizes if the number of %s don't fit");
// TODO : Trow a meaningfull error here (should use __() instead): Bancha.t(<?php echo $lala; ?>);
