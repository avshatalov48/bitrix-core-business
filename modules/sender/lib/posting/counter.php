<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Posting;

use Bitrix\Main\Localization\Loc;

use Bitrix\Sender\Entity;

Loc::loadMessages(__FILE__);

/**
 * Class Counter
 * @package Bitrix\Sender\Posting
 */
class Counter
{
	/** @var Entity\Letter $letter Letter. */
	private $letter;

	/**
	 * Constructor.
	 *
	 * @param Entity\Letter $letter Letter.
	 */
	public function __construct(Entity\Letter $letter)
	{
		$this->letter = $letter;
	}

	/**
	 * Get all.
	 *
	 * @return integer.
	 */
	public function getAll()
	{
		return $this->letter->get('COUNT_SEND_ALL', 0);
	}

	/**
	 * Get unsent.
	 *
	 * @return integer.
	 */
	public function getUnsent()
	{
		return $this->letter->get('COUNT_SEND_NONE', 0);
	}

	/**
	 * Get sent.
	 *
	 * @return integer.
	 */
	public function getSent()
	{
		return $this->letter->get('COUNT_SEND_ERROR', 0) + $this->letter->get('COUNT_SEND_SUCCESS', 0);
	}

	/**
	 * Get success.
	 *
	 * @return integer.
	 */
	public function getSuccess()
	{
		return $this->letter->get('COUNT_SEND_SUCCESS', 0);
	}

	/**
	 * Get errors.
	 *
	 * @return integer.
	 */
	public function getErrors()
	{
		return $this->letter->get('COUNT_SEND_ERROR', 0);
	}

	/**
	 * Get read.
	 *
	 * @return integer.
	 */
	public function getRead()
	{
		return $this->letter->get('COUNT_READ', 0);
	}

	/**
	 * Get clicked.
	 *
	 * @return integer.
	 */
	public function getClicked()
	{
		return $this->letter->get('COUNT_CLICK', 0);
	}

	/**
	 * Get unsubscribed.
	 *
	 * @return integer.
	 */
	public function getUnsubscribed()
	{
		return $this->letter->get('COUNT_UNSUB', 0);
	}
}