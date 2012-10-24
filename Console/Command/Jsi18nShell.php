<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011-2012 StudioQ OG
 *
 * @package       Bancha
 * @subpackage    Console
 * @copyright     Copyright 2011-2012 StudioQ OG
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v 1.0.1
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 */

/**
 * Shell for I18N management.
 *
 * @package       Cake.Console.Command
 */
class Jsi18nShell extends AppShell {

/**
 * Contains database source to use
 *
 * @var string
 */
	public $dataSource = 'default';
/**
 * Contains tasks to load and instantiate
 *
 * @var array
 */
	public $tasks = array('Bancha.BanchaExtract');

/**
 * Override startup of the Shell
 *
 * @return mixed
 */
	public function startup() {
		$this->_welcome();

		if ($this->command && !in_array($this->command, array('help'))) {
			if (!config('database')) {
				$this->out(__d('cake_console', 'Your database configuration was not found. Take a moment to create one.'), true);
				return $this->DbConfig->execute();
			}
		}
	}

/**
 * Override main() for help message hook
 *
 * @return void
 */
	public function main() {
		$this->out(__d('cake_console', '<info>Bancha I18n Shell - Beta</info>'));
		$this->out(__d('cake_console', '<info>This will collect all translations from for javascript, html and template files.</info>'));
		$this->hr();

		$this->BanchaExtract->execute();
	}
/**
 * Get and configure the Option parser
 *
 * @return ConsoleOptionParser
 */
	public function getOptionParser() {
		$parser = parent::getOptionParser();
		return $parser->description(
			__d('cake_console', 'Bancha JavaScript I18n Shell generates .pot files(s) with translations from Bancha.t usages.')
			)->addSubcommand('extract', array(
				'help' => __d('cake_console', 'Extract the po translations from your application from all javascript translations'),
				'parser' => $this->BanchaExtract->getOptionParser()
			));
	}
}
