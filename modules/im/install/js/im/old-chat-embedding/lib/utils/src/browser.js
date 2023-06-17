import {Type, Dom, Browser} from 'main.core';

const UA = navigator.userAgent.toLowerCase();

export const BrowserUtil = {

	isChrome(): boolean
	{
		return Browser.isChrome();
	},

	isFirefox(): boolean
	{
		return Browser.isFirefox();
	},

	isIe(): boolean
	{
		return Browser.isIE();
	},

	isSafari(): boolean
	{
		if (this.isChrome())
		{
			return false;
		}

		if (!UA.includes('safari'))
		{
			return false;
		}

		return !this.isSafariBased();
	},

	isSafariBased(): boolean
	{
		if (!UA.includes('applewebkit'))
		{
			return false;
		}

		return (
			UA.includes('yabrowser')
			|| UA.includes('yaapp_ios_browser')
			|| UA.includes('crios')
		);
	},

	findParent(item, findTag): ?HTMLElement
	{
		const isHtmlElement = findTag instanceof HTMLElement;

		if (
			!findTag
			|| (!Type.isString(findTag) && !isHtmlElement)
		)
		{
			return null;
		}

		for (; item && item !== document; item = item.parentNode)
		{
			if (Type.isString(findTag))
			{
				if (Dom.hasClass(findTag))
				{
					return item;
				}
			}
			else if (isHtmlElement && item === findTag)
			{
				return item;
			}
		}

		return null;
	},
	openLink(link, target = '_blank')
	{
		window.open(link, target, '', true);
		return true;
	}
};