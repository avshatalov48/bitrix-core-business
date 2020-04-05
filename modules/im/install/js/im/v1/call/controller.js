;(function()
{
	BX.namespace("BX.Call");
	if(BX.Call.Controller)
	{
		return;
	}

	BX.Call.Controller = function(config)
	{
		this.messenger = config.messenger;

		this.container = null;

		this.folded = false;
		this.debug = false;

		this.currentCall = null;
		this.childCall = null;
		this.callView = null;
		this.callNotification = null;
		this.invitePopup = null;

		this.isHttps = window.location.protocol === "https:";
		this.callWithMobile = false;

		this.autoCloseCallView = true;

		this.localStreams = {
			main: null,
			screen: null
		};

		this.cameraList = {};
		this.microphoneList = {};

		this.defaultMicrophone = localStorage ? localStorage.getItem('bx-im-settings-default-microphone') : '';
		this.defaultCamera = localStorage ? localStorage.getItem('bx-im-settings-default-camera') : '';
		this.defaultSpeaker = localStorage ? localStorage.getItem('bx-im-settings-default-speaker') : '';
		this.enableMicAutoParameters = localStorage ? (localStorage.getItem('bx-im-settings-enable-mic-auto-parameters') !== 'N') : true;

		// event handlers
		this._onCallUserInvitedHandler = this._onCallUserInvited.bind(this);
		this._onCallDestroyHandler = this._onCallDestroy.bind(this);
		this._onCallUserStateChangedHandler = this._onCallUserStateChanged.bind(this);
		this._onCallLocalMediaReceivedHandler = this._onCallLocalMediaReceived.bind(this);
		this._onCallLocalMediaStoppedHandler = this._onCallLocalMediaStopped.bind(this);
		this._onCallUserStreamReceivedHandler = this._onCallUserStreamReceived.bind(this);
		this._onCallUserStreamRemovedHandler = this._onCallUserStreamRemoved.bind(this);
		this._onCallUserVoiceStartedHandler = this._onCallUserVoiceStarted.bind(this);
		this._onCallUserVoiceStoppedHandler = this._onCallUserVoiceStopped.bind(this);
		this._onCallDeviceListUpdatedHandler = this._onCallDeviceListUpdated.bind(this);
		this._onCallFailureHandler = this._onCallFailure.bind(this);

		this._onBeforeUnloadHandler = this._onBeforeUnload.bind(this);
		this._onImTabChangeHandler = this._onImTabChange.bind(this);

		this._onChildCallFirstStreamHandler = this._onChildCallFirstStream.bind(this);

		this._onWindowFocusHandler = this._onWindowFocus.bind(this);
		this._onWindowBlurHandler = this._onWindowBlur.bind(this);

		if(BX.desktop)
		{
			this.floatingWindow = new BX.Call.FloatingVideo({
				onMainAreaClick: this._onFloatingVideoMainAreaClick.bind(this),
				onButtonClick: this._onFloatingVideoButtonClick.bind(this)
			});
			this.floatingWindowUser = 0;
		}
		this.showFloatingWindowTimeout = 0;
		this.hideIncomingCallTimeout = 0;

		this.init();
	};

	BX.Call.Controller.prototype = {
		init: function()
		{
			var self = this;
			BX.addCustomEvent(window, "CallEvents::incomingCall", function(e)
			{
				/** @var {BX.Call.AbstractCall} newCall */
				var newCall = e.call;
				this.callWithMobile = (e.isMobile === true);
				if(!this.currentCall)
				{
					this.currentCall = newCall;
					this.bindCallEvents();

					this.checkDeskop().then(function()
					{
						return self.enumerateDevices();
					}).then(function()
					{
						self.showIncomingCall({
							video: e.video == true
						});
					});
				}
				else
				{
					if(newCall.id == this.currentCall.id)
					{
						// ignoring
					}
					else if(newCall.parentId == this.currentCall.id)
					{
						if(!this.childCall)
						{
							this.childCall = newCall;
							this.childCall.users.forEach(function(userId)
							{
								this.callView.addUser(userId, BX.Call.UserState.Calling);
							}, this);

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
			}.bind(this));

			if(BX.desktop)
			{
				window.addEventListener("blur", this._onWindowBlurHandler);
				window.addEventListener("focus", this._onWindowFocusHandler);

				BX.desktop.addCustomEvent("BXForegroundChanged", function(focus)
				{
					if(focus)
					{
						this._onWindowFocus();
					}
					else
					{
						this._onWindowBlur();
					}
				}.bind(this));
			}

			window.addEventListener("beforeunload", this._onBeforeUnloadHandler);
			BX.addCustomEvent("OnDesktopTabChange", this._onImTabChangeHandler);

			BX.garbage(this.destroy, this);
		},

		bindCallEvents: function()
		{
			this.currentCall.addEventListener(BX.Call.Event.onUserInvited, this._onCallUserInvitedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onDestroy, this._onCallDestroyHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserStateChanged, this._onCallUserStateChangedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onStreamReceived, this._onCallUserStreamReceivedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onStreamRemoved, this._onCallUserStreamRemovedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
			this.currentCall.addEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
		},

		removeCallEvents: function()
		{
			this.currentCall.removeEventListener(BX.Call.Event.onUserInvited, this._onCallUserInvitedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onDestroy, this._onCallDestroyHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserStateChanged, this._onCallUserStateChangedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onLocalMediaStopped, this._onCallLocalMediaStoppedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onStreamReceived, this._onCallUserStreamReceivedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onStreamRemoved, this._onCallUserStreamRemovedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStarted, this._onCallUserVoiceStartedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onUserVoiceStopped, this._onCallUserVoiceStoppedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onDeviceListUpdated, this._onCallDeviceListUpdatedHandler);
			this.currentCall.removeEventListener(BX.Call.Event.onCallFailure, this._onCallFailureHandler);
		},

		bindCallViewEvents: function()
		{
			this.callView.setCallback('onClose', this._onCallViewClose.bind(this));
			this.callView.setCallback('onDestroy', this._onCallViewDestroy.bind(this));
			this.callView.setCallback('onButtonClick', this._onCallViewButtonClick.bind(this));
			this.callView.setCallback('onBodyClick', this._onCallViewBodyClick.bind(this));
			this.callView.setCallback('onReplaceCamera', this._onCallViewReplaceCamera.bind(this));
			this.callView.setCallback('onReplaceMicrophone', this._onCallViewReplaceMicrophone.bind(this));
			this.callView.setCallback('onSetCentralUser', this._onCallViewSetCentralUser.bind(this));
		},

		enumerateDevices: function()
		{
			var self = this;
			self.microphoneList = {};
			self.cameraList = {};
			return new Promise(function(resolve, reject)
			{
				navigator.mediaDevices.enumerateDevices().then(function(devices)
				{
					devices.forEach(function(deviceInfo)
					{
						if (deviceInfo.kind == "audioinput")
						{
							self.microphoneList[deviceInfo.deviceId] = deviceInfo.label;
						}
						else if (deviceInfo.kind == "videoinput")
						{
							self.cameraList[deviceInfo.deviceId] = deviceInfo.label;
						}
					});

					resolve();
				})
			});
		},

		isMessengerOpen: function()
		{
			return !!this.messenger.popupMessenger;
		},

		isWebRTCSupported: function()
		{
			return (typeof webkitRTCPeerConnection != 'undefined' || typeof mozRTCPeerConnection != 'undefined' || typeof RTCPeerConnection != 'undefined');
		},

		isCallServerAllowed: function()
		{
			return BX.message('call_server_enabled') === 'Y'
		},

		getUserLimit: function()
		{
			return this.isCallServerAllowed() ? 10 : 4;
		},

		createContainer: function()
		{
			this.container = BX.create("div", {
				props: {className: "bx-messenger-call-overlay"},
			});

			if(BX.MessengerWindow)
			{
				BX.MessengerWindow.content.insertBefore(this.container, BX.MessengerWindow.content.firstChild);
			}
			else
			{
				this.messenger.popupMessengerContent.insertBefore(this.container, this.messenger.popupMessengerContent.firstChild);
			}

			this.messenger.popupMessengerContent.classList.add("bx-messenger-call");
		},

		resize: function()
		{

		},

		answerChildCall: function()
		{
			this.removeCallEvents();
			this.childCall.addEventListener(BX.Call.Event.onStreamReceived, this._onChildCallFirstStreamHandler);
			this.childCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);

			this.childCall.answer({
				useVideo: this.currentCall.isVideoEnabled()
			});
		},

		_onChildCallFirstStream: function(e)
		{
			this.callView.setStream(e.userId, e.stream);

			this.childCall.removeEventListener(BX.Call.Event.onStreamReceived, this._onChildCallFirstStreamHandler);

			this.removeCallEvents();
			var oldCall = this.currentCall;
			oldCall.destroy();

			this.currentCall = this.childCall;
			this.childCall = null;

			if(this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
			{
				this.messenger.openMessenger(this.currentCall.associatedEntity.id);
			}

			if(oldCall.muted)
			{
				this.currentCall.setMuted(true);
			}

			this.bindCallEvents();
		},

		checkDeskop: function()
		{
			return new Promise(function(resolve, reject)
			{
				BX.desktopUtils.runningCheck(reject, resolve);
			});
		},

		/**
		 * @param {Object} params
		 * @param {bool} [params.video = false]
		 */
		showIncomingCall: function(params)
		{
			if(!BX.type.isPlainObject(params))
			{
				params = {};
			}
			params.video = params.video == true;

			var allowVideo = this.callWithMobile ? params.video === true : true;

			this.callNotification = new BX.Call.Notification({
				callerName: this.currentCall.associatedEntity.name,
				callerAvatar: this.currentCall.associatedEntity.avatar,
				video: params.video,
				hasCamera: Object.keys(this.cameraList).length > 0 && allowVideo,
				onClose: this._onCallNotificationClose.bind(this),
				onDestroy: this._onCallNotificationDestroy.bind(this),
				onButtonClick: this._onCallNotificationButtonClick.bind(this)
			}) ;

			this.callNotification.show();
			clearTimeout(this.hideIncomingCallTimeout);
			this.hideIncomingCallTimeout = setTimeout(function()
			{
				if(this.callNotification)
				{
					this.callNotification.close();
				}
			}.bind(this), 30 * 1000);

			window.BXIM.repeatSound('ringtone', 5000);
		},

		showNotification: function(notificationText)
		{
			BX.UI.Notification.Center.notify({
				content: notificationText,
				position: "top-right",
				autoHideDelay: 5000,
				closeButton: true
			});
		},

		startCall: function(chatId, video)
		{
			if(this.callView || this.currentCall)
			{
				return;
			}

			var provider = BX.Call.Provider.Plain;

			if(this.isCallServerAllowed() && chatId.toString().substr(0, 4) === "chat")
			{
				provider = BX.Call.Provider.Voximplant;
			}

			if(!this.isMessengerOpen())
			{
				this.messenger.openMessenger();
			}
			this.createContainer();

			this.callView = new BX.Call.View({
				container: this.container,
				showChatButtons: true,
				userLimit: this.getUserLimit(),
				language: window.BXIM.language
			});
			this.bindCallViewEvents();

			BX.Call.Engine.getInstance().createCall({
				entityType: 'chat',
				entityId: chatId,
				provider: provider,
				videoEnabled: !!video,
				enableMicAutoParameters: this.enableMicAutoParameters,
				debug: this.debug === true
			}).then(function(e)
			{
				this.currentCall = e.call;

				if(this.defaultMicrophone)
				{
					this.currentCall.setMicrophoneId(this.defaultMicrophone);
				}
				if(this.defaultCamera)
				{
					this.currentCall.setCameraId(this.defaultCamera);
				}

				if(this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
				{
					this.messenger.openMessenger(this.currentCall.associatedEntity.id);
				}

				this.autoCloseCallView = true;
				this.bindCallEvents();

				this.callView.appendUsers(this.currentCall.getUsers());
				this.callView.show();

				this.currentCall.inviteUsers();
				window.BXIM.repeatSound('dialtone', 5000);
			}.bind(this)).catch(function(error)
			{
				console.error(error);
				this._onCallFailure({
					error: error.code == 'access_denied' ? 'ACCESS_DENIED' : 'UNKNOWN_ERROR'
				})

			}.bind(this));
		},

		hasActiveCall: function()
		{
			return (this.currentCall != null) || (this.callView != null);
		},

		fold: function()
		{
			if(this.folded || BX.desktop)
			{
				return;
			}

			this.folded = true;
			this.container.classList.add('bx-messenger-call-overlay-folded');
			this.callView.setTitle(this.currentCall.associatedEntity.name);
			this.callView.setSize(BX.Call.View.Size.Folded);
		},

		unfold: function()
		{
			if(!this.folded)
			{
				return;
			}
			this.folded = false;
			this.container.classList.remove('bx-messenger-call-overlay-folded');
			this.callView.setSize(BX.Call.View.Size.Full);
		},

		// event handlers

		_onCallNotificationClose: function(e)
		{
			clearTimeout(this.hideIncomingCallTimeout);
			window.BXIM.stopRepeatSound('ringtone');
			this.callNotification.destroy();
		},

		_onCallNotificationDestroy: function(e)
		{
			this.callNotification = null;
		},

		_onCallNotificationButtonClick: function(e)
		{
			clearTimeout(this.hideIncomingCallTimeout);
			this.callNotification.close();
			switch (e.button)
			{
				case "answer":
					if(BX.desktop)
					{
						BX.desktop.windowCommand("show");
					}

					if(this.callView)
					{
						this.callView.destroy();
					}

					if(this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
					{
						this.messenger.openMessenger(this.currentCall.associatedEntity.id);
					}
					else if(!this.isMessengerOpen())
					{
						this.messenger.openMessenger();
					}
					this.createContainer();

					this.callView = new BX.Call.View({
						container: this.container,
						users: this.currentCall.users,
						userStates: this.currentCall.getUsers(),
						showChatButtons: true,
						userLimit: this.getUserLimit()
					});
					this.autoCloseCallView = true;
					if(this.callWithMobile)
					{
						this.callView.disableAddUser();
					}

					this.bindCallViewEvents();
					this.callView.show();

					if(this.defaultMicrophone)
					{
						this.currentCall.setMicrophoneId(this.defaultMicrophone);
					}
					if(this.defaultCamera)
					{
						this.currentCall.setCameraId(this.defaultCamera);
					}

					this.currentCall.answer({
						useVideo: e.video,
						enableMicAutoParameters: this.enableMicAutoParameters
					});

					break;
				case "decline":
					this.currentCall.decline();
					break;
			}
		},

		_onCallViewClose: function(e)
		{
			this.callView.destroy();
			if(this.floatingWindow)
			{
				this.floatingWindow.close();
			}
		},

		_onCallViewDestroy: function(e)
		{
			if(this.messenger.popupMessengerContent)
			{
				BX.remove(this.container);
			}

			this.messenger.popupMessengerContent.classList.remove("bx-messenger-call");

			this.container = null;
			this.callView = null;
			this.folded = false;
			this.autoCloseCallView = true;
		},

		_onCallViewBodyClick: function(e)
		{
			if(this.folded)
			{
				this.unfold();
			}
		},

		_onCallViewButtonClick: function(e)
		{
			var buttonName = e.buttonName;

			var handlers = {
				hangup: this._onCallViewHangupButtonClick.bind(this),
				close: this._onCallViewCloseButtonClick.bind(this),
				inviteUser: this._onCallViewInviteUserButtonClick.bind(this),
				toggleMute: this._onCallViewToggleMuteButtonClick.bind(this),
				toggleScreenSharing: this._onCallViewToggleScreenSharingButtonClick.bind(this),
				toggleVideo: this._onCallViewToggleVideoButtonClick.bind(this),
				showChat: this._onCallViewShowChatButtonClick.bind(this),
				showHistory: this._onCallViewShowHistoryButtonClick.bind(this),
				fullscreen: this._onCallViewFullScreenButtonClick.bind(this),
			};

			if(BX.type.isFunction(handlers[buttonName]))
			{
				handlers[buttonName].call(this, e);
			}
		},

		_onCallViewHangupButtonClick: function(e)
		{
			var self = this;
			if(!this.currentCall)
			{
				return;
			}

			this.currentCall.hangup().then(function()
			{
				if(self.currentCall)
				{
					self.currentCall.destroy();
				}

				if(self.callView)
				{
					self.callView.close();
				}
			});
		},

		_onCallViewCloseButtonClick: function(e)
		{
			if(this.currentCall)
			{
				this.currentCall.destroy();
			}

			if(this.callView)
			{
				this.callView.close();
			}
		},

		_onCallViewShowChatButtonClick: function(e)
		{
			this.messenger.openMessenger(this.currentCall.associatedEntity.id);

			if(BX.desktop)
			{
				this.detached = true;
				this.callView.hide();
				this.floatingWindow.show();
				this.container.style.width = 0;
			}
			else
			{
				this.fold();
			}
		},

		/**
		 * Returns list of users, that are not currently connected
		 * @return {Array}
		 * @private
		 */
		_getDisconnectedUsers: function()
		{
			var result = [];
			var userStates = this.currentCall.getUsers();

			for(var userId in userStates)
			{
				if(userStates[userId] !== BX.Call.UserState.Connected && BX.Call.Util.userData[userId])
				{
					result.push(BX.Call.Util.userData[userId]);
				}
			}

			return result;
		},

		_onCallViewInviteUserButtonClick: function(e)
		{
			if(this.invitePopup)
			{
				this.invitePopup.close();
				return;
			}

			var userStates = this.currentCall.getUsers();
			var idleUsers = this._getDisconnectedUsers();

			this.invitePopup = new BX.Call.InvitePopup({
				bindElement: e.node,
				zIndex: 1200,
				idleUsers: idleUsers,
				allowNewUsers: Object.keys(userStates).length < this.getUserLimit() - 1,
				onDestroy: this._onInvitePopupDestroy.bind(this),
				onSelect: this._onInvitePopupSelect.bind(this)
			});

			this.invitePopup.show();
		},

		_onCallViewToggleMuteButtonClick: function(e)
		{
			this.currentCall.setMuted(e.muted);
			this.callView.setMuted(e.muted);
			if(this.floatingWindow)
			{
				this.floatingWindow.setAudioMuted(e.muted);
			}
		},

		_onCallViewToggleScreenSharingButtonClick: function(e)
		{
			if(this.currentCall.isScreenSharingStarted())
			{
				this.currentCall.stopScreenSharing();
			}
			else
			{
				this.currentCall.startScreenSharing();
			}
		},

		_onCallViewToggleVideoButtonClick: function(e)
		{
			this.currentCall.setVideoEnabled(e.video);
		},

		_onCallViewShowHistoryButtonClick: function(e)
		{
			this.messenger.openHistory(this.currentCall.associatedEntity.id);
		},

		_onCallViewFullScreenButtonClick: function(e)
		{
			if(this.folded)
			{
				this.unfold();
			}
			this.callView.toggleFullScreen();
		},

		_onCallViewReplaceCamera: function(e)
		{
			if(this.currentCall)
			{
				this.currentCall.setCameraId(e.deviceId);
			}

			// maybe update default camera
		},

		_onCallViewReplaceMicrophone: function(e)
		{
			if(this.currentCall)
			{
				this.currentCall.setMicrophoneId(e.deviceId)
			}

			// maybe update default microphone
		},

		_onCallViewSetCentralUser: function(e)
		{
			if(e.stream && this.floatingWindow)
			{
				this.floatingWindowUser = e.userId;
				this.floatingWindow.setStream(e.stream);
			}
		},

		_onCallUserInvited: function(e)
		{
			if(this.callView)
			{
				this.callView.addUser(e.userId);
			}
		},

		_onCallDestroy: function(e)
		{
			this.currentCall = null;
			this.callWithMobile = false;

			if(this.callNotification)
			{
				this.callNotification.close();
			}
			if(this.invitePopup)
			{
				this.invitePopup.close();
			}
			if(this.callView && this.autoCloseCallView)
			{
				this.callView.close();
			}
			if(this.floatingWindow)
			{
				this.floatingWindow.close();
			}

			window.BXIM.messenger.dialogStatusRedraw();
			window.BXIM.stopRepeatSound('dialtone');
			window.BXIM.stopRepeatSound('ringtone');
		},

		_onCallUserStateChanged: function(e)
		{
			window.BXIM.stopRepeatSound('dialtone');
			if(this.callView)
			{
				this.callView.setUserState(e.userId, e.state);
				if(e.isMobile)
				{
					this.callView.disableAddUser();
					this.callView.disableSwitchCamera();
					this.callView.disableSwitchMicrophone();
					this.callView.disableScreenSharing();
					this.callView.updateButtons();
				}
			}

			if(e.state == BX.Call.UserState.Connected)
			{
				BX.Call.Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_CONNECTED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));
			}
			else if(e.state == BX.Call.UserState.Idle && e.previousState == BX.Call.UserState.Connected)
			{
				BX.Call.Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_DISCONNECTED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));
			}
			else if(e.state == BX.Call.UserState.Failed)
			{
				BX.Call.Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_FAILED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));
			}
			else if(e.state == BX.Call.UserState.Declined)
			{
				BX.Call.Util.getUser(e.userId).then(function(userData)
				{
					this.showNotification(BX.Call.Util.getCustomMessage("IM_M_CALL_USER_DECLINED", {
						gender: userData.gender,
						name: userData.name
					}));
				}.bind(this));
			}
		},

		_onCallLocalMediaReceived: function(e)
		{
			this.localStreams[e.tag] = e.stream;
			if(this.callView)
			{
				this.callView.setLocalStream(e.stream);
			}
		},

		_onCallLocalMediaStopped: function(e)
		{

			this.localStreams[e.tag] = null;
			if(e.tag === 'screen')
			{
				this.callView.setLocalStream(this.localStreams['main']);
			}
		},

		_onCallUserStreamReceived: function(e)
		{
			if(this.callView)
			{
				this.callView.setStream(e.userId, e.stream);
			}

			if(this.floatingWindow && this.floatingWindowUser == e.userId)
			{
				this.floatingWindow.setStream(e.stream);
			}
		},

		_onCallUserStreamRemoved: function(e)
		{
			// this is never used, i believe
		},

		_onCallUserVoiceStarted: function(e)
		{
			if(this.callView)
			{
				this.callView.setUserTalking(e.userId, true);
			}
		},

		_onCallUserVoiceStopped: function(e)
		{
			if(this.callView)
			{
				this.callView.setUserTalking(e.userId, false);
			}
		},

		_onCallDeviceListUpdated: function(e)
		{
			if(this.callView)
			{
				this.callView.setDeviceList(e.deviceList);
			}
		},

		_onCallFailure: function(e)
		{
			var error = e.error;
			console.error("Call failure: ", e);

			if(error == "AUTHORIZE_ERROR")
			{
				this.callView.showFatalError({text: BX.message("IM_CALL_ERROR_AUTHORIZATION")});
			}
			else if(error == "ACCESS_DENIED")
			{
				this.callView.showFatalError({text: BX.message("IM_CALL_ERROR_ACCESS_DENIED")});
			}
			else if(error == "UNKNOWN_ERROR")
			{
				this.callView.showFatalError({text: BX.message("IM_CALL_ERROR_UNKNOWN")});
			}
			else if(!this.isHttps)
			{
				this.callView.showFatalError({text: BX.message("IM_CALL_ERROR_HTTPS_REQUIRED")});
			}
			else
			{
				this.callView.showFatalError({text: BX.message("IM_CALL_ERROR_HARDWARE_ACCESS_DENIED")});
			}

			this.autoCloseCallView = false;

			if(this.currentCall)
			{
				this.currentCall.decline();
			}
		},

		_onInvitePopupDestroy: function(e)
		{
			this.invitePopup = null;
		},

		_onInvitePopupSelect: function(e)
		{
			this.invitePopup.close();

			if(!this.currentCall)
			{
				return;
			}

			var userId = e.user.id;

			if(this.isCallServerAllowed() && this.currentCall instanceof BX.Call.PlainCall)
			{
				// trying to switch to the server version of the call
				this.removeCallEvents();
				BX.Call.Engine.getInstance().createChildCall(
					this.currentCall.id,
					BX.Call.Provider.Voximplant,
					[userId]
				).then(function(e)
				{
					this.childCall = e.call;

					this.childCall.addEventListener(BX.Call.Event.onStreamReceived, this._onChildCallFirstStreamHandler);
					this.childCall.addEventListener(BX.Call.Event.onLocalMediaReceived, this._onCallLocalMediaReceivedHandler);

					this.childCall.inviteUsers({
						users: this.childCall.users
					});

				}.bind(this));
				this.callView.addUser(userId, BX.Call.UserState.Calling);
			}
			else
			{
				var currentUsers = this.currentCall.getUsers();
				if(Object.keys(currentUsers).length < this.getUserLimit() - 1 || currentUsers.hasOwnProperty(userId))
				{
					this.currentCall.inviteUsers({
						users: [userId]
					});
				}
			}
		},

		_onWindowFocus: function()
		{
			if(!this.detached)
			{
				clearTimeout(this.showFloatingWindowTimeout);
				if(this.floatingWindow)
				{
					this.floatingWindow.hide();
				}
			}
		},

		_onWindowBlur: function(e)
		{
			clearTimeout(this.showFloatingWindowTimeout);
			if(this.currentCall && this.floatingWindow && this.callView)
			{
				this.showFloatingWindowTimeout = setTimeout(function()
				{
					if(this.currentCall && this.floatingWindow && this.callView)
					{
						this.floatingWindow.show();
					}
				}.bind(this), 300);

			}
		},

		_onBeforeUnload: function(e)
		{
			if(this.hasActiveCall())
			{
				e.preventDefault();
				e.returnValue = '';
			}
		},

		_onImTabChange: function(currentTab)
		{
			if(currentTab === "notify" && this.currentCall && this.callView)
			{
				this.fold();
			}
		},

		_onFloatingVideoMainAreaClick: function(e)
		{
			BX.desktop.windowCommand("show");
			BX.desktop.changeTab("im");

			if(!this.currentCall)
			{
				return;
			}

			if(this.currentCall.associatedEntity && this.currentCall.associatedEntity.id)
			{
				this.messenger.openMessenger(this.currentCall.associatedEntity.id);
			}
			else if(!this.isMessengerOpen())
			{
				this.messenger.openMessenger();
			}

			if(this.detached)
			{
				this.container.style.removeProperty('width');
				this.callView.show();
				this.detached = false;
			}
		},

		_onFloatingVideoButtonClick: function(e)
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

		destroy: function()
		{
			if(this.floatingWindow)
			{
				this.floatingWindow.destroy();
				this.floatingWindow = null;
			}
		}
	}
})();