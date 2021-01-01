<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TaskSpec;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TolokaTransferObject;

class TaskSpecAssembler implements Assembler
{

	/**
	 * @param HttpRequest $request
	 *
	 * @return TolokaTransferObject
	 */
	public static function toDTO(HttpRequest $request)
	{
		$taskSpec = new TaskSpec();
		$inputSpec = InputSpecAssembler::toDTO($request);
		$outputSpec = OutputSpecAssembler::toDTO($request);
		$viewSpec = ViewSpecAssembler::toDTO($request);

		$taskSpec->setInputSpec($inputSpec);
		$taskSpec->setOutputSpec($outputSpec);
		$taskSpec->setViewSpec($viewSpec);

		return $taskSpec;
	}
}