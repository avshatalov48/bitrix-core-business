<?php

namespace Bitrix\Im\V2\Message\Param;

use Bitrix\Im\Text;
use Bitrix\Main\ArgumentException;
use Bitrix\Im\V2\Message\Param;
use Bitrix\Im\V2\Message\ParamArray;
use Bitrix\Im\V2\Result;

class AttachArray extends ParamArray
{
	/**
	 * @param array|\CIMMessageParamAttach|\CIMMessageParamAttach[] $values
	 * @return static
	 */
	public function setValue($values): self
	{
		if (!is_array($values) || \Bitrix\Main\Type\Collection::isAssociative($values))
		{
			$values = [$values];
		}

		foreach ($this as $param)
		{
			$param->markDrop();
		}

		foreach ($values as $value)
		{
			if (!$value instanceof \CIMMessageParamAttach)
			{
				$value = \CIMMessageParamAttach::GetAttachByJson($value);
			}

			$this->addValue($value);
		}

		$this->markChanged();

		return $this;
	}


	/**
	 * @param array|\CIMMessageParamAttach $value
	 * @return static
	 */
	public function addValue($value): self
	{
		if (!$value instanceof \CIMMessageParamAttach)
		{
			$value = \CIMMessageParamAttach::GetAttachByJson($value);
		}

		$param = new Attach();
		$param
			->setName($this->getName())
			->setType(Param::TYPE_JSON)
			->setValue($value)
		;

		if (!$param->hasValue())
		{
			return $this;
		}

		if ($this->getMessageId())
		{
			$param->setMessageId($this->getMessageId());
		}

		if ($param->getPrimaryId())
		{
			$param->setRegistry($this);
		}
		else
		{
			$this[] = $param;
		}

		$this->markChanged();

		return $this;
	}

	/**
	 * @return array
	 */
	public function getValue(): array
	{
		$values = [];
		foreach ($this as $param)
		{
			if ($param->isDeleted())
			{
				continue;
			}
			$values[] = $param->getValue();
		}

		return $values;
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
	public function toPullFormat(): array
	{
		$values = [];
		foreach ($this as $param)
		{
			if ($param->isDeleted() || !$param->hasValue())
			{
				continue;
			}
			$values[] = \CIMMessageParamAttach::PrepareAttach($param->getValue());
		}

		return $values;
	}

	public function isValid(): Result
	{
		$result = new Result();

		/** @var Attach $attach */
		foreach ($this as $attach)
		{
			$checkAttachResult = $attach->isValid();
			if (!$checkAttachResult->isSuccess())
			{
				$result->addErrors($checkAttachResult->getErrors());
			}
		}

		return $result;
	}
}
