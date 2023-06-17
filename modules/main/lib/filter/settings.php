<?php
namespace Bitrix\Main\Filter;
use Bitrix\Main;

class Settings
{
	/** @var string */
	protected $ID = '';

	function __construct(array $params)
	{
		$this->ID = $params['ID'] ?? '';
		if($this->ID === '')
		{
			throw new Main\ArgumentException('Collection does not contain value for ID.', 'params');
		}
	}
	public function getID()
	{
		return $this->ID;
	}
}