<?php

namespace Bitrix\Main\Localization;

interface LocalizableMessageInterface extends \Stringable
{
	public function localize(string $language): ?string;
}