import {Settings} from 'im.v2.const';
import {ThemeColorScheme, ThemeManager} from 'im.v2.lib.theme';

import {BackgroundService} from '../../classes/background-service';

import '../../css/background.css';

// @vue/component
export const BackgroundContent = {
	name: 'BackgroundContent',
	emits: ['close'],
	computed:
	{
		currentBackgroundId(): string
		{
			return this.$store.getters['application/settings/get'](Settings.dialog.background).toString();
		},
		backgroundIdList(): string[]
		{
			return Object.keys(ThemeColorScheme);
		}
	},
	methods:
	{
		getBackgroundStyleById(backgroundId: string)
		{
			return ThemeManager.getBackgroundStyleById(backgroundId);
		},
		onBackgroundClick(backgroundId: string)
		{
			BackgroundService.changeBackground(backgroundId);
		}
	},
	template: `
		<div class="bx-im-background-select-popup__container">
			<!-- <div class="bx-im-background-select-popup__title">Chat background</div> -->
			<div class="bx-im-background-select-popup__list">
				<div
					v-for="id in backgroundIdList"
					:key="id"
					:style="getBackgroundStyleById(id)"
					class="bx-im-background-select-popup__item"
					:class="{'--active': id === currentBackgroundId}"
					@click="onBackgroundClick(id)"
				></div>
			</div>
			<!-- <div @click="$emit('close')" class="bx-im-background-select-popup__close bx-im-messenger__cross-icon"></div> -->
		</div>
	`
};