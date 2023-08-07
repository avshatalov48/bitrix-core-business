export const iconFunctions = {
	setCounter(counter: number, important: boolean = false)
	{
		const preparedCounter = counter.toString();
		BXDesktopSystem?.SetIconBadge(preparedCounter, important);
		BXDesktopSystem?.SetTabBadge(0, preparedCounter);
	},
	setIconStatus(status: string)
	{
		BXDesktopSystem?.SetIconStatus(status);
	},
	setOfflineIcon()
	{
		BXDesktopSystem?.SetIconStatus('offline');
	},
	setBrowserIconBadge(counter: string | number)
	{
		BXDesktopSystem?.SetBrowserIconBadge(counter.toString());
	},
};
