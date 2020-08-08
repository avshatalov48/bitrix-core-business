<?php
namespace Bitrix\Sale\Exchange\Integration\Admin;

class AppLayoutLink extends DefaultLink
{
	protected $requestFields;

	public function __construct()
	{
		parent::__construct();

		$this->requestFields = new \Bitrix\Sale\Internals\Fields(
			\Bitrix\Main\Context::getCurrent()->getRequest()->toArray());
	}

	public function getType()
	{
		return ModeType::APP_LAYOUT_TYPE;
	}

	public function fill()
	{
		$this->query->set('restAppLayoutMode', 'Y');
		$this->query->set('IFRAME', 'Y');

		if((int)$this->requestFields->get('entityId')>0)
			$this->query->set('entityId', $this->requestFields->get('entityId'));

		if((int)$this->requestFields->get('entityTypeId')>0)
			$this->query->set('entityTypeId', $this->requestFields->get('entityTypeId'));

		return parent::fill();
	}
}