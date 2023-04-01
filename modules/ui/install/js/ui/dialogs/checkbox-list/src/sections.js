export const CheckboxListSections = {
	props: [
		'sections'
	],

	methods: {
		handleClick(key)
		{
			this.$emit('sectionToggled', key);
		},
		getSectionsItemClassName(sectionValue)
		{
			return [
				'ui-checkbox-list__sections-item',
				{'--checked': sectionValue}
			];
		},
	},

	template: `
		<div class="ui-checkbox-list__sections">
			<div 
				v-for="section in sections"
				:key="section.key"
				:title="section.title"
				:class="getSectionsItemClassName(section.value)"
				@click="handleClick(section.key)"
			>
				<div class="ui-checkbox-list__check-box"></div>
				{{ section.title }}
			</div>
		</div>
	`
	}