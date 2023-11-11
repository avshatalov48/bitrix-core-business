import 'ui.forms';

import './css/checkbox.css';

// @vue/component
export const CheckboxOption = {
	name: 'CheckboxOption',
	props:
	{
		value: {
			type: Boolean,
			required: true,
		},
		text: {
			type: String,
			required: true,
		},
	},
	emits: ['change'],
	data()
	{
		return {};
	},
	methods:
	{
		onInput(event: Event)
		{
			this.$emit('change', event.target.checked);
		},
	},
	template: `
		<div class="bx-im-settings-checkbox__container bx-im-settings-section-content__block_option">
			<label class="ui-ctl ui-ctl-checkbox">
				<input type="checkbox" :checked="value" @input="onInput" class="ui-ctl-element">
				<div class="ui-ctl-label-text">{{ text }}</div>
			</label>
		</div>
	`,
};
