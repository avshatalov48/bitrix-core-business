import { formatFieldsWithConfig } from 'im.v2.model';
import { voteFieldsConfig, questionFieldsConfig, answerFieldsConfig } from './format/field-config';
import type {
	VoteElementState,
	QuestionElementState,
	AnswerElementState,
	CurrentUserVotesType,
	VoteCollectionType,
	QuestionCollectionType,
	AnswerCollectionType,
} from './type';

export type {
	VoteElementState,
	QuestionElementState,
	AnswerElementState,
	CurrentUserVotesType,
	VoteCollectionType,
	QuestionCollectionType,
	AnswerCollectionType,
} from './type';

/* eslint-disable no-param-reassign */
export const VoteModel = {
	state: {
		currentUserVotes: {},
		voteCollection: {},
		questionCollection: {},
		answerCollection: {},
	},

	getVoteState(): VoteElementState {
		return {
			id: '',
			questions: [],
			isAnonymous: false,
			isVoted: false,
			isLoading: false,
			canEdit: false,
			canVote: false,
			canRevoke: false,
			isCompleted: false,
			resultUrl: '',
		};
	},

	getQuestionState(): QuestionElementState {
		return {
			id: '',
			text: '',
			answers: [],
			isMultiple: false,
			totalCounter: 0,
		};
	},

	getAnswerState(): AnswerElementState {
		return {
			id: '',
			text: '',
			counter: 0,
			percent: 0,
		};
	},

	getters:
	{
		/** @function vote/hasCurrentUserVote */
		hasCurrentUserVote: (state): boolean => (questionId, answerId) => {
			return state.currentUserVotes[questionId] && state.currentUserVotes[questionId].includes(answerId);
		},
		/** @function vote/getCurrentUserVotes */
		getCurrentUserVotes: (state): CurrentUserVotesType => {
			return state.currentUserVotes;
		},
		/** @function vote/getVoteCollection */
		getVoteCollection: (state): VoteCollectionType => {
			return state.voteCollection;
		},
		/** @function vote/getQuestionCollection */
		getQuestionCollection: (state): QuestionCollectionType => {
			return state.questionCollection;
		},
		/** @function vote/getAnswerCollection */
		getAnswerCollection: (state): AnswerCollectionType => {
			return state.answerCollection;
		},
	},

	actions:
	{
		/** @function vote/setCurrentUserVotes */
		setCurrentUserVotes: (store, payload) => {
			const formattedVotes = {};

			Object.entries(payload).forEach(([questionId, answers]) => {
				formattedVotes[questionId] = Object.keys(answers)
					.map((answerId) => Number(answerId));
			});
			store.commit('setCurrentUserVotes', formattedVotes);
		},
		/** @function vote/clearVotes */
		clearVotes: (store, { questionId, voteId }) => {
			if (store.state.currentUserVotes[questionId])
			{
				store.commit('clearVotes', { questionId, voteId });
			}
		},
		/** @function vote/resetUserVoted */
		resetUserVoted: (store, { voteId }) => {
			if (store.state.voteCollection[voteId])
			{
				store.commit('resetUserVoted', { voteId });
			}
		},
		/** @function vote/addVote */
		addVote: (store, payload) => {
			store.commit('addVote', { ...VoteModel.getVoteState(), ...VoteModel.formatVoteFields(payload) });
		},
		/** @function vote/addQuestion */
		addQuestion: (store, payload) => {
			const question = Object.values(payload)[0];
			store.commit('addQuestion', { ...VoteModel.getQuestionState(), ...VoteModel.formatQuestionFields(question) });
		},
		/** @function vote/updateQuestionTotalCounter */
		updateQuestionTotalCounter: (store, payload) => {
			if (!store.state.questionCollection[payload.questionId])
			{
				return;
			}

			store.commit('updateQuestionTotalCounter', payload);
		},
		/** @function vote/addAnswer */
		addAnswer: (store, payload) => {
			const question = Object.values(payload)[0];
			Object.values(question.ANSWERS).forEach((answer) => {
				store.commit('addAnswer', {
					...VoteModel.getAnswerState(),
					...VoteModel.formatAnswerFields(answer),
				});
			});
		},
		/** @function vote/updateAnswer */
		updateAnswer: (store, payload) => {
			store.commit('updateAnswer', payload);
		},
		/** @function vote/updateCurrentUserVotes */
		updateCurrentUserVotes: (store, payload) => {
			store.commit('updateCurrentUserVotes', payload);
		},
		/** @function vote/setUserVoted */
		setUserVoted: (store, payload) => {
			store.commit('setUserVoted', payload);
		},
		/** @function vote/setVoteCompleted */
		setVoteCompleted: (store, payload) => {
			store.commit('setVoteCompleted', payload);
		},
		/** @function vote/resetVoteCompleted */
		resetVoteCompleted: (store, payload) => {
			store.commit('resetVoteCompleted', payload);
		},
		/** @function vote/setLoadingStatus */
		setLoadingStatus: (store, payload) => {
			store.commit('setLoadingStatus', payload);
		},
	},

	mutations:
	{
		setCurrentUserVotes: (state, payload) => {
			Object.entries(payload).forEach(([questionId, answerIds]) => {
				state.currentUserVotes[questionId] = answerIds;
			});
		},
		updateCurrentUserVotes: (state, { questionId, answerIds }) => {
			state.currentUserVotes[questionId] = answerIds;
		},
		addVote: (state, payload: VoteElementState) => {
			state.voteCollection[payload.id] = payload;
		},
		addQuestion: (state, payload: QuestionElementState) => {
			state.questionCollection[payload.id] = payload;
		},
		updateQuestionTotalCounter: (state, { questionId, totalCounter }) => {
			state.questionCollection[questionId].totalCounter = totalCounter;
		},
		addAnswer: (state, payload: AnswerElementState) => {
			state.answerCollection[payload.id] = payload;
		},
		updateAnswer: (state, payload) => {
			if (state.answerCollection[payload.answerId])
			{
				state.answerCollection[payload.answerId].percent = payload.percent;
				state.answerCollection[payload.answerId].counter = payload.counter;
			}
		},
		clearVotes: (state, payload) => {
			state.currentUserVotes[payload.questionId] = [];
		},
		setUserVoted: (state, payload) => {
			state.voteCollection[payload.voteId].isVoted = true;
		},
		resetUserVoted: (state, payload) => {
			state.voteCollection[payload.voteId].isVoted = false;
		},
		setVoteCompleted: (state, payload) => {
			state.voteCollection[payload.voteId].isCompleted = true;
		},
		resetVoteCompleted: (state, payload) => {
			state.voteCollection[payload.voteId].isCompleted = false;
		},
		setLoadingStatus: (state, payload) => {
			const { voteId, isLoading } = payload;
			if (state.voteCollection[voteId])
			{
				state.voteCollection[voteId].isLoading = isLoading;
			}
		},
	},

	formatVoteFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, voteFieldsConfig);
	},
	formatQuestionFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, questionFieldsConfig);
	},
	formatAnswerFields(fields: JsonObject): JsonObject
	{
		return formatFieldsWithConfig(fields, answerFieldsConfig);
	},
};
