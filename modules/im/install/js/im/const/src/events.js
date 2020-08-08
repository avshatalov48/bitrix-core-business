/**
 * Bitrix Messenger
 * Event names constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

export const EventType = Object.freeze({
	dialog:
	{
		scrollToBottom: 'EventType.dialog.scrollToBottom',
		requestHistoryResult: 'EventType.dialog.requestHistoryResult',
		requestUnreadResult: 'EventType.dialog.requestUnreadResult',
		sendReadMessages: 'EventType.dialog.sendReadMessages',
	},
	textarea:
	{
		insertText: 'EventType.textarea.insertText',
		focus: 'EventType.textarea.focus',
		blur: 'EventType.textarea.blur',
	}
});