import { CheckboxListOption } from './option';

export const CheckboxListCategory = {
	props: [
		'columnCount',
		'category',
		'options',
		'context',
		'isActiveSearch',
		'isEditableOptionsTitle',
		'onChange',
		'setOptionRef',
	],

	emits: [
		'onToggleOption',
	],

	components: {
		CheckboxListOption,
	},

	methods: {
		setRef(ref)
		{
			if (ref)
			{
				this.setOptionRef(ref.getId(), ref);
			}
		},
		onToggleOption(event)
		{
			this.$emit('onToggleOption', event);
		},
	},

	template: `
		<div
			v-if="options.length > 0 || !isActiveSearch"
			class="ui-checkbox-list__category"
		>
			<div v-if="category" class="ui-checkbox-list__categories-title">
				{{ category.title }}
			</div>
			<div 
				class="ui-checkbox-list__options"
				:style="{ 'column-count': columnCount }"
			>
				<div
					v-for="option in options"
					:key="option.id"
				>
					<checkbox-list-option
						:context="context"
						:id="option.id"
						:title="option.title"
						:isChecked="option.value"
						:isLocked="option?.locked"
						:isEditable="isEditableOptionsTitle"
						:ref="setRef"
						@onToggleOption="onToggleOption"
					/>
				</div>
			</div>
		</div>
	`,
};
