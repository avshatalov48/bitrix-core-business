import { Dom } from 'main.core';
import { Answer } from './answer';
import { Loc } from '../mixins/loc';
import { DragAndDrop } from '../directives/answer-dnd';
import { Resize } from '../directives/resize';
import { Switcher, SwitcherSize } from 'ui.switcher';

export type QuestionData = {
	questionText: string;
	allowMultipleAnswers: boolean;
	answers: { [key: string]: string; }
};

// @vue/component
export const Question = {
	name: 'voteQuestion',
	directives: { dnd: DragAndDrop, resize: Resize },
	components: { Answer },
	mixins: [Loc],
	props: {
		id: {
			type: String,
			required: true,
		},
		question: {
			type: Object,
			required: true,
		},
		minAnswersCount: {
			type: Number,
			required: true,
		},
		maxAnswersCount: {
			type: Number,
			required: true,
		},
	},
	emits: ['addAnswer', 'removeAnswer', 'changeQuestion', 'validate'],
	data(): { questionText: string; }
	{
		return { questionText: '' };
	},
	computed:
	{
		answersCount(): number
		{
			return Object.keys(this.question.answers).length;
		},
		isValid(): boolean
		{
			const answers = Object.values(this.question.answers);
			const filledAnswers = answers.filter((answer) => answer !== '');

			return this.question.questionText !== '' && filledAnswers.length >= this.minAnswersCount;
		},
		canAddMoreAnswers(): boolean
		{
			return this.answersCount < this.maxAnswersCount;
		},
		removable(): boolean
		{
			return this.answersCount > this.minAnswersCount;
		},
	},
	watch:
	{
		isValid(): void
		{
			this.$emit('validate', this.isValid);
		},
	},
	mounted(): void
	{
		const switcher = new Switcher({
			node: this.$refs.multipleAnswersSwitcher,
			size: SwitcherSize.small,
			checked: false,
			handlers: {
				toggled: () => {
					this.$emit('changeQuestion', {
						...this.question,
						allowMultipleAnswers: switcher.checked,
					});
				},
			},
		});
		Dom.attr(switcher.node, 'data-test-id', `vote_creation_form_allow_multiple_answers_${this.id}`);
	},
	methods:
	{
		changeText(): void
		{
			const question = { ...this.question, questionText: this.questionText };
			this.$emit('changeQuestion', question);
		},
		addAnswer(): void
		{
			this.$emit('addAnswer');
		},
		removeAnswer(answerId: string): void
		{
			this.$emit('removeAnswer', answerId);
		},
		changeAnswer(answerId: string, answerText: string): void
		{
			const answers = this.question.answers;
			const question = {
				...this.question,
				answers: {
					...answers,
					[answerId]: answerText,
				},
			};
			this.$emit('changeQuestion', question);
		},
		orderAnswer(draggedKey: string, targetKey: string, shouldInsertBelow: boolean): void
		{
			const answers = this.question.answers;
			const newKeys = Object.keys(answers).filter((key) => key !== draggedKey);
			const targetIndex = newKeys.indexOf(targetKey);
			const insertionIndex = shouldInsertBelow ? targetIndex + 1 : targetIndex;
			newKeys.splice(insertionIndex, 0, draggedKey);
			const newAnswers = newKeys.reduce((acc, key) => {
				acc[key] = answers[key];

				return acc;
			}, {});
			this.$emit('changeQuestion', {
				...this.question,
				answers: newAnswers,
			});
		},
		focusQuestionField(): void {
			const textarea = this.$refs.questionField;
			textarea.focus();
		},
		getAnswerRefById(answerId: string): ?HTMLTextAreaElement
		{
			return this.$refs[`answer_${answerId}`]?.[0];
		},
		getAnswerIdByIndex(index: number): string | null
		{
			const answerIds = Object.keys(this.question.answers);

			return answerIds[index] ?? null;
		},
		async focusNewAnswer(newAnswerId: string): void
		{
			const newAnswer = this.getAnswerRefById(newAnswerId);
			if (newAnswer)
			{
				await this.$nextTick();
				newAnswer.focus();
			}
		},
		focusNextAnswer(currentAnswerId: string): void
		{
			const answerIds = Object.keys(this.question.answers);
			const currentIndex = answerIds.indexOf(currentAnswerId);

			if (currentIndex === -1)
			{
				return;
			}

			this.focusByIndex(currentIndex + 1);
		},
		handleQuestionEnter(): void
		{
			this.focusByIndex(0);
		},
		focusByIndex(index: number): void
		{
			const targetAnswerId = this.getAnswerIdByIndex(index);
			const answerElement = this.getAnswerRefById(targetAnswerId);

			if (answerElement)
			{
				answerElement.focus();
			}
			else if (this.canAddMoreAnswers)
			{
				this.addAnswer();
			}
		},
	},
	template: `
		<div class="vote-creation-form__question" :data-id="id">
			<p class="vote-creation-form__question_label">
				{{loc('VOTE_QUESTION_LABEL')}}
			</p>
			<div class="ui-ctl ui-ctl-textarea ui-ctl-no-resize">
				<textarea
					maxlength="250"
					class="ui-ctl-element"
					:data-test-id="'vote_creation_form_' + id"
					v-model.trim="questionText"
					v-resize
					@input="changeText"
					@keydown.enter.prevent="handleQuestionEnter"
					ref="questionField"
				></textarea>
			</div>
			<p class="vote-creation-form__question_answer-options">
				{{loc('VOTE_QUESTION_ANSWER_OPTIONS')}}
			</p>
			<div
				class="vote-creation-form__answers"
				:class="{'--removable': removable}"
				v-dnd="orderAnswer"
			>
				<Answer
					v-for="(answer, id) in question.answers"
					:key="id"
					:id="id"
					:ref="'answer_' + id"
					:answer="answer"
					:removable="removable"
					@removeAnswer="removeAnswer(id)"
					@changeAnswer="changeAnswer(id, $event)"
					@onKeyDownEnter="focusNextAnswer(id)"
				></Answer>
			</div>
			<div
				v-if="canAddMoreAnswers"
				:data-test-id="'vote_creation_form_add_answer_' + id"
				class="vote-creation-form__question_add-answer"
				@click="addAnswer"
			>
				<div class="vote-creation-form__question_plus"></div>
				<span>{{loc('VOTE_QUESTION_ADD_ANSWER')}}</span>
			</div>
			<div class="vote-creation-form__question_multiple-answers">
				<span>{{loc('VOTE_QUESTION_MULTIPLE_ANSWERS')}}</span>
				<div ref="multipleAnswersSwitcher"></div>
			</div>
		</div>
	`,
};
