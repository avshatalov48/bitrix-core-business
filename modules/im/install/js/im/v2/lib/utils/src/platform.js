import {Type, Browser} from 'main.core';

const UA = navigator.userAgent.toLowerCase();

export const PlatformUtil = {

	isMac(): boolean
	{
		return Browser.isMac();
	},
	isLinux(): boolean
	{
		return Browser.isLinux();
	},
	isWindows(): boolean
	{
		return Browser.isWin() || (!this.isMac() && !this.isLinux());
	},
	isBitrixMobile(): boolean
	{
		return UA.includes('bitrixmobile');
	},
	isBitrixDesktop(): boolean
	{
		return UA.includes('bitrixdesktop');
	},
	getDesktopVersion(): number
	{
		if (!Type.isUndefined(this.getDesktopVersionStatic))
		{
			return this.getDesktopVersionStatic;
		}

		if (Type.isUndefined(window.BXDesktopSystem))
		{
			return 0;
		}

		const version = window.BXDesktopSystem.GetProperty('versionParts');
		this.getDesktopVersionStatic = version[3];

		return this.getDesktopVersionStatic;
	},
	isDesktopFeatureEnabled(code: string)
	{
		if (!this.isBitrixDesktop() || !Type.isFunction(BXDesktopSystem.FeatureEnabled))
		{
			return false;
		}

		return !!BXDesktopSystem.FeatureEnabled(code);
	},
	isMobile(): boolean
	{
		return this.isAndroid() || this.isIos() || this.isBitrixMobile();
	},
	isIos(): boolean
	{
		return Browser.isIOS();
	},
	getIosVersion(): ?string
	{
		if (!this.isIos())
		{
			return null;
		}

		const matches = UA.match(/(iphone|ipad)(.+)(OS\s([0-9]+)([_.]([0-9]+))?)/i);
		if (!matches || !matches[4])
		{
			return null;
		}

		return parseFloat(matches[4]+'.'+(matches[6]? matches[6]: 0));
	},
	isAndroid(): boolean
	{
		return Browser.isAndroid();
	},
	openNewPage(url): boolean
	{
		if (!url)
		{
			return false;
		}

		if (this.isBitrixMobile())
		{
			const MobileTools = window.BX.MobileTools;
			if (Type.isUndefined())
			{
				const openWidget = MobileTools.resolveOpenFunction(url);
				if (openWidget)
				{
					openWidget();
					return true;
				}
			}
			window.app.openNewPage(url);

			return true;
		}

		window.open(url, '_blank');

		return true;
	}
};