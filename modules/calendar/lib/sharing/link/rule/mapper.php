<?php

namespace Bitrix\Calendar\Sharing\Link\Rule;

use Bitrix\Calendar\Internals\SharingLinkRuleTable;
use Bitrix\Calendar\Internals\SharingObjectRuleTable;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;

class Mapper
{
	protected const DEFAULT_SLOT_SIZE = 60;
	protected const DEFAULT_FROM = 540;
	protected const DEFAULT_TO = 1080;
	protected const DEFAULT_WORKDAYS = [1, 2, 3, 4, 5];

	public function convertToArray(?Rule $rule = null): array
	{
		if (is_null($rule))
		{
			$rule = new Rule();
		}

		$slotSize = $rule->getSlotSize() ?? self::DEFAULT_SLOT_SIZE;

		$ranges = $this->getSortedRanges($rule->getRanges() ?? []);
		$rangeArrays = [];
		foreach ($ranges as $range)
		{
			$weekdays = $range->getWeekdays() ?? $this->getWorkdays();
			$rangeArrays[] = [
				'from' => $range->getFrom() ?? $this->getDefaultFrom(),
				'to' => $range->getTo() ?? $this->getDefaultTo(),
				'weekdays' => $weekdays,
				'weekdaysTitle' => $this->getWeekdaysTitle($weekdays),
			];
		}
		if (empty($rangeArrays))
		{
			$weekdays = $this->getWorkdays();
			$rangeArrays = [
				[
					'from' => $this->getDefaultFrom(),
					'to' => $this->getDefaultTo(),
					'weekdays' => $weekdays,
					'weekdaysTitle' => $this->getWeekdaysTitle($weekdays),
				],
			];
		}

		return [
			'slotSize' => $slotSize,
			'ranges' => $rangeArrays,
		];
	}

	public function buildRuleFromArray(array $ruleArray): Rule
	{
		$slotSize = $ruleArray['slotSize'] ?? self::DEFAULT_SLOT_SIZE;

		$rangeArrays = $ruleArray['ranges'] ?? [];
		$ranges = [];
		foreach ($rangeArrays as $rangeArray)
		{
			$from = $rangeArray['from'] ?? $this->getDefaultFrom();
			$to = $rangeArray['to'] ?? $this->getDefaultTo();
			$weekdays = $rangeArray['weekdays'] ?? $this->getWorkdays();
			$ranges[] = (new Range())
				->setFrom($from)
				->setTo($to)
				->setWeekdays($weekdays);
		}
		if (empty($ranges))
		{
			$ranges = [
				(new Range())
					->setFrom($this->getDefaultFrom())
					->setTo($this->getDefaultTo())
					->setWeekdays($this->getWorkdays())
			];
		}

		return (new Rule())
			->setSlotSize($slotSize)
			->setRanges($this->getSortedRanges($ranges));
	}

	public function getFromLinkObjectRule(LinkObjectRule $objectRule): Rule
	{
		$ruleEOCollection = null;

		if (is_int($objectRule->getLinkId()))
		{
			$ruleEOCollection = $this->getRuleEOByLinkId($objectRule->getLinkId());
		}

		if ($ruleEOCollection === null || empty($ruleEOCollection->getAll()))
		{
			$ruleEOCollection = $this->getRuleEOByObject($objectRule->getObjectId(), $objectRule->getObjectType());
		}

		return $this->convertToObject($ruleEOCollection);
	}

	protected function getRuleEOByLinkId(int $linkId): ?Main\ORM\Objectify\Collection
	{
		return SharingLinkRuleTable::query()
			->setSelect(['*'])
			->where('LINK_ID', $linkId)
			->exec()->fetchCollection();
	}

	protected function getRuleEOByObject(int $objectId, string $objectType): ?Main\ORM\Objectify\Collection
	{
		return SharingObjectRuleTable::query()
			->setSelect(['*'])
			->where('OBJECT_ID', $objectId)
			->where('OBJECT_TYPE', $objectType)
			->exec()->fetchCollection();
	}

