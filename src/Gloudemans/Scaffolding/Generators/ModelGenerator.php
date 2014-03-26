<?php namespace Gloudemans\Scaffolding\Generators;

use Gloudemans\Scaffolding\Abstracts\BaseGenerator;

class ModelGenerator extends BaseGenerator {

	public function generate($entity)
	{
		$renderedModelTemplate = $this->renderModelTemplate($entity);

		$this->file->put(app_path().'/models/' . ucfirst($entity['name']) . '.php', $renderedModelTemplate);
	}

	protected function renderModelTemplate($entity)
	{
		$rendered = $this->handlebars->render($this->file->get(__DIR__.'/../Templates/Model.handlebars'), [
			'name' => ucfirst($entity['name']),
			'table' => strtolower($entity['table']),
			'fillable' => $this->getFillable($entity),
			'rules' => $this->getRules($entity),
			'traits' => $this->getTraits($entity),
			'relations' => $this->getRelations($entity),
			'primaryKey' => $this->getPrimaryKey($entity),
			'timestamps' => isset($entity['settings']['timestamps']) ? $entity['settings']['timestamps'] : false,
			'softdeletes' => isset($entity['settings']['softdeletes']) ? $entity['settings']['softdeletes'] : false,
			'auth' => isset($entity['settings']['auth']) ? $entity['settings']['auth'] : false
		]);

		return $rendered;
	}

	protected function getFillable($entity, $fillable = [])
	{
		foreach($entity['attributes'] as $attribute)
		{
			if($attribute['fillable'])
				$fillable[] = $attribute['name'];
		}

		return $fillable;
	}

	protected function getRules($entity, $rules = [])
	{
		foreach($entity['attributes'] as $attribute)
		{
			if($attribute['rules'])
			{
				$rules[$attribute['name']] = $attribute['rules'];
			}
		}

		return $rules;
	}

	protected function getTraits($entity, $traits = [])
	{
		if(isset($entity['settings']['editable']) && $entity['settings']['editable'])
			$traits[] = ['name' => 'Editable', 'namespace' => 'Gloudemans\Scaffolding\Traits\Editable'];

		if(isset($entity['settings']['destroyable']) && $entity['settings']['destroyable'])
			$traits[] = ['name' => 'Destroyable', 'namespace' => 'Gloudemans\Scaffolding\Traits\Destroyable'];

		return $traits;
	}

	protected function getRelations($entity)
	{
		return $entity['relations'];
	}

	protected function getPrimaryKey($entity)
	{
		if( ! isset($entity['settings']['increments']))
			return false;

		if(isset($entity['settings']['incrementId']))
		{
			return $entity['settings']['incrementId'];
		}

		return 'id';
	}

}