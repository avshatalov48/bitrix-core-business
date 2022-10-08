import { Type } from 'main.core';

import Notification from './notification';

export default class PushNotification extends Notification
{
	setUid(id: string): void
	{
		if (!Type.isStringFilled(id))
		{
			throw new Error(`NotificationManager: Cannot create a notification without an ID`);
		}

		this.uid = id;
	}
}