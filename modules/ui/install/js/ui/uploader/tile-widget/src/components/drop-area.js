import { Loc } from 'main.core';

import { SettingsButton } from './settings-button';

import type { BitrixVueComponentProps } from 'ui.vue3';

export const DropArea: BitrixVueComponentProps = {
	inject: ['uploader', 'widgetOptions', 'emitter'],
	components: {
		SettingsButton,
	},
	mounted(): void
	{
		this.uploader.assignBrowse(this.$refs.dropArea);
	},
	computed: {
		dropLabel(): string
		{
			return Loc.getMessage('TILE_UPLOADER_DROP_FILES_HERE');
		},
	},
	methods: {
		handleSettingsClick()
		{
			this.emitter.emit('onSettingsButtonClick', { button: this.$refs['ui-tile-uploader-settings'] });
		},
	},
	// language=Vue
	template: `
		<div class="ui-tile-uploader-drop-area">
			<div class="ui-tile-uploader-drop-box">
				<label class="ui-tile-uploader-drop-label" ref="dropArea">{{dropLabel}}</label>
				<SettingsButton v-if="widgetOptions.showSettingsButton" />
			</div>
		</div>
	`,
};
