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
 */

App::uses('BanchaResponseTransformer', 'Bancha.Bancha/Network');

/**
 * BanchaResponseTransformerTest
 *
 * @package bancha.libs
 */
class BanchaResponseTransformerTest extends CakeTestCase
{

	public function testTransformIndexAction()
	{
		$cakeResponse = array(
			array(
				'Article'	=> array(
					'id'	=> 304,
					'title'	=> 'foo',
				),
			),
			array(
				'Article'	=> array(
					'id'	=> 305,
					'title'	=> 'bar',
				),
			)
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'index',
		));

		$expectedResponse = array(
			array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
			array(
				'id'	=> 305,
				'title'	=> 'bar',
			)
		);

		$this->assertEquals($expectedResponse, BanchaResponseTransformer::transform($cakeResponse, $request));
	}

	public function testTransformViewAction()
	{
		$cakeResponse = array(
			'Article'	=> array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'			=> 'view'
		));

		$expectedResponse = array(
			array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
		);

		$this->assertEquals($expectedResponse, BanchaResponseTransformer::transform($cakeResponse, $request));
	}

	public function testTransformAddAction()
	{
		$cakeResponse = array(
			'Article'	=> array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'add'
		));

		$expectedResponse = array(
			'id'	=> 304,
			'title'	=> 'foo',
		);

		$this->assertEquals($expectedResponse, BanchaResponseTransformer::transform($cakeResponse, $request));
	}

	public function testTransformEditAction()
	{
		$cakeResponse = array(
			'Article'	=> array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'edit'
		));

		$expectedResponse = array(
			'id'	=> 304,
			'title'	=> 'foo',
		);

		$this->assertEquals($expectedResponse, BanchaResponseTransformer::transform($cakeResponse, $request));
	}

	public function testTransformDeleteAction()
	{
		$cakeResponse = array();

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'delete'
		));

		$expectedResponse = array();

		$this->assertEquals($expectedResponse, BanchaResponseTransformer::transform($cakeResponse, $request));
	}

	public function testTransformExtUploadAction()
	{
		$cakeResponse = array(
			'Article'	=> array(
				'id'	=> 304,
				'title'	=> 'foo',
			),
		);

		$request = new CakeRequest();
		$request->addParams(array(
			'controller'	=> 'Articles',
			'action'		=> 'add',
			'extUpload'		=> true,
		));

		$expectedResponse = '<html><body><textarea>' . json_encode(array(
			'id'	=> 304,
			'title'	=> 'foo',
		)) . '</textarea></body></html>';

		$this->assertEquals($expectedResponse, BanchaResponseTransformer::transform($cakeResponse, $request));
	}

}
