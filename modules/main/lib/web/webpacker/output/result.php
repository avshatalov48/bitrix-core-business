<?php

namespace Bitrix\Main\Web\WebPacker\Output;

/**
 * Class Result
 *
 * @package Bitrix\Main\Web\WebPacker\Output
 */
class Result extends \Bitrix\Main\Result
{
	protected $id;
	protected $content;

	/**
	 * Get ID.
	 *
	 * @return int|null
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set ID.
	 *
	 * @param int|null $id ID.
	 * @return $this
	 */
	public function setId($id)
	{
		$this->id = $id;
		return $this;
	}

	/**
	 * Get content.
	 *
	 * @return string| null
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Set content.
	 *
	 * @param string $content Content.
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}
}