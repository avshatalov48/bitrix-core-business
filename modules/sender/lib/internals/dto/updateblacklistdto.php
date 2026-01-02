<?php

namespace Bitrix\Sender\Internals\Dto;

use Bitrix\Main\Type\DateTime;

class UpdateBlacklistDTO implements UpdateContact
{
	public function __construct(
		public ?int $typeId = null,
		public ?string $code = null,
		public ?string $name = null,
		public ?DateTime $dateInsert = null,
		public ?DateTime $dateUpdate = null,
		private readonly bool $blacklisted = true
	)
	{
	}

	public function toArray(): array {
		return [
			'TYPE_ID' => $this->typeId,
			'CODE' => $this->code,
			'NAME' => $this->name,
			'DATE_INSERT' => $this->dateInsert,
			'DATE_UPDATE' => $this->dateUpdate,
			'BLACKLISTED' => $this->blacklisted ? 'Y' : 'N',
		];
	}

	public function getOnDuplicateKeyUpdateFields(): array
	{
		return [
			'NAME',
			'DATE_UPDATE',
			'BLACKLISTED'
		];
	}
}
