<?php

namespace Bitrix\Im\V2;

use Bitrix\Main\Localization\Loc;

class Error extends \Bitrix\Main\Error
{
	public const NOT_FOUND = 'NOT_FOUND';

	protected string $description = '';

	public function __construct(string $code, ...$args)
	{
		$message = null;
		$description = null;
		$customData = [];

		if (!empty($args))
		{
			$message = isset($args[0]) && is_string($args[0]) ? $args[0] : null;
			$description = isset($args[1]) && is_string($args[1]) ? $args[1] : null;
			$inx = count($args) - 1;
			$customData = isset($args[$inx]) && is_array($args[$inx]) ? $args[$inx] : [];
		}

		$replacements = [];
		foreach ($customData as $key => $value)
		{
			$replacements["#{$key}#"] = $value;
		}

		if (!is_string($message))
		{
			$message = $this->loadErrorMessage($code, $replacements);
		}

		if (is_string($message) && mb_strlen($message) > 0 && !is_string($description))
		{
			$description = $this->loadErrorDescription($code, $replacements);
		}

		if (!is_string($message) || mb_strlen($message) === 0)
		{
			$message = $code;
		}

		parent::__construct($message, $code, $customData);

		if (is_string($description))
		{
			$this->setDescription($description);
		}
	}

	public function getDescription(): string
	{
		return $this->description;
	}

	public function setDescription(string $description): void
	{
		$this->description = $description;
	}

	protected function loadErrorMessage($code, $replacements): string
	{
		return Loc::getMessage("ERROR_{$code}", $replacements) ?? '';
	}

	protected function loadErrorDescription($code, $replacements): string
	{
		return Loc::getMessage("ERROR_{$code}_DESC", $replacements) ?? '';
	}
}
