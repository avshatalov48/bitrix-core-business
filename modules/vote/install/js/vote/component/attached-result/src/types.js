export type VoteAttachedResultOptions = {
	votedPageSize: number;
};

type QuestionId = number;
type AnswerId = number;

export type BackendVote = {
	ID: number;
	COUNTER: number;
	QUESTIONS: Record<string, BackendVoteQuestion>;
	userAnswerMap: Record<QuestionId, Record<AnswerId, BackendUserAnswer>>; // userAnswerMap[questionId][answerId]
	canEdit: boolean;
	canVote: boolean;
	canRevote: boolean;
	isVoted: boolean;
	ANONYMITY: number;
	downloadUrl: string;
	signedAttachId: string;
};

export type BackendVoteQuestion = {
	ID: number;
	QUESTION: string;
	COUNTER: number;
	ANSWERS: Record<AnswerId, BackendVoteAnswer>;
};

export type BackendVoteAnswer = {
	ID: number;
	COUNTER: number;
	MESSAGE: string;
	PERCENT: number;
};

export type BackendUserAnswer = {
	EVENT_ID: string; // but number value
	EVENT_QUESTION_ID: string; // but number value
	ANSWER_ID: string; // but number value
};

export type BackendResultAll = {
	attach: BackendVote,
	voted: Record<AnswerId, BackendVotedUser[]>,
};

export type BackendVotedUser = {
	ID: number;
	NAME: string;
	IMAGE: string | null;
	WORK_POSITION: string | null;
};

export type CurrentVoteType = {
	id: number;
	isAnonymousVote: boolean;
	isVoted: boolean;
	signedAttachId: string;
	downloadUrl: string;
	canRevoke: boolean;
	isFinished: boolean;
}

export type QuestionsType = {
	[QuestionId]: {
		id: number;
		counter: number;
		text: string;
		answers: {
			[AnswerId]: {
				id: number;
				percent: number;
				text: string;
				counter: number;
			};
		};
	};
}

export type CurrentUserVotesType = {
	[QuestionId]: number[];
}
