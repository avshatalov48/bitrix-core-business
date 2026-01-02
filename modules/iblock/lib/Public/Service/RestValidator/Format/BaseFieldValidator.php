<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\Public\Service\RestValidator\Trait;

abstract class BaseFieldValidator extends BaseValidator implements ValidatorFieldInterface
{
	use Trait\FileValidatorTrait;
	use Trait\OrmFieldTrait;

	protected function __construct()
	{
		parent::__construct();
		$this->setFileValidator(new Type\IblockFile());
	}
}
