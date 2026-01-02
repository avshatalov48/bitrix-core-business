<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

interface ValidatorFieldInterface
{
	public function setFileValidator(Type\FileInterface $validator): static;

	public function getFileValidator(): Type\FileInterface;
}
