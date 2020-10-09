<?php
namespace Bitrix\Main;

abstract class Response
{
	/** @var string */
	protected $content;

	public function __construct()
	{
	}

	public function clear()
	{
	}

	public function flush($text = '')
	{
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
		if (!$this->checkContent($content))
		{
			throw new ArgumentTypeException('content', 'string');
		}

		$this->content = (string)$content;

		return $this;
	}

	/**
	 * Appends content.
	 * Valid types are strings, numbers, null, and objects that implement a __toString() method.
	 *
	 * @param mixed $content Content that can be cast to string.
	 *
	 * @return $this
	 * @throws ArgumentTypeException
	 */
	public function appendContent($content)
	{
		if (!$this->checkContent($content))
		{
			throw new ArgumentTypeException('content', 'string');
		}

		$this->content .= (string)$content;

		return $this;
	}

	protected function checkContent($content)
	{
		return (
			$content === null ||
			is_string($content) ||
			is_numeric($content) ||
			is_callable(array($content, '__toString'))
		);
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

	protected function writeBody($text)
	{
		echo $text;
	}
}
