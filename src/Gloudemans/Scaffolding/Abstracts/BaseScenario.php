<?php namespace Gloudemans\Scaffolding\Abstracts;

use Validator;
use ReflectionMethod;

abstract class BaseScenario {

	/**
	 * The controller that uses the scenario
	 *
	 * @var Controller
	 */
	public $controller = null;

	/**
	 * Resource model instance
	 *
	 * @var Illuminate\Database\Eloquent\Model
	 */
	protected $resource;

	/**
	 * Returns an empty instance of the resource
	 *
	 * @return Illuminate\Database\Eloquent\Model
	 */
	public function emptyResource()
	{
		return $this->resource;
	}

	/**
	 * Returns all items of the resource
	 *
	 * @return Illuminate\Support\Collection
	 */
	public function getAll()
	{
		return $this->resource->all();
	}

	/**
	 * Returns paginated items of the resource
	 *
	 * @return Illuminate\Pagination\Paginator
	 */
	public function getPaginated()
	{
		return $this->resource->paginate($this->resource->perPage);
	}

	/**
	 * Creates a new validator instance
	 *
	 * @param  array   $input
	 * @param  array   $rules
	 * @param  array   $messages
	 * @return Validator
	 */
	protected function validator($input, $rules, $messages = [])
	{
		return Validator::make($input, $rules, $messages);
	}

	/**
	 * Calls protected/public methods on a class
	 *
	 * @param  string  $methodName
	 * @param  array   $args
	 * @return mixed
	 */
	protected function invoke($methodName, $args = [])
	{
		$obj = $this->controller;
		$method = new ReflectionMethod(get_class($obj), $methodName);
		$method->setAccessible(true);
		return $method->invokeArgs($obj, $args);
	}

}