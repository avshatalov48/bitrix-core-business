<?php

namespace Bitrix\Sale\Delivery\Requests\Message;

use Bitrix\Main\Type\DateTime;

/**
 * Class Message
 *
 * @package Bitrix\Sale\Delivery\Requests\Message
 * @internal
 */
final class Message
{
	public const TYPE_SHIPMENT_PICKUPED = 'SHIPMENT_PICKUPED';

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

	/** @var array */
	private $dateValues = [];

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

		$moneyValues = $this->getMoneyValues();
		$currency = $this->getCurrency();
		if ($moneyValues && $currency)
		{
			$result = str_replace(
				array_keys($moneyValues),
				array_map(
					static function ($moneyValue) use ($currency)
					{
						return SaleFormatCurrency($moneyValue, $currency);
					},
					$this->getMoneyValues()
				),
				$result
			);
		}

		$dateValues = $this->getDateValues();
		if ($dateValues)
		{
			$result = str_replace(
				array_keys($dateValues),
				array_map(
					static function ($dateValue)
					{
						if (!isset($dateValue['VALUE']) || !isset($dateValue['FORMAT']))
						{
							return '';
						}

						return
							DateTime::createFromTimestamp((int)$dateValue['VALUE'])
								->toUserTime()
								->format($dateValue['FORMAT'])
						;
					},
					$dateValues
				),
				$result
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
	 * @return array
	 */
	public function getDateValues(): array
	{
		return $this->dateValues;
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
	 * @param string $key
	 * @param float $value
	 * @return $this
	 */
	public function addMoneyValue(string $key, float $value): Message
	{
		$this->moneyValues[$key] = $value;

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

	/**
	 * @param string $key
	 * @param int $value
	 * @param string $format
	 * @return $this
	 */
	public function addDateValue(string $key, int $value, string $format): Message
	{
		$this->dateValues[$key] = [
			'VALUE' => $value,
			'FORMAT' => $format,
		];

		return $this;
	}
}
