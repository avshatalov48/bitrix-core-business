<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\InputOutputSpec;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TolokaTransferObject;

class InputSpecAssembler implements Assembler
{

	/**
	 * @param HttpRequest $request
	 *
	 * @return TolokaTransferObject
	 */
	public static function toDTO(HttpRequest $request)
	{
		$inputSpec = new InputOutputSpec();

		$inputSpec->setIdentificator($request->get('input_identificator'));
		$inputSpec->setType($request->get('input_type'));

		return $inputSpec;
	}
}