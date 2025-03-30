<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2025 Bitrix
 */

namespace Bitrix\Main;

use Bitrix\Main\Localization\LocalizableMessageInterface;
use Throwable;

class Error implements \JsonSerializable
{
	/** @var int|string */
	protected $code;

	/** @var string|LocalizableMessageInterface */
	protected $message;
	/**
	 * @var null
	 */
	protected $customData;

	/**
	 * Creates a new Error.
	 *
	 * @param string $message Message of the error.
	 * @param int|string $code Code of the error.
	 * @param mixed|null $customData Data typically of key/value pairs that provide additional
	 * user-defined information about the error.
	 */
	public function __construct($message, $code = 0, $customData = null)
	{
		$this->message = $message;
		$this->code = $code;
		$this->customData = $customData;
	}

	/**
	 * @param Throwable $exception
	 * @return static
	 */
	public static function createFromThrowable(Throwable $exception): static
	{
		return new static($exception->getMessage(), $exception->getCode());
	}

	/**
	 * Returns the code of the error.
	 * @return int|string
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * Returns the message of the error.
	 * @return string
	 */
	public function getMessage()
	{
		return (string) $this->message;
	}

	public function getLocalizableMessage(): ?LocalizableMessageInterface
	{
		return $this->message instanceof LocalizableMessageInterface ? $this->message : null;
	}

	/**
	 * @return mixed|null
	 */
	public function getCustomData()
	{
		return $this->customData;
	}

	public function __toString()
	{
		return $this->getMessage();
	}

	/**
	 * Specify data which should be serialized to JSON
	 * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
	 * @return mixed data which can be serialized by <b>json_encode</b>,
	 * which is a value of any type other than a resource.
	 * @since 5.4.0
	 */
	#[\ReturnTypeWillChange]
	public function jsonSerialize()
	{
		return [
			'message' => $this->getMessage(),
			'code' => $this->getCode(),
			'customData' => $this->getCustomData(),
		];
	}

	/**
	 * Disables deserialization.
	 */
	public function __unserialize(array $data): void
	{
	}
}
