import { Messenger } from './public';

const legacyMessenger = {};
legacyMessenger.openMessenger = function(...args): Promise {
	console.error("Developer: method BXIM.openMessenger is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openChat(...args);
};

legacyMessenger.openMessengerSlider = function(dialogId: string): Promise {
	console.error("Developer: method BXIM.openMessengerSlider is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openChat(dialogId);
};

legacyMessenger.openNotify = function(...args): Promise {
	console.error("Developer: method BXIM.openNotify is deprecated. Use method 'Messenger.openNotifications' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openNotifications(...args);
};

legacyMessenger.openSettings = function(...args): Promise {
	console.error("Developer: method BXIM.openSettings is deprecated. Use method 'Messenger.openSettings' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.openSettings(...args);
};

legacyMessenger.callTo = function(...args): Promise {
	console.error("Developer: method BXIM.callTo is deprecated. Use method 'Messenger.startVideoCall' from 'im.public' or 'im.public.iframe' extension.");

	return Messenger.startVideoCall(...args);
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
