import {Type, Event} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

import {EventType} from 'im.v2.const';

export const windowFunctions = {
	showWindow()
	{
		BXDesktopSystem?.SetActiveTab();
		window.BXDesktopWindow?.ExecuteCommand('show');
	},
	hideWindow()
	{
		window.BXDesktopWindow?.ExecuteCommand('hide');
	},
	closeWindow()
	{
		window.BXDesktopWindow?.ExecuteCommand('close');
	},
	closeWindowByName(name: string = '')
	{
		const window = this.findWindow(name);
		if (!window)
		{
			return;
		}

		window.BXDesktopWindow?.ExecuteCommand('close');
	},
	reloadWindow()
	{
		const event = new BaseEvent();
		EventEmitter.emit(window, EventType.desktop.onReload, event);
		location.reload();
	},
	findWindow(name: string = ''): ?Window
	{
		const mainWindow = opener ?? top;

		return mainWindow.BXWindows.find(window => window?.name === name);
	},
	createWindow(name: string, callback: Function)
	{
		BXDesktopSystem.GetWindow(name, callback);
	},
	createTopmostWindow(htmlContent: string)
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
	prepareHtml(html: string | HTMLElement, js: string | HTMLElement)
	{
		if (Type.isDomNode(html))
		{
			html = html.outerHTML;
		}

		if (Type.isDomNode(js))
		{
			js = js.outerHTML;
		}

		Event.ready();

		if (Type.isStringFilled(js))
		{
			js = `
				<script type="text/javascript">
					BX.ready(() => {
						${js}
					});
				</script>
			`;
		}

		return `
			<!DOCTYPE html>
			<html>
				<body class="im-desktop im-desktop-popup">
					${html}${js}
				</body>
			</html>
		`;
	}
};