export type VoteElementState = {
	id: number,
	questions: number[],
	isAnonymous: boolean,
	isVoted: boolean,
	isLoading: boolean,
	canEdit: boolean,
	canVote: boolean,
	canRevoke: boolean,
	isCompleted: boolean,
	resultUrl: string,
}

export type QuestionElementState = {
	id: number,
	text: string,
	answers: number[],
	isMultiple: boolean,
	totalCounter: number,
}

export type AnswerElementState = {
	id: number,
	text: string,
	counter: number,
	percent: number,
}

export type CurrentUserVotesType = Record<number, number[]>;
export type VoteCollectionType = Record<number, VoteElementState>;
export type QuestionCollectionType = Record<number, QuestionElementState>;
export type AnswerCollectionType = Record<number, AnswerElementState>;
