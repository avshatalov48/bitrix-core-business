<?php declare(strict_types=1);

namespace Bitrix\Im\Services;

/**
 * Message param service.
 *
 * @package Bitrix\Im\Services
 */
class MessageParam
{
	/** @var bool */
	private $isEnabled;

	public function __construct()
	{
		$this->isEnabled = \Bitrix\Main\Loader::includeModule('im');
	}

	/**
	 * Returns message param list and their values.
	 *
	 * @param int $messageId Message Id.
	 * @param bool $withDefault Supply default value.
	 *
	 * @return array|null
	 */
	public function getParams(int $messageId, bool $withDefault = false): ?array
	{
		if ($this->isEnabled)
		{
			return \CIMMessageParam::Get($messageId, false, $withDefault);
		}

		return null;
	}

	/**
	 * Returns message param and its value.
	 *
	 * @param int $messageId Message Id.
	 * @param string $name Parameter name.
	 * @param bool $withDefault Supply default value.
	 *
	 * @return mixed|array|null
	 */
	public function getParam(int $messageId, string $name, bool $withDefault = false)
	{
		if ($this->isEnabled)
		{
			return \CIMMessageParam::Get($messageId, $name, $withDefault);
		}

		return null;
	}

	/**
	 * Sets new params values and sends pull.
	 *
	 * @param int $messageId Message Id.
	 * @param array $values Key - value pairs of parameter's value.
	 * @param bool $sendPull Allow to send pull.
	 *
	 * @return bool
	 */
	public function setParams(int $messageId, array $values, bool $sendPull = true): bool
	{
		if ($this->isEnabled)
		{
			if ($sendPull)
			{
				return
					\CIMMessageParam::Set($messageId, $values)
					&& \CIMMessageParam::SendPull($messageId, array_keys($values))
				;
			}
			else
			{
				return \CIMMessageParam::Set($messageId, $values);
			}
		}

		return false;
	}

	/**
	 * Sets new param value and sends pull.
	 *
	 * @param int $messageId Message Id.
	 * @param string $name Parameter name.
	 * @param mixed $value Parameter value.
	 * @param bool $sendPull Allow to send pull.
	 *
	 * @return bool
	 */
	public function setParam(int $messageId, string $name, $value, bool $sendPull = true): bool
	{
		if ($this->isEnabled)
		{
			if ($sendPull)
			{
				return
					\CIMMessageParam::Set($messageId, [$name => $value])
					&& \CIMMessageParam::SendPull($messageId, [$name])
				;
			}
			else
			{
				return \CIMMessageParam::Set($messageId, [$name => $value]);
			}
		}

		return false;
	}
}
