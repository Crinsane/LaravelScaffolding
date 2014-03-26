<?php namespace Gloudemans\Scaffolding;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Yaml\Parser;
use Handlebars\Handlebars;

class ScaffoldingServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerGenerators();

		$this->registerScaffoldingGenerator();

		$this->registerScaffoldCommand();
		$this->registerScaffoldClearCommand();

		$this->app['scaffolding'] = $this->app->share(function($app)
		{
			$formGenerator = $app['scaffolding.generator.form'];

			return new Scaffolding($formGenerator);
		});
	}

	protected function registerGenerators()
	{
		foreach(['skeleton', 'model', 'controller', 'scenario', 'view', 'migration'] as $generator)
		{
			$this->app['scaffolding.generator.' . $generator] = $this->app->share(function($app) use ($generator)
			{
				$file = $app['files'];
				$handlebars = new Handlebars;

				$generatorClass = 'Gloudemans\Scaffolding\Generators\\' . ucfirst($generator) . 'Generator';
				return new $generatorClass($file, $handlebars);
			});
		}

		$this->app['scaffolding.generator.form'] = $this->app->share(function($app)
		{
			$form = $app['form'];
			$session = $app['session'];

			return new Generators\FormGenerator($form, $session);
		});
	}

	protected function registerScaffoldingGenerator()
	{
		$this->app['scaffolding.generator'] = $this->app->share(function($app)
		{
			$skeletonGenerator = $app['scaffolding.generator.skeleton'];
			$modelGenerator = $app['scaffolding.generator.model'];
			$controllerGenerator = $app['scaffolding.generator.controller'];
			$scenarioGenerator = $app['scaffolding.generator.scenario'];
			$viewGenerator = $app['scaffolding.generator.view'];
			$migrationGenerator = $app['scaffolding.generator.migration'];

			return new Generators\ScaffoldingGenerator($skeletonGenerator, $modelGenerator, $controllerGenerator, $scenarioGenerator, $viewGenerator, $migrationGenerator);
		});
	}

	protected function registerScaffoldCommand()
	{
		$this->app['scaffolding.command'] = $this->app->share(function($app)
		{
			$file = $app['files'];
			$yaml = new Parser();
			$scaffolding = $app['scaffolding.generator'];
			// $skeletonGenerator = $app['scaffolding.generator.skeleton'];
			// $modelGenerator = $app['scaffolding.generator.model'];
			// $controllerGenerator = $app['scaffolding.generator.controller'];
			// $scenarioGenerator = $app['scaffolding.generator.scenario'];
			// $viewGenerator = $app['scaffolding.generator.view'];
			// $migrationGenerator = $app['scaffolding.generator.migration'];

			return new Commands\ScaffoldCommand($file, $yaml, $scaffolding);// $skeletonGenerator, $modelGenerator, $controllerGenerator, $scenarioGenerator, $viewGenerator, $migrationGenerator);
		});

		$this->commands('scaffolding.command');
	}

	protected function registerScaffoldClearCommand()
	{
		$this->app['scaffolding.command.clear'] = $this->app->share(function($app)
		{
			$file = $app['files'];
			$yaml = new Parser();

			return new Commands\ScaffoldClearCommand($file, $yaml);
		});

		$this->commands('scaffolding.command.clear');
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
