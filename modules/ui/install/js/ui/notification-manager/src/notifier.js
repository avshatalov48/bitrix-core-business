import { PULL as Pull } from 'pull.client';

import PullHandler from './pull-handler';
import DesktopHelper from './helpers/desktop';
import BrowserHelper from './helpers/browser';
import Notification from './notification/notification';
import NotificationEvent from './notification/notification-event';

import BaseProvider from './providers/base';
import MacProvider from './providers/mac';
import WindowsProvider from './providers/windows';
import BrowserProvider from './providers/browser';
import BrowserPageProvider from './providers/browser-page';

import type { NotificationOptions } from './notification/notification-options';
import type { ProviderOptions } from './providers/provider-options';

/**
 * @memberof BX.UI.NotificationManager
 */
class Notifier
{
	static EVENT_NAMESPACE: string = 'BX.UI.NotificationManager';

	constructor()
	{
		this.provider = this.createProvider();

		Pull.subscribe(new PullHandler());
	}

	createProvider(): BaseProvider
	{
		const providerOptions: ProviderOptions = {
			eventNamespace: Notifier.EVENT_NAMESPACE,
		};

		if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isMac())
		{
			return new MacProvider(providerOptions);
		}

		if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isWindows())
		{
			return new WindowsProvider(providerOptions);
		}

		if (BrowserHelper.isSupportedBrowser() && BrowserHelper.isNativeNotificationAllowed())
		{
			return new BrowserProvider(providerOptions);
		}

		return new BrowserPageProvider(providerOptions);
	}

	notify(notificationOptions: NotificationOptions): void
	{
		const notification = new Notification(notificationOptions);

		this.sendNotification(notification);
	}

	sendNotification(notification: Notification): void
	{
		this.provider.notify(notification);
	}

	subscribe(eventName: string, handler: function): void
	{
		if (!NotificationEvent.isSupported(eventName))
		{
			throw new Error(`NotificationManager: event "${eventName}" is not supported.`);
		}

		this.provider.subscribe(eventName, handler);
	}

	notifyViaDesktopProvider(notification: NotificationOptions)
	{
		if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isMac())
		{
			new MacProvider().notify(notification);
			return;
		}

		if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isMac())
		{
			new WindowsProvider().notify(notification);
			return;
		}

		throw new Error(`NotificationManager: unsupported environment for sending through a desktop provider.`);
	}
}

const notifier = new Notifier();

export {notifier as Notifier};
