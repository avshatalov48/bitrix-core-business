// @vue/component
export const Checkbox = {
	name: 'Checkbox',
	emits: ['changeData'],
	props: {
		fieldValue: {
			type: Boolean,
			required: true
		},
		fieldMetadata: {
			type: Object,
			required: true
		}
	},
	template: `
		<input
			type="checkbox"
			:checked=fieldValue
			:id="fieldMetadata.id"
			class="bx-im-form-view__field_checkbox"
			@change="(event) => {
				this.$emit('changeData', event.target.checked);
			}"
		/>
		<label
			class="bx-im-form-view__field_label"
			:for="fieldMetadata.id"
		>
			{{fieldMetadata.label}}
		</label>
	`
};