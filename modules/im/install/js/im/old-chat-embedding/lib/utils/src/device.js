import {Type} from 'main.core';

const UA = navigator.userAgent.toLowerCase();

export const DeviceUtil = {

	isDesktop(): boolean
	{
		return !this.isMobile();
	},

	isMobile(): boolean
	{
		if (!Type.isUndefined(this.isMobileStatic))
		{
			return this.isMobileStatic;
		}

		this.isMobileStatic = (
			UA.includes('android')
			|| UA.includes('webos')
			|| UA.includes('iphone')
			|| UA.includes('ipad')
			|| UA.includes('ipod')
			|| UA.includes('blackberry')
			|| UA.includes('windows phone')
		);

		return this.isMobileStatic;
	},

	orientationHorizontal: 'horizontal',
	orientationPortrait: 'portrait',

	getOrientation(): string
	{
		if (!this.isMobile())
		{
			return this.orientationHorizontal;
		}

		return Math.abs(window.orientation) === 0? this.orientationPortrait: this.orientationHorizontal;
	},

};