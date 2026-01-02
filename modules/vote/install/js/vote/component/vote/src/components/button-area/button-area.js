import { VoteApplication } from 'vote.application';
import { getMessage, getMessageWithCount } from '../helpers/helpers';

import { VoteQuestion } from '../vote-question/vote-question';

import './style.css';

const ButtonType = Object.freeze({
	vote: 'vote',
	disable: 'disable',
	showResults: 'show',
});

// @vue/component
export const ButtonArea = {
	name: 'ButtonArea',
	components: { VoteQuestion },
	props: {
		/** @type {FormattedQuestionType} */
		question: {
			type: Object,
			required: true,
		},
		isLoading: {
			type: Boolean,
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
		isBtnActive: {
			type: Boolean,
			default: false,
		},
	},
	emits: ['onClickVoteButton', 'showResults'],
	computed:
	{
		isMultipleQuestion(): boolean
		{
			if (this.isLoading)
			{
				return false;
			}

			return this.app.getStore().getters['vote/getQuestionCollection'][this.question.id].isMultiple;
		},
		buttonType(): string
		{
			if ((this.isUserVoted || this.isCompleted) && this.question.totalCounter > 0)
			{
				return ButtonType.showResults;
			}

			if (this.isMultipleQuestion && !this.isCompleted)
			{
				return ButtonType.vote;
			}

			return ButtonType.disable;
		},
		isBtnAvailableToVote(): boolean
		{
			return this.isBtnActive && this.buttonType === ButtonType.vote;
		},
		buttonClass(): string
		{
			return `--${this.buttonType}`;
		},
		summaryText(): string
		{
			if (this.question.totalCounter > 0)
			{
				return getMessageWithCount('VOTE_RESULT_COUNT', this.question.totalCounter);
			}

			return getMessage('VOTE_SUMMARY_COUNT_NO_VOTES');
		},
		buttonText(): string
		{
			if (this.isUserVoted || !this.isMultipleQuestion || this.isCompleted)
			{
				return this.summaryText;
			}

			return getMessage('VOTE_BUTTON');
		},
	},
	created(): void
	{
		this.app = VoteApplication.init();
	},
	methods: {
		handleButtonClick(): void
		{
			if (this.buttonType === ButtonType.vote)
			{
				this.$emit('onClickVoteButton');
			}
			else if (this.buttonType === ButtonType.showResults)
			{
				this.$emit('showResults');
			}
		},
	},
	template: `
		<div class="vote-display-btn-wrapper">
			<button class="vote-display-btn"
					@click="handleButtonClick"
					:class="[buttonClass, { '--active': isBtnAvailableToVote }]"
					type="button"
			>
				{{ buttonText }}
			</button>
		</div>
	`,
};
