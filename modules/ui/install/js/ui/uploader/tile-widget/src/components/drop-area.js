import { BitrixVue } from 'ui.vue';
import type TileWidget from '../tile-widget';

export const DropArea = BitrixVue.localComponent('drop-area', {
	mounted()
	{
		this.$root.getUploader().assignBrowse(this.$refs.dropArea);
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader-drop-area" ref="dropArea">
			<div class="ui-tile-uploader-drop-box">
				<label class="ui-tile-uploader-drop-label">{{
					$Bitrix.Loc.getMessage('TILE_UPLOADER_DROP_FILES_HERE')
				}}</label>
				<!--<div class="ui-tile-uploader-settings"></div>-->
			</div>
		</div>
	`
});
