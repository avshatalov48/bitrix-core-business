import { ChatService } from 'im.v2.provider.service.chat';
import { LayoutManager } from 'im.v2.lib.layout';
import { LegacyRecentService } from 'im.v2.provider.service.recent';

import type { ImModelLayout } from 'im.v2.model';

export const DesktopDataUpdater = {
	async reloadChatInfo(): Promise<void>
	{
		await LegacyRecentService.getInstance().requestItems({ firstPage: true });

		const currentLayout = LayoutManager.getInstance().getLayout();

		if (currentLayout.entityId)
		{
			await this.reopenChat(currentLayout);
		}
	},
	async reopenChat(currentLayout: ImModelLayout): Promise<void>
	{
		LayoutManager.getInstance().clearCurrentLayoutEntityId();

		const chatService = new ChatService();
		await chatService.resetChat(currentLayout.entityId);

		void LayoutManager.getInstance().setLayout(currentLayout);
	},
};
