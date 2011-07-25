<?php

class BanchaResponseTransformer
{

	public static function transform($response, CakeRequest $request) {
		$data = array();
		$modelName = null;

		if ($request->controller)
		{
			$modelName = Inflector::camelize(Inflector::singularize($request->controller));
		}

		if ('index' == $request->action && $modelName)
		{
			foreach ($response as $i => $element) {
				$data[$i] = $element[$modelName];
			}
			$response = $data;
		}
		else if ('view' == $request->action && $modelName)
		{
			$response = array($response[$modelName]);
		}
		else if (in_array($request->action, array('add', 'edit')) && $modelName)
		{
			$response = $response[$modelName];
		}

		if (isset($request['extUpload']) && $request['extUpload'])
		{
			return '<html><body><textarea>' . json_encode($response) . '</textarea></body></html>';
		}

		return $response;
	}

}
