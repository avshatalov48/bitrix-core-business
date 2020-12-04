<?php


namespace Bitrix\Calendar\ICal\Basic;


class Dictionary
{
	const LOCAL_ATTENDEES_STATUS = [
		'NEEDS-ACTION' => 'Q',
		'ACCEPTED' => 'Y',
		'DECLINED' => 'N',
		'TENTATIVE' => 'Y',
		'DELEGATED' => 'N',
	];

	const OUT_ATTENDEES_STATUS = [
		'needs_action' => 'NEEDS-ACTION',
		'accepted' => 'ACCEPTED',
		'declined' => 'DECLINED',
		'tentative' => 'TENTATIVE',
		'delegated' => 'DELEGATED',
	];

	const INVITATION_STATUS = [
		'tentative' => 'TENTATIVE',
		'confirmed' => 'CONFIRMED',
		'cancelled' => 'CANCELLED',
	];

	const EVENT_STATUS = [
		'public' => 'PUBLIC',
		'private' => 'PRIVATE',
		'confidential' => 'CONFIDENTIAL',
	];

	const METHODS = [
		'publish' => 'PUBLISH',
		'request' => 'REQUEST',
		'refresh' => 'REFRESH',
		'cancel' => 'CANCEL',
		'add' => 'ADD',
		'reply' => 'REPLY',
		'counter' => 'COUNTER',
		'declinecounter' => 'DECLINECOUNTER',
		'edit' => 'REQUEST',
	];

	const TRANSPARENT = [
		'free' => 'TRANSPARENT',
		'busy' => 'OPAQUE',
	];

	const COMPONENTS = [
		'vevent' => 'VEVENT',
		'vcalendar' => 'VCALENDAR',
		'vtimezone' => 'VTIMEZONE',
		'vjournal' => 'VJOURNAL',
		'vtodo' => 'VTODO'
	];

	const INSTANCE_FIELDS = [
		'exdate' => 'EXDATE',
		'rdate' => 'RDATE'
	];
}