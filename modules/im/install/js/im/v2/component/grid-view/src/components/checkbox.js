// @vue/component
export const Checkbox = {
	name: 'Checkbox',
	emits: ['changeData'],
	props: {
		cellValue: {
			type: Boolean,
			required: true
		}
	},
	template: `
		<input
			type="checkbox"
			:checked="cellValue"
			class="bx-im-grid-view__cell_checkbox"
			@change="(event) => {
				this.$emit('changeData', event.target.checked);
			}"
		/>
	`
};