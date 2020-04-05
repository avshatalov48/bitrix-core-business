/**
 * Bitrix Messenger
 * Common constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

const MutationType = Object.freeze({
	none: 'none',
	add: 'delete',
	update: 'update',
	delete: 'delete',
	set: 'set',
	setAfter: 'after',
	setBefore: 'before',
});

const StorageLimit = Object.freeze({
	dialogues: 50,
	messages: 20,
});

export {MutationType, StorageLimit};