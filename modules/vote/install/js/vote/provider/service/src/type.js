export type BackendVote = {
	ID: number;
	COUNTER: number;
	QUESTIONS: Record<string, BackendVoteQuestion>;
	userAnswerMap: Record<QuestionId, Record<AnswerId, BackendUserAnswer>>; // userAnswerMap[questionId][answerId]
	canEdit: boolean;
	canVote: boolean;
	canRevote: boolean;
	isVoted: boolean;
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