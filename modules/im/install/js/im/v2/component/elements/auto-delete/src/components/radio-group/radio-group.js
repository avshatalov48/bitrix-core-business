import './css/radio-group.css';

// @vue/component
export const RadioGroup = {
	name: 'RadioGroup',
	props:
	{
		items: {
			type: Array,
			required: true,
		},
	},
	emits: ['change'],
	computed:
	{
		selectedValue(): number
		{
			return this.items.find((option) => option.selected).value;
		},
	},
	methods:
	{
		onInput(value): void
		{
			this.$emit('change', value);
		},
	},
	template: `
		<div class="bx-im-auto-delete-popup-radio__container">
			<label v-for="option in items" class="bx-im-auto-delete-popup-radio__option">
				<input
					type="radio"
					class="bx-im-auto-delete-popup-radio__input"
					:value="option.value"
					:checked="option.value === selectedValue"
					@change="onInput(option.value)"
				/>
				<span class="bx-im-auto-delete-popup-radio__label">{{ option.text }}</span>
			</label>
		</div>
	`,
};
