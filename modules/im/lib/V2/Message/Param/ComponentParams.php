<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\Text;
use Bitrix\Im\V2\Message\Param;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Web\Json;

class ComponentParams extends Param
{
	protected ?string $type = Param::TYPE_JSON;

	public function setValue($value): self
	{
		$this->value = $value;
		$this->jsonValue = Json::encode($value);

		return $this;
	}
	public function saveValueFilter($value)
	{
		return '';
	}

	public function loadValueFilter($value)
	{
		if (!empty($value))
		{
			$value = Text::decodeEmoji($value);
		}
		else
		{
			$value = null;
		}

		return $value;
	}

	public function saveJsonFilter($value)
	{
		return $this->jsonValue;
	}

	public function loadJsonFilter($value)
	{
		if (!empty($value))
		{
			try
			{
				$this->value = \Bitrix\Main\Web\Json::decode($value);
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

	public function toRestFormat(): ?array
	{
		return Converter::toJson()->process($this->getValue());
	}

	public function toPullFormat(): ?array
	{
		return Converter::toJson()->process($this->getValue());
	}
}