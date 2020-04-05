<?php
namespace Bitrix\Report\VisualConstructor;

use Bitrix\Main\ArgumentException;
use Bitrix\Report\VisualConstructor\Fields\Base;
use Bitrix\Report\VisualConstructor\Fields\Html;
use Bitrix\Report\VisualConstructor\Internal\Error\Error;
use Bitrix\Report\VisualConstructor\Internal\Error\IErrorable;

/**
 * Class Form
 * @package Bitrix\Report\VisualConstructor
 */
class Form implements IErrorable
{
	private $id;
	private $name;
	private $action;
	private $method;
	private $class = array();
	private $prefix = '';
	private $postfix = '';
	private $dataAttributes = array();


	private $fields = array();

	private $errors = array();


	/**
	 * Add field to form.
	 *
	 * @param Base|mixed $field Field to add.
	 * @return $this
	 */
	public function add($field)
	{
		if (!($field instanceof Base))
		{
			$field = $this->convertToField($field);
		}

		if ($field->getKey())
		{
			$this->fields[$field->getKey()] = $field;
		}
		else
		{
			$this->fields[] = $field;
		}
		$field->setForm($this);
		return $this;
	}

	/**
	 * Add field before target field.
	 *
	 * @param Base $newField Field to add.
	 * @param Base $targetField Target field before which to add.
	 * @return void
	 */
	public function addFieldBefore($newField, $targetField)
	{
		if (!($newField instanceof Base))
		{
			$newField = $this->convertToField($newField);
		}

		$indexToInsert = null;
		$newFieldsList = array();
		foreach ($this->fields as $key => $field)
		{
			if ($field === $targetField)
			{
				$newField->setForm($this);
				if ($newField->getKey())
				{
					$newFieldsList[$newField->getKey()] = $newField;
				}
				else
				{
					$newFieldsList[] = $newField;
				}
			}
			$newFieldsList[$key] = $field;
		}

		$this->fields = $newFieldsList;
	}

	/**
	 * Add field after target field.
	 *
	 * @param Base $newField Field to add.
	 * @param Base $targetField Target field after which to add.
	 * @return void
	 */
	public function addFieldAfter($newField, $targetField)
	{
		if (!($newField instanceof Base))
		{
			$newField = $this->convertToField($newField);
		}

		$indexToInsert = null;
		$newFieldsList = array();
		foreach ($this->fields as $key => $field)
		{
			$newFieldsList[$key] = $field;
			if ($field === $targetField)
			{
				$newField->setForm($this);
				if ($newField->getKey())
				{
					$newFieldsList[$newField->getKey()] = $newField;
				}
				else
				{
					$newFieldsList[] = $newField;
				}
			}
		}

		$this->fields = $newFieldsList;

	}

	/**
	 * Try to convert to Html field.
	 * @param mixed $options String or int for convert.
	 * @return Html
	 * @throws ArgumentException
	 */
	private function convertToField($options)
	{
		if (is_string($options) || is_int($options))
		{
			return new Html($options);
		}
		else
		{
			throw new ArgumentException('Can\'t convert to form element');
		}
	}
	/**
	 * @return Base[]
	 */
	public function getFields()
	{
		return $this->fields;
	}

	/**
	 * @param string $key Unique key.
	 * @return Base|null
	 */
	public function getField($key)
	{
		static $fields;
		if (!$fields)
		{
			$fields = $this->getFields();
		}
		if (!isset($fields[$key]))
		{
			$this->errors[] = new Error('No field with key:' . $key);
			$result = null;
		}
		else
		{
			$result = $fields[$key];
		}
		return $result;
	}


	/**
	 * Collect all elements.
	 * Print form html.
	 *
	 * @return void
	 */
	public function render()
	{
		echo htmlspecialcharsbx($this->getPrefix());
		$action = $this->getAction();
		$id = $this->getId();
		$class = $this->getClass();
		$name = $this->getName();
		$dataAttributes = $this->getDataAttributes();
		$dataAttributesString = '';
		foreach ($dataAttributes as $key => $value)
		{
			$dataAttributesString .= ' data-' . $key . '="' . $value .  '" ';
		}

		$formArguments = 'action="' . (($action !== null) ? $action : '#') . '" ';
		$formArguments .= ($id !== null) ? 'id="' . $id . '" ' : '';
		$formArguments .= !empty($class) ? 'class="' . implode(' ', $class) . '" ' : '';
		$formArguments .= ($name !== null) ? 'name="' . $name . '" ' : '';
		$formArguments .= $dataAttributesString;
		echo '<form ' . $formArguments . '>';

		$fields = $this->getFields();
		foreach ($fields as $key => $field)
		{
			$field->render();
		}

		echo '</form>';

		echo htmlspecialcharsbx($this->getPostfix());
	}

	/**
	 * @return Error[]
	 */
	public function getErrors()
	{
		return $this->errors;
	}

	/**
	 * @return string
	 */
	public function getPrefix()
	{
		return $this->prefix;
	}

	/**
	 * @param string $prefix String to set as prefix of form.
	 * @return $this
	 */
	public function setPrefix($prefix)
	{
		$this->prefix = $prefix;
		return $this;
	}


	/**
	 * @return string
	 */
	public function getPostfix()
	{
		return $this->postfix;
	}

	/**
	 * @param string $postfix String to set as postfix fo form.
	 * @return Form
	 */
	public function setPostfix($postfix)
	{
		$this->postfix = $postfix;
		return $this;
	}

	/**
	 * @param string $className String to add as class name of form.
	 * @return void
	 */
	public function addClass($className)
	{
		$this->class[] = $className;
	}

	/**
	 * @return array
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * @param array $class Array of strings to set as class names.
	 * @return void
	 */
	public function setClass($class)
	{
		$this->class = $class;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @param string $name Name value.
	 * @return void
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param string $id Id value.
	 * @return void
	 */
	public function setId($id)
	{
		$this->id = $id;
	}

	/**
	 * Action url where to ubmit form.
	 *
	 * @return string
	 */
	public function getAction()
	{
		return $this->action;
	}

	/**
	 * @param string $action Url where to send form on submit.
	 * @return $this
	 */
	public function setAction($action)
	{
		$this->action = $action;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getMethod()
	{
		return $this->method;
	}

	/**
	 * @param string $method Get or post.
	 * @return $this
	 */
	public function setMethod($method)
	{
		$this->method = $method;
		return $this;
	}

	/**
	 * @return array
	 */
	public function getDataAttributes()
	{
		return $this->dataAttributes;
	}

	/**
	 * @param array $dataAttributes Data attributes key value pair array.
	 * @return $this
	 */
	public function setDataAttributes($dataAttributes)
	{
		$this->dataAttributes = $dataAttributes;
		return $this;
	}

	/**
	 * @param string $key Key for add data attribute. ('role').
	 * @param string $value Value for add data attribute. ('remove button').
	 * @return void
	 */
	public function addDataAttribute($key, $value = '')
	{
		$this->dataAttributes[$key] = $value;
	}
}