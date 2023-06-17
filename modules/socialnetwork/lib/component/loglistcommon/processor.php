<?php

namespace Bitrix\Socialnetwork\Component\LogListCommon;

use Bitrix\Main\DB\SqlExpression;
use Bitrix\Main\Loader;
use Bitrix\Main\UserCounterTable;
use Bitrix\Socialnetwork\Component\LogList\Util;
use Bitrix\Socialnetwork\LogCommentTable;

class Processor
{
	protected $component;
	protected $request;

	protected $filter = [];
	protected $order = [ 'LOG_DATE' => 'DESC' ];
	protected $listParams = [];
	protected $navParams = false;
	protected $firstPage = false;

	public function __construct($params)
	{
		if(!empty($params['component']))
		{
			$this->component = $params['component'];
		}

		if(!empty($params['request']))
		{
			$this->request = $params['request'];
		}
		else
		{
			$this->request = Util::getRequest();
		}
	}

	protected function getComponent()
	{
		return $this->component;
	}

	public function getRequest()
	{
		return $this->request;
	}

	public function setFilter(array $value = []): void
	{
		$this->filter = $value;
	}

	public function getFilter(): array
	{
		return $this->filter;
	}

	public function setFilterKey($key = '', $value = false): void
	{
		if ($key == '')
		{
			return;
		}
		$this->filter[$key] = $value;
	}

	public function unsetFilterKey($key = ''): void
	{
		if ($key == '')
		{
			return;
		}
		unset($this->filter[$key]);
	}

	public function getFilterKey($key = '')
	{
		if ($key == '')
		{
			return false;
		}
		return ($this->filter[$key] ?? false);
	}

	public function setOrder(array $value = []): void
	{
		$this->order = $value;
	}

	public function setOrderKey($key = '', $value = false): void
	{
		if ($key == '')
		{
			return;
		}
		$this->order[$key] = $value;
	}

	public function getOrder(): array
	{
		return $this->order;
	}

	public function getOrderKey($key = '')
	{
		if ($key == '')
		{
			return false;
		}
		return ($this->order[$key] ?? false);
	}

	public function setListParams(array $value = []): void
	{
		$this->listParams = $value;
	}

	public function setListParamsKey($key = '', $value = false): void
	{
		if ($key == '')
		{
			return;
		}
		$this->listParams[$key] = $value;
	}

	public function getListParams(): array
	{
		return $this->listParams;
	}

	public function getListParamsKey($key = '')
	{
		if ($key == '')
		{
			return false;
		}
		return ($this->listParams[$key] ?? false);
	}

	public function setNavParams($value = false): void
	{
		$this->navParams = $value;
	}

	public function getNavParams()
	{
		return $this->navParams;
	}

	public function setFirstPage($value = false): void
	{
		$this->firstPage = $value;
	}

	public function getFirstPage(): bool
	{
		return $this->firstPage;
	}

	public function getUnreadTaskCommentsIdList(&$result): void
	{
		$result['UNREAD_COMMENTS_ID_LIST'] = [];

		if ((int) ($result['LOG_COUNTER'] ?? null) <= 0)
		{
			return;
		}

		$tasks2LogList = $this->getComponent()->getTask2LogListValue();
		if (empty($tasks2LogList))
		{
			return;
		}

		$result['UNREAD_COMMENTS_ID_LIST'] = self::getUnreadCommentsIdList([
			'userId' => $result['currentUserId'],
			'logIdList' => array_values($tasks2LogList),
		]);
	}

