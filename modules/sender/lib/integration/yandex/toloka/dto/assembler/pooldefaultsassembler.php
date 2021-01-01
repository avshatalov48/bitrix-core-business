<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\PoolDefaults;

class PoolDefaultsAssembler implements Assembler
{
	/**
	 * @param HttpRequest $request
	 *
	 * @return PoolDefaults
	 */
	public static function toDTO(HttpRequest $request)
	{
		$defaults = new PoolDefaults();

		$defaults->setOverlapForNewTaskSuites($request->get('overlap'));
		$defaults->setOverlapForNewTasks($request->get('overlap'));

		return $defaults;
	}
}