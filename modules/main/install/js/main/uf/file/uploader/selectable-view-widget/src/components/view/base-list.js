import type { BitrixVueComponentProps } from 'ui.vue3';
import type { FileItemOptions } from '../../types';

// @vue/component
export const BaseList: BitrixVueComponentProps = {
	name: 'BaseList',

	props: {
		fileItems: {
			type: Array,
			required: true,
		},
	},

	computed: {
		files(): FileItemOptions[]
		{
			return this.fileItems;
		},
	},
};
