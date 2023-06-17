<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Main;
use Bitrix\Socialnetwork\LogTable;
use Bitrix\Socialnetwork\LogCommentTable;
use Bitrix\Socialnetwork\LogIndexTable;
use Bitrix\Main\Loader;
use Bitrix\Disk\Uf\FileUserType;
use Bitrix\Disk\AttachedObject;
use Bitrix\Socialnetwork\Livefeed\RenderParts;

class LogIndex
{
	public static function getUserName($userId = 0)
	{
		$result = '';
		$userId = (int)$userId;

		if ($userId <= 0)
		{
			return $result;
		}

		$code = 'U'.$userId;
		$data = self::getEntitiesName([ $code ]);
		if (!empty($data[$code]))
		{
			$result = $data[$code];
		}

		return $result;
	}

	public static function getEntitiesName($entityCodesList = []): array
	{
		static $renderPartsUser = false;
		static $renderPartsSonetGroup = false;
		static $renderPartsDepartment = false;

		$result = [];
		if (
			!is_array($entityCodesList)
			|| empty($entityCodesList)
		)
		{
			return $result;
		}

		$renderOptions = [
			'skipLink' => true,
		];

		if ($renderPartsUser === false)
		{
			$renderPartsUser = new RenderParts\User($renderOptions);
		}
		if ($renderPartsSonetGroup === false)
		{
			$renderPartsSonetGroup = new RenderParts\SonetGroup($renderOptions);
		}
		if ($renderPartsDepartment === false)
		{
			$renderPartsDepartment = new RenderParts\Department($renderOptions);
		}

		foreach ($entityCodesList as $code)
		{
			$renderData = false;
			if (preg_match('/^U(\d+)$/i', $code, $matches))
			{
				$renderData = $renderPartsUser->getData($matches[1]);
			}
			elseif (preg_match('/^SG(\d+)$/i', $code, $matches))
			{
				$renderData = $renderPartsSonetGroup->getData($matches[1]);
			}
			elseif (
				preg_match('/^D(\d+)$/i', $code, $matches)
				|| preg_match('/^DR(\d+)$/i', $code, $matches)
			)
			{
				$renderData = $renderPartsDepartment->getData($matches[1]);
			}

			if (
				$renderData
				&& $renderData['name']
			)
			{
				$result[$code] = $renderData['name'];
			}
		}

		return $result;
	}

	public static function getDiskUFFileNameList($valueList = []): array
	{
		$result = [];

		if (
			!empty($valueList)
			&& is_array($valueList)
			&& Loader::includeModule('disk')
		)
		{
			$attachedIdList = [];
			foreach ($valueList as $value)
			{
				list($type, $realValue) = FileUserType::detectType($value);
				if ($type == FileUserType::TYPE_NEW_OBJECT)
				{
					$file = \Bitrix\Disk\File::loadById($realValue, [ 'STORAGE' ]);
					$result[] = strip_tags($file->getName());
				}
				else
				{
					$attachedIdList[] = $realValue;
				}
			}

			if (!empty($attachedIdList))
			{
				$attachedObjects = AttachedObject::getModelList([
					'with' => [ 'OBJECT' ],
					'filter' => [
						'ID' => $attachedIdList,
					],
				]);
				foreach ($attachedObjects as $attachedObject)
				{
					$file = $attachedObject->getFile();
					$result[] = strip_tags($file->getName());
				}
			}
		}

		return $result;
	}

	public static function setIndex($params = []): void
	{
		if (!is_array($params))
		{
			return;
		}

		$fields = ($params['fields'] ?? []);
		$itemType = trim($params['itemType'] ?? '');
		$itemId = (int)($params['itemId'] ?? 0);

		if (
			!is_array($fields)
			|| empty($fields)
			|| empty($itemType)
			|| !in_array($itemType, LogIndexTable::getItemTypes())
			|| $itemId <= 0
		)
		{
			return;
		}

		$eventId = trim($fields['EVENT_ID'] ?? '');
		$sourceId = (int)($fields['SOURCE_ID'] ?? 0);
		$logId = (int)($fields['LOG_ID'] ?? 0);
		$dateCreate = false;
		$logDateUpdate = false;

		if (
			empty($eventId)
			|| $sourceId <= 0
		)
		{
			if ($itemType === LogIndexTable::ITEM_TYPE_LOG)
			{
				$logId = $itemId;
				$res = LogTable::getList([
					'filter' => [
						'=ID' => $itemId,
					],
					'select' => [ 'ID', 'EVENT_ID', 'SOURCE_ID', 'LOG_UPDATE' ],
				]);
				if ($logEntry = $res->fetch())
				{
					$eventId = trim($logEntry['EVENT_ID'] ?? '');
					$sourceId = (int)($logEntry['SOURCE_ID'] ?? 0);
					$logDateUpdate = $logEntry['LOG_UPDATE'];
					$dateCreate = $logEntry['LOG_DATE'] ?? null;
				}
			}
			elseif ($itemType === LogIndexTable::ITEM_TYPE_COMMENT)
			{
				$res = LogCommentTable::getList([
					'filter' => [
						'=ID' => $itemId,
					],
					'select' => [
						'ID',
						'LOG_ID',
						'EVENT_ID',
						'SOURCE_ID',
						'LOG_UPDATE' => 'LOG.LOG_UPDATE',
						'LOG_DATE',
					],
				]);
				if ($comment = $res->fetch())
				{
					$eventId = trim($comment['EVENT_ID'] ?? '');
					$sourceId = (int)($comment['SOURCE_ID'] ?? 0);
					$logId = (int)($comment['LOG_ID'] ?? 0);
					$logDateUpdate = $comment['LOG_UPDATE'];
					$dateCreate = $comment['LOG_DATE'];
				}
			}
		}

		if (empty($eventId))
		{
			return;
		}

		$content = '';
		$event = new Main\Event(
			'socialnetwork',
			($itemType === LogIndexTable::ITEM_TYPE_COMMENT ? 'onLogCommentIndexGetContent' : 'onLogIndexGetContent'),
			[
				'eventId' => $eventId,
				'sourceId' => $sourceId,
				'itemId' => $itemId,
			]
		);
		$event->send();

		foreach ($event->getResults() as $eventResult)
		{
			if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS)
			{
				$eventParams = $eventResult->getParameters();

				if (
					is_array($eventParams)
					&& isset($eventParams['content'])
				)
				{
					$content = $eventParams['content'];
					if (Main\Loader::includeModule('search'))
					{
						$content = \CSearch::killTags($content);
					}
					$content = trim(str_replace(
						[ "\r", "\n", "\t" ],
						' ',
						$content
					));

					$content = self::prepareToken($content);
				}
				break;
			}
		}

