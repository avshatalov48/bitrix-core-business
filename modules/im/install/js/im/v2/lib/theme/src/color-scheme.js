export const ThemeType = Object.freeze({
	light: 'light',
	dark: 'dark'
});

export const ThemeFontColor = Object.freeze({
	white: '#fff',
	gray: 'gray'
});

export const ThemeColorScheme = Object.freeze({
	// dark ones
	1: {
		color: '#9fcfff',
		type: ThemeType.dark
	},
	2: {
		color: '#81d8bf',
		type: ThemeType.dark
	},
	3: {
		color: '#7fadd1',
		type: ThemeType.dark
	},
	4: {
		color: '#7a90b6',
		type: ThemeType.dark
	},
	5: {
		color: '#5f9498',
		type: ThemeType.dark
	},
	6: {
		color: '#799fe1',
		type: ThemeType.dark
	},
	// light ones
	7: {
		color: '#cfeefa',
		type: ThemeType.light
	},
	8: {
		color: '#c5ecde',
		type: ThemeType.light
	},
	9: {
		color: '#efded3',
		type: ThemeType.light
	},
	10: {
		color: '#dff0bc',
		type: ThemeType.light
	},
	11: {
		color: '#eff4f6',
		type: ThemeType.light
	},
	12: {
		color: '#f5f3e1',
		type: ThemeType.light
	}
});

export type ThemeItem = {
	color: string,
	type: $Values<typeof ThemeType>
};