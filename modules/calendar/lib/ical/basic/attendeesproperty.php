<?php

namespace Bitrix\Calendar\ICal\Basic;


class AttendeesProperty
{
	public $email;
	public $name = null;
	public $participationStatus = null;
	public $role = null;
	public $cutype = null;
	public $mailto;
	public $rsvp = true;

	public function __construct(
		string $email = null,
		string $name = null,
		string $participationStatus = null,
		string $role = null,
		string $cutype = null,
		string $mailto = null,
		bool $rsvp = true
	)
	{
		$this->email = $email;
		$this->name = $name;
		$this->participationStatus = $participationStatus;
		$this->role = $role;
		$this->cutype = $cutype;
		$this->mailto = $mailto;
		$this->rsvp = $rsvp;
	}
}
