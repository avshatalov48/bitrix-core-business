/**
 * Bitrix Im
 * Conference application
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2021 Bitrix
 */

// im
import 'im.debug';
import 'im.application.launch';
import 'im.component.conference.conference-public';
import * as Call from 'im.call';
import { ConferenceModel, CallModel } from "im.model";
import { Controller } from 'im.controller';
import { Utils } from "im.lib.utils";
import { Cookie } from "im.lib.cookie";
import { LocalStorage } from "im.lib.localstorage";
import { Logger } from "im.lib.logger";
import { Clipboard } from 'im.lib.clipboard';
import { Desktop } from "im.lib.desktop";
import {
	EventType,
	ConferenceErrorCode,
	ConferenceRightPanelMode as RightPanelMode
} from "im.const";

//ui
import {Notifier, NotificationOptions} from 'ui.notification-manager';
import 'ui.notification';
import 'ui.buttons';
import 'ui.progressround';
import 'ui.viewer';
import { VueVendorV2 } from "ui.vue";
import { VuexBuilder } from "ui.vue.vuex";

// core
import { Loc, Tag, Dom, Text } from "main.core";
import "promise";
import 'main.date';
import {BaseEvent, EventEmitter} from 'main.core.events';

// pull and rest
import { PullClient } from "pull.client";
import { ImCallPullHandler } from "im.provider.pull";
import { CallRestClient } from "./utils/restclient"

class ConferenceApplication
{
	/* region 01. Initialize */
	constructor(params = {})
	{
		this.inited = false;
		this.hardwareInited = false;
		this.dialogInited = false;
		this.initPromise = new BX.Promise;

		this.params = params;
		this.params.userId = this.params.userId? parseInt(this.params.userId): 0;
		this.params.siteId = this.params.siteId || '';
		this.params.chatId = this.params.chatId? parseInt(this.params.chatId): 0;
		this.params.dialogId = this.params.chatId? 'chat'+this.params.chatId.toString(): '0';
		this.params.passwordRequired = !!this.params.passwordRequired;
		this.params.isBroadcast = !!this.params.isBroadcast;

		BX.Messenger.Lib.Logger.setConfig(params.loggerConfig);

		this.messagesQueue = [];

		this.template = null;
		this.rootNode = this.params.node || document.createElement('div');

		this.event = new VueVendorV2;

		this.callContainer = null;
		// this.callView = null;
		this.preCall = null;
		this.currentCall = null;
		this.videoStrategy = null;
		this.callDetails = {};
		this.showFeedback = true;

		this.featureConfig = {};
		(params.featureConfig || []).forEach(limit => {
			this.featureConfig[limit.id] = limit;
		});

		this.localVideoStream = null;

		this.conferencePageTagInterval = null;

		this.onCallUserInvitedHandler = this.onCallUserInvited.bind(this);
		this.onCallUserStateChangedHandler = this.onCallUserStateChanged.bind(this);
		this.onCallUserMicrophoneStateHandler = this.onCallUserMicrophoneState.bind(this);
		this.onCallUserCameraStateHandler = this.onCallUserCameraState.bind(this);
		this.onCallUserVideoPausedHandler = this.onCallUserVideoPaused.bind(this);
		this.onCallLocalMediaReceivedHandler = BX.debounce(this.onCallLocalMediaReceived.bind(this), 1000);
		this.onCallRemoteMediaReceivedHandler = this.onCallRemoteMediaReceived.bind(this);
		this.onCallRemoteMediaStoppedHandler = this.onCallRemoteMediaStopped.bind(this);
		this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
		this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
		this.onCallUserScreenStateHandler = this.onCallUserScreenState.bind(this);
		this.onCallUserRecordStateHandler = this.onCallUserRecordState.bind(this);
		this.onCallUserFloorRequestHandler = this.onCallUserFloorRequest.bind(this);
		this.onMicrophoneLevelHandler = this.onMicrophoneLevel.bind(this);
		this._onCallJoinHandler = this.onCallJoin.bind(this);
		this.onCallLeaveHandler = this.onCallLeave.bind(this);
		this.onCallDestroyHandler = this.onCallDestroy.bind(this);
		this.onInputFocusHandler = this.onInputFocus.bind(this);
		this.onInputBlurHandler = this.onInputBlur.bind(this);

		this.onPreCallDestroyHandler = this.onPreCallDestroy.bind(this);
		this.onPreCallUserStateChangedHandler = this.onPreCallUserStateChanged.bind(this);

		this.waitingForCallStatus = false;
		this.waitingForCallStatusTimeout = null;
		this.callEventReceived = false;
		this.callRecordState = Call.View.RecordState.Stopped;

		this.desktop = null;
		this.floatingScreenShareWindow = null;
		this.webScreenSharePopup = null;

		this.mutePopup = null;
		this.allowMutePopup = true;

		this.initDesktopEvents()
			.then(() => this.initRestClient())
			.then(() => this.subscribePreCallChanges())
			.then(() => this.subscribeNotifierEvents())
			.then(() => this.initPullClient())
			.then(() => this.initCore())
			.then(() => this.setModelData())
			.then(() => this.initComponent())
			.then(() => this.initCallInterface())
			.then(() => this.initHardware())
			.then(() => this.initUserComplete())
			.catch((error) => {
				console.error('Init error', error);
			})
		;
	}
		/* region 01. Initialize methods */
		initDesktopEvents()
		{
			if (!Utils.platform.isBitrixDesktop())
			{
				return new Promise((resolve, reject) => resolve());
			}

			this.desktop = new Desktop();
			this.floatingScreenShareWindow = new Call.FloatingScreenShare({
				desktop: this.desktop,
				onBackToCallClick: this.onFloatingScreenShareBackToCallClick.bind(this),
				onStopSharingClick: this.onFloatingScreenShareStopClick.bind(this),
				onChangeScreenClick: this.onFloatingScreenShareChangeScreenClick.bind(this)
			});

			if (this.floatingScreenShareWindow)
			{
				this.desktop.addCustomEvent("BXScreenMediaSharing", (id, title, x, y, width, height, app) =>
				{
					this.floatingScreenShareWindow.setSharingData({
						title: title,
						x: x,
						y: y,
						width: width,
						height: height,
						app: app
					}).then(() => {
						this.floatingScreenShareWindow.show();
					}).catch(error => {
						Logger.error('setSharingData error', error);
					});
				});

				window.addEventListener('focus', () => {
					this.onWindowFocus();
				});

				window.addEventListener('blur', () => {
					this.onWindowBlur();
				});
			}

			this.desktop.addCustomEvent('bxImUpdateCounterMessage', (counter) =>
			{
				if (!this.controller)
				{
					return false;
				}

				this.controller.getStore().commit('conference/common', {
					messageCount: counter
				});
			});

			EventEmitter.subscribe(EventType.textarea.focus, this.onInputFocusHandler);
			EventEmitter.subscribe(EventType.textarea.blur, this.onInputBlurHandler);
			EventEmitter.subscribe(EventType.conference.userRenameFocus, this.onInputFocusHandler);
			EventEmitter.subscribe(EventType.conference.userRenameBlur, this.onInputBlurHandler);

			return new Promise((resolve, reject) => resolve());
		}

