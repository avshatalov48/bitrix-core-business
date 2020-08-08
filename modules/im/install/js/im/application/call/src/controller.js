/**
 * Bitrix Im mobile
 * Dialog application
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2020 Bitrix
 */

// vue
import {VueVendorV2} from "ui.vue";

// im
import { Utils } from "im.lib.utils";
import { Cookie } from "im.lib.cookie";
import { LocalStorage } from "im.lib.localstorage";
import 'im_call';
import 'im.debug';
import {Clipboard} from 'im.lib.clipboard';
import 'ui.notification';
import 'ui.buttons';
import {ImCallPullHandler} from "im.provider.pull";
import {CallApplicationErrorCode, CallErrorCode} from "im.const";

// core
import {Loc} from "main.core";
import "promise";

// pull and rest
import { PullClient } from "pull.client";
import { CallRestClient } from "./utils/restclient"

// component
import "./view";
import { CallApplicationModel } from "im.model";
import { VuexBuilder } from "ui.vue.vuex";
import { Controller } from 'im.controller';
import 'im.application.launch';
import {
	CallLimit,
	FileStatus,
	RestMethod as ImRestMethod,
	RestMethodHandler as ImRestMethodHandler
} from "im.const";

export class CallApplication
{
	/* region 01. Initialize */
	constructor(params = {})
	{
		this.inited = false;
		this.dialogInited = false;
		this.initPromise = new BX.Promise;

		this.params = params;
		console.trace(params);
		this.params.userId = this.params.userId? parseInt(this.params.userId): 0;
		this.params.siteId = this.params.siteId || '';
		this.params.chatId = this.params.chatId? parseInt(this.params.chatId): 0;
		this.params.dialogId = this.params.chatId? 'chat'+this.params.chatId.toString(): '0';

		this.messagesQueue = [];

		this.template = null;
		this.rootNode = this.params.node || document.createElement('div');

		this.event = new VueVendorV2;

		this.callContainer = null;
		this.callView = null;
		this.preCall = null;
		this.currentCall = null;

		this.useVideo = true;
		this.localVideoStream = null;
		this.selectedCameraId = "";
		this.selectedMicrophoneId = "";

		this.localVideoTimeout = null;

		this.conferencePageTagInterval = null;

		this.onCallUserInvitedHandler = this.onCallUserInvited.bind(this);
		this.onCallUserStateChangedHandler = this.onCallUserStateChanged.bind(this);
		this.onCallUserMicrophoneStateHandler = this.onCallUserMicrophoneState.bind(this);
		this.onCallLocalMediaReceivedHandler = this.onCallLocalMediaReceived.bind(this);
		this.onCallUserStreamReceivedHandler = this.onCallUserStreamReceived.bind(this);
		this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
		this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
		this.onCallLeaveHandler = this.onCallLeave.bind(this);
		this.onCallDestroyHandler = this.onCallDestroy.bind(this);

		this.onPreCallDestroyHandler = this.onPreCallDestroy.bind(this);
		this.onPreCallUserStateChangedHandler = this.onPreCallUserStateChanged.bind(this);

		this.initRestClient()
			.then(() => this.subscribePreCallChanges())
			.then(() => this.initPullClient())
			.then(() => this.initCore())
			.then(() => this.initComponent())
			.then(() => this.initUser())
			.then(() => this.startPageTagInterval())
			.then(() => this.tryJoinExistingCall())
			.then(() => this.initCall())
			.then(() => this.initPullHandlers())
			.then(() => this.subscribeToStoreChanges())
			.then(() => this.initComplete())
		;
	}

	initRestClient()
	{
		console.log('1. initRestClient');
		this.restClient = new CallRestClient({endpoint: this.getHost()+'/rest'});

		return new Promise((resolve, reject) => resolve());
	}

	initPullClient()
	{
		console.log('2. initPullClient');
		if (!this.params.isIntranetOrExtranet)
		{
			this.pullClient = new PullClient({
				serverEnabled: true,
				userId: this.params.userId,
				siteId: this.params.siteId,
				restClient: this.restClient,
				skipStorageInit: true,
				configTimestamp: 0,
				skipCheckRevision: true,
				getPublicListMethod: 'im.call.channel.public.list'
			});

			return new Promise((resolve, reject) => resolve());
		}
		else
		{
			this.pullClient = BX.PULL;

			return this.pullClient.start().then(() => {
				return new Promise((resolve, reject) => resolve());
			});
		}
	}