	protected function convertToObject($ruleEOCollection): Rule
	{
		if (!empty($ruleEOCollection->getAll()))
		{
			$slotSize = $ruleEOCollection->getAll()[0]->getSlotSize();
			$ranges = array_map(static fn($ruleEO) => (new Range())
				->setWeekdays(array_map('intval', explode(',', $ruleEO->getWeekdays())))
				->setFrom($ruleEO->getTimeFrom())
				->setTo($ruleEO->getTimeTo())
			, $ruleEOCollection->getAll());

			return (new Rule())
				->setSlotSize($slotSize)
				->setRanges($ranges)
			;
		}

		$slotSize = self::DEFAULT_SLOT_SIZE;
		$ranges = [
			(new Range())
				->setFrom($this->getDefaultFrom())
				->setTo($this->getDefaultTo())
				->setWeekdays($this->getWorkdays())
		];

		return (new Rule())
			->setSlotSize($slotSize)
			->setRanges($this->getSortedRanges($ranges));
	}

	public function saveForLinkObject(Rule $rule, LinkObjectRule $linkObjectRule): void
	{
		$rows = array_map(static fn($range) => [
			'LINK_ID' => $linkObjectRule->getLinkId(),
			'SLOT_SIZE' => $rule->getSlotSize(),
			'WEEKDAYS' => implode(',', $range->getWeekdays()),
			'TIME_FROM' => $range->getFrom(),
			'TIME_TO' => $range->getTo(),
		], $rule->getRanges());
		SharingLinkRuleTable::deleteByFilter([
			'LINK_ID' => $linkObjectRule->getLinkId(),
		]);
		SharingLinkRuleTable::addMulti($rows, true);

		$rows = array_map(static fn($range) => [
			'OBJECT_ID' => $linkObjectRule->getObjectId(),
			'OBJECT_TYPE' => $linkObjectRule->getObjectType(),
			'SLOT_SIZE' => $rule->getSlotSize(),
			'WEEKDAYS' => implode(',', $range->getWeekdays()),
			'TIME_FROM' => $range->getFrom(),
			'TIME_TO' => $range->getTo(),
		], $rule->getRanges());
		SharingObjectRuleTable::deleteByFilter([
			'OBJECT_ID' => $linkObjectRule->getObjectId(),
			'OBJECT_TYPE' => $linkObjectRule->getObjectType(),
		]);
		SharingObjectRuleTable::addMulti($rows, true);
	}

	public function deleteLinkRule(int $linkId): void
	{
		SharingLinkRuleTable::deleteByFilter([
			'LINK_ID' => $linkId,
		]);
	}

	public function getChanges(?Rule $rule): array
	{
		$defaultRule = $this->convertToArray();
		$ruleArray = $this->convertToArray($rule);

		$changes = [
			'customDays' => 'N',
			'customLength' => 'N',
		];

		if ($defaultRule['slotSize'] !== $ruleArray['slotSize'])
		{
			$changes['customLength'] = 'Y';
		}

		if ($defaultRule['ranges'] !== $ruleArray['ranges'])
		{
			$changes['customDays'] = 'Y';
		}

		return $changes;
	}

	protected function getDefaultFrom(): int
	{
		$settings = \CCalendar::GetSettings();
		if (!isset($settings['work_time_start']))
		{
			return self::DEFAULT_FROM;
		}

		return $this->getMinutesFromTimeString($settings['work_time_start']);
	}

	protected function getDefaultTo(): int
	{
		$settings = \CCalendar::GetSettings();
		if (!isset($settings['work_time_end']))
		{
			return self::DEFAULT_TO;
		}

		return $this->getMinutesFromTimeString($settings['work_time_end']);
	}

	protected function getMinutesFromTimeString(string $timeString): int
	{
		$time = strtotime(str_replace('.', ':', "$timeString:00"));
		return (int)date('H', $time) * 60 + (int)date('i', $time);
	}

