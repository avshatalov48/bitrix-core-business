export const ThemeType = Object.freeze({
	light: 'light',
	dark: 'dark',
});

export const ThemePattern = Object.freeze({
	default: 'default',
	aiAssistant: 'ai-assistant',
});

export const SelectableBackground = Object.freeze({
	// dark ones
	1: {
		color: '#9fcfff',
		type: ThemeType.dark,
		pattern: ThemePattern.default,
	},
	2: {
		color: '#81d8bf',
		type: ThemeType.dark,
		pattern: ThemePattern.default,
	},
	3: {
		color: '#7fadd1',
		type: ThemeType.dark,
		pattern: ThemePattern.default,
	},
	4: {
		color: '#7a90b6',
		type: ThemeType.dark,
		pattern: ThemePattern.default,
	},
	5: {
		color: '#5f9498',
		type: ThemeType.dark,
		pattern: ThemePattern.default,
	},
	6: {
		color: '#799fe1',
		type: ThemeType.dark,
		pattern: ThemePattern.default,
	},
	// light ones
	7: {
		color: '#cfeefa',
		type: ThemeType.light,
		pattern: ThemePattern.default,
	},
	9: {
		color: '#efded3',
		type: ThemeType.light,
		pattern: ThemePattern.default,
	},
	11: {
		color: '#eff4f6',
		type: ThemeType.light,
		pattern: ThemePattern.default,
	},
});

// should be synced with \Bitrix\Im\V2\Chat\Background\BackgroundId, can potentially be used externally
export const SpecialBackgroundId = {
	collab: 'collab',
	martaAI: 'martaAI',
	copilot: 'copilot',
};

const COPILOT_BACKGROUND_ID = 4;
export const SpecialBackground = {
	[SpecialBackgroundId.collab]: {
		color: '#76c68b',
		type: ThemeType.dark,
		pattern: ThemePattern.default,
	},
	[SpecialBackgroundId.martaAI]: {
		color: '#0277ff',
		type: ThemeType.dark,
		pattern: ThemePattern.aiAssistant,
	},
	[SpecialBackgroundId.copilot]: SelectableBackground[COPILOT_BACKGROUND_ID],
};

export const ImageFileByBackgroundId = {
	[SpecialBackgroundId.collab]: 'collab-v2',
	[SpecialBackgroundId.martaAI]: 'ai-assistant',
	[SpecialBackgroundId.copilot]: COPILOT_BACKGROUND_ID.toString(),
};

export type BackgroundItem = {
	color: string,
	type: $Values<typeof ThemeType>,
	pattern: $Values<typeof ThemePattern>,
};
