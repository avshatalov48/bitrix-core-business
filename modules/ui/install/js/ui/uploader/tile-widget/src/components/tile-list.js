import { BitrixVue } from 'ui.vue';
import { TileItem } from './tile-item';

export const TileList = BitrixVue.localComponent('tile-list', {
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
});
