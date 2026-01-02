import { Extension, Reflection } from 'main.core';

class Desktop
{
	constructor()
	{
		const settings = Extension.getSettings('im.public');
		this.v2enabled = settings.get('v2enabled', false);
	}

	async openPage(url: string, options: { skipNativeBrowser?: boolean } = {}): Promise
	{
		if (!this.v2enabled)
		{
			return Promise.resolve(false);
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');

		if (DesktopManager.isDesktop())
		{
			return Promise.resolve(true);
		}

		const targetUrl = new URL(url);
		if (targetUrl.host !== location.host)
		{
			return Promise.resolve(false);
		}

		const skipNativeBrowser = Boolean(options.skipNativeBrowser);

		const isRedirectAllowed = await DesktopManager?.getInstance().checkForOpenBrowserPage();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().openPage(targetUrl.href, { skipNativeBrowser });
		}

		if (skipNativeBrowser === true)
		{
			return Promise.resolve(false);
		}

		window.open(targetUrl.href, '_blank');

		return Promise.resolve(true);
	}
}

export const desktop = new Desktop();
