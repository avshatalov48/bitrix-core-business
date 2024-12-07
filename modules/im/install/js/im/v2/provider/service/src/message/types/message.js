import type {
	RawCopilot,
	RawFile,
	RawMessage,
	RawCommentInfo,
	RawUser,
	RawReaction,
	RawShortUser,
	RawTariffRestrictions,
} from '../../types/rest';

export type PaginationRestResult = {
	chat_id: number,
	files: RawFile[],
	messages: RawMessage[],
	commentInfo: RawCommentInfo[],
	additionalMessages: RawMessage[],
	users: RawUser[],
	usersShort: RawShortUser[],
	reactions: RawReaction[],
	hasPrevPage?: boolean,
	hasNextPage?: boolean,
	copilot: RawCopilot,
	tariffRestrictions: RawTariffRestrictions,
};
