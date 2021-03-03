<?php
namespace Bitrix\Socialnetwork\Component\LogList;

use Bitrix\Socialnetwork\LogPageTable;
use Bitrix\Socialnetwork\LogViewTable;
use Bitrix\Socialnetwork\UserToGroupTable;

class Page
{
	protected $component;
	protected $processorInstance;
	protected $request;

	protected $needSetLogPage = false;
	protected $dateLastPageStart = null;
	protected $lastPageData = null;
	protected $prevPageLogIdList = [];
	protected $dateFirstPageTS = 0;

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

	public function setNeedSetLogPage($value = false)
	{
		$this->needSetLogPage = $value;
	}
	public function getNeedSetLogPage()
	{
		return $this->needSetLogPage;
	}

	public function setDateLastPageStart($value = null)
	{
		$this->dateLastPageStart = $value;
	}
	public function getDateLastPageStart()
	{
		return $this->dateLastPageStart;
	}

	public function setLastPageData($value = null)
	{
		$this->lastPageData = $value;
	}
	public function getLastPageData()
	{
		return $this->lastPageData;
	}

	public function setPrevPageLogIdList($value = [])
	{
		$this->prevPageLogIdList = $value;
	}
	public function getPrevPageLogIdList()
	{
		return $this->prevPageLogIdList;
	}

	public function setDateFirstPageTimestamp($value = 0)
	{
		$this->dateFirstPageTS = $value;
	}
	public function getDateFirstPageTimestamp()
	{
		return $this->dateFirstPageTS;
	}

	public function preparePrevPageLogId()
	{
		$request = $this->getRequest();
		$params = $this->getComponent()->arParams;

		$prevPageLogId = null;
		if (isset($params['PREV_PAGE_LOG_ID']))
		{
			$prevPageLogId = $params['PREV_PAGE_LOG_ID'];
		}
		elseif ($request->get('pplogid') !== null)
		{
			$prevPageLogId = $request->get('pplogid');
		}

		if ($prevPageLogId !== null)
		{
			$prevPageLogIdList = explode('|', trim($prevPageLogId));
			foreach($prevPageLogIdList as $key => $val)
			{
				preg_match('/^(\d+)$/', $val, $matches);
				if (count($matches) <= 0)
				{
					unset($prevPageLogIdList[$key]);
				}
			}
			$prevPageLogIdList = array_unique($prevPageLogIdList);
			$this->setPrevPageLogIdList($prevPageLogIdList);
		}
	}

	public function getLogPageData(&$result)
	{
		$params = $this->getComponent()->arParams;
		$processorInstance = $this->getProcessorInstance();

		$this->setNeedSetLogPage(false);

		if ($params['SET_LOG_PAGE_CACHE'] === 'Y')
		{
			$resPages = LogPageTable::getList([
				'order' => [],
				'filter' => [
					'USER_ID' => $result['currentUserId'],
					'=SITE_ID' => SITE_ID,
					'=GROUP_CODE' => $result['COUNTER_TYPE'],
					'PAGE_SIZE' => $params['PAGE_SIZE'],
					'PAGE_NUM' => $result['PAGE_NUMBER']
				],
				'select' => [ 'PAGE_LAST_DATE', 'TRAFFIC_AVG', 'TRAFFIC_CNT', 'TRAFFIC_LAST_DATE' ]
			]);

			if ($pagesFields = $resPages->fetch())
			{
				$this->setDateLastPageStart($pagesFields['PAGE_LAST_DATE']);
				$this->setLastPageData([
					'TRAFFIC_LAST_DATE_TS' => ($pagesFields['TRAFFIC_LAST_DATE'] ? $processorInstance->makeTimeStampFromDateTime($pagesFields['TRAFFIC_LAST_DATE'], 'FULL') : 0),
					'TRAFFIC_AVG' => (int)$pagesFields['TRAFFIC_AVG'],
					'TRAFFIC_CNT' => (int)$pagesFields['TRAFFIC_CNT']
				]);
				$processorInstance->setFilterKey('>=LOG_UPDATE', convertTimeStamp($processorInstance->makeTimeStampFromDateTime($pagesFields['PAGE_LAST_DATE'], 'FULL') - 60*60*24*1, 'FULL'));
			}
			elseif(
				$result['isExtranetSite']
				&& !$this->getComponent()->getCurrentUserAdmin()
			) // extranet user
			{
				$res = UserToGroupTable::getList([
					'order' => [
						'GROUP_DATE_CREATE' => 'ASC'
					],
					'filter' => [
						'USER_ID' => $result['currentUserId'],
						'@ROLE' => UserToGroupTable::getRolesMember(),
						'!GROUP_DATE_CREATE' => false
					],
					'select' => [
						'GROUP_DATE_CREATE' => 'GROUP.DATE_CREATE'
					]
				]);
				if ($relation = $res->fetch())
				{
					$processorInstance->setFilterKey('>=LOG_UPDATE', $relation['GROUP_DATE_CREATE']);
				}
			}
			elseif (
				(
					$result['COUNTER_TYPE'] !== '**'
					|| $result['MY_GROUPS_ONLY'] !== 'Y'
				)
				&& $result['PAGE_NUMBER'] <= 1
			)
			{
				$resPages = LogPageTable::getList([
					'order' => [
						'PAGE_LAST_DATE' => 'DESC'
					],
					'filter' => [
						'=SITE_ID' => SITE_ID,
						'=GROUP_CODE' => $result['COUNTER_TYPE'],
						'PAGE_SIZE' => $params['PAGE_SIZE'],
						'PAGE_NUM' => $result['PAGE_NUMBER']
					],
					'select' => [ 'PAGE_LAST_DATE' ]
				]);

				if ($pagesFields = $resPages->fetch())
				{
					$this->setDateLastPageStart($pagesFields['PAGE_LAST_DATE']);
					$processorInstance->setFilterKey('>=LOG_UPDATE', convertTimeStamp($processorInstance->makeTimeStampFromDateTime($pagesFields['PAGE_LAST_DATE'], 'FULL') - 60*60*24*4, 'FULL'));
					$this->setNeedSetLogPage(true);
				}
			}
		}
	}

