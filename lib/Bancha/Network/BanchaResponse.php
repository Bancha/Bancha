<?php
/**
 * Bancha Project : Combining Ext JS and CakePHP (http://banchaproject.org)
 * Copyright 2011, Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @package       bancha.libs
 * @copyright     Copyright 2011 Roland Schuetz, Kung Wong, Andreas Kern, Florian Eckerstorfer
 * @link          http://banchaproject.org Bancha Project
 * @since         Bancha v1.0
 * @license       MIT License (http://www.opensource.org/licenses/mit-license.php)
 * @author        Florian Eckerstorfer <f.eckerstorfer@gmail.com>
 * @author        Andreas Kern <andreas.kern@gmail.com>
 * @author        Roland Schuetz <mail@rolandschuetz.at>
 * @author        Kung Wong <kung.wong@gmail.com>
 */

/**
 * BanchaResponse
 *
 * @package bancha.libs
 */
// TODO: into Technical Documentation
// Doku über CRUD / was man serverseitig machen muss, damit es in cake funktioniert.
// wie sol cake developer programmieren, damit es funktioniert (für stores zu unterstützen)

// clientseitig: Create // serverseitig soll es add() heißen (auch exception etc.) (muss halt geparst werden ...)
// destroy -> delete, gibt true oder false zurück, update -> edit, update der records oder exception, read -> index (flo fragen)

class BanchaResponse extends CakeResponse
{
	// TODO: Beachten, als was der Response hineinkommt (Object / String->fehler?)
	public function addResponse(CakeResponse $response)
	{
		// TODO: implement
	}
	
	public function send()
	{
		// TODO: implement (??, maybe overwrite variables)
	}
	// TODO: EXCEPTIONS BEHANDELN
}
