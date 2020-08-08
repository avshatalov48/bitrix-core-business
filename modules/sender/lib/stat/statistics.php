<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Stat;

use Bitrix\Main\Context;
use Bitrix\Main\Entity\ExpressionField;
use Bitrix\Main\Type\Date;
use Bitrix\Main\UserTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\MailingChainTable;
use Bitrix\Sender\PostingTable;
use Bitrix\Sender\PostingClickTable;
use Bitrix\Sender\MailingSubscriptionTable;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Message;

use Bitrix\Main\Web\Uri;

Loc::loadMessages(__FILE__);

class Statistics
{
	CONST AVERAGE_EFFICIENCY = 0.15;
	CONST USER_OPTION_FILTER_NAME = 'statistics_filter';

	/** @var Filter */
	protected $filter;

	/** @var integer */
	protected $cacheTtl = 3600;

	/** @var array */
	protected $counters = null;

	/** @var integer */
	protected $userId = null;

	/**
	 * Create instance.
	 *
	 * @param Filter $filter Filter.
	 * @return static
	 */
	public static function create(Filter $filter = null)
	{
		return new static($filter);
	}

	/**
	 * Constructor.
	 *
	 * @param Filter $filter Filter.
	 */
	public function __construct(Filter $filter = null)
	{
		if ($filter)
		{
			$this->filter = $filter;
		}
		else
		{
			$this->filter = new Filter();
		}

	}

	/**
	 * Set cache TTL.
	 *
	 * @param integer $cacheTtl Cache ttl.
	 * @return $this
	 */
	public function setCacheTtl($cacheTtl)
	{
		$this->cacheTtl = $cacheTtl;
		return $this;
	}

	/**
	 * Set user Id.
	 *
	 * @param integer $userId User Id.
	 * @return $this
	 */
	public function setUserId($userId)
	{
		$this->userId = $userId;
		return $this;
	}

	/**
	 * Get cache TTL.
	 *
	 * @return integer
	 */
	public function getCacheTtl()
	{
		return $this->cacheTtl;
	}

	/**
	 * Return filter.
	 *
	 * @return Filter
	 */
	public function getFilter()
	{
		return $this->filter;
	}

	/**
	 * Return filter.
	 *
	 * @param string $name Filter name.
	 * @param string $value Filter value.
	 * @return $this
	 */
	public function filter($name, $value = null)
	{
		$this->filter->set($name, $value);
		return $this;
	}

	/**
	 * Init filter from request.
	 *
	 * @return $this
	 */
	public function initFilterFromRequest()
	{
		$request = Context::getCurrent()->getRequest();
		if ($request->isPost())
		{
			$list = $this->filter->getNames();
			foreach ($list as $name)
			{
				$this->filter->set($name, (string) $request->get($name));
			}

			$this->saveFilterToUserOption();
		}
		else
		{
			$this->initFilterFromUserOption();
		}

		return $this;
	}

	/**
	 * Init filter from request.
	 *
	 * @return $this
	 */
	protected function initFilterFromUserOption()
	{
		$isFilterSet = false;
		if ($this->userId)
		{
			$userOptionFilters = \CUserOptions::getOption(
				'sender',
				self::USER_OPTION_FILTER_NAME,
				array(),
				false,
				$this->userId
			);
			$list = $this->filter->getNames();
			foreach ($list as $name)
			{
				if (!isset($userOptionFilters[$name]) || !$userOptionFilters[$name])
				{
					continue;
				}
				$this->filter->set($name, (string) $userOptionFilters[$name]);
				$isFilterSet = true;
			}
		}

		if (!$isFilterSet && !$this->filter->get('period'))
		{
			$this->filter->set('period', Filter::PERIOD_MONTH);
		}

		return $this;
	}

	protected function saveFilterToUserOption()
	{
		if (!$this->userId)
		{
			return;
		}

		$filter = array();
		$list = $this->filter->getNames();
		foreach ($list as $name)
		{
			$value = $this->filter->get($name);
			if (!$value)
			{
				continue;
			}

			$filter[$name] = $value;
		}

		\CUserOptions::setOption('sender', self::USER_OPTION_FILTER_NAME, $filter, false, $this->userId);
	}

	protected static function calculateEfficiency($counters, $maxEfficiency = null)
	{
		$efficiency = self::div('CLICK', 'READ', $counters);
		if (!$maxEfficiency)
		{
			$maxEfficiency = self::AVERAGE_EFFICIENCY * 2;
		}
		$efficiency = $efficiency > $maxEfficiency ? $maxEfficiency : $efficiency;
		return self::getCounterCalculation('EFFICIENCY', $efficiency, $maxEfficiency);
	}

