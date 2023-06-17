import {Dropdown} from 'im.v2.component.elements';

// @vue/component
export const Select = {
	name: 'Select',
	components: {Dropdown},
	emits: ['changeData'],
	props: {
		fieldValue: {
			type: String,
			required: true
		},
		fieldMetadata: {
			type: Object,
			required: true
		}
	},
	template: `
		<label class="bx-im-form-view__field_label">
			{{fieldMetadata.label}}
		</label>
		<Dropdown
			class="bx-im-form-view__field_select"
			:id="fieldMetadata.id"
			:items="fieldMetadata.options"
			@itemChange="(event) => {
				this.$emit('changeData', event);
			}"
		/>
	`
};