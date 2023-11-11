import type { JsonObject } from 'main.core';

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
	data(): JsonObject
	{
		return {};
	},
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
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<div class="bx-im-content-create-chat__title_container">
			<input
				:value="modelValue"
				:placeholder="placeholder"
				@input="onInput"
				class="bx-im-content-create-chat__title_input"
				ref="titleInput"
			/>
		</div>
	`,
};
