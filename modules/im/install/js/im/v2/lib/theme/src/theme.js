import { Core } from 'im.v2.application.core';
import { Settings } from 'im.v2.const';
import { ThemeColorScheme, ThemeType } from './color-scheme';

import type { ThemeItem } from './color-scheme';
export type { ThemeItem } from './color-scheme';

const IMAGE_FOLDER_PATH = '/bitrix/js/im/images/chat-v2-background';

export type BackgroundStyle = {
	backgroundColor: string,
	backgroundImage: string,
	backgroundRepeat: string,
	backgroundSize: string
};

const BackgroundPatternColor = Object.freeze({
	white: 'white',
	gray: 'gray',
});

const ThemeManager = {
	isLightTheme(): boolean
	{
		const selectedBackgroundId = Core.getStore().getters['application/settings/get'](Settings.appearance.background);
		const selectedColorScheme: ThemeItem = ThemeColorScheme[selectedBackgroundId];

		return selectedColorScheme?.type === ThemeType.light;
	},

	isDarkTheme(): boolean
	{
		const selectedBackgroundId = Core.getStore().getters['application/settings/get'](Settings.appearance.background);
		const selectedColorScheme: ThemeItem = ThemeColorScheme[selectedBackgroundId];

		return selectedColorScheme?.type === ThemeType.dark;
	},

	getCurrentBackgroundStyle(): BackgroundStyle
	{
		const selectedBackgroundId = Core.getStore().getters['application/settings/get'](Settings.appearance.background);

		return this.getBackgroundStyleById(selectedBackgroundId);
	},

	getBackgroundStyleById(backgroundId: string | number): BackgroundStyle
	{
		const colorScheme: ThemeItem = ThemeColorScheme[backgroundId];
		if (!colorScheme)
		{
			return {};
		}

		const patternColor = colorScheme.type === ThemeType.light
			? BackgroundPatternColor.gray
			: BackgroundPatternColor.white
		;
		const patternImage = `url('${IMAGE_FOLDER_PATH}/pattern-${patternColor}.svg')`;
		const highlightImage = `url('${IMAGE_FOLDER_PATH}/${backgroundId}.png')`;

		return {
			backgroundColor: colorScheme.color,
			backgroundImage: `${patternImage}, ${highlightImage}`,
			backgroundPosition: 'top right, center',
			backgroundRepeat: 'repeat, no-repeat',
			backgroundSize: 'auto, cover',
		};
	},
};

export { ThemeColorScheme, ThemeType, ThemeManager };
