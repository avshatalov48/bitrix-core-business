import 'pull.client';

import PullHandler from './pull-handler';
import DesktopHelper from './helpers/desktop';
import BrowserHelper from './helpers/browser';
import Notification from './notification/notification';
import NotificationEvent from './notification/notification-event';

import BaseProvider from './providers/base';
import MacProvider from './providers/mac';
import WindowsProvider from './providers/windows';
import LinuxProvider from './providers/linux';
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
	static PROVIDER_OPTIONS: ProviderOptions = {
		eventNamespace: Notifier.EVENT_NAMESPACE,
	};

	constructor()
	{
		this.provider = this.createProvider();
		BX.PULL?.subscribe(new PullHandler());
	}

	createProvider(): BaseProvider
	{
		if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isMac() && DesktopHelper.geApiVersion() >= 73)
		{
			return new MacProvider(Notifier.PROVIDER_OPTIONS);
		}

		if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isWindows())
		{
			return new WindowsProvider(Notifier.PROVIDER_OPTIONS);
		}

		if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isLinux())
		{
			return new LinuxProvider(Notifier.PROVIDER_OPTIONS);
		}

		if (BrowserHelper.isSupportedBrowser() && BrowserHelper.isNativeNotificationAllowed())
		{
			return new BrowserProvider(Notifier.PROVIDER_OPTIONS);
		}

		return this.#getBrowserPageProvider();
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

	notifyViaBrowserProvider(notificationOptions: NotificationOptions)
	{
		const notification = new Notification(notificationOptions);

		this.#getBrowserPageProvider().notify(notification);
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

	subscribe(eventName: string, handler: function): void
	{
		if (!NotificationEvent.isSupported(eventName))
		{
			throw new Error(`NotificationManager: event "${eventName}" is not supported.`);
		}

		this.provider.subscribe(eventName, handler);
		if (this.provider !== this.#getBrowserPageProvider())
		{
			this.#getBrowserPageProvider().subscribe(eventName, handler);
		}
	}

	#getBrowserPageProvider(): BrowserPageProvider
	{
		if (!this.browserProvider)
		{
			this.browserProvider = new BrowserPageProvider(Notifier.PROVIDER_OPTIONS);
		}

		return this.browserProvider;
	}
}

const notifier = new Notifier();

export {notifier as Notifier};
