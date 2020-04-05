<?php

namespace Bitrix\Sender\Search;

use Bitrix\Sender\Entity;
use Bitrix\Sender\Runtime;
use Bitrix\Sender\Internals\Model;
use Bitrix\Main\Application;

/**
 * Class Indexer
 * @package Bitrix\Sender\Search
 */
class Indexer
{


	/**
	 * Index letters.
	 *
	 * @param int|null $lastId ID of last indexed element.
	 * @return int|null
	 */
	public static function indexLetters($lastId = null)
	{
		$timer = new Runtime\Timer(Runtime\Env::getJobExecutionTimeout(), 100);

		$filter = [];
		if ($lastId)
		{
			$filter['>ID'] = $lastId;
		}
		$list = Model\LetterTable::getList([
			'filter' => $filter,
			'order' => ['ID' => 'ASC']
		]);

		$nextId = null;
		foreach ($list as $item)
		{
			$letter = Entity\Letter::createInstanceByArray($item);
			$letter->saveSearchIndex();

			if ($timer->isElapsed())
			{
				$nextId = $item['ID'];
				break;
			}
		}

		if (!$nextId)
		{
			$hasIndex = Application::getConnection()->getIndexName(
				Model\LetterTable::getTableName(),
				["SEARCH_CONTENT"],
				true
			) !== null;

			$entity = Model\LetterTable::getEntity();
			$entity->enableFullTextIndex("SEARCH_CONTENT", $hasIndex);
		}

		return $nextId;
	}

	/**
	 * Index letters by agent.
	 *
	 * @param int|null $lastId ID of last indexed element.
	 * @return string
	 */
	public static function indexLettersAgent($lastId = null)
	{
		$nextId = self::indexLetters($lastId);
		if (!$nextId)
		{
			return '';
		}

		return "\\" . __CLASS__ . "::indexLettersAgent($nextId);";
	}
}