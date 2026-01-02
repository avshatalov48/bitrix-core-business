<?php

namespace Bitrix\Rest\V3\Exceptions;

abstract class FieldException extends RestException
{
	/**
	 * @param string $field
	 */
	public function __construct(
		protected string $field,
	) {
		parent::__construct();
	}

	public function output($localErrorLanguage = null): array
	{
		$out = parent::output($localErrorLanguage);

		$out['field'] = $this->field;

		return $out;
	}
}
