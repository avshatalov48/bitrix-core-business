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
		const isRedirectAllowed = await DesktopManager?.getInstance().checkForRedirect();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().redirectToChat(dialogId);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openChat(dialogId, text);
	}

	async openLines(dialogId: string = ''): Promise
	{
		if (!this.v2enabled)
		{
			const preparedDialogId = dialogId === '' ? 0 : dialogId;
			window.BXIM.openMessenger(preparedDialogId, 'im-ol');

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const isRedirectAllowed = await DesktopManager?.getInstance().checkForRedirect();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().redirectToLines(dialogId);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openLines(dialogId);
	}

	async openLinesHistory(dialogId: string = ''): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.openHistory(dialogId);

			return Promise.resolve();
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openHistory(dialogId);
	}

	async openNotifications(): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.openNotify();

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const isRedirectAllowed = await DesktopManager?.getInstance().checkForRedirect();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().redirectToNotifications();
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
		const isRedirectAllowed = await DesktopManager?.getInstance().checkForRedirect();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().redirectToRecentSearch();
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

		return MessengerSlider?.getInstance().openSettings(options);
	}

	async openConference(options: { code?: string, link?: string } = {}): Promise<Array>
	{
		if (!this.v2enabled)
		{
			if (Type.isPlainObject(options))
			{
				if (Type.isStringFilled(options.code))
				{
					window.BXIM.openVideoconf(options.code);
				}

				if (Type.isStringFilled(options.link))
				{
					window.BXIM.openVideoconfByUrl(options.link);
				}
			}

			return Promise.resolve();
		}

		const Utils = Reflection.getClass('BX.Messenger.v2.Lib.Utils');
		if (Type.isStringFilled(options.url) && !Utils.conference.isCurrentPortal(options.url))
		{
			Utils.browser.openLink(options.url);

			return Promise.resolve();
		}

		const code = Utils.conference.getCodeByOptions(options);

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		if (DesktopManager?.isDesktop())
		{
			return DesktopManager?.getInstance().openConference(code);
		}
		const isRedirectAllowed = await DesktopManager?.getInstance().checkForRedirect();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().redirectToConference(code);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().openConference(code);
	}

	async startVideoCall(dialogId: string = '', withVideo: boolean = true): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.callTo(dialogId, withVideo);

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const isRedirectAllowed = await DesktopManager?.getInstance().checkForRedirect();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().redirectToVideoCall(dialogId, withVideo);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().startVideoCall(dialogId, withVideo);
	}

	async startPhoneCall(number: string, params: Object<any, string>): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.phoneTo(number, params);

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const desktopIsActive = await DesktopManager?.getInstance().checkStatusInDifferentContext();
		if (desktopIsActive)
		{
			return DesktopManager?.getInstance().redirectToPhoneCall(number, params);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().startPhoneCall(number, params);
	}

	async startCallList(callListId: number, params: Object<string, any>): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.startCallList(callListId, params);

			return Promise.resolve();
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const desktopIsActive = await DesktopManager?.getInstance().checkStatusInDifferentContext();
		if (desktopIsActive)
		{
			return DesktopManager?.getInstance().redirectToCallList(callListId, params);
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');

		return MessengerSlider?.getInstance().startCallList(callListId, params);
	}

	enableDesktopRedirect()
	{
		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		DesktopManager?.getInstance().enableRedirect();
	}

	disableDesktopRedirect()
	{
		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		DesktopManager?.getInstance().disableRedirect();
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
