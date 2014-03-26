<?php namespace Gloudemans\Scaffolding\Generators;

use Gloudemans\Scaffolding\Abstracts\BaseGenerator;

class ViewGenerator extends BaseGenerator {

	public function generate($entity)
	{
		if( ! $this->file->isDirectory(app_path().'/views/' . strtolower($entity['name'])))
			$this->file->makeDirectory(app_path().'/views/' . strtolower($entity['name']));

		$views = ['index', 'show', 'create', 'edit'];

		foreach($views as $view)
		{
			$this->renderView($view, $entity);
		}
	}

	protected function renderView($view, $entity)
	{
		$renderedViewTemplate = $this->renderViewTemplate($entity, ucfirst($view));

		$this->file->put(app_path().'/views/' . strtolower($entity['name']) . '/' . $view . '.blade.php', $renderedViewTemplate);
	}

	protected function renderViewTemplate($entity, $view)
	{
		$rendered = $this->handlebars->render($this->file->get(__DIR__.'/../Templates/views/View' . $view . '.handlebars'), [
			'pluralUcfirst' => ucfirst(str_plural($entity['name'])),
			'plural' => strtolower(str_plural($entity['name'])),
			'singularUcfirst' => ucfirst($entity['name']),
			'singular' => strtolower($entity['name'])
		]);

		return $rendered;
	}

}