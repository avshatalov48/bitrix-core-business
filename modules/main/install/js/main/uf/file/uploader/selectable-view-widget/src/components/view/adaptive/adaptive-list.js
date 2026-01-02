import { BaseList } from '../base-list';
import { ViewerDecorator } from '../../viewer-decorator';
import { AdaptiveItem } from './adaptive-item';

import type { BitrixVueComponentProps } from 'ui.vue3';
import type { FileItemOptions } from '../../../types';

import '../../../css/view/adaptive.css';

// @vue/component
export const AdaptiveList: BitrixVueComponentProps = {
	name: 'AdaptiveList',

	components: {
		ViewerDecorator,
		AdaptiveItem,
	},

	extends: BaseList,

	computed: {
		sortedFiles(): FileItemOptions[]
		{
			return [...this.files]
				.sort((fileA: FileItemOptions, fileB: FileItemOptions): number => {
					switch (true)
					{
						case fileA.isImage && fileB.isImage:
							return 0;
						case fileA.isImage:
							return -1;
						case fileB.isImage:
							return 1;
						default:
							return 0;
					}
				});
		},
	},

	// language=Vue
	template: `
		<div class="main-file-field-selectable-view__adaptive-list">
			<ViewerDecorator :class="{ '--image': file.isImage }" v-for="file in sortedFiles" :file-item="file">
				<AdaptiveItem :file-item="file" />
			</ViewerDecorator>
		</div>
	`,
};
