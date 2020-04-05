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
		Calling: 1,
		Connected: 2,
		Error: 3
	};

	var zIndexBase = 1200;

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
		this.showChatButtons = (config.showChatButtons === true);

		this.language = config.language || '';

		this.cameraList = [];
		this.microphoneList = [];

		this.userLimit = config.userLimit || 1;
		this.userId = BX.message('USER_ID');
		this.localUser = new CallUser({
			id: this.userId,
			state: BX.Call.UserState.Connected,
			localUser: true
		});

		this.elements = {
			root: null,
			container: null,
			overlay: null,
			panel: null,
			audioContainer: null,
			audio: {
				// userId: <audio> for this user's stream
			},
			center: {
				container: null,
				video: null
			},
			userBlock: null,
			ear: {
				left: null,
				right: null
			},
			userList: {
				rows: [],
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
		this.layout = Layouts.Centered;
		this.grid = {
			rows: 0,
			columns: 0
		};

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
				onClick: this._onUserClick.bind(this)
			});
		}

		BX.Call.Util.updateUserData(userIds);
	};

	BX.Call.View.prototype.setDeviceList = function(deviceList)
	{
		this.cameraList = [];
		this.microphoneList = [];

		for (var i = 0; i < deviceList.length; i++)
		{
			var deviceInfo = deviceList[i];
			if (deviceInfo.kind == "audioinput")
			{
				this.microphoneList.push(deviceInfo);
			}
			else if (deviceInfo.kind == "videoinput")
			{
				this.cameraList.push(deviceInfo);
			}
		}

		if(this.buttons.camera)
		{
			if(this.cameraList.length === 0)
			{
				this.buttons.camera.hideArrow();
			}
			else
			{
				this.buttons.camera.showArrow();
			}
		}
		if(this.buttons.microphone)
		{
			if(this.microphoneList.length === 0)
			{
				this.buttons.microphone.hideArrow();
			}
			else
			{
				this.buttons.microphone.showArrow();
			}
		}
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
		if (!this.users[userId])
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
		this.updateButtons();
	};

	BX.Call.View.prototype.setLayout = function(newLayout)
	{
		if(newLayout == this.layout)
		{
			return;
		}

		this.layout = newLayout;

		for(var i = 0; i < this.elements.userList.rows.length; i++)
		{
			BX.remove(this.elements.userList.rows[i]);
		}

		this.elements.userList.rows = this.renderUserList();
		if(this.layout == Layouts.Centered)
		{
			this.elements.root.className = "bx-messenger-videocall bx-messenger-videocall-centered";
			BX.Call.Util.appendChildren(this.elements.userBlock, this.elements.userList.rows);
			this.elements.container.appendChild(this.elements.center);
			this.elements.container.appendChild(this.elements.userBlock);

			this.centralUser.reattachStream();
		}
		else if (this.layout == Layouts.Grid)
		{
			this.elements.root.className = "bx-messenger-videocall bx-messenger-videocall-grid bx-messenger-videocall-grid-r-" + this.grid.rows + " bx-messenger-videocall-grid-c-" + this.grid.columns;
			this.elements.container.removeChild(this.elements.center);
			this.elements.container.removeChild(this.elements.userBlock);
			BX.Call.Util.appendChildren(this.elements.container, this.elements.userList.rows);
		}
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
		else if(userId == this.centralUser.userId && (newState == BX.Call.UserState.Idle || newState == BX.Call.UserState.Failed))
		{
			var usersWithVideo = this.getUsersWithVideo();
			var connectedUsers = this.getConnectedUsers();
			if (usersWithVideo.length > 0)
			{
				this.setCentralUser(usersWithVideo[0]);
			}
			else if (this.localUser.hasVideo())
			{
				this.setCentralUser(this.userId);
			}
			else if (connectedUsers.length > 0)
			{
				this.setCentralUser(connectedUsers[0]);
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

	BX.Call.View.prototype.setLocalStream = function(mediaStream)
	{
		this.localUser.stream = mediaStream;
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
		else
		{
			this.microphoneId = '';
		}

		/*if(!this.localUser.hasVideo())
		{
			return false;
		}*/

		if(this.layout == Layouts.Centered && this.centralUser.userId == this.userId)
		{
			this.centralUser.setStream(mediaStream);
		}
		else
		{
			this.updateUserList();
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

		if(mediaStream.getAudioTracks().length > 0 && mediaStream != this.elements.audio[userId].srcObject)
		{
			this.elements.audio[userId].srcObject = mediaStream;
			this.elements.audio[userId].play();
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
				else if (this.localUser.hasVideo())
				{
					this.setCentralUser(this.userId);
				}
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

		if(this.layout == Layouts.Centered)
		{
			this.centralUser.reattachStream();
		}

		this.updateButtons();
		this.updateUserList();

		this.toggleEars();
	};

	BX.Call.View.prototype.hide = function()
	{
		BX.remove(this.elements.root);
	};

	/**
	 * @param {Object} params
	 * @param {string} params.text
	 * @param {string} [params.subText]
	 */
	BX.Call.View.prototype.showFatalError = function(params)
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
		this.setUiState(UiState.Error);
	};

	BX.Call.View.prototype.close = function()
	{
		BX.cleanNode(this.container);

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

			if(this.layout == Layouts.Centered)
			{
				this.centralUser.reattachStream();
			}

			this.updateButtons();
			this.updateUserList();
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

	BX.Call.View.prototype.disableSwitchMicrophone = function()
	{
		this.disabledButtons['microphone'] = true;
	};

	BX.Call.View.prototype.disableScreenSharing = function()
	{
		this.disabledButtons['screen'] = true;
	};


	BX.Call.View.prototype.getButtonList = function()
	{
		if(this.uiState == UiState.Error)
		{
			return ['close'];
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

		if(this.uiState === UiState.Connected)
		{
			result.push('microphone', 'camera');
		}

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
							children: [
								this.elements.overlay = BX.create("div", {
									props: {className: "bx-messenger-videocall-overlay"}
								})
							]
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

		if(this.layout == Layouts.Centered)
		{
			this.elements.container.appendChild(this.elements.center);
			this.elements.container.appendChild(this.elements.userBlock);
		}

		return this.elements.root;
	};

	BX.Call.View.prototype.renderUserList = function()
	{
		var containerSize = this.elements.root.getBoundingClientRect();
		var showLocalUser = this.localUser.hasVideo() && (this.layout == Layouts.Grid || this.centralUser.userId != this.userId);
		var userNodes = [];

		for (var userId in this.users)
		{
			/** @type {CallUser} */
			var user = this.users[userId];
			if(this.layout == Layouts.Centered && userId == this.centralUser.userId)
			{
				continue;
			}
			if(user.state == BX.Call.UserState.Idle)
			{
				continue;
			}

			userNodes.push(user.render());
		}
		if(showLocalUser)
		{
			userNodes.push(this.localUser.render());
		}

		var rows = [];
		var userCount = userNodes.length;
		var rowCount = (this.layout == Layouts.Centered) ? 1 : BX.Call.Util.findRowCount(containerSize.width, containerSize.height, userCount);
		var columnCount = Math.ceil(userCount / rowCount);

		var i;
		for (i = 0; i < rowCount; i++)
		{
			rows.push(BX.create("div", {props: {className: "bx-messenger-videocall-user-list"}}));
		}

		var currentRow = 0;
		var currentColumn = 0;
		for (i = 0; i < userCount; i++)
		{
			if (currentColumn >= columnCount)
			{
				currentColumn = 0;
				currentRow++;
			}

			rows[currentRow].appendChild(userNodes[i]);
			currentColumn++;
		}

		var showAdd = this.layout == Layouts.Centered && userCount > 0 && !this.isFullScreen && this.uiState === UiState.Connected && !this.isButtonDisabled("add") && this.getConnectedUserCount() < this.userLimit - 1;
		if (showAdd)
		{
			this.elements.userList.addButton = BX.create("div", {
				props: {className: "bx-messenger-videocall-user-add"},
				children: [
					BX.create("div", {
						props: {className: "bx-messenger-videocall-user-add-inner"}
					})
				],
				events: {
					click: this._onAddButtonClick.bind(this)
				}
			});

			if(!this.isFullScreen)
			{
				rows[currentRow].appendChild(this.elements.userList.addButton);
			}
		}
		else
		{
			this.elements.userList.addButton = null;
		}

		this.grid.rows = rowCount;
		this.grid.columns = columnCount;

		if(this.layout === Layouts.Centered && rows.length === 1)
		{
			rows[0].addEventListener("scroll", this.toggleEars.bind(this));
		}

		return rows;
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
						arrowEnabled: this.microphoneList.length > 0 && !this.isFullScreen,
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
						arrowEnabled: this.cameraList.length > 0 && !this.isFullScreen,
						onClick: this._onCameraButtonClick.bind(this),
						onArrowClick: this._onCameraArrowClick.bind(this)
					});
					middleSubPanel.appendChild(this.buttons.camera.render());
					break;
				case "screen":
					this.buttons.screen = new SimpleButton({
						class: "screen",
						text: BX.message("IM_M_CALL_BTN_SCREEN"),
						onClick: this._onScreenButtonClick.bind(this)
					});
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
						text: BX.message("IM_M_CALL_BTN_HANGUP"),
						onClick: this._onHangupButtonClick.bind(this)
					});
					rightSubPanel.appendChild(this.buttons.hangup.render());
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

	BX.Call.View.prototype.updateUserList = function()
	{
		for (var i = 0; i < this.elements.userList.rows.length; i++)
		{
			BX.remove(this.elements.userList.rows[i]);
		}

		this.elements.userList.rows = this.renderUserList();
		if (this.layout == Layouts.Centered)
		{
			BX.Call.Util.appendChildren(this.elements.userBlock, this.elements.userList.rows);
			var userList = this.elements.userList.rows[0];
			this.centralUser.setFullSize(userList.childElementCount == 0);

		}
		else if (this.layout == Layouts.Grid)
		{
			BX.Call.Util.appendChildren(this.elements.container, this.elements.userList.rows);

			this.elements.root.className = "bx-messenger-videocall bx-messenger-videocall-grid bx-messenger-videocall-grid-r-" + this.grid.rows + " bx-messenger-videocall-grid-c-" + this.grid.columns;
		}
		this.toggleEars();
	};

	BX.Call.View.prototype.updateButtons = function()
	{
		var buttons = this.getButtonList();
		BX.cleanNode(this.elements.panel);
		this.elements.panel.appendChild(this.renderButtons(buttons))
	};

	BX.Call.View.prototype.isScreenSharingSupported = function()
	{
		return typeof(BXDesktopSystem) !== "undefined";
	};

	BX.Call.View.prototype.isFullScreenSupported = function()
	{
		if (BX.browser.IsChrome() || BX.browser.IsSafari())
		{
			return document.webkitFullscreenEnabled === true;
		}
		else if (BX.browser.IsFirefox())
		{
			return document.mozFullscreenEnabled === true;
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
			this.elements.root.mozRequestFullScreen();
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
			&& this.elements.userList.rows.length == 1
			&& this.elements.userList.rows[0].scrollWidth > this.elements.userList.rows[0].offsetWidth
			&& (this.elements.userList.rows[0].offsetWidth + this.elements.userList.rows[0].scrollLeft) < this.elements.userList.rows[0].scrollWidth
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
			&& this.elements.userList.rows.length == 1
			&& this.elements.userList.rows[0].scrollLeft > 0
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
				this.elements.userList.rows[0].scrollLeft -= 10;
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
				this.elements.userList.rows[0].scrollLeft += 10;
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
			this.isFullScreen = (document.mozFullscreenElement == this.elements.root);
		}

		if(this.isFullScreen)
		{
			//this.elements.wrap.classList.add('bx-messenger-videocall-fullscreen');
			if(this.elements.userList.addButton)
			{
				BX.remove(this.elements.userList.addButton);
			}

		}
		else
		{
			//this.elements.wrap.classList.remove('bx-messenger-videocall-fullscreen');
			if(this.elements.userList.addButton)
			{
				this.elements.userList.rows[this.elements.userList.rows.length - 1].appendChild(this.elements.userList.addButton);
			}
		}
		this.updateButtons();
	};

	BX.Call.View.prototype._onResize = function()
	{
		if(this.centralUser)
		{
			this.centralUser.updateAvatarWidth();
		}
		this.toggleEars();
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
		if(this.microphoneList.length === 0)
		{
			return;
		}

		DeviceSelector.create({
			parentElement: e.currentTarget,
			deviceList: this.microphoneList,
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
		if(this.cameraList.length === 0)
		{
			return;
		}

		DeviceSelector.create({
			parentElement: e.currentTarget,
			deviceList: this.cameraList,
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

	BX.Call.View.prototype.destroy = function()
	{
		if(this.elements.root)
		{
			BX.cleanNode(this.elements.root, true);
			this.elements.root = null;
		}

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
		}
		else
		{
			if(this.userId != this.parent.userId)
			{
				this.elements.container.classList.add("bx-messenger-videocall-audio");
			}
		}

		this.updateUserInfo();
		return this.elements.container;
	};

	CentralUser.prototype.updateUserInfo = function()
	{
		var self = this;
		BX.Call.Util.getUserName(this.userId).then(function(name)
		{
			self.elements.name.innerText = name;
			return BX.Call.Util.getUserAvatar(self.userId);
		}).then(function (avatar)
		{
			if (avatar != '')
			{
				self.elements.avatar.style.backgroundImage = "url(" + avatar + ")";
			}
			else
			{
				self.elements.avatar.style.removeProperty("background-image");
			}
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

		if (userId == this.parent.userId)
		{
			this.setStream(this.parent.localUser.stream);
		}
		else
		{
			this.setStream(this.parent.users[userId].stream);
		}

		this.userId = userId;
		this.updateUserInfo();
	};

	CentralUser.prototype.setStream = function(stream)
	{
		this.stream = stream;
		var hasVideo = BX.Call.Util.containsVideoTrack(stream);

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

		this.hasVideo = hasVideo;
		if(this.hasVideo)
		{
			this.elements.video.srcObject = stream;
		}
	};

	CentralUser.prototype.reattachStream = function()
	{
		if(this.hasVideo)
		{
			this.elements.video.srcObject = this.stream;
		}
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
		clearInterval(this.checkAspectInterval);
	};

	var CallUser = function(config)
	{
		this.id = config.id;
		this.name = '';
		this.avatar = '';
		this.state = config.state;
		this.stream = config.stream;
		this.talking = false;

		this.localUser = config.localUser === true;
		this.hidden = false;

		this.elements = {
			root: null,
			container: null,
			video: null,
			avatar: null,
			nameContainer: null,
			name: null,
			overlay: null,
			state: null,
			removeButton: null
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
			dataset: {userId: this.id},
			children: [
				this.elements.container = BX.create("div", {
					props: {className: "bx-messenger-videocall-user-inner"},
				})
			],
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
				self.elements.avatar.style.backgroundImage = "url(" + avatar + ")";
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

		this.elements.video = BX.create("video", {
			props: {
				className: "bx-messenger-videocall-video",
				volume: 0,
				autoplay: true
			}
		});
		if(this.stream && this.stream.active)
		{
			this.elements.video.srcObject = this.stream;
		}
		this.elements.container.appendChild(this.elements.video);

		this.elements.overlay = BX.create("div", {props: {className: "bx-messenger-videocall-overlay"}});
		this.elements.container.appendChild(this.elements.overlay);

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
		this.elements.container.appendChild(this.elements.nameContainer);

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
							text: BX.message("IM_M_CALL_STATUS_CONNECTION_ERROR")
						})
					]
				});
				break;
		}

		return stateNode ? stateNode : null;
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

	CallUser.prototype.destroy = function()
	{
		this.stream = null;
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
		var prettyName = '<span class="bx-messenger-videocall-panel-title-name">' + BX.util.htmlspecialchars(this.text)+ '</span>';

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

		this.elements = {
			root: null
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
					props: {className: "bx-messenger-videocall-panel-icon bx-messenger-videocall-panel-icon-" + this.class}
				}),
				textNode
			],
			events: {
				click: this.callbacks.onClick
			}
		});

		return this.elements.root;
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
				zIndex: zIndexBase + 500,
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

	BX.Call.View.Size = {
		Folded: 'folded',
		Full: 'full'
	}


})();
