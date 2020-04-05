<?php

namespace Bitrix\Report\VisualConstructor\Fields;

/**
 * Class Image, render image
 * @package Bitrix\Report\VisualConstructor\Fields
 */
class Image extends Base
{
	const JS_EVENT_ON_CLICK = 'onClick';

	private $uri;

	/**
	 * Image constructor.
	 * @param string $sourceUri
	 */
	public function __construct($sourceUri = '')
	{
		$this->setUri($sourceUri);
	}

	/**
	 * @return void
	 */
	public function printContent()
	{
		$this->includeFieldComponent('image');
	}

	/**
	 * @return mixed
	 */
	public function getUri()
	{
		return $this->uri;
	}

	/**
	 * @param mixed $uri Src of image.
	 * @return void
	 */
	public function setUri($uri)
	{
		$this->uri = $uri;
	}
}