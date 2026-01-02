import type { BitrixVueComponentProps } from 'ui.vue3';
import type { FileItemOptions } from '../../types';

// @vue/component
export const BaseItem: BitrixVueComponentProps = {
	name: 'BaseItem',

	props: {
		fileItem: {
			type: Object,
			required: true,
		},
	},

	computed: {
		file(): FileItemOptions
		{
			return this.fileItem;
		},
	},
};
