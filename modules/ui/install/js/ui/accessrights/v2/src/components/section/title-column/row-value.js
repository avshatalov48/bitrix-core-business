import { getValueComponent, Rows } from '../value/registry';

export const RowValue = {
	name: 'RowValue',
	components: { ...Rows },
	emits: ['close'],
	inject: ['right'],
	computed: {
		component(): string
		{
			return getValueComponent(this.right);
		},
	},
	template: `
		<Component :is="component" @close="$emit('close')" />
	`,
};
