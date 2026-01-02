import { ChatType } from 'im.v2.const';
import { Analytics } from 'im.v2.lib.analytics';
import { ChatService } from 'im.v2.provider.service.chat';

export class CopilotService
{
	async createChat({ roleCode }: {roleCode: string}): Promise<string>
	{
		const chatService = new ChatService();
		const { newDialogId, newChatId } = await chatService.createChat({
			type: ChatType.copilot,
			copilotMainRole: roleCode,
		});

		this.#sendAnalytics({ chatId: newChatId, dialogId: newDialogId });

		await chatService.loadChatWithMessages(newDialogId);

		return newDialogId;
	}

	#sendAnalytics({ chatId, dialogId })
	{
		Analytics.getInstance().copilot.onCreateChat(chatId);
		Analytics.getInstance().ignoreNextChatOpen(dialogId);
	}
}
