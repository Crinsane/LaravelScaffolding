<?php namespace Gloudemans\Scaffolding\Generators;

use Gloudemans\Scaffolding\Abstracts\BaseGenerator;

class ControllerGenerator extends BaseGenerator {

	public function generate($entity)
	{
		$renderedControllerTemplate = $this->renderControllerTemplate($entity);

		$this->file->put(app_path().'/controllers/' . $entity['name'] . 'Controller.php', $renderedControllerTemplate);
	}

	protected function renderControllerTemplate($entity)
	{
		$rendered = $this->handlebars->render($this->file->get(__DIR__.'/../Templates/Controller.handlebars'), [
			'singularUcfirst' => ucfirst($entity['name']),
			'singular' => strtolower($entity['name']),
			'pluralUcfirst' => ucfirst(str_plural($entity['name'])),
			'plural' => strtolower(str_plural($entity['name']))
		]);

		return $rendered;
	}

}