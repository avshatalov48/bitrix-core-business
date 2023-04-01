export const CheckboxListCategory = {
	props: [
		'columnCount',
		'category',
		'options'
	],

	methods: {
		handleCheckBox(id)
		{
			this.$emit('changeOption', id);
		},
		getOptionClassName(optionValue)
		{
			return [
				'ui-ctl',
				'ui-ctl-checkbox',
				'ui-checkbox-list__field-item_label',
				{'--checked': optionValue}
			];
		},
	},

	template: `
		<div class="ui-checkbox-list__category">
			<div class="ui-checkbox-list__categories-title">
				{{ category.title }}
			</div>
			<div 
				class="ui-checkbox-list__options"
				:style="{'-webkit-column-count': columnCount, 
						 '-moz-column-count': columnCount, 
						 'column-count': columnCount,
						 }"
			>
				<div
					v-for="option in options"
					:key="option.id"
				>
					<label
						:title="option.title"
						:class="getOptionClassName(option.value)"
					>
						<input
							type="checkbox"
							class="ui-ctl-element ui-checkbox-list__field-item_input"
							:checked="option.value"
							@click="handleCheckBox(option.id)"
						>
						<div class="ui-ctl-label-text ui-checkbox-list__field-item_text">{{ option.title }}</div>
					</label>
				</div>
			</div>
		</div>
	`
	}