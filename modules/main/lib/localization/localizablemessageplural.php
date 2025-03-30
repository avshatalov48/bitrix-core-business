<?php

namespace Bitrix\Main\Localization;

use Bitrix\Main\Diag;
use Bitrix\Main\IO\Path;

/**
 * This class is used to defer the localization of a phrase until it is actually needed.
 * It allows you to get the localized text in any language at any point before the end of the hit.
 *
 * This class should only be instantiated when the message is actually going to be displayed.
 * Otherwise, unnecessary load may be created in the constructor method.
 *
 * @see Loc::getMessagePlural()
 */
class LocalizableMessagePlural implements LocalizableMessageInterface
{
	public function __construct(
		protected string $code,
		protected int $value,
		protected ?array $replace = null,
		protected ?string $language = null,

		/** @var string Path to the file that owns the phrase */
		protected ?string $phraseSrcFile = null,
	)
	{
		if (!isset($this->phraseSrcFile))
		{
			// guess the file that owns the phrase
			$trace = Diag\Helper::getBackTrace(1, DEBUG_BACKTRACE_IGNORE_ARGS);
			$this->phraseSrcFile = Path::normalize($trace[0]['file']);
		}
	}

	public function localize(string $language): ?string
	{
		$message = Loc::getMessagePlural($this->code, $this->value, $this->replace, $language);

		if (!isset($message))
		{
			Loc::loadLanguageFile($this->phraseSrcFile, $language, false);
			$message = Loc::getMessagePlural($this->code, $this->value, $this->replace, $language);
		}

		return $message;
	}

	public function __toString()
	{
		return $this->localize($this->language ?? Loc::getCurrentLang());
	}
}