	protected static function div($dividendCode, $dividerCode, $items)
	{
		$divider = 0;
		foreach ($items as $item)
		{
			if ($item['CODE'] == $dividerCode)
			{
				$divider = (float) $item['VALUE'];
				break;
			}
		}

		if ($divider == 0)
		{
			return 0;
		}

		$dividend = 0;
		foreach ($items as $item)
		{
			if ($item['CODE'] == $dividendCode)
			{
				$dividend = (float) $item['VALUE'];
				$dividend = $dividend > $divider ? $divider : $dividend;
				break;
			}
		}

		return $dividend / $divider;
	}

	protected static function formatNumber($number, $num = 1)
	{
		$formatted = number_format($number, $num, '.', ' ');
		$formatted = mb_substr($formatted, -($num + 1)) == '.'.str_repeat('0', $num)? mb_substr($formatted, 0, -2) : $formatted;
		return $formatted;
	}

	protected static function getCounterCalculation($code, $value, $percentBase = 0)
	{
		$value = (float) $value;
		$percentValue = $percentBase > 0 ? $value / $percentBase : 0;

		return array(
			'CODE' => $code,
			'VALUE' => round($value, 3),
			'VALUE_DISPLAY' => self::formatNumber($value, 1),
			'PERCENT_VALUE' => round($percentValue, $code == 'UNSUB' ? 3 : 3),
			'PERCENT_VALUE_DISPLAY' => self::formatNumber($percentValue * 100, $code == 'UNSUB' ? 1 : 1),
		);
	}

	protected function getMappedFilter()
	{
		$filter = array(
			'!=STATUS' => PostingTable::STATUS_NEW,
			'=MAILING.IS_TRIGGER' => 'N',
			'=MAILING_CHAIN.MESSAGE_CODE' => Message\iBase::CODE_MAIL
		);

		$fieldsMap = array(
			'chainId' => '=MAILING_CHAIN_ID',
			'periodFrom' => '>DATE_SENT',
			'periodTo' => '<DATE_SENT',
			'mailingId' => '=MAILING_ID',
			'postingId' => '=ID',
			'authorId' => '=MAILING_CHAIN.CREATED_BY',
		);
		return $this->filter->getMappedArray($fieldsMap, $filter);
	}

	/**
	 * Return efficiency.
	 *
	 * @return float
	 */
	public function getEfficiency()
	{
		return self::calculateEfficiency($this->getCounters());
	}

	/**
	 * Return dynamic of counters.
	 *
	 * @return array
	 */
	public function getCountersDynamic()
	{
		$list = array();
		$filter = $this->getMappedFilter();
		$select = array(
			'SEND_ALL' => 'COUNT_SEND_ALL',
			'SEND_ERROR' => 'COUNT_SEND_ERROR',
			'SEND_SUCCESS' => 'COUNT_SEND_SUCCESS',
			'READ' => 'COUNT_READ',
			'CLICK' => 'COUNT_CLICK',
			'UNSUB' => 'COUNT_UNSUB'
		);
		$runtime = array(
			new ExpressionField('CNT', 'COUNT(%s)', 'ID'),
			new ExpressionField('DATE', 'DATE(%s)', 'DATE_SENT'),
		);
		foreach ($select as $alias => $fieldName)
		{
			$runtime[] = new ExpressionField($alias, 'SUM(%s)', $fieldName);
		}
		$select = array_keys($select);
		$select[] = 'DATE';
		$select[] = 'CNT';
		$listDb = PostingTable::getList(array(
			'select' => $select,
			'filter' => $filter,
			'runtime' => $runtime,
			'order' => array('DATE' => 'ASC'),
			'cache' => array('ttl' => $this->getCacheTtl(), 'cache_joins' => true)
		));
		while($item = $listDb->fetch())
		{
			$date = null;
			foreach ($item as $name => $value)
			{
				if (!in_array($name, array('DATE')))
				{
					continue;
				}

				if ($item['DATE'])
				{
					/** @var Date $date */
					$date = $item['DATE']->getTimestamp();
				}
			}

			$counters = array();
			foreach ($item as $name => $value)
			{
				if (!in_array($name, array('READ', 'CLICK', 'UNSUB')))
				{
					continue;
				}
				else
				{
					$base = $item['SEND_SUCCESS'];
				}

				$counter = self::getCounterCalculation($name, $value, $base);
				$counter['DATE'] = $date;
				$counters[] = $counter;
				$list[$name][] = $counter;
			}

			$effCounter = self::calculateEfficiency($counters, 1);
			$effCounter['DATE'] = $date;
			$list['EFFICIENCY'][] = $effCounter;
		}

		return $list;

	}

