import {Type, Extension, Reflection} from 'main.core';

import {legacyMessenger} from './legacy';

class Messenger
{
	v2enabled: boolean = false;

	constructor()
	{
		const settings = Extension.getSettings('im.public');
		this.v2enabled = settings.get('v2enabled', false);
	}

	openChat(dialogId: string = '', text: string = ''): Promise
	{
		if (!this.v2enabled)
		{
			return new Promise((resolve, reject) => {
				window.BXIM.openMessenger(dialogId);
				resolve();
			});
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
		return MessengerSlider?.getInstance().openChat(dialogId, text);
	}

	openLines(): Promise
	{
		if (!this.v2enabled)
		{
			return new Promise((resolve, reject) => {
				window.BXIM.openMessenger(0, 'im-ol');
				resolve();
			});
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
		return MessengerSlider?.getInstance().openLines();
	}

	openNotifications(): Promise
	{
		if (!this.v2enabled)
		{
			return new Promise((resolve, reject) => {
				window.BXIM.openNotify();
				resolve();
			});
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
		return MessengerSlider?.getInstance().openNotifications();
	}

	openRecentSearch(): Promise
	{
		if (!this.v2enabled)
		{
			return new Promise((resolve, reject) => {
				window.BXIM.openMessenger();
				resolve();
			});
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
		return MessengerSlider?.getInstance().openRecentSearch();
	}

	openSettings(options: ?object = {}): Promise<Array>
	{
		if (!this.v2enabled)
		{
			return new Promise((resolve, reject) => {

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
				resolve();
			});
		}

		const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
		return MessengerSlider?.getInstance().openSettings();
	}

	startVideoCall(dialogId: string = '', withVideo: boolean = true): Promise<Array>
	{
		if (!this.v2enabled)
		{
			return new Promise((resolve) => {
				window.BXIM.callTo(dialogId, withVideo);
				resolve();
			});
		}

		const DesktopManager = Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager');

		return new Promise((resolve, reject) => {
			DesktopManager?.getInstance().checkRunStatus()
				.then(() => {
					DesktopManager?.getInstance().startVideoCall(dialogId, withVideo);
					resolve();
				})
				.catch(() => {
					const MessengerSlider = Reflection.getClass('BX.Messenger.v2.Lib.MessengerSlider');
					MessengerSlider?.getInstance().startVideoCall(dialogId, withVideo)
						.then(resolve)
						.catch(reject)
					;
				})
			;
		});
	}
}

const messenger = new Messenger();
export {messenger as Messenger};

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