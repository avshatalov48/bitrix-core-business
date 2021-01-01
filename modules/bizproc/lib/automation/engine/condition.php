<?php
namespace Bitrix\Bizproc\Automation\Engine;

use Bitrix\Bizproc;
use Bitrix\Bizproc\Automation\Target\BaseTarget;
use Bitrix\Bizproc\FieldType;

class Condition extends Bizproc\Activity\Condition
{
	protected $object = 'Document';
	protected $field;

	public function __construct(array $params = null)
	{
		parent::__construct($params);

		if ($params && isset($params['field']))
		{
			$this->setField($params['field']);
		}
		if ($params && isset($params['object']))
		{
			$this->setObject($params['object']);
		}
	}

	/**
	 * @param string $field The field name.
	 * @return Condition
	 */
	public function setField(string $field)
	{
		$this->field = (string)$field;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getField()
	{
		return $this->field;
	}

	/**
	 * @param string $objectName The object name.
	 * @return Condition
	 */
	public function setObject(string $objectName)
	{
		$this->object = $objectName;
		return $this;
	}

	/**
	 * @return string
	 */
	public function getObject()
	{
		return $this->object;
	}

	/**
	 * @param mixed $needle The field value to check.
	 * @param string $fieldType Type of the field.
	 * @param BaseTarget $target Automation target.
	 * @param FieldType $fieldTypeObject
	 * @return bool
	 */
	public function check($needle, $fieldType, BaseTarget $target, FieldType $fieldTypeObject)
	{
		$documentId = $target->getDocumentType();
		$documentId[2] = $target->getDocumentId();

		return $this->checkValue($needle, $fieldTypeObject, $documentId);
	}

	/**
	 * @return array Array presentation of condition.
	 */
	public function toArray()
	{
		$array = parent::toArray();
		$array['field'] = $this->getField();
		$array['object'] = $this->getObject();

		return $array;
	}
}