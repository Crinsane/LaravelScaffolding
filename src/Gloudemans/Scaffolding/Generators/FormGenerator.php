<?php namespace Gloudemans\Scaffolding\Generators;

use \Illuminate\Support\Collection;
use \HtmlObject\Element;

class FormGenerator {

	/**
	 * The actual form
	 *
	 * @var string
	 */
	protected $form;

	/**
	 * The form builder instance
	 *
	 * @var Illuminate\Html\FormBuilder
	 */
	protected $formBuilder;

	/**
	 * The session manager instance
	 *
	 * @var Illuminate\Session\SessionManager
	 */
	protected $session;

	protected $names;
	protected $rules;

	/**
	 * Scaffolding class contructor
	 *
	 * @param Illuminate\Html\FormBuilder         $formBuilder
	 * @param Illuminate\Session\SessionManager   $session
	 */
	public function __construct(\Illuminate\Html\FormBuilder $formBuilder, \Illuminate\Session\SessionManager $session)
	{
		$this->formBuilder = $formBuilder;
		$this->session = $session;
	}

	public function generate($type, $model)
	{
		$this->prepareEntity($model);

		$this->generateForm($type, $model);

		return $this->form;
	}

	protected function prepareEntity($model)
	{
		$this->names = $model->names;
		$this->rules = $model->rules;
	}

	protected function generateForm($type, $model)
	{
		$this->generateFormOpen($type, $model);
		$this->generateFormElements($model);
		$this->generateFormButtons();
		$this->generateFormClose();
	}

	protected function generateFormOpen($type, $model)
	{
		$route = $this->fetchFormRoute($model, $type);
		$formMethod = $this->fetchFormMethod($type);

		$this->form = $this->formBuilder->model($model, ['route' => $route, 'method' => $formMethod]);
	}

	protected function generateFormElements($model)
	{
		$fillableAttributes = $model->getFillable();

		foreach($fillableAttributes as $attribute)
		{
			$this->generateFormGroup($attribute, $model);
		}
	}

	protected function generateFormButtons()
	{
		$this->form .= $this->formBuilder->button('Submit', ['type' => 'submit', 'class' => 'btn btn-primary']);
	}

	protected function generateFormClose()
	{
		$this->form .= $this->formBuilder->close();
	}

	protected function generateFormGroup($attribute, $model)
	{
		$group = Element::div(null, ['class' => 'form-group']);

		$this->generateLabelAndInput($group, $attribute);

		if($this->session->has('errors') && $this->session->get('errors')->has($attribute))
		{
			$group->addClass('has-error');
			$this->addValidationErrors($group, $this->session->get('errors')->get($attribute));
		}

		$this->form .= $group;
	}

	protected function generateLabelAndInput(&$group, $attribute)
	{
		$this->generateLabel($group, $attribute);
		$this->generateInput($group, $attribute);
	}

	protected function generateLabel(&$group, $attribute)
	{
		$text = isset($this->names[$attribute]) ? $this->names[$attribute] : ucfirst($attribute);

		$group->nest($this->formBuilder->label($attribute, $text, ['class' => 'control-label']));
	}

	protected function generateInput(&$group, $attribute)
	{
		$type = $this->fetchInputType($attribute);

		$rules = $this->generateValidationRules($attribute);

		$group->nest($this->formBuilder->input($type, $attribute, null, array_merge($rules, ['class' => 'form-control'])));
	}

	protected function generateValidationRules($attribute)
	{
		return [];

		$rules = isset($this->rules[$attribute]) ? $this->rules[$attribute] : null;

		if(empty($rules)) return [];

		// $rules = explode('|', $rules);

		$validationRules = [];

		foreach($rules as $rule)
		{
			$validationRules = array_merge($validationRules, $this->fetchRule($rule));
		}

		return $validationRules;
	}

	protected function fetchRule($rule)
	{
		$rule = explode(':', $rule);

		switch($rule[0])
		{
			case 'required':
				return ['required' => 'required'];

			case 'min':
				return ['pattern' => '.{' . $rule[1] . ',}'];

			default:
				return [];
		}
	}

	protected function addValidationErrors(&$group, $errors)
	{
		foreach($errors as $error)
		{
			$group->nest(Element::div($error, ['class' => 'help-block text-danger']));
		}
	}

	protected function fetchFormRoute($model, $type)
	{
		$entity = $this->fetchEntityName($model);
		$entityKey = $model->getKeyName();

		return [$entity . '.' . $type, $model->{$entityKey}];
	}

	protected function fetchEntityName($model)
	{
		return strtolower(get_class($model));
	}

	protected function fetchFormMethod($type)
	{
		return $type == 'store' ? 'post' : 'put';
	}

	protected function fetchInputType($attribute)
	{
		if($attribute == 'email') return 'email';
		if($attribute == 'phone') return 'phone';
		if($attribute == 'password') return 'password';

		return 'text';
	}

}