import type {
	RawCopilot,
	RawFile,
	RawMessage,
	RawCommentInfo,
	RawUser,
	RawReaction,
	RawShortUser,
	RawTariffRestrictions,
} from 'im.v2.provider.service.types';

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

export type TranscriptionResponse = {
	fileId: number,
	status: 'Success' | 'Pending' | 'Error',
	transcriptText: string | null,
};
