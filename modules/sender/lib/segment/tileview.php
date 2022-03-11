<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Segment;

use Bitrix\Main\Localization\Loc;
use Bitrix\Sender\GroupConnectorTable;
use Bitrix\Sender\GroupTable;
use Bitrix\Sender\Entity;
use Bitrix\Sender\Dispatch;
use Bitrix\Sender\Integration\Sender\Connectors\Contact;
use Bitrix\Sender\Message;

Loc::loadMessages(__FILE__);

/**
 * Class TileView
 * @package Bitrix\Sender\Entity
 * @internal
 */
class TileView
{
	const MAX_COUNT = 4;

	/** @var bool $isInclude Is include. */
	protected $isInclude = true;

	/** @var Message\Adapter $message Message. */
	protected $message;

	/**
	 * Create instance.
	 *
	 * @param bool $isInclude Get stat by including or excluding segments in letters.
	 * @return static
	 */
	public static function create($isInclude = true)
	{
		return new static($isInclude);
	}

	/**
	 * Constructor.
	 *
	 * @param bool $isInclude Get stat by including or excluding segments in letters.
	 */
	public function __construct($isInclude = true)
	{
		$this->isInclude = $isInclude;
	}

	/**
	 * Set message.
	 *
	 * @param Message\Adapter|null $message Message.
	 *
	 * @return $this
	 */
	public function setMessage(Message\Adapter $message = null)
	{
		$this->message = $message;
		return $this;
	}

	/**
	 * Get segments as tiles array.
	 *
	 * @return array
	 */
	public function getSections()
	{
		$list = array(
			'last' => array(
				'id' => 'last',
				'name' => Loc::getMessage('SENDER_SEGMENT_TILEVIEW_SECTION_LAST'),
				'items' => array()
			),
			'freq' => array(
				'id' => 'freq',
				'name' => Loc::getMessage('SENDER_SEGMENT_TILEVIEW_SECTION_FREQ'),
				'items' => array()
			),
			'system' => array(
				'id' => 'system',
				'name' => Loc::getMessage('SENDER_SEGMENT_TILEVIEW_SECTION_SYSTEM'),
				'items' => array()
			),
			'case' => array(
				'id' => 'case',
				'name' => Loc::getMessage('SENDER_SEGMENT_TILEVIEW_SECTION_CASE'),
				'items' => array()
			),
			'my' => array(
				'id' => 'my',
				'name' => Loc::getMessage('SENDER_SEGMENT_TILEVIEW_SECTION_MY'),
				'items' => array()
			),
		);
		$tiles = $this->getTiles(array('filter' => array('=HIDDEN' => false)));

		foreach ($tiles as $tile)
		{
			// set last
			if ($tile['data']['last'])
			{
				/** @var \Bitrix\Main\Type\DateTime $last */
				$last = $tile['data']['last'];
				$tile['data']['last'] = $last->getTimestamp();

				$list['last']['items'][] = $tile;
			}

			// set freq
			if ($tile['data']['freq'])
			{
				$list['freq']['items'][] = $tile;
			}

			// set cases
			if ($tile['data']['case'])
			{
				$list['case']['items'][] = $tile;
			}

			// set system or my
			$key = $tile['data']['system'] ? 'system' : 'my';
			$list[$key]['items'][] = $tile;
		}

		// sort & cut last
		usort(
			$list['last']['items'],
			function ($a, $b)
			{
				return ($a['data']['last'] > $b['data']['last']) ? -1 : 1;
			}
		);
		$list['last']['items'] = array_slice($list['last']['items'], 0, self::MAX_COUNT);

		// sort freq
		usort(
			$list['freq']['items'],
			function ($a, $b)
			{
				return ($a['data']['freq'] > $b['data']['freq']) ? -1 : 1;
			}
		);
		$list['freq']['items'] = array_slice($list['freq']['items'], 0, self::MAX_COUNT);

		// remove empty sections
		foreach ($list as $sectionId => $section)
		{
			if (count($section['items']) > 0)
			{
				continue;
			}

			unset($list[$sectionId]);
		}
		$list = array_values($list);

		return $list;
	}

	/**
	 * Get segments as tiles array.
	 *
	 * @param integer $segmentId Segment ID.
	 * @return array|null
	 */
	public function getTile($segmentId)
	{
		$tiles = $this->getTiles(array('filter' => array('=ID' => $segmentId)));
		$tile = current($tiles) ?: null;
		return $tile;
	}

	/**
	 * Get segments as tiles array.
	 *
	 * @param array $parameters Parameters.
	 * @return array
	 */
	public function getTiles(array $parameters = array())
	{
		$result = [];
		$ids = [];

		$prefix = $this->isInclude ? '' : '_EXCLUDE';
		$fieldDateUse = "DATE_USE$prefix";
		$fieldUseCount = "USE_COUNT$prefix";

		if (!isset($parameters['order']))
		{
			$parameters['order'] = array(
				'SORT' => 'ASC',
				'NAME' => 'ASC',
				$fieldDateUse => 'DESC'
			);
		}

		$segments = GroupTable::getList($parameters);

		foreach ($segments as $segment)
		{
			$item = array(
				'id' => $segment['ID'],
				'name' => $segment['NAME'],
				'data' => array(
					'last' => $segment[$fieldDateUse],
					'freq' => (int) $segment[$fieldUseCount],
					'case' => mb_substr($segment['CODE'], 0, 5) == 'case_',
					'hidden' => $segment['HIDDEN'] == 'Y',
					'system' => $segment['IS_SYSTEM'] == 'Y',
					'hasStatic' => false,
					'count' => array()
				),
			);

			$item['bgcolor'] = self::getBackgroundColor($item['data']);
			$result[] = $item;
			$ids[] = $item['id'];
		}



		if (count($ids) > 0)
		{
			$connectors = GroupConnectorTable::getList([
				'filter' => [
					'@GROUP_ID' => $ids
				]
			]);

			$hasStatic = [];
			foreach ($connectors as $connector)
			{
				if (!is_array($connector['ENDPOINT']))
				{
					$hasStatic[$connector['GROUP_ID']] = false;
					continue;
				}
				$entityConnector = \Bitrix\Sender\Connector\Manager::getConnector($connector['ENDPOINT']);
				$hasStatic[$connector['GROUP_ID']] = $entityConnector instanceof Contact && $connector['ADDRESS_COUNT'] > 0;
			}

			$duration = null;
			$messageTypes = array();
			if ($this->message)
			{
				$duration = Dispatch\DurationCountBased::create($this->message);
				$messageTypes = $this->message->getSupportedRecipientTypes();
			}

			$counters = Entity\Segment::getAddressCounters($ids);
			foreach ($result as $index => $item)
			{
				if (!isset($counters[$item['id']]))
				{
					continue;
				}

				// set count
				$item['data']['count'] = $counters[$item['id']];
				$item['data']['hasStatic'] = $hasStatic[$item['id']];

				// set duration
				foreach ($item['data']['count'] as $typeId => $count)
				{
					if (isset($item['data']['duration']) && $item['data']['duration'])
					{
						continue;
					}

					if (!$duration || !$this->message)
					{
						continue;
					}

					if (!in_array($typeId, $messageTypes))
					{
						continue;
					}

					$item['data']['duration'] = $duration->getIntervalDefault($count);
				}

				$result[$index] = $item;
			}
		}

		return $result;
	}

	protected static function getBackgroundColor(array $data)
	{
		if ($data['system'])
		{
			return null;//'#d3ffcd';
		}
		elseif ($data['hidden'])
		{
			return '#eef2f4';
		}
		else
		{
			return null;
		}
	}
}