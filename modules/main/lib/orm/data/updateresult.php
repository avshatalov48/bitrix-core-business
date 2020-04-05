<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\ORM\Data;

use Bitrix\Main\DB\Connection;

class UpdateResult extends Result
{
	/** @var int */
	protected $affectedRowsCount;

	/** @var array */
	protected $primary;

	public function __construct()
	{
		parent::__construct();
	}

	public function setAffectedRowsCount(Connection $connection)
	{
		$this->affectedRowsCount = $connection->getAffectedRowsCount();
	}

	/**
	 * @return int
	 */
	public function getAffectedRowsCount()
	{
		return $this->affectedRowsCount;
	}

	public function setPrimary($primary)
	{
		$this->primary = $primary;
	}

	/**
	 * @return array
	 */
	public function getPrimary()
	{
		return $this->primary;
	}

	/**
	 * Returns id of updated record
	 * @return array|int|string
	 */
	public function getId()
	{
		if (count($this->primary) == 1)
		{
			return end($this->primary);
		}

		return $this->primary;
	}
}
