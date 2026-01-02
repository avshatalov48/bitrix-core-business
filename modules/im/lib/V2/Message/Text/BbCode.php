<?php
declare(strict_types=1);

namespace Bitrix\Im\V2\Message\Text;

interface BbCode
{
	public function compile(): string;

	public function toPlain(): string;

	public static function getName(): string;

	public function toPlaceholder(): string;
}
