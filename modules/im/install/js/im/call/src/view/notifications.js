const maximumNotifications = 5;

let instance;

export class NotificationManager
{
	constructor()
	{
		this.maxNotification = maximumNotifications;
		this.notifications = [];
	};

	static get Instance()
	{
		if (!instance)
		{
			instance = new NotificationManager()
		}

		return instance;
	}

	addNotification(notification)
	{
		notification.subscribe("onDestroy", () => this.onNotificationDestroy(notification));
		this.notifications.push(notification);

		if (this.notifications.length > this.maxNotification)
		{
			const firstNotification = this.notifications.shift();
			firstNotification.dismount();
		}
	};

	onNotificationDestroy(notification)
	{
		const index = this.notifications.indexOf(notification);

		if (index != -1)
		{
			this.notifications.splice(index, 1);
		}
	};
}