export default class BrowserHelper
{
	static isSupportedBrowser(): boolean
	{
		return BrowserHelper.isChrome() || BrowserHelper.isFirefox() || BrowserHelper.isSafari();
	}

	static isNativeNotificationAllowed(): boolean
	{
		return (
			window.Notification
			&& window.Notification.permission
			&& window.Notification.permission.toLowerCase() === 'granted'
		);
	}

	static isSafari(): boolean
	{
		if (BrowserHelper.isChrome())
		{
			return false;
		}

		if (!navigator.userAgent.toLowerCase().includes('safari'))
		{
			return false;
		}

		return !BrowserHelper.isSafariBased();
	}

	static isSafariBased(): boolean
	{
		if (!navigator.userAgent.toLowerCase().includes('applewebkit'))
		{
			return false;
		}

		return (
			navigator.userAgent.toLowerCase().includes('yabrowser')
			|| navigator.userAgent.toLowerCase().includes('yaapp_ios_browser')
			|| navigator.userAgent.toLowerCase().includes('crios')
		);
	}

	static isChrome(): boolean
	{
		return navigator.userAgent.toLowerCase().includes('chrome');
	}

	static isFirefox(): boolean
	{
		return navigator.userAgent.toLowerCase().includes('firefox');
	}
}
