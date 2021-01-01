<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TolokaTransferObject;

interface Assembler
{
	/**
	 * Convert request to TolokaTransferObject
	 * @param HttpRequest $request
	 *
	 * @return TolokaTransferObject
	 */
	public static function toDTO(HttpRequest $request);
}
