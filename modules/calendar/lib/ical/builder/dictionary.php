<?php


namespace Bitrix\Calendar\ICal\Builder;


class Dictionary
{
	public const ATTENDEE_STATUS = [
		'needs_action' => 'NEEDS-ACTION',
		'Q' => 'NEEDS-ACTION',
		'accepted' => 'ACCEPTED',
		'Y' => 'ACCEPTED',
		'H' => 'ACCEPTED',
		'declined' => 'DECLINED',
		'N' => 'DECLINED',
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
		'absent' => 'OPAQUE',
		'quest' => 'OPAQUE',
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
