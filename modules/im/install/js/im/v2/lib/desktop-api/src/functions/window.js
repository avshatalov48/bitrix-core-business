import { settingsFunctions } from './settings';
import { Utils } from 'im.v2.lib.utils';
import { Type, Event, Dom, Extension } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { EventType } from 'im.v2.const';

export const windowFunctions = {
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
	setActiveTab(target = window)
	{
		if (!Type.isObject(target))
		{
			return;
		}
		target.BXDesktopSystem?.SetActiveTab();
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
		const event = new BaseEvent();
		EventEmitter.emit(window, EventType.desktop.onReload, event);

		location.reload();
	},
	findWindow(name: string = ''): ?Window
	{
		const mainWindow = opener || top;

		return mainWindow.BXWindows.find((window) => window?.name === name);
	},
	openPage(url: string, options: { skipNativeBrowser?: boolean } = {}): Promise
	{
		const anchorElement: HTMLAnchorElement = Dom.create({ tag: 'a', attrs: { href: url } });
		if (anchorElement.host !== location.host)
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

			Utils.browser.openLink(anchorElement.href);

			// workaround timeout, if application is activated on hit, it cant be hidden immediately
			setTimeout(() => this.hideWindow(), 100);

			return Promise.resolve(true);
		}

		this.createTab(anchorElement.href);

		return Promise.resolve(true);
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
