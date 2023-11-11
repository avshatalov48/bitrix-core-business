import { Event, Type } from 'main.core';

const eventHandlers = {};

export const eventFunctions = {
	subscribe(eventName: string, handler: Function)
	{
		if (!this.isDesktop())
		{
			return;
		}

		const preparedHandler = (event) => {
			const params = event.detail ?? [];
			handler.apply(window, params);
		};

		if (!eventHandlers[eventName])
		{
			eventHandlers[eventName] = [];
		}
		eventHandlers[eventName].push(preparedHandler);

		Event.bind(window, eventName, preparedHandler);
	},
	unsubscribe(eventName: string, handler: Function)
	{
		if (!Type.isFunction(handler))
		{
			if (!Type.isArrayFilled(eventHandlers[eventName]))
			{
				return;
			}

			eventHandlers[eventName].forEach((eventHandler) => {
				Event.unbind(window, eventName, eventHandler);
			});

			return;
		}

		Event.unbind(window, eventName, handler);
	},
	emit(eventName: string, params: any[] = [])
	{
		const mainWindow = opener || top;
		const allWindows: Object[] = mainWindow.BXWindows;
		allWindows.forEach((window) => {
			if (!window || window.name === '')
			{
				return;
			}

			window?.BXDesktopWindow?.DispatchCustomEvent(eventName, params);
		});

		this.emitToMainWindow(eventName, params);
	},
	emitToMainWindow(eventName: string, params: any[] = [])
	{
		const mainWindow = opener || top;
		mainWindow.BXDesktopSystem?.GetMainWindow()?.DispatchCustomEvent(eventName, params);
	},
};
