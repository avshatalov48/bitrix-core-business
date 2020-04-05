<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Main\Mail;

class Context
{
	const CAT_EXTERNAL = 1;

	protected $category;

	/**
	 * @param int $category See Context CAT_* constants.
	 * @return $this
	 */
	public function setCategory($category)
	{
		$this->category = $category;
		return $this;
	}

	/**
	 * @return int|null
	 */
	public function getCategory()
	{
		return $this->category;
	}
}
