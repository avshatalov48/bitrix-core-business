import { Loc } from 'main.core';
import { BIcon } from 'ui.icon-set.api.vue';
import { Actions } from 'ui.icon-set.api.core';
import 'ui.icon-set.actions';
import type { BitrixVueComponentProps } from 'ui.vue3';

// @vue/component
export const TileMoreItem: BitrixVueComponentProps = {
	components: {
		BIcon,
	},
	emit: ['onClick'],
	props: {
		hiddenFilesCount: {
			type: Number,
			default: 0,
		},
	},
	setup(): Object
	{
		return {
			Actions,
		};
	},
	computed: {
		moreButtonCaption(): string
		{
			return Loc.getMessage('TILE_UPLOADER_MORE_BUTTON_CAPTION', {
				'#COUNT#': `<span class="ui-tile-uploader-item-more-count">${this.hiddenFilesCount}</span>`,
			});
		},
	},
	template: `
		<div class="ui-tile-uploader-item" @click="$emit('onClick')">
			<div class="ui-tile-uploader-item-more">
				<BIcon class="ui-tile-uploader-item-more-icon" :name="Actions.MORE"/>
				<div class="ui-tile-uploader-item-more-label" v-html="moreButtonCaption"></div>
			</div>
		</div>
	`,
};
