<?php

namespace Bitrix\UI\FileUploader\Contracts;

use Bitrix\UI\FileUploader\RemoveResultCollection;

interface CustomRemove
{
	public function remove(array $ids): RemoveResultCollection;
}