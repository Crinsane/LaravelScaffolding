<?php namespace Gloudemans\Scaffolding\Traits;

trait Editable {

	public function getEditLinkAttribute()
	{
		$key = $this->getKey();

		return link_to_route(strtolower(__CLASS__) . '.edit', 'Edit', [$key]);
	}

}