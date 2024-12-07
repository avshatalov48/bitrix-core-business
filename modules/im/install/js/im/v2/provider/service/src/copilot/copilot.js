import { ChatType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';

import { ChatService } from '../registry';

export class CopilotService
{
	async createChat({ roleCode }: {roleCode: string}): Promise<string>
	{
		const chatService = new ChatService();
		const { newDialogId, newChatId } = await chatService.createChat({
			type: ChatType.copilot,
			copilotMainRole: roleCode,
		})
			.catch((error) => {
				this.#onCreateError(error);
			});

		this.#sendAnalytics({ chatId: newChatId, dialogId: newDialogId });

		await chatService.loadChatWithMessages(newDialogId)
			.catch((error) => {
				this.#onCreateError(error);
			});

		return newDialogId;
	}

	#onCreateError(error: Error)
	{
		// eslint-disable-next-line no-console
		console.error('Copilot chat create error', error);
		throw new Error('Copilot chat create error');
	}

	#sendAnalytics({ chatId, dialogId })
	{
		Analytics.getInstance().onCreateCopilotChat({ chatId, dialogId });
	}
}
