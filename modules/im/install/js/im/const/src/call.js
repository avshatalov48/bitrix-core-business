/**
 * Bitrix Messenger
 * Call constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

export const CallLimit = Object.freeze({
	userLimitForHd: 5
});

export const CallStateType = Object.freeze({
	preparation: 'preparation',
	call: 'call'
});

export const CallErrorCode = Object.freeze({
	noSignalFromCamera: 'noSignalFromCamera'
});

export const CallApplicationErrorCode = Object.freeze({
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
	userLeftCall: 'userLeftCall'
});

export const ConferenceRightPanelMode = Object.freeze({
	hidden: 'hidden',
	chat: 'chat',
	users: 'users',
	split: 'split'
});