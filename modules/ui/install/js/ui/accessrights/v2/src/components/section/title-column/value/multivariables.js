import { Selector } from '../../value/multivariables/selector';

export const Multivariables = {
	name: 'Multivariables',
	emits: ['close'],
	components: {
		Selector,
	},
	inject: ['section', 'right'],
	methods: {
		apply({ values }): void
		{
			this.$store.dispatch('userGroups/setAccessRightValuesForShown', {
				sectionCode: this.section.sectionCode,
				valueId: this.right.id,
				values,
			});
		},
		close(): void
		{
			this.$emit('close');
		},
	},
	template: `
		<Selector @apply="apply" @close="close"/>
	`,
};
