import { Type, Extension, Reflection } from 'main.core';

import { legacyMessenger, legacyDesktop } from './legacy';

class Messenger
{
	v2enabled: boolean = false;

	constructor()
	{
		const settings = Extension.getSettings('im.public');
		this.v2enabled = settings.get('v2enabled', false);
	}

	async openChat(dialogId: string = '', text: string = ''): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.openMessenger(dialogId);

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const desktopIsActive = await DesktopManager?.getInstance().checkStatusInDifferentContext();
		if (desktopIsActive)
		{
			return DesktopManager?.getInstance().openChat(dialogId);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openChat(dialogId, text);
	}

	openLines(): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.openMessenger(0, 'im-ol');

			return Promise.resolve();
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openLines();
	}

	async openNotifications(): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.openNotify();

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const desktopIsActive = await DesktopManager?.getInstance().checkStatusInDifferentContext();
		if (desktopIsActive)
		{
			return DesktopManager?.getInstance().openNotifications();
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openNotifications();
	}

	async openRecentSearch(): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.openMessenger();

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const desktopIsActive = await DesktopManager?.getInstance().checkStatusInDifferentContext();
		if (desktopIsActive)
		{
			return DesktopManager?.getInstance().openRecentSearch();
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openRecentSearch();
	}

	openSettings(options: ?Object = {}): Promise<Array>
	{
		if (!this.v2enabled)
		{
			const params = {};
			if (Type.isPlainObject(options))
			{
				if (Type.isStringFilled(options.selected))
				{
					params.active = options.selected;
				}

				if (Type.isStringFilled(options.section))
				{
					params.onlyPanel = options.section;
				}
			}
			window.BXIM.openSettings(params);

			return Promise.resolve();
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openSettings();
	}

	async startVideoCall(dialogId: string = '', withVideo: boolean = true): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.callTo(dialogId, withVideo);

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const desktopIsActive = await DesktopManager?.getInstance().checkStatusInDifferentContext();
		if (desktopIsActive)
		{
			return DesktopManager?.getInstance().startVideoCall(dialogId, withVideo);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().startVideoCall(dialogId, withVideo);
	}
}

const messenger = new Messenger();
export { messenger as Messenger };

// pretty export
const namespace = Reflection.getClass('BX.Messenger');
if (namespace)
{
	namespace.Public = messenger;
}

// compatibility layer
if (
	messenger.v2enabled
	&& Type.isUndefined(window.BXIM)
	&& window.parent === window
)
{
	window.BXIM = legacyMessenger;
}

if (
	messenger.v2enabled
	&& Type.isUndefined(window.BX.desktop)
	&& Type.isObject(window.BXDesktopSystem)
	&& window.parent === window
)
{
	window.BX.desktop = legacyDesktop;
}
