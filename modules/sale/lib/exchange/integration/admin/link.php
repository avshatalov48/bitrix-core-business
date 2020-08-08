<?php


namespace Bitrix\Sale\Exchange\Integration\Admin;


class Link
{
	private static $instance = null;
	protected $type;

	public function __construct()
	{
		$this->type = static::resolveModeType();
	}

	public function getType()
	{
		return $this->type;
	}

	protected function resolveModeType()
	{
		$fields = new \Bitrix\Sale\Internals\Fields(
			\Bitrix\Main\Context::getCurrent()->getRequest()->toArray());

		if($this->isRestAppLayoutMode($fields))
		{
			$type = ModeType::APP_LAYOUT_TYPE;
		}
		else
		{
			$type = ModeType::DEFAULT_TYPE;
		}

		return $type;
	}

	protected function isRestAppLayoutMode(\Bitrix\Sale\Internals\Fields $fields)
	{
		return $fields->get('restAppLayoutMode') == 'Y';
	}

	public function create()
	{
		return Factory::create($this->getType());
	}

	public static function getInstance()
	{
		if(self::$instance === null)
		{
			self::$instance = new static();
		}
		return self::$instance;
	}
}