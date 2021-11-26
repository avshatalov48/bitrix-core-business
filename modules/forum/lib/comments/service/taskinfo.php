<?php
namespace Bitrix\Forum\Comments\Service;

use Bitrix\Main\Web\Json;

final class TaskInfo extends Base
{
	const TYPE = 'TASKINFO';

	public function getType()
	{
		return static::TYPE;
	}

	public function getText(string $text = '', array $params = [])
	{
		$result = '';

		try
		{
			$data = Json::decode($text);
		}
		catch(\Bitrix\Main\ArgumentException $e)
		{
			$data = [];
		}

		if (
			!is_array($data)
			|| empty($data)
			|| !\Bitrix\Main\Loader::includeModule("tasks")
		)
		{
			return $result;
		}

		$result = htmlspecialcharsEx(
			\Bitrix\Tasks\Comments\Task\CommentPoster::getCommentText(
				$data,
				array_merge($params, ['mobile' => (isset($params['mobile']) && $params['mobile'] === true)])
			)
		);

		return $result;
	}

	public function canDelete()
	{
		return false;
	}
}