	initPullHandlers()
	{
		this.pullClient.subscribe(
			new ImCallPullHandler({
				store: this.controller.getStore(),
				application: this,
				controller: this.controller,
			})
		);

		return new Promise((resolve, reject) => resolve());
	}

	initCore()
	{
		console.log('3. initCore');

		this.controller = new Controller({
			host: this.getHost(),
			siteId: this.params.siteId,
			userId: this.params.userId,
			languageId: this.params.language,
			pull: {client: this.pullClient},
			rest: {client: this.restClient},
			//localize: this.localize,
			vuexBuilder: {
				database: !Utils.browser.isIe(),
				databaseName: 'imol/call',
				databaseType: VuexBuilder.DatabaseType.localStorage,
				models: [
					CallApplicationModel.create()
				],
			}
		});

		return new Promise((resolve, reject) => {
			this.controller.ready().then(() => resolve());
		});
	}

	initComponent()
	{
		console.log('4. initComponent');

		this.controller.getStore().commit('application/set', {
			dialog: {
				chatId: this.getChatId(),
				dialogId: this.getDialogId()
			},
		});

		return this.controller.createVue(this, {
			el: this.rootNode,
			data: () =>
			{
				return {
					chatId: this.getChatId(),
					dialogId: this.getDialogId(),
					startupErrorCode: this.getStartupErrorCode()
				};
			},
			template: `<bx-im-application-call :chatId="chatId" :dialogId="dialogId" :startupErrorCode="startupErrorCode"/>`,
		})
		.then(vue => {
			this.template = vue;
			return new Promise((resolve, reject) => resolve());
		});
	}

	initUser()
	{
		const userWasKicked = LocalStorage.get(this.controller.getSiteId(), 0, `conf${this.params.alias}`);

		if (userWasKicked)
		{
			this.params.startupErrorCode = CallApplicationErrorCode.kickedFromCall;
		}

		return new Promise((resolve, reject) => {
			if (this.getStartupErrorCode())
			{
				return resolve();
			}

			console.log('5. initUser');
			if (this.params.userId > 0)
			{
				this.controller.setUserId(this.params.userId);

				if (this.params.isIntranetOrExtranet)
				{
					this.switchToSessAuth();

					this.controller.getStore().commit('callApplication/user', {
						id: this.params.userId
					});
				}
				else
				{
					let hashFromCookie = this.getUserHashCookie();
					if (hashFromCookie)
					{
						this.restClient.setAuthId(hashFromCookie);
						this.restClient.setChatId(this.getChatId());
						this.controller.getStore().commit('callApplication/user', {
							id: this.params.userId,
							hash: hashFromCookie
						});

						this.pullClient.start();
					}
				}

				this.controller.getStore().commit('callApplication/common', {
					inited: true
				});

				return resolve();
			}
			else
			{
				this.restClient.setAuthId('guest');
				this.restClient.setChatId(this.getChatId());

				if (typeof BX.SidePanel !== 'undefined')
				{
					BX.SidePanel.Instance.disableAnchorBinding();
				}

				return this.restClient.callMethod('im.call.user.register', {
					alias: this.params.alias,
					user_hash: this.getUserHashCookie() || '',
				}).then(result =>
				{
					this.controller.getStore().commit('callApplication/user', {
						id: result.data().id,
						hash: result.data().hash
					});

					this.controller.setUserId(result.data().id);

					if (result.data().created)
					{
						this.params.userCount++;
					}

					this.controller.getStore().commit('callApplication/common', {
						inited: true
					});

					this.restClient.setAuthId(result.data().hash);
					this.pullClient.start();

					return resolve();
				});
			}
		});
	}

	startPageTagInterval()
	{
		return new Promise((resolve) => {
			clearInterval(this.conferencePageTagInterval);
			this.conferencePageTagInterval = setInterval(() => {
				LocalStorage.set(this.params.siteId, this.params.userId, BX.CallEngine.getConferencePageTag(this.params.dialogId), "Y", 2);
			}, 1000);
			resolve();
		})
	}

	tryJoinExistingCall()
	{
		this.restClient.callMethod("im.call.tryJoinCall", {
			entityType: 'chat',
			entityId: this.params.dialogId,
			provider: BX.Call.Provider.Voximplant,
			type: BX.Call.Type.Permanent
		});
	}

	subscribePreCallChanges()
	{
		BX.addCustomEvent(window, 'CallEvents::callCreated', this.onCallCreated.bind(this));
	}

