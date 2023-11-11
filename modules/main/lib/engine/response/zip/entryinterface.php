<?php

namespace Bitrix\Main\Engine\Response\Zip;

interface EntryInterface
{
	public function getPath(): string;
	public function getSize(): int;
	public function getServerRelativeUrl(): string;
	public function getCrc32(): string;
}