		initRestClient()
		{
			this.restClient = new CallRestClient({endpoint: this.getHost()+'/rest'});
			this.restClient.setConfId(this.params.conferenceId);

			return new Promise((resolve, reject) => resolve());
		}

		subscribePreCallChanges()
		{
			BX.addCustomEvent(window, 'CallEvents::callCreated', this.onCallCreated.bind(this));
		}

		subscribeNotifierEvents()
		{
			Notifier.subscribe('click', (event: BaseEvent<NotifierClickParams>) => {
				const { id } = event.getData();
				if (id.startsWith('im-videconf'))
				{
					this.toggleChat();
				}
			});
		}

		initPullClient()
		{
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

		initCore()
		{
			this.controller = new Controller({
				host: this.getHost(),
				siteId: this.params.siteId,
				userId: this.params.userId,
				languageId: this.params.language,
				pull: {client: this.pullClient},
				rest: {client: this.restClient},
				vuexBuilder: {
					database: !Utils.browser.isIe(),
					databaseName: 'imol/call',
					databaseType: VuexBuilder.DatabaseType.localStorage,
					models: [
						ConferenceModel.create(),
						CallModel.create()
					],
				}
			});

			window.BX.Messenger.Application.Core = {
				controller: this.controller
			};

			return new Promise((resolve, reject) => {
				this.controller.ready().then(() => resolve());
			});
		}

		setModelData()
		{
			this.controller.getStore().commit('application/set', {
				dialog: {
					chatId: this.getChatId(),
					dialogId: this.getDialogId()
				},
				options: {
					darkBackground: true
				}
			});

			//set presenters ID list
			const presentersIds = this.params.presenters.map(presenter => presenter['id']);
			this.controller.getStore().dispatch('conference/setBroadcastMode', {broadcastMode: this.params.isBroadcast});
			this.controller.getStore().dispatch('conference/setPresenters', {presenters: presentersIds});

			//set presenters info in users model
			this.params.presenters.forEach(presenter => {
				this.controller.getStore().dispatch('users/set', presenter);
			});

			if (this.params.passwordRequired)
			{
				this.controller.getStore().commit('conference/common', {
					passChecked: false,
				});
			}

			if (this.params.conferenceTitle)
			{
				this.controller.getStore().dispatch('conference/setConferenceTitle', {
					conferenceTitle: this.params.conferenceTitle,
				});
			}

			if (this.params.alias)
			{
				this.controller.getStore().commit('conference/setAlias', {
					alias: this.params.alias,
				});
			}

			return new Promise((resolve, reject) => resolve());
		}

		initComponent()
		{
			if (this.getStartupErrorCode())
			{
				this.setError(this.getStartupErrorCode());
			}

			return new Promise((resolve, reject) =>
			{
				this.controller.createVue(this, {
					el: this.rootNode,
					data: () =>
					{
						return {
							dialogId: this.getDialogId()
						};
					},
					template: `<bx-im-component-conference-public :dialogId="dialogId"/>`,
				}).then(vue =>
				{
					this.template = vue;
					resolve();
				}).catch(error => reject(error));
			});
		}

		initCallInterface()
		{
			return new Promise((resolve, reject) =>
			{
				this.callContainer = document.getElementById('bx-im-component-call-container');

				let hiddenButtons = ['document'];
				if (this.isViewerMode())
				{
					hiddenButtons = ['camera', 'microphone', 'screen', 'record', 'floorRequest', 'document'];
				}
				if (!this.params.isIntranetOrExtranet)
				{
					hiddenButtons.push('record');
				}

				this.callView = new Call.View({
					container: this.callContainer,
					showChatButtons: true,
					showUsersButton: true,
					showShareButton: this.getFeatureState('screenSharing') !== ConferenceApplication.FeatureState.Disabled,
					showRecordButton: this.getFeatureState('record') !== ConferenceApplication.FeatureState.Disabled,
					userLimit: Call.Util.getUserLimit(),
					isIntranetOrExtranet: !!this.params.isIntranetOrExtranet,
					language: this.params.language,
					layout: Utils.device.isMobile() ? Call.View.Layout.Mobile : Call.View.Layout.Centered,
					uiState: Call.View.UiState.Preparing,
					blockedButtons: ['camera', 'microphone', 'floorRequest', 'screen', 'record'],
					localUserState: Call.UserState.Idle,
					hiddenTopButtons: !this.isBroadcast() || this.getBroadcastPresenters().length > 1? []: ['grid'],
					hiddenButtons: hiddenButtons,
					broadcastingMode: this.isBroadcast(),
					broadcastingPresenters: this.getBroadcastPresenters(),
				});

				this.callView.subscribe(Call.View.Event.onButtonClick, this.onCallButtonClick.bind(this));
				this.callView.subscribe(Call.View.Event.onReplaceCamera, this.onCallReplaceCamera.bind(this));
				this.callView.subscribe(Call.View.Event.onReplaceMicrophone, this.onCallReplaceMicrophone.bind(this));
				this.callView.subscribe(Call.View.Event.onReplaceSpeaker, this.onCallReplaceSpeaker.bind(this));
				this.callView.subscribe(Call.View.Event.onChangeHdVideo, this.onCallViewChangeHdVideo.bind(this));
				this.callView.subscribe(Call.View.Event.onChangeMicAutoParams, this.onCallViewChangeMicAutoParams.bind(this));
				this.callView.subscribe(Call.View.Event.onChangeFaceImprove, this.onCallViewChangeFaceImprove.bind(this));
				this.callView.subscribe(Call.View.Event.onUserRename, this.onCallViewUserRename.bind(this));
				this.callView.subscribe(Call.View.Event.onUserPinned, this.onCallViewUserPinned.bind(this));

				this.callView.blockAddUser();
				this.callView.blockHistoryButton();

				if (!Utils.device.isMobile())
				{
					this.callView.show();
				}

				resolve()
			})
		}

		initUserComplete()
		{
			return new Promise((resolve, reject) => {
				this.initUser()
					.then(() => this.startPageTagInterval())
					.then(() => this.tryJoinExistingCall())
					.then(() => this.initCall())
					.then(() => this.initPullHandlers())
					.then(() => this.subscribeToStoreChanges())
					.then(() => this.initComplete())
					.then(() => resolve)
					.catch((error) => reject(error));
			})
		}
		/* endregion 01. Initialize methods */

		/* region 02. initUserComplete methods */
		initUser()
		{
			return new Promise((resolve, reject) => {
				if (this.getStartupErrorCode() || !this.getConference().common.passChecked)
				{
					return reject();
				}

				if (this.params.userId > 0)
				{
					this.controller.setUserId(this.params.userId);

					if (this.params.isIntranetOrExtranet)
					{
						this.switchToSessAuth();

						this.controller.getStore().commit('conference/user', {
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
							this.controller.getStore().commit('conference/user', {
								id: this.params.userId,
								hash: hashFromCookie
							});

							this.pullClient.start();
						}
					}

					this.controller.getStore().commit('conference/common', {
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
						BX.message['USER_ID'] = result.data().id;
						this.controller.getStore().commit('conference/user', {
							id: result.data().id,
							hash: result.data().hash
						});

						this.controller.setUserId(result.data().id);
						this.callView.setLocalUserId(result.data().id);

						if (result.data().created)
						{
							this.params.userCount++;
						}

						this.controller.getStore().commit('conference/common', {
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
					provider: Call.Provider.Voximplant,
					type: Call.Type.Permanent
				})
				.then(result => {
					Logger.warn('tryJoinCall', result.data());
					if (result.data().success)
					{
						this.waitingForCallStatus = true;
						this.waitingForCallStatusTimeout = setTimeout(() => {
							this.waitingForCallStatus = false;
							if (!this.callEventReceived)
							{
								this.setConferenceStatus(false);
							}
							this.callEventReceived = false;
						}, 5000);
					}
					else
					{
						this.setConferenceStatus(false);
					}
				})
		}

		initCall()
		{
			Call.Engine.setRestClient(this.restClient);
			Call.Engine.setPullClient(this.pullClient);
			Call.Engine.setCurrentUserId(this.controller.getUserId());
			this.callView.unblockButtons(['chat']);
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

		subscribeToStoreChanges()
		{
			this.controller.getStore().subscribe((mutation, state) => {
				const { payload, type } = mutation;
				if (type === 'users/update' && payload.fields.name)
				{
					if (!this.callView)
					{
						return false;
					}

					this.callView.updateUserData(
						{[payload.id]: {name: payload.fields.name}}
					);
				}
				else if (type === 'dialogues/set')
				{
					if (payload[0].dialogId !== this.getDialogId())
					{
						return false;
					}

					if (!Utils.platform.isBitrixDesktop())
					{
						this.callView.setButtonCounter('chat', payload[0].counter);
					}
				}
				else if (type === 'dialogues/update')
				{
					if (payload.dialogId !== this.getDialogId())
					{
						return false;
					}

					if (typeof payload.fields.counter === 'number' && this.callView)
					{
						if (Utils.platform.isBitrixDesktop())
						{
							if (
								payload.actionName === "decreaseCounter"
								&& !payload.dialogMuted
								&& typeof payload.fields.previousCounter === 'number'
							)
							{
								let counter = payload.fields.counter;
								if (this.getConference().common.messageCount)
								{
									counter = this.getConference().common.messageCount - (payload.fields.previousCounter - counter);
									if (counter < 0)
									{
										counter = 0;
									}
								}
								this.callView.setButtonCounter('chat', counter);
							}
						}
						else
						{
							this.callView.setButtonCounter('chat', payload.fields.counter);
						}
					}

					if (typeof payload.fields.name !== 'undefined')
					{
						document.title = payload.fields.name.toString();
					}
				}
				else if (type === 'conference/common' && typeof payload.messageCount === 'number')
				{
					if (this.callView)
					{
						this.callView.setButtonCounter('chat', payload.messageCount);
					}
				}
				else if (type === 'conference/common' && typeof payload.userCount === 'number')
				{
					if (this.callView)
					{
						this.callView.setButtonCounter('users', payload.userCount);
					}
				}
			});
		}

		initComplete()
		{
			this.controller.getStore().commit('conference/common', {
				userCount: this.params.userCount
			});
			this.callView.setButtonCounter('users', this.params.userCount);

			if (this.isExternalUser())
			{
				this.callView.localUser.userModel.allowRename = true;
			}

			if (this.getConference().common.inited)
			{
				this.inited = true;
				this.initPromise.resolve(this);
			}

			if (Utils.platform.isBitrixDesktop())
			{
				this.desktop.onCustomEvent('bxConferenceLoadComplete', []);
			}

			return new Promise((resolve, reject) => resolve());
		}
		/* endregion 02. initUserComplete methods */
/* endregion 01. Initialize */

/* region 02. Methods */

	/* region 01. Call methods */
	initHardware()
	{
		return new Promise((resolve, reject) =>
		{
			Call.Hardware.init().then(() => {
				if (this.hardwareInited)
				{
					resolve();
					return true;
				}

				if (Object.values(Call.Hardware.microphoneList).length === 0)
				{
					this.setError(ConferenceErrorCode.missingMicrophone);
				}

				if (!this.isViewerMode())
				{
					this.callView.unblockButtons(["camera", "microphone"]);
					this.callView.enableMediaSelection();
				}

				this.hardwareInited = true;
				resolve();
			}).catch(error => {
				if (error === 'NO_WEBRTC' && this.isHttps())
				{
					this.setError(ConferenceErrorCode.unsupportedBrowser);
				}
				else if (error === 'NO_WEBRTC' && !this.isHttps())
				{
					this.setError(ConferenceErrorCode.unsafeConnection);
				}
				Logger.error('Init hardware error', error);
				reject(error)
			})
		});
	}

	startCall(videoEnabled, viewerMode = false)
	{
		const provider = Call.Provider.Voximplant;

		if (Utils.device.isMobile())
		{
			this.callView.show();
			this.callView.setButtonCounter('chat', this.getDialogData().counter);
			this.callView.setButtonCounter('users', this.getConference().common.userCount);
		}
		else
		{
			this.callView.setLayout(Call.View.Layout.Grid);
		}

		this.callView.setUiState(Call.View.UiState.Calling);

		if (this.localVideoStream)
		{
			if (videoEnabled)
			{
				this.callView.setLocalStream(this.localVideoStream, Call.Hardware.enableMirroring);
			}
			else
			{
				this.stopLocalVideoStream();
			}
		}
		if (!videoEnabled)
		{
			this.callView.setCameraState(false);
		}
		this.controller.getStore().commit('conference/startCall');

		Call.Engine.createCall({
			type: Call.Type.Permanent,
			entityType: 'chat',
			entityId: this.getDialogId(),
			provider: provider,
			videoEnabled: videoEnabled,
			enableMicAutoParameters: Call.Hardware.enableMicAutoParameters,
			joinExisting: true
		}).then(e => {
			Logger.warn('call created', e);

			this.currentCall = e.call;
			//this.currentCall.useHdVideo(Call.Hardware.preferHdQuality);
			this.currentCall.useHdVideo(true);
			if(Call.Hardware.defaultMicrophone)
			{
				this.currentCall.setMicrophoneId(Call.Hardware.defaultMicrophone);
			}
			if(Call.Hardware.defaultCamera)
			{
				this.currentCall.setCameraId(Call.Hardware.defaultCamera);
			}

			if(!Utils.device.isMobile())
			{
				this.callView.setLayout(Call.View.Layout.Grid);
			}
			this.callView.appendUsers(this.currentCall.getUsers());
			Call.Util.getUsers(this.currentCall.id, this.getCallUsers(true)).then(userData => {
				this.controller.getStore().dispatch('users/set', Object.values(userData));
				this.controller.getStore().dispatch('conference/setUsers', {users: Object.keys(userData)});
				this.callView.updateUserData(userData)
			});
			this.releasePreCall();
			this.bindCallEvents();

			if(this.callView.isMuted)
			{
				this.currentCall.setMuted(true);
			}
			if(e.isNew)
			{
				this.currentCall.setVideoEnabled(videoEnabled);
				this.currentCall.inviteUsers();
			}
			else
			{
				this.currentCall.answer({
					useVideo: videoEnabled,
					joinAsViewer: viewerMode
				});
			}

		}).catch(e => {
			Logger.error('creating call error', e);
		});
	}

	/**
	 * @param {int} callId
	 * @param {object} options
	 */
	joinCall(callId, options)
	{
		let video = BX.prop.getBoolean(options, "video", false);
		let joinAsViewer = BX.prop.getBoolean(options, "joinAsViewer", false);

		if (Utils.device.isMobile())
		{
			this.callView.show();
		}
		else
		{
			this.callView.setLayout(Call.View.Layout.Grid);
		}

		if (joinAsViewer)
		{
			this.callView.setLocalUserDirection(Call.EndpointDirection.RecvOnly);
		}
		else
		{
			this.callView.setLocalUserDirection(Call.EndpointDirection.SendRecv);
		}

		this.callView.setUiState(Call.View.UiState.Calling);
		Call.Engine.getCallWithId(callId).then((result) =>
		{
			this.currentCall = result.call;
			this.releasePreCall();
			this.bindCallEvents();

			this.controller.getStore().commit('conference/startCall');

			this.callView.appendUsers(this.currentCall.getUsers());
			Call.Util.getUsers(this.currentCall.id, this.getCallUsers(true)).then(userData => {
				this.controller.getStore().dispatch('users/set', Object.values(userData));
				this.controller.getStore().dispatch('conference/setUsers', {users: Object.keys(userData)});
				this.callView.updateUserData(userData)
			});

			if (!joinAsViewer)
			{
				//this.currentCall.useHdVideo(Call.Hardware.preferHdQuality);
				this.currentCall.useHdVideo(true);
				if (Call.Hardware.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(Call.Hardware.defaultMicrophone);
				}
				if (Call.Hardware.defaultCamera)
				{
					this.currentCall.setCameraId(Call.Hardware.defaultCamera);
				}
				if(this.callView.isMuted)
				{
					this.currentCall.setMuted(true);
				}
			}

			this.currentCall.answer({
				useVideo: !!video,
				joinAsViewer: joinAsViewer
			});
		}).catch((error) => console.error(error));
	}

	endCall()
	{
		if (this.currentCall)
		{
			this.showFeedback = this.currentCall.wasConnected;
			this.callDetails = {
				id: this.currentCall.id,
				provider: this.currentCall.provider,
				userCount: this.currentCall.users.length,
				browser: Call.Util.getBrowserForStatistics(),
				isMobile: BX.browser.IsMobile(),
				isConference: true
			}

			this.removeCallEvents();
			this.currentCall.hangup();
		}

		if (this.isRecording())
		{
			BXDesktopSystem.CallRecordStop();
		}
		this.callRecordState = Call.View.RecordState.Stopped;

		if (Utils.platform.isBitrixDesktop())
		{
			if (this.floatingScreenShareWindow)
			{
				this.floatingScreenShareWindow.destroy();
				this.floatingScreenShareWindow = null;
			}
			window.close();
		}
		else
		{
			this.callView.releaseLocalMedia();
			this.callView.close();
			this.setError(ConferenceErrorCode.userLeftCall);
			this.controller.getStore().commit('conference/endCall');
		}

		EventEmitter.unsubscribe(EventType.textarea.focus, this.onInputFocusHandler);
		EventEmitter.unsubscribe(EventType.textarea.blur, this.onInputBlurHandler);
		EventEmitter.unsubscribe(EventType.conference.userRenameFocus, this.onInputFocusHandler);
		EventEmitter.unsubscribe(EventType.conference.userRenameBlur, this.onInputBlurHandler);
	}

	restart()
	{
		console.trace("restart");
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
		this.initCallInterface();
		this.initCall();
		this.controller.getStore().commit('conference/endCall');
	}

	kickFromCall()
	{
		this.setError(ConferenceErrorCode.kickedFromCall);
		this.pullClient.disconnect();
		this.endCall();
	}

	getCallUsers(includeSelf)
	{
		let result = Object.keys(this.currentCall.getUsers());
		if (includeSelf)
		{
			result.push(this.currentCall.userId);
		}
		return result;
	}

	setLocalVideoStream(stream)
	{
		this.localVideoStream = stream;
	}

	stopLocalVideoStream()
	{
		if (this.localVideoStream)
		{
			this.localVideoStream.getTracks().forEach(tr => tr.stop());
		}
		this.localVideoStream = null;
	}

	setSelectedCamera(cameraId)
	{
		if (this.callView)
		{
			this.callView.setCameraId(cameraId)
		}
	}

	setSelectedMic(micId)
	{
		if (this.callView)
		{
			this.callView.setMicrophoneId(micId);
		}
	}

	getFeature(id)
	{
		if (typeof this.featureConfig[id] === 'undefined')
		{
			return {
				id,
				state: ConferenceApplication.FeatureState.Enabled,
				articleCode: ''
			}
		}

		return this.featureConfig[id];
	}

	getFeatureState(id)
	{
		return this.getFeature(id).state;
	}

	canRecord()
	{
		return Utils.platform.isBitrixDesktop() && Utils.platform.getDesktopVersion() >= 54;
	}

	isRecording()
	{
		return this.canRecord() && this.callRecordState != Call.View.RecordState.Stopped;
	}

	showFeatureLimitSlider(id)
	{
		const articleCode = this.getFeature(id).articleCode;
		if (!articleCode || !window.BX.UI.InfoHelper)
		{
			console.warn('Limit article not found', id);
			return false;
		}

		window.BX.UI.InfoHelper.show(articleCode);

		return true;
	}

	showMicMutedNotification()
	{
		if (this.mutePopup || !this.callView)
		{
			return;
		}

		this.mutePopup = new Call.Hint({
			bindElement: this.callView.buttons.microphone.elements.icon,
			targetContainer: this.callView.container,
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
					this.onCallViewToggleMuteButtonClick({
						muted: false
					});
					this.mutePopup.destroy();
					this.mutePopup = null;
				}
			}
		})
	}

	showWebScreenSharePopup()
	{
		if (this.webScreenSharePopup)
		{
			this.webScreenSharePopup.show();

			return;
		}

		this.webScreenSharePopup = new Call.WebScreenSharePopup({
			bindElement: this.callView.buttons.screen.elements.root,
			targetContainer: this.callView.container,
			onClose: function ()
			{
				this.webScreenSharePopup.destroy();
				this.webScreenSharePopup = null;
			}.bind(this),
			onStopSharingClick: function ()
			{
				this.onCallViewToggleScreenSharingButtonClick();
				this.webScreenSharePopup.destroy();
				this.webScreenSharePopup = null;
			}.bind(this)
		});
		this.webScreenSharePopup.show();
	}

	isViewerMode()
	{
		let viewerMode = false;
		const isBroadcast = this.isBroadcast();
		if (isBroadcast)
		{
			const presenters = this.getBroadcastPresenters();
			const currentUserId = this.controller.getStore().state.application.common.userId;
			const isCurrentUserPresenter = presenters.includes(currentUserId);
			viewerMode = isBroadcast && !isCurrentUserPresenter;
		}
		return viewerMode;
	}

	onCallCreated(e)
	{
		Logger.warn('we got event onCallCreated', e);
		if(this.preCall || this.currentCall)
		{
			return;
		}
		let call = e.call;
		if (call.associatedEntity.type === 'chat' && call.associatedEntity.id === this.params.dialogId)
		{
			this.preCall = e.call;
			this.updatePreCallCounter();
			this.preCall.addEventListener(Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
			this.preCall.addEventListener(Call.Event.onDestroy, this.onPreCallDestroyHandler);

			if (this.waitingForCallStatus)
			{
				this.callEventReceived = true;
			}
			this.setConferenceStatus(true);
			this.setConferenceStartDate(e.call.startDate);
		}

		const userReadyToJoin = this.getConference().common.userReadyToJoin;
		if (userReadyToJoin)
		{
			let viewerMode = this.isViewerMode();

			const videoEnabled = this.getConference().common.joinWithVideo;
			Logger.warn('ready to join call after waiting', videoEnabled, viewerMode);
			setTimeout(() => {
				Call.Hardware.init().then(() => {
					if (viewerMode && this.preCall)
					{
						this.joinCall(this.preCall.id, {
							joinAsViewer: true
						})
					}
					else
					{
						this.startCall(videoEnabled);
					}
				});
			}, 1000);
		}
	}

	releasePreCall()
	{
		if(this.preCall)
		{
			this.preCall.removeEventListener(Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
			this.preCall.removeEventListener(Call.Event.onDestroy, this.onPreCallDestroyHandler);
			this.preCall = null;
		}
	}

	onPreCallDestroy(e)
	{
		if (this.waitingForCallStatusTimeout)
		{
			clearTimeout(this.waitingForCallStatusTimeout);
		}
		this.setConferenceStatus(false);

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
			this.controller.getStore().commit('conference/common', {
				userInCallCount: this.preCall.getParticipatingUsers().length
			});
		}
		else
		{
			this.controller.getStore().commit('conference/common', {
				userInCallCount: 0
			});
		}
	}

	createVideoStrategy()
	{
		if (this.videoStrategy)
		{
			this.videoStrategy.destroy();
		}

		var strategyType = Utils.device.isMobile() ? VideoStrategy.Type.OnlySpeaker : VideoStrategy.Type.AllowAll;

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

	onCallReplaceCamera(event)
	{
		let cameraId = event.data.deviceId;
		Call.Hardware.defaultCamera = cameraId;
		if (this.currentCall)
		{
			this.currentCall.setCameraId(cameraId);
		}
		else
		{
			this.template.$emit('cameraSelected', cameraId);
		}
	}

	onCallReplaceMicrophone(event)
	{
		let microphoneId = event.data.deviceId;
		Call.Hardware.defaultMicrophone = microphoneId.deviceId;
		if (this.callView)
		{
			this.callView.setMicrophoneId(microphoneId);
		}
		if (this.currentCall)
		{
			this.currentCall.setMicrophoneId(microphoneId);
		}
		else
		{
			this.template.$emit('micSelected', event.data.deviceId);
		}
	}

	onCallReplaceSpeaker(event)
	{
		Call.Hardware.defaultSpeaker = event.data.deviceId;
	}

	onCallViewChangeHdVideo(event)
	{
		Call.Hardware.preferHdQuality = event.data.allowHdVideo;
	}

	onCallViewChangeMicAutoParams(event)
	{
		Call.Hardware.enableMicAutoParameters = event.data.allowMicAutoParams;
	}

	onCallViewChangeFaceImprove(event)
	{
		if (typeof (BX.desktop) === 'undefined')
		{
			return;
		}

		BX.desktop.cameraSmoothingStatus(event.data.faceImproveEnabled);
	}

	onCallViewUserRename(event)
	{
		const newName = event.data.newName;

		if (!this.isExternalUser())
		{
			return false;
		}

		if (Utils.device.isMobile())
		{
			this.renameGuestMobile(newName)
		}
		else
		{
			this.renameGuest(newName);
		}
	}

	onCallViewUserPinned(event)
	{
		if (event.data.userId)
		{
			this.updateCallUser(event.data.userId, {pinned: true});

			return true;
		}

		this.controller.getStore().dispatch('call/unpinUser');

		return true;
	}

	renameGuest(newName)
	{
		this.callView.localUser.userModel.renameRequested = true;
		this.setUserName(newName).then(() => {
			this.callView.localUser.userModel.wasRenamed = true;
			Logger.log('setting name to', newName);
		}).catch(error => {
			Logger.error('error setting name', error);
		});
	}

	renameGuestMobile(newName)
	{
		this.setUserName(newName).then(() => {
			Logger.log('setting mobile name to', newName);
			if (this.callView.renameSlider)
			{
				this.callView.renameSlider.close();
			}
		}).catch(error => {
			Logger.error('error setting name', error);
		});
	}

	onCallButtonClick(event)
	{
		const buttonName = event.data.buttonName;
		Logger.warn('Button clicked!', buttonName);

		const handlers = {
			hangup: this.onCallViewHangupButtonClick.bind(this),
			close: this.onCallViewCloseButtonClick.bind(this),
			//inviteUser: this.onCallViewInviteUserButtonClick.bind(this),
			toggleMute: this.onCallViewToggleMuteButtonClick.bind(this),
			toggleScreenSharing: this.onCallViewToggleScreenSharingButtonClick.bind(this),
			record: this.onCallViewRecordButtonClick.bind(this),
			toggleVideo: this.onCallViewToggleVideoButtonClick.bind(this),
			toggleSpeaker: this.onCallViewToggleSpeakerButtonClick.bind(this),
			showChat: this.onCallViewShowChatButtonClick.bind(this),
			toggleUsers: this.onCallViewToggleUsersButtonClick.bind(this),
			share: this.onCallViewShareButtonClick.bind(this),
			fullscreen: this.onCallViewFullScreenButtonClick.bind(this),
			floorRequest: this.onCallViewFloorRequestButtonClick.bind(this),
		};

		if(handlers[buttonName])
		{
			handlers[buttonName](event);
		}
		else
		{
			Logger.error('Button handler not found!', buttonName);
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

	onCallViewToggleMuteButtonClick(event)
	{
		if (this.currentCall)
		{
			this.currentCall.setMuted(event.data.muted);
		}
		else
		{
			this.template.$emit('setMicState', !event.data.muted);
		}

		if (this.isRecording())
		{
			BXDesktopSystem.CallRecordMute(event.data.muted);
		}

		this.callView.setMuted(event.data.muted);
	}

	onCallViewToggleScreenSharingButtonClick()
	{
		if (this.getFeatureState('screenSharing') === ConferenceApplication.FeatureState.Limited)
		{
			this.showFeatureLimitSlider('screenSharing');
			return;
		}

		if (this.getFeatureState('screenSharing') === ConferenceApplication.FeatureState.Disabled)
		{
			return;
		}

		if (this.currentCall.isScreenSharingStarted())
		{
			this.currentCall.stopScreenSharing();

			if (this.isRecording())
			{
				BXDesktopSystem.CallRecordStopSharing();
			}

			if (this.floatingScreenShareWindow)
			{
				this.floatingScreenShareWindow.close();
			}

			if (this.webScreenSharePopup)
			{
				this.webScreenSharePopup.close();
			}
		}
		else
		{
			this.restClient.callMethod("im.call.onShareScreen", {callId: this.currentCall.id});
			this.currentCall.startScreenSharing();
		}
	}

	onCallViewRecordButtonClick(event)
	{
		if (event.data.recordState === Call.View.RecordState.Started)
		{
			if (this.getFeatureState('record') === ConferenceApplication.FeatureState.Limited)
			{
				this.showFeatureLimitSlider('record');
				return;
			}

			if (this.getFeatureState('record') === ConferenceApplication.FeatureState.Disabled)
			{
				return;
			}

			if (this.canRecord())
			{
				// TODO: create popup menu with choice type of record - im/install/js/im/call/controller.js:1635
				// Call.View.RecordType.Video / Call.View.RecordType.Audio

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
		else if (event.data.recordState === Call.View.RecordState.Paused)
		{
			if (this.canRecord())
			{
				BXDesktopSystem.CallRecordPause(true);
			}
		}
		else if (event.data.recordState === Call.View.RecordState.Resumed)
		{
			if (this.canRecord())
			{
				BXDesktopSystem.CallRecordPause(false);
			}
		}
		else if (event.data.recordState === Call.View.RecordState.Stopped)
		{
			this.callView.setButtonActive('record', false);
		}

		this.currentCall.sendRecordState({
			action: event.data.recordState,
			date: new Date()
		});

		this.callRecordState = event.data.recordState;
	}

	onCallViewToggleVideoButtonClick(event)
	{
		if (this.currentCall)
		{
			if (!Call.Hardware.initialized)
			{
				return;
			}
			if (event.data.video && Object.values(Call.Hardware.cameraList).length === 0)
			{
				return;
			}
			if(!event.data.video)
			{
				this.callView.releaseLocalMedia();
			}
			this.currentCall.setVideoEnabled(event.data.video);
		}
		else
		{
			this.template.$emit('setCameraState', event.data.video);
		}
	}

	onCallViewToggleSpeakerButtonClick(event)
	{
		this.callView.muteSpeaker(!event.speakerMuted);

		if (event.fromHotKey)
		{
			BX.UI.Notification.Center.notify({
				content: BX.message(this.callView.speakerMuted? 'IM_M_CALL_MUTE_SPEAKERS_OFF': 'IM_M_CALL_MUTE_SPEAKERS_ON'),
				position: "top-right",
				autoHideDelay: 3000,
				closeButton: true
			});
		}
	}

	onCallViewShareButtonClick()
	{
		let notifyWidth = 400;
		if (Utils.device.isMobile() && document.body.clientWidth < 400)
		{
			notifyWidth = document.body.clientWidth - 40;
		}

		BX.UI.Notification.Center.notify({
			content: Loc.getMessage('BX_IM_VIDEOCONF_LINK_COPY_DONE'),
			autoHideDelay: 4000,
			width: notifyWidth
		});

		Clipboard.copy(this.getDialogData().public.link);
	}

	onCallViewFullScreenButtonClick()
	{
		this.toggleFullScreen();
	}

	onFloatingScreenShareBackToCallClick()
	{
		BXDesktopWindow.ExecuteCommand('show.active')
		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.hide();
		}
	}

	onFloatingScreenShareStopClick()
	{
		BXDesktopWindow.ExecuteCommand('show.active')
		this.onCallViewToggleScreenSharingButtonClick();
	}

	onFloatingScreenShareChangeScreenClick()
	{
		if (this.currentCall)
		{
			this.currentCall.startScreenSharing(true);
		}
	}

	onWindowFocus()
	{
		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.hide();
		}
	}

	onWindowBlur()
	{
		if(this.floatingScreenShareWindow && this.currentCall && this.currentCall.isScreenSharingStarted())
		{
			this.floatingScreenShareWindow.show();
		}
	}

	isFullScreen ()
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

	toggleFullScreen ()
	{
		if(this.isFullScreen())
		{
			this.exitFullScreen();
		}
		else
		{
			this.enterFullScreen();
		}
	}

	enterFullScreen ()
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
		{
			document.body.webkitRequestFullScreen();
		}
		else if (BX.browser.IsFirefox())
		{
			document.body.requestFullscreen();
		}
	}

	exitFullScreen()
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
		else if (document.document.exitFullscreen())
		{
			document.exitFullscreen()
		}
	}

	onCallViewShowChatButtonClick()
	{
		this.toggleChat();
	}

	onCallViewToggleUsersButtonClick()
	{
		this.toggleUserList();
	}

	onCallViewFloorRequestButtonClick()
	{
		const floorState = this.callView.getUserFloorRequestState(Call.Engine.getCurrentUserId());
		const talkingState = this.callView.getUserTalking(Call.Engine.getCurrentUserId());

		this.callView.setUserFloorRequestState(Call.Engine.getCurrentUserId(), !floorState);

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

	bindCallEvents()
	{
		this.currentCall.addEventListener(Call.Event.onUserInvited, this.onCallUserInvitedHandler);
		this.currentCall.addEventListener(Call.Event.onDestroy, this.onCallDestroyHandler);
		this.currentCall.addEventListener(Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
		this.currentCall.addEventListener(Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
		this.currentCall.addEventListener(Call.Event.onUserCameraState, this.onCallUserCameraStateHandler);
		this.currentCall.addEventListener(Call.Event.onUserVideoPaused, this.onCallUserVideoPausedHandler);
		this.currentCall.addEventListener(Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
		this.currentCall.addEventListener(Call.Event.onRemoteMediaReceived, this.onCallRemoteMediaReceivedHandler);
		this.currentCall.addEventListener(Call.Event.onRemoteMediaStopped, this.onCallRemoteMediaStoppedHandler);
		this.currentCall.addEventListener(Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
		this.currentCall.addEventListener(Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		this.currentCall.addEventListener(Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
		this.currentCall.addEventListener(Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
		this.currentCall.addEventListener(Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
		this.currentCall.addEventListener(Call.Event.onMicrophoneLevel, this.onMicrophoneLevelHandler);
		//this.currentCall.addEventListener(Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
		//this.currentCall.addEventListener(Call.Event.onCallFailure, this._onCallFailureHandler);
		this.currentCall.addEventListener(Call.Event.onJoin, this._onCallJoinHandler);
		this.currentCall.addEventListener(Call.Event.onLeave, this.onCallLeaveHandler);
	}

	removeCallEvents()
	{
		this.currentCall.removeEventListener(Call.Event.onUserInvited, this.onCallUserInvitedHandler);
		this.currentCall.removeEventListener(Call.Event.onDestroy, this.onCallDestroyHandler);
		this.currentCall.removeEventListener(Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
		this.currentCall.removeEventListener(Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
		this.currentCall.removeEventListener(Call.Event.onUserCameraState, this.onCallUserCameraStateHandler);
		this.currentCall.removeEventListener(Call.Event.onUserVideoPaused, this.onCallUserVideoPausedHandler);
		this.currentCall.removeEventListener(Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
		this.currentCall.removeEventListener(Call.Event.onRemoteMediaReceived, this.onCallRemoteMediaReceivedHandler);
		this.currentCall.removeEventListener(Call.Event.onRemoteMediaStopped, this.onCallRemoteMediaStoppedHandler);
		this.currentCall.removeEventListener(Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
		this.currentCall.removeEventListener(Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		this.currentCall.removeEventListener(Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
		this.currentCall.removeEventListener(Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
		this.currentCall.removeEventListener(Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
		this.currentCall.removeEventListener(Call.Event.onMicrophoneLevel, this.onMicrophoneLevelHandler);
		//this.currentCall.removeEventListener(Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
		//this.currentCall.removeEventListener(Call.Event.onCallFailure, this._onCallFailureHandler);
		this.currentCall.removeEventListener(Call.Event.onLeave, this.onCallLeaveHandler);
	}

	onCallUserInvited(e)
	{
		this.callView.addUser(e.userId);

		Call.Util.getUsers(this.currentCall.id, [e.userId]).then(userData => {
			this.controller.getStore().dispatch('users/set', Object.values(userData));
			this.controller.getStore().dispatch('conference/setUsers', {users: Object.keys(userData)});
			this.callView.updateUserData(userData)
		});
	}

	onCallUserStateChanged(e)
	{
		this.callView.setUserState(e.userId, e.state);
		this.updateCallUser(e.userId,{state: e.state});
		/*if (e.direction)
		{
			this.callView.setUserDirection(e.userId, e.direction);
		}*/
	}

	onCallUserMicrophoneState(e)
	{
		this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
		this.updateCallUser(e.userId, {microphoneState: e.microphoneState});
	}

	onCallUserCameraState(e)
	{
		this.callView.setUserCameraState(e.userId, e.cameraState);
		this.updateCallUser(e.userId, {cameraState: e.cameraState});
	}

	onCallUserVideoPaused(e)
	{
		this.callView.setUserVideoPaused(e.userId, e.videoPaused);
	}

	onCallLocalMediaReceived(e)
	{
		//this.template.$emit('callLocalMediaReceived');

		this.stopLocalVideoStream();
		const enableVideoMirroring = e.tag == "main" ? Call.Hardware.enableMirroring : false;
		this.callView.setLocalStream(e.stream, enableVideoMirroring);
		this.callView.setButtonActive("screen", e.tag == "screen");
		if(e.tag == "screen")
		{
			if (!Utils.platform.isBitrixDesktop())
			{
				this.showWebScreenSharePopup();
			}
			this.callView.blockSwitchCamera();
			this.callView.updateButtons();
		}
		else
		{
			if (this.webScreenSharePopup)
			{
				this.webScreenSharePopup.close();
			}

			if(!this.currentCall.callFromMobile && !this.isViewerMode())
			{
				this.callView.unblockSwitchCamera();
				this.callView.updateButtons();
			}
		}
	}

	onCallRemoteMediaReceived(e)
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

	onCallRemoteMediaStopped(e)
	{
		if (this.callView)
		{
			if ('mediaRenderer' in e)
			{
				if (e.kind === 'video' || e.kind === 'sharing')
				{
					this.callView.setVideoRenderer(e.userId, null);
				}
			}
			else
			{
				this.callView.setUserMedia(e.userId, e.kind, null);
			}
		}
	}

	onCallUserVoiceStarted(e)
	{
		if (e.local)
		{
			if (this.currentCall.muted && this.allowMutePopup)
			{
				this.showMicMutedNotification();
			}
			return;
		}

		this.callView.setUserTalking(e.userId, true);
		this.callView.setUserFloorRequestState(e.userId, false);
		this.updateCallUser(e.userId, {talking: true, floorRequestState: false});
	}

	onCallUserVoiceStopped(e)
	{
		this.callView.setUserTalking(e.userId, false);
		this.updateCallUser(e.userId, {talking: false});
	}

	onCallUserScreenState(e)
	{
		if(this.callView)
		{
			this.callView.setUserScreenState(e.userId, e.screenState);
		}
		this.updateCallUser(e.userId, {screenState: e.screenState});
	}

	onCallUserRecordState(event)
	{
		this.callRecordState = event.recordState.state;
		this.callView.setRecordState(event.recordState);

		if (!this.canRecord() || event.userId != this.controller.getUserId())
		{
			return true;
		}

		if (
			event.recordState.state === Call.View.RecordState.Started
			&& event.recordState.userId == this.controller.getUserId()
		)
		{
			const windowId = window.bxdWindowId || window.document.title;
			let fileName = BX.message('IM_CALL_RECORD_NAME');
			let dialogId = this.currentCall.associatedEntity.id;
			let dialogName = this.currentCall.associatedEntity.name;
			let callId = this.currentCall.id;
			let callDate = BX.Main.Date.format(this.params.formatRecordDate || 'd.m.Y');

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
				fileName = "call_record_"+this.currentCall.id;
			}

			Call.Engine.getRestClient().callMethod("im.call.onStartRecord", {callId: this.currentCall.id});
			BXDesktopSystem.CallRecordStart({
				windowId,
				fileName,
				callId,
				callDate,
				dialogId,
				dialogName,
				muted: this.currentCall.isMuted(),
				cropTop: 72,
				cropBottom: 73,
				shareMethod: 'im.disk.record.share'
			});
		}
		else if (event.recordState.state === Call.View.RecordState.Stopped)
		{
			BXDesktopSystem.CallRecordStop();
		}

		return true;
	}

	onCallUserFloorRequest(e)
	{
		this.callView.setUserFloorRequestState(e.userId, e.requestActive);
		this.updateCallUser(e.userId, {floorRequestState: e.requestActive});
	}

	onMicrophoneLevel(e)
	{
		this.callView.setMicrophoneLevel(e.level);
	}

	onCallJoin(e)
	{
		if (!e.local)
		{
			return;
		}

		if (!this.isViewerMode())
		{
			this.callView.unblockButtons(['camera', 'floorRequest', 'screen', 'record']);
		}
		this.callView.setUiState(Call.View.UiState.Connected);
	}

	onCallLeave(e)
	{
		if (!e.local)
		{
			return;
		}

		if (this.webScreenSharePopup)
		{
			this.webScreenSharePopup.close();
		}

		this.endCall();
	}

	onCallDestroy(e)
	{
		this.currentCall = null;

		if (this.floatingScreenShareWindow)
		{
			this.floatingScreenShareWindow.close;
		}

		if (this.webScreenSharePopup)
		{
			this.webScreenSharePopup.close();
		}

		this.restart();
	}

	onCheckDevicesSave(changedValues)
	{
		if (changedValues['camera'])
		{
			Call.Hardware.defaultCamera = changedValues['camera'];
		}

		if (changedValues['microphone'])
		{
			Call.Hardware.defaultMicrophone = changedValues['microphone'];
		}

		if (changedValues['audioOutput'])
		{
			Call.Hardware.defaultSpeaker = changedValues['audioOutput'];
		}

		if (changedValues['preferHDQuality'])
		{
			Call.Hardware.preferHdQuality = changedValues['preferHDQuality'];
		}

		if (changedValues['enableMicAutoParameters'])
		{
			Call.Hardware.enableMicAutoParameters = changedValues['enableMicAutoParameters'];
		}
	}

	setCameraState(state)
	{
		this.callView.setCameraState(state);
	}
	/* endregion 01. Call methods */

	/* region 02. Component methods */
		/* region 01. General actions */
		isChatShowed()
		{
			return this.getConference().common.showChat;
		}

		toggleChat()
		{
			const rightPanelMode = this.getConference().common.rightPanelMode;
			if (rightPanelMode === RightPanelMode.hidden)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.chat});
				this.callView.setButtonActive('chat', true);
			}
			else if (rightPanelMode === RightPanelMode.chat)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.hidden});
				this.callView.setButtonActive('chat', false);
			}
			else if (rightPanelMode === RightPanelMode.users)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.split});
				this.callView.setButtonActive('chat', true);
			}
			else if (rightPanelMode === RightPanelMode.split)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.users});
				this.callView.setButtonActive('chat', false);
			}
		}

		toggleUserList()
		{
			const rightPanelMode = this.getConference().common.rightPanelMode;
			if (rightPanelMode === RightPanelMode.hidden)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.users});
				this.callView.setButtonActive('users', true);
			}
			else if (rightPanelMode === RightPanelMode.users)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.hidden});
				this.callView.setButtonActive('users', false);
			}
			else if (rightPanelMode === RightPanelMode.chat)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.split});
				this.callView.setButtonActive('users', true);
			}
			else if (rightPanelMode === RightPanelMode.split)
			{
				this.controller.getStore().dispatch('conference/changeRightPanelMode', {mode: RightPanelMode.chat});
				this.callView.setButtonActive('users', false);
			}
		}

		pinUser(user)
		{
			if (!this.callView)
			{
				return false;
			}
			this.callView.pinUser(user.id);
			this.callView.setLayout(Call.View.Layout.Centered);
		}

		unpinUser()
		{
			if (!this.callView)
			{
				return false;
			}
			this.callView.unpinUser();
		}

		changeBackground()
		{
			if (!Call.Hardware)
			{
				return false;
			}
			Call.BackgroundDialog.open();
		}

		openChat(user)
		{
			this.desktop.onCustomEvent('bxConferenceOpenChat', [user.id]);
		}

		openProfile(user)
		{
			this.desktop.onCustomEvent('bxConferenceOpenProfile', [user.id]);
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

		sendNewMessageNotify(params)
		{
			const MAX_LENGTH = 40;
			const AUTO_HIDE_TIME = 4000;

			if (!this.checkIfMessageNotifyIsNeeded(params))
			{
				return false;
			}
			const text = Utils.text.purify(params.message.text, params.message.params, params.files);
			let avatar = '';
			let userName = '';

			// avatar and username only for non-system messages
			if (params.message.senderId > 0 && params.message.system !== 'Y')
			{
				const messageAuthor = this.controller.getStore().getters['users/get'](params.message.senderId, true);
				userName = messageAuthor.name;
				avatar = messageAuthor.avatar;
			}

			Notifier.notify({
				id: `im-videconf-${params.message.id}`,
				title: userName,
				icon: avatar,
				text
			});

			return true;
		}

		checkIfMessageNotifyIsNeeded(params)
		{
			const rightPanelMode = this.getConference().common.rightPanelMode;
			return !Utils.device.isMobile()
				&& params.chatId === this.getChatId()
				&& (rightPanelMode !== RightPanelMode.chat || rightPanelMode !== RightPanelMode.split)
				&& params.message.senderId !== this.controller.getUserId()
				&& !this.getConference().common.error;
		}

		onInputFocus(e)
		{
			this.callView.setHotKeyTemporaryBlock(true);
		}

		onInputBlur(e)
		{
			this.callView.setHotKeyTemporaryBlock(false);
		}

		setUserWasRenamed()
		{
			if (this.callView)
			{
				this.callView.localUser.userModel.wasRenamed = true;
			}
		}
		/* endregion 01. General actions */

		/* region 02. Store actions */
		setError(errorCode)
		{
			const currentError = this.getConference().common.error;
			// if user kicked from call - dont show him end of call form
			if (currentError && currentError === ConferenceErrorCode.kickedFromCall)
			{
				return;
			}

			this.controller.getStore().commit('conference/setError', {errorCode});
		}

		toggleSmiles()
		{
			this.controller.getStore().commit('conference/toggleSmiles');
		}

		setJoinType(joinWithVideo)
		{
			this.controller.getStore().commit('conference/setJoinType', {joinWithVideo});
		}

		setConferenceStatus(conferenceStarted)
		{
			this.controller.getStore().commit('conference/setConferenceStatus', {conferenceStarted});
		}

		setConferenceStartDate(conferenceStartDate)
		{
			this.controller.getStore().commit('conference/setConferenceStartDate', {conferenceStartDate});
		}

		setUserReadyToJoin()
		{
			this.controller.getStore().commit('conference/setUserReadyToJoin');
		}

		updateCallUser(userId, fields)
		{
			this.controller.getStore().dispatch('call/updateUser', {id: userId, fields});
		}
		/* endregion 02. Store actions */

		/* region 03. Rest actions */
		setUserName(name)
		{
			return new Promise((resolve, reject) => {
				this.restClient.callMethod('im.call.user.update', {
					name: name,
					chat_id: this.getChatId()
				}).then(() => {
					resolve();
				}).catch((error) => {
					reject(error)
				});
			});
		}

		checkPassword(password)
		{
			return new Promise((resolve, reject) => {
				this.restClient.callMethod('im.videoconf.password.check', { password, alias: this.params.alias })
					.then(result => {
						if (result.data() === true)
						{
							this.restClient.setPassword(password);
							this.controller.getStore().commit('conference/common', {
								passChecked: true
							});
							this.initUserComplete();
							resolve();
						}
						else
						{
							reject();
						}
					}).catch(result => {
						console.error('Password check error', result);
					});
			});
		}

		changeLink()
		{
			return new Promise((resolve, reject) => {
				this.restClient.callMethod('im.videoconf.share.change', {
					dialog_id: this.getDialogId()
				}).then(() => {
					resolve();
				}).catch((error) => {
					reject(error)
				});
			});
		}
		/* endregion 03. Rest actions */
	/* endregion 02. Component methods */

/* endregion 02. Methods */

/* region 03. Utils */
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

	getConference()
	{
		return this.controller.getStore().state.conference;
	}

	isBroadcast()
	{
		return this.getConference().common.isBroadcast;
	}

	getBroadcastPresenters()
	{
		return this.getConference().common.presenters;
	}

	isExternalUser()
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

	isHttps()
	{
		return location.protocol === 'https:';
	}

	getUserHash()
	{
		return this.getConference().user.hash;
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

	switchToSessAuth()
	{
		this.restClient.restClient.queryParams = undefined;

		return true;
	}

/* endregion 03. Utils */
}

ConferenceApplication.FeatureState = {
	Enabled: 'enabled',
	Disabled: 'disabled',
	Limited: 'limited',
};

export {ConferenceApplication};
