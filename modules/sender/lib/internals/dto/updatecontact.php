<?php

namespace Bitrix\Sender\Internals\Dto;

interface UpdateContact
{
	public function toArray(): array;
	public function getOnDuplicateKeyUpdateFields(): array;
}