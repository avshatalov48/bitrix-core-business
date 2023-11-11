export const notificationFunctions = {
	removeNativeNotifications()
	{
		if (this.getApiVersion() < 74)
		{
			return;
		}

		BXDesktopSystem?.NotificationRemoveAll();
	},
};
