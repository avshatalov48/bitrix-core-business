<?php

namespace Bitrix\Sender\Internals\Factory;

use Bitrix\Main\Type\DateTime;
use Bitrix\Sender\Internals\Dto\UpdateBlacklistDTO;
use Bitrix\Sender\Internals\Dto\UpdateContact;
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
	 * @return UpdateContact|null
	 */
	public function make(string $code, ?string $name): ?UpdateContact
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

		$item = $this->createDto();
		$item->typeId = $typeId;
		$item->code = $code;
		$item->dateInsert = $this->date;
		$item->dateUpdate = $this->date;
		$item->name = $name;

		return $item;
	}

	/**
	 * @return UpdateContact
	 */
	public function createDto(): UpdateContact
	{
		return $this->blacklisted ? new UpdateBlacklistDTO() : new UpdateContactDTO();
	}
}
