<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Socialnetwork\Item;

use Bitrix\Socialnetwork\LogCommentTable;

class LogComment
{
	private $fields;

	public static function getById($logCommentId = 0)
	{
		static $cachedFields = array();

		$logCommentItem = false;
		$logCommentId = intval($logCommentId);

		if ($logCommentId > 0)
		{
			$logCommentItem = new LogComment;
			$logCommentFields = array();

			if (isset($cachedFields[$logCommentId]))
			{
				$logCommentFields = $cachedFields[$logCommentId];
			}
			else
			{
				$select = array('*');

				$res = LogCommentTable::getList(array(
					'filter' => array('=ID' => $logCommentId),
					'select' => $select
				));
				if ($fields = $res->fetch())
				{
					$logCommentFields = $fields;

					if ($logCommentFields['LOG_DATE'] instanceof \Bitrix\Main\Type\DateTime)
					{
						$logCommentFields['LOG_DATE'] = $logCommentFields['LOG_DATE']->toString();
					}
				}

				$cachedFields[$logCommentId] = $logCommentFields;
			}

			$logCommentItem->setFields($logCommentFields);
		}

		return $logCommentItem;
	}

	public function setFields($fields = array())
	{
		$this->fields = $fields;
	}

	public function getFields()
	{
		return $this->fields;
	}
}