	/**
	 * Return counters.
	 *
	 * @return array
	 */
	public function getCounters()
	{
		if ($this->counters !== null)
		{
			return $this->counters;
		}

		$list = array();
		$filter = $this->getMappedFilter();
		$select = array(
			'SEND_ALL' => 'COUNT_SEND_ALL',
			'SEND_ERROR' => 'COUNT_SEND_ERROR',
			'SEND_SUCCESS' => 'COUNT_SEND_SUCCESS',
			'READ' => 'COUNT_READ',
			'CLICK' => 'COUNT_CLICK',
			'UNSUB' => 'COUNT_UNSUB'
		);
		$runtime = array();
		foreach ($select as $alias => $fieldName)
		{
			$runtime[] = new ExpressionField($alias, 'SUM(%s)', $fieldName);
		}
		$listDb = PostingTable::getList(array(
			'select' => array_keys($select),
			'filter' => $filter,
			'runtime' => $runtime,
			'cache' => array('ttl' => $this->getCacheTtl(), 'cache_joins' => true)
		));
		while ($item = $listDb->fetch())
		{
			$list = array_merge($list, $this->createListFromItem($item));
		}

		$this->counters = $list;
		return $list;
	}

	public function initFromArray($postingData)
	{
		$item = [
			'SEND_ALL' => (int)$postingData['COUNT_SEND_ALL'],
			'SEND_ERROR' => (int)$postingData['COUNT_SEND_ERROR'],
			'SEND_SUCCESS' => (int)$postingData['COUNT_SEND_SUCCESS'],
			'READ' => (int)$postingData['COUNT_READ'],
			'CLICK' => (int)$postingData['COUNT_CLICK'],
			'UNSUB' => (int)$postingData['COUNT_UNSUB']
		];
		$this->counters = $this->createListFromItem($item);

		return $this;
	}

	protected function createListFromItem($item)
	{
		$list = [];
		foreach ($item as $name => $value)
		{
			if (mb_substr($name, 0, 4) == 'SEND')
			{
				$base = $item['SEND_ALL'];
			}
			else
			{
				$base = $item['SEND_SUCCESS'];
			}
			$list[] = self::getCounterCalculation($name, $value, $base);
		}
		return $list;
	}

	/**
	 * Return subscribers.
	 *
	 * @return array
	 */
	public function getCounterPostings()
	{
		$query = PostingTable::query();
		$query->addSelect(new ExpressionField('CNT', 'COUNT(1)'));
		$query->setFilter($this->getMappedFilter());
		$query->setCacheTtl($this->getCacheTtl());
		$query->cacheJoins(true);
		$result = $query->exec()->fetch();

		return self::getCounterCalculation('POSTINGS', $result['CNT']);
	}

	/**
	 * Return subscribers.
	 *
	 * @return array
	 */
	public function getCounterSubscribers()
	{
		$filter = array('=IS_UNSUB' => 'N');
		$map = array(
			'mailingId' => '=MAILING_ID',
			'periodFrom' => '>DATE_INSERT',
			'periodTo' => '<DATE_INSERT',
		);
		$filter = $this->filter->getMappedArray($map, $filter);

		$query = MailingSubscriptionTable::query();
		$query->addSelect(new ExpressionField('CNT', 'COUNT(1)'));
		$query->setFilter($filter);
		$query->setCacheTtl($this->getCacheTtl());
		$query->cacheJoins(true);
		$result = $query->exec()->fetch();

		return self::getCounterCalculation('SUBS', $result['CNT']);
	}

	/**
	 * Return click links.
	 *
	 * @param integer $limit Limit.
	 * @return array
	 */
	public function getClickLinks($limit = 15)
	{
		$list = array();
		$clickDb = PostingClickTable::getList(array(
			'select' => array('URL', 'CNT'),
			'filter' => array(
				'=POSTING_ID' => $this->filter->get('postingId'),
			),
			'runtime' => array(
				new ExpressionField('CNT', 'COUNT(%s)', 'ID'),
			),
			'group' => array('URL'),
			'order' => array('CNT' => 'DESC'),
			'limit' => $limit
		));
		while($click = $clickDb->fetch())
		{
			$list[] = $click;
		}

		// TODO: temporary block! Remove
		if (!empty($list))
		{
			$letter = Entity\Letter::createInstanceByPostingId($this->filter->get('postingId'));
			$linkParams = $letter->getMessage()->getConfiguration()->get('LINK_PARAMS');
			if (!$linkParams)
			{
				return $list;
			}

			$parametersTmp = [];
			parse_str($linkParams, $parametersTmp);
			if (!is_array($parametersTmp) || empty($parametersTmp))
			{
				return $list;
			}
			$linkParams = array_keys($parametersTmp);

			$groupedList = [];
			foreach ($list as $index => $item)
			{
				$item['URL'] = (new Uri($item['URL']))
					->deleteParams($linkParams, true)
					->getUri();
				$item['URL'] = urldecode($item['URL']);
				if (!isset($groupedList[$item['URL']]))
				{
					$groupedList[$item['URL']] = 0;
				}
				$groupedList[$item['URL']] += $item['CNT'];
			}
			$list = [];
			foreach ($groupedList as $url => $cnt)
			{
				$list[] = ['URL' => $url, 'CNT' => $cnt];
			}
		}

		return $list;
	}

