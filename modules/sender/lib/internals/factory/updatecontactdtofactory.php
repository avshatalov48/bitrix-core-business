<?php

namespace Bitrix\Sender\Internals\Factory;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Internals\Dto\UpdateContactDTO;
use Bitrix\Sender\Recipient\Type;
use Bitrix\Sender\Recipient\Normalizer;

class UpdateContactDtoFactory
{
	private DateTime $date;

	private bool $blacklisted;

	/**
	 * Constructor
	 *
	 * @param bool $blacklisted Is this addition to blacklist?
	 * @param DateTime|null $date Date of operation, if empty - current date
	 */
	public function __construct(bool $blacklisted = false, ?DateTime $date = null)
	{
		$this->blacklisted = $blacklisted;
		$this->date = $date ?? new DateTime();
	}

	/**
	 * Make update item DTO
	 *
	 * @param string $code Code
	 * @param string|null $name Name
	 *
	 * @return UpdateContactDTO|null
	 */
	public function make(string $code, ?string $name): ?UpdateContactDTO
	{
		$typeId = Type::detect($code);
		if (!$typeId)
		{
			return null;
		}

		$code = Normalizer::normalize($code, $typeId);
		if (!$code)
		{
			return null;
		}

		$item = new UpdateContactDTO();
		$item->typeId = $typeId;
		$item->code = $code;
		$item->dateInsert = $this->date;
		$item->dateUpdate = $this->date;
		$item->name = $name;
		$item->blacklisted = $this->blacklisted;

		return $item;
	}
}
