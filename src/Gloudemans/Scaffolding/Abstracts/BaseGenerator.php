<?php namespace Gloudemans\Scaffolding\Abstracts;

use Illuminate\Filesystem\Filesystem;
use Handlebars\Handlebars;

abstract class BaseGenerator {

	/**
	 * Instance of the filesystem
	 *
	 * @var Illuminate\Filesystem\Filesystem
	 */
	protected $file;

	/**
	 * Instance of handlebars engine
	 *
	 * @var Handlebars\Handlebars
	 */
	protected $handlebars;

	/**
	 * Base generator constructor
	 *
	 * @param  Illuminate\Filesystem\Filesystem  $file
	 * @param  Handlebars\Handlebars             $handlebars
	 * @return void
	 */
	public function __construct(Filesystem $file, Handlebars $handlebars)
	{
		$this->file = $file;
		$this->handlebars = $handlebars;
	}

	/**
	 * Generate from the entity
	 *
	 * @param  array  $entity
	 * @return void
	 */
	public abstract function generate($entity);

}