export default class NotificationAction
{
	static BUTTON_1: string = 'button_1';
	static BUTTON_2: string = 'button_2';
	static USER_INPUT: string = 'user_input';

	static getTypes(): Array<string>
	{
		return [
			NotificationAction.BUTTON_1,
			NotificationAction.BUTTON_2,
			NotificationAction.USER_INPUT,
		];
	}

	static isSupported(action: string): boolean
	{
		return NotificationAction.getTypes().includes(action);
	}
}
