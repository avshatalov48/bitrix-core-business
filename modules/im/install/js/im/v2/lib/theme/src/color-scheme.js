export const ThemeType = Object.freeze({
	light: 'light',
	dark: 'dark',
});

export const SelectableBackground = Object.freeze({
	// dark ones
	1: {
		color: '#9fcfff',
		type: ThemeType.dark,
	},
	2: {
		color: '#81d8bf',
		type: ThemeType.dark,
	},
	3: {
		color: '#7fadd1',
		type: ThemeType.dark,
	},
	4: {
		color: '#7a90b6',
		type: ThemeType.dark,
	},
	5: {
		color: '#5f9498',
		type: ThemeType.dark,
	},
	6: {
		color: '#799fe1',
		type: ThemeType.dark,
	},
	// light ones
	7: {
		color: '#cfeefa',
		type: ThemeType.light,
	},
	9: {
		color: '#efded3',
		type: ThemeType.light,
	},
	11: {
		color: '#eff4f6',
		type: ThemeType.light,
	},
});

export const SpecialBackgroundId = {
	collab: 'collab-v2',
};

export const SpecialBackground = {
	[SpecialBackgroundId.collab]: {
		color: '#76c68b',
		type: ThemeType.dark,
	},
};

export type BackgroundItem = {
	color: string,
	type: $Values<typeof ThemeType>
};
