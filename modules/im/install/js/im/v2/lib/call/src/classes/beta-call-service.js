import { runAction } from 'im.v2.lib.rest';
import { RestMethod } from 'im.v2.const';

export class BetaCallService
{
	static createRoom(chatId: number)
	{
		runAction(RestMethod.imCallBetaCreateRoom, {
			data: { chatId },
		});
	}
}
