import {Browser, Dom, Type, Text, Reflection} from 'main.core';
import {EventEmitter} from 'main.core.events';
import {Popup, Menu} from 'main.popup';
import {MessageBox, MessageBoxButtons} from 'ui.dialogs.messagebox';
import {Button} from 'ui.buttons';
import {LocalStorage} from 'im.lib.localstorage';
import {DesktopApi} from 'im.v2.lib.desktop-api';

import {BackgroundDialog} from './dialogs/background_dialog'
import {PromoPopup, PromoPopup3D} from './dialogs/promo_popup';
import {IncomingNotification} from './dialogs/incoming_notification';
import {ConferenceNotifications} from './dialogs/conference_notification';
import {CallHint} from './call_hint_popup';
import {Sidebar} from './sidebar';
import {WebScreenSharePopup} from './web_screenshare_popup';
import {FloatingScreenShare} from './floating_screenshare';
import {CallEngine, UserState, Provider, CallState, CallEvent} from './engine/engine';
import {BitrixCall} from './engine/bitrix_call'
import {VoximplantCall} from './engine/voximplant_call'
import {PlainCall} from './engine/plain_call';
import {SimpleVAD} from './engine/simple_vad';
import {Hardware} from './hardware';
import {View} from './view/view';
import {VideoStrategy} from './video_strategy';
import Util from './util';

import './css/call-overlay.css';

const Events = {
	onViewStateChanged: 'onViewStateChanged',
	onOpenVideoConference: 'onOpenVideoConference',
	onPromoViewed: 'onPromoViewed',
	onCallJoined: 'onCallJoined',
	onCallLeft: 'onCallLeft',
	onCallDestroyed: 'onCallDestroyed',
};

const ViewState = {
	Opened: 'Opened',
	Closed: 'Closed',
	Folded: 'Folded'
};

const DocumentType = {
	Resume: 'resume',
	Blank: 'blank'
};

const FeatureState = {
	Enabled: 'enabled',
	Disabled: 'disabled',
	Limited: 'limited',
}

const DOC_EDITOR_WIDTH = 961;
const DOC_TEMPLATE_WIDTH = 328;
const DOC_CREATED_EVENT = 'CallController::documentCreated';
const DOCUMENT_PROMO_CODE = 'im:call-document:16102021:web';
const DOCUMENT_PROMO_DELAY = 5 * 60 * 1000; // 5 minutes
const FILE_TYPE_DOCX = 'docx';
const FILE_TYPE_XLSX = 'xlsx';
const FILE_TYPE_PPTX = 'pptx';

const MASK_PROMO_CODE = 'im:mask:06122022:desktop';
const MASK_PROMO_DELAY = 5 * 60 * 1000; // 5 minutes

type UserData = {
	id: number,
	name: string,
	avatar: string,
	avatar_hr: string,
	gender: string
}

type InviteParams = {
	viewElement: HTMLElement,
	bindElement: HTMLElement,
	zIndex: number,
	darkMode: boolean,
	idleUsers: number[],
	allowNewUsers: boolean,
	onDestroy: () => void,
	onSelect: ({user: UserData}) => void
}

type Closer = {
	close: () => void
}

type MessengerFacade = {
	getDefaultZIndex: () => number,
	isThemeDark: () => boolean,
	getContainer: () => HTMLElement,
	openMessenger: (dialogId: string) => Promise,
	openHistory: (dialogId: string) => Promise,
	openSettings: (params: any) => void,
	openHelpArticle: (string) => void,
	isPromoRequired: (string) => boolean,
	isMessengerOpen: () => boolean,
	isSliderFocused: () => boolean,
	showUserSelector?: (params: InviteParams) => Promise<Closer>,

	getMessageCount: () => number,
	getCurrentDialogId: () => string,

	repeatSound: (string, number, boolean) => void,
	stopRepeatSound: (string) => void
}

export class CallController extends EventEmitter
{
	currentCall: BitrixCall | PlainCall | VoximplantCall | null
	currentCallIsNew: false
	callNotification: ?IncomingNotification
	floatingScreenShareWindow: ?FloatingScreenShare
	callView: ?View
	language: string
	incomingVideoStrategyType: string
	formatRecordDate: string
	messengerFacade: MessengerFacade

	constructor(config)
	{
		super();
		this.setEventNamespace('BX.Call.Controller');

		const needInit = BX.prop.getBoolean(config, "init", true);

		this.language = config.language || 'en';
		this.incomingVideoStrategyType = config.incomingVideoStrategyType || VideoStrategy.Type.AllowAll;
		this.formatRecordDate = config.formatRecordDate || 'd.m.Y';

		this.messengerFacade = config.messengerFacade;

		this.inited = false;
		this.debug = false;

		this.container = null;
		this.docEditor = null;
		this.docEditorIframe = null;
		this.maxEditorWidth = DOC_TEMPLATE_WIDTH;
		this.docCreatedForCurrentCall = false;

		this.folded = false;

		this.localStream = null;

		// for setting the camera after reconnect
		this.lastUsedCameraId = null;
		this.reconnectingCameraId = null;

		this.childCall = null;
		this.invitePopup = null;
		/** @var {VideoStrategy} this.currentCall */
		this.videoStrategy = null;

		this.isHttps = window.location.protocol === "https:";
		this.callWithLegacyMobile = false;

		this.featureScreenSharing = FeatureState.Enabled;
		this.featureRecord = FeatureState.Enabled;

		this.callRecordState = View.RecordState.Stopped;
		this.callRecordType = View.RecordType.None;

		this.autoCloseCallView = true;

		this.talkingUsers = {};

		this._callViewState = ViewState.Closed;

		this.answeredOrDeclinedCalls = new Set();

		// event handlers
		this._onCallUserInvitedHandler = this._onCallUserInvited.bind(this);
		this._onCallDestroyHandler = this._onCallDestroy.bind(this);
		this._onCallUserStateChangedHandler = this._onCallUserStateChanged.bind(this);
		this._onCallUserMicrophoneStateHandler = this._onCallUserMicrophoneState.bind(this);
		this._onCallUserCameraStateHandler = this._onCallUserCameraState.bind(this);
		this._onCameraPublishingHandler = this._onCameraPublishing.bind(this);
		this._onMicrophonePublishingdHandler = this._onMicrophonePublishingd.bind(this);
		this._onCallUserVideoPausedHandler = this._onCallUserVideoPaused.bind(this);
		this._onCallLocalMediaReceivedHandler = this._onCallLocalMediaReceived.bind(this);
		this._onCallLocalMediaStoppedHandler = this._onCallLocalMediaStopped.bind(this);
		this._onCallLocalCameraFlipHandler = this._onCallLocalCameraFlip.bind(this);
		this._onCallLocalCameraFlipInDesktopHandler = this._onCallLocalCameraFlipInDesktop.bind(this);
		this._onCallRemoteMediaReceivedHandler = this._onCallRemoteMediaReceived.bind(this);
		this._onCallRemoteMediaStoppedHandler = this._onCallRemoteMediaStopped.bind(this);
		this._onCallBadNetworkIndicatorHandler = this._onCallBadNetworkIndicator.bind(this);
		this._onCallConnectionQualityChangedHandler = this._onCallConnectionQualityChanged.bind(this);
		this._onCallToggleRemoteParticipantVideoHandler = this._onCallToggleRemoteParticipantVideo.bind(this);
		this._onCallUserVoiceStartedHandler = this._onCallUserVoiceStarted.bind(this);
		this._onCallUserVoiceStoppedHandler = this._onCallUserVoiceStopped.bind(this);
		this._onUserStatsReceivedHandler = this._onUserStatsReceived.bind(this);
		this._onCallUserScreenStateHandler = this._onCallUserScreenState.bind(this);
		this._onCallUserRecordStateHandler = this._onCallUserRecordState.bind(this);
		this.onCallUserFloorRequestHandler = this._onCallUserFloorRequest.bind(this);
		this._onCallFailureHandler = this._onCallFailure.bind(this);
		this._onNetworkProblemHandler = this._onNetworkProblem.bind(this);
		this._onMicrophoneLevelHandler = this._onMicrophoneLevel.bind(this);
		this._onReconnectingHandler = this._onReconnecting.bind(this);
		this._onReconnectedHandler = this._onReconnected.bind(this);
		this._onCustomMessageHandler = this._onCustomMessage.bind(this);
		this._onJoinRoomOfferHandler = this._onJoinRoomOffer.bind(this);
		this._onJoinRoomHandler = this._onJoinRoom.bind(this);
		this._onLeaveRoomHandler = this._onLeaveRoom.bind(this);
		this._onTransferRoomSpeakerHandler = this._onTransferRoomSpeaker.bind(this);
		this._onCallLeaveHandler = this._onCallLeave.bind(this);
		this._onCallJoinHandler = this._onCallJoin.bind(this);
		this._onGetUserMediaEndedHandler = this._onGetUserMediaEnded.bind(this);
		this._onUpdateLastUsedCameraIdHandler = this._onUpdateLastUsedCameraId.bind(this);

		this._onBeforeUnloadHandler = this._onBeforeUnload.bind(this);
		this._onImTabChangeHandler = this._onImTabChange.bind(this);
		this._onUpdateChatCounterHandler = this._onUpdateChatCounter.bind(this);

		this._onChildCallFirstMediaHandler = this._onChildCallFirstMedia.bind(this);

		this._onWindowFocusHandler = this._onWindowFocus.bind(this);
		this._onWindowBlurHandler = this._onWindowBlur.bind(this);

		if (DesktopApi.isDesktop() && false)
		{
			this.floatingWindow = new FloatingVideo({
				onMainAreaClick: this._onFloatingVideoMainAreaClick.bind(this),
				onButtonClick: this._onFloatingVideoButtonClick.bind(this)
			});
			this.floatingWindowUser = 0;
		}
		this.showFloatingWindowTimeout = 0;
		this.hideIncomingCallTimeout = 0;
		this.ignoreDeclinedCallsTimeout = {};

		if (DesktopApi.isDesktop())
		{
			this.floatingScreenShareWindow = new FloatingScreenShare({
				darkMode: this.messengerFacade.isThemeDark(),
				onBackToCallClick: this._onFloatingScreenShareBackToCallClick.bind(this),
				onStopSharingClick: this._onFloatingScreenShareStopClick.bind(this),
				onChangeScreenClick: this._onFloatingScreenShareChangeScreenClick.bind(this)
			});
		}
		this.showFloatingScreenShareWindowTimeout = 0;

		this.mutePopup = null;
		this.allowMutePopup = true;

		this.webScreenSharePopup = null;

		this.feedbackPopup = null;

		this.resizeObserver = new BX.ResizeObserver(this._onResize.bind(this));

		this.loopTimers = {};

		if (needInit)
		{
			this.init();
			this.#subscribeEvents(config);
		}
	}

	#subscribeEvents(config)
	{
		const eventKeys = Object.keys(Events);
		for (let eventName of eventKeys)
		{
			if (Type.isFunction(config.events[eventName]))
			{
				this.subscribe(Events[eventName], config.events[eventName])
			}
		}
	}

	get userId()
	{
		return Number(BX.message('USER_ID'))
	}

	get callViewState()
	{
		return this._callViewState
	}

	set callViewState(newState)
	{
		if (this.callViewState == newState)
		{
			return;
		}
		this._callViewState = newState;
		this.emit(Events.onViewStateChanged, {
			callViewState: newState
		})
	}

	init()
	{
		BX.addCustomEvent(window, "CallEvents::incomingCall", this.onIncomingCall.bind(this));
		Hardware.subscribe(Hardware.Events.deviceChanged, this._onDeviceChange.bind(this));
		Hardware.subscribe(Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipHandler);
		if (DesktopApi.isDesktop() && this.floatingWindow)
		{
			window.addEventListener("blur", this._onWindowBlurHandler);
			window.addEventListener("focus", this._onWindowFocusHandler);

			DesktopApi.subscribe("BXForegroundChanged", (focus) =>
			{
				if (focus)
				{
					this._onWindowFocus();
				}
				else
				{
					this._onWindowBlur();
				}
			});
		}

		if (DesktopApi.isDesktop() && this.floatingScreenShareWindow)
		{
			DesktopApi.subscribe("BXScreenMediaSharing", (id, title, x, y, width, height, app) =>
			{
				this.floatingScreenShareWindow.close();

				this.floatingScreenShareWindow.setSharingData({
					title: title,
					x: x,
					y: y,
					width: width,
					height: height,
					app: app
				}).then(() =>
				{
					this.floatingScreenShareWindow.show();
				}).catch((error) =>
				{
					console.error('setSharingData error', error);
				});
			});

			window.addEventListener("blur", this._onWindowBlurHandler);
			window.addEventListener("focus", this._onWindowFocusHandler);

			DesktopApi.subscribe("BXForegroundChanged", (focus) =>
			{
				if (focus)
				{
					this._onWindowFocus();
				}
				else
				{
					this._onWindowBlur();
				}
			});
		}

		if (DesktopApi.isDesktop())
		{
			DesktopApi.subscribe(Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipInDesktopHandler);
		}

		if (window['VoxImplant'])
		{
			VoxImplant.getInstance().addEventListener(VoxImplant.Events.MicAccessResult, this.voxMicAccessResult.bind(this));
		}

		window.addEventListener("beforeunload", this._onBeforeUnloadHandler);
		BX.addCustomEvent("OnDesktopTabChange", this._onImTabChangeHandler);

		BX.addCustomEvent(window, "onImUpdateCounterMessage", this._onUpdateChatCounter.bind(this));

		BX.garbage(this.destroy, this);

		this.inited = true;
	}

	/**
	 * Workaround to get current microphoneId
	 * @param e
	 */
	voxMicAccessResult(e)
	{
		if (e.stream && e.stream.getAudioTracks().length > 0 && this.callView)
		{
			this.callView.microphoneId = e.stream.getAudioTracks()[0].getSettings().deviceId
		}
	}

	getCallUsers(includeSelf)
	{
		const result = Object.keys(this.currentCall.getUsers());
		if (includeSelf)
		{
			result.push(this.currentCall.userId);
		}

		return result;
	}

	getActiveCallUsers()
	{
		const userStates = this.currentCall.getUsers();
		let activeUsers = [];

		for (let userId in userStates)
		{
			if (userStates.hasOwnProperty(userId))
			{
				if (userStates[userId] === UserState.Connected || userStates[userId] === UserState.Connecting || userStates[userId] === UserState.Calling)
				{
					activeUsers.push(userId);
				}
			}
		}
		return activeUsers;
	}

	updateFloatingWindowContent()
	{
		if (!this.floatingWindow || !this.currentCall)
		{
			return;
		}
		this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);

