/**
 * Bitrix Messenger
 * Common constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

export const MutationType = Object.freeze({
	none: 'none',
	add: 'delete',
	update: 'update',
	delete: 'delete',
	set: 'set',
	setAfter: 'after',
	setBefore: 'before',
});

export const StorageLimit = Object.freeze({
	dialogues: 50,
	messages: 100,
});