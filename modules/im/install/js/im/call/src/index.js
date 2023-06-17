import {applyHacks} from './hacks';
import {BackgroundDialog} from './dialogs/background_dialog';
import {IncomingNotificationContent} from './dialogs/incoming_notification';
import {NotificationConferenceContent} from './dialogs/conference_notification';
import {FloatingScreenShare} from './floating_screenshare';
import {FloatingScreenShareContent} from './floating_screenshare';
import {CallHint} from './call_hint_popup'
import {CallController} from './controller';
import {CallEngine, CallEvent, EndpointDirection, UserState, Provider, CallType} from './engine/engine';
import {Hardware} from './hardware';
import Util from './util';
import {VideoStrategy} from './video_strategy';
import {View} from './view/view';
import {WebScreenSharePopup} from './web_screenshare_popup';
import 'loader';
import 'resize_observer';
import 'webrtc_adapter';
import 'im.lib.localstorage';
import 'ui.hint';
import 'voximplant';

applyHacks();

export {
	BackgroundDialog,
	CallController as Controller,
	CallEngine as Engine,
	CallEvent as Event,
	CallHint as Hint,
	EndpointDirection,
	FloatingScreenShare,
	FloatingScreenShareContent,
	IncomingNotificationContent,
	NotificationConferenceContent,
	Hardware,
	Provider,
	CallType as Type,
	UserState,
	Util,
	VideoStrategy,
	View,
	WebScreenSharePopup
};

// compatibility
BX.CallEngine = CallEngine;