import { FileIcon } from 'ui.icons.generator';

import type { BitrixVueComponentProps } from 'ui.vue3';
/**
 * @memberof BX.UI.Uploader
 */
export const FileIconComponent: BitrixVueComponentProps = {
	props: {
		name: {
			type: String,
		},
		type: {
			type: String,
		},
		color: {
			type: String,
		},
		size: {
			type: Number,
			default: 36,
		},
	},
	mounted(): void
	{
		const icon = new FileIcon({
			name: this.name,
			fileType: this.type,
			color: this.color,
			size: this.size,
		});

		icon.renderTo(this.$el);
	},
	template: '<span></span>',
};
