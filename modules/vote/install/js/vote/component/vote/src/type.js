type QuestionId = string; // but number value
type AnswerId = string; // but number value

export type VoteInit = {
	anonymity: number;
	options: number;
	questions: Record<QuestionId, VoteInitQuestion>;
};

export type VoteInitQuestion = {
	id: number;
	question: string;
	fieldType: number;
	answers: Record<AnswerId, VoteInitAnswer>;
};

export type VoteInitAnswer = {
	id: number;
	message: string;
};

export type FormattedQuestionType ={
	id: number,
	question: string,
	fieldType: number,
	totalCounter: number,
	isMultiple: boolean,
	answers: {
		[AnswerId]: {
			id: number,
			message: string,
			reaction: string,
		}
	},
};

export type AnswersSelectedEvent = {
	questionId: number;
	answerIds: number[];
};

export type FormattedAnswersType = {
	[AnswerId]: {
		id: number,
		message: string,
		reaction: string,
		counter: number,
		percent: number,
	},
};
