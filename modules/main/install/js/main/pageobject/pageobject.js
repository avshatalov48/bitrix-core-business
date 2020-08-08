window.BX = BX || {};

BX.PageObject = {
	getRootWindow: function()
	{
		return BX.PageObject.getTopWindowOfCurrentHost(window);
	},

	isCrossOriginObject: function(currentWindow)
	{
		try
		{
			void currentWindow.location.host;
		}
		catch (e)
		{
			// cross-origin object
			return true;
		}

		return false;
	},

	getTopWindowOfCurrentHost: function(currentWindow)
	{
		if (
			!BX.PageObject.isCrossOriginObject(currentWindow.parent)
			&& currentWindow.parent !== currentWindow
			&& currentWindow.parent.location.host === currentWindow.location.host
		)
		{
			return BX.PageObject.getTopWindowOfCurrentHost(currentWindow.parent);
		}

		return currentWindow;
	},

	getParentWindowOfCurrentHost: function(currentWindow)
	{
		if (BX.PageObject.isCrossOriginObject(currentWindow.parent))
		{
			return currentWindow;
		}

		return currentWindow.parent;
	}
};