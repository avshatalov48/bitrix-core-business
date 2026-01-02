import { Type } from 'main.core';

import { Core } from 'im.v2.application.core';
import { ChatType, Settings } from 'im.v2.const';

import {
	SelectableBackground,
	SpecialBackground,
	SpecialBackgroundId,
	ThemeType,
	ImageFileByBackgroundId,
} from './color-scheme';

import type { BackgroundItem } from './color-scheme';

export { SelectableBackground, SpecialBackgroundId as SpecialBackground, ThemeType, ThemeManager };

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
		const selectedColorScheme: BackgroundItem = SelectableBackground[selectedBackgroundId];

		return selectedColorScheme?.type === ThemeType.light;
	},

	isDarkTheme(): boolean
	{
		const selectedBackgroundId = Core.getStore().getters['application/settings/get'](Settings.appearance.background);
		const selectedColorScheme: BackgroundItem = SelectableBackground[selectedBackgroundId];

		return selectedColorScheme?.type === ThemeType.dark;
	},

	getCurrentBackgroundStyle(dialogId?: string): BackgroundStyle
	{
		const backgroundId = resolveBackgroundId(dialogId);

		return this.getBackgroundStyleById(backgroundId);
	},

	getBackgroundStyleById(backgroundId: string | number): BackgroundStyle
	{
		const backgroundsList = { ...SelectableBackground, ...SpecialBackground };
		const colorScheme: BackgroundItem = backgroundsList[backgroundId];
		if (!colorScheme)
		{
			return {};
		}

		const patternColor = colorScheme.type === ThemeType.light
			? BackgroundPatternColor.gray
			: BackgroundPatternColor.white
		;
		const patternType = colorScheme.pattern;

		const fileName = ImageFileByBackgroundId[backgroundId] ?? backgroundId;
		const patternImage = `url('${IMAGE_FOLDER_PATH}/pattern-${patternColor}-${patternType}.svg')`;
		const highlightImage = `url('${IMAGE_FOLDER_PATH}/${fileName}.png')`;

		return {
			backgroundColor: colorScheme.color,
			backgroundImage: `${patternImage}, ${highlightImage}`,
			backgroundPosition: 'top right, center',
			backgroundRepeat: 'repeat, no-repeat',
			backgroundSize: 'auto, cover',
		};
	},
};

/** Background selection priority:
 * 1. If there is no dialog context: user selected background (from user settings)
 * 2. Background by chat type (collab/copilot/aiAssistant)
 * 3. Chat background (from chat fields)
 * 4. Bot background (from bot fields)
 * 5. User selected background (from user settings)
 */
const resolveBackgroundId = (dialogId?: string): string => {
	const userBackground = Core.getStore().getters['application/settings/get'](Settings.appearance.background);
	if (!Type.isStringFilled(dialogId))
	{
		return userBackground;
	}

	const chatType = Core.getStore().getters['chats/get'](dialogId, true).type;
	if (chatType === ChatType.collab)
	{
		return SpecialBackgroundId.collab;
	}

	if (chatType === ChatType.copilot)
	{
		return SpecialBackgroundId.copilot;
	}

	const isAiAssistant = Core.getStore().getters['users/bots/isAiAssistant'](dialogId);
	if (isAiAssistant)
	{
		return SpecialBackgroundId.martaAI;
	}

	const chatBackground = Core.getStore().getters['chats/getBackgroundId'](dialogId);
	const botBackground = Core.getStore().getters['users/bots/getBackgroundId'](dialogId);
	if (SpecialBackgroundId[chatBackground])
	{
		return SpecialBackgroundId[chatBackground];
	}

	if (SpecialBackgroundId[botBackground])
	{
		return SpecialBackgroundId[botBackground];
	}

	return userBackground;
};
