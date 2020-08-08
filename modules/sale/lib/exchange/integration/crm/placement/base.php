<?php


namespace Bitrix\Sale\Exchange\Integration\CRM\Placement;


use Bitrix\Sale\Exchange\Integration\Admin\ModeType;
use Bitrix\Sale\Internals\Fields;

abstract class Base
{
	protected $fields;

	public function __construct($fields)
	{
		$this->fields = new Fields($fields);
	}

	abstract public function getType();
	abstract public function getEntityId();
	abstract public function getEntityTypeId();

	public function getModeType()
	{
		return ModeType::APP_LAYOUT_TYPE;
	}
}