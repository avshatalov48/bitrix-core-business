<?php

namespace Bitrix\MessageService\Providers;

interface Initiator
{
	public function getFromList(): array;
	public function getDefaultFrom(): ?string;
	public function getFirstFromList();
	public function isCorrectFrom(string $from): bool;
}