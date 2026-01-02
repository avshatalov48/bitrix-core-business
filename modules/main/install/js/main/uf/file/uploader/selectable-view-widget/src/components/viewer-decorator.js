import type { BitrixVueComponentProps } from 'ui.vue3';
import type { FileItemOptions } from '../types';

// @vue/component
export const ViewerDecorator: BitrixVueComponentProps = {
	name: 'ViewerDecorator',

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

	// language=Vue
	template: `
		<a class="main-file-field-selectable-view__viewer-decorator" v-bind="file.attributes" :href="file.urlForViewer">
			<slot />
		</a>
	`,
};
