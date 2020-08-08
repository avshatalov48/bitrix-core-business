<?php


namespace Bitrix\Sale\Exchange\Integration\Connector\Placement;


abstract class Base
{
	protected $appUrl;

	public function __construct(\Bitrix\Sale\Exchange\Integration\App\Base $app)
	{
		$this->appUrl = $app->getAppUrl();
	}

	protected function getAppUrl()
	{
		return $this->appUrl;
	}

	abstract public function getTitle();

	abstract public function getGroupName();

	abstract public function getPlacement();

	abstract public function getPlacmentHandler();
}