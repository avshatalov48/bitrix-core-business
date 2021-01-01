<?php
namespace Bitrix\Sender\Integration\Yandex\Toloka\DTO\Assembler;

use Bitrix\Main\HttpRequest;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\Project;
use Bitrix\Sender\Integration\Yandex\Toloka\DTO\TolokaTransferObject;

class ProjectAssembler implements Assembler
{
	/**
	 * @param HttpRequest $request
	 *
	 * @return TolokaTransferObject
	 */
	public static function toDTO(HttpRequest $request)
	{
		$project = new Project();

		$taskSpec = TaskSpecAssembler::toDTO($request);
		$project->setTaskSpec($taskSpec);

		$id = (int)$request->get('id');
		if ($id)
		{
			$project->setId($id);
		}


		$project->setPublicName($request->get('name'));
		$project->setPublicDescription($request->get('description'));
		$project->setPublicInstructions($request->get('instruction'));

		return $project;
	}
}