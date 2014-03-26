<?php namespace Gloudemans\Scaffolding\Generators;

use Gloudemans\Scaffolding\Abstracts\BaseGenerator;

class MigrationGenerator extends BaseGenerator {

	protected $schemaPath;

	protected $migrationPath;

	protected $schema;

	protected $attributes = [];

	protected $primaryKey = 'id';

	public function generate($entity)
	{
		$this->schemaPath = storage_path().'/scaffold/schema.json';
		$this->migrationPath = app_path().'/database/migrations/';
		$this->schema = $this->getCurrentSchema();

		$this->renderAttributes($entity);

		$this->renderMigration($entity);

		$this->putCurrentSchema($entity);
	}

	protected function renderMigration($entity)
	{
		if( ! isset($this->schema[$entity['table']]))
		{
			return $this->renderCreateMigration($entity);
		}

		return $this->renderUpdateMigration($entity);
	}

	protected function renderUpdateMigration($entity)
	{
		$currentAttributes = $this->schema[$entity['table']]['attributes'];
		$newAttributes = $this->attributes;

		foreach($currentAttributes as $key => $attribute)
		{
			if( ! isset($newAttributes[$key])) continue;

			$diff = array_diff($currentAttributes[$key], $newAttributes[$key]);

			if(empty($diff))
			{
				unset($currentAttributes[$key], $newAttributes[$key]);
			}
		}

		$this->renderDropAttributesMigration($entity, $currentAttributes);
		$this->renderAddAttributesMigration($entity, $newAttributes);
	}

	protected function renderCreateMigration($entity)
	{
		$renderedMigrationTemplate = $this->renderMigrationTemplate($entity, 'create', $this->attributes);
		$migrationFile = date('Y_m_d_His') . '_create_' . strtolower(str_plural($entity['name'])) . '_table.php';

		$this->file->put($this->migrationPath . $migrationFile, $renderedMigrationTemplate);
	}

	protected function renderDropAttributesMigration($entity, $attributes)
	{
		if(empty($attributes)) return;

		$attributes = $this->parseAttributes($attributes);

		$renderedMigrationTemplate = $this->renderMigrationTemplate($entity, 'dropColumns', $attributes);
		$migrationFile = date('Y_m_d_His') . '_drop_columns_' . strtolower(str_plural($entity['name'])) . '_table.php';

		$this->file->put($this->migrationPath . $migrationFile, $renderedMigrationTemplate);
	}

	protected function renderAddAttributesMigration($entity, $attributes)
	{
		if(empty($attributes)) return;

		$attributes = $this->parseAttributes($attributes);

		$renderedMigrationTemplate = $this->renderMigrationTemplate($entity, 'addColumns', $attributes);
		$migrationFile = date('Y_m_d_His') . '_add_columns_' . strtolower(str_plural($entity['name'])) . '_table.php';

		$this->file->put($this->migrationPath . $migrationFile, $renderedMigrationTemplate);
	}

	protected function renderMigrationTemplate($entity, $type, $attributes)
	{
		$rendered = $this->handlebars->render($this->file->get(__DIR__.'/../Templates/Migration' . ucfirst($type) . '.handlebars'), [
			'pluralUcfirst' => ucfirst(str_plural($entity['name'])),
			'table' => strtolower($entity['table']),
			'attributes' => $attributes
		]);

		return $rendered;
	}

	protected function parseAttributes($attributes)
	{
		foreach($attributes as $key => $attribute)
		{
			$dropType = $this->getDropType($attribute);

			$attributes[$key] = array_merge($attribute, ['dropType' => $dropType]);
		}

		return $attributes;
	}

	protected function getDropType($attribute)
	{
		if($attribute['type'] == 'softDeletes') return 'dropSoftDeletes';
		if($attribute['type'] == 'timestamps') return 'dropTimestamps';
		return 'dropColumn';
	}

	protected function updateJsonSchema($entity)
	{
		$this->schema[$entity['table']] = [
			'primary_key' => $this->primaryKey,
			'attributes' => $this->attributes
		];
	}

	protected function renderAttributes($entity)
	{
		$this->createIncrementsAttribute($entity);

		$this->createAttributes($entity);

		$this->createSoftDeletesAttributes($entity);

		$this->createTimestampsAttributes($entity);
	}

	protected function createIncrementsAttribute($entity)
	{
		if(isset($entity['settings']['increments']) && $entity['settings']['increments'])
		{
			$incrementId = $this->getIncrementId($entity);

			$this->addAttribute($incrementId, 'increments', false);
		}
	}

	protected function createAttributes($entity)
	{
		foreach($entity['attributes'] as $attribute)
		{
			$nullable = $this->attributeIsNullable($attribute);

			$this->addAttribute($attribute['name'], $attribute['type'], $nullable);
		}
	}

	protected function createSoftDeletesAttributes($entity)
	{
		if(isset($entity['settings']['softdeletes']) && $entity['settings']['softdeletes'])
		{
			$this->addAttribute('softdeletes', 'softDeletes', false);
		}
	}

	protected function createTimestampsAttributes($entity)
	{
		if(isset($entity['settings']['timestamps']) && $entity['settings']['timestamps'])
		{
			$this->addAttribute('timestamps', 'timestamps', false);
		}
	}

	protected function addAttribute($name, $type, $nullable)
	{
		$columnName = $this->getColumnName($name);

		$this->attributes[$name] = [
			'name' => $columnName,
			'type' => $type,
			'nullable' => $nullable
		];
	}

	protected function getColumnName($name)
	{
		if(in_array($name, ['softdeletes', 'timestamps'])) return '';

		return $name;
	}

	protected function getIncrementId($entity)
	{
		if(isset($entity['settings']['incrementId']))
		{
			$this->primaryKey = $entity['settings']['incrementId'];
		}

		return $this->primaryKey;
	}

	protected function attributeIsNullable($attribute)
	{
		return isset($attribute['nullable']) && $attribute['nullable'];
	}

	protected function getCurrentSchema()
	{
		if( ! $this->file->exists($this->schemaPath)) return [];

		$schema = $this->file->get($this->schemaPath);

		return json_decode($schema, true);
	}

	protected function putCurrentSchema($entity)
	{
		$this->updateJsonSchema($entity);

		$jsonSchema = json_encode($this->schema, JSON_PRETTY_PRINT);

		$this->file->put($this->schemaPath, $jsonSchema);
	}

}