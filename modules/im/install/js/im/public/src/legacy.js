import {Messenger} from './public';

const legacyMessenger = {};
legacyMessenger.openMessenger = function() {
	console.error("Developer: method BXIM.openMessenger is deprecated. Use method 'Messenger.openChat' from 'im.public' or 'im.public.iframe' extension.");
	return Messenger.openChat(...arguments);
};
legacyMessenger.openNotify = function() {
	console.error("Developer: method BXIM.openNotify is deprecated. Use method 'Messenger.openNotifications' from 'im.public' or 'im.public.iframe' extension.");
	return Messenger.openNotifications(...arguments);
};
legacyMessenger.openSettings = function() {
	console.error("Developer: method BXIM.openSettings is deprecated. Use method 'Messenger.openSettings' from 'im.public' or 'im.public.iframe' extension.");
	return Messenger.openSettings(...arguments);
};
legacyMessenger.callTo = function() {
	console.error("Developer: method BXIM.callTo is deprecated. Use method 'Messenger.startVideoCall' from 'im.public' or 'im.public.iframe' extension.");
	return Messenger.startVideoCall(...arguments);
};

legacyMessenger.messenger = {};
legacyMessenger.messenger.popupPopupMenu = false;

export {legacyMessenger};