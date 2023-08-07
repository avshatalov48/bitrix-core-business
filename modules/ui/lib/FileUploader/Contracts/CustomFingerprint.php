<?php

namespace Bitrix\UI\FileUploader\Contracts;

interface CustomFingerprint
{
	public function getFingerprint(): string;
}