		Util.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then((result) =>
		{
			this.floatingWindow.setAvatars(result);
		});
	}

	updateDeviceIdInChildCall()
	{
		if (!this.childCall || !this.currentCall)
		{
			return;
		}

		if (this.currentCall.microphoneId)
		{
			this.childCall.setMicrophoneId(this.currentCall.microphoneId);
		}
		if (this.currentCall.cameraId)
		{
			this.childCall.setCameraId(this.currentCall.cameraId);
		}
	}

	onIncomingCall(e)
	{
		console.warn("incoming.call", e);
		/** @var {BitrixCall|PlainCall|VoximplantCall} newCall */
		const newCall = e.call;
		const isCurrentCallActive = this.currentCall && (this.callView || this.callNotification);

		this.callWithLegacyMobile = (e.isLegacyMobile === true);

		if (!isCurrentCallActive)
		{
			if (this.ignoreDeclinedCallsTimeout[newCall.id])
			{
				clearTimeout(this.ignoreDeclinedCallsTimeout[newCall.id]);
				this.ignoreDeclinedCallsTimeout[newCall.id] = setTimeout(
					() => this.answeredOrDeclinedCalls.delete(newCall.id),
					15000
				);
			}

			if (this.callView || this.answeredOrDeclinedCalls.has(newCall.id))
			{
				return;
			}

			this.checkDesktop()
				.then(
					() =>
					{
						// don't wait for init here to speedup process
						Hardware.init();
						if (this.currentCall || newCall.state == CallState.Finished)
						{
							return;
						}

						this.currentCall = newCall;
						this.bindCallEvents();
						this.updateFloatingWindowContent();

						if (this.currentCall.associatedEntity.type === 'chat' && this.currentCall.associatedEntity.advanced['chatType'] === 'videoconf')
						{
							if (this.isConferencePageOpened(this.currentCall.associatedEntity.id))
							{
								// conference page is already opened, do nothing
								this.removeCallEvents();
								this.currentCall = null;
							}
							else
							{
								this.showIncomingConference();
							}
						}
						else
						{
							let video = e.video === true;
							this.showIncomingCall({
								video: video
							});

							Hardware.init().then(() =>
							{
								if (!Hardware.hasCamera())
								{
									if (video)
									{
										this.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
									}
									if (this.callNotification)
									{
										this.callNotification.setHasCamera(false);
									}
								}
							})
						}
					},
					(error) =>
					{
						if (this.currentCall)
						{
							this.removeVideoStrategy();
							this.removeCallEvents();
							this.currentCall = null;
						}
						console.error(error);
						this.log(error);
						if (!this.isHttps)
						{
							this.showNotification(BX.message("IM_CALL_INCOMING_ERROR_HTTPS_REQUIRED"))
						}
						else
						{
							this.showNotification(BX.message("IM_CALL_INCOMING_UNSUPPORTED_BROWSER"))
						}
					}
				);
		}
		else
		{
			if (newCall.id == this.currentCall.id)
			{
				// ignoring
			}
			else if (newCall.parentId == this.currentCall.id)
			{
				if (this.currentCall.isScreenSharingStarted())
				{
					this.currentCall.stopScreenSharing();
				}

				if (!this.childCall)
				{
					this.childCall = newCall;
				}
				this.callView.removeScreenUsers();
				this.childCall.users.forEach((userId) => this.callView.addUser(userId, UserState.Calling))
				this.updateCallViewUsers(newCall.id, this.childCall.users);
				this.updateDeviceIdInChildCall();
				this.answerChildCall();

			}
			else
			{
				// send busy
				newCall.decline(486);
				return false;
			}
		}
	}

	bindCallEvents()
	{
		this.currentCall.addEventListener(CallEvent.onUserInvited, this._onCallUserInvitedHandler);
		this.currentCall.addEventListener(CallEvent.onDestroy, this._onCallDestroyHandler);
		this.currentCall.addEventListener(CallEvent.onUserStateChanged, this._onCallUserStateChangedHandler);
		this.currentCall.addEventListener(CallEvent.onUserMicrophoneState, this._onCallUserMicrophoneStateHandler);
		this.currentCall.addEventListener(CallEvent.onUserCameraState, this._onCallUserCameraStateHandler);
		this.currentCall.addEventListener(CallEvent.onCameraPublishing, this._onCameraPublishingHandler);
		this.currentCall.addEventListener(CallEvent.onMicrophonePublishing, this._onMicrophonePublishingdHandler);
		this.currentCall.addEventListener(CallEvent.onUserVideoPaused, this._onCallUserVideoPausedHandler);
		this.currentCall.addEventListener(CallEvent.onUserScreenState, this._onCallUserScreenStateHandler);
		this.currentCall.addEventListener(CallEvent.onUserRecordState, this._onCallUserRecordStateHandler);
		this.currentCall.addEventListener(CallEvent.onUserFloorRequest, this.onCallUserFloorRequestHandler);
		this.currentCall.addEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
		this.currentCall.addEventListener(CallEvent.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
		this.currentCall.addEventListener(CallEvent.onRemoteMediaReceived, this._onCallRemoteMediaReceivedHandler);
		this.currentCall.addEventListener(CallEvent.onRemoteMediaStopped, this._onCallRemoteMediaStoppedHandler);
		this.currentCall.addEventListener(CallEvent.onBadNetworkIndicator, this._onCallBadNetworkIndicatorHandler);
		this.currentCall.addEventListener(CallEvent.onConnectionQualityChanged, this._onCallConnectionQualityChangedHandler);
		this.currentCall.addEventListener(CallEvent.onToggleRemoteParticipantVideo, this._onCallToggleRemoteParticipantVideoHandler);
		this.currentCall.addEventListener(CallEvent.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
		this.currentCall.addEventListener(CallEvent.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
		this.currentCall.addEventListener(CallEvent.onUserStatsReceived, this._onUserStatsReceivedHandler);
		this.currentCall.addEventListener(CallEvent.onCallFailure, this._onCallFailureHandler);
		this.currentCall.addEventListener(CallEvent.onNetworkProblem, this._onNetworkProblemHandler);
		this.currentCall.addEventListener(CallEvent.onMicrophoneLevel, this._onMicrophoneLevelHandler);
		this.currentCall.addEventListener(CallEvent.onReconnecting, this._onReconnectingHandler);
		this.currentCall.addEventListener(CallEvent.onReconnected, this._onReconnectedHandler);
		this.currentCall.addEventListener(CallEvent.onCustomMessage, this._onCustomMessageHandler);
		this.currentCall.addEventListener(CallEvent.onJoinRoomOffer, this._onJoinRoomOfferHandler);
		this.currentCall.addEventListener(CallEvent.onJoinRoom, this._onJoinRoomHandler);
		this.currentCall.addEventListener(CallEvent.onLeaveRoom, this._onLeaveRoomHandler);
		this.currentCall.addEventListener(CallEvent.onTransferRoomSpeaker, this._onTransferRoomSpeakerHandler);
		this.currentCall.addEventListener(CallEvent.onJoin, this._onCallJoinHandler);
		this.currentCall.addEventListener(CallEvent.onLeave, this._onCallLeaveHandler);
		this.currentCall.addEventListener(CallEvent.onGetUserMediaEnded, this._onGetUserMediaEndedHandler);
		this.currentCall.addEventListener(CallEvent.onUpdateLastUsedCameraId, this._onUpdateLastUsedCameraIdHandler);
	}

	removeCallEvents()
	{
		this.currentCall.removeEventListener(CallEvent.onUserInvited, this._onCallUserInvitedHandler);
		this.currentCall.removeEventListener(CallEvent.onDestroy, this._onCallDestroyHandler);
		this.currentCall.removeEventListener(CallEvent.onUserStateChanged, this._onCallUserStateChangedHandler);
		this.currentCall.removeEventListener(CallEvent.onUserMicrophoneState, this._onCallUserMicrophoneStateHandler);
		this.currentCall.removeEventListener(CallEvent.onUserCameraState, this._onCallUserCameraStateHandler);
		this.currentCall.removeEventListener(CallEvent.onCameraPublishing, this._onCameraPublishingHandler);
		this.currentCall.removeEventListener(CallEvent.onMicrophonePublishing, this._onMicrophonePublishingdHandler);
		this.currentCall.removeEventListener(CallEvent.onUserVideoPaused, this._onCallUserVideoPausedHandler);
		this.currentCall.removeEventListener(CallEvent.onUserScreenState, this._onCallUserScreenStateHandler);
		this.currentCall.removeEventListener(CallEvent.onUserRecordState, this._onCallUserRecordStateHandler);
		this.currentCall.removeEventListener(CallEvent.onUserFloorRequest, this.onCallUserFloorRequestHandler);
		this.currentCall.removeEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
		this.currentCall.removeEventListener(CallEvent.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
		this.currentCall.removeEventListener(CallEvent.onRemoteMediaReceived, this._onCallRemoteMediaReceivedHandler);
		this.currentCall.removeEventListener(CallEvent.onRemoteMediaStopped, this._onCallRemoteMediaStoppedHandler);
		this.currentCall.removeEventListener(CallEvent.onBadNetworkIndicator, this._onCallBadNetworkIndicatorHandler);
		this.currentCall.removeEventListener(CallEvent.onConnectionQualityChanged, this._onCallConnectionQualityChangedHandler);
		this.currentCall.removeEventListener(CallEvent.onToggleRemoteParticipantVideo, this._onCallToggleRemoteParticipantVideoHandler);
		this.currentCall.removeEventListener(CallEvent.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
		this.currentCall.removeEventListener(CallEvent.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
		this.currentCall.removeEventListener(CallEvent.onUserStatsReceived, this._onUserStatsReceivedHandler);
		this.currentCall.removeEventListener(CallEvent.onCallFailure, this._onCallFailureHandler);
		this.currentCall.removeEventListener(CallEvent.onNetworkProblem, this._onNetworkProblemHandler);
		this.currentCall.removeEventListener(CallEvent.onMicrophoneLevel, this._onMicrophoneLevelHandler);
		this.currentCall.removeEventListener(CallEvent.onReconnecting, this._onReconnectingHandler);
		this.currentCall.removeEventListener(CallEvent.onReconnected, this._onReconnectedHandler);
		this.currentCall.removeEventListener(CallEvent.onCustomMessage, this._onCustomMessageHandler);
		this.currentCall.removeEventListener(CallEvent.onJoin, this._onCallJoinHandler);
		this.currentCall.removeEventListener(CallEvent.onLeave, this._onCallLeaveHandler);
		this.currentCall.removeEventListener(CallEvent.onGetUserMediaEnded, this._onGetUserMediaEndedHandler);
		this.currentCall.removeEventListener(CallEvent.onUpdateLastUsedCameraId, this._onUpdateLastUsedCameraIdHandler);
	}

	bindCallViewEvents()
	{
		this.callView.setCallback(View.Event.onShow, this._onCallViewShow.bind(this));
		this.callView.setCallback(View.Event.onClose, this._onCallViewClose.bind(this));
		this.callView.setCallback(View.Event.onDestroy, this._onCallViewDestroy.bind(this));
		this.callView.setCallback(View.Event.onButtonClick, this._onCallViewButtonClick.bind(this));
		this.callView.setCallback(View.Event.onBodyClick, this._onCallViewBodyClick.bind(this));
		this.callView.setCallback(View.Event.onReplaceCamera, this._onCallViewReplaceCamera.bind(this));
		this.callView.setCallback(View.Event.onReplaceMicrophone, this._onCallViewReplaceMicrophone.bind(this));
		this.callView.setCallback(View.Event.onSetCentralUser, this._onCallViewSetCentralUser.bind(this));
		this.callView.setCallback(View.Event.onChangeHdVideo, this._onCallViewChangeHdVideo.bind(this));
		this.callView.setCallback(View.Event.onChangeMicAutoParams, this._onCallViewChangeMicAutoParams.bind(this));
		this.callView.setCallback(View.Event.onChangeFaceImprove, this._onCallViewChangeFaceImprove.bind(this));
		this.callView.setCallback(View.Event.onOpenAdvancedSettings, this._onCallViewOpenAdvancedSettings.bind(this));
		this.callView.setCallback(View.Event.onReplaceSpeaker, this._onCallViewReplaceSpeaker.bind(this));
		this.callView.setCallback(View.Event.onHasMainStream, this._onCallViewHasMainStream.bind(this));
		this.callView.setCallback(View.Event.onToggleSubscribe, this._onCallToggleSubscribe.bind(this));
	}

	updateCallViewUsers(callId, userList)
	{
		if (!this.callView)
		{
			return;
		}

		const convertAllItemsToInt = (arr) => Array.from(arr, (item) => parseFloat(item))
		const userListInt = convertAllItemsToInt(userList)

		let userDataInt = []
		let notIncludedEl = []

		if (this.currentCall.userData)
		{
			this.callView.updateUserData(this.currentCall.userData);

			userDataInt = convertAllItemsToInt(Object.keys(this.currentCall.userData));
			notIncludedEl = userListInt.filter((id) => userDataInt.indexOf(id) === -1);
		}

		if (this.currentCall.userData && !notIncludedEl.length)
		{
			return;
		}

		Util.getUsers(callId, userListInt).then((userData) =>
		{
			this.callView.updateUserData(userData);
		});
	}

	createVideoStrategy()
	{
		if (this.videoStrategy)
		{
			this.videoStrategy.destroy();
		}

		const strategyType = this.incomingVideoStrategyType;

		this.videoStrategy = new VideoStrategy({
			call: this.currentCall,
			callView: this.callView,
			strategyType: strategyType
		});
	}

	removeVideoStrategy()
	{
		if (this.videoStrategy)
		{
			this.videoStrategy.destroy();
		}
		this.videoStrategy = null;
	}

	setFeatureScreenSharing(enable)
	{
		this.featureScreenSharing = enable;
	}

	setFeatureRecord(enable)
	{
		this.featureRecord = enable;
	}

	setVideoStrategyType(type)
	{
		if (this.videoStrategy)
		{
			this.videoStrategy.setType(type)
		}
	}

	createContainer()
	{
		this.container = BX.create("div", {
			props: {className: "bx-messenger-call-overlay"},
		});

		const externalContainer = this.messengerFacade.getContainer();
		externalContainer.insertBefore(this.container, externalContainer.firstChild);

		externalContainer.classList.add("bx-messenger-call");
	}

	removeContainer()
	{
		if (this.container)
		{
			Dom.remove(this.container);
			this.container = null;
			this.messengerFacade.getContainer().classList.remove("bx-messenger-call");
		}
	}

	answerChildCall()
	{
		this.removeCallEvents();
		this.removeVideoStrategy();
		this.childCall.addEventListener(CallEvent.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);
		this.childCall.addEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
		this.childCall.answer();
	}

	setReconnectingCameraId(id)
	{
		this.reconnectingCameraId = id;

		if (id) {
			this.updateCameraSettingsInCurrentCallAfterReconnecting(id)
		}
	}

	updateCameraSettingsInCurrentCallAfterReconnecting(cameraId)
	{

		if (this.currentCall.cameraId === cameraId) {
			return;
		}

		const devicesList = Hardware.getCameraList();

		if (!devicesList.find(device => device.deviceId === cameraId))
		{
			return;
		}

		this.currentCall.setCameraId(cameraId);
		this.setReconnectingCameraId(null);
	}

	_onChildCallFirstMedia(e)
	{
		if (!this.childCall)
		{
			return;
		}
		this.log("Finishing one-to-one call, switching to group call");

		const newCall = this.childCall;
		this.childCall = null;

		let previousRecordType = View.RecordType.None;
		if (this.isRecording())
		{
			previousRecordType = this.callRecordType;

			BXDesktopSystem.CallRecordStop();
			this.callRecordState = View.RecordState.Stopped;
			this.callRecordType = View.RecordType.None;
			this.callView.setRecordState(this.callView.getDefaultRecordState());
			this.callView.setButtonActive('record', false);
		}

		this.callView.showButton('floorRequest');

		if (this.callView)
		{
			if ("track" in e)
			{
				this.callView.setUserMedia(e.userId, e.kind, e.track);
			}
			if ("mediaRenderer" in e && e.mediaRenderer.kind === "audio")
			{
				this.callView.setUserMedia(e.userId, 'audio', e.mediaRenderer.stream.getAudioTracks()[0]);
			}
			if ("mediaRenderer" in e && (e.mediaRenderer.kind === "video" || e.mediaRenderer.kind === "sharing"))
			{
				this.callView.setVideoRenderer(e.userId, e.mediaRenderer);
			}
		}

		newCall.removeEventListener(CallEvent.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);
		newCall.removeEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);

		this.removeCallEvents();
		const oldCall = this.currentCall;
		oldCall.hangup();

		this.currentCall = newCall;

		if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
		{
			this.messengerFacade.openMessenger(this.currentCall.associatedEntity.id);
		}

		this.bindCallEvents();
		this.createVideoStrategy();

		if (previousRecordType !== View.RecordType.None)
		{
			this._startRecordCall(previousRecordType);
		}
	}

	checkDesktop()
	{
		if (Reflection.getClass('BX.Messenger.v2.Lib.DesktopManager'))
		{
			return new Promise((resolve) => {
				const desktop = BX.Messenger.v2.Lib.DesktopManager.getInstance();
				desktop.checkStatusInDifferentContext()
					.then((result) => {
						if (result === false)
						{
							resolve();
						}
					});
			});
		}

		if (Reflection.getClass('BX.desktopUtils'))
		{
			return new Promise((resolve) => {
				BX.desktopUtils.runningCheck(
					() => {},
					() => resolve()
				);
			});
		}

		return Promise.resolve();
	}

	isMutedPopupAllowed()
	{
		if (!this.allowMutePopup || !this.currentCall)
		{
			return false;
		}

		const currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
		return !currentRoom || currentRoom.speaker == this.userId;
	}

	isConferencePageOpened(dialogId)
	{
		const tagPresent = LocalStorage.get(
			CallEngine.getSiteId(),
			CallEngine.getCurrentUserId(),
			CallEngine.getConferencePageTag(dialogId),
			'N'
		);

		return tagPresent === 'Y';
	}

	/**
	 * @param {Object} params
	 * @param {bool} [params.video = false]
	 */
	showIncomingCall(params)
	{
		if (!Type.isPlainObject(params))
		{
			params = {};
		}
		params.video = params.video === true;

		if (this.feedbackPopup)
		{
			this.feedbackPopup.close();
		}

		const allowVideo = this.callWithLegacyMobile ? params.video === true : true;

		this.callNotification = new IncomingNotification({
			callerName: this.currentCall.associatedEntity.name,
			callerAvatar: this.currentCall.associatedEntity.avatar,
			callerType: this.currentCall.associatedEntity.advanced.chatType,
			callerColor: this.currentCall.associatedEntity.avatarColor,
			video: params.video,
			hasCamera: allowVideo,
			zIndex: this.messengerFacade.getDefaultZIndex() + 200,
			onClose: this._onCallNotificationClose.bind(this),
			onDestroy: this._onCallNotificationDestroy.bind(this),
			onButtonClick: this._onCallNotificationButtonClick.bind(this)
		});

		this.callNotification.show();
		this.scheduleCancelNotification();

		this.messengerFacade.repeatSound('ringtone', 3500, true);
	}

	showIncomingConference()
	{
		this.callNotification = new ConferenceNotifications({
			zIndex: this.messengerFacade.getDefaultZIndex() + 200,
			callerName: this.currentCall.associatedEntity.name,
			callerAvatar: this.currentCall.associatedEntity.avatar,
			callerColor: this.currentCall.associatedEntity.avatarColor,
			onClose: this._onCallNotificationClose.bind(this),
			onDestroy: this._onCallNotificationDestroy.bind(this),
			onButtonClick: this._onCallConferenceNotificationButtonClick.bind(this)
		});

		this.callNotification.show();
		this.scheduleCancelNotification();

		this.messengerFacade.repeatSound('ringtone', 3500, true);
	}

	scheduleCancelNotification()
	{
		clearTimeout(this.hideIncomingCallTimeout);
		this.hideIncomingCallTimeout = setTimeout(() =>
		{
			if (this.callNotification)
			{
				this.callNotification.close();
			}
			if (this.currentCall)
			{
				this.removeVideoStrategy();
				this.removeCallEvents();
				this.currentCall = null;
			}
		}, 30 * 1000);
	}

	showNotification(notificationText, actions)
	{
		if (!actions)
		{
			actions = [];
		}
		BX.UI.Notification.Center.notify({
			content: Text.encode(notificationText),
			position: "top-right",
			autoHideDelay: 5000,
			closeButton: true,
			actions: actions
		});
	}

	showNetworkProblemNotification(notificationText)
	{
		BX.UI.Notification.Center.notify({
			content: Text.encode(notificationText),
			position: "top-right",
			autoHideDelay: 5000,
			closeButton: true,
			actions: [{
				title: BX.message("IM_M_CALL_HELP"),
				events: {
					click: (event, balloon) =>
					{
						top.BX.Helper.show('redirect=detail&code=12723718');
						balloon.close();
					}
				}
			}]
		});
	}

	showUnsupportedNotification()
	{
		let messageBox;
		if (DesktopApi.isDesktop())
		{
			messageBox = new MessageBox({
				message: BX.message('IM_CALL_DESKTOP_TOO_OLD'),
				buttons: MessageBoxButtons.OK_CANCEL,
				okCaption: BX.message('IM_M_CALL_BTN_UPDATE'),
				cancelCaption: BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
				onOk: () =>
				{
					const url = Browser.isMac() ? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg" : "http://dl.bitrix24.com/b24/bitrix24_desktop.exe";
					window.open(url, "desktopApp");
					return true;
				},
			})
		}
		else
		{
			messageBox = new MessageBox({
				message: BX.message('IM_CALL_WEBRTC_USE_CHROME_OR_DESKTOP'),
				buttons: MessageBoxButtons.OK_CANCEL,
				okCaption: BX.message("IM_CALL_DETAILS"),
				cancelCaption: BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
				onOk: () =>
				{
					top.BX.Helper.show("redirect=detail&code=11387752");
					return true;
				},
			})
		}
		messageBox.show();
	}

	isUserAgentSupported()
	{
		if (DesktopApi.isDesktop())
		{
			return DesktopApi.getApiVersion() > 48;
		}
		if ('VoxImplant' in window)
		{
			return VoxImplant.getInstance().isRTCsupported();
		}
		return Util.isWebRTCSupported();
	}

	getBlockedButtons(needBlockAddButton = false) :string[]
	{
		let result = ['record'];
		if (!this.messengerFacade.showUserSelector || needBlockAddButton)
		{
			result.push('add')
		}

		return result;
	}

	prepareUserCall(callData)
	{
		this.preparedUserCall = callData;
	}

	startCall(dialogId, video)
	{
		if (!this.isUserAgentSupported())
		{
			this.showUnsupportedNotification();
			return;
		}

		if (this.callView || this.currentCall)
		{
			this.unfold();
			return;
		}

		if (this.initCallPromise)
		{
			return;
		}

		if (this.feedbackPopup)
		{
			this.feedbackPopup.close();
		}

		let provider = Provider.Plain;
		if (dialogId.toString().startsWith("chat"))
		{
			provider = Util.getConferenceProvider();
		}
		const isCallPrepared = provider === Provider.Plain && this.preparedUserCall?.dialogId === dialogId;

		const debug1 = +(new Date());
		this.initCallPromise = this.messengerFacade.openMessenger(dialogId)
			.then(() =>
			{
				return Hardware.init();
			})
			.then(() =>
			{
				if (video && isCallPrepared)
				{
					this._prepareLocalStream();
				}

				this.createContainer();
				let hiddenButtons = [];
				if (provider === Provider.Plain)
				{
					hiddenButtons.push('floorRequest');
				}
				if (!Util.shouldShowDocumentButton())
				{
					hiddenButtons.push('document');
				}

				Hardware.isCameraOn = video;

				this.callView = new View({
					container: this.container,
					baseZIndex: this.messengerFacade.getDefaultZIndex(),
					showChatButtons: true,
					userLimit: Util.getUserLimit(),
					language: this.language,
					layout: dialogId.toString().startsWith("chat") ? View.Layout.Grid : View.Layout.Centered,
					microphoneId: Hardware.defaultMicrophone,
					showShareButton: this.featureScreenSharing !== FeatureState.Disabled,
					showRecordButton: this.featureRecord !== FeatureState.Disabled,
					hiddenButtons: hiddenButtons,
					blockedButtons: this.getBlockedButtons(provider === Provider.Plain),
				});

				this.bindCallViewEvents();

				if (isCallPrepared)
				{
					this.callView.isPreparing = true;
					this.callView.appendUsers({
						[this.preparedUserCall.user]: UserState.Calling,
					});
					this.callView.updateUserData({
						...this.preparedUserCall.userData,
					});
					if (this.localStream)
					{
						const streamData = {
							stream: this.localStream,
							tag: 'main',
							flipVideo: Hardware.enableMirroring,
						};

						this.callView.setLocalStream(streamData);
					}
					this.callView.show();
				}

				if (video && !Hardware.hasCamera())
				{
					this.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
					video = false;
				}

				return CallEngine.createCall({
					entityType: 'chat',
					entityId: dialogId,
					provider: provider,
					videoEnabled: !!video,
					enableMicAutoParameters: Hardware.enableMicAutoParameters,
					joinExisting: true,
					debug: this.debug,
				});
			})
			.then((e) =>
			{
				const debug2 = +(new Date());
				this.currentCall = e.call;
				this.currentCallIsNew = e.isNew;

				if (!this.callView)
				{
					this.leaveCurrentCall(true);
					return
				}

				this.log("Call creation time: " + (debug2 - debug1) / 1000 + " seconds");

				this.currentCall.useHdVideo(Hardware.preferHdQuality);
				if (Hardware.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(Hardware.defaultMicrophone);
				}
				if (Hardware.defaultCamera)
				{
					this.currentCall.setCameraId(Hardware.defaultCamera);
				}

				if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
				{
					if (this.messengerFacade.getCurrentDialogId() != this.currentCall.associatedEntity.id)
					{
						this.messengerFacade.openMessenger(this.currentCall.associatedEntity.id);
					}
				}

				this.autoCloseCallView = true;
				this.bindCallEvents();
				this.createVideoStrategy();

				if (isCallPrepared)
				{
					this.callView.isPreparing = false;
				}
				else
				{
					this.callView.appendUsers(this.currentCall.getUsers());
					this.updateCallViewUsers(this.currentCall.id, this.getCallUsers(true));
				}

				this.callView.show();

				this.showDocumentPromo();
				this.showMaskPromo();

				if (this.currentCallIsNew)
				{
					this.log("Inviting users");
					this.currentCall.inviteUsers();

					this.messengerFacade.repeatSound('dialtone', 5000, true);
				}
				else
				{
					this.log("Joining existing call");
					this.currentCall.answer();
				}

				this._onUpdateLastUsedCameraId();
			})
			.catch((error) =>
			{
				console.error(error);

				let errorCode;
				if (typeof (error) == "string")
				{
					errorCode = error;
				}
				else if (typeof (error) == "object" && error.code)
				{
					errorCode = error.code == 'access_denied' ? 'ACCESS_DENIED' : error.code
				}
				else
				{
					errorCode = 'UNKNOWN_ERROR';
				}
				this._onCallFailure({
					code: errorCode,
					message: error.message || "",
				})

				if (this.hasStreamFromCall)
				{
					this._stopLocalStream();
				}
			})
			.finally(() =>
			{
				this.initCallPromise = null;
				this.preparedUserCall = null;
			});
	}

	joinCall(callId: number, video: boolean, options: {joinAsViewer?: boolean})
	{
		const joinAsViewer = BX.prop.getBoolean(options, "joinAsViewer", false);

		if (!this.isUserAgentSupported())
		{
			this.showUnsupportedNotification();
			return;
		}

		if (this.callView || this.currentCall || this.initCallPromise)
		{
			return;
		}

		let isGroupCall;

		this.log("Joining call " + callId);
		this.initCallPromise = CallEngine.getCallWithId(callId)
			.then((result) =>
			{
				this.currentCall = result.call;
				isGroupCall = this.currentCall.associatedEntity.id.toString().startsWith("chat");
				return this.messengerFacade.openMessenger();
			})
			.then(() =>
			{
				return Hardware.init();
			})
			.then(() =>
			{
				this.createContainer();

				let hiddenButtons = [];
				if (this.currentCall instanceof PlainCall)
				{
					hiddenButtons.push('floorRequest');
				}
				if (!Util.shouldShowDocumentButton())
				{
					hiddenButtons.push('document');
				}

				Hardware.isCameraOn = !!video;

				this.callView = new View({
					container: this.container,
					baseZIndex: this.messengerFacade.getDefaultZIndex(),
					showChatButtons: true,
					userLimit: Util.getUserLimit(),
					language: this.language,
					layout: isGroupCall ? View.Layout.Grid : View.Layout.Centered,
					showRecordButton: this.featureRecord !== FeatureState.Disabled,
					microphoneId: Hardware.defaultMicrophone,
					hiddenButtons: hiddenButtons,
					blockedButtons: this.getBlockedButtons(this.currentCall.provider === Provider.Plain),
				});
				this.autoCloseCallView = true;
				this.bindCallViewEvents();
				this.callView.appendUsers(this.currentCall.getUsers());
				this.updateCallViewUsers(this.currentCall.id, this.getCallUsers(true));
				this.callView.show();
				this.showDocumentPromo();

				this.currentCall.useHdVideo(Hardware.preferHdQuality);
				if (Hardware.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(Hardware.defaultMicrophone);
				}
				if (Hardware.defaultCamera)
				{
					this.currentCall.setCameraId(Hardware.defaultCamera);
				}

				this.bindCallEvents();
				this.createVideoStrategy();

				if (video && !Hardware.hasCamera())
				{
					this.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
					video = false;
				}


				if (this.getCallUsers(true).length > this.getMaxActiveMicrophonesCount())
				{
					Hardware.isMicrophoneMuted = true;
					this.showAutoMicMuteNotification();
				}

				this.currentCall.answer({
					joinAsViewer: joinAsViewer
				});

				this._onUpdateLastUsedCameraId();
			})
			.finally(() =>
			{
				this.initCallPromise = null;
			});
	}

	leaveCurrentCall(force)
	{
		if (this.callView)
		{
			this.callView.releaseLocalMedia();
		}

		if (this.currentCall)
		{
			this.answeredOrDeclinedCalls.delete(this.currentCall.id);
			this.currentCall.hangup(force);
			if (force)
			{
				this.currentCall = null;
			}
		}

		if (this.callView)
		{
			this.callView.close();
		}

		this.hasStreamFromCall = false;
		this._stopLocalStream();
	}

	hasActiveCall()
	{
		return !!((this.currentCall && this.currentCall.isAnyoneParticipating()) || (this.callView));
	}

	hasVisibleCall()
	{
		return !!(this.callView && this.callView.visible && this.callView.size == View.Size.Full);
	}

	canRecord()
	{
		return DesktopApi.isDesktop() && DesktopApi.getApiVersion() >= 54;
	}

	isRecording()
	{
		return this.canRecord() && this.callRecordState != View.RecordState.Stopped;
	}

	useDevicesInCurrentCall(deviceList, isForceUse = false)
	{
		if (!this.currentCall || !this.currentCall.ready)
		{
			return;
		}

		for (let i = 0; i < deviceList.length; i++)
		{
			const deviceInfo = deviceList[i];

			switch (deviceInfo.kind)
			{
				case "audioinput":
					if (deviceInfo.deviceId === 'default' || isForceUse)
					{
						const newDeviceId = Hardware.getDefaultDeviceIdByGroupId(deviceInfo.groupId, 'audioinput');
						this.currentCall.setMicrophoneId(newDeviceId);

						if (this.currentCall instanceof BitrixCall)
						{
							this.callView.setMicrophoneId(newDeviceId);
						}
					}
					break;
				case "videoinput":
					if (deviceInfo.deviceId === 'default' || isForceUse)
					{
						this.currentCall.setCameraId(deviceInfo.deviceId);
					}

					if (this.reconnectingCameraId === deviceInfo.deviceId && !Hardware.isCameraOn)
					{
						this.updateCameraSettingsInCurrentCallAfterReconnecting(deviceInfo.deviceId)
					}
					break;
				case "audiooutput":
					if (this.callView && deviceInfo.deviceId === 'default' || isForceUse)
					{
						const newDeviceId = Hardware.getDefaultDeviceIdByGroupId(deviceInfo.groupId, 'audiooutput');
						this.callView.setSpeakerId(newDeviceId);
					}
					break;
			}
		}
	}

	removeDevicesFromCurrentCall(deviceList)
	{
		if (!this.currentCall || !this.currentCall.ready)
		{
			return;
		}

		for (let i = 0; i < deviceList.length; i++)
		{
			const deviceInfo = deviceList[i];

			switch (deviceInfo.kind)
			{
				case "audioinput":
					if (this.currentCall.microphoneId == deviceInfo.deviceId)
					{
						const microphoneIds = Object.keys(Hardware.microphoneList);
						let deviceId;

						if (microphoneIds.includes('default'))
						{
							const deviceGroup = Hardware.getDeviceGroupIdByDeviceId('default', 'audioinput');
							deviceId = Hardware.getDefaultDeviceIdByGroupId(deviceGroup, 'audioinput');
						}

						if (!deviceId)
						{
							deviceId = microphoneIds.length > 0 ? microphoneIds[0] : "";
						}

						this.currentCall.setMicrophoneId(deviceId);

						if (this.currentCall instanceof BitrixCall)
						{
							this.callView.setMicrophoneId(deviceId);
						}
					}
					break;
				case "videoinput":
					if (this.currentCall.cameraId == deviceInfo.deviceId)
					{
						const cameraIds = Object.keys(Hardware.cameraList);
						this.currentCall.setCameraId(cameraIds.length > 0 ? cameraIds[0] : "");
					}
					break;
				case "audiooutput":
					if (this.callView && this.callView.speakerId == deviceInfo.deviceId)
					{
						const speakerIds = Object.keys(Hardware.audioOutputList);
						let deviceId;

						if (speakerIds.includes('default'))
						{
							const deviceGroup = Hardware.getDeviceGroupIdByDeviceId('default', 'audiooutput');
							deviceId = Hardware.getDefaultDeviceIdByGroupId(deviceGroup, 'audiooutput');
						}

						if (!deviceId)
						{
							deviceId = speakerIds.length > 0 ? speakerIds[0] : "";
						}

						this.callView.setSpeakerId(deviceId);
					}
					break;
			}
		}
	}

	showChat()
	{
		if (DesktopApi.isDesktop() && this.floatingWindow)
		{
			this.detached = true;
			this.callView.hide();
			this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);
			Util.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then((result) =>
			{
				this.floatingWindow.setAvatars(result);
				this.floatingWindow.show();
			});

			this.container.style.width = 0;
		}
		else
		{
			this.fold(Text.decode(this.currentCall.associatedEntity.name));
		}
	}

	fold(foldedCallTitle)
	{
		if (this.folded || (DesktopApi.isDesktop() && this.floatingWindow))
		{
			return;
		}
		if (!foldedCallTitle && this.currentCall)
		{
			foldedCallTitle = Text.decode(this.currentCall.associatedEntity.name)
		}

		this.folded = true;
		if (this.resizeObserver)
		{
			this.resizeObserver.unobserve(this.container);
		}
		this.container.classList.add('bx-messenger-call-overlay-folded');
		this.callView.setTitle(foldedCallTitle);
		this.callView.setSize(View.Size.Folded);
		this.callViewState = ViewState.Folded;
		if (this.sidebar)
		{
			this.sidebar.toggleHidden(true);
		}
		this.closePromo();

		BX.onCustomEvent(this, "CallController::onFold", {});
	}

	setCallEditorMaxWidth(maxWidth)
	{
		if (maxWidth != this.maxEditorWidth)
		{
			this.maxEditorWidth = maxWidth;
			this._onResize();
		}
	}

	findCallEditorWidth()
	{
		const containerWidth = this.container.clientWidth;
		const editorWidth = containerWidth < (this.maxEditorWidth + View.MIN_WIDTH) ? containerWidth - View.MIN_WIDTH : this.maxEditorWidth;
		const callWidth = containerWidth - editorWidth;

		return {callWidth: callWidth, editorWidth: editorWidth};
	}

	showDocumentsMenu()
	{
		const targetNodeWidth = this.callView.buttons.document.elements.root.offsetWidth;
		const resumesArticleCode = Util.getResumesArticleCode();
		const documentsArticleCode = Util.getDocumentsArticleCode();

		let menuItems = [
				{
					text: BX.message('IM_M_CALL_MENU_CREATE_RESUME'),
					onclick: () =>
					{
						this.documentsMenu.close();
						this.maybeShowDocumentEditor({
							type: DocumentType.Resume,
						}, resumesArticleCode);
					}
				},
				{
					text: BX.message('IM_M_CALL_MENU_CREATE_FILE'),
					items: [
						{
							text: BX.message('IM_M_CALL_MENU_CREATE_FILE_DOC'),
							onclick: () =>
							{
								this.documentsMenu.close();
								this.maybeShowDocumentEditor({
									type: DocumentType.Blank,
									typeFile: FILE_TYPE_DOCX,
								}, documentsArticleCode);
							}
						},
						{
							text: BX.message('IM_M_CALL_MENU_CREATE_FILE_XLS'),
							onclick: () =>
							{
								this.documentsMenu.close();
								this.maybeShowDocumentEditor({
									type: DocumentType.Blank,
									typeFile: FILE_TYPE_XLSX,
								}, documentsArticleCode);
							}
						},
						{
							text: BX.message('IM_M_CALL_MENU_CREATE_FILE_PPT'),
							onclick: () =>
							{
								this.documentsMenu.close();
								this.maybeShowDocumentEditor({
									type: DocumentType.Blank,
									typeFile: FILE_TYPE_PPTX,
								}, documentsArticleCode);
							}
						}
						,
					],
				},
			]
		;

		if (!resumesArticleCode)
		{
			menuItems.push({
				text: BX.message('IM_M_CALL_MENU_OPEN_LAST_RESUME'),
				cacheable: true,
				items: [
					{
						id: "loading",
						text: BX.message('IM_M_CALL_MENU_LOADING_RESUME_LIST'),
					}
				],
				events: {
					onSubMenuShow: (e) => this.buildPreviousResumesSubmenu(e.target)
				}
			});
		}

		this.documentsMenu = new BX.PopupMenuWindow({
			angle: false,
			bindElement: this.callView.buttons.document.elements.root,
			targetContainer: this.container,
			offsetTop: -15,
			bindOptions: {position: "top"},
			cacheable: false,
			subMenuOptions: {
				maxWidth: 450
			},
			events: {
				onShow: (event) =>
				{
					const popup = event.getTarget();
					popup.getPopupContainer().style.display = 'block'; // bad hack

					const offsetLeft = (targetNodeWidth / 2) - popup.getPopupContainer().offsetWidth / 2;
					popup.setOffset({offsetLeft: offsetLeft + 40});
					popup.setAngle({offset: popup.getPopupContainer().offsetWidth / 2 - 17});
				},
				onDestroy: () => this.documentsMenu = null
			},
			items: menuItems,
		});

		this.documentsMenu.show();
	}

	buildPreviousResumesSubmenu(menuItem)
	{
		BX.ajax.runAction('disk.api.integration.messengerCall.listResumesInChatByCall', {
			data: {
				callId: this.currentCall.id
			}
		}).then((response) =>
		{
			const resumeList = response.data.resumes;

			if (resumeList.length > 0)
			{
				resumeList.forEach((resume) =>
				{
					menuItem.getSubMenu().addMenuItem({
						id: resume.id,
						text: resume.object.createDate + ': ' + resume.object.name,
						onclick: () =>
						{
							this.documentsMenu.close();
							this.viewDocumentByLink(resume.links.view);
						}
					});
				})
			}
			else
			{
				menuItem.getSubMenu().addMenuItem({
					id: 'nothing',
					text: BX.message('IM_M_CALL_MENU_NO_RESUME'),
					disabled: true
				});
			}

			menuItem.getSubMenu().removeMenuItem('loading');
			menuItem.adjustSubMenu();
		})
	}

	maybeShowDocumentEditor(options, articleCode)
	{
		if (articleCode)
		{
			if (articleCode)
			{
				BX.UI.InfoHelper.show(articleCode);
				return;
			}
		}
		this.showDocumentEditor(options);
	}

	showDocumentEditor(options)
	{
		options = options || {};
		let openAnimation = true;
		if (this.sidebar)
		{
			if (options.force)
			{
				this.sidebar.close(false);
				this.sidebar.destroy();
				this.sidebar = null;
				openAnimation = false;
			}
			else
			{
				return;
			}
		}

		if (this.callView)
		{
			this.callView.setButtonActive('document', true);
		}
		clearTimeout(this.showPromoPopupTimeout);

		this._createAndOpenSidebarWithIframe("about:blank", openAnimation);

		BX.loadExt('disk.onlyoffice-im-integration')
			.then(() =>
			{
				const docEditor = new BX.Disk.OnlyOfficeImIntegration.CreateDocument({
					dialog: {
						id: this.currentCall.associatedEntity.id,
					},
					call: {
						id: this.currentCall.id,
					},
					delegate: {
						setMaxWidth: this.setCallEditorMaxWidth.bind(this),
						onDocumentCreated: this._onDocumentCreated.bind(this),
					}
				});

				let promiseGetUrl;
				if (options.type === DocumentType.Resume)
				{
					promiseGetUrl = docEditor.getIframeUrlForTemplates();
				}
				else if (options.type === DocumentType.Blank)
				{
					promiseGetUrl = docEditor.getIframeUrlForCreate({
						typeFile: options.typeFile
					});
				}
				else
				{
					promiseGetUrl = docEditor.getIframeUrl({
						viewerItem: options.viewerItem
					});
				}

				promiseGetUrl
					.then((url) =>
					{
						this.docEditorIframe.src = url;
					})
					.catch((e) =>
					{
						console.error(e)
						this.closeDocumentEditor()
						alert(BX.message("IM_F_ERROR"));
					});

				this.docEditor = docEditor;
			})
			.catch((error) =>
			{
				console.error(error);
				this.closeDocumentEditor()
				alert(BX.message("IM_F_ERROR"));
			});

		if (this.resizeObserver)
		{
			this.resizeObserver.observe(this.container);
		}
	}

	closeDocumentEditor()
	{
		return new Promise((resolve) =>
		{
			if (this.docEditor && this.docEditorIframe)
			{
				this.docEditor.onCloseIframe(this.docEditorIframe);
			}
			if (this.container && this.resizeObserver)
			{
				this.resizeObserver.unobserve(this.container);
			}
			if (this.callView)
			{
				this.callView.setButtonActive('document', false);
				this.callView.removeMaxWidth();
			}
			if (!this.sidebar)
			{
				return resolve();
			}
			const oldSidebar = this.sidebar;
			this.sidebar = null;
			oldSidebar.close().then(() =>
			{
				this.docEditor = null;
				this.docEditorIframe = null;
				oldSidebar.destroy();
				this.maxEditorWidth = this.docCreatedForCurrentCall ? DOC_EDITOR_WIDTH : DOC_TEMPLATE_WIDTH;
				if (!this.callView)
				{
					this.removeContainer();
					resolve();
				}
			});
		});
	}

	viewDocumentByLink(url)
	{
		if (this.sidebar)
		{
			return;
		}
		if (this.callView)
		{
			this.callView.setButtonActive('document', true);
		}

		this.maxEditorWidth = DOC_EDITOR_WIDTH;
		this._createAndOpenSidebarWithIframe(url);
	}

	_createAndOpenSidebarWithIframe(url, animation)
	{
		animation = animation === true;
		const result = this.findCallEditorWidth();
		const callWidth = result.callWidth;
		const editorWidth = result.editorWidth;

		this.callView.setMaxWidth(callWidth);
		this.sidebar = new Sidebar({
			container: this.container,
			width: editorWidth,
			events: {
				onCloseClicked: this.onSideBarCloseClicked.bind(this)
			}
		});
		this.sidebar.open(animation);

		const loader = new BX.Loader({
			target: this.sidebar.elements.contentContainer
		});
		loader.show();

		const docEditorIframe = BX.create("iframe", {
			attrs: {
				src: url,
				frameborder: "0"
			},
			style: {
				display: "none",
				border: "0",
				margin: "0",
				width: "100%",
				height: "100%",
			}
		});

		docEditorIframe.addEventListener('load',
			() =>
			{
				loader.destroy();
				docEditorIframe.style.display = 'block';
			},
			{once: true}
		);
		docEditorIframe.addEventListener('error', (error) =>
		{
			console.error(error);
			this.closeDocumentEditor()
			alert(BX.message("IM_F_ERROR"));
		})
		this.sidebar.elements.contentContainer.appendChild(docEditorIframe);
		this.docEditorIframe = docEditorIframe;
	}

	_onDocumentCreated()
	{
		this.docCreatedForCurrentCall = true;
		if (this.currentCall)
		{
			this.currentCall.sendCustomMessage(DOC_CREATED_EVENT, true)
		}
	}

	onSideBarCloseClicked()
	{
		this.closeDocumentEditor();
	}

	_ensureDocumentEditorClosed()
	{
		return new Promise((resolve, reject) =>
		{
			if (!this.sidebar)
			{
				return resolve();
			}

			const messageBox = new MessageBox({
				message: BX.message('IM_CALL_CLOSE_DOCUMENT_EDITOR_TO_ANSWER'),
				buttons: MessageBoxButtons.OK_CANCEL,
				okCaption: BX.message('IM_CALL_CLOSE_DOCUMENT_EDITOR_YES'),
				cancelCaption: BX.message('IM_CALL_CLOSE_DOCUMENT_EDITOR_NO'),
				onOk: () =>
				{
					this.closeDocumentEditor().then(() => resolve());
					return true;
				},
				onCancel: () =>
				{
					reject();
					return true;
				}
			});
			messageBox.show();
		})
	}

	onDocumentPromoActionClicked()
	{
		this.closePromo();

		const articleCode = Util.getResumesArticleCode();
		if (articleCode)
		{
			BX.UI.InfoHelper.show(articleCode); //@see \Bitrix\Disk\Integration\MessengerCall::getInfoHelperCodeForDocuments()
			return;
		}

		this.showDocumentEditor({
			type: DocumentType.Resume,
		});
	}

	onDocumentPromoClosed(e)
	{
		const data = e.getData();
		if (data.dontShowAgain)
		{
			this.emit(Events.onPromoViewed, {
				code: DOCUMENT_PROMO_CODE
			})
		}

		this.documentPromoPopup = null;
	}

	unfold()
	{
		if (this.detached)
		{
			this.container.style.removeProperty('width');
			this.callView.show();
			this.detached = false;
			if (this.floatingWindow)
			{
				this.floatingWindow.hide();
			}
		}
		if (this.folded)
		{
			this.folded = false;
			this.container.classList.remove('bx-messenger-call-overlay-folded');
			this.callView.setSize(View.Size.Full);
			this.callViewState = ViewState.Opened;
			if (this.sidebar)
			{
				this.sidebar.toggleHidden(false);
				if (this.resizeObserver)
				{
					this.resizeObserver.observe(this.container);
				}
			}
		}
		BX.onCustomEvent(this, "CallController::onUnfold", {});
	}

	isFullScreen()
	{
		if ("webkitFullscreenElement" in document)
		{
			return (!!document.webkitFullscreenElement);
		}
		else if ("fullscreenElement" in document)
		{
			return (!!document.fullscreenElement);
		}
		return false;
	}

	toggleFullScreen()
	{
		if (this.isFullScreen())
		{
			this.exitFullScreen();
		}
		else
		{
			this.enterFullScreen();
		}
	}

	enterFullScreen()
	{
		if (this.messengerFacade.isSliderFocused())
		{
			BX.SidePanel.Instance.enterFullScreen();
		}
		else
		{
			if (Browser.isChrome() || Browser.isSafari())
			{
				document.body.webkitRequestFullScreen();
			}
			else if (Browser.isFirefox())
			{
				document.body.requestFullscreen();
			}
		}
	}

	exitFullScreen()
	{
		if (this.messengerFacade.isSliderFocused())
		{
			BX.SidePanel.Instance.exitFullScreen();
		}
		else
		{
			if (document.cancelFullScreen)
			{
				document.cancelFullScreen();
			}
			else if (document.mozCancelFullScreen)
			{
				document.mozCancelFullScreen();
			}
			else if (document.webkitCancelFullScreen)
			{
				document.webkitCancelFullScreen();
			}
			else if (document.exitFullscreen)
			{
				document.exitFullscreen();
			}
		}
	}

	showDocumentPromo()
	{
		if (!this.callView || !this.currentCall || !Util.shouldShowDocumentButton())
		{
			return false;
		}

		if (!this.messengerFacade.isPromoRequired(DOCUMENT_PROMO_CODE))
		{
			return false;
		}

		const documentButton = this.callView.buttons.document.elements.root;
		const bindElement = documentButton.querySelector('.bx-messenger-videocall-panel-icon');
		if (!bindElement)
		{
			return false;
		}
		this.documentPromoPopup = new PromoPopup({
			bindElement: bindElement,
			promoCode: DOCUMENT_PROMO_CODE,
			events: {
				onActionClick: this.onDocumentPromoActionClicked.bind(this),
				onClose: this.onDocumentPromoClosed.bind(this)
			}
		});
		this.showPromoPopupTimeout = setTimeout(() =>
		{
			if (this.folded)
			{
				return false;
			}
			this.documentPromoPopup.show();
		}, DOCUMENT_PROMO_DELAY);
	}

	showMaskPromo()
	{
		if (!this.callView || !this.currentCall || !BackgroundDialog.isMaskAvailable())
		{
			return false;
		}

		if (!this.messengerFacade.isPromoRequired(MASK_PROMO_CODE))
		{
			return false;
		}

		this.maskPromoPopup = new PromoPopup3D({
			callView: this.callView,
			events: {
				onClose: (e) =>
				{
					this.emit(Events.onPromoViewed, {
						code: MASK_PROMO_CODE
					})
					this.maskPromoPopup = null
				}
			}
		});

		this.showPromoPopup3dTimeout = setTimeout(
			() =>
			{
				if (!this.folded)
				{
					this.maskPromoPopup.show();
				}
			},
			MASK_PROMO_DELAY
		);
	}

	closePromo()
	{
		if (this.documentPromoPopup)
		{
			this.documentPromoPopup.close();
		}

		if (this.maskPromoPopup)
		{
			this.maskPromoPopup.close();
		}

		clearTimeout(this.showPromoPopupTimeout);
		clearTimeout(this.showPromoPopup3dTimeout);
	}

	_startRecordCall(type)
	{
		this.callView.setButtonActive('record', true);
		this.callRecordType = type;

		this.currentCall.sendRecordState({
			action: View.RecordState.Started,
			date: new Date()
		});

		this.callRecordState = View.RecordState.Started;
	}

	// event handlers

	_onCallNotificationClose()
	{
		clearTimeout(this.hideIncomingCallTimeout);
		this.messengerFacade.stopRepeatSound('ringtone');
		if (this.callNotification)
		{
			this.callNotification.destroy();
		}
	}

	_onCallNotificationDestroy()
	{
		this.messengerFacade.stopRepeatSound('ringtone');
		this.callNotification = null;
	}

	_onCallNotificationButtonClick(e)
	{
		const data = e.data;
		clearTimeout(this.hideIncomingCallTimeout);
		this.callNotification.close();
		switch (data.button)
		{
			case "answer":
				if (this.currentCall && !this.answeredOrDeclinedCalls.has(this.currentCall.id))
				{
					this.answeredOrDeclinedCalls.add(this.currentCall.id);
					this._onAnswerButtonClick(data.video);
				}
				break;
			case "decline":
				if (this.currentCall)
				{
					const callId = this.currentCall.id;
					this.answeredOrDeclinedCalls.add(callId);
					this.ignoreDeclinedCallsTimeout[callId] = setTimeout(
						() => this.answeredOrDeclinedCalls.delete(callId),
						15000
					);
					this.removeVideoStrategy();
					this.removeCallEvents();
					this.currentCall.decline();
					this.currentCall = null;
				}
				break;
		}
	}

	_onAnswerButtonClick(withVideo)
	{
		if (DesktopApi.isDesktop())
		{
			DesktopApi.activateWindow();
		}

		if (!this.isUserAgentSupported())
		{
			this.log("Error: unsupported user agent");
			this.removeVideoStrategy();
			this.removeCallEvents();
			this.currentCall.decline();
			this.currentCall = null;

			this.showUnsupportedNotification();
			return;
		}

		if (this.callView)
		{
			this.callView.destroy();
		}

		const dialogId = this.currentCall.associatedEntity && this.currentCall.associatedEntity.id ? this.currentCall.associatedEntity.id : false;
		let isGroupCall = dialogId.toString().startsWith("chat");
		this._ensureDocumentEditorClosed()
			.then(() =>
			{
				return this.messengerFacade.openMessenger(dialogId);
			})
			.then(() =>
			{
				return Hardware.init();
			})
			.then(() =>
			{
				if (!this.currentCall)
				{
					this.log('The call was destroyed while being answered');
					return;
				}

				this.createContainer();
				let hiddenButtons = [];
				if (this.currentCall instanceof PlainCall)
				{
					hiddenButtons.push('floorRequest');
				}
				if (!Util.shouldShowDocumentButton())
				{
					hiddenButtons.push('document');
				}
				Hardware.isCameraOn = withVideo && Hardware.hasCamera();
				this.callView = new View({
					container: this.container,
					baseZIndex: this.messengerFacade.getDefaultZIndex(),
					users: this.currentCall.users,
					userStates: this.currentCall.getUsers(),
					showChatButtons: true,
					showRecordButton: this.featureRecord !== FeatureState.Disabled,
					userLimit: Util.getUserLimit(),
					layout: isGroupCall ? View.Layout.Grid : View.Layout.Centered,
					microphoneId: Hardware.defaultMicrophone,
					blockedButtons: this.getBlockedButtons(this.currentCall.provider === Provider.Plain),
					hiddenButtons: hiddenButtons,
				});
				this.autoCloseCallView = true;
				if (this.callWithLegacyMobile)
				{
					this.callView.blockAddUser();
				}

				this.bindCallViewEvents();
				this.updateCallViewUsers(this.currentCall.id, this.getCallUsers(true));
				this.callView.show();
				this.showDocumentPromo();
				this.showMaskPromo();

				this.currentCall.useHdVideo(Hardware.preferHdQuality);
				if (Hardware.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(Hardware.defaultMicrophone);
				}
				if (Hardware.defaultCamera)
				{
					this.currentCall.setCameraId(Hardware.defaultCamera);
				}

				if (this.getCallUsers(true).length > this.getMaxActiveMicrophonesCount())
				{
					Hardware.isMicrophoneMuted = true;
					this.showAutoMicMuteNotification();
				}

				this.currentCall.answer({
					enableMicAutoParameters: Hardware.enableMicAutoParameters
				});

				this.createVideoStrategy();

				this._onUpdateLastUsedCameraId();
			});
	}

	_onCallConferenceNotificationButtonClick(e)
	{
		clearTimeout(this.hideIncomingCallTimeout);
		this.callNotification.close();
		switch (e.button)
		{
			case "answerConference":
				if (this.currentCall && 'id' in this.currentCall.associatedEntity)
				{
					this.answeredOrDeclinedCalls.add(this.currentCall.id);
					let dialogId = this.currentCall.associatedEntity.id.toString();
					if (dialogId.startsWith('chat'))
					{
						dialogId = dialogId.substring(4);
					}
					this.emit(Events.onOpenVideoConference, {dialogId: dialogId});
				}
				break;
			case "skipConference":
				if (this.currentCall)
				{
					this.answeredOrDeclinedCalls.add(this.currentCall.id);
					this.removeVideoStrategy();
					this.removeCallEvents();
					this.currentCall.decline();
					this.currentCall = null;
				}
				break;
		}
	}

	_onCallViewShow()
	{
		this.callView.setButtonCounter("chat", this.messengerFacade.getMessageCount());
		this.callViewState = ViewState.Opened;
	}

	_onCallViewClose()
	{
		this.callView.destroy();
		this.callViewState = ViewState.Closed;
		if (this.floatingWindow)
		{
			this.floatingWindow.close();
		}
		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.close();
		}
		if (this.documentsMenu)
		{
			this.documentsMenu.close();
		}
		if (DesktopApi.isDesktop())
		{
			DesktopApi.closeWindow(DesktopApi.findWindow('callBackground'));
		}
		this.closePromo();

		this._closeReconnectionBaloon();
		this._closeParticipantsVideoBaloon();
	}

	_onCallViewDestroy()
	{
		this.callView = null;
		this.folded = false;
		this.autoCloseCallView = true;
		if (this.sidebar)
		{
			BX.adjust(this.container, {
				style: {
					backgroundColor: "rgba(0, 0, 0, 0.5)",
					backdropFilter: "blur(5px)"
				}
			})
		}
		else
		{
			this.removeContainer();
			this.maxEditorWidth = DOC_TEMPLATE_WIDTH;
		}
	}

	_onCallViewBodyClick()
	{
		if (this.folded)
		{
			this.unfold();
		}
	}

	_onCallViewButtonClick(e)
	{
		const buttonName = e.buttonName;

		const handlers = {
			hangup: this._onCallViewHangupButtonClick.bind(this),
			close: this._onCallViewCloseButtonClick.bind(this),
			inviteUser: this._onCallViewInviteUserButtonClick.bind(this),
			toggleMute: this._onCallViewToggleMuteButtonClick.bind(this),
			toggleScreenSharing: this._onCallViewToggleScreenSharingButtonClick.bind(this),
			record: this._onCallViewRecordButtonClick.bind(this),
			toggleVideo: this._onCallViewToggleVideoButtonClick.bind(this),
			toggleSpeaker: this._onCallViewToggleSpeakerButtonClick.bind(this),
			showChat: this._onCallViewShowChatButtonClick.bind(this),
			floorRequest: this._onCallViewFloorRequestButtonClick.bind(this),
			showHistory: this._onCallViewShowHistoryButtonClick.bind(this),
			fullscreen: this._onCallViewFullScreenButtonClick.bind(this),
			document: this._onCallViewDocumentButtonClick.bind(this),
			microphoneSideIcon: this._onCallViewMicrophoneSideIconClick.bind(this),
			feedback: this._onCallViewFeedbackButtonClick.bind(this)
		};

		if (Type.isFunction(handlers[buttonName]))
		{
			handlers[buttonName].call(this, e);
		}
	}

	_onCallViewHangupButtonClick()
	{
		this.leaveCurrentCall();
	}

	_onCallViewCloseButtonClick()
	{
		if (this.callView)
		{
			this.callView.close();
		}
	}

	_onCallViewShowChatButtonClick()
	{
		this.messengerFacade.openMessenger(this.currentCall.associatedEntity.id);
		this.showChat();
	}

	_onCallViewFloorRequestButtonClick()
	{
		const floorState = this.callView.getUserFloorRequestState(CallEngine.getCurrentUserId());
		const talkingState = this.callView.getUserTalking(CallEngine.getCurrentUserId());

		this.callView.setUserFloorRequestState(CallEngine.getCurrentUserId(), !floorState);

		if (this.currentCall)
		{
			this.currentCall.requestFloor(!floorState);
		}

		clearTimeout(this.callViewFloorRequestTimeout);
		if (talkingState && !floorState)
		{
			this.callViewFloorRequestTimeout = setTimeout(() =>
			{
				if (this.currentCall)
				{
					this.currentCall.requestFloor(false);
				}
			}, 1500);
		}
	}

	/**
	 * Returns list of users, that are not currently connected
	 * @return {Array}
	 * @private
	 */
	_getDisconnectedUsers()
	{
		const result = [];
		const userStates = this.currentCall.getUsers();

		for (let userId in userStates)
		{
			if (userStates[userId] !== UserState.Connected)
			{
				const userData = Util.getUserCached(userId)
				if (userData)
				{
					result.push(userData);
				}
			}
		}

		return result;
	}

	_closeReconnectionBaloon()
	{
		if (this.reconnectionBaloon)
		{
			this.reconnectionBaloon.close();
			this.reconnectionBaloon = null;
		}
	}

	_onCallViewInviteUserButtonClick(e)
	{
		if (!this.messengerFacade.showUserSelector)
		{
			return;
		}

		const userStates = this.currentCall ? this.currentCall.getUsers() : {};
		const idleUsers = this.currentCall ? this._getDisconnectedUsers() : [];

		this.messengerFacade.showUserSelector({
			viewElement: this.callView.container,
			bindElement: e.node,
			zIndex: this.messengerFacade.getDefaultZIndex() + 200,
			darkMode: this.messengerFacade.isThemeDark(),
			idleUsers: idleUsers,
			allowNewUsers: Object.keys(userStates).length < Util.getUserLimit() - 1,
			onDestroy: this._onInvitePopupDestroy.bind(this),
			onSelect: this._onInvitePopupSelect.bind(this)
		}).then((inviteCloser) => {
			this.invitePopup = inviteCloser;
			this.callView.setHotKeyTemporaryBlock(true);
		});
	}

	_onCallViewToggleMuteButtonClick(e)
	{
		const currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
		if (currentRoom && currentRoom.speaker != this.userId && !e.muted)
		{
			this.currentCall.requestRoomSpeaker();
			return;
		}

		if (!this.currentCall.microphoneId && !e.muted)
		{
			this.currentCall.setMicrophoneId(Hardware.defaultMicrophone);
		}

		Hardware.isMicrophoneMuted = e.muted;

		if (this.floatingWindow)
		{
			this.floatingWindow.setAudioMuted(e.muted);
		}

		if (this.mutePopup)
		{
			this.mutePopup.close();
		}
		if (!e.muted)
		{
			this.allowMutePopup = true;
		}

		if (this.isRecording())
		{
			BXDesktopSystem.CallRecordMute(e.muted);
		}
	}

	_onCallViewRecordButtonClick(event)
	{
		if (event.recordState === View.RecordState.Started)
		{
			if (this.featureRecord === FeatureState.Limited)
			{
				this.messengerFacade.openHelpArticle('call_record');
				return;
			}

			if (this.featureRecord === FeatureState.Disabled)
			{
				return;
			}

			if (this.canRecord())
			{
				const forceRecord = BX.prop.getBoolean(event, "forceRecord", View.RecordType.None);
				if (forceRecord !== View.RecordType.None)
				{
					this._startRecordCall(forceRecord);
				}
				else if (DesktopApi.isDesktop() && DesktopApi.getApiVersion() > 55)
				{
					if (!this.callRecordMenu)
					{
						this.callRecordMenu = new BX.PopupMenuWindow({
							bindElement: event.node,
							targetContainer: this.callView.container,
							items: [
								{
									text: BX.message('IM_M_CALL_MENU_RECORD_VIDEO'),
									onclick: (event, item) =>
									{
										this._startRecordCall(View.RecordType.Video);
										item.getMenuWindow().close();
									}
								},
								{
									text: BX.message('IM_M_CALL_MENU_RECORD_AUDIO'),
									onclick: (event, item) =>
									{
										this._startRecordCall(View.RecordType.Audio);
										item.getMenuWindow().close();
									}
								}
							],
							autoHide: true,
							angle: {position: "top", offset: 80},
							offsetTop: 0,
							offsetLeft: -25,
							events: {
								onPopupClose: () => this.callRecordMenu.destroy(),
								onPopupDestroy: () => this.callRecordMenu = null
							}
						});
					}
					this.callRecordMenu.toggle();

					return;
				}

				this.callView.setButtonActive('record', true);
			}
			else
			{
				if (window.BX.Helper)
				{
					window.BX.Helper.show("redirect=detail&code=12398134");
				}

				return;
			}
		}
		else if (event.recordState === View.RecordState.Paused)
		{
			if (this.canRecord())
			{
				BXDesktopSystem.CallRecordPause(true);
			}
		}
		else if (event.recordState === View.RecordState.Resumed)
		{
			if (this.canRecord())
			{
				BXDesktopSystem.CallRecordPause(false);
			}
		}
		else if (event.recordState === View.RecordState.Stopped)
		{
			this.callView.setButtonActive('record', false);
		}

		this.currentCall.sendRecordState({
			action: event.recordState,
			date: new Date()
		});

		this.callRecordState = event.recordState;
	}

	_onCallViewToggleScreenSharingButtonClick()
	{
		if (this.featureScreenSharing === FeatureState.Limited)
		{
			this.messengerFacade.openHelpArticle('call_screen_sharing');
			return;
		}

		if (this.featureScreenSharing === FeatureState.Disabled)
		{
			return;
		}

		if (this.currentCall.isScreenSharingStarted())
		{
			if (this.floatingScreenShareWindow)
			{
				this.floatingScreenShareWindow.close();
			}
			if (this.webScreenSharePopup)
			{
				this.webScreenSharePopup.close();
			}
			if (this.documentPromoPopup)
			{
				this.documentPromoPopup.close();
			}
			this.currentCall.stopScreenSharing();

			if (this.isRecording())
			{
				BXDesktopSystem.CallRecordStopSharing();
			}
		}
		else
		{
			this.currentCall.startScreenSharing();
			CallEngine.getRestClient().callMethod("im.call.onShareScreen", {callId: this.currentCall.id});
		}
	}

	_onCallViewToggleVideoButtonClick(e)
	{
		Hardware.isCameraOn = e.video;
		if (!Hardware.initialized)
		{
			return;
		}
		if (e.video && Object.values(Hardware.cameraList).length === 0)
		{
			return;
		}
		if (!e.video)
		{
			this.callView.releaseLocalMedia();
		}

		if (!this.currentCall.cameraId && e.video)
		{
			this.currentCall.setCameraId(Hardware.defaultCamera);
		}
	}

	_onCallViewToggleSpeakerButtonClick(e)
	{
		const currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
		if (currentRoom && currentRoom.speaker != this.userId)
		{
			alert("only room speaker can turn on sound");
			return;
		}

		this.callView.muteSpeaker(!e.speakerMuted);

		if (e.fromHotKey)
		{
			BX.UI.Notification.Center.notify({
				content: BX.message(this.callView.speakerMuted ? 'IM_M_CALL_MUTE_SPEAKERS_OFF' : 'IM_M_CALL_MUTE_SPEAKERS_ON'),
				position: "top-right",
				autoHideDelay: 3000,
				closeButton: true
			});
		}
	}

	_onCallViewMicrophoneSideIconClick()
	{
		const currentRoom = this.currentCall.currentRoom && this.currentCall.currentRoom();
		if (currentRoom)
		{
			this.toggleRoomMenu(this.callView.buttons.microphone.elements.icon);
		}
		else
		{
			this.toggleRoomListMenu(this.callView.buttons.microphone.elements.icon);
		}
	}

	_onCallViewFeedbackButtonClick()
	{
		BX.loadExt('ui.feedback.form').then(() => {
			BX.UI.Feedback.Form.open({
				id: `call_feedback_${this.currentCall.id}-${this.currentCall.instanceId}-${Math.random()}`,
				forms: [
					{ zones: ['ru', 'by', 'kz'], id: 406, sec: '9lhjhn', lang: 'ru' },
					{ zones: ['de'], id: 754, sec: '6upe49', lang: 'de' },
					{ zones: ['es'], id: 750, sec: 'whk4la', lang: 'es' },
					{ zones: ['com.br'], id: 752, sec: 'is01cs', lang: 'com.br' },
					{ zones: ['en'], id: 748, sec: 'pds0h6', lang: 'en' },
				],
				presets: {
					sender_page: 'call',
					call_type: this.currentCall.provider,
					call_amount: this.currentCall.users.length + 1,
					call_id: `id: ${this.currentCall.id}, instanceId: ${this.currentCall.instanceId}`,
					id_of_user: this.currentCall.userId,
					from_domain: location.origin
				},
			});
		})
	}

	_onCallViewShowHistoryButtonClick()
	{
		this.messengerFacade.openHistory(this.currentCall.associatedEntity.id);
	}

	_onCallViewFullScreenButtonClick()
	{
		if (this.folded)
		{
			this.unfold();
		}
		this.toggleFullScreen();
	}

	_onCallViewDocumentButtonClick()
	{
		this.sidebar ? this.closeDocumentEditor() : this.showDocumentsMenu();
	}

	_onCallViewReplaceCamera(e)
	{
		if (this.reconnectingCameraId) {
			this.setReconnectingCameraId(null);
		}
		if (this.currentCall)
		{
			this.currentCall.setCameraId(e.deviceId);
		}

		// update default camera
		Hardware.defaultCamera = e.deviceId;
	}

	_onCallViewReplaceMicrophone(e)
	{
		if (this.currentCall)
		{
			this.currentCall.setMicrophoneId(e.deviceId)
			this.callView.setMicrophoneId(e.deviceId);
		}

		// update default microphone
		Hardware.defaultMicrophone = e.deviceId;
	}

	_onCallViewReplaceSpeaker(e)
	{
		Hardware.defaultSpeaker = e.deviceId;
	}

	_onCallViewHasMainStream(e)
	{
		if (this.currentCall && this.currentCall instanceof BitrixCall)
		{
			this.currentCall.setMainStream(e.userId);
		}
	}

	_onCallViewChangeHdVideo(e)
	{
		Hardware.preferHdQuality = e.allowHdVideo;
	}

	_onCallViewChangeMicAutoParams(e)
	{
		Hardware.enableMicAutoParameters = e.allowMicAutoParams;
	}

	_onCallViewChangeFaceImprove(e)
	{
		if (!DesktopApi.isDesktop())
		{
			return;
		}

		DesktopApi.setCameraSmoothingStatus(e.faceImproveEnabled);
	}

	_onCallViewOpenAdvancedSettings()
	{
		this.messengerFacade.openSettings({onlyPanel: 'hardware'});
	}

	_onCallViewSetCentralUser(e)
	{
		if (e.stream && this.floatingWindow)
		{
			this.floatingWindowUser = e.userId;
			//this.floatingWindow.setStream(e.stream);
		}
	}

	_onCallUserInvited(e)
	{
		if (this.callView)
		{
			this.updateCallViewUsers(this.currentCall.id, [e.userId]);
			this.callView.addUser(e.userId);
		}
	}

	_onCallDestroy()
	{
		let callDetails;
		if (this.currentCall)
		{
			this.removeVideoStrategy();
			this.removeCallEvents();
			callDetails = this.#getCallDetail(this.currentCall);
			this.currentCall = null;
		}
		this.callWithLegacyMobile = false;

		if (this.callNotification)
		{
			this.callNotification.close();
		}
		if (this.invitePopup)
		{
			this.invitePopup.close();
		}

		if (this.isRecording())
		{
			BXDesktopSystem.CallRecordStop();
		}
		this.callRecordState = View.RecordState.Stopped;
		this.callRecordType = View.RecordType.None;
		if (this.callRecordMenu)
		{
			this.callRecordMenu.close();
		}

		if (this.callView && this.autoCloseCallView)
		{
			this.callView.close();
		}

		this.hasStreamFromCall = false;
		this._stopLocalStream();

		if (this.floatingWindow)
		{
			this.floatingWindow.close();
		}
		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.close();
		}
		if (this.webScreenSharePopup)
		{
			this.webScreenSharePopup.close();
		}
		if (this.mutePopup)
		{
			this.mutePopup.close();
		}
		if (DesktopApi.isDesktop())
		{
			DesktopApi.closeWindow(DesktopApi.findWindow('callBackground'));
		}
		this.closePromo();

		this.allowMutePopup = true;
		this.docCreatedForCurrentCall = false;
		this._closeReconnectionBaloon();
		this._closeParticipantsVideoBaloon();

		this.messengerFacade.stopRepeatSound('dialtone');
		this.messengerFacade.stopRepeatSound('ringtone');

		this.emit(Events.onCallDestroyed, {
			callDetails: callDetails
		})
	}

	loopConnectionQuality(userId, quality, timeout = 200)
	{
		this.loopTimers[userId] = setTimeout(() => {
			if (this.callView)
			{
				this.callView.setUserConnectionQuality(userId, quality);
				const newQuality = quality >= 4 ? 1 : quality + 1;
				this.loopConnectionQuality(userId, newQuality, timeout);
			}
		}, timeout);
	}

	clearConnectionQualityTimer(userId)
	{
		if (this.loopTimers[userId] !== undefined)
		{
			clearTimeout(this.loopTimers[userId]);
			delete this.loopTimers[userId];
		}
	};

	_onCallUserStateChanged(e)
	{
		setTimeout(this.updateFloatingWindowContent.bind(this), 100);
		if (this.callView)
		{
			this.callView.setUserState(e.userId, e.state);
			if (e.isLegacyMobile)
			{
				this.callView.blockAddUser();
				this.callView.blockSwitchCamera();
				this.callView.blockScreenSharing();
				this.callView.disableMediaSelection();
				this.callView.updateButtons();
			}

			if (this.currentCall && this.currentCall.provider === Provider.Plain) {
				if (e.state === UserState.Connected)
				{
					this.callView.unblockAddUser();
				}
				else
				{
					this.callView.blockAddUser();
				}
			}
		}

		if (e.state !== UserState.Connected && this.loopTimers[e.userId] === undefined)
		{
			this.loopConnectionQuality(e.userId, 1);
		}

		if (e.state == UserState.Connecting || e.state == UserState.Connected)
		{
			this.messengerFacade.stopRepeatSound('dialtone');
		}

		if (e.state == UserState.Connected)
		{
			this.clearConnectionQualityTimer(e.userId);
			this.callView.setUserConnectionQuality(e.userId, 5);

			if (!e.isLegacyMobile)
			{
				this.callView.unblockButtons(['camera', 'floorRequest', 'screen']);
			}

			if (this.callRecordState === View.RecordState.Stopped)
			{
				this.callView.unblockButtons(['record']);
			}

			if (this.currentCall.provider === Provider.Plain) {
				this.callView.unblockAddUser();
			}

			/*Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(Util.getCustomMessage("IM_M_CALL_USER_CONNECTED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));*/
		}
		else if (e.state == UserState.Idle && e.previousState == UserState.Connected)
		{
			/*Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(Util.getCustomMessage("IM_M_CALL_USER_DISCONNECTED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));*/
		}
		else if (e.state == UserState.Failed)
		{
			if (e.networkProblem)
			{
				this.showNetworkProblemNotification(BX.message("IM_M_CALL_TURN_UNAVAILABLE"));
			}
			else
			{
				Util.getUser(this.currentCall.id, e.userId).then((userData) =>
				{
					this.showNotification(Util.getCustomMessage("IM_M_CALL_USER_FAILED", {
						gender: userData.gender,
						name: userData.name
					}));
				});
			}
		}
		else if (e.state == UserState.Declined)
		{
			Util.getUser(this.currentCall.id, e.userId).then((userData) =>
			{
				this.showNotification(Util.getCustomMessage("IM_M_CALL_USER_DECLINED", {
					gender: userData.gender,
					name: userData.name
				}));
			});
		}
		else if (e.state == UserState.Busy)
		{
			Util.getUser(this.currentCall.id, e.userId).then((userData) =>
			{
				this.showNotification(Util.getCustomMessage("IM_M_CALL_USER_BUSY", {
					gender: userData.gender,
					name: userData.name
				}));
			});
		}
	}

	_onCallUserMicrophoneState(e)
	{
		if (!this.callView)
		{
			return;
		}
		if (e.userId == this.userId)
		{
			Hardware.isMicrophoneMuted = !e.microphoneState;
		}
		else
		{
			this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
		}
	}

	_onCallUserCameraState(e)
	{
		if (!this.callView)
		{
			return;
		}
		this.callView.setUserCameraState(e.userId, e.cameraState);
	}

	_onCameraPublishing(e)
	{
		if (!this.callView)
		{
			return;
		}

		if (e.publishing)
		{
			this.callView.blockSwitchCamera();
			this.callView.updateButtons();
		}
		else
		{
			this.callView.unblockSwitchCamera();
			this.callView.updateButtons();
		}
	}

	_onMicrophonePublishingd(e)
	{
		if (!this.callView)
		{
			return;
		}

		if (e.publishing)
		{
			this.callView.blockSwitchMicrophone();
			this.callView.updateButtons();
		}
		else
		{
			this.callView.unblockSwitchMicrophone();
			this.callView.updateButtons();
		}
	}

	_onCallUserVideoPaused(e)
	{
		if (!this.callView)
		{
			return;
		}
		this.callView.setUserVideoPaused(e.userId, e.videoPaused);
	}

	_onCallLocalMediaReceived(e)
	{
		this.log("Received local media stream " + e.tag);
		this.hasStreamFromCall = true;
		if (this.callView)
		{
			const flipVideo = (e.tag == "main" || e.mediaRenderer) ? Hardware.enableMirroring : false;

			this.callView.setLocalStream(e);
			this.callView.flipLocalVideo(flipVideo);

			this.callView.setButtonActive("screen", this.currentCall.isScreenSharingStarted());
			if (this.currentCall.isScreenSharingStarted())
			{
				if (!DesktopApi.isDesktop())
				{
					this.showWebScreenSharePopup();
				}
				this.callView.blockSwitchCamera();
				this.callView.updateButtons();
			}
			else
			{
				if (this.floatingScreenShareWindow)
				{
					this.floatingScreenShareWindow.close();
				}
				if (this.webScreenSharePopup)
				{
					this.webScreenSharePopup.close();
				}
				if (this.isRecording())
				{
					BXDesktopSystem.CallRecordStopSharing();
				}

				if (!this.currentCall.callFromMobile)
				{
					this.callView.unblockSwitchCamera();
					this.callView.updateButtons();
				}
			}

			if (this.localStream)
			{
				this._stopLocalStream();
			}
		}

		if (this.currentCall && Hardware.isCameraOn && e.tag === 'main' && e.stream.getVideoTracks().length === 0)
		{
			this.showNotification(BX.message("IM_CALL_CAMERA_ERROR_FALLBACK_TO_MIC"));
			Hardware.isCameraOn = false;
		}
	}

	_onCallLocalCameraFlip(e)
	{
		this._onCallLocalCameraFlipInDesktop(e.data.enableMirroring);
	}

	_onCallLocalCameraFlipInDesktop(e)
	{
		console.error('FLIPPING LOCAL VIDEO');
		if (this.callView)
		{
			this.callView.flipLocalVideo(e);
		}
	}

	_onCallLocalMediaStopped(e)
	{
		// do nothing
	}

	_onCallRemoteMediaReceived(e)
	{
		if (this.callView)
		{
			if ('track' in e)
			{
				this.callView.setUserMedia(e.userId, e.kind, e.track)
			}
			if ('mediaRenderer' in e && e.mediaRenderer.kind === 'audio')
			{
				this.callView.setUserMedia(e.userId, 'audio', e.mediaRenderer.stream.getAudioTracks()[0]);
			}
			if ('mediaRenderer' in e && (e.mediaRenderer.kind === 'video' || e.mediaRenderer.kind === 'sharing'))
			{
				this.callView.setVideoRenderer(e.userId, e.mediaRenderer);
			}
		}
	}

	_onCallRemoteMediaStopped(e)
	{
		if (this.callView)
		{
			if ('mediaRenderer' in e)
			{
				if (e.kind === 'video' || e.kind === 'sharing')
				{
					e.mediaRenderer.stream = null;
					this.callView.setVideoRenderer(e.userId, e.mediaRenderer);
				}
			}
			else
			{
				this.callView.setUserMedia(e.userId, e.kind, null);
			}
		}
	}

	_prepareLocalStream(fallback = false)
	{
		const video = {};

		if (Hardware.defaultCamera)
		{
			video.deviceId = { exact: Hardware.defaultCamera };
		}

		if (!fallback)
		{
			video.width = { ideal: 1280 };
			video.height = { ideal: 720 };
		}

		const constraints = { audio: false, video };
		navigator.mediaDevices.getUserMedia(constraints)
			.then((stream) =>
			{
				this.localStream = stream;

				if (!this.initCallPromise)
				{
					this._stopLocalStream();
				}
				else if (this.callView)
				{
					const streamData = {
						stream,
						tag: 'main',
						flipVideo: Hardware.enableMirroring,
					};

					this.callView.setLocalStream(streamData);
				}
			})
			.catch((error) =>
			{
				this.log(`Attempt to get video (fallback: ${!!fallback}) for call before it has been created failed`, error);
				if (!fallback)
				{
					this._prepareLocalStream(true);
				}
			});
	}

	_stopLocalStream()
	{
		if (this.localStream)
		{
			Util.stopMediaStream(this.localStream);
			this.localStream = null;
		}
	}

	_onCallBadNetworkIndicator(e)
	{
		if (this.callView)
		{
			this.callView.setBadNetworkIndicator(e.userId, e.badNetworkIndicator);
		}
	}

	_onCallConnectionQualityChanged(e)
	{
		this.clearConnectionQualityTimer(e.userId);

		this.callView.setUserConnectionQuality(e.userId, e.score);
	}

	_onCallToggleRemoteParticipantVideo(e)
	{
		if (this.toogleParticipantsVideoBaloon)
		{
			if (e.isVideoShown)
			{
				this._closeParticipantsVideoBaloon();
			}

			return;
		}

		if (!e.isVideoShown)
		{
			this.toogleParticipantsVideoBaloon = BX.UI.Notification.Center.notify({
				content: Text.encode(BX.message('IM_M_CALL_REMOTE_PARTICIPANTS_VIDEO_MUTED')),
				autoHide: false,
				position: "top-right",
				closeButton: false,
			})
		}
	}

	_closeParticipantsVideoBaloon()
	{
		if (this.toogleParticipantsVideoBaloon)
		{
			this.toogleParticipantsVideoBaloon.close();
			this.toogleParticipantsVideoBaloon = null;
		}
	}

	_onCallUserVoiceStarted(e)
	{
		if (e.local)
		{
			if (this.currentCall.muted && this.isMutedPopupAllowed())
			{
				this.showMicMutedNotification();
			}
			return;
		}

		this.talkingUsers[e.userId] = true;
		if (this.callView)
		{
			this.callView.setUserTalking(e.userId, true);
			this.callView.setUserFloorRequestState(e.userId, false);
		}
		if (this.floatingWindow)
		{
			this.floatingWindow.setTalking(Object.keys(this.talkingUsers).map(function (id)
			{
				return Number(id);
			}));
		}
	}

	_onCallUserVoiceStopped(e)
	{
		if (e.local)
		{
			return;
		}

		if (this.talkingUsers[e.userId])
		{
			delete this.talkingUsers[e.userId];
		}
		if (this.callView)
		{
			this.callView.setUserTalking(e.userId, false);
		}
		if (this.floatingWindow)
		{
			this.floatingWindow.setTalking(Object.keys(this.talkingUsers).map(function (id)
			{
				return Number(id);
			}));
		}
	}

	_onUserStatsReceived(e)
	{
		if (this.callView)
		{
			this.callView.setUserStats(e.userId, e.report);
		}
	}

	_onCallUserScreenState(e)
	{
		if (this.callView)
		{
			this.callView.setUserScreenState(e.userId, e.screenState);
		}
		if (e.userId == CallEngine.getCurrentUserId())
		{
			this.callView.setButtonActive("screen", e.screenState);
			if (e.screenState)
			{
				if (!DesktopApi.isDesktop())
				{
					this.showWebScreenSharePopup();
				}
				this.callView.blockSwitchCamera();
			}
			else
			{
				if (this.floatingScreenShareWindow)
				{
					this.floatingScreenShareWindow.close();
				}
				if (this.webScreenSharePopup)
				{
					this.webScreenSharePopup.close();
				}
				if (this.isRecording())
				{
					BXDesktopSystem.CallRecordStopSharing();
				}

				if (!this.currentCall.callFromMobile)
				{
					this.callView.unblockSwitchCamera();
					this.callView.updateButtons();
				}
			}
			this.callView.updateButtons();
		}
	}

	_onCallUserRecordState(event)
	{
		this.callRecordState = event.recordState.state;
		this.callView.setRecordState(event.recordState);

		if (!this.canRecord() || event.userId != BX.message['USER_ID'])
		{
			return true;
		}

		if (
			event.recordState.state === View.RecordState.Started
			&& event.recordState.userId == BX.message['USER_ID']
		)
		{
			const windowId = View.RecordSource.Chat;
			const dialogId = this.currentCall.associatedEntity.id;
			const dialogName = this.currentCall.associatedEntity.name;
			const callId = this.currentCall.id;
			const callDate = BX.date.format(this.formatRecordDate);

			let fileName = BX.message('IM_CALL_RECORD_NAME');
			if (fileName)
			{
				fileName = fileName
					.replace('#CHAT_TITLE#', dialogName)
					.replace('#CALL_ID#', callId)
					.replace('#DATE#', callDate)
				;
			}
			else
			{
				fileName = "call_record_" + this.currentCall.id;
			}

			CallEngine.getRestClient().callMethod("im.call.onStartRecord", {callId: this.currentCall.id});

			console.error('DIALOG ID', dialogId);
			BXDesktopSystem.CallRecordStart({
				windowId: windowId,
				fileName: fileName,
				callId: callId,
				callDate: callDate,
				dialogId: dialogId,
				dialogName: dialogName,
				video: this.callRecordType !== View.RecordType.Audio,
				muted: Hardware.isMicrophoneMuted,
				cropTop: 72,
				cropBottom: 73,
				shareMethod: 'im.disk.record.share'
			});
		}
		else if (event.recordState.state === View.RecordState.Stopped)
		{
			BXDesktopSystem.CallRecordStop();
		}

		return true;
	}

	_onCallUserFloorRequest(e)
	{
		if (this.callView)
		{
			this.callView.setUserFloorRequestState(e.userId, e.requestActive);
		}
	}

	_onCallFailure(e)
	{
		const errorCode = e.code || e.name || e.error;

		let errorMessage;

		if (e.name == "VoxConnectionError" || e.name == "AuthResult")
		{
			Util.reportConnectionResult(e.call.id, false);
		}

		if (e.name == "AuthResult" || errorCode == "AUTHORIZE_ERROR")
		{
			errorMessage = BX.message("IM_CALL_ERROR_AUTHORIZATION");
		}
		else if (e.name == "Failed" && errorCode == 403)
		{
			errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
		}
		else if (errorCode == "ERROR_UNEXPECTED_ANSWER")
		{
			errorMessage = BX.message("IM_CALL_ERROR_UNEXPECTED_ANSWER");
		}
		else if (errorCode == "BLANK_ANSWER_WITH_ERROR_CODE")
		{
			errorMessage = BX.message("IM_CALL_ERROR_BLANK_ANSWER");
		}
		else if (errorCode == "BLANK_ANSWER")
		{
			errorMessage = BX.message("IM_CALL_ERROR_BLANK_ANSWER");
		}
		else if (errorCode == "ACCESS_DENIED")
		{
			errorMessage = BX.message("IM_CALL_ERROR_ACCESS_DENIED");
		}
		else if (errorCode == "NO_WEBRTC")
		{
			errorMessage = this.isHttps ? BX.message("IM_CALL_NO_WEBRT") : BX.message("IM_CALL_ERROR_HTTPS_REQUIRED");
		}
		else if (errorCode == "UNKNOWN_ERROR")
		{
			errorMessage = BX.message("IM_CALL_ERROR_UNKNOWN");
		}
		else if (errorCode == "NETWORK_ERROR")
		{
			errorMessage = BX.message("IM_CALL_ERROR_NETWORK");
		}
		else if (errorCode == "NotAllowedError")
		{
			errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
		}
		else
		{
			//errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
			errorMessage = BX.message("IM_CALL_ERROR_UNKNOWN_WITH_CODE").replace("#ERROR_CODE#", errorCode);
		}
		if (this.callView)
		{
			this.callView.showFatalError({text: errorMessage});
		}
		else
		{
			this.showNotification(errorMessage);
		}
		this.messengerFacade.stopRepeatSound('dialtone');
		this.autoCloseCallView = false;
		if (this.currentCall)
		{
			this.removeVideoStrategy();
			this.removeCallEvents();

			if (this.currentCallIsNew)
			{
				CallEngine.getRestClient().callMethod('im.call.interrupt', {callId: this.currentCall.id});
			}

			this.currentCall.destroy();
			this.currentCall = null;
			this.currentCallIsNew = false;
		}

		Hardware.isMicrophoneMuted = false;
	}

	_onNetworkProblem()
	{
		this.showNetworkProblemNotification(BX.message("IM_M_CALL_TURN_UNAVAILABLE"));
	}

	_onMicrophoneLevel(e)
	{
		if (this.callView)
		{
			this.callView.setMicrophoneLevel(e.level)
		}
	}

	_onReconnecting()
	{
		if (!(this.currentCall instanceof BitrixCall || this.currentCall instanceof PlainCall))
		{
			// todo: restore after fixing balloon resurrection issue
			// related to multiple simultaneous calls to the balloon manager
			// now it's enabled for Bitrix24 calls as a temp solution
			return false;
		}

		// noinspection UnreachableCodeJS
		if (this.reconnectionBaloon)
		{
			return;
		}

		this.reconnectionBaloon = BX.UI.Notification.Center.notify({
			content: Text.encode(BX.message('IM_CALL_RECONNECTING')),
			autoHide: false,
			position: "top-right",
			closeButton: false,
		})
	}

	_onReconnected()
	{
		this.setReconnectingCameraId(this.lastUsedCameraId)
		if (!(this.currentCall instanceof BitrixCall || this.currentCall instanceof PlainCall))
		{
			// todo: restore after fixing balloon resurrection issue
			// related to multiple simultaneous calls to the balloon manager
			// now it's enabled for Bitrix24 calls as a temp solution
			return false;
		}

		// noinspection UnreachableCodeJS
		this._closeReconnectionBaloon();
	}

	_onCustomMessage(event)
	{
		// there will be no more template selector in this call
		if (event.message === DOC_CREATED_EVENT)
		{
			this.docCreatedForCurrentCall = true;
			this.maxEditorWidth = DOC_EDITOR_WIDTH;
		}
	}

	_onJoinRoomOffer(event)
	{
		console.log("_onJoinRoomOffer", event)
		if (!event.initiator && !this.currentCall.currentRoom())
		{
			this.currentCall.joinRoom(event.roomId);
			this.showRoomJoinedPopup(true, event.speaker == this.userId, event.users);
		}
	}

	_onJoinRoom(event)
	{
		console.log("_onJoinRoom", event)
		if (event.speaker == this.userId)
		{
			this.callView.setRoomState(View.RoomState.Speaker);
		}
		else
		{
			Hardware.isMicrophoneMuted = true;
			this.callView.muteSpeaker(true);

			this.callView.setRoomState(View.RoomState.NonSpeaker);
		}
	}

	_onLeaveRoom()
	{
		// this.callView.setRoomState(View.RoomState.None);
		this.callView.setRoomState(View.RoomState.Speaker);
		this.callView.muteSpeaker(false);
	}

	_onTransferRoomSpeaker(event)
	{
		console.log("_onTransferRoomSpeaker", event);
		if (event.speaker == this.userId)
		{
			Hardware.isMicrophoneMuted = false;
			this.callView.setRoomState(View.RoomState.Speaker);

			if (event.initiator == this.userId)
			{
				this.callView.muteSpeaker(false);
				this.showMicTakenFromPopup(event.previousSpeaker);
			}
		}
		else
		{
			Hardware.isMicrophoneMuted = true;
			this.callView.muteSpeaker(true);
			this.callView.setRoomState(View.RoomState.NonSpeaker);

			this.showMicTakenByPopup(event.speaker);
		}
	}

	_onCallJoin(e)
	{
		if (e.local)
		{
			// self answer
			if (this.currentCall && (this.currentCall instanceof VoximplantCall))
			{
				Util.reportConnectionResult(this.currentCall.id, true);
			}

			return;
		}
		// remote answer, stop ringing and hide incoming cal notification
		if (this.currentCall)
		{
			this.removeVideoStrategy();
			this.removeCallEvents();
			this.currentCall = null;
		}

		if (this.callView)
		{
			this.callView.close();
		}

		if (this.callNotification)
		{
			this.callNotification.close();
		}

		if (this.invitePopup)
		{
			this.invitePopup.close();
		}

		if (this.floatingWindow)
		{
			this.floatingWindow.close();
		}

		if (this.mutePopup)
		{
			this.mutePopup.close();
		}

		this.messengerFacade.stopRepeatSound('dialtone');
		this.messengerFacade.stopRepeatSound('ringtone');
	}

	_onCallLeave(e)
	{
		console.log("_onCallLeave", e);
		Hardware.isMicrophoneMuted = false;
		if (!e.local && this.currentCall && this.currentCall.ready)
		{
			this.log(new Error("received remote leave with active call!"));
			return;
		}

		if (this.isRecording())
		{
			BXDesktopSystem.CallRecordStop();
		}
		this.callRecordState = View.RecordState.Stopped;
		this.callRecordType = View.RecordType.None;
		this.docCreatedForCurrentCall = false;
		let callDetails;

		if (this.currentCall && this.currentCall.associatedEntity)
		{
			this.removeVideoStrategy();
			this.removeCallEvents();

			callDetails = this.#getCallDetail(this.currentCall);
			this.currentCall = null;
		}

		if (this.callView)
		{
			this.callView.close();
		}

		this.hasStreamFromCall = false;
		this._stopLocalStream();

		if (this.invitePopup)
		{
			this.invitePopup.close();
		}

		if (this.floatingWindow)
		{
			this.floatingWindow.close();
		}

		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.close();
		}

		if (this.webScreenSharePopup)
		{
			this.webScreenSharePopup.close();
		}

		if (this.callNotification)
		{
			this.callNotification.close();
		}

		if (this.mutePopup)
		{
			this.mutePopup.close();
		}
		this.allowMutePopup = true;

		if (DesktopApi.isDesktop())
		{
			DesktopApi.closeWindow(DesktopApi.findWindow('callBackground'));
		}

		this.closePromo();

		this._closeReconnectionBaloon();
		this._closeParticipantsVideoBaloon();

		this.messengerFacade.stopRepeatSound('dialtone');
		this.messengerFacade.stopRepeatSound('ringtone');

		this.emit(Events.onCallLeft, {
			callDetails: callDetails
		})
	}

	_onGetUserMediaEnded()
	{
		Hardware.getCurrentDeviceList();
	}

	_onUpdateLastUsedCameraId()
	{
		const cameraId = this.currentCall.cameraId;

		if (cameraId)
		{
			this.lastUsedCameraId = cameraId;
		}
	}

	#getCallDetail(call)
	{
		return {
			id: call.id,
			provider: call.provider,
			chatId: call.associatedEntity.id,
			userCount: call.users.length,
			browser: Util.getBrowserForStatistics(),
			isMobile: Browser.isMobile(),
			isConference: false,
			wasConnected: call.wasConnected
		};
	}

	_onInvitePopupDestroy()
	{
		this.invitePopup = null;
		this.callView.setHotKeyTemporaryBlock(false);
	}

	_onInvitePopupSelect(e: {users: UserData})
	{
		this.invitePopup.close();

		const userIds = e.users.map((user) => user.id);

		if (!this.currentCall || !userIds.length)
		{
			return;
		}

		if (Util.isCallServerAllowed() && this.currentCall instanceof PlainCall)
		{
			// trying to switch to the server version of the call
			this.removeVideoStrategy();
			this.removeCallEvents();
			if (this.currentCall.isScreenSharingStarted())
			{
				this.currentCall.stopScreenSharing();
			}
			CallEngine.createChildCall(
				this.currentCall.id,
				Util.getConferenceProvider(),
				userIds,
                { debug: this.debug },
			).then((e) =>
			{
				this.childCall = e.call;

				this.childCall.addEventListener(CallEvent.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);
				this.childCall.addEventListener(CallEvent.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);

				this.childCall.useHdVideo(Hardware.preferHdQuality);
				if (this.currentCall.microphoneId)
				{
					this.childCall.setMicrophoneId(this.currentCall.microphoneId);
				}
				if (this.currentCall.cameraId)
				{
					this.childCall.setCameraId(this.currentCall.cameraId);
				}

				this.childCall.inviteUsers({
					users: this.childCall.users
				});
			});

			this.callView.removeScreenUsers();
			const userDta = {};
			e.users.forEach((user) => {
				this.callView.addUser(user.id, UserState.Calling);
				userDta[user.id] = user;
			});
			this.callView.updateUserData(userDta);
		}
		else
		{
			const currentUsers = this.currentCall.getUsers();
			const usersToInvite = [];
			let totalUsers = Object.keys(currentUsers).length;

			userIds.forEach((userId) =>
			{
				if (totalUsers < Util.getUserLimit() - 1 || currentUsers.hasOwnProperty(userId))
				{
					totalUsers++;
					usersToInvite.push(userId);
				}
			});

			if (usersToInvite.length)
			{
				this.currentCall.inviteUsers({
					users: usersToInvite,
				});
			}
		}
	}

	_onWindowFocus()
	{
		if (!this.detached)
		{
			clearTimeout(this.showFloatingWindowTimeout);
			clearTimeout(this.showFloatingScreenShareWindowTimeout);
			if (this.floatingWindow)
			{
				this.floatingWindow.hide();
			}
			if (this.floatingScreenShareWindow)
			{
				this.floatingScreenShareWindow.hide();
			}
		}
	}

	_onWindowBlur()
	{
		clearTimeout(this.showFloatingWindowTimeout);
		clearTimeout(this.showFloatingScreenShareWindowTimeout);
		if (this.currentCall && this.floatingWindow && this.callView)
		{
			this.showFloatingWindowTimeout = setTimeout(() =>
			{
				if (this.currentCall && this.floatingWindow && this.callView)
				{
					this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);
					Util.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then((result) =>
					{
						this.floatingWindow.setAvatars(result);
						this.floatingWindow.show();
					});
				}
			}, 300);
		}

		if (this.currentCall && this.floatingScreenShareWindow && this.callView && this.currentCall.isScreenSharingStarted())
		{
			this.showFloatingScreenShareWindowTimeout = setTimeout(() =>
			{
				if (this.currentCall && this.floatingScreenShareWindow && this.callView && this.currentCall.isScreenSharingStarted())
				{
					this.floatingScreenShareWindow.show();
				}
			}, 300);
		}
	}

	_onBeforeUnload(e)
	{
		if (this.floatingWindow)
		{
			this.floatingWindow.close();
		}
		if (this.callNotification)
		{
			this.callNotification.close();
		}
		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.close();
		}
		if (this.hasActiveCall())
		{
			e.preventDefault();
			e.returnValue = '';
		}
	}

	_onImTabChange(currentTab)
	{
		if (currentTab === "notify" && this.currentCall && this.callView)
		{
			this.fold(Text.decode(this.currentCall.associatedEntity.name));
		}
	}

	_onUpdateChatCounter(counter)
	{
		if (!this.currentCall || !this.currentCall.associatedEntity || !this.currentCall.associatedEntity.id || !this.callView)
		{
			return;
		}

		this.callView.setButtonCounter("chat", counter);
	}

	_onDeviceChange(e)
	{
		if (!this.currentCall || !this.currentCall.ready)
		{
			return;
		}

		const allAddedDevice = e.data.added;
		const allRemovedDevice = e.data.removed
		const removed = Hardware.getRemovedUsedDevices(
			e.data.removed,
			{
				microphoneId: this.currentCall.microphoneId,
				cameraId: this.currentCall.cameraId,
				speakerId: this.callView.speakerId,
			}
		);

		this.log("New devices: ", allAddedDevice);
		if (allAddedDevice)
		{
			setTimeout(() => this.useDevicesInCurrentCall(allAddedDevice), 500);
		}

		this.log("Removed devices: ", allRemovedDevice);
		if (removed.length > 0)
		{
			this.log("Removed devices: ", removed);
			BX.UI.Notification.Center.notify({
				content: BX.message("IM_CALL_DEVICES_DETACHED") + "<br><ul>" + removed.map(function (deviceInfo)
				{
					return "<li>" + deviceInfo.label
				}) + "</ul>",
				position: "top-right",
				autoHideDelay: 10000,
				closeButton: true,
				//category: "call-device-change",
				actions: [{
					title: BX.message("IM_CALL_DEVICES_CLOSE"),
					events: {
						click: function (event, balloon)
						{
							balloon.close();
						}
					}
				}]
			});
		}

		if (allRemovedDevice)
		{
			setTimeout(() => this.removeDevicesFromCurrentCall(allRemovedDevice), 500);
		}
	}

	_onFloatingVideoMainAreaClick()
	{
		DesktopApi.activateWindow();
		DesktopApi.changeTab("im");

		if (!this.currentCall)
		{
			return;
		}

		if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
		{
			this.messengerFacade.openMessenger(this.currentCall.associatedEntity.id);
		}
		else if (!this.messengerFacade.isMessengerOpen())
		{
			this.messengerFacade.openMessenger();
		}

		if (this.detached)
		{
			this.container.style.removeProperty('width');
			this.callView.show();
			this.detached = false;
		}
	}

	_onFloatingVideoButtonClick(e)
	{
		switch (e.buttonName)
		{
			case "toggleMute":
				this._onCallViewToggleMuteButtonClick(e);
				break;
			case "hangup":
				this._onCallViewHangupButtonClick();
				break;
		}
	}

	_onFloatingScreenShareBackToCallClick()
	{
		DesktopApi.activateWindow();
		DesktopApi.changeTab("im");
		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.hide();
		}
	}

	_onFloatingScreenShareStopClick()
	{
		DesktopApi.activateWindow();
		DesktopApi.changeTab("im");

		this.currentCall.stopScreenSharing();

		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.close();
		}

		if (this.isRecording())
		{
			BXDesktopSystem.CallRecordStopSharing();
		}
	}

	_onFloatingScreenShareChangeScreenClick()
	{
		if (this.currentCall)
		{
			this.currentCall.startScreenSharing(true);
		}
	}

	_onResize()
	{
		if (this.sidebar && this.callView)
		{
			const result = this.findCallEditorWidth();
			const callWidth = result.callWidth;
			const editorWidth = result.editorWidth;

			this.callView.setMaxWidth(callWidth);
			this.sidebar.setWidth(editorWidth);
		}
	}

	destroy()
	{
		if (this.floatingWindow)
		{
			this.floatingWindow.destroy();
			this.floatingWindow = null;
		}
		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.destroy();
			this.floatingScreenShareWindow = null;
		}
		if (this.resizeObserver)
		{
			this.resizeObserver.disconnect();
			this.resizeObserver = null;
		}
		Hardware.unsubscribe(Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipHandler);
	}

	log()
	{
		if (this.currentCall)
		{
			let arr = [this.currentCall.id];

			CallEngine.log.apply(CallEngine, arr.concat(Array.prototype.slice.call(arguments)));
		}
		else
		{
			CallEngine.log.apply(CallEngine, arguments);
		}
	}

	test(users = [473, 464], videoOptions = {width: 320, height: 180}, audioOptions = false)
	{
		this.messengerFacade.openMessenger().then(() =>
		{
			return (videoOptions || audioOptions) ? Hardware.init() : null;
		}).then(() =>
		{
			this.createContainer();
			let hiddenButtons = ['floorRequest'];
			if (!Util.shouldShowDocumentButton())
			{
				hiddenButtons.push('document');
			}

			this.callView = new View({
				container: this.container,
				baseZIndex: this.messengerFacade.getDefaultZIndex(),
				showChatButtons: true,
				userLimit: 48,
				language: this.language,
				layout: View.Layout.Grid,
				hiddenButtons: hiddenButtons,
				blockedButtons: this.getBlockedButtons(),
			});

			this.lastUserId = 1;

			this.callView.setCallback('onButtonClick', (e) => this._onTestCallViewButtonClick(e));
			//this.callView.blockAddUser();
			this.callView.setCallback(View.Event.onUserClick, (e) =>
			{
				if (!e.stream)
				{
					this.callView.setUserState(e.userId, UserState.Connected);
					this.callView.setUserMedia(e.userId, 'video', this.stream2.getVideoTracks()[0]);
				}
			});
			this.callView.setUiState(View.UiState.Connected);
			this.callView.setCallback(View.Event.onBodyClick, this._onCallViewBodyClick.bind(this));
			this.callView.setCallback('onShow', this._onCallViewShow.bind(this));
			this.callView.setCallback('onClose', this._onCallViewClose.bind(this));
			this.callView.setCallback('onReplaceMicrophone', function (e)
			{
				console.log("onReplaceMicrophone", e);
			});
			this.callView.setCallback('onReplaceCamera', function (e)
			{
				console.log("onReplaceCamera", e);
			});
			this.callView.setCallback('onReplaceSpeaker', function (e)
			{
				console.log("onReplaceSpeaker", e);
			});
			this.callView.setCallback(View.Event.onOpenAdvancedSettings,  (e) =>
			{
				console.log("onOpenAdvancedSettings", e);
				this._onCallViewOpenAdvancedSettings()
			});
			this.callView.show();

			if (audioOptions || videoOptions)
			{
				return navigator.mediaDevices.getUserMedia({
					audio: audioOptions,
					video: videoOptions,
				})
			}
			else
			{
				return new MediaStream();
			}

		}).then((s) =>
		{
			this.stream = s;
			const streamData = {
				stream: this.stream,
			};
			this.callView.setLocalStream(streamData);
			users.forEach(userId => this.callView.addUser(userId, UserState.Connected));

			if (audioOptions !== false)
			{
				this.vad = new SimpleVAD({
					mediaStream: this.stream
				});
				setInterval(() => this.callView.setMicrophoneLevel(this.vad.currentVolume), 100)
			}

			if (videoOptions)
			{
				return navigator.mediaDevices.getUserMedia({
					audio: false,
					video: {
						width: 320,
						height: 180
					},
				})
			}
			else
			{
				return new MediaStream();
			}

		}).then((s2) =>
		{
			this.stream2 = s2;
			/*users.forEach(function(userId)
				 {
					this.callView.setUserMedia(userId, 'video', stream2.getVideoTracks()[0]);
				},this);*/

			this.callView.setUserMedia(users[0], 'video', this.stream2.getVideoTracks()[0]);

			BX.rest.callMethod('im.user.list.get', {
				'ID': users.concat(this.userId),
				'AVATAR_HR': 'Y'
			}).then((response) => this.callView.updateUserData(response.data()));

		});
	}

	_onTestCallViewButtonClick(e)
	{
		console.log(e.buttonName);
		switch (e.buttonName)
		{
			case "hangup":
			case "close":
				this.callView.close();
				break;
			case "inviteUser":
				/*this.lastUserId++;
				BX.rest.callMethod('im.user.list.get', {
					'ID': [this.lastUserId],
					'AVATAR_HR': 'Y'
				}).then((response) => this.callView.updateUserData(response.data()))

				this.callView.addUser(this.lastUserId, UserState.Connecting);*/

				this._onCallViewInviteUserButtonClick(e)
				//this.callView.setStream(lastUserId, stream2);
				break;
			case "fullscreen":
				this.toggleFullScreen();

				break;
			case "record":
				this._onCallViewRecordButtonClick(e);

				break;
			case "floorRequest":
				this._onCallViewFloorRequestButtonClick(e);
				break;

			case "showChat":
				this.fold("asd \"asd\"");

				break;

			case "toggleScreenSharing":
				this.callView.setUserMedia(464, 'screen', this.stream2.getVideoTracks()[0]);

				/*setTimeout(function()
					{
						this.callView.setUserScreenState(464, true);
					}.bind(this), 0);*/
				break;

			case "returnToCall":
				break;

			case "document":
				this._onCallViewDocumentButtonClick();
				break;
		}
	}

	testIncoming(hasCamera)
	{
		this.callNotification = new IncomingNotification({
			callerName: "this.currentCall.associatedEntity.name",
			callerAvatar: "this.currentCall.associatedEntity.avatar",
			callerType: "this.currentCall.associatedEntity.advanced.chatType",
			callerColor: "",
			video: true,
			hasCamera: !!hasCamera,
			zIndex: this.messengerFacade.getDefaultZIndex() + 200,
			onClose: this._onCallNotificationClose.bind(this),
			onDestroy: this._onCallNotificationDestroy.bind(this),
			onButtonClick: (e) =>
			{
				console.log('button pressed', e.data);
				this.callNotification.close()
			}
		});

		this.callNotification.show();
	}

	getMaxActiveMicrophonesCount()
	{
		return 4;
	}

	showMicMutedNotification()
	{
		if (this.mutePopup || !this.callView)
		{
			return;
		}

		this.mutePopup = new CallHint({
			callFolded: this.folded,
			bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
			targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
			icon: 'mic-off',
			buttons: [
				this.createUnmuteButton()
			],
			onClose: () =>
			{
				this.allowMutePopup = false;
				this.mutePopup.destroy();
				this.mutePopup = null;
			},
		});
		this.mutePopup.show();
	}

	showAutoMicMuteNotification()
	{
		if (this.mutePopup || !this.callView)
		{
			return;
		}

		this.mutePopup = new CallHint({
			callFolded: this.folded,
			bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
			targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
			title: Text.encode(BX.message("IM_CALL_MIC_AUTO_MUTED")),
			icon: 'mic-off',
			buttons: [
				this.createUnmuteButton()
			],
			onClose: () =>
			{
				this.mutePopup.destroy();
				this.mutePopup = null;
			},
		});
		this.mutePopup.show();
	}

	createUnmuteButton()
	{
		return new BX.UI.Button({
			baseClass: "ui-btn ui-btn-icon-mic",
			text: BX.message("IM_CALL_UNMUTE_MIC"),
			size: BX.UI.Button.Size.EXTRA_SMALL,
			color: BX.UI.Button.Color.LIGHT_BORDER,
			noCaps: true,
			round: true,
			events: {
				click: () =>
				{
					this._onCallViewToggleMuteButtonClick({
						muted: false
					});
					if (this.mutePopup)
					{
						this.mutePopup.destroy();
						this.mutePopup = null;
					}
				}
			}
		})
	}

	_onCallToggleSubscribe(e) {
		if (this.currentCall && this.currentCall.provider === Provider.Bitrix)
		{
			this.currentCall.toggleRemoteParticipantVideo(e.participantIds, e.showVideo, true)
		}
	}

	toggleRoomMenu(bindElement)
	{
		if (this.roomMenu)
		{
			this.roomMenu.destroy();
			return;
		}

		const roomSpeaker = this.currentCall.currentRoom().speaker;
		const speakerModel = this.callView.userRegistry.get(roomSpeaker);

		this.roomMenu = new Menu({
			targetContainer: this.container,
			bindElement: bindElement,
			items: [
				{text: BX.message("IM_CALL_SOUND_PLAYS_VIA"), disabled: true},
				{html: `<div class="bx-messenger-videocall-room-menu-avatar" style="--avatar: url('${Text.encode(speakerModel.avatar)}')"></div>${Text.encode(speakerModel.name)}`},
				{delimiter: true},
				{
					text: BX.message("IM_CALL_LEAVE_ROOM"),
					onclick: () =>
					{
						this.currentCall.leaveCurrentRoom();
						this.roomMenu.close();
					}
				},
				{delimiter: true},
				{
					text: BX.message("IM_CALL_HELP"),
					onclick: () =>
					{
						this.showRoomHelp();
						this.roomMenu.close();
					}
				},

			],
			events: {
				onDestroy: () => this.roomMenu = null
			}
		});
		this.roomMenu.show();
	}

	toggleRoomListMenu(bindElement)
	{
		if (this.roomListMenu)
		{
			this.roomListMenu.destroy();
			return;
		}

		this.currentCall.listRooms().then((roomList) =>
		{
			this.roomListMenu = new BX.PopupMenuWindow({
				targetContainer: this.container,
				bindElement: bindElement,
				items: this.prepareRoomListMenuItems(roomList),
				events: {
					onDestroy: () => this.roomListMenu = null
				}
			});
			this.roomListMenu.show();
		})
	}

	prepareRoomListMenuItems(roomList)
	{
		let menuItems = [
			{text: BX.message("IM_CALL_JOIN_ROOM"), disabled: true},
			{delimiter: true},
		];
		menuItems = menuItems.concat(...roomList.map(room =>
		{
			return {
				text: this.getRoomDescription(room),
				onclick: () =>
				{
					if (this.currentCall && this.currentCall.joinRoom)
					{
						this.currentCall.joinRoom(room.id);
					}
					this.roomListMenu.destroy();
				}
			}
		}));

		menuItems.push({delimiter: true});
		menuItems.push({
			text: BX.message("IM_CALL_HELP"),
			onclick: () =>
			{
				this.showRoomHelp();
				this.roomMenu.close();
			}
		})

		return menuItems;
	}

	showRoomHelp()
	{
		BX.loadExt('ui.dialogs.messagebox').then(() =>
		{
			BX.UI.Dialogs.MessageBox.alert(
				BX.message("IM_CALL_HELP_TEXT"),
				BX.message("IM_CALL_HELP")
			);
		})
	}

	getRoomDescription(roomFields)
	{
		const userNames = roomFields.userList.map(userId =>
		{
			const userModel = this.callView.userRegistry.get(userId);
			return userModel.name;
		})

		let result = BX.message("IM_CALL_ROOM_DESCRIPTION");
		result = result.replace("#ROOM_ID#", roomFields.id)
		result = result.replace("#PARTICIPANTS_LIST#", userNames.join(", "))
		return result;
	}

	showRoomJoinedPopup(isAuto, isSpeaker, userIdList)
	{
		if (this.roomJoinedPopup || !this.callView)
		{
			return;
		}

		let title;
		if (!isAuto)
		{
			title = BX.message("IM_CALL_ROOM_JOINED_MANUALLY") + "<p>" + BX.message("IM_CALL_ROOM_JOINED_P2") + "</p>";
		}
		else
		{
			const userNames = userIdList.filter(userId => userId != this.userId).map(userId =>
			{
				const userModel = this.callView.userRegistry.get(userId);
				return userModel.name;
			})

			const usersInRoom = userNames.join(", ");
			if (isSpeaker)
			{
				title = BX.Text.encode(BX.message("IM_CALL_ROOM_JOINED_AUTO_SPEAKER").replace("#PARTICIPANTS_LIST#", usersInRoom));
			}
			else
			{
				title = BX.Text.encode(BX.message("IM_CALL_ROOM_JOINED_AUTO").replace("#PARTICIPANTS_LIST#", usersInRoom));
				title += "<p>" + BX.Text.encode(BX.message("IM_CALL_ROOM_JOINED_P2")) + "</p>";
			}
		}

		this.roomJoinedPopup = new CallHint({
			callFolded: this.folded,
			bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
			targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
			title: title,
			buttonsLayout: "bottom",
			autoCloseDelay: 0,
			buttons: [
				new Button({
					baseClass: "ui-btn",
					text: BX.message("IM_CALL_ROOM_JOINED_UNDERSTOOD"),
					size: Button.Size.EXTRA_SMALL,
					color: Button.Color.LIGHT_BORDER,
					noCaps: true,
					round: true,
					events: {
						click: () =>
						{
							this.roomJoinedPopup.destroy();
							this.roomJoinedPopup = null;
						}
					}
				}),
				new Button({
					text: BX.message("IM_CALL_ROOM_WRONG_ROOM"),
					size: Button.Size.EXTRA_SMALL,
					color: Button.Color.LINK,
					noCaps: true,
					round: true,
					events: {
						click: () =>
						{
							this.roomJoinedPopup.destroy();
							this.roomJoinedPopup = null;
							this.currentCall.leaveCurrentRoom();
						}
					}
				}),
			],
			onClose: () =>
			{
				this.roomJoinedPopup.destroy();
				this.roomJoinedPopup = null;
			},
		});
		this.roomJoinedPopup.show();
	}

	showMicTakenFromPopup(fromUserId)
	{
		if (this.micTakenFromPopup || !this.callView)
		{
			return;
		}

		const userModel = this.callView.userRegistry.get(fromUserId);
		const title = BX.message("IM_CALL_ROOM_MIC_TAKEN_FROM").replace('#USER_NAME#', userModel.name);
		this.micTakenFromPopup = new CallHint({
			callFolded: this.folded,
			bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
			targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
			title: BX.Text.encode(title),
			buttonsLayout: "right",
			autoCloseDelay: 5000,
			buttons: [
				/*new Button({
						text: BX.message("IM_CALL_ROOM_DETAILS"),
						size: Button.Size.SMALL,
						color: Button.Color.LINK,
						noCaps: true,
						round: true,
						events: {
							click: () => {this.micTakenFromPopup.destroy(); this.micTakenFromPopup = null;}
						}
					}),*/
			],
			onClose: () =>
			{
				this.micTakenFromPopup.destroy();
				this.micTakenFromPopup = null;
			},
		});
		this.micTakenFromPopup.show();
	}

	showMicTakenByPopup(byUserId)
	{
		if (this.micTakenByPopup || !this.callView)
		{
			return;
		}

		const userModel = this.callView.userRegistry.get(byUserId);

		this.micTakenByPopup = new CallHint({
			callFolded: this.folded,
			bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
			targetContainer: this.folded ? this.messengerFacade.getContainer() : this.callView.container,
			title: BX.Text.encode(BX.message("IM_CALL_ROOM_MIC_TAKEN_BY").replace('#USER_NAME#', userModel.name)),
			buttonsLayout: "right",
			autoCloseDelay: 5000,
			buttons: [
				/*new Button({
						text: BX.message("IM_CALL_ROOM_DETAILS"),
						size: Button.Size.SMALL,
						color: Button.Color.LINK,
						noCaps: true,
						round: true,
						events: {
							click: () => {this.micTakenByPopup.destroy(); this.micTakenByPopup = null;}
						}
					}),*/
			],
			onClose: () =>
			{
				this.micTakenByPopup.destroy();
				this.micTakenByPopup = null;
			},
		});
		this.micTakenByPopup.show();
	}

	showWebScreenSharePopup()
	{
		if (this.webScreenSharePopup)
		{
			this.webScreenSharePopup.show();

			return;
		}

		this.webScreenSharePopup = new WebScreenSharePopup({
			bindElement: this.callView.buttons.screen.elements.root,
			targetContainer: this.callView.container,
			onClose: () =>
			{
				this.webScreenSharePopup.destroy();
				this.webScreenSharePopup = null;
			},
			onStopSharingClick: () =>
			{
				this._onCallViewToggleScreenSharingButtonClick();
				this.webScreenSharePopup.destroy();
				this.webScreenSharePopup = null;
			}
		});
		this.webScreenSharePopup.show();
	}

	showFeedbackPopup(callDetails)
	{
		if (!callDetails)
		{
			if (this.lastCallDetails)
			{
				callDetails = this.lastCallDetails;
			}
			else
			{
				console.error('Could not show feedback without call ')
			}
		}

		BX.loadExt('ui.feedback.form').then(() =>
		{
			BX.UI.Feedback.Form.open({
				id: 'call_feedback_' + Math.random(),
				forms: [
					{zones: ['ru'], id: 406, sec: '9lhjhn', lang: 'ru'},
				],
				presets: {
					call_id: callDetails.id || 0,
					call_amount: callDetails.userCount || 0,
				},
			});
		})
	}

	showFeedbackPopup_(callDetails)
	{
		if (this.feedbackPopup)
		{
			return;
		}
		if (!callDetails)
		{
			callDetails = this.lastCallDetails;
		}
		const darkMode = this.messengerFacade.isThemeDark();
		if (!Type.isPlainObject(callDetails))
		{
			return;
		}

		BX.loadExt('im.component.call-feedback').then(() =>
		{
			let vueInstance;
			this.feedbackPopup = new Popup({
				id: 'im-call-feedback',
				content: '',
				titleBar: BX.message('IM_CALL_QUALITY_FEEDBACK'),
				closeIcon: true,
				noAllPaddings: true,
				cacheable: false,
				background: darkMode ? '#3A414B' : null,
				darkMode: darkMode,
				closeByEsc: true,
				autoHide: true,
				cacheable: false,
				events: {
					onPopupDestroy: () =>
					{
						if (vueInstance)
						{
							vueInstance.$destroy();
						}
						this.feedbackPopup = null;
					}
				}
			});

			const template = '<bx-im-component-call-feedback ' +
				'@feedbackSent="onFeedbackSent" ' +
				':darkMode="darkMode" ' +
				':callDetails="callDetails" />';

			vueInstance = BX.Vue.createApp({
				template: template,
				data: function ()
				{
					return {
						darkMode: darkMode,
						callDetails: callDetails
					}
				},
				methods: {
					onFeedbackSent: () =>
					{
						setTimeout(
							() =>
							{
								if (this.feedbackPopup)
								{
									this.feedbackPopup.close()
								}
							},
							1500
						)
					}
				}
			});
			vueInstance.mount('#' + this.feedbackPopup.getContentContainer().id);

			this.feedbackPopup.show();

		})
	}

	static FeatureState = FeatureState;
	static Events = Events;
	static ViewState = ViewState;
	static DocumentType = DocumentType;
}

