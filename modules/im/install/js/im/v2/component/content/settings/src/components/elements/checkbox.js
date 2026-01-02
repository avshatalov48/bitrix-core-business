import 'ui.forms';

import { ChatHint } from 'im.v2.component.elements.hint';

import './css/checkbox.css';

// @vue/component
export const CheckboxOption = {
	name: 'CheckboxOption',
	components: { ChatHint },
	props: {
		value: {
			type: Boolean,
			required: true,
		},
		text: {
			type: String,
			required: false,
			default: '',
		},
		disabled: {
			type: Boolean,
			required: false,
			default: false,
		},
		hintText: {
			type: String,
			required: false,
			default: '',
		},
	},
	emits: ['change'],
	computed: {
		hasHint(): boolean
		{
			return this.hintText.length > 0;
		},
	},
	methods:
	{
		onInput(event: Event & { target: HTMLInputElement })
		{
			this.$emit('change', event.target.checked);
		},
	},
	template: `
		<div class="bx-im-settings-checkbox__container bx-im-settings-section-content__block_option" :class="{ '--no-text': text === '' }">
			<label class="ui-ctl ui-ctl-checkbox">
				<input type="checkbox" :checked="value" :disabled="disabled" @input="onInput" class="ui-ctl-element">
				<span v-if="text" class="ui-ctl-label-text">{{ text }}</span>
				<ChatHint v-if="hasHint" :text="hintText" />
			</label>
		</div>
	`,
};