	onCallCreated(e)
	{
		if(this.preCall || this.currentCall)
		{
			return;
		}
		let call = e.call;
		if (call.associatedEntity.type === 'chat' && call.associatedEntity.id === this.params.dialogId)
		{
			this.preCall = e.call;
			this.updatePreCallCounter();
			this.preCall.addEventListener(BX.Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
			this.preCall.addEventListener(BX.Call.Event.onDestroy, this.onPreCallDestroyHandler);
		}
	}

	releasePreCall()
	{
		if(this.preCall)
		{
			this.preCall.removeEventListener(BX.Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
			this.preCall.removeEventListener(BX.Call.Event.onDestroy, this.onPreCallDestroyHandler);
			this.preCall = null;
		}
	}

	onPreCallDestroy(e)
	{
		this.releasePreCall();
	}

	onPreCallUserStateChanged(e)
	{
		this.updatePreCallCounter();
	}

	updatePreCallCounter()
	{
		if(this.preCall)
		{
			this.controller.getStore().commit('callApplication/common', {
				userInCallCount: this.preCall.getParticipatingUsers().length
			});
		}
		else
		{
			this.controller.getStore().commit('callApplication/common', {
				userInCallCount: 0
			});
		}
	}

	initCall()
	{
		BX.CallEngine.setRestClient(this.restClient);
		BX.CallEngine.setPullClient(this.pullClient);
		BX.CallEngine.setCurrentUserId(this.controller.getUserId());

		this.callContainer = document.getElementById('bx-im-component-call-container');

		return new Promise((resolve, reject) =>
		{
			BX.Call.Hardware.init().then(() =>
			{
				if (Object.values(BX.Call.Hardware.microphoneList).length === 0)
				{
					this.setComponentError(CallApplicationErrorCode.missingMicrophone);
				}

				this.callView = new BX.Call.View({
					container: this.callContainer,
					showChatButtons: true,
					showShareButton: true,
					userLimit: BX.Call.Util.getUserLimit(),
					language: this.params.language,
					//layout: BX.Call.View.Layout.Grid,
					uiState: BX.Call.View.UiState.Preparing,
				});
				this.callView.setCallback('onButtonClick', this.onCallButtonClick.bind(this));
				this.callView.disableAddUser();
				this.callView.disableHistoryButton();
				this.callView.show();

				return this.getLocalVideo();
			})
			.catch(error =>
			{
				if (error === 'NO_WEBRTC' && this.isHttps())
				{
					this.setComponentError(CallApplicationErrorCode.unsupportedBrowser);
				}
				else if (error === 'NO_WEBRTC' && !this.isHttps())
				{
					this.setComponentError(CallApplicationErrorCode.unsafeConnection);
				}
			})
			.then(stream =>
			{
				if (stream)
				{
					this.callView.setLocalStream(stream, true);
				}
				else
				{
					//todo: show text "you don't have connected camera, nobody gonna see you"
				}

				resolve();
			})
		})
	}

	subscribeToStoreChanges()
	{
		this.controller.getStore().subscribe((mutation, state) => {
			const { payload, type } = mutation;
			if (type === 'users/update' && payload.fields.name)
			{
				if(this.callView)
				{
					this.callView.updateUserData(
						{[payload.id]: {name: payload.fields.name}}
					);
				}
			}
			else if (type === 'dialogues/update' && typeof payload.fields.counter === 'number')
			{
				if(this.callView)
				{
					this.callView.setButtonCounter('chat', payload.fields.counter);
				}
			}
			else if (type === 'dialogues/update' && payload.fields.name)
			{
				document.title = payload.fields.name;
			}
		});
	}

	initComplete()
	{
		this.controller.getStore().commit('callApplication/common', {
			userCount: this.params.userCount
		});

		this.inited = true;
		this.initPromise.resolve(this);
	}

	ready()
	{
		if (this.inited)
		{
			let promise = new BX.Promise;
			promise.resolve(this);

			return promise;
		}

		return this.initPromise;
	}

	getLocalVideo()
	{
		return new Promise((resolve, reject) => {
			if(this.localVideoStream)
			{
				return resolve(this.localVideoStream)
			}

			navigator.mediaDevices.getUserMedia({
				video: {
					width: {ideal: BX.Call.Hardware.preferHdQuality ? 1280 : 640},
					height: {ideal: BX.Call.Hardware.preferHdQuality ? 720 : 360}
				}
			}).then(stream => {
				this.localVideoStream = stream;
				clearTimeout(this.localVideoTimeout);
				this.controller.getStore().commit('callApplication/common', {
					callError: ""
				});
				if(BX.Call.Util.hasHdVideo(this.localVideoStream))
				{
					// restore possibly cleared in localVideoTimeout flag
					BX.Call.Hardware.preferHdQuality = true;
				}

				resolve(stream)
			}).catch((error) => {
				clearTimeout(this.localVideoTimeout);
				if(error.name === "OverconstrainedError")
				{
					BX.Call.Hardware.preferHdQuality = false;
				}

				console.error(typeof(error) === "string" ? error : error.name);
				this.controller.getStore().commit('callApplication/common', {
					callError: typeof(error) === "string" ? error : error.name
				});
				reject(error);
			});

			this.localVideoTimeout = setTimeout(() => {
				BX.Call.Hardware.preferHdQuality = false;

				this.controller.getStore().commit('callApplication/common', {
					callError: CallErrorCode.noSignalFromCamera
				});
			}, 5000)
		})
	}

	stopLocalVideo()
	{
		if(!this.localVideoStream)
		{
			return;
		}
		this.localVideoStream.getTracks().forEach(tr => tr.stop());
		this.localVideoStream = null;
	}

	restart()
	{
		if(this.currentCall)
		{
			this.removeCallEvents();
			this.currentCall = null;
		}

		if(this.callView)
		{
			this.callView.releaseLocalMedia();
			this.callView.close();
			this.callView.destroy();
			this.callView = null;
		}

		this.initCall();
		this.controller.getStore().commit('callApplication/returnToPreparation');
	}

/* endregion 01. Initialize */

/* region 02. Methods */

	/* region 01. Call methods */
	startCall()
	{
		const provider = BX.Call.Provider.Voximplant;

		BX.Call.Engine.getInstance().createCall({
			type: BX.Call.Type.Permanent,
			entityType: 'chat',
			entityId: this.getDialogId(),
			provider: provider,
			videoEnabled: true,
			enableMicAutoParameters: BX.Call.Hardware.enableMicAutoParameters,
			joinExisting: true
		}).then(e => {
			console.warn('call created', e);

			this.currentCall = e.call;
			this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);
			if(BX.Call.Hardware.defaultMicrophone)
			{
				this.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
			}
			if(BX.Call.Hardware.defaultCamera)
			{
				this.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
			}

			this.callView.setUiState(BX.Call.View.UiState.Calling);
			this.callView.setLayout(BX.Call.View.Layout.Grid);
			this.callView.appendUsers(this.currentCall.getUsers());
			BX.Call.Util.getUsers(this.currentCall.id, this.getCallUsers(true)).then(userData => {
				this.callView.updateUserData(userData)
			});
			this.bindCallEvents();
			if(e.isNew)
			{
				this.currentCall.setVideoEnabled(this.useVideo);
				this.currentCall.inviteUsers();
			}
			else
			{
				this.currentCall.answer({
					useVideo: this.useVideo
				});
			}

		}).catch(e => {
			console.warn('creating call error', e);
		});

		this.controller.getStore().commit('callApplication/startCall');
	}

	endCall()
	{
		if(this.currentCall)
		{
			this.removeCallEvents();
			this.currentCall.hangup();
		}

		this.controller.getStore().commit('callApplication/endCall');

		this.restart();

		window.close();
	}

	kickFromCall()
	{
		this.setComponentError(CallApplicationErrorCode.kickedFromCall);
		this.pullClient.disconnect();
		this.endCall();
		LocalStorage.set(this.controller.getSiteId(), 0, `conf${this.params.alias}`, true);
	}

	getCallUsers(includeSelf)
	{
		let result = Object.keys(this.currentCall.getUsers());
		if(includeSelf)
		{
			result.push(this.currentCall.userId);
		}
		return result;
	}

	onCallButtonClick(event)
	{
		const buttonName = event.buttonName;
		console.warn('Button clicked!', buttonName);

		const handlers = {
			hangup: this.onCallViewHangupButtonClick.bind(this),
			close: this.onCallViewCloseButtonClick.bind(this),
			//inviteUser: this.onCallViewInviteUserButtonClick.bind(this),
			toggleMute: this.onCallViewToggleMuteButtonClick.bind(this),
			toggleScreenSharing: this.onCallViewToggleScreenSharingButtonClick.bind(this),
			toggleVideo: this.onCallViewToggleVideoButtonClick.bind(this),
			showChat: this.onCallViewShowChatButtonClick.bind(this),
			share: this.onCallViewShareButtonClick.bind(this),
			fullscreen: this.onCallViewFullScreenButtonClick.bind(this),
		};

		if(handlers[buttonName])
		{
			handlers[buttonName](event);
		}
		else
		{
			console.error('Button handler not found!', buttonName);
		}
	}

	onCallViewHangupButtonClick(e)
	{
		this.endCall();
	}

	onCallViewCloseButtonClick(e)
	{
		this.endCall();
	}

	onCallViewToggleMuteButtonClick(e)
	{
		if (this.currentCall)
		{
			this.currentCall.setMuted(e.muted);
		}

		this.callView.setMuted(e.muted);
	}

	onCallViewToggleScreenSharingButtonClick()
	{
		if(this.currentCall.isScreenSharingStarted())
		{
			this.currentCall.stopScreenSharing();
		}
		else
		{
			this.callView.releaseLocalMedia();
			this.currentCall.startScreenSharing();
		}
	}

	onCallViewToggleVideoButtonClick(e)
	{
		this.useVideo = e.video;
		if (!this.useVideo)
		{
			this.callView.releaseLocalMedia();
			this.stopLocalVideo();
		}

		if (this.currentCall)
		{
			this.currentCall.setVideoEnabled(e.video);
		}
		else
		{
			if(this.useVideo)
			{
				this.getLocalVideo().then(stream => this.callView.setLocalStream(stream, true));
			}
			else
			{
				this.callView.setLocalStream(new MediaStream());
			}
		}
	}

	onCallViewShareButtonClick()
	{
		BX.UI.Notification.Center.notify({
			content: Loc.getMessage('BX_IM_VIDEOCONF_LINK_COPY_DONE'),
			autoHideDelay: 4000
		});

		Clipboard.copy(this.getDialogData().public.link);
	}

	onCallViewFullScreenButtonClick()
	{
		this.callView.toggleFullScreen();
	}

	onCallViewShowChatButtonClick()
	{
		this.toggleChat();
	}

	bindCallEvents()
	{
		this.currentCall.addEventListener(BX.Call.Event.onUserInvited, this.onCallUserInvitedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onDestroy, this.onCallDestroyHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
		this.currentCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onStreamReceived, this.onCallUserStreamReceivedHandler);
		//this.currentCall.addEventListener(BX.Call.Event.onStreamRemoved, this.onCallUserStreamRemoved.bind(this));
		this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		//this.currentCall.addEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
		//this.currentCall.addEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
		//this.currentCall.addEventListener(BX.Call.Event.onJoin, this._onCallJoinHandler);
		this.currentCall.addEventListener(BX.Call.Event.onLeave, this.onCallLeaveHandler);
	}

	removeCallEvents()
	{
		this.currentCall.removeEventListener(BX.Call.Event.onUserInvited, this.onCallUserInvitedHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onDestroy, this.onCallDestroyHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onStreamReceived, this.onCallUserStreamReceivedHandler);
		//this.currentCall.removeEventListener(BX.Call.Event.onStreamRemoved, this.onCallUserStreamRemoved.bind(this));
		this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		//this.currentCall.removeEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
		//this.currentCall.removeEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onLeave, this.onCallLeaveHandler);
	}

	onCallUserInvited(e)
	{
		this.callView.addUser(e.userId);

		BX.Call.Util.getUsers(this.currentCall.id, [e.userId]).then(userData => {
			this.callView.updateUserData(userData)
		});
	}

	onCallUserStateChanged(e)
	{
		this.callView.setUserState(e.userId, e.state);
	}

	onCallUserMicrophoneState(e)
	{
		this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
	}

	onCallLocalMediaReceived(e)
	{
		this.callView.setLocalStream(e.stream, e.tag == "main");
		this.callView.setButtonActive("screen", e.tag == "screen");
		if(e.tag == "screen")
		{
			this.callView.disableSwitchCamera();
			this.callView.updateButtons();
		}
		else
		{
			if(!this.currentCall.callFromMobile)
			{
				this.callView.enableSwitchCamera();
				this.callView.updateButtons();
			}
		}
	}

	onCallUserStreamReceived(e)
	{
		this.callView.setStream(e.userId, e.stream);
	}

	onCallUserVoiceStarted(e)
	{
		this.callView.setUserTalking(e.userId, true);
	}

	onCallUserVoiceStopped(e)
	{
		this.callView.setUserTalking(e.userId, false);
	}

	onCallLeave(e)
	{
		this.restart();
	}

	onCallDestroy(e)
	{
		this.currentCall = null;
		this.restart();
	}

	onCheckDevicesSave(changedValues)
	{
		if (changedValues['camera'])
		{
			BX.Call.Hardware.defaultCamera = changedValues['camera'];
		}

		if (changedValues['microphone'])
		{
			BX.Call.Hardware.defaultMicrophone = changedValues['microphone'];
		}

		if (changedValues['audioOutput'])
		{
			BX.Call.Hardware.defaultSpeaker = changedValues['audioOutput'];
		}

		if (changedValues['preferHDQuality'])
		{
			BX.Call.Hardware.preferHdQuality = changedValues['preferHDQuality'];
		}

		if (changedValues['enableMicAutoParameters'])
		{
			BX.Call.Hardware.enableMicAutoParameters = changedValues['enableMicAutoParameters'];
		}
	}

	/* endregion 01. Call methods */

	/* region 02. Component methods */
	setCallError(errorCode)
	{
		this.controller.getStore().commit('callApplication/setCallError', {errorCode});
	}

	setComponentError(errorCode)
	{
		this.controller.getStore().commit('callApplication/setComponentError', {errorCode});
	}

	isChatShow()
	{
		return this.controller.getStore().state.callApplication.common.showChat;
	}

	toggleChat()
	{
		let newState = !this.isChatShow();

		this.controller.getStore().state.callApplication.common.showChat = newState;
		this.callView.setButtonActive('chat', newState);
	}

	setUserName(name)
	{
		this.restClient.callMethod('im.call.user.update', {
			name: name,
			chat_id: this.getChatId()
		}).then(() => {
			this.template.isSettingNewName = false;
		});
	}

	setDialogInited()
	{
		this.dialogInited = true;
		let dialogData = this.getDialogData();
		document.title = dialogData.name;
	}

	changeVideoconfUrl(newUrl)
	{
		window.history.pushState("", "", newUrl);
	}

	sendNewMessageNotify(text)
	{
		const MAX_LENGTH = 40;
		const AUTO_HIDE_TIME = 4000;

		text = text.replace(/<br \/>/gi, ' ');

		text = text.replace(/\[USER=([0-9]+)](.*?)\[\/USER]/ig, (whole, userId, text) => text);
		text = text.replace(/\[CHAT=(imol\|)?([0-9]+)](.*?)\[\/CHAT]/ig, (whole, imol, chatId, text) => text);
		text = text.replace(/\[PCH=([0-9]+)](.*?)\[\/PCH]/ig, (whole, historyId, text) => text);
		text = text.replace(/\[SEND(?:=(.+?))?](.+?)?\[\/SEND]/ig, (whole, command, text) => text? text: command);
		text = text.replace(/\[PUT(?:=(.+?))?](.+?)?\[\/PUT]/ig, (whole, command, text) => text? text: command);
		text = text.replace(/\[CALL(?:=(.+?))?](.+?)?\[\/CALL]/ig, (whole, command, text) => text? text: command);
		text = text.replace(/\[ATTACH=([0-9]+)]/ig, (whole, historyId, text) => '');

		if (text.length > MAX_LENGTH)
		{
			text = text.substring(0, MAX_LENGTH - 1) + '...';
		}

		const notifyNode = BX.create("div", {
			props: {
				className: 'bx-im-application-call-notify-new-message'
			},
			html: text
		});

		const notify = BX.UI.Notification.Center.notify({
			content: notifyNode,
			autoHideDelay: AUTO_HIDE_TIME
		});

		notifyNode.addEventListener('click', (event) => {
			notify.close();
			this.toggleChat();
		});
	}

	addMessage(text = '', file = null)
	{
		if (!text && !file)
		{
			return false;
		}

		if (!this.controller.application.isUnreadMessagesLoaded())
		{
			this.sendMessage({ id: 0, text, file });
			this.processSendMessages();

			return true;
		}

		let params = {};
		if (file)
		{
			params.FILE_ID = [file.id];
		}

		this.controller.getStore().commit('application/increaseDialogExtraCount');

		this.controller.getStore().dispatch('messages/add', {
			chatId: this.getChatId(),
			authorId: this.controller.getUserId(),
			text: text,
			params,
			sending: !file,
		}).then(messageId => {
			this.messagesQueue.push({
				id: messageId,
				text,
				file,
				sending: false
			});

			this.processSendMessages();
		});

		return true;
	}

	processSendMessages()
	{
		this.messagesQueue.filter(element => !element.sending).forEach(element => {
			element.sending = true;
			if (element.file)
			{
				this.sendMessageWithFile(element);
			}
			else
			{
				this.sendMessage(element);
			}
		});

		return true;
	}

	sendMessage(message)
	{
		this.controller.application.stopWriting();

		//let quiteId = this.controller.getStore().getters['dialogues/getQuoteId'](this.getDialogId());
		//if (quiteId)
		//{
		//	let quoteMessage = this.controller.getStore().getters['messages/getMessage'](this.getChatId(), quiteId);
		//	if (quoteMessage)
		//	{
		//		let user = this.controller.getStore().getters['users/get'](quoteMessage.authorId);
		//
		//		let newMessage = [];
		//		newMessage.push("------------------------------------------------------");
		//		newMessage.push((user.name ? user.name : this.getLocalize('BX_LIVECHAT_SYSTEM_MESSAGE')));
		//		newMessage.push(quoteMessage.text);
		//		newMessage.push('------------------------------------------------------');
		//		newMessage.push(message.text);
		//		message.text = newMessage.join("\n");
		//
		//		this.quoteMessageClear();
		//	}
		//}

		message.chatId = this.getChatId();

		this.controller.restClient.callMethod(ImRestMethod.imMessageAdd, {
			'TEMPLATE_ID': message.id,
			'CHAT_ID': message.chatId,
			'MESSAGE': message.text
		}, null, null)
		.then(response => {
			this.controller.getStore().dispatch('messages/update', {
				id: message.id,
				chatId: message.chatId,
				fields: {
					id: response.data(),
					sending: false,
					error: false,
				}
			}).then(() => {
				this.controller.getStore().dispatch('messages/actionFinish', {
					id: response.data(),
					chatId: message.chatId
				});
			});
			//this.controller.executeRestAnswer(ImRestMethodHandler.imMessageAdd, response, message);
		}).catch(error => {
			//this.controller.executeRestAnswer(ImRestMethodHandler.imMessageAdd, error, message);
		});

		return true;
	}

	sendMessageWithFile(message)
	{
		this.controller.application.stopWriting();

		let fileType = this.controller.getStore().getters['files/get'](this.getChatId(), message.file.id, true).type;

		let diskFolderId = this.getDiskFolderId();

		let query = {};

		if (diskFolderId)
		{
			query[ImRestMethod.imDiskFileUpload] = [ImRestMethod.imDiskFileUpload, {
				id: diskFolderId,
				data: { NAME: message.file.source.files[0].name },
				fileContent: message.file.source,
				generateUniqueName: true
			}];
		}
		else
		{
			query[ImRestMethod.imDiskFolderGet] = [ImRestMethod.imDiskFolderGet, { chat_id: this.getChatId() }];
			query[ImRestMethod.imDiskFileUpload] = [ImRestMethod.imDiskFileUpload, {
				id: '$result[' + ImRestMethod.imDiskFolderGet + '][ID]',
				data: {
					NAME: message.file.source.files[0].name
				},
				fileContent: message.file.source,
				generateUniqueName: true
			}];
		}

		this.controller.restClient.callBatch(query, (response) => {
			if (!response)
			{
				this.requestDataSend = false;
				console.warn('EMPTY_RESPONSE', 'Server returned an empty response. [1]');
				this.fileError(this.getChatId, message.file.id, message.id);
				return false;
			}

			if (!diskFolderId)
			{
				let diskFolderGet = response[ImRestMethodHandler.imDiskFolderGet];
				if (diskFolderGet && diskFolderGet.error())
				{
					console.warn(diskFolderGet.error().ex.error, diskFolderGet.error().ex.error_description);
					this.fileError(this.getChatId(), message.file.id, message.id);
					return false;
				}
		//		this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, diskFolderGet);
				this.controller.getStore().commit('application/set', {
					dialog: {
						diskFolderId: diskFolderGet.ID,
					}
				});
			}

			let diskId = 0;
			let diskFileUpload = response[ImRestMethod.imDiskFileUpload];
			if (diskFileUpload)
			{
				let result = diskFileUpload.data();
				if (diskFileUpload.error())
				{
					console.warn(diskFileUpload.error().ex.error, diskFileUpload.error().ex.error_description);
					this.fileError(this.getChatId(), message.file.id, message.id);
					return false;
				}
				else if (!result)
				{
					console.warn('EMPTY_RESPONSE', 'Server returned an empty response. [2]');
					this.fileError(this.getChatId(), message.file.id, message.id);
					return false;
				}

				diskId = result.ID;
			}
			else
			{
				console.warn('EMPTY_RESPONSE', 'Server returned an empty response. [3]');
				this.fileError(this.getChatId(), message.file.id, message.id);
				return false;
			}

			message.chatId = this.getChatId();

			this.controller.getStore().dispatch('files/update', {
				chatId: message.chatId,
				id: message.file.id,
				fields: {
					status: FileStatus.wait,
					progress: 95
				}
			});

			this.fileCommit({
				chatId: message.chatId,
				uploadId: diskId,
				messageText: message.text,
				messageId: message.id,
				fileId: message.file.id,
				fileType
			}, message);

		}, false, (xhr) => {
			message.xhr = xhr
		});
	}

	uploadFile(fileInput)
	{
		if (!fileInput)
		{
			return false;
		}

		console.warn('addFile', fileInput.files[0].name, fileInput.files[0].size, fileInput.files[0]);

		let file = fileInput.files[0];

		let fileType = 'file';
		if (file.type.toString().startsWith('image'))
		{
			fileType = 'image';
		}

		//if (!this.controller.application.isUnreadMessagesLoaded())
		//{
		//	this.addMessage('', { id: 0, source: fileInput });
		//	return true;
		//}

		this.controller.getStore().dispatch('files/add', {
			chatId: this.getChatId(),
			authorId: this.controller.getUserId(),
			name: file.name,
			type: fileType,
			extension: file.name.split('.').splice(-1)[0],
			size: file.size,
			image: false,
			status: FileStatus.upload,
			progress: 0,
			authorName: this.controller.application.getCurrentUser().name,
			urlPreview: "",
		}).then(fileId => this.addMessage('', { id: fileId, source: fileInput }));

		return true;
	}

	fileError(chatId, fileId, messageId = 0)
	{
		this.controller.getStore().dispatch('files/update', {
			chatId: chatId,
			id: fileId,
			fields: {
				status: FileStatus.error,
				progress: 0
			}
		});
		if (messageId)
		{
			this.controller.getStore().dispatch('messages/actionError', {
				chatId: chatId,
				id: messageId,
				retry: false,
			});
		}
	}

	fileCommit(params, message)
	{
		this.controller.restClient.callMethod(ImRestMethod.imDiskFileCommit, {
			chat_id: params.chatId,
			upload_id: params.uploadId,
			message: params.messageText,
			template_id: params.messageId,
			file_template_id: params.fileId,
		}, null, null, ).then(response => {
			//this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, response, message);
			this.controller.getStore().dispatch('messages/update', {
				id: message.id,
				chatId: message.chatId,
				fields: {
					id: response['MESSAGE_ID'],
					sending: false,
					error: false,
				}
			}).then(() => {
				this.controller.getStore().dispatch('messages/actionFinish', {
					id: response['MESSAGE_ID'],
					chatId: message.chatId
				});
			});
		}).catch(error => {
			//this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, error, message);
		});

		return true;
	}
	/* endregion 02. Component methods */

/* endregion 02. Methods */

/* region 03. Utils */

	addLocalize(phrases)
	{
		return this.controller.addLocalize(phrases);
	}

	getLocalize(name)
	{
		return this.controller.getLocalize(name);
	}

	isUserRegistered()
	{
		return !!this.getUserHash();
	}

	getChatId()
	{
		return parseInt(this.params.chatId);
	}

	getDialogId()
	{
		return this.params.dialogId;
	}

	getDialogData()
	{
		if (!this.dialogInited)
		{
			return false;
		}

		return this.controller.getStore().getters['dialogues/get'](this.getDialogId());
	}

	getHost()
	{
		return location.origin || '';
	}

	getStartupErrorCode()
	{
		return this.params.startupErrorCode? this.params.startupErrorCode : '';
	}

	getDiskFolderId()
	{
		return this.controller.getStore().state.application.dialog.diskFolderId;
	}

	isHttps()
	{
		return location.protocol === 'https:';
	}

	getUserHash()
	{
		return this.controller.getStore().state.callApplication.user.hash;
	}

	getUserHashCookie()
	{
		let userHash = '';

		let cookie = Cookie.get(null, 'BITRIX_CALL_HASH');
		if (typeof cookie === 'string' && cookie.match(/^[a-f0-9]{32}$/))
		{
			userHash = cookie;
		}

		return userHash;
	}

	getAlias()
	{
		return this.params.alias ? this.params.alias : '';
	}

	switchToSessAuth()
	{
		this.restClient.restClient.queryParams = undefined;
		return true;
	}

/* endregion 03. Utils */
}