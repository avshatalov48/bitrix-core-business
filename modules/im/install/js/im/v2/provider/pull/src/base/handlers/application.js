import { SidePanel, type SliderManager } from 'main.sidepanel';

import { Messenger } from 'im.public';
import { DesktopManager } from 'im.v2.lib.desktop';
import { MessengerSlider } from 'im.v2.lib.slider';
import { LayoutManager } from 'im.v2.lib.layout';
import { Logger } from 'im.v2.lib.logger';

import type { ApplicationOpenChatParams } from '../../types/application';

export class ApplicationPullHandler
{
	handleApplicationOpenChat(params: ApplicationOpenChatParams): void
	{
		Logger.warn('ApplicationPullHandler: handleOpenChat', params);

		if (!this.#isChatFocused())
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

		void Messenger.openChat(params.dialogId);
	}

	#isChatFocused(): boolean
	{
		if (!document.hasFocus())
		{
			return false;
		}

		const sidePanelManager: SliderManager = SidePanel.Instance;
		const hasOpenSliders = sidePanelManager.getOpenSlidersCount() > 0;
		const isEmbeddedMode = LayoutManager.getInstance().isEmbeddedMode();
		if (isEmbeddedMode && hasOpenSliders)
		{
			return false;
		}

		const isChatSliderFocused = MessengerSlider.getInstance().isFocused();
		if (!isEmbeddedMode && !isChatSliderFocused)
		{
			return false;
		}

		return true;
	}
}
