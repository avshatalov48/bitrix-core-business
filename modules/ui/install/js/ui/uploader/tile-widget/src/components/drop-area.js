import { Loc } from 'main.core';

export const DropArea = {
	inject: ['uploader'],
	mounted()
	{
		this.uploader.assignBrowse(this.$refs.dropArea);
	},
	computed: {
		dropLabel() {
			return Loc.getMessage('TILE_UPLOADER_DROP_FILES_HERE');
		}
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader-drop-area" ref="dropArea">
			<div class="ui-tile-uploader-drop-box">
				<label class="ui-tile-uploader-drop-label">{{dropLabel}}</label>
				<!--<div class="ui-tile-uploader-settings"></div>-->
			</div>
		</div>
	`
};
