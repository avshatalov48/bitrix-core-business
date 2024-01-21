<?php


namespace Bitrix\Calendar\ICal\Basic;


class RecurrenceRuleProperty
{
	public $count;
	public $until;
	public $interval;
	public $freq;
	public $day;
	public $exdate;
	public $rdate;

	public function __construct($params = [])
	{
		$this->count = $params['COUNT'] ?? null;
		$this->until = $params['UNTIL'] ?? null;
		$this->interval = $params['INTERVAL'] ?? null;
		$this->freq = $params['FREQ'] ?? null;
		$this->day = $params['BYDAY'] ?? null;
		$this->exdate = $params['EXDATE'] ?? null;
		$this->rdate = $params['RDATE'] ?? null;
	}
}