import { hint, type HintParams } from 'ui.vue3.directives.hint';

import { VoteApplication } from 'vote.application';
import { getMessageWithCount } from '../helpers/helpers';

import type { JsonObject } from 'main.core';
import type { AnswerCollectionType } from 'vote.store.vote';
import type { AnswersSelectedEvent, FormattedAnswersType } from '../../type';

import './style.css';

// @vue/component
export const VoteQuestion = {
	name: 'VoteQuestion',
	directives: { hint },
	props: {
		contextId: {
			type: String,
			required: true,
		},
		isLoading: {
			type: Boolean,
			default: false,
		},
		/** @type {FormattedQuestionType} */
		question: {
			type: Object,
			required: true,
		},
		/** @type {FormattedAnswersType} */
		answers: {
			type: Object,
			required: true,
		},
		isUserVoted: {
			type: Boolean,
			default: false,
		},
		isCompleted: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['answersSelected'],
	data(): JsonObject
	{
		return {
			selectedRadioBtn: null,
			selectedCheckboxes: [],
		};
	},
	computed: {
		answersCollection(): AnswerCollectionType
		{
			return this.app.getStore().getters['vote/getAnswerCollection'];
		},
		formattedAnswers(): FormattedAnswersType
		{
			const formattedAnswers = {};

			Object.keys(this.answers).forEach((key, index) => {
				const answer = this.answers[key];
				const storeAnswer = this.answersCollection[answer.id] || {};

				formattedAnswers[key] = {
					...answer,
					counter: storeAnswer.counter || 0,
					percent: this.question.isMultiple ? Math.round(storeAnswer.percent) : this.roundPercentages[index],
				};
			});

			return formattedAnswers;
		},
		getCurrentUserVotes(): number[]
		{
			return this.app.getStore().getters['vote/getCurrentUserVotes'][this.question.id] || [];
		},
		canShowResults(): boolean
		{
			return this.isUserVoted || this.isCompleted;
		},
		answerVotes(): number[]
		{
			return Object.values(this.answers).map((answer) => {
				const counter = this.answersCollection[answer.id]?.counter;

				return counter || 0;
			});
		},
		roundPercentages(): number[]
		{
			const totalVotes = this.answerVotes.reduce((sum, count) => sum + count, 0);
			if (totalVotes === 0)
			{
				return this.answerVotes.map(() => 0);
			}

			const calculatedPercents = this.answerVotes.map((vote) => (vote / totalVotes) * 100);
			const roundedPercents = calculatedPercents.map((percent) => Math.floor(percent));
			const remainder = 100 - roundedPercents.reduce((sum, p) => sum + p, 0);

			const fractionalParts = calculatedPercents
				.map((percent, index) => ({ index, fraction: percent % 1 }))
				.sort((a, b) => b.fraction - a.fraction);

			for (let i = 0; i < remainder; i++)
			{
				roundedPercents[fractionalParts[i].index] += 1;
			}

			return roundedPercents;
		},
	},
	watch: {
		isLoading(newValue): void
		{
			if (newValue)
			{
				this.selectedCheckboxes = this.getCurrentUserVotes || [];
				this.selectedRadioBtn = this.getCurrentUserVotes[0];
			}
		},
		isUserVoted(voted: boolean): void
		{
			if (voted)
			{
				return;
			}

			this.selectedCheckboxes = [];
			this.selectedRadioBtn = null;
		},
	},
	created()
	{
		this.app = VoteApplication.init();
	},
	methods:
	{
		radioChanged(): void
		{
			this.emitAnswersSelectedWithValue([this.selectedRadioBtn]);
		},
		checkboxChanged(): void
		{
			this.emitAnswersSelectedWithValue(this.selectedCheckboxes);
		},
		emitAnswersSelectedWithValue(answerIds: number[]): void
		{
			const eventData: AnswersSelectedEvent = {
				questionId: this.question.id,
				answerIds,
			};
			this.$emit('answersSelected', eventData);
		},
		hasCurrentUserVote(answerId: number): boolean
		{
			if (this.canShowResults)
			{
				return this.app.getStore().getters['vote/hasCurrentUserVote'](this.question.id, answerId);
			}

			return this.selectedCheckboxes.includes(answerId);
		},
		getUniqueAnswerId(answerId: number): string
		{
			return `vote-answer-${answerId}-${this.contextId}`;
		},
		showHintCounter(counter: number): Partial<HintParams> | null
		{
			return {
				text: this.countText(counter),
				popupOptions: {
					position: 'bottom',
					targetContainer: document.body,
					offsetLeft: 25,
					offsetTop: 5,
					autoHide: false,
					angle: {
						position: 'top',
					},
				},
			};
		},
		countText(counter: number): string
		{
			return getMessageWithCount('VOTE_RESULT_COUNT', counter);
		},
	},
	template: `
		<div class="vote__question">
			<div class="vote__question-text">{{ question.question }}</div>
		</div>
		<div :class="['vote__answers', { '--voted': canShowResults }]">
			<div v-for="(answer, answerKey) in formattedAnswers" 
				 :key="answerKey"
				 :class="['vote__answer', { '--selected': hasCurrentUserVote(answer.id) }]"
			>
				<input
					class="vote__answer-select"
					v-if="!isLoading && !question.isMultiple"
					type="radio"
					v-model="selectedRadioBtn"
					:value="answer.id"
					:id="getUniqueAnswerId(answer.id)"
					@change="radioChanged"
				/>
				<input
					class="vote__answer-select --checkbox"
					v-if="!isLoading && question.isMultiple"
					type="checkbox"
					v-model="selectedCheckboxes"
					:value="answer.id"
					:id="getUniqueAnswerId(answer.id)"
					:key="answer.id"
					@change="checkboxChanged"
				/>
				<div class="vote__progress-bar">
					<label class='vote__answer-text' :for="getUniqueAnswerId(answer.id)">{{ answer.message }}</label>
					<transition name="vote__answer-percent-show">
						<div
							v-if="canShowResults"
							v-hint="answer.counter > 0 ? (() => showHintCounter(answer.counter)) : null"
							class="vote__answer-percent"
							:key="'percent-' + answerKey + '-' + answer.counter">
							<span>{{ answer.percent }}</span>
							%
						</div>
					</transition>
					<transition name="vote__progress-bar-filled">
						<div v-if="canShowResults" class="vote__progress-bar-fill"
							 :key="'fill-' + answerKey"
							 :style="{ '--target-width': answer.percent + '%' }"
						></div>
					</transition>
				</div>
			</div>
		</div>
	`,
};
