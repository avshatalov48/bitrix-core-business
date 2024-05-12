import {Type} from 'main.core';

export class Desktop
{
	clientVersion = 0;
	eventHandlers = {};
	htmlWrapperHead = null;

	addCustomEvent(eventName: string, eventHandler: Function): boolean
	{
		const realHandler = (event) =>
		{
			eventHandler.apply(window, [...Object.values(event.detail)]);
		};

		if (!this.eventHandlers[eventName])
		{
			this.eventHandlers[eventName] = [];
		}

		this.eventHandlers[eventName].push(realHandler);
		window.addEventListener(eventName, realHandler);

		return true;
	}

	removeCustomEvents(eventName: string): boolean
	{
		if (!this.eventHandlers[eventName])
		{
			return false;
		}

		this.eventHandlers[eventName].forEach(eventHandler =>
		{
			window.removeEventListener(eventName, eventHandler);
		});
		this.eventHandlers[eventName] = [];

		return true;
	}

	onCustomEvent(windowTarget, eventName: string, eventParams: Array<any>): boolean
	{
		if (arguments.length === 2)
		{
			eventParams = eventName
			eventName = windowTarget;
			windowTarget = 'all';
		}
		else if (arguments.length < 2)
		{
			return false;
		}

		const convertedEventParams = {...eventParams};

		if (windowTarget === 'all')
		{
			const mainWindow = opener? opener: top;
			mainWindow.BXWindows.forEach(windowItem => {
				if (
					windowItem &&
					windowItem.name !== '' &&
					windowItem.BXDesktopWindow &&
					windowItem.BXDesktopWindow.DispatchCustomEvent
				)
				{
					windowItem.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
				}
			});
			mainWindow.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
		}
		else if (Type.isObject(windowTarget) && windowTarget.hasOwnProperty("BXDesktopWindow"))
		{
			windowTarget.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
		}
		else
		{
			const existingWindow = this.findWindow(windowTarget);
			if (existingWindow)
			{
				existingWindow.BXDesktopWindow.DispatchCustomEvent(eventName, convertedEventParams);
			}
		}

		return true;
	}

	findWindow(name: string = 'main'): Object | void
	{
		const mainWindow = opener? opener: top;
		if (name === 'main')
		{
			return mainWindow;
		}
		else
		{
			return mainWindow.BXWindows.find(windowItem => {
				return windowItem.name === name;
			});
		}
	}

	setWindowResizable(enabled: boolean = true): boolean
	{
		BXDesktopWindow.SetProperty("resizable", enabled);

		return true;
	}

	setWindowClosable(enabled: boolean = true): boolean
	{
		BXDesktopWindow.SetProperty("closable", enabled);

		return true;
	}

	setWindowTitle(title: string): boolean
	{
		if (Type.isUndefined(title))
		{
			return false;
		}

		title = title.trim();
		if (title.length <= 0)
		{
			return false;
		}

		BXDesktopWindow.SetProperty("title", title);

		return true;
	}

	setWindowPosition(params: Object): boolean
	{
		BXDesktopWindow.SetProperty("position", params);

		return true;
	}

	setWindowMinSize(params: Object): boolean
	{
		if (!params.Width || !params.Height)
		{
			return false;
		}

		BXDesktopWindow.SetProperty("minClientSize", params);

		return true;
	}

	getHtmlPage(content, jsContent, initImJs, bodyClass: string = ''): string
	{
		if (window.BXIM)
		{
			return window.BXIM.desktop.getHtmlPage(content, jsContent, initImJs, bodyClass);
		}

		content = content || '';
		jsContent = jsContent || '';
		bodyClass = bodyClass || '';

		if (Type.isDomNode(content))
		{
			content = content.outerHTML;
		}

		if (Type.isDomNode(jsContent))
		{
			jsContent = jsContent.outerHTML;
		}

		if (jsContent !== '')
		{
			jsContent = '<script>BX.ready(function(){'+jsContent+'});</script>';
		}

		if (this.isPopupPageLoaded())
		{
			return '<div class="im-desktop im-desktop-popup '+bodyClass+'">'+content+jsContent+'</div>';
		}
		else
		{
			if (this.htmlWrapperHead == null)
			{
				this.htmlWrapperHead = document.head.outerHTML.replace(/BX\.PULL\.start\([^)]*\);/g, '');
			}

			return '<!DOCTYPE html><html>'+this.htmlWrapperHead+'<body class="im-desktop im-desktop-popup '+bodyClass+'">'+content+jsContent+'</body></html>';
		}
	}

	isPopupPageLoaded(): boolean
	{
		if (!this.enableInVersion(45))
		{
			return false;
		}

		if (window.BXIM && !window.BXIM.isUtfMode)
		{
			return false;
		}

		if (!BXInternals)
		{
			return false;
		}

		if (!BXInternals.PopupTemplate)
		{
			return false;
		}

		if (BXInternals.PopupTemplate === '#PLACEHOLDER#')
		{
			return false;
		}

		return true;
	}

	enableInVersion(version: number)
	{
		if (Type.isUndefined(BXDesktopSystem))
		{
			return false;
		}

		return this.getApiVersion() >= parseInt(version);
	}

	getApiVersion(): number
	{
		if (Type.isUndefined(BXDesktopSystem))
		{
			return 0;
		}

		if (!this.clientVersion)
		{
			this.clientVersion = BXDesktopSystem.GetProperty('versionParts');
		}

		return this.clientVersion[3];
	}

	isReady()
	{
		return typeof(BXDesktopSystem) != "undefined";
	}
}