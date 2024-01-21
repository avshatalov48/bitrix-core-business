<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\V2\Message\Param;
use Bitrix\Im\V2\Result;
use Bitrix\Main\ArgumentException;

class Attach extends Param
{
	private ?\CIMMessageParamAttach $attach = null;
	private bool $isValid = true;

	protected ?string $type = Param::TYPE_JSON;

	/**
	 * @param array|\CIMMessageParamAttach $value
	 * @return static
	 */
	public function setValue($value): self
	{
		if ($value === null || $value === $this->getDefaultValue())
		{
			return $this->unsetValue();
		}
		if ($value instanceof \CIMMessageParamAttach)
		{
			$this->attach = $value;
		}
		elseif (!empty($value))
		{
			$this->attach = \CIMMessageParamAttach::GetAttachByJson($value);
			if ($this->attach === null)
			{
				$this->isValid = false;
			}
		}

		if (isset($this->attach))
		{
			$this->value = $this->attach->getArray();
			$this->jsonValue = $this->attach->getJson();
		}

		return $this;
	}

	/**
	 * @return array|null
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadValueFilter($value)
	{
		if (!empty($value))
		{
			$value = \Bitrix\Im\Text::decodeEmoji($value);
		}
		else
		{
			$value = null;
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveValueFilter($value)
	{
		$value = '';
		if (!empty($this->value['DESCRIPTION']))
		{
			$value = \Bitrix\Im\Text::encodeEmoji($this->value['DESCRIPTION']);
		}

		return $value;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function saveJsonFilter($value)
	{
		return $this->jsonValue;
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function loadJsonFilter($value)
	{
		if (!empty($value))
		{
			try
			{
				$val = \Bitrix\Main\Web\Json::decode($value);
				$this->value = \CIMMessageParamAttach::PrepareAttach($val);
			}
			catch (ArgumentException $ext)
			{}
		}
		else
		{
			$value = null;
		}

		return $value;
	}

	/**
	 * @return array|null
	 */
	public function toRestFormat(): ?array
	{
		return $this->getValue();
	}

	/**
	 * @return mixed
	 */
	public function toPullFormat()
	{
		return \CIMMessageParamAttach::PrepareAttach($this->getValue());
	}

	/**
	 * @return Result
	 */
	public function isValid(): Result
	{
		$result = new Result();

		if ($this->isValid && (!isset($this->attach) || $this->attach->IsAllowSize()))
		{
			return $result;
		}

		return $result->addError(new ParamError(ParamError::ATTACH_ERROR));
	}
}
