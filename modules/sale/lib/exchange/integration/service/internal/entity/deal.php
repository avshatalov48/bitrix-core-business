<?php


namespace Bitrix\Sale\Exchange\Integration\Service\Internal\Entity;


use Bitrix\Sale\Exchange\Integration\CRM\EntityType;

class Deal extends Base
{
	function getType()
	{
		return EntityType::DEAL;
	}

	public function getTitle()
	{
		return $this->fields->get('TITLE');
	}

	public function setTitle($value)
	{
		$this->fields->set('TITLE', $value);
		return $this;
	}

	public function getContactId()
	{
		return $this->fields->get('CONTACT_ID');
	}

	public function setContactId($value)
	{
		$this->fields->set('CONTACT_ID', $value);
		return $this;
	}

	public function getCompanyId()
	{
		return $this->fields->get('COMPANY_ID');
	}

	public function setCompanyId($value)
	{
		$this->fields->set('COMPANY_ID', $value);
		return $this;
	}

	public function setOpportunity($value)
	{
		$this->fields->set('OPPORTUNITY', $value);
		return $this;
	}

	public function setCurrency($value)
	{
		$this->fields->set('CURRENCY_ID', $value);
		return $this;
	}
}