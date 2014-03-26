<?php namespace Gloudemans\Scaffolding\Generators;

use Gloudemans\Scaffolding\Abstracts\BaseGenerator;

class ScenarioGenerator extends BaseGenerator {

	public function generate($entity)
	{
		if( ! $this->file->isDirectory(app_path().'/scenarios'))
			$this->file->makeDirectory(app_path().'/scenarios');

		$renderedScenarioTemplate = $this->renderScenarioTemplate($entity);

		$this->file->put(app_path().'/scenarios/' . $entity['name'] . 'Scenario.php', $renderedScenarioTemplate);
	}

	protected function renderScenarioTemplate($entity)
	{
		$rendered = $this->handlebars->render($this->file->get(__DIR__.'/../Templates/Scenario.handlebars'), [
			'singularUcfirst' => ucfirst($entity['name']),
			'singular' => strtolower($entity['name'])
		]);

		return $rendered;
	}

}