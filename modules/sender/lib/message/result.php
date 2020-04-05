<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */

namespace Bitrix\Sender\Message;

use Bitrix\Main\Result as MainResult;

/**
 * Class Result
 * @package Bitrix\Sender\Message
 */
class Result extends MainResult
{
	/** @var integer|string|null $id ID. */
	protected $id;

	/**
	 * Get ID.
	 *
	 * @return integer|string|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param integer|string|null
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}
}