export default class NotificationCloseReason
{
	static CLOSED_BY_USER: string = 'closed_by_user';
	static EXPIRED: string = 'expired';

	static getTypes(): Array<string>
	{
		return [
			NotificationCloseReason.CLOSED_BY_USER,
			NotificationCloseReason.EXPIRED,
		];
	}

	static isSupported(closeReason: string): boolean
	{
		return NotificationCloseReason.getTypes().includes(closeReason);
	}
}
