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
		$this->count = $params['COUNT'];
		$this->until = $params['UNTIL'];
		$this->interval = $params['INTERVAL'];
		$this->freq = $params['FREQ'];
		$this->day = $params['BYDAY'];
		$this->exdate = $params['EXDATE'];
		$this->rdate = $params['RDATE'];
	}
}