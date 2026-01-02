import { Dom, Event, Extension, Type } from 'main.core';

import { Path, WINDOW_ACTIVATION_DELAY } from 'im.v2.const';
import { Utils } from 'im.v2.lib.utils';

import { settingsFunctions } from './settings';

type TabsList = {
	url: string,
	height: number,
	width: number,
	id: string,
	inMainWindow: boolean,
	popup: boolean,
	windowClass: string
}

export const windowFunctions = {
	wait(ms: number): Promise
	{
		return new Promise((resolve) => {
			setTimeout(resolve, ms);
		});
	},
	async handlePortalTabActivation(): Promise
	{
		const hasActiveTab = await this.hasActivePortalTab();

		if (hasActiveTab)
		{
			return Promise.resolve();
		}

		this.activatePortalFirstTab();

		return Promise.resolve();
	},
	activatePortalFirstTab()
	{
		BXDesktopSystem.ActivateFirstTab();
	},
	hasActivePortalTab(): Promise
	{
		return BXDesktopSystem.HasActiveTab();
	},
	async setTabWithChatPageActive(): Promise<void>
	{
		this.setActiveTabUrl(`${location.origin}${Path.online}`);
		await this.wait(WINDOW_ACTIVATION_DELAY);
	},
	shouldActivateTabWithChatPage(): boolean
	{
		const tabsList = this.getTabsList();

		const tabsWithChatPage = tabsList.filter((tab) => tab.url.includes(Path.online));

		const hasTabsWithVisibleChatPage = tabsWithChatPage.some((tab) => tab.visible);

		return tabsWithChatPage.length > 0 && !hasTabsWithVisibleChatPage;
	},
	getTabsList(): TabsList[]
	{
		return BXDesktopSystem.BrowserList();
	},
	isTwoWindowMode(): boolean
	{
		return Boolean(BXDesktopSystem?.IsTwoWindowsMode());
	},
	isChatWindow(): boolean
	{
		const settings = Extension.getSettings('im.v2.lib.desktop-api');

		return (
			this.isDesktop()
			&& settings.get('isChatWindow')
		);
	},
	isChatTab(): boolean
	{
		return (
			this.isChatWindow()
			|| (
				this.isDesktop()
				&& location.href.includes('&IM_TAB=Y')
			)
		);
	},
	isActiveTab(): boolean
	{
		return (
			this.isDesktop()
			&& BXDesktopSystem.IsActiveTab()
		);
	},
	async showBrowserWindow()
	{
		BXDesktopWindow.ExecuteCommand('show.main');
		await this.wait(WINDOW_ACTIVATION_DELAY);
	},
	setActiveTab(target = window)
	{
		if (!Type.isObject(target))
		{
			return;
		}
		target.BXDesktopSystem?.SetActiveTab();
	},
	setActiveTabUrl(url: string)
	{
		BXDesktopSystem.SetActiveTabUrl(url);
	},
	showWindow(target = window)
	{
		if (!Type.isObject(target))
		{
			return;
		}
		target.BXDesktopWindow?.ExecuteCommand('show');
	},
	activateWindow(target = window)
	{
		this.setActiveTab(target);
		this.showWindow(target);
	},
	hideWindow(target = window)
	{
		if (!Type.isObject(target))
		{
			return;
		}
		target.BXDesktopWindow?.ExecuteCommand('hide');
	},
	closeWindow(target = window)
	{
		if (!Type.isObject(target))
		{
			return;
		}
		target.BXDesktopWindow?.ExecuteCommand('close');
	},
	hideLoader()
	{
		Dom.remove(document.getElementById('bx-desktop-loader'));
	},
	reloadWindow()
	{
		BXDesktopSystem.Login({});
	},
	findWindow(name: string = ''): ?Window
	{
		const mainWindow = opener || top;

		return mainWindow.BXWindows.find((window) => window?.name === name);
	},
	openPage(url: string, options: { skipNativeBrowser?: boolean } = {}): Promise
	{
		const targetUrl = new URL(url);
		if (targetUrl.host !== location.host)
		{
			setTimeout(() => this.hideWindow(), 100);

			return Promise.resolve(false);
		}

		if (!settingsFunctions.isTwoWindowMode())
		{
			if (options.skipNativeBrowser === true)
			{
				setTimeout(() => this.hideWindow(), 100);

				return Promise.resolve(false);
			}

			Utils.browser.openLink(targetUrl.href);

			// workaround timeout, if application is activated on hit, it cant be hidden immediately
			setTimeout(() => this.hideWindow(), 100);

			return Promise.resolve(true);
		}

		this.createTab(targetUrl.href);

		return Promise.resolve(true);
	},
	openInBrowser(url: string)
	{
		BXDesktopSystem.OpenInBrowser(url);
	},
	createTab(path: string): void
	{
		const preparedPath = Dom.create({ tag: 'a', attrs: { href: path } }).href;

		BXDesktopSystem.CreateTab(preparedPath);
	},
	createImTab(path: string): void
	{
		const preparedPath = Dom.create({ tag: 'a', attrs: { href: path } }).href;

		BXDesktopSystem.CreateImTab(preparedPath);
	},
	createWindow(name: string, callback: Function)
	{
		BXDesktopSystem.GetWindow(name, callback);
	},
	createTopmostWindow(htmlContent: string): boolean
	{
		return BXDesktopSystem.ExecuteCommand('topmost.show.html', htmlContent);
	},
	setWindowPosition(rawParams: {x?: number, y?: number, width?: number, height?: number})
	{
		const preparedParams = {};
		Object.entries(rawParams).forEach(([key, value]) => {
			const preparedKey = key[0].toUpperCase() + key.slice(1);
			preparedParams[preparedKey] = value;
		});
		BXDesktopWindow?.SetProperty('position', preparedParams);
	},
	prepareHtml(html: string | HTMLElement, js: string | HTMLElement): string
	{
		let plainHtml = '';
		if (Type.isDomNode(html))
		{
			plainHtml = html.outerHTML;
		}
		else
		{
			plainHtml = html;
		}

		let plainJs = '';
		if (Type.isDomNode(js))
		{
			plainJs = js.outerHTML;
		}
		else
		{
			plainJs = js;
		}

		Event.ready();

		if (Type.isStringFilled(plainJs))
		{
			plainJs = `
				<script>
					BX.ready(() => {
						${plainJs}
					});
				</script>
			`;
		}

		const head = document.head.outerHTML.replaceAll(/BX\.PULL\.start\([^)]*\);/g, '');

		return `
			<!DOCTYPE html>
			<html lang="">
				${head}
				<body class="im-desktop im-desktop-popup">
					${plainHtml}${plainJs}
				</body>
			</html>
		`;
	},
	setWindowSize(width: number, height: number)
	{
		BXDesktopWindow.SetProperty('clientSize', { Width: width, Height: height });
	},
	setMinimumWindowSize(width: number, height: number)
	{
		BXDesktopWindow.SetProperty('minClientSize', { Width: width, Height: height });
	},
};
