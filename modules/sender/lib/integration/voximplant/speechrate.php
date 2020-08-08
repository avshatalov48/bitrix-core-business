<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */

namespace Bitrix\Sender\Integration\VoxImplant;

/**
 * Class SpeechRate
 * @package Bitrix\Sender\Integration\VoxImplant
 */
class SpeechRate
{
	/** @var string $speedId Speed ID. */
	private $speedId;

	/** @var string $text Text. */
	private $text = '';

	/**
	 * Create instance.
	 *
	 * @return static
	 */
	public static function create()
	{
		return new static();
	}

	/**
	 * SpeechRate constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * With speed.
	 *
	 * @param string|null $speedId
	 * @return $this
	 */
	public function withSpeed($speedId = null)
	{
		$this->speedId = $speedId;
		return $this;
	}

	/**
	 * With text.
	 *
	 * @param string $text
	 * @return $this
	 */
	public function withText($text = '')
	{
		$this->text = $text;
		return $this;
	}

	/**
	 * Return speech duration of text.
	 *
	 * @return integer
	 */
	public function getDuration()
	{
		return mb_strlen($this->text) * $this->getRatioPerChar();
	}

	private function getRatioPerChar()
	{
		$rates = self::getList();
		$rate = isset($rates[$this->speedId]) ? $rates[$this->speedId] : $rates['medium'];

		return self::getBaseInterval() / $rate;
	}

	/**
	 * Get list of rates: chars in 30 seconds.
	 *
	 * @return array
	 */
	public static function getList()
	{
		return array(
			'x-slow' => 270,
			'slow' => 300,
			'medium' => 340,
			'fast' => 370,
			'x-fast' => 400
		);
	}

	/**
	 * Get base interval in seconds.
	 *
	 * @return integer
	 */
	public static function getBaseInterval()
	{
		return 30;
	}
}