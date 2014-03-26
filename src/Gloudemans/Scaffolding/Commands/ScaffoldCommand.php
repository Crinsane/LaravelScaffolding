<?php namespace Gloudemans\Scaffolding\Commands;

use Gloudemans\Scaffolding\Abstracts\BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Parser;
use Illuminate\Filesystem\Filesystem;
use Gloudemans\Scaffolding\Generators\ScaffoldingGenerator;

class ScaffoldCommand extends BaseCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'scaffold';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scaffold the application based on the entity models.';

	/**
	 * Instance of the scaffolding generator
	 *
	 * @var Gloudemans\Scaffolding\Generators\ScaffoldingGenerator
	 */
	protected $scaffolding;

	/**
	 * Holds an overview of all scaffolded entities
	 *
	 * @var array
	 */
	protected $scaffoldEntities;

	/**
	 * Create a new command instance.
	 *
	 * @param  Illuminate\Filesystem\Filesystem  					   $file
	 * @param  Symfony\Component\Yaml\Parser  						   $yaml
	 * @param  Gloudemans\Scaffolding\Generators\ScaffoldingGenerator  $scaffolding
	 * @return void
	 */
	public function __construct(Filesystem $file, Parser $yaml, ScaffoldingGenerator $scaffolding)
	{
		parent::__construct($file, $yaml);

		$this->scaffolding = $scaffolding;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->info('Scaffolding your application');

		$this->scaffoldEntities();
		$this->dumpAutoloader();
		$this->migrateDatabase();

		$this->info("\nYour application has successfully been generated");
	}

	/**
	 * Scaffold all the entity models
	 *
	 * @return void
	 */
	protected function scaffoldEntities()
	{
		if($this->initialScaffold()) $this->setupScaffoldingSkeleton();

		foreach($this->getEntityFiles() as $entityFile)
		{
			$entity = $this->getEntityFromFile($entityFile);

			$this->scaffoldEntity($entity);
		}

		$this->updateScaffoldingManifestFile();
	}

	/**
	 * Check if this is the first scaffold
	 *
	 * @return boolean
	 */
	protected function initialScaffold()
	{
		return ! $this->file->isDirectory(storage_path().'/scaffold');
	}

	/**
	 * Scaffold the entity
	 *
	 * @param  array   $entity
	 * @return void
	 */
	protected function scaffoldEntity($entity)
	{
		$this->addEntityToManifest($entity);

		$this->info("\nScaffolding the entity '" . $entity['name'] . "'");
		$this->generateEntityComponents($entity);

		$this->comment(' -> Updating the routes file');
		$this->updateRoutesFile($entity);
	}

	/**
	 * Call the migrate artisan command
	 *
	 * @return void
	 */
	protected function migrateDatabase()
	{
		$this->info("\nMigrating the database");
		$this->call('migrate');
	}

	/**
	 * Generate the main entity components
	 *
	 * @param  array   $entity
	 * @return void
	 */
	protected function generateEntityComponents($entity)
	{
		$components = ['model', 'controller', 'scenario', 'view', 'migration'];

		foreach($components as $component)
		{
			$this->scaffoldComponent($component, $entity);
		}
	}

	/**
	 * Scaffold a component
	 *
	 * @param  string   $component
	 * @param  array    $entity
	 * @return void
	 */
	protected function scaffoldComponent($component, $entity)
	{
		$this->comment(' -> Generating ' . $component);
		$this->scaffolding->generate($component, $entity);
	}

	/**
	 * Scaffold the application skeleton
	 *
	 * @return void
	 */
	protected function setupScaffoldingSkeleton()
	{
		$this->info("\nGenerating the skeleton of the application");

		$this->comment(' -> Creating layout views');
		$this->scaffolding->generate('skeleton');

		$this->comment(' -> Update routes.php');
		$this->addHomeRoute();

		$this->comment(' -> Updating composer.json');
		$this->updateComposerFile();

		$this->comment(' -> Creating scaffold storage directory');
		$this->file->makeDirectory(storage_path().'/scaffold');
	}

	/**
	 * Update the scaffolding manifest file
	 *
	 * @return void
	 */
	protected function updateScaffoldingManifestFile()
	{
		$this->info("\nUpdating scaffolding manifest file\n");
		$this->file->put(storage_path().'/scaffold/scaffold.json', json_encode($this->scaffoldEntities, JSON_PRETTY_PRINT));
	}

	/**
	 * Update the composer.json file
	 *
	 * @return void
	 */
	protected function updateComposerFile()
	{
		$composerFile = $this->file->get(base_path().'/composer.json');

		$composerAutoload = '"app/database/seeds",' . PHP_EOL . "\t\t\t" . '"app/observers",' . PHP_EOL . "\t\t\t" . '"app/scenarios",';

		$composerFile = str_replace('"app/database/seeds",', $composerAutoload, $composerFile);

		$this->file->put(base_path().'/composer.json', $composerFile);
	}

	/**
	 * Add home route to routes.php file
	 *
	 * @return void
	 */
	protected function addHomeRoute()
	{
		$routesFile = $this->file->get(app_path().'/routes.php');

		$content = "Route::get('/', ['as' => 'home', function(){return View::make('home');}]);" . PHP_EOL;

		if(strpos($routesFile, $content) === false)
		{
			$this->file->append(app_path().'/routes.php', $content);
		}
	}

	/**
	 * Update the routes.php file
	 *
	 * @param  array    $entity
	 * @return void
	 */
	protected function updateRoutesFile($entity)
	{
		$routesFile = $this->file->get(app_path().'/routes.php');

		$content = 'Route::model(\'' . strtolower($entity['name']) . '\', \'' . ucfirst($entity['name']) . '\');' . PHP_EOL . 'Route::resource(\'' . strtolower($entity['name']) . '\', \'' . ucfirst($entity['name']) . 'Controller\');' . PHP_EOL;

		if(strpos($routesFile, $content) === false)
		{
			$this->file->append(app_path().'/routes.php', $content);
		}
	}

	/**
	 * Add the entity to the manifest
	 *
	 * @param array   $entity
	 * @param void
	 */
	protected function addEntityToManifest($entity)
	{
		$entityName = $entity['name'];
		$this->scaffoldEntities[strtolower($entityName)] = str_plural($entityName);
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
			// array('example', InputArgument::REQUIRED, 'An example argument.'),
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
			// array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
		);
	}

}
