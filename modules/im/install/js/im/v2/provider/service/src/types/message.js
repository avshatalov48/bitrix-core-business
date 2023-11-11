import type { RawFile, RawMessage, RawUser, RawReaction, RawShortUser } from './rest';

export type PaginationRestResult = {
	chat_id: number,
	files: RawFile[],
	messages: RawMessage[],
	additionalMessages: RawMessage[],
	users: RawUser[],
	usersShort: RawShortUser[],
	reactions: RawReaction[],
	hasPrevPage?: boolean,
	hasNextPage?: boolean
};