	protected static function getUnreadCommentsIdList($params): array
	{
		$result = [];

		$userId = (int)($params['userId'] ?? 0);
		$logIdList = (is_set($params['logIdList']) && is_array($params['logIdList']) ? $params['logIdList'] : []);

		if (
			$userId <= 0
			|| empty($logIdList)
		)
		{
			return $result;
		}

		foreach($logIdList as $logId)
		{
			$result[(int)$logId] = [];
		}

		$query = UserCounterTable::query();
		$query->addFilter('=USER_ID', $userId);
		$query->addFilter('=SITE_ID', SITE_ID);

		$subQuery = LogCommentTable::query();
		$subQuery->whereIn('LOG_ID', $logIdList);
		$subQuery->addSelect(new \Bitrix\Main\Entity\ExpressionField('COMMENT_CODE', "CONCAT('**LC', %s)", [ 'ID' ]));

		$query->addFilter('@CODE', new SqlExpression($subQuery->getQuery()));
		$query->addSelect('CODE');
		$res = $query->exec();

		$unreadCommentIdList = [];

		while ($counterFields = $res->fetch())
		{
			if (preg_match('/\*\*LC(\d+)/i', $counterFields['CODE'], $matches))
			{
				$unreadCommentIdList[] = (int)$matches[1];
			}
		}

		if (!empty($unreadCommentIdList))
		{
			$res = LogCommentTable::getList([
				'filter' => [
					'@ID' => $unreadCommentIdList,
				],
				'select' => [ 'ID', 'LOG_ID' ],
			]);
			while ($logCommentFields = $res->fetch())
			{
				$result[(int)$logCommentFields['LOG_ID']][] = (int)$logCommentFields['ID'];
			}
		}

		return $result;
	}

	public function getResultTaskCommentsIdList(&$result): void
	{
		$result['RESULT_TASKS_DATA'] = [];
		$result['RESULT_FIELD_TASKS_ID'] = [];
		$result['RESULT_COMMENTS_DATA'] = [];

		$tasks2LogList = $this->getComponent()->getTask2LogListValue();
		if (
			empty($tasks2LogList)
			|| !Loader::includeModule('tasks')
			|| !class_exists("Bitrix\\Tasks\\Internals\\Task\\Result\\ResultManager")
		)
		{
			return;
		}

		foreach ($tasks2LogList as $taskId => $logId)
		{
			$result['RESULT_TASKS_DATA'][(int)$logId] = (int)$taskId;
		}

		$taskIdList = [];

		$res = \Bitrix\Tasks\Internals\TaskTable::getList([
			'filter' => [
				'@ID' => array_keys($tasks2LogList),
				'!=STATUS' => \CTasks::STATE_COMPLETED,
			],
			'select' => [ 'ID' ],
		]);
		while ($taskFields = $res->fetch())
		{
			$taskIdList[] = (int)$taskFields['ID'];
		}

		if (empty($taskIdList))
		{
			return;
		}

		$result['RESULT_FIELD_TASKS_ID'] = $taskIdList;

		$resultCommentIdList = \Bitrix\Tasks\Internals\Task\Result\ResultManager::findResultComments($taskIdList);
		foreach ($tasks2LogList as $taskId => $logId)
		{
			if (isset($resultCommentIdList[$taskId]))
			{
				$res = [];
				foreach ($resultCommentIdList[$taskId] as $value)
				{
					$res[$value] = [
						'taskId' => $taskId,
						'logId' => $logId,
						'commentId' => $value,
					];
				}
				$result['RESULT_COMMENTS_DATA'][$taskId] = $res;
			}
		}
	}

	public function getMicroblogUserId(&$result): void
	{
		$params = $this->getComponent()->arParams;

		$result['MICROBLOG_USER_ID'] = (
			$result['currentUserId'] > 0
			&& (
				($params['ENTITY_TYPE'] ?? null) !== SONET_ENTITY_GROUP
				|| (
					\CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'full_post', $this->getComponent()->getCurrentUserAdmin())
					|| \CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'write_post')
					|| \CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'moderate_post')
					|| \CSocNetFeaturesPerms::canPerformOperation($result['currentUserId'], SONET_ENTITY_GROUP, $params['GROUP_ID'], 'blog', 'premoderate_post')
				)
			)
				? $result['currentUserId']
				: 0
		);
	}
}
