/**
 * Bitrix Im mobile
 * Dialog application
 *
 * @package bitrix
 * @subpackage mobile
 * @copyright 2001-2020 Bitrix
 */

// im
import 'im_call';
import 'im.debug';
import 'im.application.launch';
import 'im.component.call';
import { CallApplicationModel } from "im.model";
import { Controller } from 'im.controller';
import { Utils } from "im.lib.utils";
import { Cookie } from "im.lib.cookie";
import { LocalStorage } from "im.lib.localstorage";
import { Logger } from "im.lib.logger";
import { Clipboard } from 'im.lib.clipboard';
import { Uploader } from "im.lib.uploader";
import { Desktop } from "im.lib.desktop";
import {
	CallApplicationErrorCode,
	EventType,
	FileStatus,
	RestMethod as ImRestMethod,
	RestMethodHandler as ImRestMethodHandler
} from "im.const";

//ui
import 'ui.notification';
import 'ui.buttons';
import 'ui.progressround';
import 'ui.viewer';
import { VueVendorV2 } from "ui.vue";
import { VuexBuilder } from "ui.vue.vuex";

// core
import {Loc} from "main.core";
import "promise";
import 'main.date';

// pull and rest
import { PullClient } from "pull.client";
import { ImCallPullHandler } from "im.provider.pull";
import { CallRestClient } from "./utils/restclient"

class CallApplication
{
	/* region 01. Initialize */
	constructor(params = {})
	{
		this.inited = false;
		this.dialogInited = false;
		this.initPromise = new BX.Promise;

		this.params = params;
		this.params.userId = this.params.userId? parseInt(this.params.userId): 0;
		this.params.siteId = this.params.siteId || '';
		this.params.chatId = this.params.chatId? parseInt(this.params.chatId): 0;
		this.params.dialogId = this.params.chatId? 'chat'+this.params.chatId.toString(): '0';
		this.params.passwordRequired = !!this.params.passwordRequired;

		this.messagesQueue = [];

		this.template = null;
		this.rootNode = this.params.node || document.createElement('div');

		this.event = new VueVendorV2;

		this.callContainer = null;
		this.callView = null;
		this.preCall = null;
		this.currentCall = null;
		this.videoStrategy = null;

		this.featureConfig = {};
		(params.featureConfig || []).forEach(limit => {
			this.featureConfig[limit.id] = limit;
		});

		this.localVideoStream = null;

		this.conferencePageTagInterval = null;

		this.onCallUserInvitedHandler = this.onCallUserInvited.bind(this);
		this.onCallUserStateChangedHandler = this.onCallUserStateChanged.bind(this);
		this.onCallUserMicrophoneStateHandler = this.onCallUserMicrophoneState.bind(this);
		this.onCallLocalMediaReceivedHandler = BX.debounce(this.onCallLocalMediaReceived.bind(this), 1000);
		this.onCallUserStreamReceivedHandler = this.onCallUserStreamReceived.bind(this);
		this.onCallUserVoiceStartedHandler = this.onCallUserVoiceStarted.bind(this);
		this.onCallUserVoiceStoppedHandler = this.onCallUserVoiceStopped.bind(this);
		this.onCallUserScreenStateHandler = this.onCallUserScreenState.bind(this);
		this.onCallUserRecordStateHandler = this.onCallUserRecordState.bind(this);
		this.onCallUserFloorRequestHandler = this.onCallUserFloorRequest.bind(this);
		this._onCallJoinHandler = this.onCallJoin.bind(this);
		this.onCallLeaveHandler = this.onCallLeave.bind(this);
		this.onCallDestroyHandler = this.onCallDestroy.bind(this);

		this.onPreCallDestroyHandler = this.onPreCallDestroy.bind(this);
		this.onPreCallUserStateChangedHandler = this.onPreCallUserStateChanged.bind(this);

		this.waitingForCallStatus = false;
		this.waitingForCallStatusTimeout = null;
		this.callEventReceived = false;
		this.callRecordState = BX.Call.View.RecordState.Stopped;

		this.desktop = null;
		this.floatingScreenShareWindow = null;

		this.initDesktopEvents()
			.then(() => this.initRestClient())
			.then(() => this.subscribePreCallChanges())
			.then(() => this.initPullClient())
			.then(() => this.initCore())
			.then(() => this.setModelData())
			.then(() => this.initComponent())
			.then(() => this.initCallInterface())
			.then(() => this.initUploader())
			.then(() => this.initUserComplete())
			.catch(() => {})
		;
	}

