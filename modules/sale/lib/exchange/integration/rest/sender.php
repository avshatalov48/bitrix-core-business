<?php
namespace Bitrix\Sale\Exchange\Integration\Rest;

use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Exchange\Integration;

Loc::loadMessages(__FILE__);

class Sender
{
	protected $fields;

	public function __construct()
	{
		$this->fields = new \Bitrix\Sale\Internals\Fields(
			\Bitrix\Main\Context::getCurrent()->getRequest()->toArray());
	}

	/**
	 * @return \Bitrix\Sale\Internals\Fields
	 */
	protected function getFields()
	{
		return $this->fields;
	}

	public function getField($name)
	{
		return isset($this->fields[$name]) ? $this->fields[$name]:'';
	}

	public function checkFields()
	{
		$r = new \Bitrix\Sale\Result();

		if(empty($this->getField('orderIds')))
		{
			$r->addError(new Error(Loc::getMessage('SALE_ORDER_REQUEST_ORDER_IDS_EMPTY'))) ;
		}

		if(empty($this->getField('entityId')))
		{
			$r->addError(new Error(Loc::getMessage('SALE_ORDER_REQUEST_ENTITY_ID_EMPTY'))) ;
		}

		if(empty($this->getField('entityTypeId')))
		{
			$r->addError(new Error(Loc::getMessage('SALE_ORDER_REQUEST_ENTITY_TYPE_ID_EMPTY')));
		}
		elseif(Integration\CRM\EntityType::isDefined($this->getField('entityTypeId')) == false)
		{
			$r->addError(new Error(Loc::getMessage('SALE_ORDER_REQUEST_ENTITY_TYPE_ID_UNKNOW'))) ;
		}

		return $r;
	}
}