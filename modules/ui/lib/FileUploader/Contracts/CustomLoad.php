<?php

namespace Bitrix\UI\FileUploader\Contracts;

use Bitrix\UI\FileUploader\LoadResultCollection;

interface CustomLoad
{
	public function load(array $ids): LoadResultCollection;
}