import { CheckboxListOption } from './option';

export const CheckboxListCategory = {
	props: [
		'columnCount',
		'category',
		'options',
		'isActiveSearch',
		'isEditableOptionsTitle',
		'onChange',
		'setOptionRef',
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
						v-else
						:id="option.id"
						:title="option.title"
						:isChecked="option.value"
						:isLocked="option?.locked"
						:isEditable="isEditableOptionsTitle"
						:ref="setRef"
					/>
				</div>
			</div>
		</div>
	`,
};
