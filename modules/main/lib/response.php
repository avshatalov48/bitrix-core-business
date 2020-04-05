<?php
namespace Bitrix\Main;

abstract class Response
{
	const SPREAD_SITES = 2;
	const SPREAD_DOMAIN = 4;

	/** @var string */
	protected $content;

	public function __construct()
	{
	}

	public function clear()
	{

	}

	public function redirect($url)
	{

	}

	public function flush($text = '')
	{
		$this->writeHeaders();
		$this->writeBody($text);
	}

	/**
	 * Sets content.
	 * Valid types are strings, numbers, null, and objects that implement a __toString() method.
	 *
	 * @param mixed $content Content that can be cast to string.
	 *
	 * @return $this
	 * @throws ArgumentTypeException
	 */
	public function setContent($content)
	{
		if (
			$content !== null &&
			!is_string($content) &&
			!is_numeric($content) &&
			!is_callable(array($content, '__toString')))
		{
			throw new ArgumentTypeException('content', 'string');
		}

		$this->content = (string)$content;

		return $this;
	}

	/**
	 * Returns content of response.
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}

	/**
	 * Sends content to the output.
	 *
	 * @return void
	 */
	public function send()
	{
		$this->flush($this->content);
	}

	protected abstract function writeHeaders();

	protected function writeBody($text)
	{
		echo $text;
	}

}
