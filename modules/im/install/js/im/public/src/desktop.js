import { Dom, Extension, Reflection } from 'main.core';

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

		const anchorElement: HTMLAnchorElement = Dom.create({ tag: 'a', attrs: { href: url } });
		if (anchorElement.host !== location.host)
		{
			return Promise.resolve(false);
		}

		const skipNativeBrowser = Boolean(options.skipNativeBrowser);
		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');

		const isRedirectAllowed = await DesktopManager?.getInstance().checkForOpenBrowserPage();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().openPage(anchorElement.href, { skipNativeBrowser });
		}

		if (skipNativeBrowser === true)
		{
			return Promise.resolve(false);
		}

		window.open(anchorElement.href, '_blank');

		return Promise.resolve(true);
	}
}

export const desktop = new Desktop();
