/**
 * Bitrix Messenger
 * File constants
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

export const FileStatus = Object.freeze({
	upload: 'upload',
	wait: 'wait',
	progress: 'progress',
	done: 'done',
	error: 'error',
});

export const FileType = Object.freeze({
	image: 'image',
	video: 'video',
	audio: 'audio',
	file: 'file',
});

export const FileIconType = Object.freeze({
	file: 'file',
	image: 'image',
	audio: 'audio',
	video: 'video',
	code: 'code',
	call: 'call',
	attach: 'attach',
	quote: 'quote;'
});