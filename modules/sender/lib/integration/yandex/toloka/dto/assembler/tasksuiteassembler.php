<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\InputValue;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Task;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TaskSuite;

class TaskSuiteAssembler implements Assembler
{
	/**
	 * @param HttpRequest $request
	 *
	 * @return TaskSuite
	 */
	public static function toDTO(HttpRequest $request)
	{

		$userTasks = explode(",", $request->get('tasks'));
		$identificator = $request->get('identificator');
		$tasks = [];
		$defaults = PoolDefaultsAssembler::toDTO($request);

		foreach ($userTasks as $task)
		{
			$task = trim($task);
			if(empty($task))
			{
				continue;
			}
			$newTask = new Task();

			$inputValue = new InputValue();
			$inputValue->setIdentificator($identificator);
			$inputValue->setValue(trim($task));

			$newTask->setPoolId($request->get('id'));
			$newTask->setInputValues($inputValue);
			$newTask->setOverlap($defaults->getOverlapForNewTasks());

			$tasks[] = $newTask;
		}

		$taskSuite = new TaskSuite();
		$taskSuite->setPoolId($request->get('id'));
		$taskSuite->setTasks($tasks);

		return $taskSuite;
	}
}