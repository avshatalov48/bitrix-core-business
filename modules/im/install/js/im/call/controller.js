;(function ()
{
	BX.namespace("BX.Call");
	if (BX.Call.Controller)
	{
		return;
	}

	var Events = {
		onViewStateChanged: 'onViewStateChanged',
	};

	var ViewState = {
		Opened: 'Opened',
		Closed: 'Closed',
		Folded: 'Folded'
	};

	var DocumentType = {
		Resume: 'resume',
		Blank: 'blank'
	};

	var DOC_EDITOR_WIDTH = 961;
	var DOC_TEMPLATE_WIDTH = 328;
	var DOCUMENT_PROMO_CODE = 'im:call-document:16102021:web';
	var DOCUMENT_PROMO_DELAY = 60000 * 5; // 5 minutes
	var DOC_CREATED_EVENT = 'CallController::documentCreated';
	var FILE_TYPE_DOCX = 'docx';
	var FILE_TYPE_XLSX = 'xlsx';
	var FILE_TYPE_PPTX = 'pptx';

	BX.Call.Controller = function (config)
	{
		this.messenger = config.messenger;

		this.container = null;
		this.docEditor = null;
		this.docEditorIframe = null;
		this.maxEditorWidth = DOC_TEMPLATE_WIDTH;
		this.docCreatedForCurrentCall = false;

		this.folded = false;

		/** @var {BX.Call.PlainCall|BX.Call.VoximplantCall} this.currentCall */
		this.currentCall = null;
		this.childCall = null;
		this.callView = null;
		this.callNotification = null;
		this.invitePopup = null;
		/** @var {BX.Call.VideoStrategy} this.currentCall */
		this.videoStrategy = null;

		this.isHttps = window.location.protocol === "https:";
		this.callWithLegacyMobile = false;

		this.featureScreenSharing = BX.Call.Controller.FeatureState.Enabled;
		this.featureRecord = BX.Call.Controller.FeatureState.Enabled;

		this.callRecordState = BX.Call.View.RecordState.Stopped;
		this.callRecordType = BX.Call.View.RecordType.None;

		this.autoCloseCallView = true;

		this.talkingUsers = {};

		this._callViewState = ViewState.Closed;
		Object.defineProperty(this, 'callViewState', {
			get: function ()
			{
				return this._callViewState
			},
			set: function (callViewState)
			{
				if (this.callViewState == callViewState)
				{
					return;
				}
				this._callViewState = callViewState;
				this.eventEmitter.emit(Events.onViewStateChanged, {
					callViewState: callViewState
				})
			}
		});

		// event handlers
		this._onCallUserInvitedHandler = this._onCallUserInvited.bind(this);
		this._onCallDestroyHandler = this._onCallDestroy.bind(this);
		this._onCallUserStateChangedHandler = this._onCallUserStateChanged.bind(this);
		this._onCallUserMicrophoneStateHandler = this._onCallUserMicrophoneState.bind(this);
		this._onCallUserCameraStateHandler = this._onCallUserCameraState.bind(this);
		this._onCallUserVideoPausedHandler = this._onCallUserVideoPaused.bind(this);
		this._onCallLocalMediaReceivedHandler = this._onCallLocalMediaReceived.bind(this);
		this._onCallLocalMediaStoppedHandler = this._onCallLocalMediaStopped.bind(this);
		this._onCallLocalCameraFlipHandler = this._onCallLocalCameraFlip.bind(this);
		this._onCallLocalCameraFlipInDesktopHandler = this._onCallLocalCameraFlipInDesktop.bind(this);
		this._onCallRemoteMediaReceivedHandler = this._onCallRemoteMediaReceived.bind(this);
		this._onCallRemoteMediaStoppedHandler = this._onCallRemoteMediaStopped.bind(this);
		this._onCallUserVoiceStartedHandler = this._onCallUserVoiceStarted.bind(this);
		this._onCallUserVoiceStoppedHandler = this._onCallUserVoiceStopped.bind(this);
		this._onCallUserScreenStateHandler = this._onCallUserScreenState.bind(this);
		this._onCallUserRecordStateHandler = this._onCallUserRecordState.bind(this);
		this.onCallUserFloorRequestHandler = this._onCallUserFloorRequest.bind(this);
		this._onCallFailureHandler = this._onCallFailure.bind(this);
		this._onNetworkProblemHandler = this._onNetworkProblem.bind(this);
		this._onMicrophoneLevelHandler = this._onMicrophoneLevel.bind(this);
		this._onReconnectingHandler = this._onReconnecting.bind(this);
		this._onReconnectedHandler = this._onReconnected.bind(this);
		this._onCustomMessageHandler = this._onCustomMessage.bind(this);
		this._onCallLeaveHandler = this._onCallLeave.bind(this);
		this._onCallJoinHandler = this._onCallJoin.bind(this);

		this._onBeforeUnloadHandler = this._onBeforeUnload.bind(this);
		this._onImTabChangeHandler = this._onImTabChange.bind(this);
		this._onUpdateChatCounterHandler = this._onUpdateChatCounter.bind(this);

		this._onChildCallFirstMediaHandler = this._onChildCallFirstMedia.bind(this);

		this._onWindowFocusHandler = this._onWindowFocus.bind(this);
		this._onWindowBlurHandler = this._onWindowBlur.bind(this);

		if (BX.desktop && false)
		{
			this.floatingWindow = new BX.Call.FloatingVideo({
				onMainAreaClick: this._onFloatingVideoMainAreaClick.bind(this),
				onButtonClick: this._onFloatingVideoButtonClick.bind(this)
			});
			this.floatingWindowUser = 0;
		}
		this.showFloatingWindowTimeout = 0;
		this.hideIncomingCallTimeout = 0;

		if (BX.desktop)
		{
			var darkMode = !!BX.MessengerTheme.isDark();
			this.floatingScreenShareWindow = new BX.Call.FloatingScreenShare({
				darkMode: darkMode,
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

		this.eventEmitter = new BX.Event.EventEmitter(this, 'BX.Call.Controller');
		this.resizeObserver = new BX.ResizeObserver(this._onResize.bind(this));

		this.init();
	};

	BX.Call.Controller.prototype = {
		init: function ()
		{
			BX.addCustomEvent(window, "CallEvents::incomingCall", this.onIncomingCall.bind(this));
			BX.Call.Hardware.subscribe(BX.Call.Hardware.Events.deviceChanged, this._onDeviceChange.bind(this));
			BX.Call.Hardware.subscribe(BX.Call.Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipHandler);
			if (BX.desktop && this.floatingWindow)
			{
				window.addEventListener("blur", this._onWindowBlurHandler);
				window.addEventListener("focus", this._onWindowFocusHandler);

				BX.desktop.addCustomEvent("BXForegroundChanged", function (focus)
				{
					if (focus)
					{
						this._onWindowFocus();
					}
					else
					{
						this._onWindowBlur();
					}
				}.bind(this));
			}

			if (BX.desktop && this.floatingScreenShareWindow)
			{
				BX.desktop.addCustomEvent("BXScreenMediaSharing", function (id, title, x, y, width, height, app)
				{
					this.floatingScreenShareWindow.close();

					this.floatingScreenShareWindow.setSharingData({
						title: title,
						x: x,
						y: y,
						width: width,
						height: height,
						app: app
					}).then(function ()
					{
						this.floatingScreenShareWindow.show();
					}.bind(this)).catch(function (error)
					{
						console.error('setSharingData error', error);
					}.bind(this));
				}.bind(this));

				window.addEventListener("blur", this._onWindowBlurHandler);
				window.addEventListener("focus", this._onWindowFocusHandler);

				BX.desktop.addCustomEvent("BXForegroundChanged", function (focus)
				{
					if (focus)
					{
						this._onWindowFocus();
					}
					else
					{
						this._onWindowBlur();
					}
				}.bind(this));
			}

			if (BX.desktop)
			{
				BX.desktop.addCustomEvent(BX.Call.Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipInDesktopHandler);
			}

			if (window['VoxImplant'])
			{
				VoxImplant.getInstance().addEventListener(VoxImplant.Events.MicAccessResult, this.voxMicAccessResult.bind(this));
			}

			window.addEventListener("beforeunload", this._onBeforeUnloadHandler);
			BX.addCustomEvent("OnDesktopTabChange", this._onImTabChangeHandler);

			BX.addCustomEvent(window, "onImUpdateCounterMessage", this._onUpdateChatCounter.bind(this));

			BX.garbage(this.destroy, this);
		},

		subscribe: function (eventName, listener)
		{
			return this.eventEmitter.subscribe(eventName, listener);
		},

		unsubscribe: function (eventName, listener)
		{
			return this.eventEmitter.unsubscribe(eventName, listener);
		},

		/**
		 * Workaround to get current microphoneId
		 * @param e
		 */
		voxMicAccessResult: function (e)
		{
			if (e.stream && e.stream.getAudioTracks().length > 0 && this.callView)
			{
				this.callView.microphoneId = e.stream.getAudioTracks()[0].getSettings().deviceId
			}
		},

		getCallUsers: function (includeSelf)
		{
			var result = Object.keys(this.currentCall.getUsers());
			if (includeSelf)
			{
				result.push(this.currentCall.userId);
			}
			return result;
		},

		getActiveCallUsers: function ()
		{
			var activeUsers = [];
			var userStates = this.currentCall.getUsers();

			for (var userId in userStates)
			{
				if (userStates.hasOwnProperty(userId))
				{
					if (userStates[userId] === BX.Call.UserState.Connected || userStates[userId] === BX.Call.UserState.Connecting || userStates[userId] === BX.Call.UserState.Calling)
					{
						activeUsers.push(userId);
					}
				}
			}
			return activeUsers;
		},

		updateFloatingWindowContent: function ()
		{
			if (!this.floatingWindow || !this.currentCall)
			{
				return;
			}
			this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);

			BX.Call.Util.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then(function (result)
			{
				this.floatingWindow.setAvatars(result);
			}.bind(this));
		},

		onIncomingCall: function (e)
		{
			console.warn("incoming.call", e);
			var self = this;
			/** @var {BX.Call.PlainCall|BX.Call.VoximplantCall} newCall */
			var newCall = e.call;
			this.callWithLegacyMobile = (e.isLegacyMobile === true);

			var isCurrentCallActive = this.currentCall && (this.callView || this.callNotification);

			if (!isCurrentCallActive)
			{
				if (this.callView)
				{
					return;
				}

				this.checkDesktop().then(function ()
				{
					return BX.Call.Hardware.init();
				}).then(
					function ()
					{
						if (this.currentCall || newCall.state == BX.Call.State.Finished)
						{
							return;
						}

						this.currentCall = newCall;
						this.bindCallEvents();
						this.updateFloatingWindowContent();

						if (this.currentCall.associatedEntity.type === 'chat' && this.currentCall.associatedEntity.advanced['chatType'] === 'videoconf')
						{
							this.isConferencePageOpened(this.currentCall.associatedEntity.id).then(function (result)
							{
								if (result)
								{
									// conference page is already opened, do nothing
									this.removeCallEvents();
									this.currentCall = null;
								}
								else
								{
									this.showIncomingConference();
								}
							}.bind(this));
						}
						else
						{
							var video = e.video === true;
							if (video && !BX.Call.Hardware.hasCamera())
							{
								this.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
								video = false;
							}

							this.showIncomingCall({
								video: video
							});
						}

					}.bind(this),
					function (error)
					{
						if (self.currentCall)
						{
							self.removeVideoStrategy();
							self.removeCallEvents();
							self.currentCall = null;
						}
						console.error(error);
						self.log(error);
						if (!self.isHttps)
						{
							self.showNotification(BX.message("IM_CALL_INCOMING_ERROR_HTTPS_REQUIRED"))
						}
						else
						{
							self.showNotification(BX.message("IM_CALL_INCOMING_UNSUPPORTED_BROWSER"))
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
					if (!this.childCall)
					{
						this.childCall = newCall;
						this.childCall.users.forEach(function (userId)
						{
							this.callView.addUser(userId, BX.Call.UserState.Calling);
						}, this);
						BX.Call.Util.getUsers(newCall.id, this.childCall.users).then(function (userData)
						{
							this.callView.updateUserData(userData)
						}.bind(this));

						this.answerChildCall();
					}
				}
				else
				{
					// send busy
					newCall.decline(486);
					return false;
				}
			}
		},

		bindCallEvents: function ()
		{
			this.currentCall.addEventListener(BX.Call.Event.onUserInvited, this._onCallUserInvitedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onDestroy, this._onCallDestroyHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserStateChanged, this._onCallUserStateChangedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserMicrophoneState, this._onCallUserMicrophoneStateHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserCameraState, this._onCallUserCameraStateHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserVideoPaused, this._onCallUserVideoPausedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserScreenState, this._onCallUserScreenStateHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserRecordState, this._onCallUserRecordStateHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
			this.currentCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onRemoteMediaReceived, this._onCallRemoteMediaReceivedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onRemoteMediaStopped, this._onCallRemoteMediaStoppedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
			this.currentCall.addEventListener(BX.Call.Event.onNetworkProblem, this._onNetworkProblemHandler);
			this.currentCall.addEventListener(BX.Call.Event.onMicrophoneLevel, this._onMicrophoneLevelHandler);
			this.currentCall.addEventListener(BX.Call.Event.onReconnecting, this._onReconnectingHandler);
			this.currentCall.addEventListener(BX.Call.Event.onReconnected, this._onReconnectedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onCustomMessage, this._onCustomMessageHandler);
			this.currentCall.addEventListener(BX.Call.Event.onJoin, this._onCallJoinHandler);
			this.currentCall.addEventListener(BX.Call.Event.onLeave, this._onCallLeaveHandler);
		},

		removeCallEvents: function ()
		{
			this.currentCall.removeEventListener(BX.Call.Event.onUserInvited, this._onCallUserInvitedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onDestroy, this._onCallDestroyHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserStateChanged, this._onCallUserStateChangedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserMicrophoneState, this._onCallUserMicrophoneStateHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserCameraState, this._onCallUserCameraStateHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserVideoPaused, this._onCallUserVideoPausedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserScreenState, this._onCallUserScreenStateHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserRecordState, this._onCallUserRecordStateHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserFloorRequest, this.onCallUserFloorRequestHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onRemoteMediaReceived, this._onCallRemoteMediaReceivedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onRemoteMediaStopped, this._onCallRemoteMediaStoppedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onNetworkProblem, this._onNetworkProblemHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onMicrophoneLevel, this._onMicrophoneLevelHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onReconnecting, this._onReconnectingHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onReconnected, this._onReconnectedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onCustomMessage, this._onCustomMessageHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onJoin, this._onCallJoinHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onLeave, this._onCallLeaveHandler);
		},

		bindCallViewEvents: function ()
		{
			this.callView.setCallback(BX.Call.View.Event.onShow, this._onCallViewShow.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onClose, this._onCallViewClose.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onDestroy, this._onCallViewDestroy.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onButtonClick, this._onCallViewButtonClick.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onBodyClick, this._onCallViewBodyClick.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onReplaceCamera, this._onCallViewReplaceCamera.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onReplaceMicrophone, this._onCallViewReplaceMicrophone.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onSetCentralUser, this._onCallViewSetCentralUser.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onChangeHdVideo, this._onCallViewChangeHdVideo.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onChangeMicAutoParams, this._onCallViewChangeMicAutoParams.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onChangeFaceImprove, this._onCallViewChangeFaceImprove.bind(this));
			this.callView.setCallback(BX.Call.View.Event.onReplaceSpeaker, this._onCallViewReplaceSpeaker.bind(this));
		},

		createVideoStrategy: function ()
		{
			if (this.videoStrategy)
			{
				this.videoStrategy.destroy();
			}

			var strategyType = window.BXIM.settings.callAcceptIncomingVideo || BX.Call.VideoStrategy.Type.AllowAll;

			this.videoStrategy = new BX.Call.VideoStrategy({
				call: this.currentCall,
				callView: this.callView,
				strategyType: strategyType
			});
		},

		removeVideoStrategy: function ()
		{
			if (this.videoStrategy)
			{
				this.videoStrategy.destroy();
			}
			this.videoStrategy = null;
		},

		setFeatureScreenSharing: function (enable)
		{
			this.featureScreenSharing = enable;
		},

		setFeatureRecord: function (enable)
		{
			this.featureRecord = enable;
		},

		setVideoStrategyType: function (type)
		{
			if (this.videoStrategy)
			{
				this.videoStrategy.setType(type)
			}
		},

		isMessengerOpen: function ()
		{
			return !!this.messenger.popupMessenger;
		},

		createContainer: function ()
		{
			this.container = BX.create("div", {
				props: {className: "bx-messenger-call-overlay"},
			});

			if (BX.MessengerWindow)
			{
				BX.MessengerWindow.content.insertBefore(this.container, BX.MessengerWindow.content.firstChild);
			}
			else
			{
				this.messenger.popupMessengerContent.insertBefore(this.container, this.messenger.popupMessengerContent.firstChild);
			}

			this.messenger.popupMessengerContent.classList.add("bx-messenger-call");
		},

		removeContainer: function ()
		{
			if (this.container)
			{
				BX.remove(this.container);
				this.container = null;
				this.messenger.popupMessengerContent.classList.remove("bx-messenger-call");
			}
		},

		answerChildCall: function ()
		{
			this.removeCallEvents();
			this.removeVideoStrategy();
			this.childCall.addEventListener(BX.Call.Event.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);
			this.childCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);

			this.childCall.answer({
				useVideo: this.currentCall.isVideoEnabled()
			});
		},

		_onChildCallFirstMedia: function (e)
		{
			this.log("Finishing one-to-one call, switching to group call");

			var previousRecordType = BX.Call.View.RecordType.None;
			if (this.isRecording())
			{
				previousRecordType = this.callRecordType;

				BXDesktopSystem.CallRecordStop();
				this.callRecordState = BX.Call.View.RecordState.Stopped;
				this.callRecordType = BX.Call.View.RecordType.None;
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

			this.childCall.removeEventListener(BX.Call.Event.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);

			this.removeCallEvents();
			var oldCall = this.currentCall;
			oldCall.hangup();

			this.currentCall = this.childCall;
			this.childCall = null;

			if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
			{
				this.messenger.openMessenger(this.currentCall.associatedEntity.id);
			}

			if (oldCall.muted)
			{
				this.currentCall.setMuted(true);
			}

			this.bindCallEvents();
			this.createVideoStrategy();

			if (previousRecordType !== BX.Call.View.RecordType.None)
			{
				this._startRecordCall(previousRecordType);
			}
		},

		checkDesktop: function ()
		{
			return new Promise(function (resolve)
			{
				BX.desktopUtils.runningCheck(
					function ()
					{
					},
					function ()
					{
						resolve()
					}
				);
			});
		},

		isConferencePageOpened: function (dialogId)
		{
			return new Promise(function (resolve, reject)
			{
				var tagPresent = BX.Messenger.Lib.LocalStorage.get(BX.CallEngine.getSiteId(), BX.CallEngine.getCurrentUserId(), BX.CallEngine.getConferencePageTag(dialogId), 'N');
				return resolve(tagPresent === 'Y');
			}.bind(this))
		},

		/**
		 * @param {Object} params
		 * @param {bool} [params.video = false]
		 */
		showIncomingCall: function (params)
		{
			if (!BX.type.isPlainObject(params))
			{
				params = {};
			}
			params.video = params.video == true;

			if (this.feedbackPopup)
			{
				this.feedbackPopup.close();
			}

			var allowVideo = this.callWithLegacyMobile ? params.video === true : true;

			this.callNotification = new BX.Call.Notification({
				callerName: this.currentCall.associatedEntity.name,
				callerAvatar: this.currentCall.associatedEntity.avatar,
				callerType: this.currentCall.associatedEntity.advanced.chatType,
				callerColor: this.currentCall.associatedEntity.avatarColor,
				video: params.video,
				hasCamera: BX.Call.Hardware.hasCamera() && allowVideo,
				onClose: this._onCallNotificationClose.bind(this),
				onDestroy: this._onCallNotificationDestroy.bind(this),
				onButtonClick: this._onCallNotificationButtonClick.bind(this)
			});

			this.callNotification.show();
			this.scheduleCancelNotification();

			window.BXIM.repeatSound('ringtone', 3500, true);
		},

		showIncomingConference: function ()
		{
			this.callNotification = new BX.Call.NotificationConference({
				callerName: this.currentCall.associatedEntity.name,
				callerAvatar: this.currentCall.associatedEntity.avatar,
				callerColor: this.currentCall.associatedEntity.avatarColor,
				onClose: this._onCallNotificationClose.bind(this),
				onDestroy: this._onCallNotificationDestroy.bind(this),
				onButtonClick: this._onCallConferenceNotificationButtonClick.bind(this)
			});

			this.callNotification.show();
			this.scheduleCancelNotification();

			window.BXIM.repeatSound('ringtone', 3500, true);
		},

		scheduleCancelNotification: function ()
		{
			clearTimeout(this.hideIncomingCallTimeout);
			this.hideIncomingCallTimeout = setTimeout(function ()
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
			}.bind(this), 30 * 1000);
		},

		showNotification: function (notificationText, actions)
		{
			if (!actions)
			{
				actions = [];
			}
			BX.UI.Notification.Center.notify({
				content: BX.util.htmlspecialchars(notificationText),
				position: "top-right",
				autoHideDelay: 5000,
				closeButton: true,
				actions: actions
			});
		},

		showNetworkProblemNotification: function (notificationText)
		{
			BX.UI.Notification.Center.notify({
				content: BX.util.htmlspecialchars(notificationText),
				position: "top-right",
				autoHideDelay: 5000,
				closeButton: true,
				actions: [{
					title: BX.message("IM_M_CALL_HELP"),
					events: {
						click: function (event, balloon, action)
						{
							top.BX.Helper.show('redirect=detail&code=12723718');
							balloon.close();
						}
					}
				}]
			});
		},

		showUnsupportedNotification: function ()
		{
			if (BX.desktop && BX.desktop.apiReady)
			{
				window.BXIM.openConfirm(
					BX.message('IM_CALL_DESKTOP_TOO_OLD'),
					[
						updateButton = new BX.PopupWindowButton({
							text: BX.message('IM_M_CALL_BTN_UPDATE'),
							className: "popup-window-button-accept",
							events: {
								click: function ()
								{
									window.open(BX.browser.IsMac() ? "http://dl.bitrix24.com/b24/bitrix24_desktop.dmg" : "http://dl.bitrix24.com/b24/bitrix24_desktop.exe", "desktopApp");
									this.popupWindow.close();
								}
							}
						}),
						new BX.PopupWindowButton({
							text: BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
							className: "popup-window-button-decline",
							events: {
								click: function ()
								{
									this.popupWindow.close();
								}
							}
						})
					]
				);
			}
			else
			{
				window.BXIM.openConfirm(
					BX.message("IM_CALL_WEBRTC_USE_CHROME_OR_DESKTOP"),
					[
						new BX.PopupWindowButton({
							text: BX.message("IM_CALL_DETAILS"),
							className: "popup-window-button-accept",
							events: {
								click: function ()
								{
									this.popupWindow.close();
									//https://helpdesk.bitrix24.ru/open/11387752/
									top.BX.Helper.show("redirect=detail&code=11387752");
								}
							}
						}),
						new BX.PopupWindowButton({
							text: BX.message('IM_NOTIFY_CONFIRM_CLOSE'),
							className: "popup-window-button-decline",
							events: {
								click: function ()
								{
									this.popupWindow.close();
								}
							}
						}),
					]
				);
			}
		},

		isUserAgentSupported: function ()
		{
			if (BX.desktop && BX.desktop.apiReady)
			{
				return BX.desktop.enableInVersion(48);
			}
			if ('VoxImplant' in window)
			{
				return VoxImplant.getInstance().isRTCsupported();
			}
			return BX.Call.Util.isWebRTCSupported();
		},

		startCall: function (dialogId, video)
		{
			if (!this.isUserAgentSupported())
			{
				this.showUnsupportedNotification();
				return;
			}

			if (this.callView || this.currentCall)
			{
				return;
			}

			if (this.feedbackPopup)
			{
				this.feedbackPopup.close();
			}

			var provider = BX.Call.Provider.Plain;
			if (BX.Call.Util.isCallServerAllowed() && dialogId.toString().substr(0, 4) === "chat")
			{
				provider = BX.Call.Provider.Voximplant;
			}

			var debug1 = +(new Date());
			this._openMessenger(dialogId).then(function ()
			{
				return BX.Call.Hardware.init();
			}.bind(this)).then(function ()
			{
				this.createContainer();
				var hiddenButtons = [];
				if (provider === BX.Call.Provider.Plain)
				{
					hiddenButtons.push('floorRequest');
				}
				if (!BX.Call.Util.shouldShowDocumentButton())
				{
					hiddenButtons.push('document');
				}

				this.callView = new BX.Call.View({
					container: this.container,
					showChatButtons: true,
					userLimit: BX.Call.Util.getUserLimit(),
					language: window.BXIM.language,
					layout: dialogId.toString().startsWith("chat") ? BX.Call.View.Layout.Grid : BX.Call.View.Layout.Centered,
					microphoneId: BX.Call.Hardware.defaultMicrophone,
					showShareButton: this.featureScreenSharing !== BX.Call.Controller.FeatureState.Disabled,
					showRecordButton: this.featureRecord !== BX.Call.Controller.FeatureState.Disabled,
					hiddenButtons: hiddenButtons,
					blockedButtons: ['record'],
				});
				this.bindCallViewEvents();

				if (video && !BX.Call.Hardware.hasCamera())
				{
					this.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
					video = false;
				}

				return BX.Call.Engine.getInstance().createCall({
					entityType: 'chat',
					entityId: dialogId,
					provider: provider,
					videoEnabled: !!video,
					enableMicAutoParameters: BX.Call.Hardware.enableMicAutoParameters,
					joinExisting: true
				});
			}.bind(this)).then(function (e)
			{
				var debug2 = +(new Date());
				this.currentCall = e.call;

				this.log("Call creation time: " + (debug2 - debug1) / 1000 + " seconds");

				this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);
				if (BX.Call.Hardware.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
				}
				if (BX.Call.Hardware.defaultCamera)
				{
					this.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
				}

				if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
				{
					if (this.messenger.currentTab != this.currentCall.associatedEntity.id)
					{
						this.messenger.openMessenger(this.currentCall.associatedEntity.id);
					}
				}

				this.autoCloseCallView = true;
				this.bindCallEvents();
				this.createVideoStrategy();

				this.callView.appendUsers(this.currentCall.getUsers());
				BX.Call.Util.getUsers(this.currentCall.id, this.getCallUsers(true)).then(function (userData)
				{
					this.callView.updateUserData(userData)
				}.bind(this));
				this.callView.show();
				this.showDocumentPromo();

				if (e.isNew)
				{
					this.log("Inviting users");
					this.currentCall.inviteUsers();
					window.BXIM.repeatSound('dialtone', 5000, true);
				}
				else
				{
					this.log("Joining existing call");
					this.currentCall.answer({
						useVideo: video
					});
				}
			}.bind(this)).catch(function (error)
			{
				console.error(error);

				var errorCode;
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

			}.bind(this));
		},

		joinCall: function (callId, video, options)
		{
			var joinAsViewer = BX.prop.getBoolean(options, "joinAsViewer", false);

			if (!this.isUserAgentSupported())
			{
				this.showUnsupportedNotification();
				return;
			}

			if (this.callView || this.currentCall)
			{
				return;
			}

			var isGroupCall;

			this.log("Joining call " + callId);
			BX.CallEngine.getCallWithId(callId).then(function (result)
			{
				this.currentCall = result.call;
				isGroupCall = this.currentCall.associatedEntity.id.toString().startsWith("chat");
				return this._openMessenger();
			}.bind(this)).then(function ()
			{
				return BX.Call.Hardware.init();
			}.bind(this)).then(function ()
			{
				this.createContainer();

				var hiddenButtons = [];
				if (this.currentCall instanceof BX.Call.PlainCall)
				{
					hiddenButtons.push('floorRequest');
				}
				if (!BX.Call.Util.shouldShowDocumentButton())
				{
					hiddenButtons.push('document');
				}

				this.callView = new BX.Call.View({
					container: this.container,
					showChatButtons: true,
					userLimit: BX.Call.Util.getUserLimit(),
					language: window.BXIM.language,
					layout: isGroupCall ? BX.Call.View.Layout.Grid : BX.Call.View.Layout.Centered,
					showRecordButton: this.featureRecord !== BX.Call.Controller.FeatureState.Disabled,
					microphoneId: BX.Call.Hardware.defaultMicrophone,
					hiddenButtons: hiddenButtons,
					blockedButtons: ['record'],
				});
				this.autoCloseCallView = true;
				this.bindCallViewEvents();
				this.callView.appendUsers(this.currentCall.getUsers());
				BX.Call.Util.getUsers(this.currentCall.id, this.getCallUsers(true)).then(function (userData)
				{
					this.callView.updateUserData(userData)
				}.bind(this));
				this.callView.show();
				this.showDocumentPromo();

				this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);
				if (BX.Call.Hardware.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
				}
				if (BX.Call.Hardware.defaultCamera)
				{
					this.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
				}

				this.bindCallEvents();
				this.createVideoStrategy();

				if (video && !BX.Call.Hardware.hasCamera())
				{
					this.showNotification(BX.message('IM_CALL_ERROR_NO_CAMERA'));
					video = false;
				}

				if (this.getCallUsers(true).length > this.getMaxActiveMicrophonesCount())
				{
					this.currentCall.setMuted(true);
					this.callView.setMuted(true);
					this.showAutoMicMuteNotification();
				}

				this.currentCall.answer({
					useVideo: !!video,
					joinAsViewer: joinAsViewer
				});
			}.bind(this));
		},

		leaveCurrentCall: function ()
		{
			if (this.callView)
			{
				this.callView.releaseLocalMedia();
			}

			if (this.currentCall)
			{
				this.currentCall.hangup();
			}

			if (this.callView)
			{
				this.callView.close();
			}
		},

		hasActiveCall: function ()
		{
			return (this.currentCall != null && this.currentCall.isAnyoneParticipating()) || (this.callView != null);
		},

		hasVisibleCall: function ()
		{
			return !!(this.callView && this.callView.visible && this.callView.size == BX.Call.View.Size.Full);
		},

		canRecord: function ()
		{
			return BX.desktop && BX.desktop.getApiVersion() >= 54;
		},

		isRecording: function ()
		{
			return this.canRecord() && this.callRecordState != BX.Call.View.RecordState.Stopped;
		},

		useDevicesInCurrentCall: function (deviceList)
		{
			if (!this.currentCall || !this.currentCall.ready)
			{
				return;
			}

			for (var i = 0; i < deviceList.length; i++)
			{
				var deviceInfo = deviceList[i];

				switch (deviceInfo.kind)
				{
					case "audioinput":
						this.currentCall.setMicrophoneId(deviceInfo.deviceId);
						break;
					case "videoinput":
						this.currentCall.setCameraId(deviceInfo.deviceId);
						break;
					case "audiooutput":
						if (this.callView)
						{
							this.callView.setSpeakerId(deviceInfo.deviceId);
						}
						break;
				}
			}
		},

		removeDevicesFromCurrentCall: function (deviceList)
		{
			if (!this.currentCall || !this.currentCall.ready)
			{
				return;
			}

			for (var i = 0; i < deviceList.length; i++)
			{
				var deviceInfo = deviceList[i];

				switch (deviceInfo.kind)
				{
					case "audioinput":
						if (this.currentCall.microphoneId == deviceInfo.deviceId)
						{
							var microphoneIds = Object.keys(BX.Call.Hardware.microphoneList);
							this.currentCall.setMicrophoneId(microphoneIds.length > 0 ? microphoneIds[0] : "");
						}
						break;
					case "videoinput":
						if (this.currentCall.cameraId == deviceInfo.deviceId)
						{
							var cameraIds = Object.keys(BX.Call.Hardware.cameraList);
							this.currentCall.setCameraId(cameraIds.length > 0 ? cameraIds[0] : "");
						}
						break;
					case "audiooutput":
						if (this.callView && this.callView.speakerId == deviceInfo.deviceId)
						{
							var speakerIds = Object.keys(BX.Call.Hardware.audioOutputList);
							this.callView.setSpeakerId(speakerIds.length > 0 ? speakerIds[0] : "");
						}
						break;
				}
			}
		},

		showChat: function ()
		{
			if (BX.desktop && this.floatingWindow)
			{
				this.detached = true;
				this.callView.hide();
				this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);
				BX.Call.Util.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then(function (result)
				{
					this.floatingWindow.setAvatars(result);
					this.floatingWindow.show();
				}.bind(this));

				this.container.style.width = 0;
			}
			else
			{
				this.fold(BX.util.htmlspecialcharsback(this.currentCall.associatedEntity.name));
			}
		},

		fold: function (foldedCallTitle)
		{
			if (this.folded || (BX.desktop && this.floatingWindow))
			{
				return;
			}
			if (!foldedCallTitle && this.currentCall)
			{
				foldedCallTitle = BX.util.htmlspecialcharsback(this.currentCall.associatedEntity.name)
			}

			this.folded = true;
			this.resizeObserver.unobserve(this.container);
			this.container.classList.add('bx-messenger-call-overlay-folded');
			this.callView.setTitle(foldedCallTitle);
			this.callView.setSize(BX.Call.View.Size.Folded);
			this.callViewState = ViewState.Folded;
			if (this.sidebar)
			{
				this.sidebar.toggleHidden(true);
			}
			if (this.documentPromoPopup)
			{
				this.documentPromoPopup.close();
			}

			BX.onCustomEvent(this, "CallController::onFold", {});
		},

		setCallEditorMaxWidth: function (maxWidth)
		{
			if (maxWidth != this.maxEditorWidth)
			{
				this.maxEditorWidth = maxWidth;
				this._onResize();
			}
		},

		findCallEditorWidth: function ()
		{
			var containerWidth = this.container.clientWidth;
			var editorWidth = containerWidth < (this.maxEditorWidth + BX.Call.View.MIN_WIDTH) ? containerWidth - BX.Call.View.MIN_WIDTH : this.maxEditorWidth;
			var callWidth = containerWidth - editorWidth;

			return {callWidth: callWidth, editorWidth: editorWidth};
		},

		showDocumentsMenu: function ()
		{
			var targetNodeWidth = this.callView.buttons.document.elements.root.offsetWidth;
			var resumesArticleCode = BX.Call.Util.getResumesArticleCode();
			var documentsArticleCode = BX.Call.Util.getDocumentsArticleCode();

			var menuItems = [
				{
					text: BX.message('IM_M_CALL_MENU_CREATE_RESUME'),
					onclick: function (event, item)
					{
						this.documentsMenu.close();
						this.maybeShowDocumentEditor({
							type: DocumentType.Resume,
						}, resumesArticleCode);
					}.bind(this)
				},
				{
					text: BX.message('IM_M_CALL_MENU_CREATE_FILE'),
					items: [
						{
							text: BX.message('IM_M_CALL_MENU_CREATE_FILE_DOC'),
							onclick: function (event, item)
							{
								this.documentsMenu.close();
								this.maybeShowDocumentEditor({
									type: DocumentType.Blank,
									typeFile: FILE_TYPE_DOCX,
								}, documentsArticleCode);
							}.bind(this)
						},
						{
							text: BX.message('IM_M_CALL_MENU_CREATE_FILE_XLS'),
							onclick: function (event, item)
							{
								this.documentsMenu.close();
								this.maybeShowDocumentEditor({
									type: DocumentType.Blank,
									typeFile: FILE_TYPE_XLSX,
								}, documentsArticleCode);
							}.bind(this)
						},
						{
							text: BX.message('IM_M_CALL_MENU_CREATE_FILE_PPT'),
							onclick: function (event, item)
							{
								this.documentsMenu.close();
								this.maybeShowDocumentEditor({
									type: DocumentType.Blank,
									typeFile: FILE_TYPE_PPTX,
								}, documentsArticleCode);
							}.bind(this)
						},
					],
				},
			];

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
						onSubMenuShow: function(e)
						{
							this.buildPreviousResumesSubmenu(e.target)
						}.bind(this)
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
					onShow: function (event)
					{
						var popup = event.getTarget();
						popup.getPopupContainer().style.display = 'block'; // bad hack

						var offsetLeft = (targetNodeWidth / 2) - popup.getPopupContainer().offsetWidth / 2;
						popup.setOffset({offsetLeft: offsetLeft + 40});
						popup.setAngle({offset: popup.getPopupContainer().offsetWidth / 2 - 17});
					},
					onDestroy: function()
					{
						this.documentsMenu = null
					}.bind(this)
				},
				items: menuItems,
			});

			this.documentsMenu.show();
		},

		buildPreviousResumesSubmenu: function(menuItem)
		{
			BX.ajax.runAction('disk.api.integration.messengerCall.listResumesInChatByCall', {
				data: {
					callId: this.currentCall.id
				}
			}).then(function (response)
			{
				var resumeList =  response.data.resumes;

				if (resumeList.length > 0)
				{
					resumeList.forEach(function (resume)
					{
						menuItem.getSubMenu().addMenuItem({
							id: resume.id,
							text: resume.object.createDate + ': ' + resume.object.name,
							onclick: function (event, item)
							{
								this.documentsMenu.close();
								this.viewDocumentByLink(resume.links.view);
							}.bind(this)
						});
					}.bind(this))
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
			}.bind(this))
		},

		maybeShowDocumentEditor: function(options, articleCode)
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
		},

		showDocumentEditor: function (options)
		{
			options = options || {};
			var openAnimation = true;
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

			BX.loadExt('disk.onlyoffice-im-integration').then(function ()
			{
				var docEditor = new BX.Disk.OnlyOfficeImIntegration.CreateDocument({
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

				var promiseGetUrl;
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

				promiseGetUrl.then(function (url)
				{
					this.docEditorIframe.src = url;
				}.bind(this)).catch(function (e)
				{
					console.error(e)
					this.closeDocumentEditor()
					alert(BX.message("IM_F_ERROR"));
				}.bind(this));
				this.docEditor = docEditor;
			}.bind(this)).catch(function (error)
			{
				console.error(error);
				this.closeDocumentEditor()
				alert(BX.message("IM_F_ERROR"));
			}.bind(this));

			this.resizeObserver.observe(this.container);
		},

		closeDocumentEditor: function ()
		{
			return new Promise(function (resolve)
			{
				if (this.docEditor && this.docEditorIframe)
				{
					this.docEditor.onCloseIframe(this.docEditorIframe);
				}
				if (this.container)
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
				var oldSidebar = this.sidebar;
				this.sidebar = null;
				oldSidebar.close().then(function ()
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
				}.bind(this));
			}.bind(this));
		},

		viewDocumentByLink: function(url)
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
		},

		_createAndOpenSidebarWithIframe: function (url, animation)
		{
			animation = animation != false;
			var result = this.findCallEditorWidth();
			var callWidth = result.callWidth;
			var editorWidth = result.editorWidth;

			this.callView.setMaxWidth(callWidth);
			this.sidebar = new BX.Call.Sidebar({
				container: this.container,
				width: editorWidth,
				events: {
					onCloseClicked: this.onSideBarCloseClicked.bind(this)
				}
			});
			this.sidebar.open(animation);

			var loader = new BX.Loader({
				target: this.sidebar.elements.contentContainer
			});
			loader.show();

			var docEditorIframe = BX.create("iframe", {
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

			docEditorIframe.addEventListener('load', function ()
			{
				loader.destroy();
				docEditorIframe.style.display = 'block';
			}, {
				once: true
			});
			docEditorIframe.addEventListener('error', function (error)
			{
				console.error(error);
				this.closeDocumentEditor()
				alert(BX.message("IM_F_ERROR"));
			}.bind(this))
			this.sidebar.elements.contentContainer.appendChild(docEditorIframe);
			this.docEditorIframe = docEditorIframe;
		},

		_onDocumentCreated: function ()
		{
			this.docCreatedForCurrentCall = true;
			if (this.currentCall)
			{
				this.currentCall.sendCustomMessage(DOC_CREATED_EVENT, true)
			}
		},

		onSideBarCloseClicked: function ()
		{
			this.closeDocumentEditor();
		},

		_ensureDocumentEditorClosed: function ()
		{
			return new Promise(function (resolve, reject)
			{
				if (!this.sidebar)
				{
					return resolve();
				}

				var self = this;

				window.BXIM.openConfirm(
					BX.message("IM_CALL_CLOSE_DOCUMENT_EDITOR_TO_ANSWER"),
					[
						new BX.PopupWindowButton({
							text: BX.message("IM_CALL_CLOSE_DOCUMENT_EDITOR_YES"),
							className: "popup-window-button-accept",
							events: {
								click: function ()
								{
									// we can't bind this function to have access to this.popupWindow
									this.popupWindow.close();
									self.closeDocumentEditor().then(function ()
									{
										resolve();
									})
								}
							}
						}),
						new BX.PopupWindowButton({
							text: BX.message('IM_CALL_CLOSE_DOCUMENT_EDITOR_NO'),
							className: "popup-window-button-decline",
							events: {
								click: function ()
								{
									this.popupWindow.close();
									reject();
								}
							}
						}),
					],
					true,
					{maxWidth: 600}
				);

			}.bind(this))
		},

		onDocumentPromoActionClicked: function ()
		{
			if (this.documentPromoPopup)
			{
				this.documentPromoPopup.close();
			}

			var articleCode = BX.Call.Util.getResumesArticleCode();
			if (articleCode)
			{
				BX.UI.InfoHelper.show(articleCode); //@see \Bitrix\Disk\Integration\MessengerCall::getInfoHelperCodeForDocuments()
				return;
			}

			this.showDocumentEditor({
				type: DocumentType.Resume,
			});
		},

		onDocumentPromoClosed: function()
		{
			this.documentPromoPopup = null;
		},

		unfold: function ()
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
				this.callView.setSize(BX.Call.View.Size.Full);
				this.callViewState = ViewState.Opened;
				if (this.sidebar)
				{
					this.sidebar.toggleHidden(false);
					this.resizeObserver.observe(this.container);
				}
			}
			BX.onCustomEvent(this, "CallController::onUnfold", {});
		},

		isFullScreen: function ()
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
		},

		toggleFullScreen: function ()
		{
			if (this.isFullScreen())
			{
				this.exitFullScreen();
			}
			else
			{
				this.enterFullScreen();
			}
		},

		enterFullScreen: function ()
		{
			if (BX.MessengerSlider && BX.MessengerSlider.isFocus() && BX.getClass("BX.SidePanel.Instance.enterFullScreen"))
			{
				BX.SidePanel.Instance.enterFullScreen();
			}
			else
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
		},

		exitFullScreen: function ()
		{
			if (BX.MessengerSlider && BX.MessengerSlider.isFocus() && BX.getClass("BX.SidePanel.Instance.exitFullScreen"))
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
		},

		showDocumentPromo: function()
		{
			if (!this.callView || !this.currentCall || !BX.Call.Util.shouldShowDocumentButton())
			{
				return false;
			}

			if (!BX.MessengerPromo || !BX.MessengerPromo.needToShow(DOCUMENT_PROMO_CODE))
			{
				return false;
			}

			var documentButton = this.callView.buttons.document.elements.root;
			var bindElement = documentButton.querySelector('.bx-messenger-videocall-panel-icon');
			if (!bindElement)
			{
				return false;
			}
			this.documentPromoPopup = new BX.Call.PromoPopup({
				bindElement: bindElement,
				promoCode: DOCUMENT_PROMO_CODE,
				events: {
					onActionClick: this.onDocumentPromoActionClicked.bind(this),
					onClose: this.onDocumentPromoClosed.bind(this)
				}
			});
			this.showPromoPopupTimeout = setTimeout(function ()
			{
				if (this.folded)
				{
					return false;
				}
				this.documentPromoPopup.show();
			}.bind(this), DOCUMENT_PROMO_DELAY);
		},

		// converter from BX.Promise to normal Promise
		_openMessenger: function (dialogId)
		{
			return new Promise(function (resolve, reject)
			{
				this.messenger.openMessenger(dialogId).then(function ()
				{
					resolve();
				}).catch(function (e)
				{
					reject(e);
				})
			}.bind(this))
		},

		_startRecordCall: function (type)
		{
			this.callView.setButtonActive('record', true);
			this.callRecordType = type;

			this.currentCall.sendRecordState({
				action: BX.Call.View.RecordState.Started,
				date: new Date()
			});

			this.callRecordState = BX.Call.View.RecordState.Started;
		},

		// event handlers

		_onCallNotificationClose: function (e)
		{
			clearTimeout(this.hideIncomingCallTimeout);
			window.BXIM.stopRepeatSound('ringtone');
			if (this.callNotification)
			{
				this.callNotification.destroy();
			}
		},

		_onCallNotificationDestroy: function (e)
		{
			this.callNotification = null;
		},

		_onCallNotificationButtonClick: function (e)
		{
			clearTimeout(this.hideIncomingCallTimeout);
			this.callNotification.close();
			switch (e.button)
			{
				case "answer":
					this._onAnswerButtonClick(e.video);
					break;
				case "decline":
					if (this.currentCall)
					{
						this.removeVideoStrategy();
						this.removeCallEvents();
						this.currentCall.decline();
						this.currentCall = null;
					}
					break;
			}
		},

		_onAnswerButtonClick: function (withVideo)
		{
			if (BX.desktop)
			{
				BX.desktop.windowCommand("show");
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

			var dialogId = this.currentCall.associatedEntity && this.currentCall.associatedEntity.id ? this.currentCall.associatedEntity.id : false;
			var isGroupCall = dialogId.toString().startsWith("chat");
			this._ensureDocumentEditorClosed().then(function ()
			{
				return this._openMessenger(dialogId);
			}.bind(this)).then(function ()
			{
				this.createContainer();
				var hiddenButtons = [];
				if (this.currentCall instanceof BX.Call.PlainCall)
				{
					hiddenButtons.push('floorRequest');
				}
				if (!BX.Call.Util.shouldShowDocumentButton())
				{
					hiddenButtons.push('document');
				}
				this.callView = new BX.Call.View({
					container: this.container,
					users: this.currentCall.users,
					userStates: this.currentCall.getUsers(),
					showChatButtons: true,
					showRecordButton: this.featureRecord !== BX.Call.Controller.FeatureState.Disabled,
					userLimit: BX.Call.Util.getUserLimit(),
					layout: isGroupCall ? BX.Call.View.Layout.Grid : BX.Call.View.Layout.Centered,
					microphoneId: BX.Call.Hardware.defaultMicrophone,
					blockedButtons: ['record'],
					hiddenButtons: hiddenButtons,
				});
				this.autoCloseCallView = true;
				if (this.callWithLegacyMobile)
				{
					this.callView.blockAddUser();
				}

				this.bindCallViewEvents();
				BX.Call.Util.getUsers(this.currentCall.id, this.getCallUsers(true)).then(function (userData)
				{
					this.callView.updateUserData(userData)
				}.bind(this));
				this.callView.show();
				this.showDocumentPromo();

				this.currentCall.useHdVideo(BX.Call.Hardware.preferHdQuality);
				if (BX.Call.Hardware.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(BX.Call.Hardware.defaultMicrophone);
				}
				if (BX.Call.Hardware.defaultCamera)
				{
					this.currentCall.setCameraId(BX.Call.Hardware.defaultCamera);
				}

				if (this.getCallUsers(true).length > this.getMaxActiveMicrophonesCount())
				{
					this.currentCall.setMuted(true);
					this.callView.setMuted(true);
					this.showAutoMicMuteNotification();
				}

				this.currentCall.answer({
					useVideo: withVideo,
					enableMicAutoParameters: BX.Call.Hardware.enableMicAutoParameters
				});

				this.createVideoStrategy();

			}.bind(this));
		},

		_onCallConferenceNotificationButtonClick: function (e)
		{
			clearTimeout(this.hideIncomingCallTimeout);
			this.callNotification.close();
			switch (e.button)
			{
				case "answerConference":
					if (this.currentCall && 'id' in this.currentCall.associatedEntity)
					{
						var dialogId = this.currentCall.associatedEntity.id.toString();
						if (dialogId.startsWith('chat'))
						{
							dialogId = dialogId.substr(4);
						}
						if (window.BXIM.messenger.chat.hasOwnProperty(dialogId))
						{
							window.BXIM.openVideoconf(window.BXIM.messenger.chat[dialogId].public.code);
						}
					}
					break;
				case "skipConference":
					if (this.currentCall)
					{
						this.removeVideoStrategy();
						this.removeCallEvents();
						this.currentCall.decline();
						this.currentCall = null;
					}
					break;
			}
		},

		_onCallViewShow: function (e)
		{
			this.callView.setButtonCounter("chat", BXIM.messenger.messageCount);
			this.callViewState = ViewState.Opened;
		},

		_onCallViewClose: function (e)
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
			if (this.documentPromoPopup)
			{
				this.documentPromoPopup.close();
			}
			if (this.documentsMenu)
			{
				this.documentsMenu.close();
			}
			clearTimeout(this.showPromoPopupTimeout);
			this._closeReconnectionBaloon();
		},

		_onCallViewDestroy: function (e)
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
		},

		_onCallViewBodyClick: function (e)
		{
			if (this.folded)
			{
				this.unfold();
			}
		},

		_onCallViewButtonClick: function (e)
		{
			var buttonName = e.buttonName;

			var handlers = {
				hangup: this._onCallViewHangupButtonClick.bind(this),
				close: this._onCallViewCloseButtonClick.bind(this),
				inviteUser: this._onCallViewInviteUserButtonClick.bind(this),
				toggleMute: this._onCallViewToggleMuteButtonClick.bind(this),
				toggleScreenSharing: this._onCallViewToggleScreenSharingButtonClick.bind(this),
				record: this._onCallViewRecordButtonClick.bind(this),
				toggleVideo: this._onCallViewToggleVideoButtonClick.bind(this),
				showChat: this._onCallViewShowChatButtonClick.bind(this),
				floorRequest: this._onCallViewFloorRequestButtonClick.bind(this),
				showHistory: this._onCallViewShowHistoryButtonClick.bind(this),
				fullscreen: this._onCallViewFullScreenButtonClick.bind(this),
				document: this._onCallViewDocumentButtonClick.bind(this),
			};

			if (BX.type.isFunction(handlers[buttonName]))
			{
				handlers[buttonName].call(this, e);
			}
		},

		_onCallViewHangupButtonClick: function (e)
		{
			this.leaveCurrentCall();
		},

		_onCallViewCloseButtonClick: function (e)
		{
			if (this.callView)
			{
				this.callView.close();
			}
		},

		_onCallViewShowChatButtonClick: function (e)
		{
			this.messenger.openMessenger(this.currentCall.associatedEntity.id);
			this.showChat();
		},

		_onCallViewFloorRequestButtonClick: function (e)
		{
			var floorState = this.callView.getUserFloorRequestState(BX.CallEngine.getCurrentUserId());
			var talkingState = this.callView.getUserTalking(BX.CallEngine.getCurrentUserId());

			this.callView.setUserFloorRequestState(BX.CallEngine.getCurrentUserId(), !floorState);

			if (this.currentCall)
			{
				this.currentCall.requestFloor(!floorState);
			}

			clearTimeout(this.callViewFloorRequestTimeout);
			if (talkingState && !floorState)
			{
				this.callViewFloorRequestTimeout = setTimeout(function ()
				{
					if (this.currentCall)
					{
						this.currentCall.requestFloor(false);
					}
				}.bind(this), 1500);
			}
		},

		/**
		 * Returns list of users, that are not currently connected
		 * @return {Array}
		 * @private
		 */
		_getDisconnectedUsers: function ()
		{
			var result = [];
			var userStates = this.currentCall.getUsers();

			for (var userId in userStates)
			{
				if (userStates[userId] !== BX.Call.UserState.Connected && BX.Call.Util.userData[userId])
				{
					result.push(BX.Call.Util.userData[userId]);
				}
			}

			return result;
		},

		_closeReconnectionBaloon: function ()
		{
			if (this.reconnectionBaloon)
			{
				this.reconnectionBaloon.close();
				this.reconnectionBaloon = null;
			}
		},

		_onCallViewInviteUserButtonClick: function (e)
		{
			if (this.invitePopup)
			{
				this.invitePopup.close();
				return;
			}

			var userStates = this.currentCall.getUsers();
			var idleUsers = this._getDisconnectedUsers();

			this.invitePopup = new BX.Call.InvitePopup({
				viewElement: this.callView.container,
				bindElement: e.node,
				zIndex: BX.MessengerCommon.getDefaultZIndex() + 200,
				idleUsers: idleUsers,
				allowNewUsers: Object.keys(userStates).length < BX.Call.Util.getUserLimit() - 1,
				onDestroy: this._onInvitePopupDestroy.bind(this),
				onSelect: this._onInvitePopupSelect.bind(this)
			});

			this.callView.setHotKeyTemporaryBlock(true);
			this.invitePopup.show();
		},

		_onCallViewToggleMuteButtonClick: function (e)
		{
			this.currentCall.setMuted(e.muted);
			this.callView.setMuted(e.muted);

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
		},

		_onCallViewRecordButtonClick: function (event)
		{
			if (event.recordState === BX.Call.View.RecordState.Started)
			{
				if (this.featureRecord === BX.Call.Controller.FeatureState.Limited)
				{
					BX.MessengerLimit.showHelpSlider('call_record');
					return;
				}

				if (this.featureRecord === BX.Call.Controller.FeatureState.Disabled)
				{
					return;
				}

				if (this.canRecord())
				{
					var forceRecord = BX.prop.getBoolean(event, "forceRecord", BX.Call.View.RecordType.None);
					if (forceRecord !== BX.Call.View.RecordType.None)
					{
						this._startRecordCall(forceRecord);
					}
					else if (BX.desktop && BX.desktop.enableInVersion(55))
					{
						if (!this.callRecordMenu)
						{
							this.callRecordMenu = new BX.PopupMenuWindow({
								bindElement: event.node,
								targetContainer: this.callView.container,
								items: [
									{
										text: BX.message('IM_M_CALL_MENU_RECORD_VIDEO'),
										onclick: function (event, item)
										{
											this._startRecordCall(BX.Call.View.RecordType.Video);
											item.getMenuWindow().close();
										}.bind(this)
									},
									{
										text: BX.message('IM_M_CALL_MENU_RECORD_AUDIO'),
										onclick: function (event, item)
										{
											this._startRecordCall(BX.Call.View.RecordType.Audio);
											item.getMenuWindow().close();
										}.bind(this)
									}
								],
								autoHide: true,
								angle: {position: "top", offset: 80},
								offsetTop: 0,
								offsetLeft: -25,
								events: {
									onPopupClose: function ()
									{
										this.callRecordMenu.destroy();
									}.bind(this),
									onPopupDestroy: function ()
									{
										this.callRecordMenu = null;
									}.bind(this),
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
			else if (event.recordState === BX.Call.View.RecordState.Paused)
			{
				if (this.canRecord())
				{
					BXDesktopSystem.CallRecordPause(true);
				}
			}
			else if (event.recordState === BX.Call.View.RecordState.Resumed)
			{
				if (this.canRecord())
				{
					BXDesktopSystem.CallRecordPause(false);
				}
			}
			else if (event.recordState === BX.Call.View.RecordState.Stopped)
			{
				this.callView.setButtonActive('record', false);
			}

			this.currentCall.sendRecordState({
				action: event.recordState,
				date: new Date()
			});

			this.callRecordState = event.recordState;
		},

		_onCallViewToggleScreenSharingButtonClick: function (e)
		{
			if (this.featureScreenSharing === BX.Call.Controller.FeatureState.Limited)
			{
				BX.MessengerLimit.showHelpSlider('call_screen_sharing');
				return;
			}

			if (this.featureScreenSharing === BX.Call.Controller.FeatureState.Disabled)
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
				BX.CallEngine.getRestClient().callMethod("im.call.onShareScreen", {callId: this.currentCall.id});
			}
		},

		_onCallViewToggleVideoButtonClick: function (e)
		{
			if (!BX.Call.Hardware.initialized)
			{
				return;
			}
			if (e.video && Object.values(BX.Call.Hardware.cameraList).length === 0)
			{
				return;
			}
			if (!e.video)
			{
				this.callView.releaseLocalMedia();
			}
			this.currentCall.setVideoEnabled(e.video);
		},

		_onCallViewShowHistoryButtonClick: function (e)
		{
			this.messenger.openHistory(this.currentCall.associatedEntity.id);
		},

		_onCallViewFullScreenButtonClick: function (e)
		{
			if (this.folded)
			{
				this.unfold();
			}
			this.toggleFullScreen();
		},

		_onCallViewDocumentButtonClick: function (e)
		{
			this.sidebar ? this.closeDocumentEditor() : this.showDocumentsMenu();
		},

		_onCallViewReplaceCamera: function (e)
		{
			if (this.currentCall)
			{
				this.currentCall.setCameraId(e.deviceId);
			}

			// update default camera
			BX.Call.Hardware.defaultCamera = e.deviceId;
		},

		_onCallViewReplaceMicrophone: function (e)
		{
			if (this.currentCall)
			{
				this.currentCall.setMicrophoneId(e.deviceId)
			}

			if (this.currentCall instanceof BX.Call.VoximplantCall)
			{
				this.callView.setMicrophoneId(e.deviceId);
			}

			// update default microphone
			BX.Call.Hardware.defaultMicrophone = e.deviceId;
		},

		_onCallViewReplaceSpeaker: function (e)
		{
			BX.Call.Hardware.defaultSpeaker = e.deviceId;
		},

		_onCallViewChangeHdVideo: function (e)
		{
			BX.Call.Hardware.preferHdQuality = e.allowHdVideo;
		},

		_onCallViewChangeMicAutoParams: function (e)
		{
			BX.Call.Hardware.enableMicAutoParameters = e.allowMicAutoParams;
		},

		_onCallViewChangeFaceImprove: function (e)
		{
			if (typeof (BX.desktop) === 'undefined')
			{
				return;
			}

			BX.desktop.cameraSmoothingStatus(e.faceImproveEnabled);
		},

		_onCallViewSetCentralUser: function (e)
		{
			if (e.stream && this.floatingWindow)
			{
				this.floatingWindowUser = e.userId;
				//this.floatingWindow.setStream(e.stream);
			}
		},

		_onCallUserInvited: function (e)
		{
			if (this.callView)
			{
				BX.Call.Util.getUsers(this.currentCall.id, [e.userId]).then(function (userData)
				{
					this.callView.updateUserData(userData)
				}.bind(this));
				this.callView.addUser(e.userId);
			}
		},

		_onCallDestroy: function (e)
		{
			if (this.currentCall)
			{
				this.removeVideoStrategy();
				this.removeCallEvents();
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
			this.callRecordState = BX.Call.View.RecordState.Stopped;
			this.callRecordType = BX.Call.View.RecordType.None;
			if (this.callRecordMenu)
			{
				this.callRecordMenu.close();
			}

			if (this.callView && this.autoCloseCallView)
			{
				this.callView.close();
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
			if (this.mutePopup)
			{
				this.mutePopup.close();
			}
			if (this.documentPromoPopup)
			{
				this.documentPromoPopup.close();
			}
			this.allowMutePopup = true;
			this.docCreatedForCurrentCall = false;
			this._closeReconnectionBaloon();

			window.BXIM.messenger.dialogStatusRedraw();
			window.BXIM.stopRepeatSound('dialtone');
			window.BXIM.stopRepeatSound('ringtone');
		},

		_onCallUserStateChanged: function (e)
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
			}

			if (e.state == BX.Call.UserState.Connecting || e.state == BX.Call.UserState.Connected)
			{
				window.BXIM.stopRepeatSound('dialtone');
			}

			if (e.state == BX.Call.UserState.Connected)
			{
				if (!e.isLegacyMobile)
				{
					this.callView.unblockButtons(['camera', 'floorRequest', 'screen']);
				}

				if (this.callRecordState === BX.Call.View.RecordState.Stopped)
				{
					this.callView.unblockButtons(['record']);
				}

				/*BX.Call.Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_CONNECTED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));*/
			}
			else if (e.state == BX.Call.UserState.Idle && e.previousState == BX.Call.UserState.Connected)
			{
				/*BX.Call.Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_DISCONNECTED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));*/
			}
			else if (e.state == BX.Call.UserState.Failed)
			{
				if (e.networkProblem)
				{
					this.showNetworkProblemNotification(BX.message("IM_M_CALL_TURN_UNAVAILABLE"));
				}
				else
				{
					BX.Call.Util.getUser(this.currentCall.id, e.userId).then(function (userData)
					{
						this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_FAILED", {
							gender: userData.gender,
							name: userData.name
						}));
					}.bind(this));
				}
			}
			else if (e.state == BX.Call.UserState.Declined)
			{
				BX.Call.Util.getUser(this.currentCall.id, e.userId).then(function (userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_DECLINED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));
			}
			else if (e.state == BX.Call.UserState.Busy)
			{
				BX.Call.Util.getUser(this.currentCall.id, e.userId).then(function (userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_BUSY", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));
			}
		},

		_onCallUserMicrophoneState: function (e)
		{
			if (!this.callView)
			{
				return;
			}
			this.callView.setUserMicrophoneState(e.userId, e.microphoneState);
		},

		_onCallUserCameraState: function (e)
		{
			if (!this.callView)
			{
				return;
			}
			this.callView.setUserCameraState(e.userId, e.cameraState);
		},

		_onCallUserVideoPaused: function (e)
		{
			if (!this.callView)
			{
				return;
			}
			this.callView.setUserVideoPaused(e.userId, e.videoPaused);
		},

		_onCallLocalMediaReceived: function (e)
		{
			this.log("Received local media stream " + e.tag);
			if (this.callView)
			{
				var flipVideo = e.tag == "main" ? BX.Call.Hardware.enableMirroring : false;

				this.callView.setLocalStream(e.stream);
				this.callView.flipLocalVideo(flipVideo);

				this.callView.setButtonActive("screen", e.tag == "screen");
				if (e.tag == "screen")
				{
					if (!BX.desktop)
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
			}

			if (this.currentCall && this.currentCall.videoEnabled && e.stream.getVideoTracks().length === 0)
			{
				this.showNotification(BX.message("IM_CALL_CAMERA_ERROR_FALLBACK_TO_MIC"));
				this.currentCall.setVideoEnabled(false);
			}
		},
		_onCallLocalCameraFlip: function (e)
		{
			this._onCallLocalCameraFlipInDesktop(e.data.enableMirroring);
		},

		_onCallLocalCameraFlipInDesktop: function(e)
		{
			if(this.callView)
			{
				this.callView.flipLocalVideo(e);
			}
		},

		_onCallLocalMediaStopped: function (e)
		{
			// do nothing
		},

		_onCallRemoteMediaReceived: function (e)
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
		},

		_onCallRemoteMediaStopped: function (e)
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
		},

		_onCallUserVoiceStarted: function (e)
		{
			if (e.local)
			{
				if (this.currentCall.muted && this.allowMutePopup)
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
		},

		_onCallUserVoiceStopped: function (e)
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
		},

		_onCallUserScreenState: function (e)
		{
			if (this.callView)
			{
				this.callView.setUserScreenState(e.userId, e.screenState);
			}
			if (e.userId == BX.CallEngine.getCurrentUserId())
			{
				this.callView.setButtonActive("screen", e.screenState);
				if (e.screenState)
				{
					if (!BX.desktop)
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
		},

		_onCallUserRecordState: function (event)
		{
			this.callRecordState = event.recordState.state;
			this.callView.setRecordState(event.recordState);

			if (!this.canRecord() || event.userId != BX.message['USER_ID'])
			{
				return true;
			}

			if (
				event.recordState.state === BX.Call.View.RecordState.Started
				&& event.recordState.userId == BX.message['USER_ID']
			)
			{
				var windowId = BX.Call.View.RecordSource.Chat;
				var dialogId = this.currentCall.associatedEntity.id;
				var dialogName = this.currentCall.associatedEntity.name;
				var callId = this.currentCall.id;
				var callDate = BX.date.format(window.BXIM.webrtc.formatRecordDate || 'd.m.Y');

				var fileName = BX.message('IM_CALL_RECORD_NAME');
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

				BX.CallEngine.getRestClient().callMethod("im.call.onStartRecord", {callId: this.currentCall.id});

				BXDesktopSystem.CallRecordStart({
					windowId: windowId,
					fileName: fileName,
					callId: callId,
					callDate: callDate,
					dialogId: dialogId,
					dialogName: dialogName,
					video: this.callRecordType !== BX.Call.View.RecordType.Audio,
					muted: this.currentCall.isMuted(),
					cropTop: 72,
					cropBottom: 73,
					shareMethod: 'im.disk.record.share'
				});
			}
			else if (event.recordState.state === BX.Call.View.RecordState.Stopped)
			{
				BXDesktopSystem.CallRecordStop();
			}

			return true;
		},

		_onCallUserFloorRequest: function (e)
		{
			if (this.callView)
			{
				this.callView.setUserFloorRequestState(e.userId, e.requestActive);
			}
		},

		_onCallFailure: function (e)
		{
			var errorCode = e.code || e.name || e.error;
			console.error("Call failure: ", e);

			var errorMessage;

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
			else if (errorCode == "NotAllowedError")
			{
				errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
			}
			else
			{
				//errorMessage = BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED");
				errorMessage = BX.message("IM_CALL_ERROR_UNKNOWN_WITH_CODE")
					.replace("#ERROR_CODE#", errorCode);
			}
			if (this.callView)
			{
				this.callView.showFatalError({text: errorMessage});
			}
			else
			{
				this.showNotification(errorMessage);
			}
			window.BXIM.stopRepeatSound('dialtone');
			this.autoCloseCallView = false;
			if (this.currentCall)
			{
				this.removeVideoStrategy();
				this.removeCallEvents();
				this.currentCall.destroy();
				this.currentCall = null;
			}
		},

		_onNetworkProblem: function (e)
		{
			this.showNetworkProblemNotification(BX.message("IM_M_CALL_TURN_UNAVAILABLE"));
		},

		_onMicrophoneLevel: function (e)
		{
			if (this.callView)
			{
				this.callView.setMicrophoneLevel(e.level)
			}
		},

		_onReconnecting: function ()
		{
			// todo: restore after fixing balloon resurrection issue
			return false;

			if (this.reconnectionBaloon)
			{
				return;
			}

			this.reconnectionBaloon = BX.UI.Notification.Center.notify({
				content: BX.util.htmlspecialchars(BX.message('IM_CALL_RECONNECTING')),
				autoHide: false,
				position: "top-right",
				closeButton: false,
			})
		},

		_onReconnected: function ()
		{
			// todo: restore after fixing balloon resurrection issue
			return false;

			this._closeReconnectionBaloon();
		},

		_onCustomMessage: function (event)
		{
			// there will be no more template selector in this call
			if (event.message === DOC_CREATED_EVENT)
			{
				this.docCreatedForCurrentCall = true;
				this.maxEditorWidth = DOC_EDITOR_WIDTH;
			}
		},

		_onCallJoin: function (e)
		{
			if (e.local)
			{
				// self answer, no nothing
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

			window.BXIM.stopRepeatSound('dialtone');
		},

		_onCallLeave: function (e)
		{
			if (!e.local && this.currentCall && this.currentCall.ready)
			{
				this.log(new Error("received remote leave with active call!"));
				return;
			}

			if (this.isRecording())
			{
				BXDesktopSystem.CallRecordStop();
			}
			this.callRecordState = BX.Call.View.RecordState.Stopped;
			this.callRecordType = BX.Call.View.RecordType.None;
			this.docCreatedForCurrentCall = false;
			var showFeedback = false;
			var callDetails;

			if (this.currentCall)
			{
				this.removeVideoStrategy();
				this.removeCallEvents();

				showFeedback = this.currentCall.wasConnected;
				callDetails = {
					id: this.currentCall.id,
					provider: this.currentCall.provider,
					userCount: this.currentCall.users.length,
					browser: BX.Call.Util.getBrowserForStatistics(),
					isMobile: BX.browser.IsMobile(),
					isConference: false,
				};

				this.currentCall = null;
			}

			if (this.callView)
			{
				this.callView.close();
			}

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

			if (this.documentPromoPopup)
			{
				this.documentPromoPopup.close();
			}

			this._closeReconnectionBaloon();

			window.BXIM.messenger.dialogStatusRedraw();
			window.BXIM.stopRepeatSound('dialtone');
			window.BXIM.stopRepeatSound('ringtone');

			if (showFeedback)
			{
				//this.showFeedbackPopup(callDetails);
			}
		},

		_onInvitePopupDestroy: function (e)
		{
			this.callView.setHotKeyTemporaryBlock(false);
			this.invitePopup = null;
		},

		_onInvitePopupSelect: function (e)
		{
			this.invitePopup.close();

			if (!this.currentCall)
			{
				return;
			}

			var userId = e.user.id;

			if (BX.Call.Util.isCallServerAllowed() && this.currentCall instanceof BX.Call.PlainCall)
			{
				// trying to switch to the server version of the call
				this.removeVideoStrategy();
				this.removeCallEvents();
				BX.Call.Engine.getInstance().createChildCall(
					this.currentCall.id,
					BX.Call.Provider.Voximplant,
					[userId]
				).then(function (e)
				{
					this.childCall = e.call;

					this.childCall.addEventListener(BX.Call.Event.onRemoteMediaReceived, this._onChildCallFirstMediaHandler);
					this.childCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);

					this.childCall.useHdVideo(BX.Call.Hardware.preferHdQuality);
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

				}.bind(this));
				this.callView.addUser(userId, BX.Call.UserState.Calling);

				if (BXIM.messenger.users[userId])
				{
					var userData = {};
					userData[userId] = BXIM.messenger.users[userId];
					this.callView.updateUserData(userData)
				}
			}
			else
			{
				var currentUsers = this.currentCall.getUsers();
				if (Object.keys(currentUsers).length < BX.Call.Util.getUserLimit() - 1 || currentUsers.hasOwnProperty(userId))
				{
					this.currentCall.inviteUsers({
						users: [userId]
					});
				}
			}
		},

		_onWindowFocus: function ()
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
		},

		_onWindowBlur: function (e)
		{
			clearTimeout(this.showFloatingWindowTimeout);
			clearTimeout(this.showFloatingScreenShareWindowTimeout);
			if (this.currentCall && this.floatingWindow && this.callView)
			{
				this.showFloatingWindowTimeout = setTimeout(function ()
				{
					if (this.currentCall && this.floatingWindow && this.callView)
					{
						this.floatingWindow.setTitle(this.currentCall.associatedEntity.name);
						BX.Call.Util.getUserAvatars(this.currentCall.id, this.getActiveCallUsers()).then(function (result)
						{
							this.floatingWindow.setAvatars(result);
							this.floatingWindow.show();
						}.bind(this));
					}
				}.bind(this), 300);
			}

			if (this.currentCall && this.floatingScreenShareWindow && this.callView && this.currentCall.isScreenSharingStarted())
			{
				this.showFloatingScreenShareWindowTimeout = setTimeout(function ()
				{
					if (this.currentCall && this.floatingScreenShareWindow && this.callView && this.currentCall.isScreenSharingStarted())
					{
						this.floatingScreenShareWindow.show();
					}
				}.bind(this), 300);
			}
		},

		_onBeforeUnload: function (e)
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
		},

		_onImTabChange: function (currentTab)
		{
			if (currentTab === "notify" && this.currentCall && this.callView)
			{
				this.fold(BX.util.htmlspecialcharsback(this.currentCall.associatedEntity.name));
			}
		},

		_onUpdateChatCounter: function (counter)
		{
			if (!this.currentCall || !this.currentCall.associatedEntity || !this.currentCall.associatedEntity.id || !this.callView)
			{
				return;
			}

			this.callView.setButtonCounter("chat", counter);
		},

		_onDeviceChange: function (e)
		{
			if (!this.currentCall || !this.currentCall.ready)
			{
				return;
			}

			var added = e.data.added;
			var removed = e.data.removed;
			if (added.length > 0)
			{
				this.log("New devices: ", added);
				BX.UI.Notification.Center.notify({
					content: BX.message("IM_CALL_DEVICES_FOUND") + "<br><ul>" + added.map(function (deviceInfo)
					{
						return "<li>" + deviceInfo.label
					}) + "</ul>",
					position: "top-right",
					autoHideDelay: 10000,
					closeButton: true,
					//category: "call-device-change",
					actions: [
						{
							title: BX.message("IM_CALL_DEVICES_CLOSE"),
							events: {
								click: function (event, balloon, action)
								{
									balloon.close();
								}.bind(this)
							}
						}
					]
				});
				setTimeout(function ()
				{
					this.useDevicesInCurrentCall(added);
				}.bind(this), 500);
			}

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
							click: function (event, balloon, action)
							{
								balloon.close();
							}
						}
					}]
				});
				setTimeout(function ()
				{
					this.removeDevicesFromCurrentCall(removed);
				}.bind(this), 500)
			}
		},

		_onFloatingVideoMainAreaClick: function (e)
		{
			BX.desktop.windowCommand("show");
			BX.desktop.changeTab("im");

			if (!this.currentCall)
			{
				return;
			}

			if (this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
			{
				this.messenger.openMessenger(this.currentCall.associatedEntity.id);
			}
			else if (!this.isMessengerOpen())
			{
				this.messenger.openMessenger();
			}

			if (this.detached)
			{
				this.container.style.removeProperty('width');
				this.callView.show();
				this.detached = false;
			}
		},

		_onFloatingVideoButtonClick: function (e)
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
		},

		_onFloatingScreenShareBackToCallClick: function ()
		{
			BX.desktop.windowCommand("show");
			BX.desktop.changeTab("im");
			if (this.floatingScreenShareWindow)
			{
				this.floatingScreenShareWindow.hide();
			}
		},

		_onFloatingScreenShareStopClick: function ()
		{
			BX.desktop.windowCommand("show");
			BX.desktop.changeTab("im");

			this.currentCall.stopScreenSharing();

			if (this.floatingScreenShareWindow)
			{
				this.floatingScreenShareWindow.close();
			}

			if (this.isRecording())
			{
				BXDesktopSystem.CallRecordStopSharing();
			}
		},

		_onFloatingScreenShareChangeScreenClick: function ()
		{
			if (this.currentCall)
			{
				this.currentCall.startScreenSharing(true);
			}
		},

		_onResize: function ()
		{
			if (this.sidebar && this.callView)
			{
				var result = this.findCallEditorWidth();
				var callWidth = result.callWidth;
				var editorWidth = result.editorWidth;

				this.callView.setMaxWidth(callWidth);
				this.sidebar.setWidth(editorWidth);
			}
		},

		destroy: function ()
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
			BX.Call.Hardware.unsubscribe(BX.Call.Hardware.Events.onChangeMirroringVideo, this._onCallLocalCameraFlipHandler);
		},

		log: function ()
		{
			if (this.currentCall)
			{
				var arr = [this.currentCall.id];

				BX.CallEngine.log.apply(BX.CallEngine, arr.concat(Array.prototype.slice.call(arguments)));
			}
			else
			{
				BX.CallEngine.log.apply(BX.CallEngine, arguments);
			}
		},

		test: function (users, videoOptions, audioOptions)
		{
			var stream;
			var stream2;
			users = typeof (users) == "undefined" ? [473, 464] : users;
			videoOptions = typeof (videoOptions) == "undefined" ? {width: 320, height: 180} : videoOptions;
			audioOptions = typeof (audioOptions) == "undefined" ? false : audioOptions;

			this._openMessenger().then(function ()
			{
				return (videoOptions || audioOptions) ? BX.Call.Hardware.init() : null;
			}.bind(this)).then(function ()
			{
				this.createContainer();
				var hiddenButtons = ['floorRequest'];
				if (!BX.Call.Util.shouldShowDocumentButton())
				{
					hiddenButtons.push('document');
				}

				this.callView = new BX.Call.View({
					container: this.container,
					showChatButtons: true,
					userLimit: 48,
					language: window.BXIM.language,
					layout: BX.Call.View.Layout.Grid,
					hiddenButtons: hiddenButtons
				});

				var lastUserId = 1;

				this.callView.setCallback('onButtonClick', function (e)
				{
					console.log(e.buttonName);
					switch (e.buttonName)
					{
						case "hangup":
						case "close":
							this.callView.close();
							break;
						case "inviteUser":
							lastUserId++;
							BX.rest.callMethod('im.user.list.get', {
								'ID': [lastUserId],
								'AVATAR_HR': 'Y'
							}).then(function (response)
							{
								this.callView.updateUserData(response.data())
							}.bind(this));

							this.callView.addUser(lastUserId, BX.Call.UserState.Connecting);
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
							this.callView.setUserMedia(464, 'screen', stream2.getVideoTracks()[0]);

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
				}.bind(this));
				//this.callView.blockAddUser();
				this.callView.setCallback(BX.Call.View.Event.onUserClick, function (e)
				{
					if (!e.stream)
					{
						this.callView.setUserState(e.userId, BX.Call.UserState.Connected);
						this.callView.setUserMedia(e.userId, 'video', stream2.getVideoTracks()[0]);
					}
				}.bind(this));
				this.callView.setUiState(BX.Call.View.UiState.Connected);
				this.callView.setCallback(BX.Call.View.Event.onBodyClick, this._onCallViewBodyClick.bind(this));
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

			}.bind(this)).then(function (s)
			{
				stream = s;
				this.callView.setLocalStream(stream);
				users.forEach(function (userId)
				{
					this.callView.addUser(userId, BX.Call.UserState.Connected);
				}, this);

				if (audioOptions !== false)
				{
					this.vad = new BX.SimpleVAD({
						mediaStream: stream
					});
					setInterval(function ()
					{
						this.callView.setMicrophoneLevel(this.vad.currentVolume)
					}.bind(this), 100)
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

			}.bind(this)).then(function (s2)
			{
				stream2 = s2;
				/*users.forEach(function(userId)
				 {
					this.callView.setUserMedia(userId, 'video', stream2.getVideoTracks()[0]);
				},this);*/

				this.callView.setUserMedia(users[0], 'video', stream2.getVideoTracks()[0]);

				BX.rest.callMethod('im.user.list.get', {
					'ID': users.concat(BXIM.userId),
					'AVATAR_HR': 'Y'
				}).then(function (response)
				{
					this.callView.updateUserData(response.data())
				}.bind(this));

			}.bind(this));
		},

		testIncoming: function ()
		{
			this.callNotification = new BX.Call.Notification({
				callerName: "this.currentCall.associatedEntity.name",
				callerAvatar: "this.currentCall.associatedEntity.avatar",
				callerType: "this.currentCall.associatedEntity.advanced.chatType",
				callerColor: "",
				video: true,
				hasCamera: false,
				onClose: this._onCallNotificationClose.bind(this),
				onDestroy: this._onCallNotificationDestroy.bind(this),
				onButtonClick: function ()
				{
					this.callNotification.close()
				}.bind(this)
			});

			this.callNotification.show();
		},

		getMaxActiveMicrophonesCount: function()
		{
			return 4;
		},

		showMicMutedNotification: function ()
		{
			if (this.mutePopup || !this.callView)
			{
				return;
			}

			this.mutePopup = new BX.Call.MicMutedPopup({
				callFolded: this.folded,
				bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
				targetContainer: this.folded ? this.messenger.popupMessengerContent : this.callView.container,
				onClose: function ()
				{
					this.allowMutePopup = false;
					this.mutePopup.destroy();
					this.mutePopup = null;
				}.bind(this),
				onUnmuteClick: function ()
				{
					this._onCallViewToggleMuteButtonClick({
						muted: false
					});
					this.mutePopup.destroy();
					this.mutePopup = null;
				}.bind(this)
			});
			this.mutePopup.show();
		},

		showAutoMicMuteNotification: function ()
		{
			if (this.mutePopup || !this.callView)
			{
				return;
			}

			this.mutePopup = new BX.Call.MicMutedPopup({
				callFolded: this.folded,
				bindElement: this.folded ? null : this.callView.buttons.microphone.elements.icon,
				targetContainer: this.folded ? this.messenger.popupMessengerContent : this.callView.container,
				title: BX.util.htmlspecialchars(BX.message("IM_CALL_MIC_AUTO_MUTED")),
				onClose: function ()
				{
					this.mutePopup.destroy();
					this.mutePopup = null;
				}.bind(this),
				onUnmuteClick: function ()
				{
					this._onCallViewToggleMuteButtonClick({
						muted: false
					});
					this.mutePopup.destroy();
					this.mutePopup = null;
				}.bind(this)
			});
			this.mutePopup.show();
		},

		showWebScreenSharePopup: function ()
		{
			if (this.webScreenSharePopup)
			{
				this.webScreenSharePopup.show();

				return;
			}

			this.webScreenSharePopup = new BX.Call.WebScreenSharePopup({
				bindElement: this.callView.buttons.screen.elements.root,
				targetContainer: this.callView.container,
				onClose: function ()
				{
					this.webScreenSharePopup.destroy();
					this.webScreenSharePopup = null;
				}.bind(this),
				onStopSharingClick: function ()
				{
					this._onCallViewToggleScreenSharingButtonClick();
					this.webScreenSharePopup.destroy();
					this.webScreenSharePopup = null;
				}.bind(this)
			});
			this.webScreenSharePopup.show();
		},

		showFeedbackPopup: function (callDetails)
		{
			if (this.feedbackPopup)
			{
				return;
			}
			var darkMode = !!BX.MessengerTheme.isDark();
			if (!BX.type.isPlainObject(callDetails))
			{
				callDetails = {};
			}

			BX.loadExt('im.component.call-feedback').then(function ()
			{
				var vueInstance;
				this.feedbackPopup = new BX.PopupWindow({
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
					events: {
						onPopupClose: function ()
						{
							this.destroy();
						},
						onPopupDestroy: function ()
						{
							if (vueInstance)
							{
								vueInstance.$destroy();
							}
							this.feedbackPopup = null;
						}.bind(this)
					}
				});

				var template = '<bx-im-component-call-feedback ' +
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
						onFeedbackSent: function ()
						{
							setTimeout(
								function ()
								{
									if (this.feedbackPopup)
									{
										this.feedbackPopup.close()
									}
								}.bind(this),
								1500
							)
						}.bind(this)
					}
				});
				vueInstance.mount('#' + this.feedbackPopup.getContentContainer().id);

				this.feedbackPopup.show();

			}.bind(this))
		}
	};

	BX.Call.Controller.FeatureState = {
		Enabled: 'enabled',
		Disabled: 'disabled',
		Limited: 'limited',
	};

	BX.Call.Controller.Events = Events;
	BX.Call.Controller.ViewState = ViewState;
	BX.Call.Controller.DocumentType = DocumentType;
})();
