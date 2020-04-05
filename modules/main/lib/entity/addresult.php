<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Main\Entity;

class AddResult extends Result
{
	/** @var array */
	protected $primary;

	public function __construct()
	{
		parent::__construct();
	}

	public function setId($id)
	{
		$this->primary = array('ID' => $id);
	}

	/**
	 * Returns id of added record
	 * @return int|array
	 */
	public function getId()
	{
		if (count($this->primary) == 1)
		{
			return end($this->primary);
		}

		return $this->primary;
	}

	/**
	 * @param array $primary
	 */
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
}