	public function setLogPageData(&$result)
	{
		$params = $this->getComponent()->arParams;
		$processorInstance = $this->getProcessorInstance();

		$lastEventFields = false;
		if (is_array($result['Events']))
		{
			$tmp = $result['Events'];
			$lastEventFields = array_pop($tmp);
			unset($tmp);
		}

		$result['LAST_ENTRY_DATE_TS'] = 0;
		$result['dateLastPageId'] = ($lastEventFields ? $lastEventFields['ID'] : 0);

		if ($lastEventFields)
		{
			if ($params['USE_FOLLOW'] === 'N')
			{
				if (!empty($processorInstance->getOrderKey('LOG_DATE')))
				{
					$result['LAST_ENTRY_DATE_TS'] = $processorInstance->makeTimeStampFromDateTime($lastEventFields['LOG_DATE'], 'FULL');
				}
				elseif ($lastEventFields['LOG_UPDATE'])
				{
					$result['LAST_ENTRY_DATE_TS'] = $processorInstance->makeTimeStampFromDateTime($lastEventFields['LOG_UPDATE'], 'FULL');
				}
			}

			if (
				empty($result['LAST_ENTRY_DATE_TS'])
				&& $lastEventFields['DATE_FOLLOW']
			)
			{
				$result['LAST_ENTRY_DATE_TS'] = $processorInstance->makeTimeStampFromDateTime($lastEventFields['DATE_FOLLOW'], 'FULL');
			}
		}

		if ($params['SET_LOG_PAGE_CACHE'] !== 'N')
		{
			$result['dateLastPageTS'] = $result['LAST_ENTRY_DATE_TS'];
		}

		if (!empty($result['dateLastPageTS']))
		{
			$dateLastPage = convertTimeStamp($result['dateLastPageTS'], 'FULL');
		}

		if (
			Util::checkUserAuthorized()
			&& $params['SET_LOG_PAGE_CACHE'] === 'Y'
			&& $dateLastPage
			&& (
				!$this->getDateLastPageStart()
				|| $this->getDateLastPageStart() != $dateLastPage
				|| $this->getNeedSetLogPage()
			)
		)
		{
			$lastPageData = $this->getLastPageData();
			if (empty($lastPageData))
			{
				$lastPageData = [
					'TRAFFIC_AVG' => 0,
					'TRAFFIC_CNT' => 0,
					'TRAFFIC_LAST_DATE_TS' => 0
				];
			}

			$bNeedSetTraffic = \CSocNetLogComponent::isSetTrafficNeeded([
				'PAGE_NUMBER' => $result['PAGE_NUMBER'],
				'GROUP_CODE' => $result['COUNTER_TYPE'],
				'TRAFFIC_LAST_DATE_TS' => $lastPageData['TRAFFIC_LAST_DATE_TS']
			]);

			\CSocNetLogPages::set(
				$result['currentUserId'],
				convertTimeStamp($processorInstance->makeTimeStampFromDateTime($dateLastPage, 'FULL') - $result['TZ_OFFSET'], 'FULL'),
				$params['PAGE_SIZE'],
				$result['PAGE_NUMBER'],
				SITE_ID,
				$result['COUNTER_TYPE'],
				(
					$bNeedSetTraffic
						? ($lastPageData['TRAFFIC_AVG'] + $this->getDateFirstPageTimestamp() - $result['dateLastPageTS']) / ($lastPageData['TRAFFIC_CNT'] + 1)
						: false
				),
				(
				$bNeedSetTraffic
					? ($lastPageData['TRAFFIC_CNT'] + 1)
					: false
				)
			);

			if (
				$result['PAGE_NUMBER'] == 1
				&& $params['USE_TASKS'] == 'Y'
				&& $result['EXPERT_MODE'] != 'Y'
			)
			{
				$result['EXPERT_MODE_SET'] = LogViewTable::checkExpertModeAuto($result['currentUserId'], $processorInstance->getTasksCount(), $params['PAGE_SIZE']);
				if ($result['EXPERT_MODE_SET'])
				{
					$params['SET_LOG_COUNTER'] = 'N';
					$this->getComponent()->arParams = $params;
				}
			}
		}
	}

	public function deleteLogPageData($result)
	{
		$params = $this->getComponent()->arParams;

		if (
			count($result['arLogTmpID']) == 0
			&& $this->getDateLastPageStart() !== null
			&& Util::checkUserAuthorized()
			&& $params['SET_LOG_PAGE_CACHE'] === 'Y'
		)
		{
			\CSocNetLogPages::deleteEx($result['currentUserId'], SITE_ID, $params['PAGE_SIZE'], $result['COUNTER_TYPE']);
			$this->setNeedSetLogPage(true);
		}
	}

}
?>