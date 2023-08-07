import type { BitrixVueComponentProps } from 'ui.vue3';
import { ErrorPopup, UploadLoader } from 'ui.uploader.tile-widget';
import { FileIconComponent } from './file-icon';
import { Loc } from 'main.core';

export const TileMoreItem: BitrixVueComponentProps = {
	components: {
		UploadLoader,
		ErrorPopup,
		FileIconComponent,
	},
	emit: ['onClick'],
	props: {
		hiddenFilesCount: {
			type: Number,
			default: 0
		},
	},
	computed: {
		moreButtonCaption(): string {
			return Loc.getMessage(
				'TILE_UPLOADER_MORE_BUTTON_CAPTION',
				{
					'#COUNT#': `<span class="ui-tile-uploader-item-more-count">${this.hiddenFilesCount}</span>`
				}
			);
		},
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader-item" @click="$emit('onClick')">
			<div class="ui-tile-uploader-item-more">
				<div class="ui-tile-uploader-item-more-icon"></div>
				<div class="ui-tile-uploader-item-more-label" v-html="moreButtonCaption"></div>
			</div>
		</div>
	`,
};