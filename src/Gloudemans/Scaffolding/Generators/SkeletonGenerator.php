<?php namespace Gloudemans\Scaffolding\Generators;

use Gloudemans\Scaffolding\Abstracts\BaseGenerator;

class SkeletonGenerator extends BaseGenerator {

	public function generate($entity)
	{
		$this->setupBaseViewLayout();
	}

	protected function setupBaseViewLayout()
	{
		$viewPath = app_path().'/views/';
		$templatePath = __DIR__.'/../Templates/skeleton/';

		$this->file->put($viewPath . 'master.blade.php', $this->renderTemplate($this->file->get($templatePath.'Master.handlebars')));
		$this->file->put($viewPath . 'home.blade.php', $this->renderTemplate($this->file->get($templatePath.'Home.handlebars')));

		$this->file->makeDirectory($viewPath . 'partials');
		$this->file->put($viewPath . 'partials/navigation.blade.php', $this->renderTemplate($this->file->get($templatePath.'Navigation.handlebars')));
		$this->file->put($viewPath . 'partials/notifications.blade.php', $this->renderTemplate($this->file->get($templatePath.'Notifications.handlebars')));
	}

	protected function renderTemplate($template)
	{
		$rendered = $this->handlebars->render($template, []);

		return $rendered;
	}

}