<?php
namespace Bitrix\Main\Filter;
class UserSettings extends EntitySettings
{
	private $whiteList = [];

	function __construct(array $params)
	{
		parent::__construct($params);

		$this->whiteList = isset($params['WHITE_LIST']) && is_array($params['WHITE_LIST'])
			? $params['WHITE_LIST'] : [];
	}

	public function getWhiteList()
	{
		return $this->whiteList;
	}

	/**
	 * Get Entity Type ID.
	 * @return int
	 */
	public function getEntityTypeName()
	{
		return 'USER';
	}

	/**
	 * Get User Field Entity ID.
	 * @return string
	 */
	public function getUserFieldEntityID()
	{
		return \Bitrix\Main\UserTable::getUfId();
	}
}