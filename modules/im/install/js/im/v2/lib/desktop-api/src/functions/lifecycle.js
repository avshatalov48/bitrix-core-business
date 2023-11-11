import { Type } from 'main.core';
export const lifecycleFunctions = {
	isDesktop(): boolean
	{
		return Type.isObject(window.BXDesktopSystem);
	},
	restart()
	{
		if (this.getApiVersion() < 74)
		{
			return;
		}

		BXDesktopSystem?.Restart();
	},
	shutdown()
	{
		BXDesktopSystem?.Shutdown();
	},
};
