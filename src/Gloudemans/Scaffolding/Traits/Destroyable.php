<?php namespace Gloudemans\Scaffolding\Traits;

trait Destroyable {

	public function getDestroyLinkAttribute()
	{
		$key = $this->getKey();
		$form = app('form');

		$link = $form->open(['route' => [strtolower(__CLASS__) . '.destroy', $key], 'method' => 'delete', 'name' =>'form' . __CLASS__ . $key]);
		$link .= '<a href="#" onclick="form' . __CLASS__ . $key . '.submit();">Delete</a>';
		return $link . $form->close();
	}

}