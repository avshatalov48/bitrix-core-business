import { Utils } from 'im.v2.lib.utils';

let nextChatId = Number.MIN_SAFE_INTEGER;
let nextUserId = Number.MIN_SAFE_INTEGER;
let nextMessageId = Number.MIN_SAFE_INTEGER;
let nextFileId = Number.MIN_SAFE_INTEGER;

export const IdGenerator = {
	getDialogId(): string
	{
		return Utils.text.getUuidV4();
	},

	getNextChatId(): number
	{
		nextChatId++;

		return nextChatId;
	},

	getNextUserId(): number
	{
		nextUserId++;

		return nextUserId;
	},

	getNextMessageId(): number
	{
		nextMessageId++;

		return nextMessageId;
	},

	getNextFileId(): number
	{
		nextFileId++;

		return nextFileId;
	},
};
