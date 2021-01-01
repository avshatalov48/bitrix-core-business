<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\InputOutputSpec;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TolokaTransferObject;

class OutputSpecAssembler implements Assembler
{

	/**
	 * @param HttpRequest $request
	 *
	 * @return TolokaTransferObject
	 */
	public static function toDTO(HttpRequest $request)
	{
		$outputSpec = new InputOutputSpec();

		$outputSpec->setIdentificator($request->get('output_identificator'));
		$outputSpec->setType($request->get('output_type'));

		return $outputSpec;
	}

}