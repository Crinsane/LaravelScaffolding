<?php namespace Gloudemans\Scaffolding;

class Scaffolding {

	protected $formGenerator;

	public function __construct(\Gloudemans\Scaffolding\Generators\FormGenerator $formGenerator)
	{
		$this->formGenerator = $formGenerator;
	}

	public function form($type, $model)
	{
		return $this->formGenerator->generate($type, $model);
	}

}