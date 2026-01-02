export const PageObject = {
	getRootWindow(): Window
	{
		return PageObject.getTopWindowOfCurrentHost(window);
	},

	isCrossOriginObject(currentWindow): boolean
	{
		try
		{
			void currentWindow.location.host;
		}
		catch
		{
			// cross-origin object
			return true;
		}

		return false;
	},

	getTopWindowOfCurrentHost(currentWindow): Window
	{
		if (
			!PageObject.isCrossOriginObject(currentWindow.parent)
			&& currentWindow.parent !== currentWindow
			&& currentWindow.parent.location.host === currentWindow.location.host
		)
		{
			return PageObject.getTopWindowOfCurrentHost(currentWindow.parent);
		}

		return currentWindow;
	},

	getParentWindowOfCurrentHost(currentWindow): Window
	{
		if (PageObject.isCrossOriginObject(currentWindow.parent))
		{
			return currentWindow;
		}

		return currentWindow.parent;
	},
};
