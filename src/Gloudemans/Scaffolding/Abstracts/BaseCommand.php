<?php namespace Gloudemans\Scaffolding\Abstracts;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Yaml\Parser;
use Illuminate\Filesystem\Filesystem;

class BaseCommand extends Command {

	/**
	 * Instance of the filesystem
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $file;

	/**
	 * Instance of the Yaml parser
	 *
	 * @var Symfony\Component\Yaml\Parser
	 */
	protected $yaml;

	/**
	 * Create a new command instance.
	 *
	 * @param  Illuminate\Filesystem\Filesystem  $file
	 * @return void
	 */
	public function __construct(Filesystem $file, Parser $yaml)
	{
		parent::__construct();

		$this->file = $file;
		$this->yaml = $yaml;
	}

	/**
	 * Get the entity yaml files
	 *
	 * @return array
	 */
	protected function getEntityFiles()
	{
		$entitiesPath = base_path() . '/entities';
		return $this->file->files($entitiesPath);
	}

	/**
	 * Parse the entity yaml file
	 *
	 * @param  string    $entityFile
	 * @return array
	 */
	protected function getEntityFromFile($entityFile)
	{
		$fileContent = $this->file->get($entityFile);
		return $this->yaml->parse($fileContent);
	}

	/**
	 * Call the dump-autoload artisan command
	 *
	 * @return void
	 */
	protected function dumpAutoloader()
	{
		$this->call('dump-autoload');
	}

}