const themesMap = new Map();
themesMap.set(
	'business-light',
	{
		theme: 'business-light',
		dark: false,
		style: '',
		color: {
			primary: '#0f58d0ff',
			primaryText: '#ffffffff',
			background: '#ffffffff',
			text: '#000000ff',
			fieldBackground: '#00000011',
			fieldFocusBackground: '#ffffffff',
			fieldBorder: '#00000016',
		},
		shadow: true,
		font: {
			uri: '',
			family: '',
		},
		border: {
			left: false,
			top: false,
			bottom: true,
			right: false,
		},
	},
);

themesMap.set(
	'business-dark',
	{
		theme: 'business-dark',
		dark: true,
		style: '',
		color: {
			primary: '#0f58d0ff',
			primaryText: '#ffffffff',
			background: '#282d30ff',
			text: '#ffffffff',
			fieldBackground: '#ffffff11',
			fieldFocusBackground: '#00000028',
			fieldBorder: '#ffffff16',
		},
		shadow: true,
		font: {
			uri: '',
			family: '',
		},
		border: {
			left: false,
			top: false,
			bottom: true,
			right: false,
		},
	},
);

themesMap.set(
	'modern-light',
	{
		theme: 'modern-light',
		dark: false,
		style: 'modern',
		color: {
			primary: '#ffd110ff',
			primaryText: '#000000ff',
			background: '#ffffffff',
			text: '#000000ff',
			fieldBackground: '#00000000',
			fieldFocusBackground: '#00000000',
			fieldBorder: '#00000011',
		},
		shadow: true,
		font: {
			uri: 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap&subset=cyrillic',
			family: 'Open Sans',
		},
		border: {
			left: false,
			top: false,
			bottom: true,
			right: false,
		},
	},
);

themesMap.set(
	'modern-dark',
	{
		theme: 'modern-dark',
		dark: true,
		style: 'modern',
		color: {
			primary: '#ffd110ff',
			primaryText: '#000000ff',
			background: '#282d30ff',
			text: '#ffffffff',
			fieldBackground: '#00000000',
			fieldFocusBackground: '#00000000',
			fieldBorder: '#ffffff11',
		},
		shadow: true,
		font: {
			uri: 'https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600&display=swap&subset=cyrillic',
			family: 'Open Sans',
		},
		border: {
			left: false,
			top: false,
			bottom: true,
			right: false,
		},
	},
);

themesMap.set(
	'classic-light',
	{
		theme: 'classic-light',
		dark: false,
		style: '',
		color: {
			primary: '#000000ff',
			primaryText: '#ffffffff',
			background: '#ffffffff',
			text: '#000000ff',
			fieldBackground: '#00000011',
			fieldFocusBackground: '#0000000a',
			fieldBorder: '#00000011',
		},
		shadow: true,
		font: {
			uri: 'https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&display=swap&subset=cyrillic',
			family: 'PT Serif',
		},
		border: {
			left: false,
			top: false,
			bottom: true,
			right: false,
		},
	},
);

themesMap.set(
	'classic-dark',
	{
		theme: 'classic-dark',
		dark: true,
		style: '',
		color: {
			primary: '#ffffffff',
			primaryText: '#000000ff',
			background: '#000000ff',
			text: '#ffffffff',
			fieldBackground: '#ffffff11',
			fieldFocusBackground: '#ffffff0a',
			fieldBorder: '#ffffff11',
		},
		shadow: true,
		font: {
			uri: 'https://fonts.googleapis.com/css2?family=PT+Serif:wght@400;700&display=swap&subset=cyrillic',
			family: 'PT Serif',
		},
		border: {
			left: false,
			top: false,
			bottom: true,
			right: false,
		},
	},
);

themesMap.set(
	'fun-light',
	{
		theme: 'fun-light',
		dark: false,
		style: '',
		color: {
			primary: '#f09b22ff',
			primaryText: '#000000ff',
			background: '#ffffffff',
			text: '#000000ff',
			fieldBackground: '#f09b2211',
			fieldFocusBackground: '#0000000a',
			fieldBorder: '#00000011',
		},
		shadow: true,
		font: {
			uri: 'https://fonts.googleapis.com/css2?family=Pangolin&display=swap&subset=cyrillic',
			family: 'Pangolin',
		},
		border: {
			left: false,
			top: false,
			bottom: true,
			right: false,
		},
	},
);

themesMap.set(
	'fun-dark',
	{
		theme: 'fun-dark',
		dark: true,
		style: '',
		color: {
			primary: '#f09b22ff',
			primaryText: '#000000ff',
			background: '#221400ff',
			text: '#ffffffff',
			fieldBackground: '#f09b2211',
			fieldFocusBackground: '#ffffff0a',
			fieldBorder: '#f09b220a',
		},
		shadow: true,
		font: {
			uri: 'https://fonts.googleapis.com/css2?family=Pangolin&display=swap&subset=cyrillic',
			family: 'Pangolin',
		},
		border: {left: false, top: false, bottom: true, right: false},
	},
);

themesMap.set(
	'pixel-light',
	{
		theme: 'pixel-light',
		dark: true,
		style: '',
		color: {
			primary: '#00a74cff',
			primaryText: '#ffffffff',
			background: '#282d30ff',
			text: '#90ee90ff',
			fieldBackground: '#ffffff11',
			fieldFocusBackground: '#00000028',
			fieldBorder: '#ffffff16',
		},
		shadow: true,
		font: {
			uri: 'https://fonts.googleapis.com/css2?family=Press+Start+2P&display=swap&subset=cyrillic',
			family: 'Press Start 2P',
		},
		border: {left: false, top: false, bottom: true, right: false},
	},
);

themesMap.set(
	'pixel-dark',
	{
		...themesMap.get('pixel-light'),
		theme: 'pixel-dark',
	},
);

export default themesMap;