	/**
	 * Return read counter data by day time.
	 *
	 * @param int $step Step.
	 * @return array
	 */
	public function getReadingByDayTime($step = 2)
	{
		$list = array();
		for ($i = 0; $i < 24; $i++)
		{
			$list[$i] = array(
				'CNT' => 0,
				'CNT_DISPLAY' => 0,
				'DAY_HOUR' => $i,
				'DAY_HOUR_DISPLAY' => (mb_strlen($i) == 1 ? '0' : '') . $i . ':00',
			);
		}

		$filter = $this->getMappedFilter();
		$readDb = PostingTable::getList(array(
			'select' => array('DAY_HOUR', 'CNT'),
			'filter' => $filter,
			'runtime' => array(
				new ExpressionField('CNT', 'COUNT(%s)', 'POSTING_READ.ID'),
				new ExpressionField('DAY_HOUR', 'HOUR(%s)', 'POSTING_READ.DATE_INSERT'),
			),
			'order' => array('DAY_HOUR' => 'ASC'),
		));
		while($read = $readDb->fetch())
		{
			$read['DAY_HOUR'] = intval($read['DAY_HOUR']);
			if (array_key_exists($read['DAY_HOUR'], $list))
			{
				$list[$read['DAY_HOUR']]['CNT'] = $read['CNT'];
				$list[$read['DAY_HOUR']]['CNT_DISPLAY'] = self::formatNumber($read['CNT'], 0);
			}
		}

		if ($step > 1)
		{
			for ($i = 0; $i < 24; $i+=$step)
			{
				for ($j = 1; $j < $step; $j++)
				{
					$list[$i]['CNT'] += $list[$i + $j]['CNT'];
					unset($list[$i + $j]);
				}
				$list[$i]['CNT_DISPLAY'] = self::formatNumber($list[$i]['CNT'], 0);
			}
		}

		$list = array_values($list);

		return $list;
	}

	/**
	 * Return recommended sending time for mailing.
	 *
	 * @param integer $chainId Chain Id.
	 * @return string
	 */
	public function getRecommendedSendTime($chainId = null)
	{
		$timeList = $this->getReadingByDayTime(1);
		$len = count($timeList);
		$weightList = array();
		for ($i = 0; $i <= $len; $i++)
		{
			$j = $i + 1;
			if ($j > $len)
			{
				$j = 0;
			}
			else if ($j < 0)
			{
				$j = 23;
			}
			$weight = $timeList[$i]['CNT'] + $timeList[$j]['CNT'];
			$weightList[$i] = $weight;
		}

		$deliveryTime = 0;
		if ($chainId)
		{
			$listDb = PostingTable::getList(array(
				'select' => array('COUNT_SEND_ALL'),
				'filter' => array(
					'=MAILING_CHAIN_ID' => $chainId
				),
				'order' => array('DATE_CREATE' => 'DESC'),
			));
			if ($item = $listDb->fetch())
			{
				$deliveryTime = intval($item['COUNT_SEND_ALL']  * 1/10 * 1/3600);
			}
		}
		if ($deliveryTime <= 0)
		{
			$deliveryTime = 1;
		}

		arsort($weightList);
		foreach ($weightList as $i => $weight)
		{
			$i -= $deliveryTime;
			if ($i >= $len)
			{
				$i = $i - $len;
			}
			else if ($i < 0)
			{
				$i = $len + $i;
			}
			$timeList[$i]['DELIVERY_TIME'] = $deliveryTime;
			return $timeList[$i];
		}

		return null;
	}

