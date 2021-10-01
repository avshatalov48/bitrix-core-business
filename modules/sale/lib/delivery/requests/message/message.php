<?php

namespace Bitrix\Sale\Delivery\Requests\Message;

/**
 * Class Message
 * @package Bitrix\Sale\Delivery\Requests\Message
 * @internal
 */
final class Message
{
	public const TYPE_SHIPMENT_PICKUPED = 'SHIPMENT_PICKUPED';

	public const MESSAGE_TEXT_MONEY_PLACEHOLDER = '#MONEY#';

	/** @var string */
	private $subject;

	/** @var string */
	private $body;

	/** @var Status */
	private $status;

	/** @var string */
	private $type;

	/** @var string|null */
	private $currency;

	/** @var array */
	private $moneyValues = [];

	/**
	 * @return string|null
	 */
	public function getSubject(): ?string
	{
		return $this->subject;
	}

	/**
	 * @return string|null
	 */
	public function getBody(): ?string
	{
		return $this->body;
	}

	/**
	 * @return string
	 */
	public function getBodyForHtml(): string
	{
		$result = htmlspecialcharsbx($this->getBody());

		if ($this->getCurrency() && $this->getMoneyValues())
		{
			$replaceValues = array_map(
				function ($moneyValue)
				{
					return SaleFormatCurrency($moneyValue, $this->getCurrency());
				},
				$this->getMoneyValues()
			);

			if (substr_count($result, self::MESSAGE_TEXT_MONEY_PLACEHOLDER) !== count($replaceValues))
			{
				return $result;
			}

			return sprintf(
				str_replace(self::MESSAGE_TEXT_MONEY_PLACEHOLDER, '%s', $result),
				...$replaceValues
			);
		}

		return $result;
	}

	/**
	 * @return Status|null
	 */
	public function getStatus(): ?Status
	{
		return $this->status;
	}

	/**
	 * @return string|null
	 */
	public function getType(): ?string
	{
		return $this->type;
	}

	/**
	 * @return string|null
	 */
	public function getCurrency(): ?string
	{
		return $this->currency;
	}

	/**
	 * @return array
	 */
	public function getMoneyValues(): array
	{
		return $this->moneyValues;
	}

	/**
	 * @param string $subject
	 * @return Message
	 */
	public function setSubject(string $subject): Message
	{
		$this->subject = $subject;
		return $this;
	}

	/**
	 * @param string $body
	 * @return Message
	 */
	public function setBody(string $body): Message
	{
		$this->body = $body;
		return $this;
	}

	/**
	 * @param Status $status
	 * @return Message
	 */
	public function setStatus(Status $status): Message
	{
		$this->status = $status;
		return $this;
	}

	/**
	 * @param string $type
	 * @return Message
	 */
	public function setType(string $type): Message
	{
		$this->type = $type;
		return $this;
	}

	/**
	 * @param float $value
	 * @return $this
	 */
	public function addMoneyValue(float $value): Message
	{
		$this->moneyValues[] = $value;
		return $this;
	}

	/**
	 * @param string $currency
	 * @return Message
	 */
	public function setCurrency(string $currency): Message
	{
		$this->currency = $currency;
		return $this;
	}
}
