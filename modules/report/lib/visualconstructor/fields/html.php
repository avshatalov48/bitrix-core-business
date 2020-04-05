<?php

namespace Bitrix\Report\VisualConstructor\Fields;

/**
 * Class Html simple field to display some text or simple html
 * @package Bitrix\Report\VisualConstructor\Fields
 */
class Html extends Base
{
	private $content;

	/**
	 * Html constructor.
	 * @param string $contentHtml String to set as content of html element.
	 */
	public function __construct($contentHtml = '')
	{
		$this->setContent($contentHtml);
	}

	/**
	 * @return void
	 */
	public function printContent()
	{
		echo $this->getContent();
	}

	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Setter for content.
	 *
	 * @param string $content String to set as Html element content.
	 * @return void
	 */
	public function setContent($content)
	{
		$this->content = $content;
	}


}