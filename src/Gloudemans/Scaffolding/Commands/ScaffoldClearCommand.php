<?php namespace Gloudemans\Scaffolding\Commands;

use Gloudemans\Scaffolding\Abstracts\BaseCommand;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ScaffoldClearCommand extends BaseCommand {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'scaffold:clear';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Clear the entire scaffolded application';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
		$this->info('Cleaning up the scaffolding');

		$this->info("\nRollback the migrations");
		$this->rollbackMigrations();

		$this->info("\nDeleting application skeleton");
		$this->deleteSkeleton();

		foreach($this->getEntityFiles() as $entityFile)
		{
			$entity = $this->getEntityFromFile($entityFile);

			$this->info("\nDeleting the entity '" . $entity['name'] . "'");

			$this->deleteEntityComponents($entity);

			$this->removeEntityFromRoute($entity);
		}

		$this->info("\nDeleting scenarios");
		$this->file->deleteDirectory(app_path().'/scenarios');

		$this->info("\nDeleting migrations");
		$this->file->cleanDirectory(app_path().'/database/migrations');

		$this->dumpAutoloader();

		$this->info("\nThe scaffolding has successfully been cleaned");
	}

	protected function rollbackMigrations()
	{
		$this->call('migrate:reset');
	}

	protected function deleteSkeleton()
	{
		$this->comment(' -> Deleting layout views');
		$this->file->deleteDirectory(app_path().'/views/partials');
		$this->file->delete(app_path().'/views/home.blade.php');
		$this->file->delete(app_path().'/views/master.blade.php');

		$this->comment(' -> Deleting scaffolding storage directory');
		$this->file->deleteDirectory(storage_path().'/scaffold');

		$this->comment(' -> Update routes file');
		$this->removeHomeRoute();

		$this->comment(' -> Rollback composer.json changes');
		$this->rollbackComposerChanges();
	}

	protected function removeHomeRoute()
	{
		$routesFile = $this->file->get(app_path().'/routes.php');
		$content = "Route::get('/', ['as' => 'home', function(){return View::make('home');}]);";

		$this->file->put(app_path().'/routes.php', str_replace($content, '', $routesFile));
	}

	protected function deleteEntityComponents($entity)
	{
		$this->comment(' -> Deleting views');
		$this->file->deleteDirectory(app_path().'/views/' . strtolower($entity['name']));

		$this->comment(' -> Deleting controller');
		$this->file->delete(app_path().'/controllers/' . $entity['name'] . 'Controller.php');

		$this->comment(' -> Deleting model');
		$this->file->delete(app_path().'/models/' . ucfirst($entity['name']) . '.php');
	}

	protected function removeEntityFromRoute($entity)
	{
		$routesFile = $this->file->get(app_path().'/routes.php');

		$content = 'Route::model(\'' . strtolower($entity['name']) . '\', \'' . ucfirst($entity['name']) . '\');' . PHP_EOL . 'Route::resource(\'' . strtolower($entity['name']) . '\', \'' . ucfirst($entity['name']) . 'Controller\');' . PHP_EOL;

		$this->file->put(app_path().'/routes.php', str_replace($content, '', $routesFile));
	}

	protected function rollbackComposerChanges()
	{
		$composerFile = $this->file->get(base_path().'/composer.json');

		$composerAutoload = PHP_EOL . "\t\t\t" . '"app/observers",' . PHP_EOL . "\t\t\t" . '"app/scenarios",';

		$composerFile = str_replace($composerAutoload, '', $composerFile);

		$this->file->put(base_path().'/composer.json', $composerFile);
	}

}
