;(function()
{
	BX.namespace('BX.Call');

	if (BX.Call.View)
	{
		return;
	}

	var Layouts = {
		Grid: 1,
		Centered: 2
	};

	var UiState = {
		Initializing: 1,
		Calling: 2,
		Connected: 3,
		Error: 4
	};

	var localUserPosition = 1000;
	var addButtonPosition = 1001;

	/**
	 *
	 * @param config
	 * @constructor
	 */
	BX.Call.View = function(config)
	{
		this.title = config.title;
		this.container = config.container;
		this.cameraId = config.cameraId;
		this.microphoneId = config.microphoneId;
		this.speakerId = BX.Call.Hardware.defaultSpeaker;
		this.speakerMuted = false;
		this.showChatButtons = (config.showChatButtons === true);

		this.language = config.language || '';

		this.lastPosition = 1;

		this.userLimit = config.userLimit || 1;
		this.userId = BX.message('USER_ID');
		this.localUser = new CallUser({
			id: this.userId,
			state: BX.Call.UserState.Connected,
			localUser: true,
			order: localUserPosition,
		});

		this.mediaSelectionBlocked = false;

		this.visible = false;
		this.elements = {
			root: null,
			container: null,
			overlay: null,
			panel: null,
			audioContainer: null,
			audio: {
				// userId: <audio> for this user's stream
			},
			center: null,
			userBlock: null,
			ear: {
				left: null,
				right: null
			},
			userList: {
				container: null,
				addButton: null
			},
		};

		this.buttons = {
			title: null,
			grid: null,
			add: null,
			link: null,
			microphone: null,
			camera: null,
			speaker: null,
			screen: null,
			chat: null,
			history: null,
			hangup: null,
			fullscreen: null,
			overlay: null,
			status: null
		};

		this.size = BX.Call.View.Size.Full;
		this.isMuted = false;
		this.isCameraOn = false;
		this.isFullScreen = false;

		this.disabledButtons = {};

		this.uiState = UiState.Calling;
		this.layout = config.layout || Layouts.Centered;

		this.users = {}; // Call participants. The key is the user id.

		if(BX.type.isPlainObject(config.userStates))
		{
			this.appendUsers(config.userStates);
		}

		this.callbacks = {
			onClose: BX.type.isFunction(config.onClose) ? config.onClose : BX.DoNothing,
			onDestroy: BX.type.isFunction(config.onDestroy) ? config.onDestroy : BX.DoNothing,
			onButtonClick: BX.type.isFunction(config.onButtonClick) ? config.onButtonClick : BX.DoNothing,
			onBodyClick: BX.type.isFunction(config.onBodyClick) ? config.onBodyClick : BX.DoNothing,
			onReplaceCamera: BX.type.isFunction(config.onReplaceCamera) ? config.onReplaceCamera : BX.DoNothing,
			onReplaceMicrophone: BX.type.isFunction(config.onReplaceMicrophone) ? config.onReplaceMicrophone : BX.DoNothing,
			onSetCentralUser: BX.type.isFunction(config.onSetCentralUser) ? config.onSetCentralUser : BX.DoNothing
		};

		this.scrollInterval = 0;

		// Event handlers
		this._onFullScreenChangeHandler = this._onFullScreenChange.bind(this);
		//this._onResizeHandler = BX.throttle(this._onResize.bind(this), 500);
		this._onResizeHandler = this._onResize.bind(this);

		this.init();
	};

	BX.Call.View.prototype.init = function()
	{
		if(this.isFullScreenSupported())
		{
			if (BX.browser.IsChrome() || BX.browser.IsSafari())
			{
				window.addEventListener("webkitfullscreenchange", this._onFullScreenChangeHandler);
			}
			else if (BX.browser.IsFirefox())
			{
				window.addEventListener("mozfullscreenchange", this._onFullScreenChangeHandler);
			}
		}
		window.addEventListener("resize", this._onResizeHandler);

		this.elements.audioContainer = BX.create("div", {
			props: {className: "bx-messenger-videocall-audio-container"}
		});

		this.container.appendChild(this.elements.audioContainer);
	};

	BX.Call.View.prototype.setCallback = function(name, cb)
	{
		if(BX.type.isFunction(cb) && this.callbacks.hasOwnProperty(name))
		{
			this.callbacks[name] = cb;
		}
	};

	BX.Call.View.prototype.getNextPosition = function()
	{
		return this.lastPosition++;
	};

	/**
	 * @param {object} userStates {userId -> state}
	 */
	BX.Call.View.prototype.appendUsers = function(userStates)
	{
		if(!BX.type.isPlainObject(userStates))
		{
			return;
		}

		var userIds = Object.keys(userStates);

		for (var i = 0; i < userIds.length; i++)
		{
			this.users[userIds[i]] = new CallUser({
				id: userIds[i],
				state : userStates[userIds[i]] ? userStates[userIds[i]] : BX.Call.UserState.Idle,
				order: this.getNextPosition(),
				onClick: this._onUserClick.bind(this)
			});
		}

		BX.Call.Util.updateUserData(userIds);
	};

	BX.Call.View.prototype.setCentralUser = function(userId)
	{
		if (this.centralUser.userId == userId)
		{
			return;
		}
		if (userId == this.userId && this.getUsersWithVideo().length > 0)
		{
			return;
		}
		if (!this.users[userId] && userId != this.userId)
		{
			return;
		}

		this.centralUser.setUserId(userId);
		if(this.layout == Layouts.Centered)
		{
			this.updateUserList();
		}
		this.callbacks.onSetCentralUser({
			userId: userId,
			stream: userId == this.userId ? this.localUser.stream : this.users[userId].stream
		})
	};

	BX.Call.View.prototype.getUserCount = function()
	{
		return Object.keys(this.users).length;
	};

	BX.Call.View.prototype.getConnectedUserCount = function()
	{
		return this.getConnectedUsers().length;
	};

	BX.Call.View.prototype.getUsersWithVideo = function()
	{
		var result = [];

		for (var userId in this.users)
		{
			if(this.users[userId].hasVideo())
			{
				result.push(userId);
			}
		}
		return result;
	};

	BX.Call.View.prototype.getConnectedUsers = function()
	{
		var result = [];
		for (var userId in this.users)
		{
			if(this.users[userId].state == BX.Call.UserState.Connected)
			{
				result.push(userId);
			}
		}
		return result;
	};

	BX.Call.View.prototype.setUiState = function(uiState)
	{
		if(this.uiState == uiState)
		{
			return;
		}

		this.uiState = uiState;
		if(this.uiState == UiState.Error && this.elements.container)
		{
			this.elements.container.textContent = '';
		}
		if(this.elements.root)
		{
			this.updateButtons();
		}
	};

	BX.Call.View.prototype.setLayout = function(newLayout)
	{
		if(newLayout == this.layout)
		{
			return;
		}

		this.layout = newLayout;

		if(this.layout == Layouts.Centered)
		{
			this.elements.root.className = "bx-messenger-videocall bx-messenger-videocall-centered";
			this.elements.userBlock.appendChild(this.elements.userList.container);
			this.elements.container.appendChild(this.elements.center);
			this.elements.container.appendChild(this.elements.userBlock);

			this.centralUser.playVideo();
		}
		else if (this.layout == Layouts.Grid)
		{
			this.elements.root.className = "bx-messenger-videocall bx-messenger-videocall-grid";
			this.elements.container.removeChild(this.elements.center);
			this.elements.container.removeChild(this.elements.userBlock);
			this.elements.container.appendChild(this.elements.userList.container);
		}
		this.renderUserList();
		this.toggleEars();
	};

	BX.Call.View.prototype.setCameraState = function(newCameraState)
	{
		newCameraState = !!newCameraState;
		if(this.isCameraOn == newCameraState)
		{
			return;
		}

		this.isCameraOn = newCameraState;

		if(this.buttons.camera)
		{
			if(this.isCameraOn)
			{
				this.buttons.camera.enable();
			}
			else
			{
				this.buttons.camera.disable();
			}
		}
	};

	BX.Call.View.prototype.setMuted = function(isMuted)
	{
		isMuted = !!isMuted;
		if(this.isMuted == isMuted)
		{
			return;
		}

		this.isMuted = isMuted;
		if(this.buttons.microphone)
		{
			if(this.isMuted)
			{
				this.buttons.microphone.disable();
			}
			else
			{
				this.buttons.microphone.enable();
			}
		}

		this.localUser.setMicrophoneState(!isMuted);
	};

	BX.Call.View.prototype.addUser = function(userId, state)
	{
		if(this.users[userId])
		{
			return;
		}

		this.users[userId] = new CallUser({
			id: userId,
			state : state || BX.Call.UserState.Idle,
			order: this.getNextPosition(),
			onClick: this._onUserClick.bind(this)
		});

		this.updateUserList();
		this.updateButtons();
	};

	BX.Call.View.prototype.setUserState = function(userId, newState)
	{
		/** @type {CallUser} */
		var user = this.users[userId];
		if(!user)
		{
			return;
		}

		user.setState(newState);

		// maybe switch central user
		if(this.centralUser.userId == this.userId && newState == BX.Call.UserState.Connected)
		{
			this.setCentralUser(userId);
		}
		else if(userId == this.centralUser.userId)
		{
			if(newState == BX.Call.UserState.Connecting || newState == BX.Call.UserState.Failed)
			{
				this.centralUser.blurVideo();
			}
			else if (newState == BX.Call.UserState.Connected)
			{
				this.centralUser.blurVideo(false);
			}
			else if (newState == BX.Call.UserState.Idle)
			{
				var usersWithVideo = this.getUsersWithVideo();
				var connectedUsers = this.getConnectedUsers();
				if (usersWithVideo.length > 0)
				{
					this.setCentralUser(usersWithVideo[0]);
				}
				else if (connectedUsers.length > 0)
				{
					this.setCentralUser(connectedUsers[0]);
				}
				else
				{
					this.centralUser.blurVideo();
				}
			}
		}

		this.updateUserList();
		this.updateButtons();
	};

	BX.Call.View.prototype.setTitle = function(title)
	{
		this.title = title;
	};

	BX.Call.View.prototype.setUserTalking = function(userId, talking)
	{
		/** @type {CallUser} */
		var user = this.users[userId];
		if(!user)
		{
			return;
		}

		user.setTalking(talking);
	};

	BX.Call.View.prototype.setUserMicrophoneState = function(userId, isMicrophoneOn)
	{
		/** @type {CallUser} */
		var user = this.users[userId];
		if(!user)
		{
			return;
		}

		user.setMicrophoneState(isMicrophoneOn);
	};

	BX.Call.View.prototype.setLocalStream = function(mediaStream, flipVideo)
	{
		this.localUser.stream = mediaStream;
		this.localUser.flipVideo = !!flipVideo;
		this.setCameraState(this.localUser.hasVideo());

		var videoTracks = mediaStream.getVideoTracks();
		if(videoTracks.length > 0)
		{
			var videoTrackSettings = videoTracks[0].getSettings();
			this.cameraId = videoTrackSettings.deviceId || '';
		}
		else
		{
			this.cameraId = '';
		}

		var audioTracks = mediaStream.getAudioTracks();
		if(audioTracks.length > 0)
		{
			var audioTrackSettings = audioTracks[0].getSettings();
			this.microphoneId = audioTrackSettings.deviceId || '';
		}

		/*if(!this.localUser.hasVideo())
		{
			return false;
		}*/

		if(this.layout == Layouts.Centered && this.centralUser.userId == this.userId)
		{
			if(this.localUser.hasVideo())
			{
				this.centralUser.setStream(mediaStream);
			}
			else
			{
				this.setCentralUser(Object.keys(this.users)[0]);
			}
		}
		else
		{
			this.updateUserList();
		}
	};

	BX.Call.View.prototype.setSpeakerId = function(speakerId)
	{
		if (!('setSinkId' in HTMLMediaElement.prototype))
		{
			console.error("Speaker selection is not supported");
		}

		this.speakerId = speakerId;
		for (var userId in this.elements.audio)
		{
			this.elements.audio[userId].setSinkId(this.speakerId);
		}
	};

	BX.Call.View.prototype.muteSpeaker = function(mute)
	{
		this.speakerMuted = !!mute;

		for (var userId in this.elements.audio)
		{
			this.elements.audio[userId].volume = this.speakerMuted ? 0 : 1;
		}

		if(this.speakerMuted)
		{
			this.buttons.speaker.disable();
			this.buttons.speaker.hideArrow()
		}
		else
		{
			this.buttons.speaker.enable();
			if(BX.Call.Hardware.canSelectSpeaker())
			{
				this.buttons.speaker.showArrow()
			}
		}
	};

	BX.Call.View.prototype.setStream = function(userId, mediaStream)
	{
		if(this.uiState == UiState.Calling)
		{
			this.setUiState(UiState.Connected);
		}

		if(!this.users[userId])
		{
			throw Error("User " + userId + " is not a part of this call");
		}

		if(!this.elements.audio[userId])
		{
			this.elements.audio[userId] = BX.create("audio");
			this.elements.audioContainer.appendChild(this.elements.audio[userId]);
		}

		this.elements.audio[userId].volume = this.speakerMuted ? 0 : 1;

		if(mediaStream.getAudioTracks().length > 0 && mediaStream != this.elements.audio[userId].srcObject)
		{
			if(this.speakerId && this.elements.audio[userId].setSinkId)
			{
				this.elements.audio[userId].setSinkId(this.speakerId).then(function()
				{
					this.elements.audio[userId].srcObject = mediaStream;
					this.elements.audio[userId].play().catch(BX.DoNothing);
				}.bind(this)).catch(console.error);
			}
			else
			{
				this.elements.audio[userId].srcObject = mediaStream;
				this.elements.audio[userId].play().catch(BX.DoNothing);
			}
		}

		this.users[userId].stream = mediaStream;

		if(this.users[userId].hasVideo())
		{
			if(this.centralUser.userId == this.userId)
			{
				this.setCentralUser(userId);
			}
		}
		else
		{
			// no video
			if(this.centralUser.userId == userId)
			{
				var usersWithVideo = this.getUsersWithVideo();
				if(usersWithVideo.length > 0)
				{
					this.setCentralUser(usersWithVideo[0]);
				}
				/*else if (this.localUser.hasVideo())
				{
					this.setCentralUser(this.userId);
				}*/
			}
		}
		if(this.centralUser.userId == userId)
		{
			this.centralUser.setStream(mediaStream);
		}

		this.updateUserList();
	};

	BX.Call.View.prototype.show = function()
	{
		if(!this.elements.root)
		{
			this.render();
		}
		this.container.appendChild(this.elements.root);

		this.updateButtons();
		this.updateUserList();
		this.resumeVideo();

		this.toggleEars();
		this.visible = true;
	};

	BX.Call.View.prototype.hide = function()
	{
		BX.remove(this.elements.root);
		this.visible = false;
	};

	/**
	 * @param {Object} params
	 * @param {string} params.text
	 * @param {string} [params.subText]
	 */
	BX.Call.View.prototype.showMessage = function(params)
	{
		if(!this.elements.root)
		{
			this.render();
			this.container.appendChild(this.elements.root);
		}
		var statusNode = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-status bx-messenger-videocall-user-status-wide"},
		});

		if(BX.type.isNotEmptyString(params.text))
		{
			var textNode = BX.create("div", {
				props: {className: "bx-messenger-videocall-status-text"},
				text: params.text
			});
			statusNode.appendChild(textNode);
		}

		if(this.elements.overlay.childElementCount)
		{
			BX.cleanNode(this.elements.overlay);
		}
		this.elements.overlay.appendChild(statusNode);
	};

	BX.Call.View.prototype.hideMessage = function()
	{
		this.elements.overlay.textContent = '';
	};

	/**
	 * @param {Object} params
	 * @param {string} params.text
	 * @param {string} [params.subText]
	 */
	BX.Call.View.prototype.showFatalError = function(params)
	{
		this.showMessage(params);
		this.setUiState(UiState.Error);
	};

	BX.Call.View.prototype.close = function()
	{
		BX.cleanNode(this.container);

		this.visible = false;
		this.callbacks.onClose();
	};

	BX.Call.View.prototype.setSize = function(size)
	{
		if(this.size == size)
		{
			return;
		}

		this.size = size;

		if(this.size == BX.Call.View.Size.Folded)
		{
			this.elements.panel.classList.add('bx-messenger-videocall-panel-folded');
			BX.remove(this.elements.container);
			this.updateButtons();
		}
		else
		{
			this.elements.panel.classList.remove('bx-messenger-videocall-panel-folded');
			this.elements.wrap.appendChild(this.elements.container);

			this.updateButtons();
			this.updateUserList();
			this.resumeVideo();
		}
	};

	BX.Call.View.prototype.toggleFullScreen = function()
	{
		if(this.isFullScreen)
		{
			this.exitFullScreen();
		}
		else
		{
			this.enterFullScreen();
		}
	};

	BX.Call.View.prototype.isButtonDisabled = function(buttonName)
	{
		return this.disabledButtons.hasOwnProperty(buttonName);
	};

	BX.Call.View.prototype.disableAddUser = function()
	{
		this.disabledButtons['add'] = true;

		if(this.elements.userList.addButton)
		{
			BX.remove(this.elements.userList.addButton);
			this.elements.userList.addButton = null;
		}
	};

	BX.Call.View.prototype.disableSwitchCamera = function()
	{
		this.disabledButtons['camera'] = true;
	};

	BX.Call.View.prototype.enableSwitchCamera = function()
	{
		delete this.disabledButtons['camera'];
	};

	BX.Call.View.prototype.disableScreenSharing = function()
	{
		this.disabledButtons['screen'] = true;
	};

	BX.Call.View.prototype.disableMediaSelection = function()
	{
		this.mediaSelectionBlocked = true;
	};

	BX.Call.View.prototype.isMediaSelectionAllowed = function()
	{
		return this.uiState == UiState.Connected && !this.mediaSelectionBlocked && !this.isFullScreen;
	};

	BX.Call.View.prototype.getButtonList = function()
	{
		if(this.uiState == UiState.Error)
		{
			return ['close'];
		}
		if(this.uiState == UiState.Initializing)
		{
			return ['hangup'];
		}

		if(this.size == BX.Call.View.Size.Folded)
		{
			return ['title', 'hangup', 'fullscreen'];
		}

		var result = [];

		if(this.uiState === UiState.Connected)
		{
			result.push('grid');
		}

		if(this.getConnectedUserCount() < this.userLimit - 1 && !this.isFullScreen && this.uiState === UiState.Connected)
		{
			result.push('add');
		}

		if(false && !this.isFullScreen)
		{
			// todo: enable when ready :)
			result.push('link');
		}

		result.push('microphone');
		if(this.uiState === UiState.Connected)
		{
			result.push('camera');
		}

		result.push('speaker');

		if(this.isScreenSharingSupported() && !this.isFullScreen && this.uiState === UiState.Connected)
		{
			result.push('screen')
		}

		if(this.showChatButtons && !this.isFullScreen)
		{
			result.push('chat', 'history');
		}

		result.push('hangup');
		if(this.isFullScreenSupported())
		{
			result.push('fullscreen');
		}

		result = result.filter(function(buttonName)
		{
			return !this.isButtonDisabled(buttonName);
		}, this);

		return result;
	};

	BX.Call.View.prototype.render = function()
	{
		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall bx-messenger-videocall-centered"},
			children: [
				this.elements.wrap = BX.create("div", {
					props: {className: "bx-messenger-videocall-wrap"},
					children: [
						this.elements.container = BX.create("div", {
							props: {className: "bx-messenger-videocall-inner"},
						}),
						this.elements.overlay = BX.create("div", {
							props: {className: "bx-messenger-videocall-overlay"}
						}),
						this.elements.panel = BX.create("div", {
							props: {className: "bx-messenger-videocall-panel"},
						}),
					]
				})
			],
			events: {
				click: this._onBodyClick.bind(this)
			}
		});

		this.centralUser  = new CentralUser({
			parent: this,
			video: false,
			stream: false,
			userId: this.userId, // Show local user until someone is connected,
			language: this.language
		});

		this.elements.center = this.centralUser.render();

		this.elements.userBlock = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-block"},
			children: [
				this.elements.ear.left = BX.create("div", {
					props: {className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-left"},
					events: {
						mouseenter: this.scrollUserBlockLeft.bind(this),
						mouseleave: this.stopScroll.bind(this)
					}
				}),
				this.elements.ear.right = BX.create("div", {
					props: {className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-right"},
					events: {
						mouseenter: this.scrollUseBlockRight.bind(this),
						mouseleave: this.stopScroll.bind(this)
					}
				})
			]
		});

		this.elements.userList.container = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-list"
			},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-user-extra"}
				})
			],
			events: {
				scroll: this.toggleEars.bind(this)
			}
		});

		this.elements.userList.addButton = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-add"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-user-add-inner"}
				})
			],
			style: {
				order: addButtonPosition
			},
			events: {
				click: this._onAddButtonClick.bind(this)
			}
		});

		if(this.layout == Layouts.Centered)
		{
			this.elements.container.appendChild(this.elements.center);
			this.elements.container.appendChild(this.elements.userBlock);
		}

		return this.elements.root;
	};

	BX.Call.View.prototype.renderUserList = function()
	{
		var containerSize = this.elements.userList.container.getBoundingClientRect();
		var showLocalUser = this.localUser.hasVideo() && (this.layout == Layouts.Grid || this.centralUser.userId != this.userId);
		var userCount = 0;

		for (var userId in this.users)
		{
			/** @type {CallUser} */
			var user = this.users[userId];
			if(this.layout == Layouts.Centered && userId == this.centralUser.userId)
			{
				user.dismount();
				continue;
			}
			if(user.state == BX.Call.UserState.Idle
				|| user.state == BX.Call.UserState.Declined
				|| user.state == BX.Call.UserState.Unavailable
				|| user.state == BX.Call.UserState.Busy
			)
			{
				user.dismount();
				continue;
			}
			user.mount(this.elements.userList.container);
			userCount++;
		}
		if(showLocalUser)
		{
			// using force true to always move self to the end of the list
			this.localUser.mount(this.elements.userList.container, true);
			userCount++;
		}
		else
		{
			this.localUser.dismount();
		}

		if (this.layout == Layouts.Grid)
		{
			var userSize = BX.Call.Util.findBestElementSize(containerSize.width, containerSize.height, userCount);

			this.elements.userList.container.style.setProperty('--grid-user-width', userSize.width + 'px');
			this.elements.userList.container.style.setProperty('--grid-user-height', userSize.height + 'px');
		}

		var showAdd = this.layout == Layouts.Centered && userCount > 0 && !this.isFullScreen && this.uiState === UiState.Connected && !this.isButtonDisabled("add") && this.getConnectedUserCount() < this.userLimit - 1;
		if (showAdd && !this.isFullScreen)
		{
			this.elements.userList.container.appendChild(this.elements.userList.addButton);
		}
		else
		{
			BX.remove(this.elements.userList.addButton);
		}
	};

	/**
	 * @return {Element}
	 */
	BX.Call.View.prototype.renderButtons = function(buttons)
	{
		var panelInner;
		var leftSubPanel;
		var middleSubPanel;
		var rightSubPanel;

		panelInner = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-inner"},
			children: [
				leftSubPanel = BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-block"},
				}),
				middleSubPanel = BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-block"},
				}),
				rightSubPanel = BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-block"},
				}),
			]
		});

		for (var i = 0; i < buttons.length; i++)
		{
			switch (buttons[i])
			{
				case "title":
					this.buttons.title = new TitleButton({
						text: this.title,
						isGroupCall: Object.keys(this.users).length > 1
					});
					leftSubPanel.appendChild(this.buttons.title.render());
					break;
				case "grid":
					this.buttons.grid = new SimpleButton({
						class: "grid",
						text: BX.message("IM_M_CALL_BTN_GRID"),
						onClick: this._onGridButtonClick.bind(this)
					});
					leftSubPanel.appendChild(this.buttons.grid.render());
					break;
				case "add":
					this.buttons.add = new SimpleButton({
						class: "add",
						text: BX.message("IM_M_CALL_BTN_ADD"),
						onClick: this._onAddButtonClick.bind(this)
					});
					leftSubPanel.appendChild(this.buttons.add.render());
					break;
				case "link":
					this.buttons.link = new SimpleButton({
						class: "link",
						text: BX.message("IM_M_CALL_BTN_LINK"),
						onClick: this._onLinkButtonClick.bind(this)
					});
					leftSubPanel.appendChild(this.buttons.link.render());
					break;
				case "microphone":
					this.buttons.microphone = new ButtonWithArrow({
						class: "microphone",
						text: BX.message("IM_M_CALL_BTN_MIC"),
						enabled: !this.isMuted,
						arrowEnabled: this.isMediaSelectionAllowed(),
						onClick: this._onMicrophoneButtonClick.bind(this),
						onArrowClick: this._onMicrophoneArrowClick.bind(this)
					});
					middleSubPanel.appendChild(this.buttons.microphone.render());
					break;
				case "camera":
					this.buttons.camera = new ButtonWithArrow({
						class: "camera",
						text: BX.message("IM_M_CALL_BTN_CAMERA"),
						enabled: this.isCameraOn,
						arrowEnabled: this.isMediaSelectionAllowed(),
						onClick: this._onCameraButtonClick.bind(this),
						onArrowClick: this._onCameraArrowClick.bind(this)
					});
					middleSubPanel.appendChild(this.buttons.camera.render());
					break;
				case "speaker":
					this.buttons.speaker = new ButtonWithArrow({
						class: "speaker",
						text: BX.message("IM_M_CALL_BTN_SPEAKER"),
						enabled: !this.speakerMuted,
						arrowEnabled: BX.Call.Hardware.canSelectSpeaker(),
						onClick: this._onSpeakerButtonClick.bind(this),
						onArrowClick: this._onSpeakerArrowClick.bind(this)
					});
					middleSubPanel.appendChild(this.buttons.speaker.render());
				case "screen":
					if(!this.buttons.screen)
					{
						this.buttons.screen = new SimpleButton({
							class: "screen",
							text: BX.message("IM_M_CALL_BTN_SCREEN"),
							onClick: this._onScreenButtonClick.bind(this)
						});
					}
					middleSubPanel.appendChild(this.buttons.screen.render());
					break;
				case "chat":
					this.buttons.chat = new SimpleButton({
						class: "chat",
						text: BX.message("IM_M_CALL_BTN_CHAT"),
						onClick: this._onChatButtonClick.bind(this)
					});
					middleSubPanel.appendChild(this.buttons.chat.render());
					break;
				case "history":
					this.buttons.history = new SimpleButton({
						class: "history",
						text: BX.message("IM_M_CALL_BTN_HISTORY"),
						onClick: this._onHistoryButtonClick.bind(this)
					});
					middleSubPanel.appendChild(this.buttons.history.render());
					break;
				case "hangup":
					this.buttons.hangup = new HangupButton({
						text: Object.keys(this.users).length > 1 ?  BX.message("IM_M_CALL_BTN_DISCONNECT") : BX.message("IM_M_CALL_BTN_HANGUP"),
						onClick: this._onHangupButtonClick.bind(this)
					});
					if(this.uiState == UiState.Initializing)
					{
						middleSubPanel.appendChild(this.buttons.hangup.render());
					}
					else
					{
						rightSubPanel.appendChild(this.buttons.hangup.render());
					}
					break;
				case "close":
					this.buttons.close = new HangupButton({
						text: BX.message("IM_M_CALL_BTN_CLOSE"),
						onClick: this._onCloseButtonClick.bind(this)
					});
					middleSubPanel.appendChild(this.buttons.close.render());
					break;
				case "fullscreen":
					this.buttons.fullscreen = new SimpleButton({
						class: "resize",
						text: "",
						onClick: this._onFullScreenButtonClick.bind(this)
					});
					rightSubPanel.appendChild(this.buttons.fullscreen.render());
					break;
			}
		}

		return panelInner;
	};

	BX.Call.View.prototype.setButtonActive = function(buttonName, isActive)
	{
		if(!this.buttons[buttonName])
		{
			return;
		}

		this.buttons[buttonName].setActive(isActive);
	};

	BX.Call.View.prototype.setButtonCounter = function(buttonName, counter)
	{
		if(!this.buttons[buttonName])
		{
			return;
		}

		this.buttons[buttonName].setCounter(counter);
	};

	BX.Call.View.prototype.updateUserList = function()
	{
		this.renderUserList();

		if (this.layout == Layouts.Centered)
		{
			if(!this.elements.userList.container.parentElement)
			{
				this.elements.userBlock.appendChild(this.elements.userList.container);
			}
			this.centralUser.setFullSize(this.elements.userList.container.childElementCount === 0);

		}
		else if (this.layout == Layouts.Grid)
		{
			if(!this.elements.userList.container.parentElement)
			{
				this.elements.container.appendChild(this.elements.userList.container);
			}
			this.elements.root.className = "bx-messenger-videocall bx-messenger-videocall-grid";
		}
		this.toggleEars();
	};

	BX.Call.View.prototype.resumeVideo = function()
	{
		for (var userId in this.users)
		{
			/** @type {CallUser} */
			var user = this.users[userId];
			user.playVideo()
		}
		this.localUser.playVideo(true);
		if(this.layout == Layouts.Centered)
		{
			this.centralUser.playVideo();
		}
	};

	BX.Call.View.prototype.updateButtons = function()
	{
		var buttons = this.getButtonList();
		BX.cleanNode(this.elements.panel);
		this.elements.panel.appendChild(this.renderButtons(buttons))
	};

	BX.Call.View.prototype.isScreenSharingSupported = function()
	{
		return navigator.mediaDevices && typeof(navigator.mediaDevices.getDisplayMedia) === "function" || typeof(BXDesktopSystem) !== "undefined";
	};

	BX.Call.View.prototype.isFullScreenSupported = function()
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
		{
			return document.webkitFullscreenEnabled === true;
		}
		else if (BX.browser.IsFirefox())
		{
			return document.fullscreenEnabled === true;
		}
		else
		{
			return false;
		}
	};

	BX.Call.View.prototype.enterFullScreen = function()
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
		{
			this.elements.root.webkitRequestFullScreen();
		}
		else if (BX.browser.IsFirefox())
		{
			this.elements.root.requestFullscreen();
		}
	};

	BX.Call.View.prototype.exitFullScreen = function()
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
	};

	BX.Call.View.prototype.toggleEars = function()
	{
		this.toggleRightEar();
		this.toggleLeftEar();
	};

	BX.Call.View.prototype.toggleRightEar = function()
	{
		if (
			this.layout == Layouts.Centered
			&& this.elements.userList.container.scrollWidth > this.elements.userList.container.offsetWidth
			&& (this.elements.userList.container.offsetWidth + this.elements.userList.container.scrollLeft) < this.elements.userList.container.scrollWidth
	  	   )
		{
			this.elements.ear.right.classList.add("bx-messenger-videocall-ear-show");
		}
		else
		{
			this.elements.ear.right.classList.remove("bx-messenger-videocall-ear-show");
		}
	};

	BX.Call.View.prototype.toggleLeftEar = function()
	{
		if (
			this.layout == Layouts.Centered
			&& this.elements.userList.container.scrollLeft > 0
		   )
		{
			this.elements.ear.left.classList.add("bx-messenger-videocall-ear-show");
		}
		else
		{
			this.elements.ear.left.classList.remove("bx-messenger-videocall-ear-show");
		}
	};

	BX.Call.View.prototype.scrollUserBlockLeft = function()
	{
		this.stopScroll();
		this.scrollInterval = setInterval(
			function()
			{
				this.elements.userList.container.scrollLeft -= 10;
			}.bind(this),
			20
		);
	};

	BX.Call.View.prototype.scrollUseBlockRight = function()
	{
		this.stopScroll();
		this.scrollInterval = setInterval(
			function()
			{
				this.elements.userList.container.scrollLeft += 10;
			}.bind(this),
			20
		);
	};

	BX.Call.View.prototype.stopScroll = function()
	{
		if(this.scrollInterval)
		{
			clearInterval(this.scrollInterval);
			this.scrollInterval = 0;
		}
	};

	// event handlers

	BX.Call.View.prototype._onBodyClick = function(e)
	{
		this.callbacks.onBodyClick();
	};

	BX.Call.View.prototype._onFullScreenChange = function(e)
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
		{
			this.isFullScreen = (document.webkitFullscreenElement == this.elements.root);
		}
		else if (BX.browser.IsFirefox())
		{
			this.isFullScreen = (document.fullscreenElement == this.elements.root);
		}

		this.updateUserList();
		this.updateButtons();
	};

	BX.Call.View.prototype._onResize = function()
	{
		if(this.centralUser)
		{
			this.centralUser.updateAvatarWidth();
		}
		this.toggleEars();
		if(this.layout == Layouts.Grid)
		{
			this.updateUserList();
		}
	};

	BX.Call.View.prototype._onUserClick = function(e)
	{
		var userId = e.userId;

		if(userId == this.userId)
		{
			return;
		}

		this.setCentralUser(userId);
		if(this.layout == Layouts.Grid)
		{
			this.setLayout(Layouts.Centered);
		}
	};

	BX.Call.View.prototype._onGridButtonClick = function(e)
	{
		this.setLayout(this.layout == Layouts.Centered ? Layouts.Grid : Layouts.Centered);
	};

	BX.Call.View.prototype._onAddButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: "inviteUser",
			node: e.currentTarget
		});
	};

	BX.Call.View.prototype._onLinkButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: "settings",
			node: e.currentTarget
		});
	};

	BX.Call.View.prototype._onMicrophoneButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: "toggleMute",
			muted: !this.isMuted
		});
	};

	BX.Call.View.prototype._onMicrophoneArrowClick = function(e)
	{
		e.stopPropagation();

		DeviceSelector.create({
			parentElement: e.currentTarget,
			deviceList: BX.Call.Hardware.getMicrophoneList(),
			current: this.microphoneId,
			onSelect: this._onMicrophoneSelected.bind(this)
		}).show();
	};

	BX.Call.View.prototype._onMicrophoneSelected = function(deviceInfo)
	{
		if(deviceInfo.deviceId === this.microphoneId)
		{
			return;
		}

		this.callbacks.onReplaceMicrophone(deviceInfo);
	};

	BX.Call.View.prototype._onCameraButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: "toggleVideo",
			video: !this.isCameraOn
		});
	};

	BX.Call.View.prototype._onCameraArrowClick = function(e)
	{
		e.stopPropagation();
		if(!BX.Call.Hardware.hasCamera())
		{
			return false;
		}

		DeviceSelector.create({
			parentElement: e.currentTarget,
			deviceList: BX.Call.Hardware.getCameraList(),
			current: this.cameraId,
			onSelect: this._onCameraSelected.bind(this)
		}).show();
	};

	BX.Call.View.prototype._onCameraSelected = function(deviceInfo)
	{
		if(deviceInfo.deviceId === this.cameraId)
		{
			return;
		}

		this.callbacks.onReplaceCamera(deviceInfo);
	};

	BX.Call.View.prototype._onSpeakerButtonClick = function(e)
	{
		this.muteSpeaker(!this.speakerMuted);
	};

	BX.Call.View.prototype._onSpeakerArrowClick = function(e)
	{
		e.stopPropagation();

		DeviceSelector.create({
			parentElement: e.currentTarget,
			deviceList: BX.Call.Hardware.getSpeakerList(),
			current: this.speakerId,
			onSelect: this._onSpeakerSelected.bind(this)
		}).show();
	};

	BX.Call.View.prototype._onSpeakerSelected = function(deviceInfo)
	{
		this.setSpeakerId(deviceInfo.deviceId);
	};

	BX.Call.View.prototype._onScreenButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: 'toggleScreenSharing',
			node: e.target
		});
	};

	BX.Call.View.prototype._onChatButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: 'showChat',
			node: e.target
		});
	};

	BX.Call.View.prototype._onHistoryButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: 'showHistory',
			node: e.target
		});
	};

	BX.Call.View.prototype._onHangupButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: 'hangup',
			node: e.target
		});
	};

	BX.Call.View.prototype._onCloseButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: 'close',
			node: e.target
		});
	};

	BX.Call.View.prototype._onFullScreenButtonClick = function(e)
	{
		e.stopPropagation();
		this.callbacks.onButtonClick({
			buttonName: 'fullscreen',
			node: e.target
		});
	};

	BX.Call.View.prototype.releaseLocalMedia = function()
	{
		this.localUser.releaseStream();
		if(this.centralUser.userId == this.userId)
		{
			this.centralUser.releaseStream();
		}
	};

	BX.Call.View.prototype.destroy = function()
	{
		if(this.elements.root)
		{
			BX.cleanNode(this.elements.root, true);
			this.elements.root = null;
		}
		this.visible = false;

		window.removeEventListener("webkitfullscreenchange", this._onFullScreenChangeHandler);
		window.removeEventListener("mozfullscreenchange", this._onFullScreenChangeHandler);
		window.removeEventListener("resize", this._onResizeHandler);

		for(var userId in this.users)
		{
			if(this.users.hasOwnProperty(userId))
			{
				this.users[userId].destroy();
			}
		}

		this.centralUser.destroy();

		this.callbacks.onDestroy();
	};

	var CentralUser = function (config)
	{
		this.parent = config.parent;
		this.stream = config.stream || null;
		this.userId = config.userId;
		this.language = config.language;

		this.hasVideo = false;

		this.elements = {
			container: null,
			inner: null,
			watermark: null,
			video: null,
			user:null,
			userBlock: null,
			avatar: null,
			nameBlock: null,
			name: null
		};
		this.loader = null;

		this.checkAspectInterval = setInterval(this.checkVideoAspect.bind(this), 500);
	};

	CentralUser.prototype.getWatermarkUrl = function(language)
	{
		switch (language)
		{
			case 'ua':
				return '/bitrix/js/im/images/watermark-white-ua.svg';
			case 'ru':
			case 'kz':
			case 'by':
				return '/bitrix/js/im/images/watermark-white-ru.svg';
			default:
				return '/bitrix/js/im/images/watermark-white-en.svg';
		}
	};

	CentralUser.prototype.render = function()
	{
		this.elements.container = BX.create("div", {
			props: {className: "bx-messenger-videocall-video-block"},
			children: [
				this.elements.watermark = BX.create("div", {
					props: {className: "bx-messenger-videocall-watermark"},
					children: [
						BX.create("img", {
							props: {
								className: "bx-messenger-videocall-watermark-img",
								src: this.getWatermarkUrl(this.language)
							},
						})
					]
				}),
			]
		});

		this.elements.video = BX.create("video", {
			props: {
				className: "bx-messenger-videocall-video",
				autoplay: true,
				volume: 0
			}
		});

		this.elements.user = BX.create("div", {
			props: {className: "bx-messenger-audiocall"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-audiocall-wrap"},
					children: [
						this.elements.inner = BX.create("div", {
							props: {className: "bx-messenger-audiocall-inner"},
							children: [
								this.elements.userBlock = BX.create("div", {
									props: {className: "bx-messenger-audiocall-user-block"},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-audiocall-user-block-inner"},
											children: [
												BX.create("div", {
													props: {className: "bx-messenger-audiocall-user"},
													children: [
														this.elements.avatar = BX.create("div", {
															props: {className: "bx-messenger-audiocall-user-item"},
														})
													]
												})
											]
										})
									]
								}),
								this.elements.nameBlock = BX.create("div", {
									props: {className: "bx-messenger-audiocall-user-name"},
									children: [
										this.elements.name = BX.create("span", {
											props: {className: "bx-messenger-audiocall-user-link"},
										})
									]
								})
							]
						})
					]
				})
			]
		});

		if(this.stream && BX.Call.Util.containsVideoTrack(this.stream))
		{
			this.elements.container.appendChild(this.elements.video);
			this.elements.container.classList.remove("bx-messenger-videocall-audio");
			if(this.userId == this.parent.userId && this.parent.localUser.flipVideo)
			{
				this.elements.video.classList.add("bx-messenger-videocall-video-flipped")
			}
			else
			{
				this.elements.video.classList.remove("bx-messenger-videocall-video-flipped")
			}
		}
		else
		{
			this.elements.container.classList.add("bx-messenger-videocall-audio");
		}

		this.loader = new BX.Loader({
			target: this.elements.container
		});

		this.updateUserInfo();
		return this.elements.container;
	};

	CentralUser.prototype.blurVideo = function(blur)
	{
		blur = blur !== false;

		if(blur)
		{
			this.elements.video.pause();
			this.elements.video.classList.add("bx-messenger-videocall-video-blurred");
			this.loader.show();
		}
		else
		{
			this.elements.video.play().catch(BX.DoNothing);
			this.elements.video.classList.remove("bx-messenger-videocall-video-blurred");
			this.loader.hide();
		}
	};

	CentralUser.prototype.updateUserInfo = function()
	{
		var self = this;
		var userId = this.userId;
		BX.Call.Util.getUserName(userId).then(function(name)
		{
			if(self.userId == userId)
			{
				self.elements.name.innerText = name;
			}
			return BX.Call.Util.getUserAvatar(self.userId);
		}).then(function (avatar)
		{
			if (avatar != '')
			{
				self.elements.avatar.style.backgroundImage = "url('" + avatar + "')";
			}
			else
			{
				self.elements.avatar.style.removeProperty("background-image");
			}
			self.updateAvatarWidth();
		});
	};

	CentralUser.prototype.updateAvatarWidth = function()
	{
		this.elements.userBlock.style.maxWidth = (this.elements.inner.offsetHeight - this.elements.nameBlock.offsetHeight) + 'px';
	};

	CentralUser.prototype.setUserId = function(userId)
	{
		if (this.userId == userId)
		{
			return;
		}

		if (userId == this.parent.userId && this.parent.localUser.flipVideo)
		{
			this.setStream(this.parent.localUser.stream);
			this.elements.video.classList.add("bx-messenger-videocall-video-flipped");
		}
		else
		{
			this.setStream(this.parent.users[userId].stream);
			this.elements.video.classList.remove("bx-messenger-videocall-video-flipped");
		}

		this.userId = userId;
		this.updateUserInfo();
	};

	CentralUser.prototype.setStream = function(stream)
	{
		this.stream = stream;
		var hasVideo = BX.Call.Util.containsVideoTrack(stream);

		if(this.stream)
		{
			if(this.hasVideo && !hasVideo)
			{
				BX.remove(this.elements.video);
				this.elements.container.appendChild(this.elements.user);
				this.elements.container.classList.add("bx-messenger-videocall-audio");
				this.updateAvatarWidth();
			}
			else if(!this.hasVideo && hasVideo)
			{
				BX.remove(this.elements.user);
				this.elements.container.appendChild(this.elements.video);
				this.elements.container.classList.remove("bx-messenger-videocall-audio");
			}
		}
		else
		{
			BX.remove(this.elements.video);
			this.elements.container.appendChild(this.elements.user);
			this.elements.container.classList.add("bx-messenger-videocall-audio");
			this.updateAvatarWidth();
		}

		this.hasVideo = hasVideo;
		if(this.hasVideo)
		{
			if (this.elements.video.srcObject != stream)
			{
				this.elements.video.srcObject = stream;
			}
			this.blurVideo(false);
			if(this.userId == this.parent.userId && this.parent.localUser.flipVideo)
			{
				this.elements.video.classList.add("bx-messenger-videocall-video-flipped")
			}
			else
			{
				this.elements.video.classList.remove("bx-messenger-videocall-video-flipped")
			}
		}
	};

	CentralUser.prototype.playVideo = function()
	{
		if(this.elements.video)
		{
			this.elements.video.play().catch(BX.DoNothing);
		}
	};

	CentralUser.prototype.releaseStream = function()
	{
		this.elements.video.srcObject = null;
		this.stream = null;
	};

	CentralUser.prototype.setFullSize = function(fullSize)
	{
		if(fullSize)
		{
			this.elements.container.classList.add('bx-messenger-videocall-video-block-full');
		}
		else
		{
			this.elements.container.classList.remove('bx-messenger-videocall-video-block-full');
		}
		this.updateAvatarWidth();
	};

	CentralUser.prototype.isVideo = function()
	{

	};

	CentralUser.prototype.checkVideoAspect = function()
	{
		if(!this.elements.video)
		{
			return;
		}

		if(this.elements.video.videoHeight > this.elements.video.videoWidth)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-vertical");
		}
		else
		{
			this.elements.video.classList.remove("bx-messenger-videocall-video-vertical");
		}
	};

	CentralUser.prototype.destroy = function()
	{
		this.stream = null;
		if(this.loader)
		{
			this.loader.destroy();
			this.loader = null;
		}
		clearInterval(this.checkAspectInterval);
	};

	var CallUser = function(config)
	{
		this.id = config.id;
		this.name = '';
		this.avatar = '';
		this.state = config.state;
		this.order = config.order;

		this._stream = config.stream;
		Object.defineProperty(this, "stream", {
			get: function()
			{
				return this._stream;
			},
			set: function(stream)
			{
				this._stream = stream;
				this.update()
			}
		});
		this._flipVideo = false;
		Object.defineProperty(this, "flipVideo", {
			get: function()
			{
				return this._flipVideo;
			},
			set: function(flipVideo)
			{
				this._flipVideo = flipVideo;
				this.update()
			}
		});

		this.talking = false;
		this.isMicrophoneOn = config.isMicrophoneOn !== false;

		this.localUser = config.localUser === true;
		this.hidden = false;

		this.elements = {
			root: null,
			container: null,
			videoContainer: null,
			video: null,
			videoBorder: null,
			avatar: null,
			nameContainer: null,
			name: null,
			overlay: null,
			state: null,
			removeButton: null,
			micState: null
		};

		this.callBacks = {
			onClick: BX.type.isFunction(config.onClick) ?  config.onClick : BX.DoNothing
		};

		this.checkAspectInterval = setInterval(this.checkVideoAspect.bind(this), 500);
	};

	CallUser.prototype.render = function()
	{
		var self = this;
		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-user"},
			dataset: {userId: this.id, order: this.order},
			children: [
				this.elements.container = BX.create("div", {
					props: {className: "bx-messenger-videocall-user-inner"},
				}),
			],
			style: {
				order: this.order
			},
			events: {
				click: function(e)
				{
					e.stopPropagation();
					this.callBacks.onClick({
						userId: this.id
					});
				}.bind(this)
			}
		});

		if (this.talking)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-talking");
		}

		if (this.localUser)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-self");
		}

		this.elements.avatar = BX.create("div", {
			props: {className: "bx-message-videocall-user-avatar"}
		});

		BX.Call.Util.getUserAvatar(this.id).then(function(avatar)
		{
			if (avatar != '')
			{
				self.elements.avatar.style.backgroundImage = "url('" + avatar + "')";
			}
			else
			{
				self.elements.avatar.style.removeProperty("background-image");
			}
		});

		if(!this.hasVideo())
		{
			this.elements.container.appendChild(this.elements.avatar);
		}

		this.elements.videoContainer = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-video-container",
			},
			children: [
				this.elements.video = BX.create("video", {
					props: {
						className: "bx-messenger-videocall-video",
						volume: 0,
						autoplay: true
					}
				}),
				this.elements.videoBorder = BX.create("div", {
					props: {
						className: "bx-messenger-videocall-video-border",
					}
				})
			]
		});
		this.elements.container.appendChild(this.elements.videoContainer);

		if(this.stream && this.stream.active)
		{
			this.elements.video.srcObject = this.stream;
		}
		if(this.flipVideo)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-flipped");
		}

		this.elements.overlay = BX.create("div", {props: {className: "bx-messenger-videocall-overlay"}});
		this.elements.root.appendChild(this.elements.overlay);

		this.elements.state = this.renderState();
		if(this.elements.state)
		{
			this.elements.overlay.appendChild(this.elements.state);
		}

		this.elements.nameContainer = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-name"},
			children: [
				this.elements.name = BX.create("span", {
					props: {className: "bx-messenger-videocall-user-text"},
					text: name
				}),
				BX.create("div", {
					props: {className: "bx-messenger-videocall-user-shadow"}
				})
			]
		});
		this.elements.videoContainer.appendChild(this.elements.nameContainer);

		this.elements.micState = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-mic-state" + (this.isMicrophoneOn ? " hidden" : "")},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-user-mic-state-icon"},
				}),
			]
		});
		this.elements.nameContainer.insertBefore(this.elements.micState, this.elements.name);

		BX.Call.Util.getUserName(this.id).then(function(name)
		{
			if (name != '')
			{
				self.elements.name.innerText = name;
			}
		});

		// todo: show button only if user have the permission to remove user
		/*this.elements.removeButton = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-close"}
		});

		this.elements.container.appendChild(this.elements.removeButton);*/

		return this.elements.root;
	};

	CallUser.prototype.update = function()
	{
		if(!this.elements.root)
		{
			return;
		}
		if(this.hasVideo())
		{
			if(this.elements.video.srcObject != this.stream)
			{
				this.elements.video.srcObject = this.stream;
			}
			BX.remove(this.elements.avatar);
			if(this.flipVideo)
			{
				this.elements.video.classList.add("bx-messenger-videocall-video-flipped");
			}
			else
			{
				this.elements.video.classList.remove("bx-messenger-videocall-video-flipped");
			}
		}
		else
		{
			this.elements.video.srcObject = null;
			this.elements.container.appendChild(this.elements.avatar);
		}
	};

	CallUser.prototype.playVideo = function()
	{
		if(this.elements.video)
		{
			this.elements.video.play().catch(BX.DoNothing);
		}
	};

	CallUser.prototype.renderState = function()
	{
		var stateNode;

		switch (this.state)
		{
			case BX.Call.UserState.Idle:
				// We should never get here. Idle users are not to be painted
				break;
			case BX.Call.UserState.Calling:
				stateNode = BX.create("div", {
					props: {className: "bx-messenger-videocall-user-status"},
					children: [
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-pic"},
							children: [
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-dot"}}),
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-dot"}}),
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-dot"}})
							]
						}),
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-text"},
							text: BX.message("IM_M_CALL_STATUS_WAIT_ANSWER")
						})
					]
				});
				break;
			case BX.Call.UserState.Declined:
				stateNode = BX.create("div", {
					props: {className: "bx-messenger-videocall-user-status bx-messenger-videocall-user-status-wide"},
					children: [
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-pic"},
							children: [
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-cross"}}),
							]
						}),
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-text"},
							text: BX.message("IM_M_CALL_STATUS_DECLINED")
						})
					]
				});
				break;
			case BX.Call.UserState.Ready:
			case BX.Call.UserState.Connecting:
				stateNode = BX.create("div", {
					props: {className: "bx-messenger-videocall-user-status"},
					children: [
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-pic"},
							children: [
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-dot"}}),
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-dot"}}),
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-dot"}})
							]
						}),
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-text"},
							text: BX.message("IM_M_CALL_STATUS_WAIT_CONNECT")
						})
					]
				});
				break;
			case BX.Call.UserState.Connected:
				break;
			case BX.Call.UserState.Failed:
			case BX.Call.UserState.Unavailable:
				stateNode = BX.create("div", {
					props: {className: "bx-messenger-videocall-user-status bx-messenger-videocall-user-status-wide"},
					children: [
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-pic"},
							children: [
								BX.create("span", {props: {className: "bx-messenger-videocall-user-status-cross"}}),
							]
						}),
						BX.create("span", {
							props: {className: "bx-messenger-videocall-user-status-text"},
							text: this.state == BX.Call.UserState.Failed ? BX.message("IM_M_CALL_STATUS_CONNECTION_ERROR") : BX.message("IM_M_CALL_STATUS_UNAVAILABLE")
						})
					]
				});
				break;
		}

		return stateNode ? stateNode : null;
	};

	CallUser.prototype.mount = function(parent, force)
	{
		force = force === true;
		if(!this.elements.root)
		{
			this.render();
		}

		if(this.isMounted() && !force)
		{
			return false;
		}

		parent.appendChild(this.elements.root);
		this.update();
	};

	CallUser.prototype.dismount = function()
	{
		if(!this.isMounted())
		{
			return false;
		}

		this.elements.video.srcObject = null;
		BX.remove(this.elements.root);
	};

	CallUser.prototype.isMounted = function()
	{
		return !!(this.elements.root && this.elements.root.parentElement);
	};

	CallUser.prototype.setState = function(newState)
	{
		if(this.state == newState)
		{
			return;
		}

		this.state = newState;

		if(!this.elements.root)
		{
			// not rendered yet
			return;
		}

		if(this.elements.state)
		{
			BX.cleanNode(this.elements.overlay);
			this.elements.state = null;
		}

		this.elements.state = this.renderState();
		if(this.elements.state)
		{
			this.elements.overlay.appendChild(this.elements.state);
		}
	};

	CallUser.prototype.setTalking = function(talking)
	{
		if(this.talking == talking)
		{
			return;
		}

		this.talking = talking;
		if(!this.elements.root)
		{
			return;
		}
		if(this.talking)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-talking");
		}
		else
		{
			this.elements.root.classList.remove("bx-messenger-videocall-user-talking");
		}
	};

	CallUser.prototype.setMicrophoneState = function(isMicrophoneOn)
	{
		if(this.isMicrophoneOn == isMicrophoneOn)
		{
			return;
		}
		this.isMicrophoneOn = isMicrophoneOn;
		if(!this.elements.root)
		{
			return;
		}
		if(this.isMicrophoneOn)
		{
			this.elements.micState.classList.add("hidden");
		}
		else
		{
			this.elements.micState.classList.remove("hidden");
		}
	};

	CallUser.prototype.hide = function()
	{
		if(!this.elements.root)
		{
			return;
		}

		this.elements.root.dataset.hidden = 1;
	};

	CallUser.prototype.show = function()
	{
		if(!this.elements.root)
		{
			return;
		}

		delete this.elements.root.dataset.hidden;
	};

	CallUser.prototype.hasVideo = function()
	{
		return this.state == BX.Call.UserState.Connected && BX.Call.Util.containsVideoTrack(this.stream);
	};

	CallUser.prototype.checkVideoAspect = function()
	{
		if(!this.elements.video)
		{
			return;
		}

		if(this.elements.video.videoHeight > this.elements.video.videoWidth)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-vertical");
		}
		else
		{
			this.elements.video.classList.remove("bx-messenger-videocall-video-vertical");
		}
	};

	CallUser.prototype.releaseStream = function()
	{
		if(this.elements.video)
		{
			this.elements.video.srcObject = null;
		}
		this.stream = null;
	};


	CallUser.prototype.destroy = function()
	{
		this.releaseStream();
		clearInterval(this.checkAspectInterval);
	};

	var TitleButton = function(config)
	{
		this.elements = {
			root: null
		};

		this.text = BX.type.isNotEmptyString(config.text) ? config.text : '';
		this.isGroupCall = config.isGroupCall;
	};

	TitleButton.prototype.render = function()
	{
		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-title"},
			html: this.getTitle(this.text)
		});

		return this.elements.root;
	};

	TitleButton.prototype.getTitle = function(name)
	{
		var prettyName = '<span class="bx-messenger-videocall-panel-title-name">' + this.text + '</span>';

		if(this.isGroupCall)
		{
			return BX.message("IM_M_GROUP_CALL_WITH").replace("#CHAT_NAME#", prettyName);
		}
		else
		{
			return BX.message("IM_M_CALL_WITH").replace("#USER_NAME#", prettyName);
		}
	};

	var SimpleButton = function(config)
	{
		this.class = config.class;
		this.text = BX.type.isNotEmptyString(config.text) ? config.text : '';
		this.isActive = false;
		this.counter = config.counter || 0;

		this.elements = {
			root: null,
			counter: null,
			counterValue: null
		};

		this.callbacks = {
			onClick: BX.type.isFunction(config.onClick) ? config.onClick : BX.DoNothing
		}
	};

	SimpleButton.prototype.render = function()
	{
		if(this.elements.root)
		{
			return this.elements.root;
		}

		var textNode;
		if(this.text !== '')
		{
			textNode = BX.create("div", {props: {className: "bx-messenger-videocall-panel-text"}, text: this.text});
		}
		else
		{
			textNode = null;
		}

		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-item"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-icon bx-messenger-videocall-panel-icon-" + this.class},
					children: [
						this.elements.counter = BX.create("span", {
							props: {className: "bx-messenger-cl-count"},
							style: {
								display: this.counter > 0 ? "inline-block" : "none",
								position: "absolute",
								right: "-19px",
								top: "-13px"
							},
							children: [
								this.elements.counterValue = BX.create("span", {
									props: {className: "bx-messenger-cl-count-digit"},
									text: this.counter
								})
							]
						}),
					]
				}),

				textNode
			],
			events: {
				click: this.callbacks.onClick
			}
		});
		if(this.isActive)
		{
			this.elements.root.classList.add("active");
		}

		return this.elements.root;
	};

	SimpleButton.prototype.setActive = function (isActive)
	{
		if(this.isActive == isActive)
		{
			return;
		}
		this.isActive = isActive;
		if (!this.elements.root)
		{
			return;
		}
		if(this.isActive)
		{
			this.elements.root.classList.add("active");
		}
		else
		{
			this.elements.root.classList.remove("active");
		}
	};

	SimpleButton.prototype.setCounter = function (counter)
	{
		this.counter = counter;
		if(this.counter == 0)
		{
			this.elements.counter.style.display = "none";
		}
		else
		{
			this.elements.counter.style.removeProperty("display");
		}
		this.elements.counterValue.innerText = counter;
	};

	var HangupButton = function (config)
	{
		this.text = config.text;

		this.elements = {
			root: null
		};

		this.callbacks = {
			onClick: BX.type.isFunction(config.onClick) ? config.onClick : BX.DoNothing
		}
	};

	HangupButton.prototype.render = function()
	{
		if(this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-item bx-messenger-videocall-panel-item-btn"},
			children: [
				BX.create("button", {
					props: {className: "ui-btn ui-btn-round bx-messenger-videocall-panel-btn ui-btn-icon-phone-down"},
					text: this.text
				})
			],
			events: {
				click: this.callbacks.onClick
			}
		});
		return this.elements.root;
	};


	var ButtonWithArrow = function(config)
	{
		this.class = config.class;
		this.text = config.text;

		this.enabled = (config.enabled == true);
		this.arrowEnabled = (config.arrowEnabled == true);

		this.elements = {
			root: null,
			icon: null,
			arrow: null
		};

		this.callbacks = {
			onClick: BX.type.isFunction(config.onClick) ? config.onClick : BX.DoNothing,
			onArrowClick: BX.type.isFunction(config.onArrowClick) ? config.onArrowClick : BX.DoNothing
		}
	};

	ButtonWithArrow.prototype.render = function()
	{
		if(this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-item"},
			children: [
				this.elements.icon = BX.create("div", {
					props: {className: this.getIconClass()},
				}),
				BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-text"},
					text: this.text
				})
			],
			events: {
				click: this.callbacks.onClick
			}
		});

		this.elements.arrow = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-arrow"},
			events: {
				click: function(e)
				{
					this.callbacks.onArrowClick.apply(this, arguments);
					e.stopPropagation();
				}.bind(this)
			}
		});

		if(this.arrowEnabled)
		{
			this.elements.icon.appendChild(this.elements.arrow);
		}

		return this.elements.root;
	};

	ButtonWithArrow.prototype.getIconClass = function()
	{
		return "bx-messenger-videocall-panel-icon bx-messenger-videocall-panel-icon-" + this.class + (this.enabled ? "" : "-off");
	};

	ButtonWithArrow.prototype.enable = function()
	{
		if(this.enabled)
		{
			return;
		}
		this.enabled = true;
		this.elements.icon.className = this.getIconClass();
	};

	ButtonWithArrow.prototype.disable = function()
	{
		if(!this.enabled)
		{
			return;
		}
		this.enabled = false;
		this.elements.icon.className = this.getIconClass();
	};

	ButtonWithArrow.prototype.showArrow = function()
	{
		if(this.arrowEnabled)
		{
			return;
		}
		this.arrowEnabled = true;
		this.elements.icon.appendChild(this.elements.arrow);
	};

	ButtonWithArrow.prototype.hideArrow = function()
	{
		if(!this.arrowEnabled)
		{
			return;
		}
		this.arrowEnabled = false;
		this.elements.icon.removeChild(this.elements.arrow);
	};

	var DeviceSelector = function(config)
	{
		this.deviceList = config.deviceList;
		this.current = config.current;
		this.parentElement = config.parentElement;

		this.menu = null;

		this.callbacks = {
			onSelect: BX.type.isFunction(config.onSelect) ? config.onSelect : BX.DoNothing
		}
	};

	DeviceSelector.create = function(config)
	{
		return new DeviceSelector(config);
	};

	DeviceSelector.prototype.show = function()
	{
		var self = this;
		var menuItems = [];

		this.deviceList.forEach(function(deviceInfo)
		{
			menuItems.push({
				id: deviceInfo.deviceId,
				text: deviceInfo.label || "(" + BX.message("IM_M_CALL_DEVICE_NO_NAME") + ")",
				className: (self.current == deviceInfo.deviceId ?  "menu-popup-item-accept" : "device-selector-empty"),
				onclick: function()
				{
					self.menu.close();
					self.callbacks.onSelect(deviceInfo);
				}
			})
		});

		this.menu = BX.PopupMenu.create(
			'call-view-select-device',
			this.parentElement,
			menuItems,
			{
				autoHide: true,
				zIndex: BX.MessengerCommon.getDefaultZIndex() + 500,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				bindOptions: {
					position: 'top'
				},
				angle: {position: "bottom"},
				overlay: {
					backgroundColor: 'white',
					opacity: 0
				},
				events: {
					onPopupClose : function()
					{
						self.menu.popupWindow.destroy();
						BX.PopupMenu.destroy('call-view-select-device');
					},
					onPopupDestroy: function ()
					{
						self.menu = null;
					}
				}
			}
		);
		this.menu.popupWindow.show();
	};

	BX.Call.View.Layout = Layouts;

	BX.Call.View.Size = {
		Folded: 'folded',
		Full: 'full'
	};

	BX.Call.View.UiState = UiState;


})();
