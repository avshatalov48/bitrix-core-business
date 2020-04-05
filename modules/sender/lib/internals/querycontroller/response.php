<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpResponse;

Loc::loadMessages(__FILE__);

/**
 * Class Response
 * @package Bitrix\Sender\Internals\QueryController
 */
class Response extends HttpResponse
{
	/** @var Content $content Content. */
	protected $content;

	/**
	 * Flush content.
	 *
	 */
	public function flushContent()
	{
		$this->content = $this->getContent();
		$this->content->onFlush();
		$this->flush($this->content->toText());
	}

	/**
	 * Set Content instance.
	 *
	 * @param Content $content Content.
	 * @return $this
	 */
	public function setContent($content)
	{
		$this->content = $content;
		return $this;
	}

	/**
	 * Get Content instance.
	 *
	 * @return Content
	 */
	public function getContent()
	{
		if ($this->content)
		{
			return $this->content;
		}

		return $this->initContent();
	}

	/**
	 * Create Content instance.
	 *
	 * @param string $type Type.
	 * @return Content
	 */
	public function initContent($type = Content::TYPE_JSON)
	{
		return $this->setContent(Content::create($this, $type))->getContent();
	}

	/**
	 * Create ContentJson instance.
	 *
	 * @return ContentJson|Content
	 */
	public function initContentJson()
	{
		return $this->initContent(Content::TYPE_JSON);
	}

	/**
	 * Create ContentHtml instance.
	 *
	 * @return ContentHtml|Content
	 */
	public function initContentHtml()
	{
		return $this->initContent(Content::TYPE_HTML);
	}
}