	initDesktopEvents()
	{
		if (!Utils.platform.isBitrixDesktop())
		{
			return new Promise((resolve, reject) => resolve());
		}

		this.desktop = new Desktop();
		this.floatingScreenShareWindow = new BX.Call.FloatingScreenShare({
			desktop: this.desktop,
			onBackToCallClick: this.onFloatingScreenShareBackToCallClick.bind(this),
			onStopSharingClick: this.onFloatingScreenShareStopClick.bind(this)
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
					Logger.log('setSharingData error', error);
				});
			});

			window.addEventListener('focus', () => {
				this.onWindowFocus();
			});

			window.addEventListener('blur', () => {
				this.onWindowBlur();
			});
		}

		return new Promise((resolve, reject) => resolve());
	}

	initRestClient()
	{
		this.restClient = new CallRestClient({endpoint: this.getHost()+'/rest'});
		this.restClient.setConfId(this.params.conferenceId);

		return new Promise((resolve, reject) => resolve());
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
		if (this.getStartupErrorCode())
		{
			this.setError(this.getStartupErrorCode());
		}

		return this.controller.createVue(this, {
			el: this.rootNode,
			data: () =>
			{
				return {
					chatId: this.getChatId(),
					dialogId: this.getDialogId()
				};
			},
			template: `<bx-im-component-call :chatId="chatId"/>`,
		})
		.then(vue => {
			this.template = vue;

			return new Promise((resolve, reject) => resolve());
		});
	}

	setModelData()
	{
		this.controller.getStore().commit('application/set', {
			dialog: {
				chatId: this.getChatId(),
				dialogId: this.getDialogId()
			},
		});

		if (this.params.passwordRequired)
		{
			this.controller.getStore().commit('callApplication/common', {
				passChecked: false,
			});
		}

		if (this.params.conferenceTitle)
		{
			this.controller.getStore().commit('callApplication/setConferenceTitle', {
				conferenceTitle: this.params.conferenceTitle,
			});
		}

		if (this.params.alias)
		{
			this.controller.getStore().commit('callApplication/setAlias', {
				alias: this.params.alias,
			});
		}

		return new Promise((resolve, reject) => resolve());
	}

	initCallInterface()
	{
		this.callContainer = document.getElementById('bx-im-component-call-container');

		this.callView = new BX.Call.View({
			container: this.callContainer,
			showChatButtons: true,
			showShareButton: this.getFeatureState('screenSharing') !== CallApplication.FeatureState.Disabled,
			showRecordButton: this.getFeatureState('record') !== CallApplication.FeatureState.Disabled,
			userLimit: BX.Call.Util.getUserLimit(),
			isIntranetOrExtranet: !!this.params.isIntranetOrExtranet,
			language: this.params.language,
			layout: Utils.device.isMobile() ? BX.Call.View.Layout.Mobile : BX.Call.View.Layout.Centered,
			uiState: BX.Call.View.UiState.Preparing,
			blockedButtons: ['camera', 'microphone', 'chat', 'floorRequest', 'screen', 'record'],
			localUserState: BX.Call.UserState.Idle,
			hiddenButtons: this.params.isIntranetOrExtranet? []: ['record']
		});

		this.callView.subscribe(BX.Call.View.Event.onButtonClick, this.onCallButtonClick.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onReplaceCamera, this.onCallReplaceCamera.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onReplaceMicrophone, this.onCallReplaceMicrophone.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onReplaceSpeaker, this.onCallReplaceSpeaker.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onChangeHdVideo, this.onCallViewChangeHdVideo.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onChangeMicAutoParams, this.onCallViewChangeMicAutoParams.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onUserNameMouseOver, this.onCallViewUserNameMouseOver.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onUserNameMouseOut, this.onCallViewUserNameMouseOut.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onUserNameClick, this.onCallViewUserNameClick.bind(this));
		this.callView.subscribe(BX.Call.View.Event.onUserChangeNameClick, this.onCallViewUserChangeNameClick.bind(this));

		this.callView.blockAddUser();
		this.callView.blockHistoryButton();

		if (!Utils.device.isMobile())
		{
			this.callView.show();
		}

		return new Promise((resolve, reject) => resolve());
	}

	initUser()
	{
		return new Promise((resolve, reject) => {
			if (this.getStartupErrorCode() || !this.controller.getStore().state.callApplication.common.passChecked)
			{
				return reject();
			}

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
					BX.message['USER_ID'] = result.data().id;
					this.controller.getStore().commit('callApplication/user', {
						id: result.data().id,
						hash: result.data().hash
					});

					this.controller.setUserId(result.data().id);
					this.callView.setLocalUserId(result.data().id);

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

	initUploader()
	{
		this.uploader = new Uploader({
			generatePreview: true,
			sender: {
				actionUploadChunk: 'im.call.disk.upload',
				actionCommitFile: 'im.call.disk.commit',
			}
		});

		this.uploader.subscribe('onStartUpload', event => {
			const eventData = event.getData();
			Logger.log('Uploader: onStartUpload', eventData);

			this.controller.getStore().dispatch('files/update', {
				chatId: this.getChatId(),
				id: eventData.id,
				fields: {
					status: FileStatus.upload,
					progress: 0
				}
			});
		});

		this.uploader.subscribe('onProgress', (event) => {
			const eventData = event.getData();
			Logger.log('Uploader: onProgress', eventData);

			this.controller.getStore().dispatch('files/update', {
				chatId: this.getChatId(),
				id: eventData.id,
				fields: {
					status: FileStatus.upload,
					progress: (eventData.progress === 100 ? 99 : eventData.progress),
				}
			});
		});

		this.uploader.subscribe('onSelectFile', (event) => {
			const eventData = event.getData();
			const file = eventData.file;
			Logger.log('Uploader: onSelectFile', eventData);

			let fileType = 'file';
			if (file.type.toString().startsWith('image'))
			{
				fileType = 'image';
			}
			else if (file.type.toString().startsWith('video'))
			{
				fileType = 'video';
			}

			this.controller.getStore().dispatch('files/add', {
				chatId: this.getChatId(),
				authorId: this.controller.getUserId(),
				name: file.name,
				type: fileType,
				extension: file.name.split('.').splice(-1)[0],
				size: file.size,
				image: !eventData.previewData? false: {
					width: eventData.previewDataWidth,
					height: eventData.previewDataHeight,
				},
				status: FileStatus.wait,
				progress: 0,
				authorName: this.controller.application.getCurrentUser().name,
				urlPreview: eventData.previewData? URL.createObjectURL(eventData.previewData) : "",
			}).then(fileId => {
				this.addMessage('', {id: fileId, source: eventData, previewBlob: eventData.previewData})
			});
		});

		this.uploader.subscribe('onComplete', (event) => {
			const eventData = event.getData();
			Logger.log('Uploader: onComplete', eventData);

			this.controller.getStore().dispatch('files/update', {
				chatId: this.getChatId(),
				id: eventData.id,
				fields: {
					status: FileStatus.wait,
					progress: 100
				}
			});

			const message = this.messagesQueue.find(message => {
				if (message.file)
				{
					return message.file.id === eventData.id;
				}

				return false;
			});
			const fileType = this.controller.getStore().getters['files/get'](this.getChatId(), message.file.id, true).type;

			this.fileCommit({
				chatId: this.getChatId(),
				uploadId: eventData.result.data.file.id,
				messageText: message.text,
				messageId: message.id,
				fileId: message.file.id,
				fileType
			}, message);
		});

		this.uploader.subscribe('onUploadFileError', (event) => {
			const eventData = event.getData();
			Logger.log('Uploader: onUploadFileError', eventData);

			const message = this.messagesQueue.find(message => {
				if (message.file)
				{
					return message.file.id === eventData.id;
				}

				return false;
			});

			this.fileError(this.getChatId(), message.file.id, message.id);
		});

		this.uploader.subscribe('onCreateFileError', (event) => {
			const eventData = event.getData();
			Logger.log('Uploader: onCreateFileError', eventData);

			const message = this.messagesQueue.find(message => {
				if (message.file)
				{
					return message.file.id === eventData.id;
				}

				return false;
			});

			this.fileError(this.getChatId(), message.file.id, message.id);
		});

		return new Promise((resolve, reject) => resolve());
	}

	initUserComplete()
	{
		return this.initUser()
			.then(() => this.startPageTagInterval())
			.then(() => this.tryJoinExistingCall())
			.then(() => this.initCall())
			.then(() => this.initPullHandlers())
			.then(() => this.subscribeToStoreChanges())
			.then(() => this.initComplete())
			.catch(() => {});
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

	subscribePreCallChanges()
	{
		BX.addCustomEvent(window, 'CallEvents::callCreated', this.onCallCreated.bind(this));
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
			this.preCall.addEventListener(BX.Call.Event.onUserStateChanged, this.onPreCallUserStateChangedHandler);
			this.preCall.addEventListener(BX.Call.Event.onDestroy, this.onPreCallDestroyHandler);

			if (this.waitingForCallStatus)
			{
				this.callEventReceived = true;
			}
			this.setConferenceStatus(true);
			this.setConferenceStartDate(e.call.startDate);
		}

		const userReadyToJoin = this.controller.getStore().state.callApplication.common.userReadyToJoin;
		if (userReadyToJoin)
		{
			const videoEnabled = this.controller.getStore().state.callApplication.common.joinWithVideo;
			setTimeout(() => {
				BX.Call.Hardware.init().then(() => {
					this.startCall(videoEnabled);
				});
			}, 1000);
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
		this.callView.unblockButtons(['chat']);
	}

	createVideoStrategy()
	{
		if (this.videoStrategy)
		{
			this.videoStrategy.destroy();
		}

		var strategyType = Utils.device.isMobile() ? BX.Call.VideoStrategy.Type.OnlySpeaker : BX.Call.VideoStrategy.Type.AllowAll;

		this.videoStrategy = new BX.Call.VideoStrategy({
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

		if (this.controller.getStore().state.callApplication.common.inited)
		{
			this.inited = true;
			this.initPromise.resolve(this);
		}
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

	restart()
	{
		console.trace("restart");
		return;
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
		this.controller.getStore().commit('callApplication/returnToPreparation');
	}

/* endregion 01. Initialize */

/* region 02. Methods */

	/* region 01. Call methods */
	initHardware()
	{
		return new Promise((resolve, reject) =>
		{
			BX.Call.Hardware.init().then(() => {
				if (Object.values(BX.Call.Hardware.microphoneList).length === 0)
				{
					this.setError(CallApplicationErrorCode.missingMicrophone);
				}
				this.callView.unblockButtons(["camera", "microphone"]);
				this.callView.enableMediaSelection();
				resolve();
			}).catch(error => {
				if (error === 'NO_WEBRTC' && this.isHttps())
				{
					this.setError(CallApplicationErrorCode.unsupportedBrowser);
				}
				else if (error === 'NO_WEBRTC' && !this.isHttps())
				{
					this.setError(CallApplicationErrorCode.unsafeConnection);
				}
				reject(error)
			})
		});
	}

	startCall(videoEnabled)
	{
		const provider = BX.Call.Provider.Voximplant;

		if (Utils.device.isMobile())
		{
			this.callView.show();
		}
		else
		{
			this.callView.setLayout(BX.Call.View.Layout.Grid);
		}

		this.callView.setUiState(BX.Call.View.UiState.Calling);
		this.callView.setLocalUserState(BX.Call.UserState.Connected);

		if (this.localVideoStream)
		{
			if (videoEnabled)
			{
				this.callView.setLocalStream(this.localVideoStream, true);
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
		this.controller.getStore().commit('callApplication/startCall');

		BX.Call.Engine.getInstance().createCall({
			type: BX.Call.Type.Permanent,
			entityType: 'chat',
			entityId: this.getDialogId(),
			provider: provider,
			videoEnabled: videoEnabled,
			enableMicAutoParameters: BX.Call.Hardware.enableMicAutoParameters,
			joinExisting: true
		}).then(e => {
			Logger.warn('call created', e);

			this.currentCall = e.call;
			//this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);
			this.currentCall.useHdVideo(true);
			if(BX.Call.Hardware.defaultMicrophone)
			{
				this.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
			}
			if(BX.Call.Hardware.defaultCamera)
			{
				this.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
			}

			if(!Utils.device.isMobile())
			{
				this.callView.setLayout(BX.Call.View.Layout.Grid);
			}
			this.callView.appendUsers(this.currentCall.getUsers());
			BX.Call.Util.getUsers(this.currentCall.id, this.getCallUsers(true)).then(userData => {
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
					useVideo: videoEnabled
				});
			}

		}).catch(e => {
			Logger.warn('creating call error', e);
		});
	}

	endCall()
	{
		if (this.currentCall)
		{
			this.removeCallEvents();
			this.currentCall.hangup();
		}

		if (this.isRecording())
		{
			BXDesktopSystem.CallRecordStop();
		}
		this.callRecordState = BX.Call.View.RecordState.Stopped;

		if (Utils.platform.isBitrixDesktop())
		{
			this.floatingScreenShareWindow.destroy();
			this.floatingScreenShareWindow = null;
			window.close();
		}
		else
		{
			this.callView.releaseLocalMedia();
			this.callView.close();
			this.setError(CallApplicationErrorCode.userLeftCall);
			this.controller.getStore().commit('callApplication/endCall');
		}
	}

	kickFromCall()
	{
		this.setError(CallApplicationErrorCode.kickedFromCall);
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
				state: CallApplication.FeatureState.Enabled,
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
		return this.canRecord() && this.callRecordState != BX.Call.View.RecordState.Stopped;
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

	onCallReplaceCamera(event)
	{
		let cameraId = event.data.deviceId;
		BX.Call.Hardware.defaultCamera = cameraId;
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
		BX.Call.Hardware.defaultMicrophone = microphoneId.deviceId;
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
		BX.Call.Hardware.defaultSpeaker = event.data.deviceId;
	}

	onCallViewChangeHdVideo(event)
	{
		BX.Call.Hardware.preferHdQuality = event.data.allowHdVideo;
	}

	onCallViewChangeMicAutoParams(event)
	{
		BX.Call.Hardware.enableMicAutoParameters = event.data.allowMicAutoParams;
	}

	onCallViewUserNameMouseOver()
	{
		if (!this.isExternalUser())
		{
			return false;
		}

		this.callView.toggleLocalUserNameEditIcon();
	}

	onCallViewUserNameMouseOut()
	{
		if (!this.isExternalUser())
		{
			return false;
		}

		this.callView.toggleLocalUserNameEditIcon();
	}

	onCallViewUserNameClick()
	{
		if (!this.isExternalUser())
		{
			return false;
		}

		this.callView.toggleLocalUserNameInput();
	}

	onCallViewUserChangeNameClick(event)
	{
		if (!this.isExternalUser())
		{
			return false;
		}

		if (Utils.device.isMobile())
		{
			this.renameGuestMobile(event)
		}
		else
		{
			this.renameGuest(event);
		}
	}

	renameGuest(event)
	{
		if (event.data.needToUpdate)
		{
			this.callView.toggleLocalUserNameLoader();
			this.setUserName(event.data.newName).then(() => {
				Logger.log('setting name to', event.data.newName);
			}).catch(error => {
				Logger.log('error setting name', error);
			});
		}
		else
		{
			this.callView.toggleLocalUserNameInput();
		}
	}

	renameGuestMobile(event)
	{
		if (event.data.needToUpdate)
		{
			this.callView.toggleRenameSliderInputLoader();
			this.setUserName(event.data.newName).then(() => {
				Logger.log('setting name to', event.data.newName);
				if (this.callView.renameSlider)
				{
					this.callView.renameSlider.close();
				}
			}).catch(error => {
				Logger.log('error setting name', error);
			});
		}
		else if (!event.data.needToUpdate && this.callView.renameSlider)
		{
			this.callView.renameSlider.close();
		}
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
			showChat: this.onCallViewShowChatButtonClick.bind(this),
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
		if (this.getFeatureState('screenSharing') === CallApplication.FeatureState.Limited)
		{
			this.showFeatureLimitSlider('screenSharing');
			return;
		}

		if (this.getFeatureState('screenSharing') === CallApplication.FeatureState.Disabled)
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
		}
		else
		{
			this.currentCall.startScreenSharing();
		}
	}

	onCallViewRecordButtonClick(event)
	{
		if (event.data.recordState === BX.Call.View.RecordState.Started)
		{
			if (this.getFeatureState('record') === CallApplication.FeatureState.Limited)
			{
				this.showFeatureLimitSlider('record');
				return;
			}

			if (this.getFeatureState('record') === CallApplication.FeatureState.Disabled)
			{
				return;
			}

			if (this.canRecord())
			{
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
		else if (event.data.recordState === BX.Call.View.RecordState.Paused)
		{
			if (this.canRecord())
			{
				BXDesktopSystem.CallRecordPause(true);
			}
		}
		else if (event.data.recordState === BX.Call.View.RecordState.Resumed)
		{
			if (this.canRecord())
			{
				BXDesktopSystem.CallRecordPause(false);
			}
		}
		else if (event.data.recordState === BX.Call.View.RecordState.Stopped)
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
			if (!BX.Call.Hardware.initialized)
			{
				return;
			}
			if (event.data.video && Object.values(BX.Call.Hardware.cameraList).length === 0)
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

	onCallViewFloorRequestButtonClick()
	{
		const floorState = this.callView.getUserFloorRequestState(BX.CallEngine.getCurrentUserId());
		const talkingState = this.callView.getUserTalking(BX.CallEngine.getCurrentUserId());

		this.callView.setUserFloorRequestState(BX.CallEngine.getCurrentUserId(), !floorState);

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
		this.currentCall.addEventListener(BX.Call.Event.onUserInvited, this.onCallUserInvitedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onDestroy, this.onCallDestroyHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserStateChanged, this.onCallUserStateChangedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserMicrophoneState, this.onCallUserMicrophoneStateHandler);
		this.currentCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this.onCallLocalMediaReceivedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onStreamReceived, this.onCallUserStreamReceivedHandler);
		//this.currentCall.addEventListener(BX.Call.Event.onStreamRemoved, this.onCallUserStreamRemoved.bind(this));
		this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStarted, this.onCallUserVoiceStartedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStopped, this.onCallUserVoiceStoppedHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
		this.currentCall.addEventListener(BX.Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
		//this.currentCall.addEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
		//this.currentCall.addEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
		this.currentCall.addEventListener(BX.Call.Event.onJoin, this._onCallJoinHandler);
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
		this.currentCall.removeEventListener(BX.Call.Event.onUserScreenState, this.onCallUserScreenStateHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onUserRecordState, this.onCallUserRecordStateHandler);
		this.currentCall.removeEventListener(BX.Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
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
		//this.template.$emit('callLocalMediaReceived');

		this.stopLocalVideoStream();
		this.callView.setLocalStream(e.stream, e.tag == "main");
		this.callView.setButtonActive("screen", e.tag == "screen");
		if(e.tag == "screen")
		{
			this.callView.blockSwitchCamera();
			this.callView.updateButtons();
		}
		else
		{
			if(!this.currentCall.callFromMobile)
			{
				this.callView.unblockSwitchCamera();
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
		this.callView.setUserFloorRequestState(e.userId, false);
	}

	onCallUserVoiceStopped(e)
	{
		this.callView.setUserTalking(e.userId, false);
	}

	onCallUserScreenState(e)
	{
		if(this.callView)
		{
			this.callView.setUserScreenState(e.userId, e.screenState);
		}
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
			event.recordState.state === BX.Call.View.RecordState.Started
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
			});
		}
		else if (event.recordState.state === BX.Call.View.RecordState.Stopped)
		{
			BXDesktopSystem.CallRecordStop();
		}

		return true;
	}

	onCallUserFloorRequest(e)
	{
		this.callView.setUserFloorRequestState(e.userId, e.requestActive);
	}

	onCallJoin(e)
	{
		if (!e.local)
		{
			return;
		}

		this.callView.unblockButtons(['camera', 'floorRequest', 'screen', 'record']);
		this.callView.setUiState(BX.Call.View.UiState.Connected);
	}

	onCallLeave(e)
	{
		if (!e.local)
		{
			return;
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

	setCameraState(state)
	{
		this.callView.setCameraState(state);
	}
	/* endregion 01. Call methods */

	/* region 02. Component methods */
		/* region 01. General actions */
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
			if (Utils.device.isMobile())
			{
				return true;
			}

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

			return true;
		}

		insertText(params = {})
		{
			this.template.$emit(EventType.textarea.insertText, params);
		}
		/* endregion 01. General actions */

		/* region 02. Store actions */
		setError(errorCode)
		{
			this.controller.getStore().commit('callApplication/setError', {errorCode});
		}

		toggleSmiles()
		{
			this.controller.getStore().commit('callApplication/toggleSmiles');
		}

		setJoinType(joinWithVideo)
		{
			this.controller.getStore().commit('callApplication/setJoinType', {joinWithVideo});
		}

		setConferenceStatus(conferenceStarted)
		{
			this.controller.getStore().commit('callApplication/setConferenceStatus', {conferenceStarted});
		}

		setConferenceStartDate(conferenceStartDate)
		{
			this.controller.getStore().commit('callApplication/setConferenceStartDate', {conferenceStartDate});
		}

		setUserReadyToJoin()
		{
			this.controller.getStore().commit('callApplication/setUserReadyToJoin');
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
							this.controller.getStore().commit('callApplication/common', {
								passChecked: true
							});
							this.initUserComplete();
							resolve();
						}
						else
						{
							reject();
						}
					});
			});
		}
		/* endregion 03. Rest actions */

		/* region 04. Messages and files */
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
			if (!this.getDiskFolderId())
			{
				this.requestDiskFolderId().then(() => {
					this.processSendMessages();
				}).catch(() => {
					Logger.warn('uploadFile', 'Error get disk folder id');
					return false;
				});

				return false;
			}

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

			let diskFolderId = this.getDiskFolderId();
			message.chatId = this.getChatId();

			this.uploader.senderOptions.customHeaders['Call-Auth-Id'] = this.getUserHash();
			this.uploader.senderOptions.customHeaders['Call-Chat-Id'] = message.chatId;

			this.uploader.addTask({
				taskId: message.file.id,
				fileData: message.file.source.file,
				fileName: message.file.source.file.name,
				generateUniqueName: true,
				diskFolderId: diskFolderId,
				previewBlob: message.file.previewBlob,
			});
		}

		uploadFile(event)
		{
			if (!event)
			{
				return false;
			}

			this.uploader.addFilesFromEvent(event);
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

	requestDiskFolderId()
	{
		if (this.requestDiskFolderPromise)
		{
			return this.requestDiskFolderPromise;
		}

		this.requestDiskFolderPromise = new Promise((resolve, reject) =>
		{
			if (
				this.flagRequestDiskFolderIdSended
				|| this.getDiskFolderId()
			)
			{
				this.flagRequestDiskFolderIdSended = false;
				resolve();
				return true;
			}

			this.flagRequestDiskFolderIdSended = true;

			this.controller.restClient.callMethod(ImRestMethod.imDiskFolderGet, {chat_id: this.controller.application.getChatId()}).then(response => {
				this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, response);
				this.flagRequestDiskFolderIdSended = false;
				resolve();
			}).catch(error => {
				this.flagRequestDiskFolderIdSended = false;
				this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFolderGet, error);
				reject();
			});
		});

		return this.requestDiskFolderPromise;
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
				this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, response, message);
			}).catch(error => {
				this.controller.executeRestAnswer(ImRestMethodHandler.imDiskFileCommit, error, message);
			});

			return true;
		}
		/* endregion 04. Messages and files */
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

CallApplication.FeatureState = {
	Enabled: 'enabled',
	Disabled: 'disabled',
	Limited: 'limited',
};

export {CallApplication};
