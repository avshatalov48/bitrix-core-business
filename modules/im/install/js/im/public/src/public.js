import { Type, Extension, Reflection, type JsonObject } from 'main.core';

import { legacyMessenger, legacyDesktop } from './legacy';
import { desktop } from './desktop';
import { prepareSettingsSection } from './functions/settings';

type Opener = {
	openChat: (dialogId?: string, text?: string) => Promise,
	openLines: (dialogId?: string) => Promise,
	openCopilot: (dialogId?: string) => Promise,
	openLinesHistory: (dialogId?: string) => Promise,
	openNotifications: () => Promise,
	openRecentSearch: () => Promise,
	openSettings: ({ onlyPanel?: string }) => Promise,
	openConference: ({ code?: string, link?: string }) => Promise,
	startVideoCall: (dialogId?: string, withVideo?: boolean) => Promise,
	startPhoneCall: (number: string, params: JsonObject) => Promise,
	startCallList: (callListId: number, params: JsonObject) => Promise,
	enableDesktopRedirect: () => void,
	disableDesktopRedirect: () => void,
};

class Messenger
{
	v2enabled: boolean = false;

	constructor()
	{
		const settings = Extension.getSettings('im.public');
		this.v2enabled = settings.get('v2enabled', false);
		this.desktop = desktop;
	}

	async openChat(dialogId: string = '', messageId: number = 0): Promise
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
			return DesktopManager?.getInstance().redirectToChat(dialogId, messageId);
		}

		return getOpener()?.openChat(dialogId, messageId);
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

		return getOpener()?.openLines(dialogId);
	}

	async openCopilot(dialogId: string = '', contextId: number = 0): Promise
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
			return DesktopManager?.getInstance().redirectToCopilot(dialogId);
		}

		return getOpener()?.openCopilot(dialogId, contextId);
	}

	async openLinesHistory(dialogId: string = ''): Promise
	{
		if (!this.v2enabled)
		{
			window.BXIM.openHistory(dialogId);

			return Promise.resolve();
		}

		return getOpener()?.openHistory(dialogId);
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

		return getOpener()?.openNotifications();
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

		return getOpener()?.openRecentSearch();
	}

	async openSettings(options: { onlyPanel?: string } = {}): Promise
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

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');
		const isRedirectAllowed = await DesktopManager?.getInstance().checkForRedirect();
		if (isRedirectAllowed)
		{
			return DesktopManager?.getInstance().redirectToSettings(options.onlyPanel ?? '');
		}

		const settingsSection = prepareSettingsSection(options.onlyPanel ?? '');

		return getOpener()?.openSettings(settingsSection);
	}

	async openConference(options: { code?: string, link?: string } = {}): Promise
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

		return getOpener()?.openConference(code);
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

		return getOpener()?.startVideoCall(dialogId, withVideo);
	}

	async startPhoneCall(number: string, params: JsonObject): Promise
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

		return getOpener()?.startPhoneCall(number, params);
	}

	async startCallList(callListId: number, params: JsonObject): Promise
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

		return getOpener()?.startCallList(callListId, params);
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

	setWebRTCDebug(debug: boolean = false)
	{
		if (!this.v2enabled)
		{
			return;
		}

		const PhoneManager = Reflection.getClass('BX.Messenger.v2.Lib.PhoneManager');
		PhoneManager?.getInstance().toggleDebugFlag(debug);

		const CallManager = Reflection.getClass('BX.Messenger.v2.Lib.CallManager');
		CallManager?.getInstance().toggleDebugFlag(debug);
	}
}

const getOpener = (): ?Opener => {
	return Reflection.getClass('BX.Messenger.v2.Lib.Opener');
};

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
