import { Messenger } from 'im.public';
import { DesktopManager } from 'im.v2.lib.desktop';
import { MessengerSlider } from 'im.v2.lib.slider';
import { Logger } from 'im.v2.lib.logger';
import type { ApplicationOpenChatParams } from '../../types/application';

export class ApplicationPullHandler
{
	handleApplicationOpenChat(params: ApplicationOpenChatParams): void
	{
		Logger.warn('ApplicationPullHandler: handleOpenChat', params);

		const hasFocus = document.hasFocus();
		if (!hasFocus)
		{
			return;
		}

		if (DesktopManager.isDesktop())
		{
			if (!DesktopManager.isChatWindow())
			{
				return;
			}

			void Messenger.openChat(params.dialogId);

			return;
		}

		if (!MessengerSlider.getInstance().isFocused())
		{
			return;
		}

		void Messenger.openChat(params.dialogId);
	}
}
