<?php


namespace Bitrix\Calendar\ICal\Builder;


class Dictionary
{
	public const ATTENDEE_STATUS = [
		'needs_action' => 'NEEDS-ACTION',
		'Q' => 'NEEDS-ACTION',
		'accepted' => 'ACCEPTED',
		'Y' => 'ACCEPTED',
		'declined' => 'DECLINED',
		'tentative' => 'TENTATIVE',
		'delegated' => 'DELEGATED',
	];

	public const ATTENDEE_ROLE = [
		'CHAIR' => 'CHAIR',
		'REQ_PARTICIPANT' => 'REQ-PARTICIPANT',
		'OPT_PARTICIPANT' => 'OPT-PARTICIPANT',
		'NON_PARTICIPANT' => 'NON-PARTICIPANT',
	];

	public const TRANSPARENT =[
		'free' => 'TRANSPARENT',
		'busy' => 'OPAQUE',
	];

	public const EVENT_STATUS = [
		'tentative' => 'TENTATIVE',
		'confirmed' => 'CONFIRMED',
		'cancelled' => 'CANCELLED',
	];

	public const ATTENDEE_CUTYPE = [
		'individual' => 'INDIVIDUAL',
		'group' => 'GROUP',
		'resource' => 'RESOURCE',
		'room' => 'ROOM',
		'unknown' => 'UNKNOWN',
		'xname' => 'x-name',
		'iana-token' => 'iana-token',
	];
}
