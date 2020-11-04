<?php
namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Socialnetwork\LogPageTable;
use Bitrix\Socialnetwork\LogViewTable;
use Bitrix\Socialnetwork\UserToGroupTable;

class Counter
{
	protected $component;
	protected $processorInstance;
	protected $request;

	protected $emptyCounter = false;

	public function __construct($params)
	{
		if (!empty($params['component']))
		{
			$this->component = $params['component'];
		}
		if (!empty($params['processorInstance']))
		{
			$this->processorInstance = $params['processorInstance'];
		}
		if (!empty($params['request']))
		{
			$this->request = $params['request'];
		}
		else
		{
			$this->request = Util::getRequest();;
		}
	}

	public function getRequest()
	{
		return $this->request;
	}

	protected function getComponent()
	{
		return $this->component;
	}
	protected function getProcessorInstance()
	{
		return $this->processorInstance;
	}

	public function setEmptyCounter($value = false)
	{
		$this->emptyCounter = $value;
	}
	public function getEmptyCounter()
	{
		return $this->emptyCounter;
	}

	public function processCounterTypeData(&$result)
	{
		$params = $this->getComponent()->arParams;

		$result['COUNTER_TYPE'] = '**';

		if ($params['GROUP_ID'] > 0)
		{
			$result['COUNTER_TYPE'] = 'SG'.$params['GROUP_ID'];
		}
		elseif(
			$params['IS_CRM'] === 'Y'
			&& (
				$params['SET_LOG_COUNTER'] != 'N'
				|| $params['SET_LOG_PAGE_CACHE'] != 'N'
			)
		)
		{
			$result['COUNTER_TYPE'] = (
				is_set($params['CUSTOM_DATA'])
				&& is_set($params['CUSTOM_DATA']['CRM_PRESET_TOP_ID'])
				&& $params['CUSTOM_DATA']['CRM_PRESET_TOP_ID'] == 'all'
					? 'CRM_**_ALL'
					: 'CRM_**'
			);
		}
		elseif($params['EXACT_EVENT_ID'] == 'blog_post')
		{
			$result['COUNTER_TYPE'] = 'blog_post';
		}
	}

	public function clearLogCounter(&$result)
	{
		$params = $this->getComponent()->arParams;

		if (
			Util::checkUserAuthorized()
			&& $params['SET_LOG_COUNTER'] == 'Y'
			&& !(isset($result['EXPERT_MODE_SET']) && $result['EXPERT_MODE_SET'])
			&& (
				intval($result['LOG_COUNTER']) > 0
				|| $this->getEmptyCounter()
			)
		)
		{
			\CUserCounter::clearByUser(
				$result['currentUserId'],
				[ SITE_ID, '**' ],
				$result['COUNTER_TYPE'],
				true
			);

			if (intval($result['LOG_COUNTER_IMPORTANT']) > 0)
			{
				\CUserCounter::clearByUser(
					$result['currentUserId'],
					SITE_ID,
					'BLOG_POST_IMPORTANT'
				);
			}

			$res = getModuleEvents('socialnetwork', 'OnSonetLogCounterClear');
			while ($eventFields = $res->fetch())
			{
				executeModuleEventEx($eventFields, [ $result['COUNTER_TYPE'], intval($result['LAST_LOG_TS']) ]);
			}
		}
	}

	public function setLogCounter(&$result)
	{
		$params = $this->getComponent()->arParams;

		$result['LOG_COUNTER'] = 0;
		$result['LOG_COUNTER_IMPORTANT'] = 0;

		if (
			Util::checkUserAuthorized()
			&& $params['SET_LOG_COUNTER'] == 'Y'
		)
		{
			$counters = \CUserCounter::getValues($result['currentUserId'], SITE_ID);

			if (isset($counters['BLOG_POST_IMPORTANT']))
			{
				$result['LOG_COUNTER_IMPORTANT'] = intval($counters['BLOG_POST_IMPORTANT']);
			}

			if (isset($counters[$result['COUNTER_TYPE']]))
			{
				$result['LOG_COUNTER'] = intval($counters[$result['COUNTER_TYPE']]);
			}
			else
			{
				$this->setEmptyCounter(true);
				$result['LOG_COUNTER'] = 0;
			}
		}
	}
}
?>