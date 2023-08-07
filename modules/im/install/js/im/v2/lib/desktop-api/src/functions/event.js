import {Event} from 'main.core';

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

		Event.bind(window, eventName, preparedHandler);
	},
	unsubscribe(eventName: string, handler: Function)
	{
		Event.unbind(window, eventName, handler);
	},
	emit(eventName: string, params: any[] = [])
	{
		BXDesktopWindow?.DispatchCustomEvent(eventName, params);
	},
	emitToMainWindow(eventName: string, params: any[] = [])
	{
		BXDesktopSystem?.GetMainWindow()?.DispatchCustomEvent(eventName, params);
	}
};