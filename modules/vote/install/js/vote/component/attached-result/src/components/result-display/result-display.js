import { Loc } from 'main.core';

import { VotedUsersList } from '../voted-users/voted-users';
import type { BackendVotedUser, CurrentVoteType, QuestionsType, CurrentUserVotesType } from '../../types';

import './style.css';

const ANONYMOUS_VOTE = 2;

// @vue/component
export const VoteResultDisplay = {
	name: 'VoteResultDisplay',
	components: { VotedUsersList },
	props:
	{
		loadedData: {
			/** @type {BackendResultAll} */
			type: Object,
			required: true,
		},
		votedPageSize: {
			type: Number,
			required: true,
		},
	},
	computed:
	{
		currentVote(): CurrentVoteType
		{
			const {
				VOTE_ID,
				isVoted,
				signedAttachId,
				downloadUrl,
				canRevote,
				isFinished,
			} = this.loadedData.attach;

			return {
				id: VOTE_ID,
				isAnonymousVote: this.isAnonymousVote,
				isVoted,
				signedAttachId,
				downloadUrl,
				canRevoke: canRevote,
				isFinished,
			};
		},
		questions(): QuestionsType
		{
			const questionsData = this.loadedData.attach?.QUESTIONS;
			const calculatedPercentages = this.calculatePercentages(questionsData);

			const questions = {};
			Object.keys(questionsData).forEach((questionId) => {
				const question = questionsData[questionId];
				const answers = {};
				const isMultiple = question.FIELD_TYPE === '1';

				Object.keys(question.ANSWERS).forEach((answerId, index) => {
					const answer = question.ANSWERS[answerId];
					answers[answerId] = {
						id: Number(answer.ID),
						percent: isMultiple ? Math.round(answer.PERCENT) : calculatedPercentages[questionId][index],
						text: answer.MESSAGE,
						counter: Number(answer.COUNTER),
					};
				});

				questions[questionId] = {
					id: Number(question.ID),
					counter: Number(question.COUNTER),
					text: question.QUESTION,
					answers,
				};
			});

			return questions;
		},
		currentUserVotes(): CurrentUserVotesType
		{
			const currentUserVotes = {};
			const userAnswerMap = this.loadedData.attach?.userAnswerMap;
			Object.entries(userAnswerMap).forEach(([questionId, answers]) => {
				currentUserVotes[questionId] = Object.keys(answers)
					.map((answerId) => Number(answerId));
			});

			return currentUserVotes;
		},
		isAnonymousVote(): boolean
		{
			return this.loadedData.attach?.ANONYMITY === ANONYMOUS_VOTE;
		},
		voteTypeText(): string
		{
			return this.isAnonymousVote
				? Loc.getMessage('VOTE_JS_ATTACHED_RESULT_ANONYMOUS')
				: Loc.getMessage('VOTE_JS_ATTACHED_RESULT_PUBLIC')
			;
		},
		isFinishedVote(): boolean
		{
			return this.currentVote.isFinished;
		},
		canRevoke(): boolean
		{
			if (this.currentVote.isFinished)
			{
				return this.currentVote.isFinished;
			}

			return this.currentVote.canRevoke;
		},
	},
	methods: {
		hasCurrentUserVote(questionId: number, answerId: number): boolean
		{
			return this.currentUserVotes[questionId] && this.currentUserVotes[questionId].includes(answerId);
		},
		getSummaryText(count: number): string
		{
			return Loc.getMessagePlural('VOTE_JS_ATTACHED_RESULT_QUESTION_VOTED_COUNT', count, {
				'#COUNT#': count,
			});
		},
		getVotedUsers(answerId: number): BackendVotedUser[]
		{
			return this.loadedData.voted?.[answerId] ?? [];
		},
		hasVotes(counter: number): boolean
		{
			return counter > 0;
		},
		getSelectedClass(questionId, answerId): string
		{
			return this.hasCurrentUserVote(questionId, answerId) ? '--selected' : '';
		},
		calculatePercentages(questions): CurrentUserVotesType
		{
			const percentages = {};

			Object.values(questions).forEach((question) => {
				const answers = question.ANSWERS;

				const answerVotes = Object.values(answers).map((answer) => Number(answer.COUNTER));
				const totalVotes = answerVotes.reduce((sum, count) => sum + count, 0);

				if (totalVotes === 0)
				{
					percentages[question.ID] = answerVotes.map(() => 0);

					return;
				}

				const calculatedPercents = answerVotes.map((vote) => (vote / totalVotes) * 100);
				const roundedPercents = calculatedPercents.map((percent) => Math.floor(percent));
				const remainder = 100 - roundedPercents.reduce((sum, p) => sum + p, 0);

				const fractionalParts = calculatedPercents
					.map((percent, index) => ({ index, fraction: percent % 1 }))
					.sort((a, b) => b.fraction - a.fraction);

				for (let i = 0; i < remainder; i++)
				{
					roundedPercents[fractionalParts[i].index] += 1;
				}

				percentages[question.ID] = roundedPercents;
			});

			return percentages;
		},
	},
	template: `
		<div class="vote-result__wrapper">
			<div class="vote-result__info">
			  <span>{{ voteTypeText }}</span>
			  <span v-if="!canRevoke">{{ $Bitrix.Loc.getMessage('VOTE_JS_ATTACHED_RESULT_REVOKE_IS_NOT_AVAILABLE') }} </span>
			  <span v-if="isFinishedVote">{{ $Bitrix.Loc.getMessage('VOTE_JS_ATTACHED_RESULT_VOTE_IS_FINISHED') }}</span>
			</div>
			<div v-for="question in questions" :key="question.id">
				<div class="vote-result__title">{{ question.text }}</div>
				<div class="vote-result__summary">{{ getSummaryText(question.counter) }}</div>
				<div class="vote-result__answers">
					<div class="vote-result__answer" v-for="answer in question.answers" :key="answer.id">
						<div class="vote-result__answer-inner" :class="getSelectedClass(question.id, answer.id)">
							<div class="vote-result__answer-text">{{ answer.text }}</div>
							<div class="vote-result__answer-percent">
								<span>{{ answer.percent }}</span>
								%
							</div>
							<div class="vote-result__answer-fill"
								:style="{ width: answer.percent + '%' }"
							></div>
						</div>
						<VotedUsersList
							v-if="hasVotes(answer.counter)"
							:count="answer.counter"
							:votedUsers="getVotedUsers(answer.id)"
							:signedAttachId="currentVote.signedAttachId"
							:answerId="answer.id"
							:pageSize="votedPageSize"
							:showUsers="!isAnonymousVote"
						/>
					</div>
				</div>
			</div>
		</div>
	`,
};
