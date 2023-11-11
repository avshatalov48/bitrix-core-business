import { Browser } from 'main.core';

export const iconFunctions = {
	setCounter(counter: number, important: boolean = false)
	{
		const preparedCounter = counter.toString();
		BXDesktopSystem?.SetIconBadge(preparedCounter, important);
		BXDesktopSystem?.SetTabBadge(0, preparedCounter);
	},
	setBrowserIconBadge(counter: string | number)
	{
		BXDesktopSystem?.SetBrowserIconBadge(counter.toString());
	},
	setIconStatus(status: string)
	{
		BXDesktopSystem?.SetIconStatus(status);
	},
	setOfflineIcon()
	{
		BXDesktopSystem?.SetIconStatus('offline');
	},
	flashIcon()
	{
		if (!Browser.isWin())
		{
			return;
		}

		BXDesktopSystem?.FlashIcon();
	},
};