		if (empty($content))
		{
			return;
		}

		if ($logId <= 0)
		{
			if ($itemType === LogIndexTable::ITEM_TYPE_LOG)
			{
				$logId = $itemId;
			}
			elseif ($itemType === LogIndexTable::ITEM_TYPE_COMMENT)
			{
				$res = LogCommentTable::getList([
					'filter' => [
						'=ID' => $itemId,
					],
					'select' => [
						'ID',
						'LOG_ID',
						'LOG.LOG_UPDATE',
						'LOG_UPDATE' => 'LOG.LOG_UPDATE',
						'LOG_DATE',
					],
				]);
				if ($comment = $res->fetch())
				{
					$logId = (int)$comment['LOG_ID'];
					$logDateUpdate = $comment['LOG_UPDATE'];
					$dateCreate = $comment['LOG_DATE'];
				}
			}
		}

		if ($logId <= 0)
		{
			return;
		}

		if (
			!$logDateUpdate
			|| (
				!$dateCreate
				&& $itemType === LogIndexTable::ITEM_TYPE_LOG
			)
		)
		{
			$res = LogTable::getList([
				'filter' => [
					'=ID' => $logId,
				],
				'select' => [ 'ID', 'LOG_UPDATE', 'LOG_DATE' ],
			]);
			if ($logEntry = $res->fetch())
			{
				$logDateUpdate = $logEntry['LOG_UPDATE'];
				if ($itemType === LogIndexTable::ITEM_TYPE_LOG)
				{
					$dateCreate = $logEntry['LOG_DATE'];
				}
			}
		}

		if (
			!$dateCreate
			&& $itemType === LogIndexTable::ITEM_TYPE_COMMENT
		)
		{
			$res = LogCommentTable::getList([
				'filter' => [
					'=ID' => $itemId,
				],
				'select' => [ 'ID', 'LOG_DATE' ],
			]);
			if ($logComment = $res->fetch())
			{
				$dateCreate = $logComment['LOG_DATE'];
			}
		}

		$indexFields = [
			'itemType' => $itemType,
			'itemId' => $itemId,
			'logId' => $logId,
			'content' => $content,
		];

		if ($logDateUpdate)
		{
			$indexFields['logDateUpdate'] = $logDateUpdate;
		}

		if ($dateCreate)
		{
			$indexFields['dateCreate'] = $dateCreate;
		}

		LogIndexTable::set($indexFields);
	}

	public static function deleteIndex($params = []): void
	{
		if (!is_array($params))
		{
			return;
		}

		$itemType = trim($params['itemType'] ?? '');
		$itemId = (int)($params['itemId'] ?? 0);

		if (
			empty($itemType)
			|| !in_array($itemType, LogIndexTable::getItemTypes())
			|| $itemId <= 0
		)
		{
			return;
		}

		if ($itemType === LogIndexTable::ITEM_TYPE_LOG) // delete all comments
		{
			$connection = Main\Application::getConnection();
			$query = "DELETE FROM ".LogIndexTable::getTableName()." WHERE LOG_ID = ".$itemId;
			$connection->queryExecute($query);
		}

		LogIndexTable::delete([
			'ITEM_TYPE' => $itemType,
			'ITEM_ID' => $itemId,
		]);
	}

	public static function prepareToken($str): string
	{
		return str_rot13($str);
	}

	public static function OnAfterLogUpdate(\Bitrix\Main\Entity\Event $event)
	{
		$primary = $event->getParameter('primary');
		$logId = (int)(!empty($primary['ID']) ? $primary['ID'] : 0);
		$fields = $event->getParameter('fields');

		if (
			$logId > 0
			&& !empty($fields)
			&& !empty($fields['LOG_UPDATE'])
		)
		{
			LogIndexTable::setLogUpdate([
				'logId' => $logId,
				'value' => $fields['LOG_UPDATE'],
			]);
		}
	}
}
