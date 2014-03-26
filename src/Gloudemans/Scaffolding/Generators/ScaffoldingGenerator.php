<?php namespace Gloudemans\Scaffolding\Generators;

use Gloudemans\Scaffolding\Generators\SkeletonGenerator;
use Gloudemans\Scaffolding\Generators\ModelGenerator;
use Gloudemans\Scaffolding\Generators\ControllerGenerator;
use Gloudemans\Scaffolding\Generators\ScenarioGenerator;
use Gloudemans\Scaffolding\Generators\ViewGenerator;
use Gloudemans\Scaffolding\Generators\MigrationGenerator;

class ScaffoldingGenerator {

	public function __construct(SkeletonGenerator $skeletonGenerator,
								ModelGenerator $modelGenerator,
								ControllerGenerator $controllerGenerator,
								ScenarioGenerator $scenarioGenerator,
								ViewGenerator $viewGenerator,
								MigrationGenerator $migrationGenerator)
	{
		$this->skeletonGenerator = $skeletonGenerator;
		$this->modelGenerator = $modelGenerator;
		$this->controllerGenerator = $controllerGenerator;
		$this->scenarioGenerator = $scenarioGenerator;
		$this->viewGenerator = $viewGenerator;
		$this->migrationGenerator = $migrationGenerator;
	}

	public function generate($component, $entity = null)
	{
		$generator = $component . 'Generator';

		$this->{$generator}->generate($entity);
	}

}