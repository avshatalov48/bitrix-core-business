import { TileItemAdapter } from './tile-item-adapter';
import { ViewerDecorator } from '../../viewer-decorator';
import { BaseList } from '../base-list';

import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const TileList: BitrixVueComponentProps = {
	name: 'TileList',

	components: {
		TileItemAdapter,
		ViewerDecorator,
	},

	extends: BaseList,

	// language=Vue
	template: `
		<div class="ui-tile-uploader">
			<div class='main-file-field-selectable-view__tile-list ui-tile-uploader-items'>
				<ViewerDecorator v-for="file in files" :file-item="file">
					<TileItemAdapter :file-item="file" />
				</ViewerDecorator>
			</div>
		</div>
	`,
};
