<?php

namespace Bitrix\Rest\V3\Exceptions\Validation;

use Bitrix\Main\Error;
use Bitrix\Rest\V3\Exceptions\RestException;
use Bitrix\Rest\V3\Exceptions\SkipWriteToLogException;

/**
 * This class is used for displaying validation errors.
 * It supports outputting multiple error messages linked to specific fields.
 */
abstract class ValidationException extends RestException implements SkipWriteToLogException
{
	/**
	 * @param Error[] $errors
	 */
	public function __construct(
		protected array $errors,
	) {
		parent::__construct();
	}

	public function output($localErrorLanguage = null): array
	{
		$out = parent::output($localErrorLanguage);

		$validationItems = [];

		foreach ($this->errors as $error)
		{
			$validationItem = [
				'message' => $error->getLocalizableMessage()?->localize('en') ?? $error->getMessage(),
			];

			if (isset($localErrorLanguage))
			{
				$validationItem['localMessage'] = $error->getLocalizableMessage()?->localize($localErrorLanguage)
					?? $error->getMessage();
			}

			if (!empty($error->getCode()))
			{
				$validationItem['field'] = $error->getCode();
			}

			$validationItems[] = $validationItem;

		}

		$out['validation'] = $validationItems;

		return $out;
	}
}
