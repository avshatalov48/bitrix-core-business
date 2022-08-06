/**
 * Bitrix Messenger
 * Conference constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

export const ConferenceFieldState = Object.freeze({
	view: 'view',
	edit: 'edit',
	create: 'create'
});

export const ConferenceStateType = Object.freeze({
	preparation: 'preparation',
	call: 'call'
});

export const ConferenceErrorCode = Object.freeze({
	userLimitReached: 'userLimitReached',
	detectIntranetUser: 'detectIntranetUser',
	bitrix24only: 'bitrix24only',
	kickedFromCall: 'kickedFromCall',
	unsupportedBrowser: 'unsupportedBrowser',
	missingMicrophone: 'missingMicrophone',
	unsafeConnection: 'unsafeConnection',
	wrongAlias: 'wrongAlias',
	notStarted: 'notStarted',
	finished: 'finished',
	userLeftCall: 'userLeftCall',
	noSignalFromCamera: 'noSignalFromCamera'
});

export const ConferenceRightPanelMode = Object.freeze({
	hidden: 'hidden',
	chat: 'chat',
	users: 'users',
	split: 'split'
});

//BX.Call.UserState sync
export const ConferenceUserState = Object.freeze({
	Idle: 'Idle',
	Busy: 'Busy',
	Calling: 'Calling',
	Unavailable: 'Unavailable',
	Declined: 'Declined',
	Ready: 'Ready',
	Connecting: 'Connecting',
	Connected: 'Connected',
	Failed: 'Failed'
});