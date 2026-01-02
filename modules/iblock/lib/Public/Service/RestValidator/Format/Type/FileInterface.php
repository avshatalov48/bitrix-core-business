<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format\Type;

interface FileInterface
{
	public function isCorrectFormat(mixed $value) : bool;
}
