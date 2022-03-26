export class PageEventsManager
{
	_settings = {};

	constructor(settings)
	{
		this._settings = settings ? settings : {};
		this.eventHandlers = {};
	}

	registerEventHandler(eventName, eventHandler)
	{
		if (!this.eventHandlers[eventName])
			this.eventHandlers[eventName] = [];
		this.eventHandlers[eventName].push(eventHandler);
		BX.addCustomEvent(this, eventName, eventHandler);
	}

	fireEvent(eventName, eventParams)
	{
		BX.onCustomEvent(this, eventName, eventParams);
	}

	unregisterEventHandlers(eventName)
	{
		if (this.eventHandlers[eventName])
		{
			for (var i = 0; i < this.eventHandlers[eventName].length; i++)
			{
				BX.removeCustomEvent(this, eventName, this.eventHandlers[eventName][i]);
			}
			delete this.eventHandlers[eventName];
		}
	}
}