<?php
namespace Bitrix\Sale\Exchange\Integration\Service\Internal\Entity;


use Bitrix\Sale\Exchange\Integration\CRM\EntityType;

class Activity extends Base
{
	function getType()
	{
		return EntityType::ACTIVITY;
	}

	public function getSubject()
	{
		return $this->fields->get('SUBJECT');
	}

	public function setSubject($value)
	{
		$this->fields->set('SUBJECT', $value);
		return $this;
	}
}