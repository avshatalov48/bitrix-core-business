<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Trait;

use Bitrix\Iblock\Public\Service\RestValidator\Format\Type;

trait FileValidatorTrait
{
	protected Type\FileInterface $fileValidator;

	public function setFileValidator(Type\FileInterface $validator): static
	{
		$this->fileValidator = $validator;

		return $this;
	}

	public function getFileValidator(): Type\FileInterface
	{
		return $this->fileValidator;
	}
}
