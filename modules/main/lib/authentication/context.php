<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Main\Authentication;

class Context
{
	protected $userId;

	public function __construct()
	{
	}

	public function getUserId()
	{
		return $this->userId;
	}

	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}
}
