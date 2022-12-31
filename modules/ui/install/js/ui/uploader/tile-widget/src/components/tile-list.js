import { TileItem } from './tile-item';

/**
 * @memberof BX.UI.Uploader
 */
export const TileList = {
	components: {
		TileItem,
	},
	props: {
		items: {
			type: Array,
			default: []
		}
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader-items">
			<TileItem v-for="item in items" :key="item.id" :item="item" />
		</div>
	`
};
