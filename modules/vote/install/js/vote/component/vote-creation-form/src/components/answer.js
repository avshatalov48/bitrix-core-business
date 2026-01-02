import { Resize } from '../directives/resize';

// @vue/component
export const Answer = {
	name: 'voteAnswer',
	directives: { resize: Resize },
	props:
	{
		id: {
			type: String,
			required: true,
		},
		removable: {
			type: Boolean,
			required: true,
		},
	},
	emits: ['removeAnswer', 'changeAnswer', 'onKeyDownEnter'],
	data(): { answerText: string; }
	{
		return {
			answerText: '',
		};
	},
	methods:
	{
		changeAnswer(): void
		{
			this.$emit('changeAnswer', this.answerText);
		},
		focus(): void
		{
			this.$refs.answerField.focus();
		},
		handleDeleteClick(): void
		{
			if (this.removable)
			{
				this.$emit('removeAnswer');
			}
			else
			{
				this.answerText = '';
				this.$emit('changeAnswer', this.answerText);
			}
		},
		handleEnterPress(): void
		{
			this.$emit('onKeyDownEnter');
		},
	},
	template: `
		<div class="vote-creation-form__answer" :data-id="id">
			<div class="vote-creation-form__answer_dnd-icon ui-icon-set --more-points"></div>
			<div class="ui-ctl ui-ctl-textarea ui-ctl-no-resize">
				<textarea
					maxlength="100"
					autocomplete="off"
					class="ui-ctl-element"
					:data-test-id="'vote_creation_form_' + id"
					v-resize
					v-model.trim="answerText"
					@input="changeAnswer"
					@keydown.enter.prevent="handleEnterPress"
					ref="answerField"
				></textarea>
				<span
					:data-test-id="'vote_creation_form_delete_' + id"
					class="vote-creation-form__answer_delete"
					@click="handleDeleteClick"
				>
				</span>
			</div>
		</div>
	`,
};