	/**
	 * Return chain list.
	 *
	 * @param integer $limit Limit.
	 * @return array
	 */
	public function getChainList($limit = 20)
	{
		$filter = $this->getMappedFilter();
		$listDb = PostingTable::getList(array(
			'select' => array(
				'MAX_DATE_SENT',
				'CHAIN_ID' => 'MAILING_CHAIN_ID',
				'TITLE' => 'MAILING_CHAIN.TITLE',
				'MAILING_ID',
				'MAILING_NAME' => 'MAILING.NAME',
			),
			'filter' => $filter,
			'runtime' => array(
				new ExpressionField('MAX_DATE_SENT', 'MAX(%s)', 'DATE_SENT'),
			),
			//'group' => array('CHAIN_ID', 'TITLE', 'SUBJECT', 'MAILING_ID', 'MAILING_NAME'),
			'order' => array('MAX_DATE_SENT' => 'DESC'),
			'limit' => $limit,
			'cache' => array('ttl' => $this->getCacheTtl(), 'cache_joins' => true)
		));
		$list = array();
		while ($item = $listDb->fetch())
		{
			$dateSentFormatted = '';
			if ($item['MAX_DATE_SENT'])
			{
				$dateSentFormatted = \FormatDate('x', $item['MAX_DATE_SENT']->getTimestamp());
			}

			$list[] = array(
				'ID' => $item['CHAIN_ID'],
				'NAME' => $item['TITLE'] ? $item['TITLE'] : $item['SUBJECT'],
				'MAILING_ID' => $item['MAILING_ID'],
				'MAILING_NAME' => $item['MAILING_NAME'],
				'DATE_SENT' => (string) $item['MAX_DATE_SENT'],
				'DATE_SENT_FORMATTED' => $dateSentFormatted,
			);
		}
		return $list;
	}

	/**
	 * Return global filter data.
	 *
	 * @return array
	 */
	public function getGlobalFilterData()
	{
		$period = $this->getFilter()->get('period');

		return array(
			array(
				'name' => 'authorId',
				'value' => $this->getFilter()->get('authorId'),
				'list' => $this->getAuthorList(),
			),
			array(
				'name' => 'period',
				'value' => $period ? $period : Filter::PERIOD_MONTH,
				'list' => $this->getPeriodList(),
			)
		);
	}

	/**
	 * Return period list.
	 *
	 * @return array
	 */
	protected function getPeriodList()
	{
		$list = array(
			Filter::PERIOD_WEEK,
			Filter::PERIOD_MONTH,
			Filter::PERIOD_MONTH_3,
			Filter::PERIOD_MONTH_6,
			Filter::PERIOD_MONTH_12
		);

		$result = array();
		foreach ($list as $period)
		{
			$result[] = array(
				'ID' => $period,
				'NAME' => Loc::getMessage('SENDER_STAT_STATISTICS_FILTER_PERIOD_' . $period)
			);
		}

		return $result;
	}

	/**
	 * Return author list.
	 *
	 * @return array
	 */
	protected function getAuthorList()
	{
		$listDb = MailingChainTable::getList(array(
			'select' => ['CREATED_BY', 'MAX_DATE_INSERT'],
			'group' => ['CREATED_BY'],
			'runtime' => [new ExpressionField('MAX_DATE_INSERT', 'MAX(%s)', 'DATE_INSERT'),],
			'limit' => 100,
			'order' => ['MAX_DATE_INSERT' => 'DESC'],
			'cache' => ['ttl' => $this->getCacheTtl(), 'cache_joins' => true]
		));
		$userList = array();
		while ($item = $listDb->fetch())
		{
			if (!$item['CREATED_BY'])
			{
				continue;
			}

			$userList[] = $item['CREATED_BY'];
		}

		$list = array();
		$list[] = array(
			'ID' => 'all',
			'NAME' => Loc::getMessage('SENDER_STAT_STATISTICS_FILTER_AUTHOR_FROM_ALL')
		);

		/** @var CUser */
		global $USER;
		if (is_object($USER) && $USER->getID())
		{
			$list[] = array(
				'ID' => $USER->getID(),
				'NAME' => Loc::getMessage('SENDER_STAT_STATISTICS_FILTER_AUTHOR_FROM_ME')
			);
		}

		$listDb = UserTable::getList(array(
			'select' => array(
				'ID',
				'TITLE',
				'NAME',
				'SECOND_NAME',
				'LAST_NAME',
				'LOGIN',
			),
			'filter' => array('=ID' => $userList),
			'order' => array('NAME' => 'ASC')
		));
		while ($item = $listDb->fetch())
		{
			$name = \CUser::formatName(\CSite::getNameFormat(true), $item, true, true);
			$list[] = array(
				'ID' => $item['ID'],
				'NAME' => $name,
			);
		}

		return $list;
	}
}
