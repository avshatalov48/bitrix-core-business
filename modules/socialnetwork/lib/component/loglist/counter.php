<?php

namespace Bitrix\Socialnetwork\Component\LogList;

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
			$this->request = Util::getRequest();
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

	public function setEmptyCounter($value = false): void
	{
		$this->emptyCounter = $value;
	}

	public function getEmptyCounter(): bool
	{
		return $this->emptyCounter;
	}

	public function processCounterTypeData(&$result): void
	{
		$params = $this->getComponent()->arParams;

		$result['COUNTER_TYPE'] = \CUserCounter::LIVEFEED_CODE;

		if ($params['GROUP_ID'] > 0)
		{
			$result['COUNTER_TYPE'] = \CUserCounter::LIVEFEED_CODE . 'SG' . $params['GROUP_ID'];
		}
		elseif(
			$params['IS_CRM'] === 'Y'
			&& (
				$params['SET_LOG_COUNTER'] !== 'N'
				|| $params['SET_LOG_PAGE_CACHE'] !== 'N'
			)
		)
		{
			$result['COUNTER_TYPE'] = (
				is_set($params['CUSTOM_DATA'])
				&& is_set($params['CUSTOM_DATA']['CRM_PRESET_TOP_ID'])
				&& $params['CUSTOM_DATA']['CRM_PRESET_TOP_ID'] === 'all'
					? 'CRM_**_ALL'
					: 'CRM_**'
			);
		}
		elseif(($params['EXACT_EVENT_ID'] ?? '') === 'blog_post')
		{
			$result['COUNTER_TYPE'] = 'blog_post';
		}
	}

	public function clearLogCounter(&$result): void
	{
		$params = $this->getComponent()->arParams;

		if (
			$params['SET_LOG_COUNTER'] !== 'Y'
			|| (isset($result['EXPERT_MODE_SET']) && $result['EXPERT_MODE_SET'])
			|| !Util::checkUserAuthorized()
		)
		{
			return;
		}

		if (
			(int)$result['LOG_COUNTER'] > 0
			|| $this->getEmptyCounter()
		)
		{
			\CUserCounter::clear(
				$result['currentUserId'],
				$result['COUNTER_TYPE'],
				[ SITE_ID, '**' ],
				true, // sendPull
				true // multiple
			);

			if ((int)$result['LOG_COUNTER_IMPORTANT'] > 0)
			{
				\CUserCounter::clear(
					$result['currentUserId'],
					'BLOG_POST_IMPORTANT',
					SITE_ID
				);
			}

			$res = getModuleEvents('socialnetwork', 'OnSonetLogCounterClear');
			while ($eventFields = $res->fetch())
			{
				executeModuleEventEx($eventFields, [ $result['COUNTER_TYPE'], (int)$result['LAST_LOG_TS'] ]);
			}
		}
		elseif (in_array($result['COUNTER_TYPE'], [ '**', 'CRM_**' ], true)) // set last date only
		{
			$pool = \Bitrix\Main\Application::getInstance()->getConnectionPool();
			$pool->useMasterOnly(true);

			\CUserCounter::clear(
				$result['currentUserId'],
				$result['COUNTER_TYPE'],
				[ SITE_ID, '**' ],
				false, // sendPull
				false, // multiple
				false // cleanCache
			);

			$pool->useMasterOnly(false);
		}
	}

	public function setLogCounter(&$result): void
	{
		$params = $this->getComponent()->arParams;

		$result['LOG_COUNTER'] = 0;
		$result['LOG_COUNTER_IMPORTANT'] = 0;

		if (
			$params['SET_LOG_COUNTER'] !== 'Y'
			|| !Util::checkUserAuthorized()
		)
		{
			return;
		}

		$counters = \CUserCounter::getValues($result['currentUserId'], SITE_ID);

		if (isset($counters['BLOG_POST_IMPORTANT']))
		{
			$result['LOG_COUNTER_IMPORTANT'] = (int)$counters['BLOG_POST_IMPORTANT'];
		}

		if (isset($counters[$result['COUNTER_TYPE']]))
		{
			$result['LOG_COUNTER'] = (int)$counters[$result['COUNTER_TYPE']];
		}
		else
		{
			$this->setEmptyCounter(true);
			$result['LOG_COUNTER'] = 0;
		}
	}
}
