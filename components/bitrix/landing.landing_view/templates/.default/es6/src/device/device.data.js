export type DeviceItem = {
	name: string,
	code: string,
	className: string,
	langCode?: string,
};

export const Devices = {
	defaultDevice: {
		tablet: 'iphone14pro',
		mobile: 'iphone14pro',
	},
	devices: {
		delimiter1: {
			code: 'delimiter',
			langCode: 'LANDING_PREVIEW_DEVICE_MOBILES',
		},
		iphone14pro: {
			name: 'iPhone 14 Pro',
			code: 'iphone14pro',
			className: '--iphone-14-pro',
			width: 393,
			height: 852
		},
		iPhoneXR: {
			name: 'iPhone XR',
			code: 'iPhoneXR',
			className: '--iphone-xr',
			width: 414,
			height: 896
		},
		iPhoneSE: {
			name: 'iPhone SE',
			code: 'iPhoneSE',
			className: '--iphone-se',
			width: 375,
			height: 667
		},
		SamsungGalaxyNote10: {
			name: 'Samsung Galaxy Note10',
			code: 'SamsungGalaxyNote10',
			className: '--samsung-galaxy-note10',
			width: 412,
			height: 896
		},
		SamsungGalaxyS8: {
			name: 'Samsung Galaxy S8+',
			code: 'SamsungGalaxyS8',
			className: '--samsung-galaxy-s8-plus',
			width: 360,
			height: 740
		},
		GooglePixel4: {
			name: 'Google Pixel 4',
			code: 'GooglePixel4',
			className: '--google-pixel-4',
			width: 353,
			height: 745
		},
		delimiter2: {
			code: 'delimiter',
			langCode: 'LANDING_PREVIEW_DEVICE_TABLETS',
		},
		iPad: {
			name: 'iPad',
			code: 'iPad',
			className: '--ipad',
			width: 810,
			height: 1080
		},
		iPadMini: {
			name: 'iPad Mini',
			code: 'iPadMini',
			className: '--ipad-mini',
			width: 744,
			height: 1133
		},
		SamsungGalaxyTabS8: {
			name: 'Samsung Galaxy Tab S8',
			code: 'SamsungGalaxyTabS8',
			className: '--samsung-galaxy-tab-s8',
			width: 800,
			height: 1280
		},
	}
}
