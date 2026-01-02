import { type Store } from 'ui.vue3.vuex';

type VotingParams = {
	VOTE_ID: number,
	AUTHOR_ID: number, // user who voted
	COUNTER: number | string,
	QUESTIONS: Questions,
};

type Questions = {
	[string: questionId]: {
		ANSWERS: {
			[string: answerId]: AnswerData
		}
	},
};

type UserVotingParams = {
	VOTE_ID: number,
	AUTHOR_ID: number,
	COUNTER: number | string,
	QUESTIONS: Questions,
	userAnswerMap: userVotes,
};

type userVotes = {
	[string: questionId]: {
		[string: answerId]: {
			ANSWER_ID: string,
			EVENT_ID: string,
			EVENT_QUESTION_ID: string,
			ID: string,
			MESSAGE: string
		}
	},
};

type AnswerData = {
	PERCENT: number,
	USERS: [], // always empty
	COUNTER: number | string,
};

export class VotePullHandler
{
	#store: Store;

	constructor(store: Store)
	{
		this.#store = store;
	}

	getModuleId(): string
	{
		return 'vote';
	}

	handleVoting(params: VotingParams): void
	{
		this.#updateQuestionTotalCounter(params);
		this.#updateAnswer(params);
	}

	#updateQuestionTotalCounter(params: VotingParams): void
	{
		const { QUESTIONS: questions, COUNTER: totalCounter } = params;

		Object.keys(questions).forEach((questionId) => {
			this.#store.store.dispatch('vote/updateQuestionTotalCounter', { questionId, totalCounter });
		});
	}

	#updateAnswer(params: VotingParams): void
	{
		const { QUESTIONS: questions } = params;

		Object.values(questions).forEach((questionData) => {
			Object.entries(questionData.ANSWERS).forEach(([answerId, answerData]) => {
				this.#store.store.dispatch('vote/updateAnswer', {
					answerId,
					counter: answerData.COUNTER,
					percent: answerData.PERCENT,
				});
			});
		});
	}

	handleUser_vote(params: UserVotingParams): void
	{
		this.updateCurrentUserVotes(params);
	}

	updateCurrentUserVotes(params: UserVotingParams): void
	{
		const { VOTE_ID: voteId, QUESTIONS: questions, userAnswerMap: currentUserVotes } = params;

		if (Object.keys(currentUserVotes).length === 0)
		{
			this.#store.store.dispatch('vote/resetUserVoted', {
				voteId,
			});

			Object.keys(questions).forEach((questionId) => {
				this.#store.store.dispatch('vote/clearVotes', {
					questionId,
					voteId,
				});
			});

			return;
		}

		Object.entries(currentUserVotes).forEach(([questionId, answers]) => {
			const answerIds = Object.keys(answers).map(Number);

			this.#store.store.dispatch('vote/updateCurrentUserVotes', {
				questionId,
				answerIds,
			});
		});

		this.#store.store.dispatch('vote/setUserVoted', {
			voteId,
		});
	}

	handleStop(params: { voteId: number }): void
	{
		this.#store.store.dispatch('vote/setVoteCompleted', {
			voteId: params.voteId,
		});
	}
}