	/**
	 * @param array<Range> $ranges
	 * @return array<Range>
	 */
	protected function getSortedRanges(array $ranges): array
	{
		usort($ranges, fn($a, $b) => $this->compareRanges($a, $b));

		return $ranges;
	}
	
	/**
	 * @param Range $range1
	 * @param Range $range2
	 * @return int
	 */
	protected function compareRanges(Range $range1, Range $range2): int
	{
		$weekdaysWeight1 = $this->getWeekdaysWeight($range1->getWeekdays());
		$weekdaysWeight2 = $this->getWeekdaysWeight($range2->getWeekdays());

		if ($weekdaysWeight1 !== $weekdaysWeight2)
		{
		return $weekdaysWeight1 - $weekdaysWeight2;
		}

		if ($range1->getFrom() !== $range2->getFrom())
		{
			return $range1->getFrom() - $range2->getFrom();
		}

		return $range1->getTo() - $range2->getTo();
	}
	
	/**
	 * @param array $weekdays
	 * @return int
	 */
	protected function getWeekdaysWeight(array $weekdays): int
	{
		$weekStart = $this->getWeekStart();
		$mappedWeekdays = array_map(static fn($w) => $w < $weekStart ? $w + 10 : $w, $weekdays);
		sort($mappedWeekdays);

		$weight = 0;
		foreach ($mappedWeekdays as $index => $w)
		{
			$weight += $w * 10 ** (10 - $index);
		}

		return $weight;
	}
	
	/**
	 * @param array $weekdays
	 * @return string
	 */
	protected function getWeekdaysTitle(array $weekdays): string
	{
		$workdays = $this->getWorkdays();
		sort($weekdays);
		sort($workdays);
		if (implode(',', $weekdays) === implode(',', $workdays))
		{
			return Loc::getMessage('CALENDAR_SHARING_WORKDAYS_MSGVER_1');
		}

		return $this->formatWeekdays($weekdays);
	}
	
	/**
	 * @param array $weekdays
	 * @return string
	 */
	protected function formatWeekdays(array $weekdays): string
	{
		$weekdaysLoc = $this->getWeekdaysLoc();
		$weekdays = array_map(static fn($weekday) => $weekdaysLoc[$weekday], $this->getSortedWeekdays($weekdays));

		return implode(', ', $weekdays);
	}
	
	/**
	 * @param array $weekdays
	 * @return array
	 */
	protected function getSortedWeekdays(array $weekdays): array
	{
		$weekStart = $this->getWeekStart();
		$mappedWeekdays = array_map(static fn($w) => $w < $weekStart ? $w + 10 : $w, $weekdays);
		usort($mappedWeekdays, static fn($a, $b) => $a - $b);

		return array_map(static fn($w) => $w % 10, $mappedWeekdays);
	}
	
	/**
	 * @return int[]
	 */
	protected function getWorkdays(): array
	{
		$settings = \CCalendar::GetSettings();
		if (empty($settings['week_holidays']))
		{
			return self::DEFAULT_WORKDAYS;
		}

		$holidays = array_map(static function(string $weekday) {
			return \CCalendar::IndByWeekDay($weekday);
		}, $settings['week_holidays']);

		return array_values(array_diff([0, 1, 2, 3, 4, 5, 6], $holidays));
	}
	
	/**
	 * @return int
	 */
	protected function getWeekStart(): int
	{
		$weekStart = \CCalendar::GetWeekStart();

		return \CCalendar::IndByWeekDay($weekStart);
	}
	
	/**
	 * @return array
	 */
	protected function getWeekdaysLoc(): array
	{
		$dayLength = 60 * 60 * 24;
		$now = time();
		$dayOfWeek = FormatDate('w', $now);
		$sunday = $now - $dayOfWeek * $dayLength;
		$weekdays = [0, 1, 2, 3, 4, 5, 6];

		return array_map(static fn($weekday) => FormatDate('D', $sunday + $weekday * $dayLength), $weekdays);
	}
}