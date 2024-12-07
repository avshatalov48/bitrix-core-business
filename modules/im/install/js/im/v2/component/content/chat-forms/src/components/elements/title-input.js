import './css/title-input.css';

// @vue/component
export const TitleInput = {
	name: 'TitleInput',
	props:
	{
		modelValue: {
			type: String,
			default: '',
		},
		placeholder: {
			type: String,
			required: true,
		},
	},
	emits: ['update:modelValue'],
	mounted()
	{
		this.$refs.titleInput.focus();
	},
	methods:
	{
		onInput(event: Event)
		{
			this.$emit('update:modelValue', event.target.value);
		},
	},
	template: `
		<div class="bx-im-chat-forms-title-input__container">
			<input
				:value="modelValue"
				:placeholder="placeholder"
				@input="onInput"
				class="bx-im-chat-forms-title-input__input"
				ref="titleInput"
			/>
		</div>
	`,
};
