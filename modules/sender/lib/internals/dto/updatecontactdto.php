<?php

namespace Bitrix\Sender\Internals\Dto;

use Bitrix\Main\Type\DateTime;

class UpdateContactDTO
{
	public int $typeId;

	public string $code;

	public ?string $name = null;

	public DateTime $dateInsert;

	public DateTime $dateUpdate;

	public bool $blacklisted = false;

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
}
