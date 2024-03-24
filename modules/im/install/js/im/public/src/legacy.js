import { Reflection, Type } from 'main.core';
import { Messenger } from './public';

const legacyMessenger = {};
legacyMessenger.openMessenger = function(...args): Promise {
	console.warn("Developer: method BXIM.openMessenger is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openChat(...args);
};

legacyMessenger.openMessengerSlider = function(dialogId: string): Promise {
	console.warn("Developer: method BXIM.openMessengerSlider is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openChat(dialogId);
};

legacyMessenger.openHistory = function(...args): Promise {
	console.warn("Developer: method BXIM.openHistory is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");

	const Opener = Reflection.getClass('BX.Messenger.v2.Lib.Opener');
	return Opener?.openHistory(...args);
};

legacyMessenger.openNotify = function(...args): Promise {
	console.warn("Developer: method BXIM.openNotify is deprecated. Use method 'Messenger.openNotifications' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openNotifications(...args);
};

legacyMessenger.openSettings = function(...args): Promise {
	console.warn("Developer: method BXIM.openSettings is deprecated. Use method 'Messenger.openSettings' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openSettings(...args);
};

legacyMessenger.openVideoconf = function(code): Promise {
	console.warn("Developer: method BXIM.openVideoconf is deprecated. Use method 'Messenger.openConference' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openConference({ code });
};

legacyMessenger.openVideoconfByUrl = function(link): boolean {
	console.warn("Developer: method BXIM.openVideoconfByUrl is deprecated. Use method 'Messenger.openConference' from 'im.public' or 'im.public.iframe' extension.");

	const Utils = Reflection.getClass('BX.Messenger.v2.Lib.Utils');
	if (Utils && Type.isStringFilled(url) && !Utils.conference.isCurrentPortal(url))
	{
		return false;
	}

	Messenger.openConference({ link });

	return true;
};

legacyMessenger.callTo = function(...args): Promise {
	console.warn("Developer: method BXIM.callTo is deprecated. Use method 'Messenger.startVideoCall' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.startVideoCall(...args);
};

legacyMessenger.phoneTo = function(...args): Promise {
	console.warn("Developer: method BXIM.phoneTo is deprecated. Use method 'Messenger.startPhoneCall' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.startPhoneCall(...args);
};

legacyMessenger.startCallList = function(...args): Promise {
	console.warn("Developer: method BXIM.startCallList is deprecated. Use method 'Messenger.startCallList' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.startCallList(...args);
};

legacyMessenger.messenger = {};
legacyMessenger.messenger.popupPopupMenu = false;
legacyMessenger.settings = {};

const legacyDesktop = {
	init: () => {},
	enableInVersion: () => false,
	getApiVersion: () => 0,
	addCustomEvent: () => {},
	onCustomEvent: () => {},
	ready: () => true,
	log: () => {},
};

export { legacyMessenger, legacyDesktop };
