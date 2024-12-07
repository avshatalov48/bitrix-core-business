;(function()
{
	BX.namespace('BX.Call');

	if (BX.Call.View)
	{
		return;
	}

	var Layouts = {
		Grid: 1,
		Centered: 2,
		Mobile: 3
	};

	var UiState = {
		Preparing: 1,
		Initializing: 2,
		Calling: 3,
		Connected: 4,
		Error: 5
	};

	const RoomState = {
		None: 1,
		Speaker: 2,
		NonSpeaker: 3,
	}

	var EventName = {
		onShow: 'onShow',
		onClose: 'onClose',
		onDestroy: 'onDestroy',
		onButtonClick: 'onButtonClick',
		onBodyClick: 'onBodyClick',
		onReplaceCamera: 'onReplaceCamera',
		onReplaceMicrophone: 'onReplaceMicrophone',
		onReplaceSpeaker: 'onReplaceSpeaker',
		onSetCentralUser: 'onSetCentralUser',
		onLayoutChange: 'onLayoutChange',
		onChangeHdVideo: 'onChangeHdVideo',
		onChangeMicAutoParams: 'onChangeMicAutoParams',
		onChangeFaceImprove: 'onChangeFaceImprove',
		onUserClick: 'onUserClick',
		onUserRename: 'onUserRename',
		onUserPinned: 'onUserPinned',
		onDeviceSelectorShow: 'onDeviceSelectorShow',
	};

	var newUserPosition = 999;
	var localUserPosition = 1000;
	var addButtonPosition = 1001;
	var maximumNotifications = 5;

	var MIN_WIDTH = 250;

	var SIDE_USER_WIDTH = 160; // keep in sync with .bx-messenger-videocall-user-block .bx-messenger-videocall-user width
	var SIDE_USER_HEIGHT = 90; // keep in sync with .bx-messenger-videocall-user height

	var MAX_USERS_PER_PAGE = 15;

	var MIN_GRID_USER_WIDTH = 249;
	var MIN_GRID_USER_HEIGHT = 140;

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
		this.speakerId = '';
		this.speakerMuted = false;
		this.showChatButtons = (config.showChatButtons === true);
		this.showUsersButton = (config.showUsersButton === true);
		this.showShareButton = (config.showShareButton !== false);
		this.showRecordButton = (config.showRecordButton !== false);
		this.showDocumentButton = (config.showDocumentButton !== false);
		this.showButtonPanel = (config.showButtonPanel !== false);

		this.broadcastingMode = BX.prop.getBoolean(config, "broadcastingMode", false);
		this.broadcastingPresenters = BX.prop.getArray(config, "broadcastingPresenters", []);

		this.currentPage = 1;
		this.pagesCount = 1;

		this.usersPerPage = 0; // initializes after rendering and on resize

		this.language = config.language || '';

		this.lastPosition = 1;

		this.userData = {};
		if(config.userData)
		{
			this.updateUserData(config.userData);
		}
		this.userLimit = config.userLimit || 1;
		this.userId = BX.message('USER_ID');
		this.isIntranetOrExtranet = BX.prop.getBoolean(config, "isIntranetOrExtranet", true);
		this.users = {}; // Call participants. The key is the user id.
		this.screenUsers = {}; // Screen sharing participants. The key is the user id.
		this.userRegistry = new UserRegistry();

		var localUserModel = new UserModel({
			id: this.userId,
			state: BX.prop.getString(config, "localUserState", BX.Call.UserState.Connected),
			localUser: true,
			order: localUserPosition,
			name: this.userData[this.userId] ? this.userData[this.userId].name : '',
			avatar: this.userData[this.userId] ? this.userData[this.userId].avatar_hr : '',
		});
		this.userRegistry.push(localUserModel);

		this.localUser = new CallUser({
			parentContainer: this.container,
			userModel: localUserModel,
			allowBackgroundItem: BX.Call.Hardware.BackgroundDialog.isAvailable() && this.isIntranetOrExtranet,
			allowMaskItem: BX.Call.Hardware.BackgroundDialog.isMaskAvailable() && this.isIntranetOrExtranet,
			onUserRename: this._onUserRename.bind(this),
			onUserRenameInputFocus: this._onUserRenameInputFocus.bind(this),
			onUserRenameInputBlur: this._onUserRenameInputBlur.bind(this),
		});

		this.centralUser = this.localUser; //show local user until someone is connected
		this.centralUserMobile = null;
		this.pinnedUser = null;
		this.presenterId = null;

		this.returnToGridAfterScreenStopped = false;

		this.mediaSelectionBlocked = (config.mediaSelectionBlocked === true);

		this.visible = false;
		this.elements = {
			root: null,
			wrap: null,
			watermark: null,
			container: null,
			overlay: null,
			topPanel: null,
			bottom: null,
			notificationPanel: null,
			panel: null,
			audioContainer: null,
			audio: {
				// userId: <audio> for this user's stream
			},
			center: null,
			localUserMobile: null,
			userBlock: null,
			ear: {
				left: null,
				right: null
			},
			userList: {
				container: null,
				addButton: null
			},
			userSelectorContainer: null,
			pinnedUserContainer: null,
			renameSlider: {
				input: null,
				button: null
			},
			pageNavigatorLeft: null,
			pageNavigatorLeftCounter: null,
			pageNavigatorRight: null,
			pageNavigatorRightCounter: null,
		};

		this.buttons = {
			title: null,
			grid: null,
			add: null,
			share: null,
			record: null,
			document: null,
			microphone: null,
			camera: null,
			speaker: null,
			screen: null,
			mobileMenu: null,
			chat: null,
			users: null,
			history: null,
			hangup: null,
			fullscreen: null,
			overlay: null,
			status: null,
			returnToCall: null,
			recordStatus: null,
			participants: null,
			participantsMobile: null,
			watermark: null,
			hd: null,
			protected: null,
			more: null,
		};

		this.size = BX.Call.View.Size.Full;
		this.maxWidth = null;
		this.isMuted = false;
		this.isCameraOn = false;
		this.isFullScreen = false;
		this.isUserBlockFolded = false;

		this.recordState = this.getDefaultRecordState();

		this.blockedButtons = {};
		var configBlockedButtons = BX.prop.getArray(config, "blockedButtons", []);
		configBlockedButtons.forEach(function(buttonCode)
		{
			this.blockedButtons[buttonCode] = true
		}, this);

		this.hiddenButtons = {};
		this.overflownButtons = {};
		if (!this.showUsersButton)
		{
			this.hiddenButtons['users'] = true;
		}
		var configHiddenButtons = BX.prop.getArray(config, "hiddenButtons", []);
		configHiddenButtons.forEach(function(buttonCode)
		{
			this.hiddenButtons[buttonCode] = true
		}, this);

		this.hiddenTopButtons = {};
		var configHiddenTopButtons = BX.prop.getArray(config, "hiddenTopButtons", []);
		configHiddenTopButtons.forEach(function(buttonCode)
		{
			this.hiddenTopButtons[buttonCode] = true
		}, this);

		this.uiState = config.uiState || UiState.Calling;
		this.layout = config.layout || Layouts.Centered;
		this.roomState = RoomState.None;

		this.eventEmitter = new BX.Event.EventEmitter(this, 'BX.Call.View');

		this.scrollInterval = 0;

		// Event handlers
		this._onFullScreenChangeHandler = this._onFullScreenChange.bind(this);
		//this._onResizeHandler = BX.throttle(this._onResize.bind(this), 500);
		this._onResizeHandler = this._onResize.bind(this);
		this._onOrientationChangeHandler = BX.debounce(this._onOrientationChange.bind(this), 500);
		this._onKeyDownHandler = this._onKeyDown.bind(this);
		this._onKeyUpHandler = this._onKeyUp.bind(this);

		this.resizeObserver = new BX.ResizeObserver(this._onResizeHandler);
		this.intersectionObserver = null;

		// timers
		this.switchPresenterTimeout = 0;

		this.deviceSelector = null;
		this.userSelector = null;
		this.pinnedUserContainer = null;

		this.callMenu = null;
		this.userMenu = null;
		this.participantsMenu = null;
		this.renameSlider = null;

		this.userSize = {width: 0, height: 0};

		this.hintManager = BX.UI.Hint.createInstance({
			popupParameters: {
				targetContainer: document.body,
				className: 'bx-messenger-videocall-panel-item-hotkey-hint',
				bindOptions: {forceBindPosition: true}
			}
		});

		this.hotKey = {
			all: BX.Call.Util.isDesktop(),
			microphone: true,
			microphoneSpace: true,
			camera: true,
			screen: true,
			record: true,
			speaker: true,
			chat: true,
			users: true,
			floorRequest: true,
			muteSpeaker: true,
			grid: true,
		};
		this.hotKeyTemporaryBlock = 0;

		this.init();
		this.subscribeEvents(config);
		if(BX.type.isPlainObject(config.userStates))
		{
			this.appendUsers(config.userStates);
		}

		/*this.resizeCalled = 0;
		this.reportResizeCalled = BX.debounce(function()
		{
			console.log('resizeCalled ' + this.resizeCalled + ' times');
			this.resizeCalled = 0;
		}.bind(this), 100)*/
	};

	BX.Call.View.prototype.init = function()
	{
		if(this.isFullScreenSupported())
		{
			if (BX.browser.IsChrome() || BX.browser.IsSafari())
			{
				window.addEventListener("fullscreenchange", this._onFullScreenChangeHandler);
				window.addEventListener("webkitfullscreenchange", this._onFullScreenChangeHandler);
			}
			else if (BX.browser.IsFirefox())
			{
				window.addEventListener("mozfullscreenchange", this._onFullScreenChangeHandler);
			}
		}
		if (BX.browser.IsMobile())
		{
			document.documentElement.style.setProperty('--view-height', window.innerHeight + 'px');
			window.addEventListener("orientationchange", this._onOrientationChangeHandler);
		}

		this.elements.audioContainer = BX.create("div", {
			props: {className: "bx-messenger-videocall-audio-container"}
		});

		if (BX.Call.Hardware.initialized)
		{
			this.setSpeakerId(BX.Call.Hardware.defaultSpeaker);
		}
		else
		{
			BX.Call.Hardware.subscribe(BX.Call.Hardware.Events.initialized, function()
			{
				this.setSpeakerId(BX.Call.Hardware.defaultSpeaker);
			}.bind(this))
		}

		window.addEventListener("keydown", this._onKeyDownHandler);
		window.addEventListener("keyup", this._onKeyUpHandler);

		if (BX.browser.IsMac())
		{
			this.keyModifier = '&#8984; + Shift';
		}
		else
		{
			this.keyModifier = 'Ctrl + Shift';
		}

		this.container.appendChild(this.elements.audioContainer);
	};

	BX.Call.View.prototype.subscribeEvents = function(config)
	{
		for (var event in EventName)
		{
			if(EventName.hasOwnProperty(event) && BX.type.isFunction(config[event]))
			{
				this.setCallback(event, config[event]);
			}
		}
	};

	BX.Call.View.prototype.setCallback = function(name, cb)
	{
		if(BX.type.isFunction(cb) && EventName.hasOwnProperty(name))
		{
			this.eventEmitter.subscribe(name, function(event) {
				cb(event.data);
			});
		}
	};

	BX.Call.View.prototype.subscribe = function(eventName, listener)
	{
		return this.eventEmitter.subscribe(eventName, listener);
	};

	BX.Call.View.prototype.unsubscribe = function(eventName, listener)
	{
		return this.eventEmitter.unsubscribe(eventName, listener);
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
			var userId = userIds[i];
			this.addUser(userId, userStates[userId] ? userStates[userId] : BX.Call.UserState.Idle);
		}
	};

	BX.Call.View.prototype.setCentralUser = function(userId)
	{
		if (this.centralUser.id == userId)
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

		var previousCentralUser = this.centralUser;
		this.centralUser = (userId == this.userId ? this.localUser : this.users[userId]);
		if (this.layout == Layouts.Centered || this.layout == Layouts.Mobile)
		{
			previousCentralUser.dismount();
			this.updateUserList();
		}
		if (this.layout == Layouts.Mobile)
		{
			if (this.centralUserMobile)
			{
				this.centralUserMobile.setUserModel(this.userRegistry.get(userId));
			}
			else
			{
				this.centralUserMobile = new CallUserMobile({
					userModel: this.userRegistry.get(userId),
					onClick: function()
					{
						this.showUserMenu(this.centralUser.id)
					}.bind(this)
				});
				this.centralUserMobile.mount(this.elements.pinnedUserContainer);
			}

		}
		this.userRegistry.users.forEach(function(userModel)
		{
			userModel.centralUser = (userModel.id == userId);
		});
		this.eventEmitter.emit(EventName.onSetCentralUser, {
			userId: userId,
			stream: userId == this.userId ? this.localUser.stream : this.users[userId].stream
		})
	};

	BX.Call.View.prototype.getLeftUser = function(userId)
	{
		var candidateUserId = null;
		for (var i = 0; i < this.userRegistry.users.length; i++)
		{
			var userModel = this.userRegistry.users[i];
			if (userModel.id == userId && candidateUserId)
			{
				return candidateUserId
			}

			if (!userModel.localUser && userModel.state == BX.Call.UserState.Connected)
			{
				candidateUserId = userModel.id
			}
		}

		return candidateUserId;
	};

	BX.Call.View.prototype.getRightUser = function(userId)
	{
		var candidateUserId = null;
		for (var i = this.userRegistry.users.length - 1; i >= 0; i--)
		{
			var userModel = this.userRegistry.users[i];
			if (userModel.id == userId && candidateUserId)
			{
				return candidateUserId
			}

			if (!userModel.localUser && userModel.state == BX.Call.UserState.Connected)
			{
				candidateUserId = userModel.id
			}
		}

		return candidateUserId;
	};

	BX.Call.View.prototype.getUserCount = function()
	{
		return Object.keys(this.users).length;
	};

	BX.Call.View.prototype.getConnectedUserCount = function(withYou)
	{
		var count = this.getConnectedUsers().length;

		if (withYou)
		{
			var userId = parseInt(this.userId, 10);
			if (!this.broadcastingMode || this.broadcastingPresenters.includes(userId))
			{
				count += 1;
			}
		}

		return count;
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
		for (var i = 0; i < this.userRegistry.users.length; i++)
		{
			var userModel = this.userRegistry.users[i];
			if (userModel.id != this.userId && userModel.state == BX.Call.UserState.Connected)
			{
				result.push(userModel.id);
			}
		}
		return result;
	};

	BX.Call.View.prototype.getDisplayedUsers = function()
	{
		var result = [];
		for (var i = 0; i < this.userRegistry.users.length; i++)
		{
			var userModel = this.userRegistry.users[i];
			if (userModel.id != this.userId && (userModel.state == BX.Call.UserState.Connected || userModel.state == BX.Call.UserState.Connecting))
			{
				result.push(userModel.id);
			}
		}
		return result;
	};

	BX.Call.View.prototype.hasUserWithScreenSharing = function()
	{
		return this.userRegistry.users.some(function(userModel)
		{
			return userModel.screenState;
		})
	};

	BX.Call.View.prototype.getPresenterUserId = function()
	{
		var currentPresenterId = this.presenterId || 0;
		if (currentPresenterId == this.localUser.id)
		{
			currentPresenterId = 0;
		}
		var userId; // for usage in iterators

		var currentPresenterModel = this.userRegistry.get(currentPresenterId);

		// 1. Current user, who is sharing screen has top priority
		if (currentPresenterModel && currentPresenterModel.screenState === true)
		{
			return currentPresenterId;
		}

		// 2. If current user is not sharing screen, but someone is sharing - he should become presenter
		for (userId in this.users)
		{
			if(this.users.hasOwnProperty(userId) && this.userRegistry.get(userId).screenState === true)
			{
				return parseInt(userId, 10);
			}
		}

		// 3. If current user is talking, or stopped talking less then one second ago - he should stay presenter
		if (currentPresenterModel && currentPresenterModel.wasTalkingAgo() < 1000)
		{
			return currentPresenterId;
		}

		// 4. Return currently talking user
		var minTalkingAgo = 0;
		var minTalkingAgoUserId = 0;
		for (userId in this.users)
		{
			if (!this.users.hasOwnProperty(userId))
			{
				continue;
			}
			var userWasTalkingAgo = this.userRegistry.get(userId).wasTalkingAgo();
			if (userWasTalkingAgo < 1000)
			{
				return parseInt(userId, 10);
			}
			if (userWasTalkingAgo < minTalkingAgo)
			{
				minTalkingAgoUserId = parseInt(userId, 10);
			}
		}

		// 5. Return last talking user
		if (minTalkingAgoUserId)
		{
			return minTalkingAgoUserId;
		}

		// return current user in center
		return this.centralUser.id;
	};

	BX.Call.View.prototype.switchPresenter = function()
	{
		var newPresenterId = this.getPresenterUserId();

		if (!newPresenterId)
		{
			return;
		}

		this.presenterId = newPresenterId;
		this.userRegistry.users.forEach(function(userModel)
		{
			userModel.presenter = userModel.id == this.presenterId
		}, this);

		if (this.pinnedUser === null)
		{
			this.setCentralUser(newPresenterId);
		}

		if (this.layout == Layouts.Grid)
		{
			var presentersPage = this.findUsersPage(this.presenterId);
			if (presentersPage)
			{
				this.setCurrentPage(presentersPage);
			}
		}
	};

	BX.Call.View.prototype.switchPresenterDeferred = function()
	{
		clearTimeout(this.switchPresenterTimeout);
		this.switchPresenterTimeout = setTimeout(this.switchPresenter.bind(this), 1000);
	};

	BX.Call.View.prototype.cancelSwitchPresenter = function()
	{
		clearTimeout(this.switchPresenterTimeout);
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
			BX.cleanNode(this.elements.container);
			this.elements.container.appendChild(this.elements.overlay);
		}
		if(!this.elements.root)
		{
			return;
		}
		this.updateButtons();
		if (this.uiState == UiState.Preparing)
		{
			this.elements.wrap.classList.add("with-clouds");
		}
		else
		{
			this.elements.wrap.classList.remove("with-clouds");
		}
	};

	BX.Call.View.prototype.setLayout = function(newLayout)
	{
		if(newLayout == this.layout)
		{
			return;
		}

		this.layout = newLayout;

		if(this.layout == Layouts.Centered || this.layout == Layouts.Mobile)
		{
			this.elements.root.classList.remove("bx-messenger-videocall-grid");
			this.elements.root.classList.add("bx-messenger-videocall-centered");
			this.centralUser.mount(this.elements.center);
			this.elements.container.appendChild(this.elements.userBlock);

			if(this.layout != Layouts.Mobile)
			{
				this.elements.userBlock.appendChild(this.elements.userList.container);
			}

			this.centralUser.playVideo();
			//this.centralUser.updateAvatarWidth();
		}
		if (this.layout == Layouts.Grid)
		{
			this.elements.root.classList.remove("bx-messenger-videocall-centered");
			this.elements.root.classList.add("bx-messenger-videocall-grid");

			this.elements.container.appendChild(this.elements.userList.container);
			this.elements.container.removeChild(this.elements.userBlock);
			if (this.isFullScreen && this.buttons.participants)
			{
				this.buttons.participants.update({
					foldButtonState: ParticipantsButton.FoldButtonState.Hidden
				});
			}
			this.unpinUser();
		}
		if(this.layout == Layouts.Centered && this.isFullScreen)
		{
			this.setUserBlockFolded(true);
		}

		if (this.layout == Layouts.Mobile)
		{
			this.elements.root.classList.add("bx-messenger-videocall-fullscreen-mobile");
		}
		else
		{
			this.elements.root.classList.remove("bx-messenger-videocall-fullscreen-mobile");
		}

		this.renderUserList();
		this.toggleEars();
		this.updateButtons();
		this.eventEmitter.emit(EventName.onLayoutChange, {
			layout: this.layout
		});
	};

	BX.Call.View.prototype.setRoomState = function(roomState)
	{
		if (this.roomState === roomState)
		{
			return;
		}
		this.roomState = roomState;
		if (this.buttons.microphone)
		{
			this.buttons.microphone.setSideIcon(this.getMicrophoneSideIcon(this.roomState));
		}
	}

	BX.Call.View.prototype.getMicrophoneSideIcon = function(roomState)
	{
		switch (roomState)
		{
			case RoomState.Speaker:
				return 'ellipsis';
			case RoomState.NonSpeaker:
				return 'pointer';
			case RoomState.None:
			default:
				return null;
		}
	}

	BX.Call.View.prototype.setCurrentPage = function(pageNumber)
	{
		if (pageNumber < 1 || pageNumber > this.pagesCount || pageNumber == this.currentPage)
		{
			return;
		}
		this.currentPage = pageNumber;
		if (this.elements.root)
		{
			this.elements.pageNavigatorLeftCounter.innerHTML = (this.currentPage - 1) + '&nbsp;/&nbsp;' + this.pagesCount;
			this.elements.pageNavigatorRightCounter.innerHTML = (this.currentPage + 1) + '&nbsp;/&nbsp;' + this.pagesCount;
		}
		if (this.layout !== Layouts.Grid)
		{
			return;
		}

		this.renderUserList();
		this.toggleEars();
	};

	BX.Call.View.prototype.calculateUsersPerPage = function()
	{
		if (!this.elements.userList)
		{
			return 1000;
		}

		var containerSize = this.elements.userList.container.getBoundingClientRect();
		var columns = Math.floor(containerSize.width / MIN_GRID_USER_WIDTH) || 1;
		var rows = Math.floor(containerSize.height / MIN_GRID_USER_HEIGHT) || 1;
		var usersPerPage = columns * rows - 1;

		if (!usersPerPage)
		{
			return 1000;
		}

		if (usersPerPage <= MAX_USERS_PER_PAGE)
		{
			return usersPerPage;
		}
		else
		{
			// check if the last row should be filled up
			var elementSize = BX.Call.Util.findBestElementSize(
				containerSize.width,
				containerSize.height,
				MAX_USERS_PER_PAGE + 1,
				MIN_GRID_USER_WIDTH,
				MIN_GRID_USER_HEIGHT
			);
			// console.log('Optimal element size: width '+elementSize.width+' height '+elementSize.height);
			columns = Math.floor(containerSize.width / elementSize.width);
			rows = Math.floor(containerSize.height / elementSize.height);
			return columns * rows -1;
		}
	};

	BX.Call.View.prototype.calculatePagesCount = function(usersPerPage)
	{
		var pages = Math.ceil((this.getDisplayedUsers().length) / usersPerPage);
		return pages > 0 ? pages : 1;
	};

	BX.Call.View.prototype.recalculatePages = function()
	{
		this.usersPerPage = this.calculateUsersPerPage();
		this.pagesCount = this.calculatePagesCount(this.usersPerPage);

		if (this.elements.root)
		{
			this.elements.pageNavigatorLeftCounter.innerHTML = (this.currentPage - 1) + '&nbsp;/&nbsp;' + this.pagesCount;
			this.elements.pageNavigatorRightCounter.innerHTML = (this.currentPage + 1) + '&nbsp;/&nbsp;' + this.pagesCount;
		}
	};

	/**
	 * Returns page number, where the user is displayed, or 0 if user is not found
	 * @param {int} userId Id of the user
	 * @return {int}
	 */
	BX.Call.View.prototype.findUsersPage = function(userId)
	{
		if (userId == this.userId || this.usersPerPage === 0)
		{
			return 0;
		}
		var displayedUsers = this.getDisplayedUsers();
		var userPosition = 0;

		for (var i = 0; i < displayedUsers.length; i++)
		{
			if (displayedUsers[i] == userId)
			{
				userPosition = i + 1;
				break;
			}
		}

		return (userPosition ? Math.ceil(userPosition / this.usersPerPage) : 0);
	};

	BX.Call.View.prototype.setCameraId = function(cameraId)
	{
		if (this.cameraId == cameraId)
		{
			return;
		}

		if (this.localUser.stream && this.localUser.stream.getVideoTracks().length > 0 )
		{
			throw new Error("Can not set camera id while having active stream")
		}
		this.cameraId = cameraId;
	};

	BX.Call.View.prototype.setMicrophoneId = function(microphoneId)
	{
		if (this.microphoneId == microphoneId)
		{
			return;
		}

		if (this.localUser.stream && this.localUser.stream.getAudioTracks().length > 0 )
		{
			throw new Error("Can not set microphone id while having active stream")
		}
		this.microphoneId = microphoneId;
	};

	BX.Call.View.prototype.setMicrophoneLevel = function(level)
	{
		this.microphoneLevel = level;
		if (this.buttons.microphone)
		{
			this.buttons.microphone.setLevel(level);
		}
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
		this.userRegistry.get(this.userId).microphoneState = !isMuted;
	};

	BX.Call.View.prototype.setLocalUserId = function(userId)
	{
		if (userId == this.userId)
		{
			return;
		}

		this.userId = parseInt(userId);
		this.localUser.userModel.id = this.userId;
		this.localUser.userModel.name = this.userData[this.userId] ? this.userData[this.userId].name : '';
		this.localUser.userModel.avatar = this.userData[this.userId] ? this.userData[this.userId].avatar_hr : '';
	};

	BX.Call.View.prototype.setUserBlockFolded = function(isUserBlockFolded)
	{
		this.isUserBlockFolded = isUserBlockFolded;

		if (this.elements.userBlock)
		{
			this.elements.userBlock.classList.toggle("folded", this.isUserBlockFolded);
		}
		if (this.elements.root)
		{
			this.elements.root.classList.toggle("bx-messenger-videocall-userblock-folded", this.isUserBlockFolded);
		}
		if(this.isUserBlockFolded)
		{
			if (this.buttons.participants && this.layout == Layouts.Centered)
			{
				this.buttons.participants.update({
					foldButtonState: ParticipantsButton.FoldButtonState.Unfold
				});
			}
		}
		else
		{
			if (this.buttons.participants)
			{
				this.buttons.participants.update({
					foldButtonState: (this.isFullScreen && this.layout == Layouts.Centered) ? ParticipantsButton.FoldButtonState.Fold : ParticipantsButton.FoldButtonState.Hidden
				});
			}
		}
	};

	BX.Call.View.prototype.addUser = function(userId, state, direction)
	{
		userId = Number(userId);
		if(this.users[userId])
		{
			return;
		}

		state = state || BX.Call.UserState.Idle;
		if (!direction)
		{
			if (this.broadcastingPresenters.length > 0 && !this.broadcastingPresenters.includes(userId))
			{
				direction = BX.Call.EndpointDirection.RecvOnly;
			}
			else
			{
				direction = BX.Call.EndpointDirection.SendRecv
			}
		}

		var userModel = new UserModel({
			id: userId,
			name: this.userData[userId] ? this.userData[userId].name : '',
			avatar: this.userData[userId] ? this.userData[userId].avatar_hr : '',
			state : state,
			order: state == BX.Call.UserState.Connected ? this.getNextPosition() : newUserPosition,
			direction: direction
		});

		this.userRegistry.push(userModel);

		if(!this.elements.audio[userId])
		{
			this.elements.audio[userId] = BX.create("audio");
			this.elements.audioContainer.appendChild(this.elements.audio[userId]);
		}

		this.users[userId] = new CallUser({
			parentContainer: this.container,
			userModel: userModel,
			audioElement: this.elements.audio[userId],
			allowPinButton: this.getConnectedUserCount() > 1,
			onClick: this._onUserClick.bind(this),
			onPin: this._onUserPin.bind(this),
			onUnPin: this._onUserUnPin.bind(this),
		});

		this.screenUsers[userId] = new CallUser({
			parentContainer: this.container,
			userModel: userModel,
			allowPinButton: false,
			screenSharingUser: true,
		});

		if (this.elements.root)
		{
			this.updateUserList();
			this.updateButtons();
			this.updateUserButtons();
		}
	};

	BX.Call.View.prototype.setUserDirection = function(userId, direction)
	{
		/** @type {UserModel} */
		var user = this.userRegistry.get(userId);
		if(!user || user.direction == direction)
		{
			return;
		}

		user.direction = direction;
		this.updateUserList();
	};

	BX.Call.View.prototype.setLocalUserDirection = function(direction)
	{
		if (this.localUser.userModel.direction != direction)
		{
			this.localUser.userModel.direction = direction;
			this.updateUserList();
		}
	};

	BX.Call.View.prototype.setUserState = function(userId, newState)
	{
		/** @type {UserModel} */
		var user = this.userRegistry.get(userId);
		if(!user)
		{
			return;
		}

		if(newState === BX.Call.UserState.Connected && this.uiState === UiState.Calling)
		{
			this.setUiState(UiState.Connected);
		}

		user.state = newState;

		// maybe switch central user
		if(this.centralUser.id == this.userId && newState == BX.Call.UserState.Connected)
		{
			this.setCentralUser(userId);
		}
		else if(userId == this.centralUser.id)
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

				if (connectedUsers.length === 0)
				{
					this.setCentralUser(this.userId);
				}
				else if (usersWithVideo.length > 0)
				{
					this.setCentralUser(usersWithVideo[0]);
				}
				else //if (connectedUsers.length > 0)
				{
					this.setCentralUser(connectedUsers[0]);
				}
			}
		}

		if (newState == BX.Call.UserState.Connected && user.order == newUserPosition)
		{
			user.order = this.getNextPosition();
		}

		if (userId == this.localUser.id)
		{
			this.setCameraState(this.localUser.hasVideo());
			this.localUser.userModel.cameraState = this.localUser.hasVideo();
		}

		this.updateUserList();
		this.updateButtons();
		this.updateUserButtons();
	};

	BX.Call.View.prototype.setTitle = function(title)
	{
		this.title = title;
	};

	BX.Call.View.prototype.getUserTalking = function(userId)
	{
		/** @type {CallUser} */
		var user = this.userRegistry.get(userId);
		if (!user)
		{
			return false;
		}

		return !!user.talking;
	}

	BX.Call.View.prototype.setUserTalking = function(userId, talking)
	{
		/** @type {UserModel} */
		var user = this.userRegistry.get(userId);
		if (user)
		{
			user.talking = talking;
		}

		if (userId == this.userId)
		{
			return;
		}

		if (userId == this.presenterId && !talking)
		{
			this.switchPresenterDeferred();
		}
		else
		{
			this.switchPresenter();
		}
	};

	BX.Call.View.prototype.setUserMicrophoneState = function(userId, isMicrophoneOn)
	{
		/** @type {UserModel} */
		var user = this.userRegistry.get(userId);
		if (user)
		{
			user.microphoneState = isMicrophoneOn;
		}
	};

	BX.Call.View.prototype.setUserCameraState = function(userId, cameraState)
	{
		/** @type {UserModel} */
		var user = this.userRegistry.get(userId);
		if (user)
		{
			user.cameraState = cameraState;
		}
	};

	BX.Call.View.prototype.setUserVideoPaused = function(userId, videoPaused)
	{
		/** @type {UserModel} */
		var user = this.userRegistry.get(userId);
		if (user)
		{
			user.videoPaused = videoPaused;
		}
	};

	BX.Call.View.prototype.getUserFloorRequestState = function(userId)
	{
		/** @type {CallUser} */
		var user = this.userRegistry.get(userId);
		if (!user)
		{
			return false;
		}

		return !!user.floorRequestState;
	};

	BX.Call.View.prototype.setUserFloorRequestState = function(userId, userFloorRequestState)
	{
		/** @type {CallUser} */
		var user = this.userRegistry.get(userId);
		if (!user)
		{
			return;
		}

		if (user.floorRequestState != userFloorRequestState)
		{
			user.floorRequestState = userFloorRequestState;
			if (userId != this.localUser.id && userFloorRequestState)
			{
				this.showFloorRequestNotification(userId);
			}
		}

		if (userId == this.userId)
		{
			this.setButtonActive('floorRequest', userFloorRequestState);
		}
	};

	BX.Call.View.prototype.pinUser = function(userId)
	{
		if (!(userId in this.users))
		{
			console.error("User " + userId + " is not known");
			return;
		}
		this.pinnedUser = this.users[userId];
		this.userRegistry.users.forEach(function(userModel)
		{
			userModel.pinned = userModel.id == userId;
		});
		this.setCentralUser(userId);
		this.eventEmitter.emit(EventName.onUserPinned, {
			userId: userId
		});
	};

	BX.Call.View.prototype.unpinUser = function()
	{
		this.pinnedUser = null;
		this.userRegistry.users.forEach(function(userModel)
		{
			userModel.pinned = false;
		});

		this.eventEmitter.emit(EventName.onUserPinned, {
			userId: null
		});
		this.switchPresenterDeferred();
	};

	BX.Call.View.prototype.showFloorRequestNotification = function(userId)
	{
		var userModel = this.userRegistry.get(userId);
		if (!userModel)
		{
			return;
		}
		var notification = FloorRequest.create({
			userModel: userModel
		});

		notification.mount(this.elements.notificationPanel);
		NotificationManager.Instance.addNotification(notification);
	};

	BX.Call.View.prototype.setUserScreenState = function(userId, screenState)
	{
		/** @type {UserModel} */
		var user = this.userRegistry.get(userId);
		if(!user)
		{
			return;
		}

		user.screenState = screenState;
		if (userId != this.userId)
		{
			if(screenState === true && this.layout === BX.Call.View.Layout.Grid)
			{
				this.setLayout(BX.Call.View.Layout.Centered);
				this.returnToGridAfterScreenStopped = true;
			}
			if (screenState === false
				&& this.layout === BX.Call.View.Layout.Centered
				&& !this.hasUserWithScreenSharing()
				&& !this.pinnedUser
				&& this.returnToGridAfterScreenStopped)
			{
				this.returnToGridAfterScreenStopped = false;
				this.setLayout(BX.Call.View.Layout.Grid);
			}
			this.switchPresenter();
		}
	};

	BX.Call.View.prototype.flipLocalVideo = function(flipVideo)
	{
		this.localUser.flipVideo = !!flipVideo;
	}

	BX.Call.View.prototype.setLocalStream = function(mediaStream, flipVideo)
	{
		this.localUser.videoTrack = mediaStream.getVideoTracks().length > 0 ? mediaStream.getVideoTracks()[0] : null;
		if (!BX.type.isUndefined(flipVideo))
		{
			this.flipLocalVideo(flipVideo);
		}
		this.setCameraState(this.localUser.hasVideo());
		this.localUser.userModel.cameraState = this.localUser.hasVideo();

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

		if(this.layout !== Layouts.Grid && this.centralUser.id == this.userId)
		{
			if(this.localUser.hasVideo() || Object.keys(this.users).length === 0)
			{
				this.centralUser.stream = mediaStream;
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
		if (this.speakerId == speakerId)
		{
			return;
		}

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

		if (!this.buttons.speaker)
		{
			return;
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
		console.error("BX.Call.View.prototype.setStream is deprecated");
		return;

		if(!this.users[userId])
		{
			throw Error("User " + userId + " is not a part of this call");
		}

		if (!(mediaStream instanceof MediaStream))
		{
			throw Error("mediaStream should be instance of MediaStream");
		}

		if(mediaStream.getAudioTracks().length > 0 && mediaStream != this.elements.audio[userId].srcObject)
		{
			if(this.speakerId && this.elements.audio[userId].setSinkId)
			{
				this.elements.audio[userId].setSinkId(this.speakerId).then(function()
				{
					this.elements.audio[userId].srcObject = mediaStream;
					this.elements.audio[userId].play().catch(logPlaybackError);
				}.bind(this)).catch(console.error);
			}
			else
			{
				this.elements.audio[userId].srcObject = mediaStream;
				this.elements.audio[userId].play().catch(logPlaybackError);
			}
		}

		this.users[userId].stream = mediaStream.getVideoTracks().length > 0 ? mediaStream : null;
	};

	BX.Call.View.prototype.setVideoRenderer = function(userId, mediaRenderer)
	{
		if(!this.users[userId])
		{
			throw Error("User " + userId + " is not a part of this call");
		}
		if (mediaRenderer === null)
		{
			this.users[userId].videoRenderer = null;
			return;
		}

		if (!("render" in mediaRenderer) || !BX.type.isFunction(mediaRenderer.render))
		{
			throw Error("mediaRenderer should have method render");
		}
		if (!("kind" in mediaRenderer) || (mediaRenderer.kind !== "video" && mediaRenderer.kind !== "sharing"))
		{
			throw Error("mediaRenderer should be of video kind");
		}

		this.users[userId].videoRenderer = mediaRenderer;
	};

	BX.Call.View.prototype.setUserMedia = function(userId, kind, track)
	{
		if (kind === 'audio')
		{
			this.users[userId].audioTrack = track;
		}
		if (kind === 'video')
		{
			this.users[userId].videoTrack = track;
		}
		if (kind === 'screen')
		{
			this.screenUsers[userId].videoTrack = track;
			this.updateUserList();
			this.setUserScreenState(userId, track !== null);
		}
	};

	BX.Call.View.prototype.applyIncomingVideoConstraints = function()
	{
		var userId;
		var user;
		if (this.layout === BX.Call.View.Layout.Grid)
		{
			for (userId in this.users)
			{
				/** @type {CallUser} */
				user = this.users[userId];
				user.setIncomingVideoConstraints(this.userSize.width, this.userSize.height);
			}
		}
		else if (this.layout === BX.Call.View.Layout.Centered)
		{
			for (userId in this.users)
			{
				/** @type {CallUser} */
				user = this.users[userId];
				if (userId == this.centralUser.id)
				{
					var containerSize = this.elements.center.getBoundingClientRect();
					user.setIncomingVideoConstraints(Math.floor(containerSize.width), Math.floor(containerSize.height));
				}
				else
				{
					user.setIncomingVideoConstraints(SIDE_USER_WIDTH, SIDE_USER_HEIGHT);
				}
			}
		}
	};

	BX.Call.View.prototype.getDefaultRecordState = function()
	{
		return {
			state: BX.Call.View.RecordState.Stopped,
			userId: 0,
			date: {
				start: null,
				pause: []
			},
		};
	};

	BX.Call.View.prototype.setRecordState = function(recordState)
	{
		this.recordState = recordState;
		if (this.buttons.recordStatus)
		{
			this.buttons.recordStatus.update(this.recordState);
		}

		if (this.recordState.userId != this.userId)
		{
			if (this.recordState.state === BX.Call.View.RecordState.Stopped)
			{
				this.unblockButtons(['record']);
			}
			else
			{
				this.blockButtons(['record']);
			}
		}

		if (this.elements.topPanel)
		{
			if (this.recordState.state === BX.Call.View.RecordState.Stopped)
			{
				delete (this.elements.topPanel.dataset.recordState);
			}
			else
			{
				this.elements.topPanel.dataset.recordState = recordState.state;
			}
		}
	};

	BX.Call.View.prototype.show = function()
	{
		if(!this.elements.root)
		{
			this.render();
		}
		this.container.appendChild(this.elements.root);

		if (this.layout !== Layouts.Mobile)
		{
			this.startIntersectionObserver();
		}
		this.updateButtons();
		this.updateUserList();

		this.resumeVideo();
		this.toggleEars();
		this.visible = true;

		this.eventEmitter.emit(EventName.onShow);
	};

	BX.Call.View.prototype.hide = function()
	{
		if (this.overflownButtonsPopup)
		{
			this.overflownButtonsPopup.close();
		}
		BX.remove(this.elements.root);
		this.visible = false;
	};

	BX.Call.View.prototype.startIntersectionObserver = function()
	{
		if (!('IntersectionObserver' in window))
		{
			return;
		}

		this.intersectionObserver = new IntersectionObserver(
			this._onIntersectionChange.bind(this),
			{
				root: this.elements.userList.container,
				threshold: 0.5
			}
		);
	};

	/**
	 * @param {CallUser} callUser
	 */
	BX.Call.View.prototype.observeIntersections = function(callUser)
	{
		if (this.intersectionObserver && callUser.elements.root)
		{
			this.intersectionObserver.observe(callUser.elements.root);
		}
	};

	/**
	 * @param {CallUser} callUser
	 */
	BX.Call.View.prototype.unobserveIntersections = function(callUser)
	{
		if (this.intersectionObserver && callUser.elements.root)
		{
			this.intersectionObserver.unobserve(callUser.elements.root);
		}
	};

	BX.Call.View.prototype.showDeviceSelector = function(bindElement)
	{
		if (this.deviceSelector)
		{
			return;
		}

		this.deviceSelector = new DeviceSelector({
			viewElement: this.container,
			parentElement: bindElement,
			microphoneEnabled: !this.isMuted,
			microphoneId: this.microphoneId || BX.Call.Hardware.defaultMicrophone,
			cameraEnabled: this.isCameraOn,
			cameraId: this.cameraId,
			speakerEnabled: !this.speakerMuted,
			speakerId: this.speakerId,
			allowHdVideo: BX.Call.Hardware.preferHdQuality,
			faceImproveEnabled: BX.Call.Util.isDesktop() && typeof (BX.desktop) !== 'undefined' && BX.desktop.cameraSmoothingStatus(),
			allowFaceImprove: BX.Call.Util.isDesktop() && typeof (BX.desktop) !== 'undefined' && BX.desktop.enableInVersion(64),
			allowBackground: BX.Call.Hardware.BackgroundDialog.isAvailable() && this.isIntranetOrExtranet,
			allowMask: BX.Call.Hardware.BackgroundDialog.isMaskAvailable() && this.isIntranetOrExtranet,
			allowAdvancedSettings: typeof (BXIM) !== 'undefined' && this.isIntranetOrExtranet,
			showCameraBlock: !this.isButtonBlocked('camera'),
			events: {
				onMicrophoneSelect: this._onMicrophoneSelected.bind(this),
				onMicrophoneSwitch: this._onMicrophoneButtonClick.bind(this),
				onCameraSelect: this._onCameraSelected.bind(this),
				onCameraSwitch: this._onCameraButtonClick.bind(this),
				onSpeakerSelect: this._onSpeakerSelected.bind(this),
				onSpeakerSwitch: this._onSpeakerButtonClick.bind(this),
				onChangeHdVideo: this._onChangeHdVideo.bind(this),
				onChangeMicAutoParams: this._onChangeMicAutoParams.bind(this),
				onChangeFaceImprove: this._onChangeFaceImprove.bind(this),
				onDestroy: function() {
					this.deviceSelector = null;
				}.bind(this),
				onShow: function() {
					this.eventEmitter.emit(EventName.onDeviceSelectorShow, {})
				}.bind(this)
			}
		});
		this.deviceSelector.show();
	};

	BX.Call.View.prototype.showCallMenu = function()
	{
		var menuItems = [
			{
				text: BX.message("IM_M_CALL_BTN_WANT_TO_SAY"),
				iconClass: "hand",
				onClick: this._onMobileCallMenuFloorRequestClick.bind(this)
			},
			{
				text: BX.message("IM_M_CALL_MOBILE_MENU_PARTICIPANTS_LIST"),
				iconClass: "participants",
				onClick: this._onMobileCallMenShowParticipantsClick.bind(this)
			},
			// TODO:
			/*{
				text: "Add participant",
				iconClass: "add-participant",
				onClick: function() {}
			},*/

			/*{ //DEBUG: mobile audio
				text: "Enable audio",
				iconClass: "",
				onClick: function() {
					for (var userId in this.elements.audio)
					{
						if (this.users[userId].stream)
						{
							console.log('user ' + userId + ' stream found, trying to play');
							this.elements.audio[userId].srcObject = this.users[userId].stream;
							this.elements.audio[userId].play();
						}
					}
					this.callMenu.close();
				}.bind(this)
			},*/
			{
				text: BX.message("IM_M_CALL_MOBILE_MENU_COPY_INVITE"),
				iconClass: "add-participant",
				onClick: this._onMobileCallMenuCopyInviteClick.bind(this)

			},
			!this.isIntranetOrExtranet
				?
				{
					text: BX.message("IM_M_CALL_MOBILE_MENU_CHANGE_MY_NAME"),
					iconClass: "change-name",
					onClick: function()
					{
						this.callMenu.close();
						setTimeout(this.showRenameSlider.bind(this), 100);
					}.bind(this)
				}
				:
				null,
			{
				separator: true,
			},
			{
				text: BX.message("IM_M_CALL_MOBILE_MENU_CANCEL"),
				enabled: false,
				onClick: this._onMobileCallMenuCancelClick.bind(this)
			}
		];

		this.callMenu = new MobileMenu({
			parent: this.elements.root,
			items: menuItems,
			onClose: function()
			{
				this.callMenu.destroy();
			}.bind(this),
			onDestroy: function()
			{
				this.callMenu = null;
			}.bind(this)
		});

		this.callMenu.show();
	};

	BX.Call.View.prototype.showUserMenu = function(userId)
	{
		var userModel = this.userRegistry.get(userId);
		if (!userModel)
		{
			return false;
		}

		var pinItem = null;
		if (this.pinnedUser && this.pinnedUser.id == userId)
		{
			pinItem = {
				text: BX.message("IM_M_CALL_MOBILE_MENU_UNPIN"),
				iconClass: "unpin",
				onClick: function()
				{
					this.userMenu.close();
					this.unpinUser();
				}.bind(this)
			};
		}
		else if (this.userId != userId)
		{
			pinItem = {
				text: BX.message("IM_M_CALL_MOBILE_MENU_PIN"),
				iconClass: "pin",
				onClick: function()
				{
					this.userMenu.close();
					this.pinUser(userId);
				}.bind(this)
			};
		}

		var menuItems = [
			{
				userModel: userModel,
				enabled: false
			},
			{
				separator: true,
			},
			pinItem,
			this.userId == userId && !this.isIntranetOrExtranet
				?
				{
					text: BX.message("IM_M_CALL_MOBILE_MENU_CHANGE_MY_NAME"),
					iconClass: "change-name",
					onClick: function()
					{
						this.userMenu.close();
						setTimeout(this.showRenameSlider.bind(this), 100)
					}.bind(this)
				}
				:
				null,
			/*{
				text: BX.message("IM_M_CALL_MOBILE_MENU_WRITE_TO_PRIVATE_CHAT"),
				iconClass: "private-chat",
				onClick: function()
				{
					this.userMenu.close();
					this.eventEmitter.emit(EventName.onButtonClick, {

					})
				}.bind(this)
			},*/
			/*{
				// TODO:
				text: "Remove user",
				iconClass: "remove-user"
			},*/
			{
				separator: true
			},
			{
				text: BX.message("IM_M_CALL_MOBILE_MENU_CANCEL"),
				enabled: false,
				onClick: function()
				{
					this.userMenu.close();
				}.bind(this)
			}
		];

		this.userMenu = new MobileMenu({
			parent: this.elements.root,
			items: menuItems,
			onClose: function()
			{
				this.userMenu.destroy();
			}.bind(this),
			onDestroy: function()
			{
				this.userMenu = null;
			}.bind(this)
		});
		this.userMenu.show();
	};

	BX.Call.View.prototype.showParticipantsMenu = function()
	{
		if (this.participantsMenu)
		{
			return;
		}
		var menuItems = [];
		menuItems.push({
			userModel: this.localUser.userModel,
			showSubMenu: true,
			onClick: function()
			{
				this.participantsMenu.close();
				this.showUserMenu(this.localUser.userModel.id);
			}.bind(this)
		});
		this.userRegistry.users.forEach(function(userModel)
		{
			if (userModel.localUser || userModel.state != BX.Call.UserState.Connected)
			{
				return;
			}
			if (menuItems.length > 0)
			{
				menuItems.push({
					separator: true
				});
			}
			menuItems.push({
				userModel: userModel,
				showSubMenu: true,
				onClick: function()
				{
					this.participantsMenu.close();
					this.showUserMenu(userModel.id);
				}.bind(this)
			})
		}, this);

		if (menuItems.length === 0)
		{
			return false;
		}

		this.participantsMenu = new MobileMenu({
			parent: this.elements.root,
			items: menuItems,
			header: BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.getConnectedUserCount(true)),
			largeIcons: true,

			onClose: function()
			{
				this.participantsMenu.destroy();
			}.bind(this),
			onDestroy: function()
			{
				this.participantsMenu = null;
			}.bind(this)
		});

		this.participantsMenu.show();
		return true;
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
		if (this.buttons.recordStatus)
		{
			this.buttons.recordStatus.stopViewUpdate();
		}
		this.recordState = this.getDefaultRecordState();

		if (this.elements.root)
		{
			BX.remove(this.elements.root);
		}

		this.visible = false;
		this.eventEmitter.emit(EventName.onClose);
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
			if (this.overflownButtonsPopup)
			{
				this.overflownButtonsPopup.close();
			}
			if (this.elements.panel)
			{
				this.elements.panel.classList.add('bx-messenger-videocall-panel-folded');
			}
			BX.remove(this.elements.container);
			BX.remove(this.elements.topPanel);
			this.elements.root.style.removeProperty('max-width');
			this.updateButtons();
		}
		else
		{
			if(this.elements.panel)
			{
				this.elements.panel.classList.remove('bx-messenger-videocall-panel-folded');
			}
			this.elements.wrap.appendChild(this.elements.topPanel);
			this.elements.wrap.appendChild(this.elements.container);
			if (this.maxWidth > 0)
			{
				this.elements.root.style.maxWidth = Math.max(this.maxWidth, MIN_WIDTH)  + 'px';
			}
			this.updateButtons();
			this.updateUserList();
			this.resumeVideo();
		}
	};

	BX.Call.View.prototype.isButtonBlocked = function(buttonName)
	{
		switch (buttonName)
		{
			case 'camera':
				return (this.uiState !== UiState.Preparing && this.uiState !== UiState.Connected) || this.blockedButtons[buttonName] === true;
			case 'chat':
				return !this.showChatButtons || this.blockedButtons[buttonName] === true;
			case 'floorRequest':
				return (this.uiState !== UiState.Connected) || this.blockedButtons[buttonName] === true;
			case 'screen':
				return !this.showShareButton || (!this.isScreenSharingSupported() || this.isFullScreen) || this.blockedButtons[buttonName] === true;
			case 'users':
				return !this.showUsersButton || this.blockedButtons[buttonName] === true;
			case 'record':
				return !this.showRecordButton || this.blockedButtons[buttonName] === true;
			case 'document':
				return !this.showDocumentButton || this.blockedButtons[buttonName] === true;
			default:
				return this.blockedButtons[buttonName] === true;
		}
	};

	BX.Call.View.prototype.isButtonHidden = function(buttonName)
	{
		return this.hiddenButtons[buttonName] === true;
	};

	BX.Call.View.prototype.showButton = function(buttonCode)
	{
		this.showButtons([buttonCode]);
	};

	BX.Call.View.prototype.hideButton = function(buttonCode)
	{
		this.hideButtons([buttonCode]);
	};

	/**
	 * @return {bool} Returns true if buttons update is required
	 */
	BX.Call.View.prototype.checkPanelOverflow = function()
	{
		var delta = this.elements.panel.scrollWidth - this.elements.panel.offsetWidth
		var mediumButtonMinWidth = 55; // todo: move to constants maybe? or maybe even calculate dynamically somehow?
		if (delta > 0)
		{
			var countOfButtonsToHide = Math.ceil(delta / mediumButtonMinWidth);
			if (Object.keys(this.overflownButtons).length === 0)
			{
				countOfButtonsToHide += 1;
			}

			var buttons = this.getButtonList();

			for (var i = buttons.length - 1; i > 0; i--)
			{
				if (buttons[i] === 'hangup' || buttons[i] === 'close' || buttons[i] === 'more')
				{
					continue;
				}

				this.overflownButtons[buttons[i]] = true;
				countOfButtonsToHide -= 1;
				if (!countOfButtonsToHide)
				{
					break;
				}
			}
			return true;
		}
		else
		{
			var hiddenButtonsCount = Object.keys(this.overflownButtons).length;
			if (hiddenButtonsCount > 0)
			{
				var unusedPanelSpace = this.calculateUnusedPanelSpace();
				if (unusedPanelSpace > mediumButtonMinWidth)
				{
					var countOfButtonsToShow = Math.min(Math.floor(unusedPanelSpace / mediumButtonMinWidth), hiddenButtonsCount);
					var buttonsLeftHidden = hiddenButtonsCount - countOfButtonsToShow;
					if (buttonsLeftHidden === 1)
					{
						countOfButtonsToShow += 1;
					}

					if (countOfButtonsToShow == hiddenButtonsCount)
					{
						// show all buttons;
						this.overflownButtons = {};
					}
					else
					{
						for (i = 0; i < countOfButtonsToShow; i++)
						{
							delete this.overflownButtons[Object.keys(this.overflownButtons)[0]]
						}
					}

					return true;
				}
			}

		}

		return false;
	};

	/**
	 * @param {string[]} buttons Array of buttons names to show
	 */
	BX.Call.View.prototype.showButtons = function(buttons)
	{
		if (!BX.type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach(function(buttonName)
		{
			if (this.hiddenButtons.hasOwnProperty(buttonName))
			{
				delete this.hiddenButtons[buttonName];
			}
		}, this)

		this.updateButtons();
	};

	/**
	 * @param {string[]} buttons Array of buttons names to hide
	 */
	BX.Call.View.prototype.hideButtons = function(buttons)
	{
		if (!BX.type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach(function(buttonName) {
			this.hiddenButtons[buttonName] = true;
		}, this);

		this.updateButtons();
	};

	BX.Call.View.prototype.blockAddUser = function()
	{
		this.blockedButtons['add'] = true;

		if(this.elements.userList.addButton)
		{
			BX.remove(this.elements.userList.addButton);
			this.elements.userList.addButton = null;
		}
	};

	BX.Call.View.prototype.blockSwitchCamera = function()
	{
		this.blockedButtons['camera'] = true;
	};

	BX.Call.View.prototype.unblockSwitchCamera = function()
	{
		delete this.blockedButtons['camera'];
	};

	BX.Call.View.prototype.blockScreenSharing = function()
	{
		this.blockedButtons['screen'] = true;
	};

	BX.Call.View.prototype.blockHistoryButton = function()
	{
		this.blockedButtons['history'] = true;
	};

	/**
	 * @param {string[]} buttons Array of buttons names to block
	 */
	BX.Call.View.prototype.blockButtons = function(buttons)
	{
		if (!BX.type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach(function(buttonName)
		{
			this.blockedButtons[buttonName] = true;
			if (this.buttons[buttonName])
			{
				this.buttons[buttonName].setBlocked(true);
			}
		}, this)
	};

	/**
	 * @param {string[]} buttons Array of buttons names to unblock
	 */
	BX.Call.View.prototype.unblockButtons = function(buttons)
	{
		if (!BX.type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach(function(buttonName)
		{
			delete this.blockedButtons[buttonName];
			if (this.buttons[buttonName])
			{
				this.buttons[buttonName].setBlocked(this.isButtonBlocked(buttonName));
			}
		}, this)
	};

	BX.Call.View.prototype.disableMediaSelection = function()
	{
		this.mediaSelectionBlocked = true;
	};

	BX.Call.View.prototype.enableMediaSelection = function()
	{
		this.mediaSelectionBlocked = false;
		if (this.buttons.microphone && this.isMediaSelectionAllowed())
		{
			this.buttons.microphone.showArrow();
		}
		if (this.buttons.camera && this.isMediaSelectionAllowed())
		{
			this.buttons.camera.showArrow();
		}
	};

	BX.Call.View.prototype.isMediaSelectionAllowed = function()
	{
		return this.layout != Layouts.Mobile && (this.uiState == UiState.Preparing || this.uiState == UiState.Connected) && !this.mediaSelectionBlocked && !this.isFullScreen;
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
			return ['title', 'spacer', 'returnToCall', 'hangup'];
		}

		var result = [];


		result.push('microphone');
		result.push('camera');

		if(this.layout != Layouts.Mobile)
		{
			result.push('speaker');
		}
		else
		{
			result.push('mobileMenu');
		}

		result.push('chat');
		result.push('users');

		if(this.layout != Layouts.Mobile)
		{
			result.push('floorRequest');
			result.push('screen');
			result.push('record');
			result.push('document');
		}

		result = result.filter(function(buttonCode)
		{
			return !this.hiddenButtons.hasOwnProperty(buttonCode) && !this.overflownButtons.hasOwnProperty(buttonCode);
		}, this);

		if (Object.keys(this.overflownButtons).length > 0)
		{
			result.push('more');
		}

		if(this.uiState == UiState.Preparing)
		{
			result.push('close');
		}
		else
		{
			result.push('hangup');
		}

		return result;
	};

	BX.Call.View.prototype.getTopButtonList = function()
	{
		var result = [];

		if (this.layout == Layouts.Mobile)
		{
			return ['participantsMobile'];
		}
		result.push('watermark');
		result.push('hd');
		result.push('separator');
		result.push('protected');
		result.push('recordStatus');
		result.push('spacer');

		var separatorNeeded = false;
		if(this.uiState === UiState.Connected && this.layout != Layouts.Mobile)
		{
			result.push('grid');
			separatorNeeded = true;
		}
		if(this.uiState != UiState.Preparing && this.isFullScreenSupported() && this.layout != Layouts.Mobile)
		{
			result.push('fullscreen');
			separatorNeeded = true;
		}

		if(this.uiState != UiState.Preparing)
		{
			if (separatorNeeded)
			{
				result.push('separator');
			}
			result.push('participants');
		}

		var previousButtonCode = '';
		result = result.filter(function(buttonCode)
		{
			if (
				previousButtonCode === 'spacer'
				&& buttonCode === 'separator'
			)
			{
				return true;
			}

			previousButtonCode = buttonCode;

			return !this.hiddenTopButtons.hasOwnProperty(buttonCode);
		}, this);

		return result;
	};

	BX.Call.View.prototype.render = function()
	{
		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall"},
			children: [
				this.elements.wrap = BX.create("div", {
					props: {className: "bx-messenger-videocall-wrap"},
					children: [
						this.elements.container = BX.create("div", {
							props: {className: "bx-messenger-videocall-inner"},
							children: [
								this.elements.center = BX.create("div", {
									props: {className: "bx-messenger-videocall-central-user"},
									events: {
										touchstart: this._onCenterTouchStart.bind(this),
										touchend: this._onCenterTouchEnd.bind(this),
									}
								}),
								this.elements.pageNavigatorLeft = BX.create("div", {
									props: {className: "bx-messenger-videocall-page-navigator left"},
									children: [
										this.elements.pageNavigatorLeftCounter = BX.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-counter left"},
											html: (this.currentPage - 1) + '&nbsp;/&nbsp;' + this.pagesCount
										}),
										BX.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-icon left"}
										}),
									],
									events: {
										click: this._onLeftPageNavigatorClick.bind(this)
									}
								}),
								this.elements.pageNavigatorRight = BX.create("div", {
									props: {className: "bx-messenger-videocall-page-navigator right"},
									children: [
										this.elements.pageNavigatorRightCounter = BX.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-counter right"},
											html: (this.currentPage + 1) + '&nbsp;/&nbsp;' + this.pagesCount
										}),
										BX.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-icon right"}
										})
									],
									events: {
										click: this._onRightPageNavigatorClick.bind(this)
									}
								}),
							]
						}),
						this.elements.topPanel = BX.create("div", {
							props: {className: "bx-messenger-videocall-top-panel"},
						}),
						this.elements.notificationPanel = BX.create("div", {
							props: {className: "bx-messenger-videocall-notification-panel"},
						}),
						this.elements.bottom = BX.create("div", {
							props: {className: "bx-messenger-videocall-bottom"},
							children: [
								this.elements.userSelectorContainer = BX.create("div", {
									props: {className: "bx-messenger-videocall-bottom-user-selector-container"}
								}),
								this.elements.pinnedUserContainer = BX.create("div", {
									props: {className: "bx-messenger-videocall-bottom-pinned-user-container"}
								}),
							]
						}),
					]
				}),
			],
			events: {
				click: this._onBodyClick.bind(this)
			}
		});

		if (this.uiState == UiState.Preparing)
		{
			this.elements.wrap.classList.add("with-clouds");
		}

		if(this.showButtonPanel)
		{
			this.elements.panel = BX.create("div", {
				props: {className: "bx-messenger-videocall-panel"},
			});
			this.elements.bottom.appendChild(this.elements.panel);
		}
		else
		{
			this.elements.root.classList.add("bx-messenger-videocall-no-button-panel");
		}

		if(this.layout == Layouts.Mobile)
		{
			this.userSelector = new UserSelectorMobile({
				userRegistry: this.userRegistry
			});
			this.userSelector.mount(this.elements.userSelectorContainer);

			this.elements.ear.left = BX.create("div", {
				props: {
					className: "bx-messenger-videocall-mobile-ear left"
				},
				events: {
					click: this._onLeftEarClick.bind(this)
				}
			});
			this.elements.ear.right = BX.create("div", {
				props: {
					className: "bx-messenger-videocall-mobile-ear right"
				},
				events: {
					click: this._onRightEarClick.bind(this)
				}
			});
			this.elements.localUserMobile = BX.create("div", {
				props: {className: "bx-messenger-videocall-local-user-mobile"}
			});

			if (window.innerHeight < window.innerWidth)
			{
				this.elements.root.classList.add("orientation-landscape");
			}

			this.elements.wrap.appendChild(this.elements.ear.left);
			this.elements.wrap.appendChild(this.elements.ear.right);
			this.elements.wrap.appendChild(this.elements.localUserMobile);
		}

		this.centralUser.mount(this.elements.center);

		this.elements.overlay = BX.create("div", {
			props: {className: "bx-messenger-videocall-overlay"}
		});

		this.elements.userBlock = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-block"},
			children: [
				this.elements.ear.top = BX.create("div", {
					props: {className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-top"},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-videocall-ear-icon"}
						})
					],
					events: {
						mouseenter: this.scrollUserListUp.bind(this),
						mouseleave: this.stopScroll.bind(this)
					}
				}),
				this.elements.ear.bottom = BX.create("div", {
					props: {className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-bottom"},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-videocall-ear-icon"}
						})
					],
					events: {
						mouseenter: this.scrollUserListDown.bind(this),
						mouseleave: this.stopScroll.bind(this)
					}
				})
			]
		});

		this.elements.userList.container = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-list"
			},
			events: {
				scroll: BX.debounce(this.toggleEars.bind(this), 300),
				wheel: function(e)
				{
					this.elements.userList.container.scrollTop += e.deltaY;
				}.bind(this)
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

		if(this.layout == Layouts.Centered || this.layout == Layouts.Mobile)
		{
			this.centralUser.mount(this.elements.center);
			this.elements.root.classList.add("bx-messenger-videocall-centered");
			if (this.layout != Layouts.Mobile)
			{
				this.elements.container.appendChild(this.elements.userBlock);
			}
		}
		if (this.layout == Layouts.Grid)
		{
			this.elements.root.classList.add("bx-messenger-videocall-grid");
		}
		if (this.layout == Layouts.Mobile)
		{
			this.elements.root.classList.add("bx-messenger-videocall-fullscreen-mobile");
		}

		this.resizeObserver.observe(this.elements.root);
		this.resizeObserver.observe(this.container);
		return this.elements.root;
	};

	BX.Call.View.prototype.renderUserList = function()
	{
		var showLocalUser = this.shouldShowLocalUser();
		var userCount = 0;
		var skipUsers = 0;
		var skippedUsers = 0;
		var renderedUsers = 0;

		if (this.layout == Layouts.Grid && this.pagesCount > 1)
		{
			skipUsers = (this.currentPage - 1) * this.usersPerPage;
		}

		for (var i = 0; i < this.userRegistry.users.length; i++)
		{
			var userModel = this.userRegistry.users[i];
			var userId = userModel.id;
			if (!this.users.hasOwnProperty(userId))
			{
				continue;
			}

			/** @type {CallUser} */
			var user = this.users[userId];
			var screenUser = this.screenUsers[userId];
			if(userId == this.centralUser.id && (this.layout == Layouts.Centered || this.layout == Layouts.Mobile))
			{
				this.unobserveIntersections(user);
				if (screenUser.hasVideo())
				{
					screenUser.mount(this.elements.center);
					screenUser.visible = true;
					user.mount(this.elements.userList.container);
				}
				else
				{
					user.visible = true;
					user.mount(this.elements.center);
					screenUser.dismount();
				}

				continue;
			}
			var userState = userModel.state;
			var userActive = (userState != BX.Call.UserState.Idle
				&& userState != BX.Call.UserState.Declined
				&& userState != BX.Call.UserState.Unavailable
				&& userState != BX.Call.UserState.Busy
				&& userModel.direction != BX.Call.EndpointDirection.RecvOnly
			);

			if (userActive && skipUsers > 0 && skippedUsers < skipUsers)
			{
				// skip users on previous pages
				skippedUsers++;
				userActive = false;
			}

			if (userActive && this.layout == Layouts.Grid && this.usersPerPage > 0 && renderedUsers >= this.usersPerPage)
			{
				// skip users on following pages
				userActive = false;
			}

			if(!userActive)
			{
				user.dismount();
				this.unobserveIntersections(user);
				screenUser.dismount();
				continue;
			}

			if (screenUser.hasVideo())
			{
				screenUser.mount(this.elements.userList.container);
				userCount++;
			}
			else
			{
				screenUser.dismount();
			}
			user.mount(this.elements.userList.container);
			this.observeIntersections(user);
			renderedUsers++;
			userCount++;
		}
		if(showLocalUser)
		{
			if(this.layout == Layouts.Centered && this.userId == this.centralUser.id || this.layout == Layouts.Mobile)
			{
				// this.unobserveIntersections(this.localUser);
				this.localUser.mount(this.elements.center, true);
				this.localUser.visible = true;
			}
			else
			{
				// using force true to always move self to the end of the list
				this.localUser.mount(this.elements.userList.container);
				if(this.layout == Layouts.Centered && this.intersectionObserver)
				{
					// this.observeIntersections(this.localUser);
				}
				else
				{
					this.localUser.visible = true;
				}
			}

			userCount++;
		}
		else
		{
			this.localUser.dismount();
			// this.unobserveIntersections(this.localUser);
		}

		if (this.layout == Layouts.Grid)
		{
			this.updateGridUserSize(userCount);
		}
		else
		{
			this.elements.userList.container.classList.add("bx-messenger-videocall-user-list-small");
			this.elements.userList.container.style.removeProperty('--avatar-size');
			this.updateCentralUserAvatarSize();
		}
		this.applyIncomingVideoConstraints();

		var showAdd = this.layout == Layouts.Centered && userCount > 0 /*&& !this.isFullScreen*/ && this.uiState === UiState.Connected && !this.isButtonBlocked("add") && this.getConnectedUserCount() < this.userLimit - 1;
		if (showAdd && !this.isFullScreen)
		{
			this.elements.userList.container.appendChild(this.elements.userList.addButton);
		}
		else
		{
			BX.remove(this.elements.userList.addButton);
		}

		this.elements.root.classList.toggle("bx-messenger-videocall-user-list-empty", (this.elements.userList.container.childElementCount === 0));
		this.localUser.updatePanelDeferred();
	};

	BX.Call.View.prototype.shouldShowLocalUser = function()
	{
		return (
			this.localUser.userModel.state != BX.Call.UserState.Idle
			&& this.localUser.userModel.direction != BX.Call.EndpointDirection.RecvOnly
		);
	};

	BX.Call.View.prototype.updateGridUserSize = function(userCount)
	{
		var containerSize = this.elements.userList.container.getBoundingClientRect();
		this.userSize = BX.Call.Util.findBestElementSize(
			containerSize.width,
			containerSize.height,
			userCount,
			MIN_GRID_USER_WIDTH,
			MIN_GRID_USER_HEIGHT
		);

		var avatarSize = Math.round(this.userSize.height * 0.45);
		this.elements.userList.container.style.setProperty('--grid-user-width', this.userSize.width + 'px');
		this.elements.userList.container.style.setProperty('--grid-user-height', this.userSize.height + 'px');
		this.elements.userList.container.style.setProperty('--avatar-size', avatarSize + 'px');
		if (this.userSize.width < 220)
		{
			this.elements.userList.container.classList.add("bx-messenger-videocall-user-list-small");
		}
		else
		{
			this.elements.userList.container.classList.remove("bx-messenger-videocall-user-list-small");
		}
	};

	BX.Call.View.prototype.updateCentralUserAvatarSize = function()
	{
		var containerSize;
		var avatarSize;
		if (this.layout == Layouts.Mobile)
		{
			containerSize = this.elements.root.getBoundingClientRect();
			avatarSize = Math.round(containerSize.width * 0.55);
		}
		else if (this.layout == Layouts.Centered)
		{
			containerSize = this.elements.center.getBoundingClientRect();
			avatarSize = Math.round(containerSize.height * 0.45);
			avatarSize = Math.min(avatarSize, 142);
			this.centralUser.setIncomingVideoConstraints(Math.floor(containerSize.width), Math.floor(containerSize.height));
		}
		this.elements.center.style.setProperty('--avatar-size', avatarSize + 'px');
	};

	/**
	 * @return {Element}
	 */
	BX.Call.View.prototype.renderButtons = function(buttons)
	{
		var panelInner, left, center, right;

		panelInner = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-inner"}
		});

		if (this.layout === Layouts.Mobile || this.size === BX.Call.View.Size.Folded)
		{
			left = panelInner;
			center = panelInner;
			right = panelInner;
		}
		else
		{
			left = BX.create("div", {
				props: {className: "bx-messenger-videocall-panel-inner-left"},
			});
			center = BX.create("div", {
				props: {className: "bx-messenger-videocall-panel-inner-center"},
			});
			right = BX.create("div", {
				props: {className: "bx-messenger-videocall-panel-inner-right"},
			});
			panelInner.appendChild(left);
			panelInner.appendChild(center);
			panelInner.appendChild(right);
		}

		for (var i = 0; i < buttons.length; i++)
		{
			switch (buttons[i])
			{
				case "title":
					this.buttons.title = new TitleButton({
						text: this.title,
						isGroupCall: Object.keys(this.users).length > 1
					});
					left.appendChild(this.buttons.title.render());
					break;
				/*case "grid":
					this.buttons.grid = new SimpleButton({
						class: "grid",
						text: BX.message("IM_M_CALL_BTN_GRID"),
						onClick: this._onGridButtonClick.bind(this)
					});
					panelInner.appendChild(this.buttons.grid.render());
					break;*/
				/*case "add":
					this.buttons.add = new SimpleButton({
						class: "add",
						text: BX.message("IM_M_CALL_BTN_ADD"),
						onClick: this._onAddButtonClick.bind(this)
					});
					leftSubPanel.appendChild(this.buttons.add.render());
					break;*/
				case "share":
					this.buttons.share = new SimpleButton({
						class: "share",
						text: BX.message("IM_M_CALL_BTN_LINK"),
						onClick: this._onShareButtonClick.bind(this)
					});
					center.appendChild(this.buttons.share.render());
					break;
				case "microphone":
					this.buttons.microphone = new DeviceButton({
						class: "microphone",
						text: BX.message("IM_M_CALL_BTN_MIC"),
						enabled: !this.isMuted,
						arrowHidden: this.layout == Layouts.Mobile,
						arrowEnabled: this.isMediaSelectionAllowed(),
						showPointer: true, //todo
						blocked: this.isButtonBlocked("microphone"),
						showLevel: true,
						sideIcon: this.getMicrophoneSideIcon(this.roomState),
						onClick: function(e)
						{
							this._onMicrophoneButtonClick(e);
							this._showMicrophoneHint(e);
						}.bind(this),
						onArrowClick: this._onMicrophoneArrowClick.bind(this),
						onMouseOver: this._showMicrophoneHint.bind(this),
						onMouseOut: function(e)
						{
							this._destroyHotKeyHint();
						}.bind(this),
						onSideIconClick: this._onMicrophoneSideIconClick.bind(this),
					});
					left.appendChild(this.buttons.microphone.render());
					break;
				case "camera":
					this.buttons.camera = new DeviceButton({
						class: "camera",
						text: BX.message("IM_M_CALL_BTN_CAMERA"),
						enabled: this.isCameraOn,
						arrowHidden: this.layout == Layouts.Mobile,
						arrowEnabled: this.isMediaSelectionAllowed(),
						blocked: this.isButtonBlocked("camera"),
						onClick: this._onCameraButtonClick.bind(this),
						onArrowClick: this._onCameraArrowClick.bind(this),
						onMouseOver: function(e)
						{
							this._showHotKeyHint(e.currentTarget.firstChild, "camera", this.keyModifier + " + V");
						}.bind(this),
						onMouseOut: function(e)
						{
							this._destroyHotKeyHint();
						}.bind(this)
					});
					left.appendChild(this.buttons.camera.render());
					break;
				case "screen":
					if(!this.buttons.screen)
					{
						this.buttons.screen = new SimpleButton({
							class: "screen",
							text: BX.message("IM_M_CALL_BTN_SCREEN"),
							blocked: this.isButtonBlocked("screen"),
							onClick: this._onScreenButtonClick.bind(this),
							onMouseOver: function(e)
							{
								this._showHotKeyHint(e.currentTarget, "screen", this.keyModifier + " + S");
							}.bind(this),
							onMouseOut: function(e)
							{
								this._destroyHotKeyHint();
							}.bind(this)
						});
					}
					else
					{
						this.buttons.screen.setBlocked(this.isButtonBlocked("screen"));
					}
					center.appendChild(this.buttons.screen.render());
					break;
				case "users":
					if(!this.buttons.users)
					{
						this.buttons.users = new SimpleButton({
							class: "users",
							backgroundClass: "calm-counter",
							text: BX.message("IM_M_CALL_BTN_USERS"),
							blocked: this.isButtonBlocked("users"),
							onClick: this._onUsersButtonClick.bind(this),
							onMouseOver: function(e) {
								this._showHotKeyHint(e.currentTarget, "users", this.keyModifier + ' + U');
							}.bind(this),
							onMouseOut: function(e) {
								this._destroyHotKeyHint();
							}.bind(this)
						});
					}
					else
					{
						this.buttons.users.setBlocked(this.isButtonBlocked("users"));
					}
					center.appendChild(this.buttons.users.render());
					break;
				case "record":
					if(!this.buttons.record)
					{
						this.buttons.record = new SimpleButton({
							class: "record",
							backgroundClass: "bx-messenger-videocall-panel-background-record",
							text: BX.message("IM_M_CALL_BTN_RECORD"),
							blocked: this.isButtonBlocked("record"),
							onClick: this._onRecordToggleClick.bind(this),
							onMouseOver: function(e)
							{
								if (this.isRecordingHotKeySupported())
								{
									this._showHotKeyHint(e.currentTarget, "record", this.keyModifier + " + R");
								}
							}.bind(this),
							onMouseOut: function(e)
							{
								if (this.isRecordingHotKeySupported())
								{
									this._destroyHotKeyHint();
								}
							}.bind(this)
						});
					}
					else
					{
						this.buttons.record.setBlocked(this.isButtonBlocked('record'));
					}
					center.appendChild(this.buttons.record.render());
					break;
				case "document":
					if (!this.buttons.document)
					{
						this.buttons.document = new SimpleButton({
							class: "document",
							text: BX.message("IM_M_CALL_BTN_DOCUMENT"),
							blocked: this.isButtonBlocked("document"),
							onClick: this._onDocumentButtonClick.bind(this)
						});
					}
					else
					{
						this.buttons.document.setBlocked(this.isButtonBlocked('document'));
					}
					center.appendChild(this.buttons.document.render());
					break;
				case "returnToCall":
					this.buttons.returnToCall = new SimpleButton({
						class: "returnToCall",
						text: BX.message("IM_M_CALL_BTN_RETURN_TO_CALL"),
						onClick: this._onBodyClick.bind(this)
					});
					right.appendChild(this.buttons.returnToCall.render());
					break;
				case "hangup":
					this.buttons.hangup = new SimpleButton({
						class: "hangup",
						backgroundClass: "bx-messenger-videocall-panel-icon-background-hangup",
						text: Object.keys(this.users).length > 1 ?  BX.message("IM_M_CALL_BTN_DISCONNECT") : BX.message("IM_M_CALL_BTN_HANGUP"),
						onClick: this._onHangupButtonClick.bind(this)
					});
					right.appendChild(this.buttons.hangup.render());
					break;
				case "close":
					this.buttons.close = new SimpleButton({
						class: "close",
						backgroundClass: "bx-messenger-videocall-panel-icon-background-hangup",
						text: BX.message("IM_M_CALL_BTN_CLOSE"),
						onClick: this._onCloseButtonClick.bind(this)
					});
					right.appendChild(this.buttons.close.render());
					break;
				case "speaker":
					/*this.buttons.speaker = new DeviceButton({
						class: "speaker",
						text: BX.message("IM_M_CALL_BTN_SPEAKER"),
						enabled: !this.speakerMuted,
						arrowEnabled: BX.Call.Hardware.canSelectSpeaker() && this.isMediaSelectionAllowed(),
						onClick: this._onSpeakerButtonClick.bind(this),
						onArrowClick: this._onSpeakerArrowClick.bind(this)
					});
					rightSubPanel.appendChild(this.buttons.speaker.render());*/
					break;
				case "mobileMenu":
					if (!this.buttons.mobileMenu)
					{
						this.buttons.mobileMenu = new SimpleButton({
							class: "sandwich",
							text: BX.message("IM_M_CALL_BTN_MENU"),
							onClick: this._onMobileMenuButtonClick.bind(this)
						})
					}
					center.appendChild(this.buttons.mobileMenu.render());
					break;
				case "chat":
					if(!this.buttons.chat)
					{
						this.buttons.chat = new SimpleButton({
							class: "chat",
							text: BX.message("IM_M_CALL_BTN_CHAT"),
							blocked: this.isButtonBlocked("chat"),
							onClick: this._onChatButtonClick.bind(this),
							onMouseOver: function(e)
							{
								this._showHotKeyHint(e.currentTarget, "chat", this.keyModifier + " + C");
							}.bind(this),
							onMouseOut: function(e)
							{
								this._destroyHotKeyHint();
							}.bind(this)
						});
					}
					else
					{
						this.buttons.chat.setBlocked(this.isButtonBlocked('chat'));
					}
					center.appendChild(this.buttons.chat.render());
					break;
				case "floorRequest":
					if(!this.buttons.floorRequest)
					{
						this.buttons.floorRequest = new SimpleButton({
							class: "floor-request",
							backgroundClass: "bx-messenger-videocall-panel-background-floor-request",
							text: BX.message("IM_M_CALL_BTN_WANT_TO_SAY"),
							blocked: this.isButtonBlocked("floorRequest"),
							onClick: this._onFloorRequestButtonClick.bind(this),
							onMouseOver: function(e) {
								this._showHotKeyHint(e.currentTarget, "floorRequest", this.keyModifier + " + H");
							}.bind(this),
							onMouseOut: function(e) {
								this._destroyHotKeyHint();
							}.bind(this)
						});
					}
					else
					{
						this.buttons.floorRequest.setBlocked(this.isButtonBlocked('floorRequest'));
					}
					center.appendChild(this.buttons.floorRequest.render());
					break;
				case "more":
					if (!this.buttons.more)
					{
						this.buttons.more = new SimpleButton({
							class: "more",
							onClick: this._onMoreButtonClick.bind(this)
						})
					}
					center.appendChild(this.buttons.more.render());
					break;
				case "spacer":
					panelInner.appendChild(BX.create("div", {
						props: {className: "bx-messenger-videocall-panel-spacer"}
					}));
					break;
				/*case "history":
					this.buttons.history = new SimpleButton({
						class: "history",
						text: BX.message("IM_M_CALL_BTN_HISTORY"),
						onClick: this._onHistoryButtonClick.bind(this)
					});
					rightSubPanel.appendChild(this.buttons.history.render());
					break;*/
			}
		}

		return panelInner;
	};

	BX.Call.View.prototype.renderTopButtons = function(buttons)
	{
		var result = BX.createFragment();

		for (var i = 0; i < buttons.length; i++)
		{
			switch (buttons[i])
			{
				case "watermark":
					this.buttons.waterMark = new WaterMarkButton({
						language: this.language
					});
					result.appendChild(this.buttons.waterMark.render());
					break;
				case "hd":
					this.buttons.hd = new TopFramelessButton({
						iconClass: "hd"
					});
					result.appendChild(this.buttons.hd.render());
					break;
				case "protected":
					this.buttons.protected = new TopFramelessButton({
						iconClass: "protected",
						textClass: "protected",
						text: BX.message("IM_M_CALL_PROTECTED"),
						onMouseOver: function(e)
						{
							this.hintManager.show(e.currentTarget, BX.message("IM_M_CALL_PROTECTED_HINT"));
						}.bind(this),
						onMouseOut: function(e)
						{
							this.hintManager.hide();
						}.bind(this)
					});
					result.appendChild(this.buttons.protected.render());
					break;
				case "recordStatus":
					if (this.buttons.recordStatus)
					{
						this.buttons.recordStatus.updateView();
					}
					else
					{
						this.buttons.recordStatus = new RecordStatusButton({
							userId: this.userId,
							recordState: this.recordState,
							onPauseClick: this._onRecordPauseClick.bind(this),
							onStopClick: this._onRecordStopClick.bind(this),
							onMouseOver: this._onRecordMouseOver.bind(this),
							onMouseOut: this._onRecordMouseOut.bind(this)
						});
					}
					result.appendChild(this.buttons.recordStatus.render());
					break;
				case "grid":
					this.buttons.grid = new TopButton({
						iconClass: this.layout == Layouts.Grid ? "speaker" : "grid",
						text: this.layout == Layouts.Grid ?  BX.message("IM_M_CALL_SPEAKER_MODE") : BX.message("IM_M_CALL_GRID_MODE"),
						onClick: this._onGridButtonClick.bind(this),
						onMouseOver: function(e) {
							this._showHotKeyHint(e.currentTarget, "grid", this.keyModifier + " + W", {position: "bottom"});
						}.bind(this),
						onMouseOut: function(e) {
							this._destroyHotKeyHint();
						}.bind(this)
					});
					result.appendChild(this.buttons.grid.render());
					break;
				case "fullscreen":
					this.buttons.fullscreen = new TopButton({
						iconClass: this.isFullScreen ? "fullscreen-leave" : "fullscreen-enter",
						text: this.isFullScreen ? BX.message("IM_M_CALL_WINDOW_MODE") : BX.message("IM_M_CALL_FULLSCREEN_MODE"),
						onClick: this._onFullScreenButtonClick.bind(this)
					});
					result.appendChild(this.buttons.fullscreen.render());
					break;
				case "participants":
					var foldButtonState;

					if (this.isFullScreen && this.layout == Layouts.Centered)
					{
						foldButtonState = this.isUserBlockFolded ? ParticipantsButton.FoldButtonState.Unfold : ParticipantsButton.FoldButtonState.Fold
					}
					else if (this.showUsersButton)
					{
						foldButtonState = ParticipantsButton.FoldButtonState.Active;
					}
					else
					{
						foldButtonState = ParticipantsButton.FoldButtonState.Hidden;
					}

					if (this.buttons.participants)
					{
						this.buttons.participants.update({
							foldButtonState: foldButtonState,
							allowAdding: !this.isButtonBlocked("add"),
							count: this.getConnectedUserCount(true),
						});
					}
					else
					{
						this.buttons.participants = new ParticipantsButton({
							foldButtonState: foldButtonState,
							allowAdding: !this.isButtonBlocked("add"),
							count: this.getConnectedUserCount(true),
							onListClick: this._onParticipantsButtonListClick.bind(this),
							onAddClick: this._onAddButtonClick.bind(this)
						});
					}

					result.appendChild(this.buttons.participants.render());
					break;
				case "participantsMobile":
					this.buttons.participantsMobile = new ParticipantsButtonMobile({
						count: this.getConnectedUserCount(true),
						onClick: this._onParticipantsButtonMobileListClick.bind(this),
					});
					result.appendChild(this.buttons.participantsMobile.render());
					break;
				case "separator":
					result.appendChild(BX.create("div", {
						props: {className: "bx-messenger-videocall-top-separator"}
					}));
					break;
				case "spacer":
					result.appendChild(BX.create("div", {
						props: {className: "bx-messenger-videocall-top-panel-spacer"}
					}));
					break;
			}
		}
		return result;
	};

	BX.Call.View.prototype.calculateUnusedPanelSpace = function(buttonList)
	{
		if (!buttonList)
		{
			buttonList = this.getButtonList();
		}

		var totalButtonWidth = 0;
		for (var i = 0; i < buttonList.length; i++)
		{
			var button = this.buttons[buttonList[i]];
			if (!button)
			{
				continue;
			}
			buttonWidth = button.elements.root ? button.elements.root.getBoundingClientRect().width : 0;
			totalButtonWidth += buttonWidth;
		}
		return this.elements.panel.scrollWidth - totalButtonWidth - 32;
	};

	BX.Call.View.prototype.setButtonActive = function(buttonName, isActive)
	{
		if(!this.buttons[buttonName])
		{
			return;
		}

		this.buttons[buttonName].setActive(isActive);
	};

	BX.Call.View.prototype.getButtonActive = function(buttonName)
	{
		if(!this.buttons[buttonName])
		{
			return false;
		}

		return this.buttons[buttonName].isActive;
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
		if (this.layout == Layouts.Mobile)
		{
			if (this.localUser != this.centralUser)
			{
				if (this.localUser.hasVideo())
				{
					this.localUser.mount(this.elements.localUserMobile);
					this.localUser.visible = true;
				}
				else
				{
					this.localUser.dismount();
				}

				this.centralUser.mount(this.elements.center);
				this.centralUser.visible = true;
			}
			return;
		}
		if (this.layout == Layouts.Grid && this.size == BX.Call.View.Size.Full)
		{
			this.recalculatePages();
		}
		this.renderUserList();

		if (this.layout == Layouts.Centered)
		{
			if(!this.elements.userList.container.parentElement)
			{
				this.elements.userBlock.appendChild(this.elements.userList.container);
			}
			//this.centralUser.setFullSize(this.elements.userList.container.childElementCount === 0);

		}
		else if (this.layout == Layouts.Grid)
		{
			if(!this.elements.userList.container.parentElement)
			{
				this.elements.container.appendChild(this.elements.userList.container);
			}
		}
		this.toggleEars();
	};

	BX.Call.View.prototype.showOverflownButtonsPopup = function()
	{
		if (this.overflownButtonsPopup)
		{
			this.overflownButtonsPopup.show();
			return;
		}

		var bindElement = this.buttons.more && this.buttons.more.elements.root ? this.buttons.more.elements.root : this.elements.panel;

		this.overflownButtonsPopup = new BX.PopupWindow('bx-call-buttons-popup', bindElement, {
			targetContainer: this.container,
			content: this.renderButtons(Object.keys(this.overflownButtons)),
			cacheable: false,
			closeIcon: false,
			autoHide: true,
			overlay: {backgroundColor: 'white', opacity: 0},
			bindOptions: {
				position: 'top'
			},
			angle: {position: 'bottom', offset: 49},
			className: 'bx-call-buttons-popup',
			contentBackground: 'unset',
			events: {
				onPopupDestroy: function()
				{
					this.overflownButtonsPopup = null;
					this.buttons.more.setActive(false);
				}.bind(this),
			}
		});
		this.overflownButtonsPopup.show();
	}

	BX.Call.View.prototype.resumeVideo = function()
	{
		for (var userId in this.users)
		{
			/** @type {CallUser} */
			var user = this.users[userId];
			user.playVideo()
			/** @type {CallUser} */
			var screenUser = this.screenUsers[userId];
			screenUser.playVideo();
		}
		this.localUser.playVideo(true);
	};

	BX.Call.View.prototype.updateUserButtons = function()
	{
		for (var userId in this.users)
		{
			if (this.users.hasOwnProperty(userId))
			{
				this.users[userId].allowPinButton = this.getConnectedUserCount() > 1;
			}
		}
	};

	BX.Call.View.prototype.updateButtons = function()
	{
		if(!this.elements.panel)
		{
			return;
		}
		BX.cleanNode(this.elements.panel);
		BX.cleanNode(this.elements.topPanel);
		this.elements.panel.appendChild(this.renderButtons(this.getButtonList()));
		if(this.elements.topPanel)
		{
			this.elements.topPanel.appendChild(this.renderTopButtons(this.getTopButtonList()));
		}
		if (this.buttons.participantsMobile)
		{
			this.buttons.participantsMobile.setCount(this.getConnectedUserCount(true));
		}
	};

	BX.Call.View.prototype.updateUserData = function(userData)
	{
		for(var userId in userData)
		{
			if(!this.userData[userId])
			{
				this.userData[userId] = {
					name: '',
					avatar_hr: '',
					gender: 'M'
				}
			}
			if(userData[userId].name)
			{
				this.userData[userId].name = userData[userId].name;
			}

			if(userData[userId].avatar_hr)
			{
				this.userData[userId].avatar_hr = BX.Call.Util.isAvatarBlank(userData[userId].avatar_hr) ? '' : userData[userId].avatar_hr;
			}
			else if(userData[userId].avatar)
			{
				this.userData[userId].avatar_hr = BX.Call.Util.isAvatarBlank(userData[userId].avatar) ? '' : userData[userId].avatar;
			}

			if(userData[userId].gender)
			{
				this.userData[userId].gender = userData[userId].gender === 'F' ? 'F' : 'M';
			}

			var userModel = this.userRegistry.get(userId);
			if (userModel)
			{
				userModel.name = this.userData[userId].name;
				userModel.avatar = this.userData[userId].avatar_hr;
			}
		}
	};

	BX.Call.View.prototype.isScreenSharingSupported = function()
	{
		return navigator.mediaDevices && typeof(navigator.mediaDevices.getDisplayMedia) === "function" || typeof(BXDesktopSystem) !== "undefined";
	};

	BX.Call.View.prototype.isRecordingHotKeySupported = function()
	{
		return typeof(BXDesktopSystem) !== "undefined" && BXDesktopSystem.ApiVersion() >= 60;
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

	BX.Call.View.prototype.toggleEars = function()
	{
		this.toggleTopEar();
		this.toggleBottomEar();

		if (this.layout == Layouts.Grid && this.pagesCount > 1 && this.currentPage > 1)
		{
			this.elements.pageNavigatorLeft.classList.add("active");
		}
		else
		{
			this.elements.pageNavigatorLeft.classList.remove("active");
		}

		if (this.layout == Layouts.Grid && this.pagesCount > 1 && this.currentPage < this.pagesCount)
		{
			this.elements.pageNavigatorRight.classList.add("active");
		}
		else
		{
			this.elements.pageNavigatorRight.classList.remove("active");
		}
	};

	BX.Call.View.prototype.toggleTopEar = function()
	{
		if (
			this.layout !== Layouts.Grid
			&& this.elements.userList.container.scrollHeight > this.elements.userList.container.offsetHeight
			&& this.elements.userList.container.scrollTop > 0
	  	   )
		{
			this.elements.ear.top.classList.add("active");
		}
		else
		{
			this.elements.ear.top.classList.remove("active");
		}
	};

	BX.Call.View.prototype.toggleBottomEar = function()
	{
		if (
			this.layout !== Layouts.Grid
			&& (this.elements.userList.container.offsetHeight + this.elements.userList.container.scrollTop) < this.elements.userList.container.scrollHeight
		   )
		{
			this.elements.ear.bottom.classList.add("active");
		}
		else
		{
			this.elements.ear.bottom.classList.remove("active");
		}
	};

	BX.Call.View.prototype.scrollUserListUp = function()
	{
		this.stopScroll();
		this.scrollInterval = setInterval(
			function()
			{
				this.elements.userList.container.scrollTop -= 10;
			}.bind(this),
			20
		);
	};

	BX.Call.View.prototype.scrollUserListDown = function()
	{
		this.stopScroll();
		this.scrollInterval = setInterval(
			function()
			{
				this.elements.userList.container.scrollTop+= 10;
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

	BX.Call.View.prototype.toggleRenameSliderInputLoader = function()
	{
		this.elements.renameSlider.button.classList.add('ui-btn-wait');
	};


	BX.Call.View.prototype.setHotKeyTemporaryBlock = function(isActive, force)
	{
		if (!!isActive)
		{
			this.hotKeyTemporaryBlock++;
		}
		else
		{

			this.hotKeyTemporaryBlock--;
			if (this.hotKeyTemporaryBlock < 0 || force)
			{
				this.hotKeyTemporaryBlock = 0;
			}
		}
	}

	BX.Call.View.prototype.setHotKeyActive = function(name, isActive)
	{
		if (typeof this.hotKey[name] === 'undefined')
		{
			return;
		}

		this.hotKey[name] = !!isActive;
	};

	BX.Call.View.prototype.isHotKeyActive = function(name)
	{
		if (!this.hotKey['all'])
		{
			return false;
		}

		if (this.hotKeyTemporaryBlock > 0)
		{
			return false;
		}

		if (this.isButtonHidden(name))
		{
			return false;
		}

		if (this.isButtonBlocked(name))
		{
			return false;
		}

		return !!this.hotKey[name];
	}

	// event handlers

	BX.Call.View.prototype._onBodyClick = function(e)
	{
		this.eventEmitter.emit(EventName.onBodyClick);
	};

	BX.Call.View.prototype._onCenterTouchStart = function(e)
	{
		this.centerTouchX = e.pageX;
	};

	BX.Call.View.prototype._onCenterTouchEnd = function(e)
	{
		var delta = e.pageX - this.centerTouchX;

		if (delta > 100)
		{
			this.pinUser(this.getRightUser(this.centralUser.id));
			e.preventDefault();
		}
		if (delta < -100)
		{
			this.pinUser(this.getLeftUser(this.centralUser.id));
			e.preventDefault();
		}
	};

	BX.Call.View.prototype._onFullScreenChange = function(e)
	{
		if ("webkitFullscreenElement" in document)
		{
			this.isFullScreen = (!!document.webkitFullscreenElement);
		}
		else if ("fullscreenElement" in document)
		{
			this.isFullScreen = (!!document.fullscreenElement);
		}
		else
		{
			return;
		}

		// safari workaround
		setTimeout(function()
		{
			if (!this.elements.root)
			{
				return;
			}
			if (this.isFullScreen)
			{
				this.elements.root.classList.add("bx-messenger-videocall-fullscreen");
			}
			else
			{
				this.elements.root.classList.remove("bx-messenger-videocall-fullscreen");
			}
			this.updateUserList();
			this.updateButtons();
			this.setUserBlockFolded(this.isFullScreen);

		}.bind(this), 0);
	};

	BX.Call.View.prototype._onIntersectionChange = function(entries)
	{
		var t = {};
		entries.forEach(function(intersectionEntry)
		{
			t[intersectionEntry.target.dataset.userId] = intersectionEntry.isIntersecting;
		});
		for (var userId in t)
		{
			if (this.users[userId])
			{
				this.users[userId].visible = t[userId];
			}
			if (userId == this.localUser.id)
			{
				this.localUser.visible = t[userId];
			}
		}
	};

	BX.Call.View.prototype._onResize = function()
	{
		// this.resizeCalled++;
		// this.reportResizeCalled();

		if(!this.elements.root)
		{
			return;
		}
		if(this.centralUser)
		{
			//this.centralUser.updateAvatarWidth();
		}
		if (BX.browser.IsMobile())
		{
			document.documentElement.style.setProperty('--view-height', window.innerHeight + 'px');
		}
		if(this.layout == Layouts.Grid)
		{
			this.updateUserList();
		}
		else
		{
			this.updateCentralUserAvatarSize();
			this.toggleEars();
		}

		var rootDimensions = this.elements.root.getBoundingClientRect()
		this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-450", rootDimensions.width < 450);
		this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-550", rootDimensions.width < 550);
		this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-650", rootDimensions.width < 650);
		this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-700", rootDimensions.width < 700);
		this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-850", rootDimensions.width < 850);
		this.elements.root.classList.toggle("bx-messenger-videocall-width-lt-900", rootDimensions.width < 900);

		/*if (this.maxWidth === 0)
		{
			this.elements.root.style.maxWidth = this.container.clientWidth + 'px';
		}*/

		if (this.checkPanelOverflow())
		{
			this.updateButtons();
			if (this.overflownButtonsPopup && !Object.keys(this.overflownButtons).length)
			{
				this.overflownButtonsPopup.close();
			}
		}
	};

	BX.Call.View.prototype._onOrientationChange = function()
	{
		if (!this.elements.root)
		{
			return;
		}
		if (window.innerHeight > window.innerWidth)
		{
			this.elements.root.classList.remove("orientation-landscape");
		}
		else
		{
			this.elements.root.classList.add("orientation-landscape");
		}
	};

	BX.Call.View.prototype._showHotKeyHint = function(targetNode, name, text, options)
	{
		var existingHint = BX.PopupWindowManager.getPopupById('ui-hint-popup');
		if (existingHint)
		{
			existingHint.destroy();
		}

		if (!this.isHotKeyActive(name))
		{
			return;
		}

		options = options || {};

		this.hintManager.popupParameters.events = {
			onShow: function(event) {
				var popup = event.getTarget();
				// hack to get hint sizes
				popup.getPopupContainer().style.display = 'block';
				if (options.position === 'bottom')
				{
					popup.setOffset({
						offsetTop: 10,
						offsetLeft: (targetNode.offsetWidth / 2) - (popup.getPopupContainer().offsetWidth / 2)
					});
				}
				else
				{
					popup.setOffset({
						offsetLeft: (targetNode.offsetWidth / 2) - (popup.getPopupContainer().offsetWidth / 2)
					});
				}
			}
		}

		this.hintManager.show(
			targetNode,
			text
		);
	}

	BX.Call.View.prototype._destroyHotKeyHint = function()
	{
		if (!BX.Call.Util.isDesktop())
		{
			return;
		}

		if (!this.hintManager.popup)
		{
			return;
		}

		// we need to destroy, not .hide for onShow event handler (see method _showHotKeyHint).
		this.hintManager.popup.destroy();
		this.hintManager.popup = null;
	}

	BX.Call.View.prototype._showMicrophoneHint = function(e)
	{
		this.hintManager.hide();

		if (!this.isHotKeyActive("microphone"))
		{
			return;
		}

		var micHotkeys = '';
		if (this.isMuted && this.isHotKeyActive("microphoneSpace"))
		{
			micHotkeys = BX.message("IM_SPACE_HOTKEY") + '<br>';
		}
		micHotkeys += this.keyModifier + ' + A';

		this._showHotKeyHint(e.currentTarget.firstChild, "microphone", micHotkeys);
	}

	BX.Call.View.prototype._onKeyDown = function(e)
	{
		if (!BX.Call.Util.isDesktop())
		{
			return;
		}
		if (!(e.shiftKey && (e.ctrlKey || e.metaKey)) && !(e.code === 'Space'))
		{
			return;
		}
		if (event.repeat)
		{
			return;
		}

		var callMinimized = this.size === BX.Call.View.Size.Folded;

		if (
			e.code === 'KeyA'
			&& this.isHotKeyActive('microphone')
		)
		{
			e.preventDefault();
			this._onMicrophoneButtonClick(e);
		}
		else if (
			e.code === 'Space' && this.isMuted
			&& this.isHotKeyActive('microphoneSpace')
		)
		{
			if (!callMinimized)
			{
				e.preventDefault();
				this.pushToTalk = true;
				this.microphoneHotkeyTimerId = setTimeout(function () {
					this._onMicrophoneButtonClick(e);
				}.bind(this), 100);
			}
		}
		else if (
			e.code === 'KeyS'
			&& this.isHotKeyActive('screen')
		)
		{
			e.preventDefault();
			this._onScreenButtonClick(e);
		}
		else if (
			e.code === 'KeyV'
			&& this.isHotKeyActive('camera')
		)
		{
			e.preventDefault();
			this._onCameraButtonClick(e);
		}
		else if (
			e.code === 'KeyU'
			&& this.isHotKeyActive('users')
		)
		{
			e.preventDefault();
			this._onUsersButtonClick(e);
		}
		else if (
			e.code === 'KeyR'
			&& this.isRecordingHotKeySupported()
			&& this.isHotKeyActive('record')
		)
		{
			e.preventDefault();
			this._onForceRecordToggleClick(e);
		}
		else if (
			e.code === 'KeyH'
			&& this.isHotKeyActive('floorRequest')
		)
		{
			e.preventDefault();
			this._onFloorRequestButtonClick(e);
		}
		else if (
			e.code === 'KeyC'
			&& this.isHotKeyActive('chat')
		)
		{
			e.preventDefault();
			if (callMinimized)
			{
				this._onBodyClick(e);
			}
			else
			{
				this._onChatButtonClick(e);
				this._destroyHotKeyHint();
			}
		}
		else if (
			e.code === 'KeyM'
			&& this.isHotKeyActive('muteSpeaker')
		)
		{
			e.preventDefault();
			this.eventEmitter.emit(EventName.onButtonClick, {
				buttonName: "toggleSpeaker",
				speakerMuted: this.speakerMuted,
				fromHotKey: true,
			});
		}
		else if (
			e.code === 'KeyW'
			&& this.isHotKeyActive('grid')
		)
		{
			e.preventDefault();
			this.setLayout(this.layout == Layouts.Centered ? Layouts.Grid : Layouts.Centered);
		}
	};

	BX.Call.View.prototype._onKeyUp = function(e)
	{
		if (!BX.Call.Util.isDesktop())
		{
			return;
		}

		clearTimeout(this.microphoneHotkeyTimerId);
		if (this.pushToTalk && !this.isMuted && e.code === 'Space')
		{
			e.preventDefault();
			this.pushToTalk = false;
			this._onMicrophoneButtonClick(e);
		}
	};

	BX.Call.View.prototype._onUserClick = function(e)
	{
		var userId = e.userId;
		if(userId == this.userId)
		{
			return;
		}

		/*if(this.layout == Layouts.Grid)
		{
			this.setLayout(Layouts.Centered);
		}*/
		if (userId == this.centralUser.id && this.layout != Layouts.Grid)
		{
			this.elements.root.classList.toggle("bx-messenger-videocall-hidden-panels");
		}

		if (this.layout == Layouts.Centered && userId != this.centralUser.id)
		{
			this.pinUser(userId);
		}

		this.eventEmitter.emit(EventName.onUserClick, {
			userId: userId,
			stream: userId == this.userId ? this.localUser.stream : this.users[userId].stream
		});
	};

	BX.Call.View.prototype._onUserRename = function(newName)
	{
		this.eventEmitter.emit(EventName.onUserRename, {newName: newName});
	};

	BX.Call.View.prototype._onUserRenameInputFocus = function(newName)
	{
		this.setHotKeyTemporaryBlock(true);
	};

	BX.Call.View.prototype._onUserRenameInputBlur = function(newName)
	{
		this.setHotKeyTemporaryBlock(false);
	};

	BX.Call.View.prototype._onUserPin = function(e)
	{
		if(this.layout == Layouts.Grid)
		{
			this.setLayout(Layouts.Centered)
		}
		this.pinUser(e.userId);
	};

	BX.Call.View.prototype._onUserUnPin = function(e)
	{
		this.unpinUser();
	};

	BX.Call.View.prototype._onRecordToggleClick = function(e)
	{
		if (this.recordState.state === BX.Call.View.RecordState.Stopped)
		{
			this._onRecordStartClick(e);
		}
		else
		{
			this._onRecordStopClick(e);
		}
	}

	BX.Call.View.prototype._onForceRecordToggleClick = function(e)
	{
		if (this.recordState.state === BX.Call.View.RecordState.Stopped)
		{
			this._onForceRecordStartClick(BX.Call.View.RecordType.Video);
		}
		else
		{
			this._onRecordStopClick(e);
		}
	}

	BX.Call.View.prototype._onForceRecordStartClick = function(recordType)
	{
		if (typeof recordType === 'undefined')
		{
			recordType = BX.Call.View.RecordType.None;
		}

		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: BX.Call.View.RecordState.Started,
			forceRecord: recordType, // none, video, audio
			node: null
		});
	}

	BX.Call.View.prototype._onRecordStartClick = function(e)
	{
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: BX.Call.View.RecordState.Started,
			node: e.currentTarget
		});
	}

	BX.Call.View.prototype._onRecordPauseClick = function(e)
	{
		var recordState;
		if (this.recordState.state === BX.Call.View.RecordState.Paused)
		{
			this.recordState.state = BX.Call.View.RecordState.Started;
			recordState = BX.Call.View.RecordState.Resumed;
		}
		else
		{
			this.recordState.state = BX.Call.View.RecordState.Paused;
			recordState = this.recordState.state;
		}

		this.buttons.recordStatus.update(this.recordState);

		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: recordState,
			node: e.currentTarget
		});
	};

	BX.Call.View.prototype._onRecordStopClick = function(e)
	{
		this.recordState.state = BX.Call.View.RecordState.Stopped;
		this.buttons.recordStatus.update(this.recordState);

		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: this.recordState.state,
			node: e.currentTarget
		});
	};

	BX.Call.View.prototype._onRecordMouseOver = function(e)
	{
		if (this.recordState.userId == this.userId || !this.userData[this.recordState.userId])
		{
			return;
		}

		var recordingUserName = BX.util.htmlspecialchars(this.userData[this.recordState.userId].name);
		this.hintManager.show(e.currentTarget, BX.message("IM_M_CALL_RECORD_HINT").replace("#USER_NAME#", recordingUserName));
	};

	BX.Call.View.prototype._onRecordMouseOut = function(e)
	{
		this.hintManager.hide();
	};

	BX.Call.View.prototype._onDocumentButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'document',
			node: e.target
		});
	};

	BX.Call.View.prototype._onGridButtonClick = function(e)
	{
		this.setLayout(this.layout == Layouts.Centered ? Layouts.Grid : Layouts.Centered);
	};

	BX.Call.View.prototype._onAddButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "inviteUser",
			node: e.currentTarget
		});
	};

	BX.Call.View.prototype._onShareButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "share",
			node: e.currentTarget
		});
	};

	BX.Call.View.prototype._onMicrophoneButtonClick = function(e)
	{
		if ("stopPropagation" in e)
		{
			e.stopPropagation();
		}
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "toggleMute",
			muted: !this.isMuted
		});
	};

	BX.Call.View.prototype._onMicrophoneArrowClick = function(e)
	{
		e.stopPropagation();
		this.showDeviceSelector(e.currentTarget);
	};

	BX.Call.View.prototype._onMicrophoneSideIconClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "microphoneSideIcon",
		});
	};

	BX.Call.View.prototype._onMicrophoneSelected = function(e)
	{
		if(e.data.deviceId === this.microphoneId)
		{
			return;
		}

		this.eventEmitter.emit(EventName.onReplaceMicrophone, {
			deviceId: e.data.deviceId
		});
	};

	BX.Call.View.prototype._onCameraButtonClick = function(e)
	{
		if ("stopPropagation" in e)
		{
			e.stopPropagation();
		}
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "toggleVideo",
			video: !this.isCameraOn
		});
	};

	BX.Call.View.prototype._onCameraArrowClick = function(e)
	{
		e.stopPropagation();
		this.showDeviceSelector(e.currentTarget);
	};

	BX.Call.View.prototype._onCameraSelected = function(e)
	{
		if(e.data.deviceId === this.cameraId)
		{
			return;
		}

		this.eventEmitter.emit(EventName.onReplaceCamera, {
			deviceId: e.data.deviceId
		});
	};

	BX.Call.View.prototype._onSpeakerButtonClick = function(e)
	{
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "toggleSpeaker",
			speakerMuted: this.speakerMuted
		});
	};

	BX.Call.View.prototype._onChangeHdVideo = function(e)
	{
		this.eventEmitter.emit(EventName.onChangeHdVideo, e.data);
	};

	BX.Call.View.prototype._onChangeMicAutoParams = function(e)
	{
		this.eventEmitter.emit(EventName.onChangeMicAutoParams, e.data);
	};

	BX.Call.View.prototype._onChangeFaceImprove = function(e)
	{
		this.eventEmitter.emit(EventName.onChangeFaceImprove, e.data);
	};

	BX.Call.View.prototype._onSpeakerSelected = function(e)
	{
		this.setSpeakerId(e.data.deviceId);

		this.eventEmitter.emit(EventName.onReplaceSpeaker, {
			deviceId: e.data.deviceId
		});
	};

	BX.Call.View.prototype._onScreenButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'toggleScreenSharing',
			node: e.target
		});
	};

	BX.Call.View.prototype._onChatButtonClick = function(e)
	{
		this.hintManager.hide();
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'showChat',
			node: e.target
		});
	};

	BX.Call.View.prototype._onUsersButtonClick = function(e)
	{
		this.hintManager.hide();
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'toggleUsers',
			node: e.target
		});
	};

	BX.Call.View.prototype._onMobileMenuButtonClick = function(e)
	{
		e.stopPropagation();
		this.showCallMenu();
	};

	BX.Call.View.prototype._onFloorRequestButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'floorRequest',
			node: e.target
		});
	};

	BX.Call.View.prototype._onMoreButtonClick = function(e)
	{
		e.stopPropagation();
		if (this.overflownButtonsPopup)
		{
			this.overflownButtonsPopup.close();
			this.buttons.more.setActive(false);
		}
		else
		{
			this.showOverflownButtonsPopup();
			this.buttons.more.setActive(true);
		}
	};

	BX.Call.View.prototype._onHistoryButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'showHistory',
			node: e.target
		});
	};

	BX.Call.View.prototype._onHangupButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'hangup',
			node: e.target
		});
	};

	BX.Call.View.prototype._onCloseButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'close',
			node: e.target
		});
	};

	BX.Call.View.prototype._onFullScreenButtonClick = function(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'fullscreen',
			node: e.target
		});
	};

	BX.Call.View.prototype._onParticipantsButtonListClick = function(event)
	{
		if (!this.isButtonBlocked('users'))
		{
			this._onUsersButtonClick(event);
			return;
		}

		if (!this.isFullScreen)
		{
			return;
		}

		this.setUserBlockFolded(!this.isUserBlockFolded);
	};

	BX.Call.View.prototype._onParticipantsListButtonClick = function(e)
	{
		e.stopPropagation();

		var viewEvent = new BX.Event.BaseEvent({
			data: {
				buttonName: 'participantsList',
				node: e.target
			},
			compatData: ['participantsList', e.target],
		});
		this.eventEmitter.emit(EventName.onButtonClick, viewEvent);

		if(viewEvent.isDefaultPrevented())
		{
			return;
		}

		UserSelector.create({
			parentElement: e.currentTarget,
			userList: Object.values(this.users),
			current: this.centralUser.id,
			onSelect: function(userId)
			{
				this.setCentralUser(userId)
			}.bind(this)
		}).show();
	};

	BX.Call.View.prototype._onParticipantsButtonMobileListClick = function(e)
	{
		this.showParticipantsMenu();
	};

	BX.Call.View.prototype._onMobileCallMenuFloorRequestClick = function()
	{
		this.callMenu.close();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'floorRequest',
		});
	};

	BX.Call.View.prototype._onMobileCallMenShowParticipantsClick = function()
	{
		this.callMenu.close();
		this.showParticipantsMenu();
	};

	BX.Call.View.prototype._onMobileCallMenuCopyInviteClick = function()
	{
		this.callMenu.close();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "share",
			node: null
		})
	};

	BX.Call.View.prototype.showRenameSlider = function()
	{
		if (!this.renameSlider)
		{
			this.renameSlider = new MobileSlider({
				parent: this.elements.root,
				content: this.renderRenameSlider(),
				onClose: function()
				{
					this.renameSlider.destroy()
				}.bind(this),
				onDestroy: function()
				{
					this.renameSlider = null;
				}.bind(this)
			});
		}

		this.renameSlider.show();
		setTimeout(function(){
			this.elements.renameSlider.input.focus();
			this.elements.renameSlider.input.select();
		}.bind(this), 400);
	};

	BX.Call.View.prototype.renderRenameSlider = function()
	{
		return BX.create("div", {
			props: {
				className: "bx-videocall-mobile-rename-slider-wrap"
			},
			children: [
				BX.create("div", {
					props: {
						className: "bx-videocall-mobile-rename-slider-title"
					},
					text: BX.message("IM_M_CALL_MOBILE_MENU_CHANGE_MY_NAME")
				}),
				this.elements.renameSlider.input = BX.create("input", {
					props: {
						className: "bx-videocall-mobile-rename-slider-input"
					},
					attrs: {
						type: "text",
						value: this.localUser.userModel.name
					}
				}),
				this.elements.renameSlider.button = BX.create("button", {
					props: {
						className: "bx-videocall-mobile-rename-slider-button ui-btn ui-btn-md ui-btn-primary"
					},
					text: BX.message("IM_M_CALL_MOBILE_RENAME_CONFIRM"),
					events: {
						click: this._onMobileUserRename.bind(this)
					}
				})
			]
		});
	};

	BX.Call.View.prototype._onMobileUserRename = function(event)
	{
		event.stopPropagation();

		var inputValue = this.elements.renameSlider.input.value;
		var newName = inputValue.trim();
		var needToUpdate = true;
		if (newName === this.localUser.userModel.name || newName === '')
		{
			needToUpdate = false;
		}

		if (needToUpdate)
		{
			this.toggleRenameSliderInputLoader();
			this._onUserRename(newName)
		}
		else
		{
			this.renameSlider.close();
		}
	};

	BX.Call.View.prototype._onMobileCallMenuCancelClick = function()
	{
		this.callMenu.close();
	};

	BX.Call.View.prototype._onLeftEarClick = function()
	{
		this.pinUser(this.getLeftUser(this.centralUser.id));
	};

	BX.Call.View.prototype._onRightEarClick = function()
	{
		this.pinUser(this.getRightUser(this.centralUser.id));
	};

	BX.Call.View.prototype._onLeftPageNavigatorClick = function(e)
	{
		e.stopPropagation();
		this.setCurrentPage(this.currentPage - 1)
	};

	BX.Call.View.prototype._onRightPageNavigatorClick = function(e)
	{
		e.stopPropagation();
		this.setCurrentPage(this.currentPage + 1)
	};

	BX.Call.View.prototype.setMaxWidth = function(maxWidth)
	{
		if (this.maxWidth !== maxWidth)
		{
			var MAX_WIDTH_SPEAKER_MODE = 650;
			if (maxWidth < MAX_WIDTH_SPEAKER_MODE
				&& (!this.maxWidth || this.maxWidth > MAX_WIDTH_SPEAKER_MODE)
				&& this.layout === Layouts.Centered
			)
			{
				this.setLayout(Layouts.Grid)
			}

			var animateUnsetProperty = this.maxWidth === null;
			this.maxWidth = maxWidth;
			if (this.size !== BX.Call.View.Size.Folded)
			{
				this._applyMaxWidth(animateUnsetProperty);
			}
		}
	};

	BX.Call.View.prototype.removeMaxWidth = function()
	{
		this.setMaxWidth(null);
	}

	BX.Call.View.prototype._applyMaxWidth = function(animateUnsetProperty)
	{
		var containerDimensions = this.container.getBoundingClientRect();
		if (this.maxWidth !== null)
		{
			if (!this.elements.root.style.maxWidth && animateUnsetProperty)
			{
				this.elements.root.style.maxWidth = containerDimensions.width + 'px';
			}
			setTimeout(function() {
				this.elements.root.style.maxWidth = Math.max(this.maxWidth, MIN_WIDTH)  + 'px';
			}.bind(this), 0)
		}
		else
		{
			this.elements.root.style.maxWidth = containerDimensions.width + 'px';
			this.elements.root.addEventListener('transitionend', function(){
				this.elements.root.style.removeProperty('max-width');
			}.bind(this), {
				once: true
			})
		}
	};

	BX.Call.View.prototype.releaseLocalMedia = function()
	{
		this.localUser.releaseStream();
		if(this.centralUser.id == this.userId)
		{
			this.centralUser.releaseStream();
		}
	};

	BX.Call.View.prototype.destroy = function()
	{
		if (this.overflownButtonsPopup)
		{
			this.overflownButtonsPopup.close();
		}
		if(this.elements.root)
		{
			BX.cleanNode(this.elements.root, true);
			this.elements.root = null;
		}
		this.visible = false;

		window.removeEventListener("webkitfullscreenchange", this._onFullScreenChangeHandler);
		window.removeEventListener("mozfullscreenchange", this._onFullScreenChangeHandler);
		window.removeEventListener("orientationchange", this._onOrientationChangeHandler);
		window.removeEventListener("keydown", this._onKeyDownHandler);
		window.removeEventListener("keyup", this._onKeyUpHandler);
		this.resizeObserver.disconnect();
		this.resizeObserver = null;
		if (this.intersectionObserver)
		{
			this.intersectionObserver.disconnect();
			this.intersectionObserver = null;
		}
		for(var userId in this.users)
		{
			if(this.users.hasOwnProperty(userId))
			{
				this.users[userId].destroy();
			}
		}
		this.userData = null;
		this.centralUser.destroy();
		this.hintManager.hide();
		this.hintManager = null;

		clearTimeout(this.switchPresenterTimeout);

		if (this.buttons.recordStatus)
		{
			this.buttons.recordStatus.stopViewUpdate();
		}
		this.recordState = this.getDefaultRecordState();
		this.buttons = null;

		this.eventEmitter.emit(EventName.onDestroy);
		this.eventEmitter.unsubscribeAll();
	};

	var CallUser = function(config)
	{
		this.parentContainer = config.parentContainer;
		this.userModel = config.userModel;
		this.allowBackgroundItem = BX.prop.getBoolean(config, "allowBackgroundItem", true);
		this.allowMaskItem = BX.prop.getBoolean(config, "allowMaskItem", true);
		this.userModel.subscribe("changed", this._onUserFieldChanged.bind(this));
		this.incomingVideoConstraints = {
			width: 0,
			height: 0
		};
		this._allowPinButton = BX.prop.getBoolean(config, "allowPinButton", true);
		this._visible = true;

		this.screenSharingUser = BX.prop.getBoolean(config, "screenSharingUser", false);

		Object.defineProperty(this, "allowPinButton", {
			get: function()
			{
				return this._allowPinButton;
			},
			set: function(allowPinButton)
			{
				if (this._allowPinButton == allowPinButton)
				{
					return;
				}
				this._allowPinButton = allowPinButton;
				this.update()
			}
		});
		Object.defineProperty(this, "id", {
			get: function()
			{
				return this.userModel.id
			}
		});

		this._audioTrack = config.audioTrack;
		this._audioStream = this._audioTrack ? new MediaStream([this._audioTrack]) : null;
		Object.defineProperty(this, 'audioTrack', {
			get: function()
			{
				return this._audioTrack;
			},
			set: function(audioTrack)
			{
				if (this._audioTrack === audioTrack)
				{
					return;
				}
				this._audioTrack = audioTrack;
				this._audioStream = this._audioTrack ? new MediaStream([this._audioTrack]) : null;
				this.playAudio()
			}
		});
		Object.defineProperty(this, 'audioStream', {
			get: function()
			{
				return this._audioStream;
			}
		});

		this._videoTrack = config.videoTrack;
		this._stream = this._videoTrack ? new MediaStream([this._videoTrack]) : null;
		Object.defineProperty(this, "videoTrack", {
			get: function()
			{
				return this._videoTrack;
			},
			set: function(videoTrack)
			{
				if (this._videoTrack === videoTrack)
				{
					return;
				}
				this._videoTrack = videoTrack;
				this._stream = this._videoTrack ? new MediaStream([this._videoTrack]) : null;
				this.update()
			}
		});
		Object.defineProperty(this, "stream", {
			get: function()
			{
				return this._stream;
			}
		});
		this._videoRenderer = null;
		Object.defineProperty(this, "videoRenderer", {
			get: function()
			{
				return this._videoRenderer;
			},
			set: function(videoRenderer)
			{
				this._videoRenderer = videoRenderer;
				this.update();
				this.updateRendererState();
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
		Object.defineProperty(this, "visible", {
			get: function()
			{
				return this._visible;
			},
			set: function(visible)
			{
				if (this._visible !== visible)
				{
					console.warn("user " + this.id + " is " + (visible ? "visible" : "invisible"));
					this._visible = visible;
					this.update();
					this.updateRendererState();
				}
			}
		});

		this.hidden = false;
		this.videoBlurState = false;
		this.isChangingName = false;

		this.elements = {
			root: null,
			container: null,
			videoContainer: null,
			video: null,
			audio: config.audioElement || null,
			videoBorder: null,
			avatarContainer: null,
			avatar: null,
			nameContainer: null,
			name: null,
			changeNameIcon: null,
			changeNameContainer: null,
			changeNameCancel: null,
			changeNameInput: null,
			changeNameConfirm: null,
			introduceYourselfContainer: null,
			floorRequest: null,
			state: null,
			removeButton: null,
			micState: null,
			cameraState: null,
			panel: null,
			buttonMenu: null,
			buttonBackground: null,
			buttonPin: null,
			buttonUnPin: null,
		};
		this.menu = null;

		this.callBacks = {
			onClick: BX.type.isFunction(config.onClick) ?  config.onClick : BX.DoNothing,
			onUserRename: BX.type.isFunction(config.onUserRename) ?  config.onUserRename : BX.DoNothing,
			onUserRenameInputFocus: BX.type.isFunction(config.onUserRenameInputFocus) ?  config.onUserRenameInputFocus : BX.DoNothing,
			onUserRenameInputBlur: BX.type.isFunction(config.onUserRenameInputBlur) ?  config.onUserRenameInputBlur : BX.DoNothing,
			onPin: BX.type.isFunction(config.onPin) ?  config.onPin : BX.DoNothing,
			onUnPin: BX.type.isFunction(config.onUnPin) ?  config.onUnPin : BX.DoNothing,
		};
		this.checkAspectInterval = setInterval(this.checkVideoAspect.bind(this), 500);
	};

	CallUser.prototype.render = function()
	{
		if(this.elements.root)
		{
			return this.elements.root;
		}
		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-user"},
			dataset: {userId: this.userModel.id, order: this.userModel.order},
			children: [
				this.elements.videoBorder = BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-border",
					},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-videocall-user-talking-icon"},
						}),
					]
				}),
				this.elements.container = BX.create("div", {
					props: {className: "bx-messenger-videocall-user-inner"},
					children: [
						this.elements.avatarBackground = BX.create("div", {
							props: {className: "bx-messenger-videocall-user-avatar-background"},
						}),
						this.elements.avatarContainer = BX.create("div", {
							props: {className: "bx-messenger-videocall-user-avatar-border"},
							children: [
								this.elements.avatar = BX.create("div", {
									props: {className: "bx-messenger-videocall-user-avatar"},
								}),
								BX.create("div", {
									props: {className: "bx-messenger-videocall-user-avatar-overlay-border"}
								})
							]
						}),
						this.elements.panel = BX.create("div", {
							props: {className: "bx-messenger-videocall-user-panel"}
						}),
						this.elements.state = BX.create("div", {
							props: {className: "bx-messenger-videocall-user-status-text"},
							text: this.getStateMessage(this.userModel.state)
						}),
						BX.create("div", {
							props: {className: "bx-messenger-videocall-user-bottom"},
							children: [
								this.elements.nameContainer = BX.create("div", {
									props: {className: "bx-messenger-videocall-user-name-container" + ((this.userModel.allowRename && !this.userModel.wasRenamed) ? " hidden": "")},
									children: [
										this.elements.micState = BX.create("div", {
											props: {className: "bx-messenger-videocall-user-device-state mic" + (this.userModel.microphoneState ? " hidden" : "")},
										}),
										this.elements.cameraState = BX.create("div", {
											props: {className: "bx-messenger-videocall-user-device-state camera" + (this.userModel.cameraState ? " hidden" : "")},
										}),
										this.elements.name = BX.create("span", {
											props: {className: "bx-messenger-videocall-user-name"},
											text: (this.screenSharingUser ? BX.message('IM_CALL_USERS_SCREEN').replace("#NAME#", this.userModel.name)	: this.userModel.name)
										}),
										this.elements.changeNameIcon = BX.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-icon hidden"},
										})
									],
									events: {
										click: this.toggleNameInput.bind(this)
									}
								}),
								this.elements.changeNameContainer = BX.create("div", {
									props: {className: "bx-messenger-videocall-user-change-name-container hidden"},
									children: [
										this.elements.changeNameCancel = BX.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-cancel"},
											events: {
												click: this.toggleNameInput.bind(this)
											}
										}),
										this.elements.changeNameInput = BX.create("input", {
											props: {
												className: "bx-messenger-videocall-user-change-name-input"
											},
											attrs: {
												type: 'text',
												value: this.userModel.name
											},
											events: {
												keydown: this.onNameInputKeyDown.bind(this),
												focus: this.callBacks.onUserRenameInputFocus,
												blur: this.callBacks.onUserRenameInputBlur
											}
										}),
										this.elements.changeNameConfirm = BX.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-confirm"},
											events: {
												click: this.changeName.bind(this)
											}
										}),
										this.elements.changeNameLoader = BX.create("div", {
											props: {className: "bx-messenger-videocall-user-change-name-loader hidden"},
											children: [
												BX.create("div", {
													props: {className: "bx-messenger-videocall-user-change-name-loader-icon"}
												})
											]
										})
									]
								}),
								this.elements.introduceYourselfContainer = BX.create("div", {
									props: {className: "bx-messenger-videocall-user-introduce-yourself-container" + (!this.userModel.allowRename || this.userModel.wasRenamed ? " hidden" : "")},
									children: [
										BX.create("div", {
											props: {className: "bx-messenger-videocall-user-introduce-yourself-text"},
											text: BX.message('IM_CALL_GUEST_INTRODUCE_YOURSELF'),
										})
									],
									events: {
										click: this.toggleNameInput.bind(this)
									}
								})
							]
						}),
						this.elements.floorRequest = BX.create("div", {
							props: {className: "bx-messenger-videocall-user-floor-request bx-messenger-videocall-floor-request-icon"}
						})
					]
				}),
			],
			style: {
				order: this.userModel.order
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

		if (this.userModel.talking)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-talking");
		}

		if (this.userModel.localUser)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-self");
		}

		if (this.userModel.avatar !== '')
		{
			this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
		}
		else
		{
			this.elements.root.style.removeProperty("--avatar");
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
					},
					attrs: {
						playsinline: true,
						muted: true
					}
				}),
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
		if (this.userModel.screenState)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-contain");
		}

		if (this.userModel.cameraState && this.userModel.microphoneState)
		{
			this.elements.nameContainer.classList.add("extra-padding");
		}

		//this.elements.nameContainer.appendChild(this.elements.micState);

		// todo: show button only if user have the permission to remove user
		/*this.elements.removeButton = BX.create("div", {
			props: {className: "bx-messenger-videocall-user-close"}
		});

		this.elements.container.appendChild(this.elements.removeButton);*/

		this.elements.buttonMask = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button mask"
			},
			children: [
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon mask"
					}
				}),
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					},
					text: BX.message("IM_CALL_CHANGE_MASK")
				})
			],
			events: {
				click: function()
				{
					BX.Call.Hardware.BackgroundDialog.open({'tab': 'mask'});
				}
			}
		});
		this.elements.buttonBackground = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon background"
					}
				}),
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					},
					text: BX.message("IM_CALL_CHANGE_BACKGROUND")
				})
			],
			events: {
				click: function()
				{
					BX.Call.Hardware.BackgroundDialog.open();
				}
			}
		});
		this.elements.buttonMenu = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon menu"
					}
				}),
			],
			events: {
				click: this.showMenu.bind(this)
			}
		});
		this.elements.buttonPin = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon pin"
					}
				}),
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					},
					text: BX.message("IM_CALL_PIN")
				})
			],
			events: {
				click: function(e)
				{
					e.stopPropagation();
					this.callBacks.onPin({userId: this.userModel.id});
				}.bind(this)
			}
		});
		this.elements.buttonUnPin = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-panel-button"
			},
			children: [
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-icon unpin"
					}
				}),
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-panel-button-text"
					},
					text: BX.message("IM_CALL_UNPIN")
				})
			],
			events: {
				click: function(e)
				{
					e.stopPropagation();
					this.callBacks.onUnPin();
				}.bind(this)
			}
		});

		this.updatePanelDeferred();
		return this.elements.root;
	};

	CallUser.prototype.setIncomingVideoConstraints = function(width, height)
	{
		this.incomingVideoConstraints.width = typeof(width) === "undefined" ? this.incomingVideoConstraints.width : width;
		this.incomingVideoConstraints.height = typeof(height) === "undefined" ? this.incomingVideoConstraints.height : height;

		if (!this.videoRenderer)
		{
			return;
		}

		// vox low quality temporary workaround
		// (disabled to test quality)
		// if (this.incomingVideoConstraints.width >= 320 && this.incomingVideoConstraints.width <= 640)
		// {
		// 	this.incomingVideoConstraints.width = 640;
		// }
		// if (this.incomingVideoConstraints.height >= 180 && this.incomingVideoConstraints.height <= 360)
		// {
		// 	this.incomingVideoConstraints.height = 360;
		// }

		this.videoRenderer.requestVideoSize(this.incomingVideoConstraints.width, this.incomingVideoConstraints.height);
	};

	CallUser.prototype.updateRendererState = function()
	{
		/*if (this.videoRenderer)
		{
			if (this.visible)
			{
				this.videoRenderer.enable();
			}
			else
			{
				this.videoRenderer.disable();
			}
		}*/

		/*if (this.elements.video && this.elements.video.srcObject)
		{
			if (this.visible)
			{
				this.elements.video.play();
			}
			else
			{
				this.elements.video.pause();
			}
		}*/
	};

	CallUser.prototype._onUserFieldChanged = function(event)
	{
		var eventData = event.data;

		switch (eventData.fieldName)
		{
			case "id":
				return this.updateId();
			case "name":
				return this.updateName();
			case "avatar":
				return this.updateAvatar();
			case "state":
				return this.updateState();
			case "talking":
				return this.updateTalking();
			case "microphoneState":
				return this.updateMicrophoneState();
			case "cameraState":
				return this.updateCameraState();
			case "videoPaused":
				return this.updateVideoPaused();
			case "floorRequestState":
				return this.updateFloorRequestState();
			case "screenState":
				return this.updateScreenState();
			case "pinned":
				return this.updatePanel();
			case "allowRename":
				return this.updateRenameAllowed();
			case "wasRenamed":
				return this.updateWasRenamed();
			case "renameRequested":
				return this.updateRenameRequested();
			case "order":
				return this.updateOrder();

		}
	};

	CallUser.prototype.toggleRenameIcon = function()
	{
		if (!this.userModel.allowRename)
		{
			return;
		}

		this.elements.changeNameIcon.classList.toggle('hidden');
	};

	CallUser.prototype.toggleNameInput = function(event)
	{
		if (!this.userModel.allowRename || !this.elements.root)
		{
			return;
		}

		event.stopPropagation();

		if (this.isChangingName)
		{
			this.isChangingName = false;
			if (!this.userModel.wasRenamed)
			{
				this.elements.introduceYourselfContainer.classList.remove('hidden');
				this.elements.changeNameContainer.classList.add('hidden');
			}
			else
			{
				this.elements.changeNameContainer.classList.add('hidden');
				this.elements.nameContainer.classList.remove('hidden');
			}
		}
		else
		{
			if (!this.userModel.wasRenamed)
			{
				this.elements.introduceYourselfContainer.classList.add('hidden');
			}
			this.isChangingName = true;
			this.elements.nameContainer.classList.add('hidden');
			this.elements.changeNameContainer.classList.remove('hidden');
			this.elements.changeNameInput.value = this.userModel.name;
			this.elements.changeNameInput.focus();
			this.elements.changeNameInput.select();
		}
	};

	CallUser.prototype.onNameInputKeyDown = function(event)
	{
		if (!this.userModel.allowRename)
		{
			return;
		}

		//enter
		if (event.keyCode === 13)
		{
			this.changeName(event);
		}
		//escape
		else if (event.keyCode === 27)
		{
			this.toggleNameInput(event);
		}
	};

	CallUser.prototype.onNameInputFocus = function(event)
	{

	};

	CallUser.prototype.onNameInputBlur = function(event)
	{

	};

	CallUser.prototype.changeName = function(event)
	{
		event.stopPropagation();

		var inputValue = this.elements.changeNameInput.value;
		var newName = inputValue.trim();
		var needToUpdate = true;
		if (newName === this.userModel.name || newName === '')
		{
			needToUpdate = false;
		}

		if (needToUpdate)
		{
			this.elements.changeNameConfirm.classList.toggle('hidden');
			this.elements.changeNameLoader.classList.toggle('hidden');
			this.callBacks.onUserRename(newName);
		}
		else
		{
			this.toggleNameInput(event);
		}
	};

	CallUser.prototype.showMenu = function()
	{
		var menuItems = [];

		if (this.userModel.localUser && this.allowBackgroundItem)
		{
			menuItems.push({
				text: (this.allowMaskItem? BX.message("IM_CALL_CHANGE_BG_MASK"): BX.message("IM_CALL_CHANGE_BACKGROUND")),
				onclick: function()
				{
					this.menu.close();
					BX.Call.Hardware.BackgroundDialog.open();
				}.bind(this)
			});
		}
		if (menuItems.length === 0)
		{
			return;
		}

		let rect =  BX.Dom.getRelativePosition(this.elements.buttonMenu, this.parentContainer)
		this.menu = BX.PopupMenu.create(
			'call-view-user-menu-' + this.userModel.id,
			{left: rect.left, top: rect.top, bottom: rect.bottom}, // this.elements.buttonMenu,
			menuItems,
			{
				targetContainer: this.parentContainer,
				autoHide: true,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				bindOptions: {
					position: 'bottom'
				},
				angle: true,
				overlay: {
					backgroundColor: 'white',
					opacity: 0
				},
				events: {
					onPopupClose : function()
					{
						this.menu.popupWindow.destroy();
						BX.PopupMenu.destroy('call-view-select-device' + this.userModel.id);
					}.bind(this),
					onPopupDestroy: function ()
					{
						this.menu = null;
					}.bind(this)
				}
			}
		);
		this.menu.popupWindow.show();
	};

	CallUser.prototype.updateAvatar = function()
	{
		if(this.elements.root)
		{
			if (this.userModel.avatar !== '')
			{
				this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
			}
			else
			{
				this.elements.root.style.removeProperty("--avatar");
			}
		}
	};

	CallUser.prototype.updateId = function()
	{
		if (this.elements.root)
		{
			this.elements.root.dataset.userId = this.userModel.id;
		}
	};

	CallUser.prototype.updateName = function()
	{
		if (this.isChangingName)
		{
			this.isChangingName = false;
			this.elements.changeNameConfirm.classList.toggle('hidden');
			this.elements.changeNameLoader.classList.toggle('hidden');
			this.elements.changeNameContainer.classList.add('hidden');
			this.elements.nameContainer.classList.remove('hidden');
		}

		if(this.elements.name)
		{
			this.elements.name.innerText = this.screenSharingUser
				? BX.message('IM_CALL_USERS_SCREEN').replace("#NAME#", this.userModel.name)
				: this.userModel.name
			;
		}
	};

	CallUser.prototype.updateRenameAllowed = function()
	{
		if (this.userModel.allowRename && this.elements.nameContainer && this.elements.introduceYourselfContainer)
		{
			this.elements.nameContainer.classList.add('hidden');
			this.elements.introduceYourselfContainer.classList.remove('hidden');
		}
	};

	CallUser.prototype.updateWasRenamed = function()
	{
		if (!this.elements.root)
		{
			return;
		}

		if (this.userModel.allowRename)
		{
			this.elements.introduceYourselfContainer.classList.add('hidden');
			this.elements.changeNameIcon.classList.remove('hidden');
			if (this.elements.changeNameContainer.classList.contains('hidden'))
			{
				this.elements.nameContainer.classList.remove('hidden');
			}
		}
	};

	CallUser.prototype.updateRenameRequested = function()
	{
		if (this.userModel.allowRename)
		{
			this.elements.introduceYourselfContainer.classList.add('hidden');
		}
	};

	CallUser.prototype.updateOrder = function()
	{
		if (this.elements.root)
		{
			this.elements.root.dataset.order = this.userModel.order;
			this.elements.root.style.order = this.userModel.order;
		}
	};

	CallUser.prototype.updatePanelDeferred = function()
	{
		setTimeout(this.updatePanel.bind(this), 0);
	};

	CallUser.prototype.updatePanel = function()
	{
		if (!this.isMounted())
		{
			return;
		}
		var width = this.elements.root.offsetWidth;

		BX.clean(this.elements.panel);
		if (this.userModel.localUser && this.allowBackgroundItem)
		{
			if (width > 300)
			{
				if (this.allowMaskItem)
				{
					this.elements.panel.appendChild(this.elements.buttonMask);
				}
				this.elements.panel.appendChild(this.elements.buttonBackground);
			}
			else
			{
				this.elements.panel.appendChild(this.elements.buttonMenu);
			}
		}

		if (!this.userModel.localUser && this.allowPinButton)
		{
			if (this.userModel.pinned)
			{
				this.elements.panel.appendChild(this.elements.buttonUnPin);
			}
			else
			{
				this.elements.panel.appendChild(this.elements.buttonPin);
			}

			if (width > 250)
			{
				this.elements.buttonPin.classList.remove("no-text");
				this.elements.buttonUnPin.classList.remove("no-text");
			}
			else
			{
				this.elements.buttonPin.classList.add("no-text");
				this.elements.buttonUnPin.classList.add("no-text");
			}
		}
	};

	CallUser.prototype.update = function()
	{
		if(!this.elements.root)
		{
			return;
		}
		if(this.hasVideo()/* && this.visible*/)
		{
			if (this.visible)
			{
				if (this.videoRenderer)
				{
					this.videoRenderer.render(this.elements.video);
				}
				else if (this.elements.video.srcObject != this.stream)
				{
					this.elements.video.srcObject = this.stream;
				}
			}

			BX.remove(this.elements.avatarContainer);
			this.elements.video.classList.toggle("bx-messenger-videocall-video-flipped", this.flipVideo);
			this.elements.video.classList.toggle("bx-messenger-videocall-video-contain", this.userModel.screenState);
		}
		else
		{
			this.elements.video.srcObject = null;
			this.elements.container.insertBefore(this.elements.avatarContainer, this.elements.panel);
		}
		this.updatePanelDeferred();
	};

	CallUser.prototype.playAudio = function()
	{
		if (!this.audioStream)
		{
			this.elements.audio.srcObject = null;
			return;
		}

		if(this.speakerId && this.elements.audio.setSinkId)
		{
			this.elements.audio.setSinkId(this.speakerId).then(function()
			{
				this.elements.audio.srcObject = this.audioStream;
				this.elements.audio.play().catch(logPlaybackError);
			}.bind(this)).catch(console.error);
		}
		else
		{
			this.elements.audio.srcObject = this.audioStream;
			this.elements.audio.play().catch(logPlaybackError);
		}
	};

	CallUser.prototype.playVideo = function()
	{
		if(this.elements.video)
		{
			this.elements.video.play().catch(logPlaybackError);
		}
	};

	CallUser.prototype.blurVideo = function(blurState)
	{
		blurState = !!blurState;

		if (this.videoBlurState == blurState)
		{
			return;
		}
		this.videoBlurState = blurState;
		if (this.elements.video)
		{
			this.elements.video.classList.toggle('bx-messenger-videocall-video-blurred');
		}
	};

	CallUser.prototype.getStateMessage = function(userState, videoPaused)
	{
		switch (userState)
		{
			case BX.Call.UserState.Idle:
				return "";
			case BX.Call.UserState.Calling:
				return BX.message("IM_M_CALL_STATUS_WAIT_ANSWER");
			case BX.Call.UserState.Declined:
				return BX.message("IM_M_CALL_STATUS_DECLINED");
			case BX.Call.UserState.Ready:
			case BX.Call.UserState.Connecting:
				return BX.message("IM_M_CALL_STATUS_WAIT_CONNECT");
			case BX.Call.UserState.Connected:
				return videoPaused ? BX.message("IM_M_CALL_STATUS_VIDEO_PAUSED") : "";
			case BX.Call.UserState.Failed:
				return BX.message("IM_M_CALL_STATUS_CONNECTION_ERROR");
			case BX.Call.UserState.Unavailable:
				return BX.message("IM_M_CALL_STATUS_UNAVAILABLE");
			default:
				return "";
		}
	};

	CallUser.prototype.mount = function(parent, force)
	{
		force = force === true;
		if(!this.elements.root)
		{
			this.render();
		}

		if(this.isMounted() && this.elements.root.parentElement == parent && !force)
		{
			this.updatePanelDeferred();
			return false;
		}

		parent.appendChild(this.elements.root);
		this.update();
	};

	CallUser.prototype.dismount = function()
	{
		// this.visible = false;
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

	CallUser.prototype.updateState = function()
	{
		if(!this.elements.root)
		{
			return;
		}

		if (this.userModel.state == BX.Call.UserState.Calling || this.userModel.state == BX.Call.UserState.Connecting)
		{
			this.elements.avatar.classList.add("bx-messenger-videocall-user-avatar-pulse");
		}
		else
		{
			this.elements.avatar.classList.remove("bx-messenger-videocall-user-avatar-pulse");
		}

		this.elements.state.innerText = this.getStateMessage(this.userModel.state, this.userModel.videoPaused);
		this.update();
	};

	CallUser.prototype.updateTalking = function()
	{
		if(!this.elements.root)
		{
			return;
		}
		if(this.userModel.talking)
		{
			this.elements.root.classList.add("bx-messenger-videocall-user-talking");
		}
		else
		{
			this.elements.root.classList.remove("bx-messenger-videocall-user-talking");
		}
	};

	CallUser.prototype.updateMicrophoneState = function()
	{
		if(!this.elements.root)
		{
			return;
		}
		if(this.userModel.microphoneState)
		{
			this.elements.micState.classList.add("hidden");
		}
		else
		{
			this.elements.micState.classList.remove("hidden");
		}

		if (this.userModel.cameraState && this.userModel.microphoneState)
		{
			this.elements.nameContainer.classList.add("extra-padding");
		}
		else
		{
			this.elements.nameContainer.classList.remove("extra-padding");
		}
	};

	CallUser.prototype.updateCameraState = function()
	{
		if(!this.elements.root)
		{
			return;
		}
		if(this.userModel.cameraState)
		{
			this.elements.cameraState.classList.add("hidden");
		}
		else
		{
			this.elements.cameraState.classList.remove("hidden");
		}

		if (this.userModel.cameraState && this.userModel.microphoneState)
		{
			this.elements.nameContainer.classList.add("extra-padding");
		}
		else
		{
			this.elements.nameContainer.classList.remove("extra-padding");
		}
	};

	CallUser.prototype.updateVideoPaused = function()
	{
		if (!this.elements.root)
		{
			return;

		}
		if (this.stream && this.hasVideo())
		{
			this.blurVideo(this.userModel.videoPaused);
		}
		this.updateState();
	};

	CallUser.prototype.updateFloorRequestState = function()
	{
		if(!this.elements.floorRequest)
		{
			return;
		}
		if(this.userModel.floorRequestState)
		{
			this.elements.floorRequest.classList.add("active");
		}
		else
		{
			this.elements.floorRequest.classList.remove("active");
		}
	};

	CallUser.prototype.updateScreenState = function()
	{
		if(!this.elements.video)
		{
			return;
		}
		if(this.userModel.screenState)
		{
			this.elements.video.classList.add("bx-messenger-videocall-video-contain");
		}
		else
		{
			this.elements.video.classList.remove("bx-messenger-videocall-video-contain");
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
		return this.userModel.state == BX.Call.UserState.Connected && (
			!!this._videoTrack || !!this._videoRenderer
		);
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
		this.videoTrack = null;
	};


	CallUser.prototype.destroy = function()
	{
		this.releaseStream();
		clearInterval(this.checkAspectInterval);
	};

	var CallUserMobile = function(config)
	{
		this.userModel = config.userModel;

		this.elements = {
			root: null,
			avatar: null,
			avatarOutline: null,
			userName: null,
			userStatus: null,
			menuArrow: null,
			floorRequest: null,
			mic: null,
			cam: null,
		};

		this._onUserFieldChangeHandler = this._onUserFieldChange.bind(this);
		this.userModel.subscribe("changed", this._onUserFieldChangeHandler);

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing)
		}
	};

	CallUserMobile.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-mobile"
			},
			children: [
				this.elements.avatar = BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-mobile-avatar" + (this.userModel.talking ? " talking" : "")
					},
					children: [
						this.elements.floorRequest = BX.create("div", {
							props: {
								className: "bx-messenger-videocall-user-mobile-floor-request bx-messenger-videocall-floor-request-icon"
							}
						})
					]
				}),
				BX.create("div", {
					props: {
						className: "bx-messenger-videocall-user-mobile-body"
					},
					children: [
						BX.create("div", {
							props: {
								className: "bx-messenger-videocall-user-mobile-text"
							},
							children: [
								this.elements.mic = BX.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-icon" + (this.userModel.microphoneState ? "" : " bx-call-view-icon-red-microphone-off")
									}
								}),
								this.elements.cam = BX.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-icon" + (this.userModel.cameraState ? "" : " bx-call-view-icon-red-camera-off")
									}
								}),
								this.elements.userName = BX.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-username"
									},
									text: this.userModel.name
								}),
								BX.create("div", {
									props: {
										className: "bx-messenger-videocall-user-mobile-menu-arrow"
									}
								})
							]
						}),
						this.elements.userStatus = BX.create("div", {
							props: {
								className: "bx-messenger-videocall-user-mobile-user-status"
							},
							text: this.userModel.pinned ? BX.message("IM_M_CALL_PINNED_USER") : BX.message("IM_M_CALL_CURRENT_PRESENTER")
						})
					]
				}),

			],
			events: {
				click: this.callbacks.onClick
			}
		});

		return this.elements.root;
	};

	CallUserMobile.prototype.update = function()
	{
		if (!this.elements.root)
		{
			return;
		}

		this.elements.userName.innerText = this.userModel.name;

		if (this.userModel.avatar !== '')
		{
			this.elements.root.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
		}
		else
		{
			this.elements.root.style.removeProperty("--avatar");
		}
		this.userModel.talking ? this.elements.avatar.classList.add("talking") : this.elements.avatar.classList.remove("talking");
		this.userModel.floorRequestState ? this.elements.floorRequest.classList.add("active") : this.elements.floorRequest.classList.remove("active");
		this.userModel.microphoneState ? this.elements.mic.classList.remove("bx-call-view-icon-red-microphone-off") : this.elements.mic.classList.add("bx-call-view-icon-red-microphone-off");
		this.userModel.cameraState ? this.elements.cam.classList.remove("bx-call-view-icon-red-camera-off") : this.elements.cam.classList.add("bx-call-view-icon-red-camera-off");

		this.elements.userStatus.innerText = this.userModel.pinned ? BX.message("IM_M_CALL_PINNED_USER") : BX.message("IM_M_CALL_CURRENT_PRESENTER");
	};

	CallUserMobile.prototype.mount = function(parentElement)
	{
		parentElement.appendChild(this.render());
	};

	CallUserMobile.prototype.dismount = function()
	{
		if (!this.elements.root)
		{
			return;
		}
		BX.remove(this.elements.root);
	};

	CallUserMobile.prototype.setUserModel = function(userModel)
	{
		this.userModel.unsubscribe("changed", this._onUserFieldChangeHandler);
		this.userModel = userModel;
		this.userModel.subscribe("changed", this._onUserFieldChangeHandler);
		this.update();
	};

	CallUserMobile.prototype._onUserFieldChange = function(event)
	{
		this.update();
	};

	var UserSelectorMobile = function(config)
	{
		this.userRegistry = config.userRegistry;
		this.userRegistry.subscribe("userAdded", this._onUserAdded.bind(this));
		this.userRegistry.subscribe("userChanged", this._onUserChanged.bind(this));

		this.elements = {
			root: null,
			users: {}
		}
	};

	UserSelectorMobile.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-user-selector-mobile",
			},
		});

		this.updateUsers();

		return this.elements.root;
	};

	UserSelectorMobile.prototype.renderUser = function(userFields)
	{
		return createSVG("svg", {
			attrNS: {
				width: 14.5, height: 11.6
			},
			style: {
				order: userFields.order
			},
			children: [
				createSVG("circle", {
					attrNS: {
						class: "bx-messenger-videocall-user-selector-mobile-border" + (userFields.talking ? " talking" : ""),
						cx: 7.25, cy: 5.8, r: 4.6,
					},
				}),
				createSVG("circle", {
					attrNS: {
						class: "bx-messenger-videocall-user-selector-mobile-dot" + (userFields.centralUser ? " pinned" : ""),
						cx: 7.25, cy: 5.8, r: 3.3
					},
				})
			]
		});
	};

	UserSelectorMobile.prototype.updateUsers = function()
	{
		this.userRegistry.users.forEach(function(userFields)
		{
			if (userFields.localUser || userFields.state != BX.Call.UserState.Connected)
			{
				if (this.elements.users[userFields.id])
				{
					BX.remove(this.elements.users[userFields.id]);
					this.elements.users[userFields.id] = null;
				}
			}
			else
			{
				var newNode = this.renderUser(userFields);
				if (this.elements.users[userFields.id])
				{
					BX.replace(this.elements.users[userFields.id], newNode)
				}
				else
				{
					this.elements.root.appendChild(newNode)
				}
				this.elements.users[userFields.id] = newNode;
			}
		}, this)
	};

	UserSelectorMobile.prototype._onUserAdded = function(event)
	{
		this.updateUsers();
	};

	UserSelectorMobile.prototype._onUserChanged = function(event)
	{
		this.updateUsers();
	};

	UserSelectorMobile.prototype.mount = function(parentElement)
	{
		parentElement.appendChild(this.render());
	};

	UserSelectorMobile.prototype.dismount = function()
	{
		if (!this.elements.root)
		{
			return;
		}
		BX.remove(this.elements.root);
	};

	var MobileSlider = function(config)
	{
		this.parent = config.parent || null;
		this.content = config.content || null;

		this.elements = {
			background: null,
			root: null,
			handle: null,
			body: null,
		};

		this.callbacks = {
			onClose: BX.prop.getFunction(config, "onClose", BX.DoNothing),
			onDestroy: BX.prop.getFunction(config, "onDestroy", BX.DoNothing),
		};

		this.touchStartY = 0;
		this.processedTouchId = 0;
	};

	MobileSlider.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.background = BX.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-background"
			},
			events: {
				click: this._onBackgroundClick.bind(this)
			}
		});
		this.elements.root = BX.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-container"
			},
			children: [
				this.elements.handle = BX.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-handle"
					},
				}),
				this.elements.body = BX.create("div", {
					props: {
						className: "bx-videocall-mobile-menu"
					},
					children: [
						this.content
					]
				})
			],
			events: {
				touchstart: this._onTouchStart.bind(this),
				touchmove: this._onTouchMove.bind(this),
				touchend: this._onTouchEnd.bind(this),
			}
		});

		return this.elements.root;
	};


	MobileSlider.prototype.show = function()
	{
		if (this.parent)
		{
			this.render();
			this.parent.appendChild(this.elements.root);
			this.parent.appendChild(this.elements.background);
		}
	};

	MobileSlider.prototype.close = function()
	{
		BX.remove(this.elements.root);
		BX.remove(this.elements.background);
		this.callbacks.onClose();
	};

	MobileSlider.prototype.closeWithAnimation = function()
	{
		if (!this.elements.root)
		{
			return;
		}

		this.elements.root.classList.add("closing");
		this.elements.background.classList.add("closing");
		this.elements.root.addEventListener("animationend", function()
		{
			this.close();
		}.bind(this));
	};

	MobileSlider.prototype._onTouchStart = function(e)
	{
		this.touchStartY = e.pageY;
		if (this.processedTouchId || e.touches.length > 1)
		{
			return;
		}
		if (e.target == this.elements.header || e.target == this.elements.root || this.elements.body.scrollTop === 0)
		{
			this.processedTouchId = e.touches[0].identifier;
		}
	};

	MobileSlider.prototype._onTouchMove = function(e)
	{
		if (e.touches.length > 1)
		{
			return;
		}
		if (e.touches[0].identifier != this.processedTouchId)
		{
			return;
		}
		var delta = this.touchStartY - e.pageY;
		if (delta > 0)
		{
			delta = 0;
		}
		this.elements.root.style.bottom = delta + "px";
		if (delta)
		{
			e.preventDefault();
		}
	};

	MobileSlider.prototype._onTouchEnd = function(e)
	{
		var allowProcessing = false;
		for (var i = 0; i < e.changedTouches.length; i++)
		{
			if (e.changedTouches[i].identifier == this.processedTouchId)
			{
				allowProcessing = true;
				break;
			}
		}
		if (!allowProcessing)
		{
			return;
		}

		var delta = e.pageY - this.touchStartY;
		if (delta > 100)
		{
			this.closeWithAnimation();
			e.preventDefault();
		}
		else
		{
			this.elements.root.style.removeProperty("bottom");
		}

		this.processedTouchId = 0;
		this.touchStartY = 0;
	};

	MobileSlider.prototype.destroy = function()
	{
		this.callbacks.onDestroy();
		this.elements = {};
		this.callbacks = {};
		this.parent = null;
	};

	MobileSlider.prototype._onBackgroundClick = function()
	{
		this.closeWithAnimation();
	};

	var MobileMenu = function(config)
	{
		this.parent = config.parent || null;
		this.header = BX.prop.getString(config, "header", "");
		this.largeIcons = BX.prop.getBoolean(config, "largeIcons", false);

		this.slider = null;

		var items = BX.prop.getArray(config, "items", []);
		if (items.length === 0)
		{
			throw Error("Items array should not be empty");
		}

		this.items = items.filter(function (item) {return typeof(item) === "object" && !!item}).map(function(item)
		{
			return new MobileMenuItem(item);
		});

		this.elements = {
			root: null,
			header: null,
			body: null
		};

		this.callbacks = {
			onClose: BX.prop.getFunction(config, "onClose", BX.DoNothing),
			onDestroy: BX.prop.getFunction(config, "onDestroy", BX.DoNothing),
		};
	};

	MobileMenu.prototype.render = function()
	{
		this.elements.header = BX.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-header"
			},
			text: this.header
		});
		this.elements.body = BX.create("div", {
			props: {
				className: "bx-videocall-mobile-menu-body" + (this.largeIcons ? " bx-videocall-mobile-menu-large" : "")
			}
		});

		this.items.forEach(function(item)
		{
			if (item)
			{
				this.elements.body.appendChild(item.render());
			}
		}, this);

		return BX.createFragment([
			this.elements.header,
			this.elements.body
		]);
	};

	MobileMenu.prototype.setHeader = function(header)
	{
		this.header = header;
		if (this.elements.header)
		{
			this.elements.header.innerText = header;
		}
	};

	MobileMenu.prototype.show = function()
	{
		if (!this.slider)
		{
			this.slider = new MobileSlider({
				parent: this.parent,
				content: this.render(),
				onClose: this.onSliderClose.bind(this),
				onDestroy: this.onSliderDestroy.bind(this),
			});
		}

		this.slider.show()
	};

	MobileMenu.prototype.close = function()
	{
		if (this.slider)
		{
			this.slider.close()
		}
	};

	MobileMenu.prototype.onSliderClose = function()
	{
		this.slider.destroy();
	};

	MobileMenu.prototype.onSliderDestroy = function()
	{
		this.slider = null;
		this.destroy();
	};

	MobileMenu.prototype.destroy = function()
	{
		if (this.slider)
		{
			this.slider.destroy();
		}
		this.slider = null;
		this.items.forEach(function(item)
		{
			item.destroy();
		});
		this.items = [];

		this.callbacks.onDestroy();
		this.elements = {};
		this.callbacks = {};
		this.parent = null;
	};

	var MobileMenuItem = function(config)
	{
		this.id = BX.prop.getString(config, "id", BX.Call.Util.getUuidv4());
		this.icon = BX.prop.getString(config, "icon", "");
		this.iconClass = BX.prop.getString(config, "iconClass", "");
		this.text = BX.prop.getString(config, "text", "");
		this.showSubMenu = BX.prop.getBoolean(config, "showSubMenu", false);
		this.separator = BX.prop.getBoolean(config, "separator", false);
		this.enabled = BX.prop.getBoolean(config, "enabled", true);
		this.userModel = BX.prop.get(config, "userModel", null);

		if (this.userModel)
		{
			this._userChangeHandler = this._onUserChange.bind(this);
			this.subscribeUserEvents();
			this.text = this.userModel.name;
			this.icon = this.userModel.avatar;
			this.iconClass = "user-avatar";
		}

		this.elements = {
			root: null,
			icon: null,
			content: null,
			submenu: null,
			separator: null,
			mic: null,
			cam: null,
		};

		this.callbacks = {
			click: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			clickSubMenu: BX.prop.getFunction(config, "onClickSubMenu", BX.DoNothing),
		};
	};

	MobileMenuItem.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		if (this.separator)
		{
			this.elements.root = BX.create("hr", {
				props: {
					className: "bx-videocall-mobile-menu-item-separator",
				},
			})
		}
		else
		{
			this.elements.root = BX.create("div", {
				props: {
					className: "bx-videocall-mobile-menu-item" + (this.enabled ? "" : " disabled"),
				},
				children: [
					this.elements.icon = BX.create("div", {
						props: {
							className: "bx-videocall-mobile-menu-item-icon " + this.iconClass
						}
					}),
					this.elements.content = BX.create("div", {
						props: {
							className: "bx-videocall-mobile-menu-item-content"
						},
						children: [
							BX.create("span", {
								text: this.text
							})
						]
					}),
				],
				events: {
					click: this.callbacks.click
				}
			});

			if (this.icon != "")
			{
				this.elements.icon.style.backgroundImage = "url(\"" + this.icon + "\")";
			}

			if (this.showSubMenu)
			{
				this.elements.submenu = BX.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-item-submenu-icon"
					}
				});
				this.elements.root.appendChild(this.elements.submenu);
			}

			if (this.userModel)
			{
				this.elements.mic = BX.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-icon-user bx-call-view-icon-red-microphone-off"
					}
				});
				this.elements.cam = BX.create("div", {
					props: {
						className: "bx-videocall-mobile-menu-icon-user bx-call-view-icon-red-camera-off"
					}
				});
				if (!this.userModel.cameraState)
				{
					this.elements.content.prepend(this.elements.cam);
				}
				if (!this.userModel.microphoneState)
				{
					this.elements.content.prepend(this.elements.mic);
				}
			}
		}

		return this.elements.root;
	};

	MobileMenuItem.prototype.updateUserIcons = function()
	{
		if (!this.userModel)
		{
			return;
		}

		if (this.userModel.microphoneState)
		{
			BX.remove(this.elements.mic);
		}
		else
		{
			this.elements.content.prepend(this.elements.mic);
		}
		if (this.userModel.cameraState)
		{
			BX.remove(this.elements.cam);
		}
		else
		{
			this.elements.content.prepend(this.elements.cam);
		}
	};

	MobileMenuItem.prototype.subscribeUserEvents = function()
	{
		this.userModel.subscribe("changed", this._userChangeHandler);
	};

	MobileMenuItem.prototype._onUserChange = function(event)
	{
		this.updateUserIcons();
	};

	MobileMenuItem.prototype.destroy = function()
	{
		if (this.userModel)
		{
			this.userModel.unsubscribe("changed", this._userChangeHandler);
			this.userModel = null;
		}
		this.callbacks = null;
		this.elements = null;
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
			html: this.getTitle()
		});

		return this.elements.root;
	};

	TitleButton.prototype.getTitle = function()
	{
		var prettyName = '<span class="bx-messenger-videocall-panel-title-name">' + BX.util.htmlspecialchars(this.text) + '</span>';

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
		this.backgroundClass = BX.prop.getString(config, "backgroundClass", "");
		this.backgroundClass = "bx-messenger-videocall-panel-icon-background" + (this.backgroundClass ? " " : "") + this.backgroundClass;
		this.blocked = config.blocked === true;

		this.text = BX.prop.getString(config, "text", "");
		this.isActive = false;
		this.counter = BX.prop.getInteger(config, "counter", 0);

		this.elements = {
			root: null,
			counter: null,
		};

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
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
			props: {className: "bx-messenger-videocall-panel-item" + (this.blocked ? " blocked" : "")},
			children: [
				BX.create("div", {
					props: {className: this.backgroundClass},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-videocall-panel-icon bx-messenger-videocall-panel-icon-" + this.class},
							children: [
								this.elements.counter = BX.create("span", {
									props: {className: "bx-messenger-videocall-panel-item-counter"},
									text: 0,
									dataset: {
										counter: 0,
										counterType: 'digits',
									}
								}),
							]
						}),
					]
				}),
				textNode,
				BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-item-bottom-spacer"}
				})
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		});

		if(this.isActive)
		{
			this.elements.root.classList.add("active");
		}

		if (this.counter)
		{
			this.setCounter(this.counter);
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

	SimpleButton.prototype.setBlocked = function (isBlocked)
	{
		if (this.blocked == isBlocked)
		{
			return;
		}

		this.blocked = isBlocked;
		if (this.blocked)
		{
			this.elements.root.classList.add("blocked");
		}
		else
		{
			this.elements.root.classList.remove("blocked");
		}
	};

	SimpleButton.prototype.setCounter = function (counter)
	{
		this.counter = parseInt(counter, 10);

		var counterLabel = this.counter;
		if (counterLabel > 999)
		{
			counterLabel = 999;
		}

		var counterType = 'digits';
		if (counterLabel.toString().length === 2)
		{
			counterType = 'dozens';
		}
		else if (counterLabel.toString().length > 2)
		{
			counterType = 'hundreds';
		}

		this.elements.counter.dataset.counter = counterLabel;
		this.elements.counter.dataset.counterType = counterType;
		this.elements.counter.innerText = counterLabel;
	};

	var DeviceButton = function(config)
	{
		this.class = config.class;
		this.text = config.text;

		this.enabled = (config.enabled === true);
		this.arrowEnabled = (config.arrowEnabled === true);
		this.arrowHidden = (config.arrowHidden === true);
		this.blocked = (config.blocked === true);

		this.showLevel = (config.showLevel === true);
		this.level = config.level || 0;

		this.sideIcon = BX.prop.getString(config, "sideIcon", "");

		this.elements = {
			root: null,
			iconContainer: null,
			icon: null,
			arrow: null,
			levelMeter: null,
			pointer: null,
			ellipsis: null,
		};

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onArrowClick: BX.prop.getFunction(config, "onArrowClick", BX.DoNothing),
			onSideIconClick: BX.prop.getFunction(config, "onSideIconClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	DeviceButton.prototype.render = function()
	{
		if(this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {
				id: "bx-messenger-videocall-panel-item-with-arrow-"+this.class,
				className: "bx-messenger-videocall-panel-item-with-arrow" + (this.blocked ? " blocked" : "")
			},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-item-with-arrow-left"},
					children: [
						this.elements.iconContainer = BX.create("div", {
							props: {className: "bx-messenger-videocall-panel-item-with-arrow-icon-container"},
							children: [
								this.elements.icon = BX.create("div", {
									props: {className: this.getIconClass()},
								}),
							]
						}),

						BX.create("div", {
							props: {className: "bx-messenger-videocall-panel-text"},
							text: this.text
						})
					]
				})
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		});

		this.elements.arrow = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-item-with-arrow-right"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-panel-item-with-arrow-right-icon"},
				})
			],
			events: {
				click: function(e)
				{
					this.callbacks.onArrowClick.apply(this, arguments);
					e.stopPropagation();
				}.bind(this)
			}
		});

		if(!this.arrowHidden)
		{
			this.elements.root.appendChild(this.elements.arrow);
		}

		if (this.showLevel)
		{
			this.elements.icon.appendChild(createSVG("svg", {
				attrNS: {
					class: "bx-messenger-videocall-panel-item-level-meter-container",
					width: 3, height: 20
				},
				children: [
					createSVG("g", {
						attrNS: {
							fill: "#30B1DC"
						},
						children: [
							createSVG("rect", {
								attrNS: {
									x: 0, y: 0, width: 3, height: 20, rx: 1.5, opacity: .1,
								}
							}),
							this.elements.levelMeter = createSVG("rect", {
								attrNS: {
									x: 0, y: 20, width: 3, height: 20, rx: 1.5,
								}
							}),
						]
					})
				]
			}));
		}

		this.elements.ellipsis = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-icon-ellipsis"},
			events: {
				click: this.callbacks.onSideIconClick
			}
		})

		this.elements.pointer = BX.create("div", {
			props: {className: "bx-messenger-videocall-panel-icon-pointer"},
			events: {
				click: this.callbacks.onSideIconClick
			}
		})

		if (this.sideIcon == "pointer")
		{
			BX.Dom.insertAfter(this.elements.pointer, this.elements.icon);
		}
		else if (this.sideIcon == "ellipsis")
		{
			BX.Dom.insertAfter(this.elements.ellipsis, this.elements.icon);
		}

		return this.elements.root;
	};

	DeviceButton.prototype.getIconClass = function()
	{
		return "bx-messenger-videocall-panel-item-with-arrow-icon bx-messenger-videocall-panel-item-with-arrow-icon-" + this.class + (this.enabled ? "" : "-off");
	};

	DeviceButton.prototype.enable = function()
	{
		if(this.enabled)
		{
			return;
		}
		this.enabled = true;
		this.elements.icon.className = this.getIconClass();
		if (this.elements.levelMeter)
		{
			this.elements.levelMeter.setAttribute('y', Math.round((1-this.level) * 20));
		}
	};

	DeviceButton.prototype.disable = function()
	{
		if(!this.enabled)
		{
			return;
		}
		this.enabled = false;
		this.elements.icon.className = this.getIconClass();
		if (this.elements.levelMeter)
		{
			this.elements.levelMeter.setAttribute('y', 20);
		}
	};

	DeviceButton.prototype.setBlocked = function (blocked)
	{
		if (this.blocked == blocked)
		{
			return;
		}

		this.blocked = blocked;
		this.elements.icon.className = this.getIconClass();
		if (this.blocked)
		{
			this.elements.root.classList.add("blocked");
		}
		else
		{
			this.elements.root.classList.remove("blocked");
		}
	};

	DeviceButton.prototype.setSideIcon = function (sideIcon)
	{
		if (this.sideIcon == sideIcon)
		{
			return;
		}
		this.sideIcon = sideIcon;

		BX.Dom.remove(this.elements.pointer);
		BX.Dom.remove(this.elements.ellipsis);

		if (this.sideIcon == "pointer")
		{
			BX.Dom.insertAfter(this.elements.pointer, this.elements.icon);
		}
		else if (this.sideIcon == "ellipsis")
		{
			BX.Dom.insertAfter(this.elements.ellipsis, this.elements.icon);
		}
	}

	DeviceButton.prototype.showArrow = function()
	{
		if(!this.arrowHidden)
		{
			return;
		}
		this.arrowHidden = false;
		this.elements.root.appendChild(this.elements.arrow);
	};

	DeviceButton.prototype.hideArrow = function()
	{
		if(this.arrowHidden)
		{
			return;
		}
		this.arrowHidden = false;
		this.elements.root.removeChild(this.elements.arrow);
	};

	DeviceButton.prototype.setLevel = function(level)
	{
		this.level = Math.log(level * 100) / 4.6;
		if (this.showLevel && this.enabled)
		{
			this.elements.levelMeter.setAttribute('y', Math.round((1-this.level) * 20));
		}
	}

	var WaterMarkButton = function(config)
	{
		this.language = config.language;
	};

	WaterMarkButton.prototype.render = function()
	{
		return BX.create("div", {
			props: {className: "bx-messenger-videocall-watermark"},
			children: [
				BX.create("img", {
					props: {
						className: "bx-messenger-videocall-watermark-img",
						src: this.getWatermarkUrl(this.language)
					},
				})
			]
		});
	};

	WaterMarkButton.prototype.getWatermarkUrl = function(language)
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

	var TopButton = function(config)
	{
		this.iconClass = BX.prop.getString(config, "iconClass", "");
		this.text = BX.prop.getString(config, "text", "");

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	TopButton.prototype.render = function()
	{
		return BX.create("div", {
			props: {className: "bx-messenger-videocall-top-button"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-button-icon " + this.iconClass}
				}),
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-button-text "},
					text: this.text
				})
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		})
	};

	var TopFramelessButton = function(config)
	{
		this.iconClass = BX.prop.getString(config, "iconClass", "");
		this.textClass = BX.prop.getString(config, "textClass", "");
		this.text = BX.prop.getString(config, "text", "");

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	TopFramelessButton.prototype.render = function()
	{
		return BX.create("div", {
			props: {className: "bx-messenger-videocall-top-button-frameless"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-button-icon " + this.iconClass}
				}),
				(this.text != "" ?
					BX.create("div", {
						props: {className: "bx-messenger-videocall-top-button-text " + this.textClass},
						text: this.text
					})
					:
					null
				)
			],
			events: {
				click: this.callbacks.onClick,
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			},
		});
	};

	var ParticipantsButton = function(config)
	{
		this.count = BX.prop.getInteger(config, "count", 0);
		this.foldButtonState = BX.prop.getString(config, "foldButtonState", ParticipantsButton.FoldButtonState.Hidden);
		this.allowAdding = BX.prop.getBoolean(config, "allowAdding", false);

		this.elements = {
			root: null,
			leftContainer: null,
			rightContainer: null,
			foldIcon: null,
			count: null,
			separator: null
		};

		this.callbacks = {
			onListClick: BX.prop.getFunction(config, "onListClick", BX.DoNothing),
			onAddClick: BX.prop.getFunction(config, "onAddClick", BX.DoNothing)
		}
	};

	ParticipantsButton.FoldButtonState = {
		Active: "active",
		Fold: "fold",
		Unfold: "unfold",
		Hidden: "hidden"
	};

	ParticipantsButton.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}
		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-top-participants"},
			children: [
				this.elements.leftContainer = BX.create("div", {
					props: {className: "bx-messenger-videocall-top-participants-inner left" + (this.foldButtonState != ParticipantsButton.FoldButtonState.Hidden ? " active" : "")},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-videocall-top-button-icon participants"}
						}),
						this.elements.count = BX.create("div", {
							props: {className: "bx-messenger-videocall-top-participants-text-count"},
							text: this.count
						}),
						this.elements.foldIcon = BX.create("div", {
							props: {className: "bx-messenger-videocall-top-participants-fold-icon " + this.foldButtonState},
						})
					],
					events: {
						click: this.callbacks.onListClick
					}
				}),

			]
		});

		this.elements.separator = BX.create("div", {
			props: {className: "bx-messenger-videocall-top-participants-separator"}
		});
		this.elements.rightContainer = BX.create("div", {
			props: {className: "bx-messenger-videocall-top-participants-inner active"},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-button-icon add"}
				}),
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-participants-text"},
					text: BX.message("IM_M_CALL_BTN_ADD")
				})
			],
			events: {
				click: this.callbacks.onAddClick
			}
		});

		if (this.allowAdding)
		{
			this.elements.root.appendChild(this.elements.separator);
			this.elements.root.appendChild(this.elements.rightContainer);
		}
		return this.elements.root;
	};

	ParticipantsButton.prototype.update = function(config)
	{
		this.count = BX.prop.getInteger(config, "count", this.count);
		this.foldButtonState = BX.prop.getString(config, "foldButtonState", this.foldButtonState);
		this.allowAdding = BX.prop.getBoolean(config, "allowAdding", this.allowAdding);

		this.elements.count.innerText = this.count;

		this.elements.foldIcon.className = "bx-messenger-videocall-top-participants-fold-icon " + this.foldButtonState;
		if (this.foldButtonState == ParticipantsButton.FoldButtonState.Hidden)
		{
			this.elements.leftContainer.classList.remove("active");
		}
		else
		{
			this.elements.leftContainer.classList.add("active");
		}

		if (this.allowAdding && !this.elements.separator.parentElement)
		{
			this.elements.root.appendChild(this.elements.separator);
			this.elements.root.appendChild(this.elements.rightContainer);
		}
		if (!this.allowAdding && this.elements.separator.parentElement)
		{
			BX.remove(this.elements.separator);
			BX.remove(this.elements.rightContainer);
		}
	};

	var ParticipantsButtonMobile = function(config)
	{
		this.count = BX.prop.getInteger(config, "count", 0);
		this.elements = {
			root: null,
			icon: null,
			text: null,
			arrow: null
		};

		this.callbacks = {
			onClick: BX.prop.getFunction(config, "onClick", BX.DoNothing),
		}
	};

	ParticipantsButtonMobile.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {
				className: "bx-messenger-videocall-top-participants-mobile"
			},
			children: [
				this.elements.icon = BX.create("div", {
					props: {
						className: "bx-messenger-videocall-top-participants-mobile-icon"}
				}),
				this.elements.text = BX.create("div", {
					props: {
						className: "bx-messenger-videocall-top-participants-mobile-text"
					},
					text: BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.count)
				}),
				this.elements.arrow = BX.create("div", {
					props: {
						className: "bx-messenger-videocall-top-participants-mobile-arrow"
					}
				}),
			],
			events: {
				click: this.callbacks.onClick
			}
		});

		return this.elements.root;
	};

	ParticipantsButtonMobile.prototype.setCount = function(count)
	{
		if (this.count == count)
		{
			return;
		}
		this.count = count;
		this.elements.text.innerText = BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.count);
	};

	var RecordStatusButton = function(config)
	{
		this.userId = config.userId;
		this.recordState = config.recordState;

		this.updateViewInterval = null;

		this.elements = {
			root: null,
			timeText: null,
			stateText: null,
		};

		this.callbacks = {
			onPauseClick: BX.prop.getFunction(config, "onPauseClick", BX.DoNothing),
			onStopClick: BX.prop.getFunction(config, "onStopClick", BX.DoNothing),
			onMouseOver: BX.prop.getFunction(config, "onMouseOver", BX.DoNothing),
			onMouseOut: BX.prop.getFunction(config, "onMouseOut", BX.DoNothing),
		}
	};

	RecordStatusButton.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {className: "bx-messenger-videocall-top-recordstatus record-status-"+this.recordState.state+" "+(this.recordState.userId == this.userId? '': 'record-user-viewer')},
			children: [
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-recordstatus-status"},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-videocall-top-button-icon record-status"}
						}),
					]
				}),
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-recordstatus-time"},
					children: [
						this.elements.timeText = BX.create("span", {
							props: {className: "bx-messenger-videocall-top-recordstatus-time-text"},
							text: this.getTimeText()
						}),
						BX.create("span", {
							props: {className: "bx-messenger-videocall-top-recordstatus-time-separator"},
							html: ' &ndash; '
						}),
						this.elements.stateText = BX.create("span", {
							props: {className: "bx-messenger-videocall-top-recordstatus-time-state"},
							text: BX.message('IM_M_CALL_RECORD_TITLE')
						}),
					]
				}),
				BX.create("div", {
					props: {className: "bx-messenger-videocall-top-recordstatus-buttons"},
					children: [
						BX.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-separator"}
						}),
						BX.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-button"},
							children: [
								BX.create("div", {
									props: {className: "bx-messenger-videocall-top-button-icon record-pause"},
								}),
							],
							events: {
								click: this.callbacks.onPauseClick
							}
						}),
						BX.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-separator"}
						}),
						BX.create("div", {
							props: {className: "bx-messenger-videocall-top-recordstatus-button"},
							children: [
								BX.create("div", {
									props: {className: "bx-messenger-videocall-top-button-icon record-stop"},
								}),
							],
							events: {
								click: this.callbacks.onStopClick
							}
						}),
					]
				}),
			],
			events: {
				mouseover: this.callbacks.onMouseOver,
				mouseout: this.callbacks.onMouseOut
			}
		});

		return this.elements.root;
	};

	RecordStatusButton.prototype.getTimeText = function()
	{
		if (this.recordState.state === BX.Call.View.RecordState.Stopped)
		{
			return '';
		}

		var nowDate = new Date();
		var startDate = new Date(this.recordState.date.start);
		if (startDate.getTime() < nowDate.getDate())
		{
			startDate = nowDate;
		}

		var pauseTime = this.recordState.date.pause.map(function (element) {
			var finish = element.finish? new Date(element.finish): nowDate;
			return finish - new Date(element.start);
		}).reduce(function(sum, element) {
			return sum + element;
		}, 0);

		var totalTime = nowDate - startDate - pauseTime;
		if (totalTime <= 0)
		{
			totalTime = 0;
		}

		var second = Math.floor(totalTime/1000);

		var hour = Math.floor(second/60/60);
		if (hour > 0)
		{
			second -= hour*60*60;
		}

		var minute = Math.floor(second/60);
		if (minute > 0)
		{
			second -= minute*60;
		}

		return (hour > 0? hour+':': '')
				+ (hour > 0? minute.toString().padStart(2, "0")+':': minute+':')
				+ second.toString().padStart(2, "0")
		;
	}

	RecordStatusButton.prototype.update = function(recordState)
	{
		if (this.recordState.state !== recordState.state)
		{
			clearInterval(this.updateViewInterval);
			if (recordState.state === BX.Call.View.RecordState.Started)
			{
				this.updateViewInterval = setInterval(this.updateView.bind(this), 1000);
			}
		}

		this.recordState = recordState;
		this.updateView();
	}

	RecordStatusButton.prototype.updateView = function()
	{
		var timeText = this.getTimeText();
		if (this.elements.timeText.innerText !== timeText)
		{
			this.elements.timeText.innerText = this.getTimeText();
		}

		if (!this.elements.root.classList.contains("record-status-"+this.recordState.state))
		{
			this.elements.root.className = "bx-messenger-videocall-top-recordstatus record-status-"+this.recordState.state+' '+(this.recordState.userId == this.userId? '': 'record-user-viewer');
		}
	};

	RecordStatusButton.prototype.stopViewUpdate = function()
	{
		if (this.updateViewInterval)
		{
			clearInterval(this.updateViewInterval);
			this.updateViewInterval = null;
		}
	};

	/**
	 * @param config
	 * @param {Node} config.parentElement
	 * @param {boolean} config.cameraEnabled
	 * @param {boolean} config.microphoneEnabled
	 * @param {boolean} config.speakerEnabled
	 * @param {boolean} config.allowHdVideo
	 * @param {boolean} config.faceImproveEnabled
	 * @constructor
	 */
	var DeviceSelector = function(config)
	{
		this.viewElement = config.viewElement || null;
		this.parentElement = config.parentElement;

		this.cameraEnabled = BX.prop.getBoolean(config, "cameraEnabled", false);
		this.cameraId = BX.prop.getString(config, "cameraId", false);
		this.microphoneEnabled = BX.prop.getBoolean(config, "microphoneEnabled", false);
		this.microphoneId = BX.prop.getString(config, "microphoneId", false);
		this.speakerEnabled = BX.prop.getBoolean(config, "speakerEnabled", false);
		this.speakerId = BX.prop.getString(config, "speakerId", false);
		this.allowHdVideo = BX.prop.getBoolean(config, "allowHdVideo", false);
		this.faceImproveEnabled = BX.prop.getBoolean(config, "faceImproveEnabled", false);
		this.allowFaceImprove = BX.prop.getBoolean(config, "allowFaceImprove", false);
		this.allowBackground = BX.prop.getBoolean(config, "allowBackground", true);
		this.allowMask = BX.prop.getBoolean(config, "allowMask", true);
		this.allowAdvancedSettings = BX.prop.getBoolean(config, "allowAdvancedSettings", false);
		this.showCameraBlock = BX.prop.getBoolean(config, "showCameraBlock", true);

		this.popup = null;
		this.eventEmitter = new BX.Event.EventEmitter(this, "DeviceSelector");
		this.elements = {
			root: null,
			micContainer: null,
			cameraContainer: null,
			speakerContainer: null,
		};
		var eventListeners = BX.prop.getObject(config, "events", {});
		Object.values(DeviceSelector.Events).forEach(function(eventName)
		{
			if(eventListeners[eventName])
			{
				this.eventEmitter.subscribe(eventName, eventListeners[eventName]);
			}
		}, this)
	};

	/**
	 * @param config
	 * @param {Node} config.parentElement
	 * @param {boolean} config.cameraEnabled
	 * @param {boolean} config.microphoneEnabled
	 * @param {boolean} config.speakerEnabled
	 * @param {boolean} config.allowHdVideo
	 * @param {boolean} config.faceImproveEnabled
	 * @param {object} config.events

	 * @returns {DeviceSelector}
	 */
	DeviceSelector.create = function(config)
	{
		return new DeviceSelector(config);
	};

	DeviceSelector.Events = {
		onMicrophoneSelect: "onMicrophoneSelect",
		onMicrophoneSwitch: "onMicrophoneSwitch",
		onCameraSelect: "onCameraSelect",
		onCameraSwitch: "onCameraSwitch",
		onSpeakerSelect: "onSpeakerSelect",
		onSpeakerSwitch: "onSpeakerSwitch",
		onChangeHdVideo: "onChangeHdVideo",
		onChangeMicAutoParams: "onChangeMicAutoParams",
		onChangeFaceImprove: "onChangeFaceImprove",
		onShow: "onShow",
		onDestroy: "onDestroy",
	};

	DeviceSelector.prototype.show = function()
	{
		if(this.popup)
		{
			this.popup.show();
			return;
		}
		this.popup = new BX.PopupWindow(
			'call-view-device-selector',
			this.parentElement,
			{
				targetContainer: this.viewElement,
				autoHide: true,
				zIndex: window['BX'] && BX.MessengerCommon ? (BX.MessengerCommon.getDefaultZIndex() + 500) : 1500,
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
				content: this.render(),
				events: {
					onPopupClose : function()
					{
						this.popup.destroy();
					}.bind(this),
					onPopupDestroy: function ()
					{
						this.destroy();
					}.bind(this)
				}
			}
		);
		this.popup.show();

		this.eventEmitter.emit(DeviceSelector.Events.onShow, {});
	};

	DeviceSelector.prototype.render = function()
	{
		if(this.elements.root)
		{
			return this.elements.root;
		}

		return BX.create("div", {
			props: {className: "bx-call-view-device-selector"},
			children: [
				BX.create("div", {
					props: {className: "bx-call-view-device-selector-top"},
					children: [
						DeviceMenu.create({
							deviceLabel: BX.message("IM_M_CALL_BTN_MIC"),
							deviceList: BX.Call.Hardware.getMicrophoneList(),
							selectedDevice: this.microphoneId,
							deviceEnabled: this.microphoneEnabled,
							icons: ["microphone", "microphone-off"],
							events: {
								onSwitch: this.onMicrophoneSwitch.bind(this),
								onSelect: this.onMicrophoneSelect.bind(this)
							}
						}).render(),
						this.showCameraBlock ?
							DeviceMenu.create({
								deviceLabel: BX.message("IM_M_CALL_BTN_CAMERA"),
								deviceList: BX.Call.Hardware.getCameraList(),
								selectedDevice: this.cameraId,
								deviceEnabled: this.cameraEnabled,
								icons: ["camera", "camera-off"],
								events: {
									onSwitch: this.onCameraSwitch.bind(this),
									onSelect: this.onCameraSelect.bind(this)
								}
							}).render()
							: null,
						BX.Call.Hardware.canSelectSpeaker() ?
							DeviceMenu.create({
								deviceLabel: BX.message("IM_M_CALL_BTN_SPEAKER"),
								deviceList: BX.Call.Hardware.getSpeakerList(),
								selectedDevice: this.speakerId,
								deviceEnabled: this.speakerEnabled,
								icons: ["speaker", "speaker-off"],
								events: {
									onSwitch: this.onSpeakerSwitch.bind(this),
									onSelect: this.onSpeakerSelect.bind(this)
								}
							}).render()
							: null,
					]
				}),
				BX.create("div", {
					props: {className: "bx-call-view-device-selector-bottom"},
					children: [
						BX.create("div", {
							props: {className: "bx-call-view-device-selector-bottom-item"},
							children: [
								BX.create("input", {
									props: {
										id: "device-selector-hd-video",
										className: "bx-call-view-device-selector-bottom-item-checkbox"
									},
									attrs: {
										type: "checkbox",
										checked: this.allowHdVideo
									},
									events: {
										change: this.onAllowHdVideoChange.bind(this)
									}
								}),
								BX.create("label", {
									props: {className: "bx-call-view-device-selector-bottom-item-label"},
									attrs: {for: "device-selector-hd-video"},
									text: BX.message("IM_M_CALL_HD_VIDEO")
								}),
							]
						}),
						this.allowFaceImprove ?
							BX.create("div", {
								props: {className: "bx-call-view-device-selector-bottom-item"},
								children: [
									BX.create("input", {
										props: {
											id: "device-selector-mic-auto-params",
											className: "bx-call-view-device-selector-bottom-item-checkbox"
										},
										attrs: {
											type: "checkbox",
											checked: this.faceImproveEnabled
										},
										events: {
											change: this.onFaceImproveChange.bind(this)
										}
									}),
									BX.create("label", {
										props: {className: "bx-call-view-device-selector-bottom-item-label"},
										attrs: {for: "device-selector-mic-auto-params"},
										text: BX.message("IM_SETTINGS_HARDWARE_CAMERA_FACE_IMPROVE")
									}),
								]
							})
							: null,
						this.allowBackground ?
							BX.create("div", {
								props: {className: "bx-call-view-device-selector-bottom-item"},
								children: [
									BX.create("span", {
										props: {className: "bx-call-view-device-selector-bottom-item-action"},
										text: this.allowMask? BX.message("IM_M_CALL_BG_MASK_CHANGE"): BX.message("IM_M_CALL_BACKGROUND_CHANGE"),
										events: {
											click: function() {
												BX.Call.Hardware.BackgroundDialog.open();
												this.popup.close();
											}.bind(this)
										}
									}),
								]
							})
							: null,
						this.allowAdvancedSettings ?
							BX.create("div", {
								props: {className: "bx-call-view-device-selector-bottom-item"},
								children: [
									BX.create("span", {
										props: {className: "bx-call-view-device-selector-bottom-item-action"},
										text: BX.message("IM_M_CALL_ADVANCED_SETTINGS"),
										events: {
											click: function() {
												BXIM.openSettings({onlyPanel: 'hardware'});
												this.popup.close();
											}.bind(this)
										}
									}),
								]
							})
							: null,
					]
				}),
			]
		})
	};

	DeviceSelector.prototype.onMicrophoneSwitch = function(e)
	{
		this.microphoneEnabled = !this.microphoneEnabled;
		this.eventEmitter.emit(DeviceSelector.Events.onMicrophoneSwitch, {
			microphoneEnabled: this.microphoneEnabled
		})
	};

	DeviceSelector.prototype.onMicrophoneSelect = function(e)
	{
		this.eventEmitter.emit(DeviceSelector.Events.onMicrophoneSelect, {
			deviceId: e.data.deviceId
		})
	};

	DeviceSelector.prototype.onCameraSwitch = function(e)
	{
		this.cameraEnabled = !this.cameraEnabled;
		this.eventEmitter.emit(DeviceSelector.Events.onCameraSwitch, {
			cameraEnabled: this.cameraEnabled
		})
	};

	DeviceSelector.prototype.onCameraSelect = function(e)
	{
		this.eventEmitter.emit(DeviceSelector.Events.onCameraSelect, {
			deviceId: e.data.deviceId
		});
	};

	DeviceSelector.prototype.onSpeakerSwitch = function(e)
	{
		this.speakerEnabled = !this.speakerEnabled;
		this.eventEmitter.emit(DeviceSelector.Events.onSpeakerSwitch, {
			speakerEnabled: this.speakerEnabled
		})
	};

	DeviceSelector.prototype.onSpeakerSelect = function(e)
	{
		this.eventEmitter.emit(DeviceSelector.Events.onSpeakerSelect, {
			deviceId: e.data.deviceId
		});
	};

	DeviceSelector.prototype.onAllowHdVideoChange = function(e)
	{
		this.allowHdVideo = e.currentTarget.checked;
		this.eventEmitter.emit(DeviceSelector.Events.onChangeHdVideo, {
			allowHdVideo: this.allowHdVideo
		})
	};

	DeviceSelector.prototype.onAllowMirroringVideoChange = function(e)
	{
		BX.Call.Hardware.enableMirroring = e.target.checked;
	};

	DeviceSelector.prototype.onFaceImproveChange = function(e)
	{
		this.faceImproveEnabled = e.currentTarget.checked;
		this.eventEmitter.emit(DeviceSelector.Events.onChangeFaceImprove, {
			faceImproveEnabled: this.faceImproveEnabled
		})
	};

	DeviceSelector.prototype.destroy = function()
	{
		this.popup = null;
		this.eventEmitter.emit(DeviceSelector.Events.onDestroy, {});
	};

	var DeviceMenu = function(config)
	{
		config = BX.type.isObject(config) ? config : {};

		this.deviceList = BX.prop.getArray(config, "deviceList", []);
		this.selectedDevice = BX.prop.getString(config, "selectedDevice", "");
		this.deviceEnabled = BX.prop.getBoolean(config, "deviceEnabled", false);
		this.deviceLabel = BX.prop.getString(config, "deviceLabel", "");
		this.icons = BX.prop.getArray(config, "icons", []);
		this.eventEmitter = new BX.Event.EventEmitter(this, 'DeviceMenu');
		this.elements = {
			root: null,
			switchIcon: null,
			menuInner: null,
			menuItems: {}  // deviceId => {root: element, icon: element}
		};

		var events = BX.prop.getObject(config, "events", {});
		for (var eventName in events)
		{
			if(!events.hasOwnProperty(eventName))
			{
				continue;
			}

			this.eventEmitter.subscribe(eventName, events[eventName]);
		}
	};

	DeviceMenu.create = function(config)
	{
		return new DeviceMenu(config);
	};

	DeviceMenu.Events = {
		onSelect: "onSelect",
		onSwitch: "onSwitch"
	};

	DeviceMenu.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {className: "bx-call-view-device-selector-menu-container"},
			children: [
				BX.create("div", {
					props: {className: "bx-call-view-device-selector-switch-wrapper"},
					children: [
						this.elements.switchIcon = BX.create("div", {
							props: {className: "bx-call-view-device-selector-device-icon " + this.getDeviceIconClass()}
						}),
						BX.create("span", {
							props: {className: "bx-call-view-device-selector-device-text"},
							text: this.deviceLabel

						}),
						BX.create("div", {
							props: {className: "bx-call-view-device-selector-device-switch"},
							children: [
								(new BX.UI.Switcher({
									size: 'small',
									checked: this.deviceEnabled,
									handlers: {
										toggled: this.onSwitchToggled.bind(this)
									}
								})).getNode()
							]
						}),
					]
				}),
				this.elements.menuInner = BX.create("div", {
					props: {className: "bx-call-view-device-selector-menu-inner" + (this.deviceEnabled ? "" : " inactive")},
					children: this.deviceList.map(this.renderDevice.bind(this))
				})
			]
		});
		return this.elements.root;
	};

	DeviceMenu.prototype.renderDevice = function(deviceInfo)
	{
		var iconClass = this.selectedDevice === deviceInfo.deviceId ? "selected" : "";
		var deviceElements = {};
		deviceElements.root = BX.create("div", {
			props: {className: "bx-call-view-device-selector-menu-item"},
			dataset: {
				deviceId: deviceInfo.deviceId
			},
			children: [
				deviceElements.icon = BX.create("div", {
					props: {className: "bx-call-view-device-selector-menu-item-icon " + iconClass},
				}),
				BX.create("div", {
					props: {className: "bx-call-view-device-selector-menu-item-text"},
					text: deviceInfo.label || "(" + BX.message("IM_M_CALL_DEVICE_NO_NAME") + ")",
				}),
			],
			events: {
				click: this.onMenuItemClick.bind(this)
			}
		});
		this.elements.menuItems[deviceInfo.deviceId] = deviceElements;
		return deviceElements.root;
	};

	DeviceMenu.prototype.getDeviceIconClass = function()
	{
		var result = "";
		if (this.deviceEnabled && this.icons.length > 0)
		{
			result = this.icons[0];
		}
		else if (!this.deviceEnabled && this.icons.length > 1)
		{
			result = this.icons[1];
		}
		return result;
	};

	DeviceMenu.prototype.onSwitchToggled = function(e)
	{
		this.deviceEnabled = !this.deviceEnabled;
		this.elements.switchIcon.className = "bx-call-view-device-selector-device-icon " + this.getDeviceIconClass();
		if (this.deviceEnabled)
		{
			this.elements.menuInner.classList.remove("inactive");
		}
		else
		{
			this.elements.menuInner.classList.add("inactive");
		}

		this.eventEmitter.emit(DeviceMenu.Events.onSwitch, {
			deviceEnabled: this.deviceEnabled
		})
	};

	DeviceMenu.prototype.onMenuItemClick = function(e)
	{
		var currentDevice = this.selectedDevice;
		var selectedDevice = e.currentTarget.dataset.deviceId;
		if (currentDevice == selectedDevice)
		{
			return;
		}
		this.selectedDevice = selectedDevice;
		if(this.elements.menuItems[currentDevice])
		{
			this.elements.menuItems[currentDevice]['icon'].classList.remove('selected');
		}
		if(this.elements.menuItems[this.selectedDevice])
		{
			this.elements.menuItems[this.selectedDevice]['icon'].classList.add('selected');
		}

		this.eventEmitter.emit(DeviceMenu.Events.onSelect, {
			deviceId: this.selectedDevice
		})
	};

	var UserSelector = function(config)
	{
		this.userList = config.userList;
		this.current = config.current;
		this.parentElement = config.parentElement;

		this.menu = null;

		this.callbacks = {
			onSelect: BX.type.isFunction(config.onSelect) ? config.onSelect : BX.DoNothing
		}
	};

	UserSelector.create = function(config)
	{
		return new UserSelector(config);
	};

	UserSelector.prototype.show = function()
	{
		var self = this;
		var menuItems = [];

		this.userList.forEach(function(user)
		{
			menuItems.push({
				id: user.id,
				text: user.name || "unknown (" + user.id+ ")",
				className: (self.current == user.id ?  "menu-popup-item-accept" : "device-selector-empty"),
				onclick: function()
				{
					self.menu.close();
					self.callbacks.onSelect(user.id);
				}
			})
		});

		this.menu = BX.PopupMenu.create(
			'call-view-select-user',
			this.parentElement,
			menuItems,
			{
				autoHide: true,
				zIndex: window['BX'] && BX.MessengerCommon ? (BX.MessengerCommon.getDefaultZIndex() + 500) : 500,
				closeByEsc: true,
				offsetTop: 0,
				offsetLeft: 0,
				bindOptions: {
					position: 'bottom'
				},
				angle: false,
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

	var FloorRequest = function(config)
	{
		this.hideTime = BX.prop.getInteger(config, "hideTime", 10);
		this.userModel = config.userModel;

		this.eventEmitter = new BX.Event.EventEmitter(this, "FloorRequest");

		this.elements = {
			root: null,
			avatar: null
		};

		this._hideTimeout = null;
		this._onUserModelChangedHandler = this._onUserModelChanged.bind(this);
		this.userModel.subscribe("changed", this._onUserModelChangedHandler);
	};

	FloorRequest.create = function(config)
	{
		return new FloorRequest(config);
	};

	FloorRequest.prototype.mount = function(container)
	{
		container.appendChild(this.render());
		this.scheduleDismount();
	};

	FloorRequest.prototype.dismount = function()
	{
		BX.remove(this.elements.root);
		this.destroy();
	};

	FloorRequest.prototype.dismountWithAnimation = function()
	{
		if(!this.elements.root)
		{
			return;
		}
		this.elements.root.classList.add("closing");

		this.elements.root.addEventListener("animationend", function()
		{
			this.dismount();
		}.bind(this));
	};

	FloorRequest.prototype.render = function()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = BX.create("div", {
			props: {className: "bx-call-view-floor-request-notification"},
			children: [
				BX.create("div", {
					props: {className: "bx-call-view-floor-request-notification-icon-container"},
					children: [
						this.elements.avatar = BX.create("div", {
							props: {className: "bx-call-view-floor-request-notification-avatar"}
						}),
						BX.create("div", {
							props: {className: "bx-call-view-floor-request-notification-icon bx-messenger-videocall-floor-request-icon"}
						}),
					]
				}),
				BX.create("span", {
					props: {className: "bx-call-view-floor-request-notification-text-container"},
					html: BX.message("IM_CALL_WANTS_TO_SAY_" + (this.userModel.gender == "F" ? "F" : "M")).replace("#NAME#", '<span class ="bx-call-view-floor-request-notification-text-name">' + BX.util.htmlspecialchars(this.userModel.name) + '</span>')
				}),
				BX.create("div", {
					props: {className: "bx-call-view-floor-request-notification-close"},
					events: {
						click: this.dismount.bind(this)
					}
				})
			]
		});

		if (this.userModel.avatar)
		{
			this.elements.avatar.style.setProperty("--avatar", "url('" + this.userModel.avatar + "')");
		}

		return this.elements.root
	};

	FloorRequest.prototype.scheduleDismount = function()
	{
		return;
		this._hideTimeout = setTimeout(this.dismountWithAnimation.bind(this), this.hideTime * 1000);
	};

	FloorRequest.prototype.subscribe = function(eventName, handler)
	{
		this.eventEmitter.subscribe(eventName, handler);
	};

	FloorRequest.prototype._onUserModelChanged = function(event)
	{
		var eventData = event.data;

		if (eventData.fieldName == "floorRequestState" && !this.userModel.floorRequestState)
		{
			this.dismountWithAnimation();
		}
	};

	FloorRequest.prototype.destroy = function()
	{
		clearTimeout(this._hideTimeout);
		this._hideTimeout = null;
		this.elements = null;
		if (this.userModel)
		{
			this.userModel.unsubscribe("changed", this._onUserModelChangedHandler);
			this.userModel = null;
		}
		this.eventEmitter.emit("onDestroy", {});
	};

	var NotificationManager = function()
	{
		this.maxNotification = maximumNotifications;
		this.notifications = [];
	};

	var NotificationManagerInstance = new NotificationManager();
	Object.defineProperty(NotificationManager, "Instance", {
		get: function()
		{
			return NotificationManagerInstance;
		}
	});

	NotificationManager.prototype.addNotification = function(notification)
	{
		notification.subscribe("onDestroy", function()
		{
			this.onNotificationDestroy(notification)
		}.bind(this));
		this.notifications.push(notification);

		if (this.notifications.length > this.maxNotification)
		{
			var firstNotification = this.notifications.shift();
			firstNotification.dismount();
		}
	};

	NotificationManager.prototype.onNotificationDestroy = function(notification)
	{
		var index = this.notifications.indexOf(notification);

		if (index != -1)
		{
			this.notifications.splice(index, 1);
		}
	};

	var UserModel = function(config)
	{
		this.data = {
			id: BX.prop.getInteger(config, "id", 0),
			name: BX.prop.getString(config, "name", ""),
			avatar: BX.prop.getString(config, "avatar", ""),
			gender: BX.prop.getString(config, "gender", ""),
			state: BX.prop.getString(config, "state", BX.Call.UserState.Idle),
			talking: BX.prop.getBoolean(config, "talking", false),
			cameraState: BX.prop.getBoolean(config, "cameraState", true),
			microphoneState: BX.prop.getBoolean(config, "microphoneState", true),
			screenState: BX.prop.getBoolean(config, "screenState", false),
			videoPaused: BX.prop.getBoolean(config, "videoPaused", false),
			floorRequestState: BX.prop.getBoolean(config, "floorRequestState", false),
			localUser: BX.prop.getBoolean(config, "localUser", false),
			centralUser: BX.prop.getBoolean(config, "centralUser", false),
			pinned: BX.prop.getBoolean(config, "pinned", false),
			presenter: BX.prop.getBoolean(config, "presenter", false),
			order: BX.prop.getInteger(config, "order", false),
			allowRename: BX.prop.getBoolean(config, "allowRename", false),
			wasRenamed: BX.prop.getBoolean(config, "wasRenamed", false),
			renameRequested:  BX.prop.getBoolean(config, "renameRequested", false),
			direction: BX.prop.getString(config, "direction", BX.Call.EndpointDirection.SendRecv),
		};

		for (var fieldName in this.data)
		{
			if (this.data.hasOwnProperty(fieldName))
			{
				Object.defineProperty(this, fieldName, {
					get: this._getField(fieldName).bind(this),
					set: this._setField(fieldName).bind(this),
				});
			}
		}

		this.onUpdate = {
			talking: this._onUpdateTalking.bind(this),
			state: this._onUpdateState.bind(this),
		};

		this.talkingStop = null;

		this.eventEmitter = new BX.Event.EventEmitter(this, 'UserModel');
	};

	UserModel.prototype._getField = function(fieldName)
	{
		return function()
		{
			return this.data[fieldName];
		}
	};

	UserModel.prototype._setField = function(fieldName)
	{
		return function(newValue)
		{
			var oldValue = this.data[fieldName];
			if (oldValue == newValue)
			{
				return;
			}
			this.data[fieldName] = newValue;

			if (this.onUpdate.hasOwnProperty(fieldName))
			{
				this.onUpdate[fieldName](newValue, oldValue);
			}

			this.eventEmitter.emit("changed", {
				user: this,
				fieldName: fieldName,
				oldValue: oldValue,
				newValue: newValue,
			});
		}
	};

	UserModel.prototype._onUpdateTalking = function(talking)
	{
		if (talking)
		{
			this.floorRequestState = false;
		}
		else
		{
			this.talkingStop = (new Date()).getTime();
		}
	};

	UserModel.prototype._onUpdateState = function(newValue)
	{
		if (newValue != BX.Call.UserState.Connected)
		{
			this.talking = false;
			this.screenState = false;
		}
	};

	UserModel.prototype.wasTalkingAgo = function()
	{
		if (this.state != BX.Call.UserState.Connected)
		{
			return +Infinity;
		}
		if (this.talking)
		{
			return 0;
		}
		if (!this.talkingStop)
		{
			return +Infinity;
		}

		return ((new Date()).getTime() - this.talkingStop);
	};

	UserModel.prototype.subscribe = function(event, handler)
	{
		this.eventEmitter.subscribe(event, handler);
	};

	UserModel.prototype.unsubscribe = function(event, handler)
	{
		this.eventEmitter.unsubscribe(event, handler);
	};

	var UserRegistry = function(config)
	{
		/** @var {UserModel[]} this.users */
		this.users = BX.prop.getArray(config, "users", []);

		this.eventEmitter = new BX.Event.EventEmitter(this, 'UserRegistry');
		this._sort();
	};

	UserRegistry.prototype.subscribe = function(eventName, handler)
	{
		this.eventEmitter.subscribe(eventName, handler);
	};

	/**
	 *
	 * @param {int} userId
	 * @returns {UserModel|null}
	 */
	UserRegistry.prototype.get = function(userId)
	{
		for (var i = 0; i < this.users.length; i++)
		{
			if (this.users[i].id == userId)
			{
				return this.users[i];
			}
		}
		return null;
	};

	UserRegistry.prototype.push = function(user)
	{
		if (!(user instanceof UserModel))
		{
			throw Error("user should be instance of UserModel")
		}

		this.users.push(user);
		this._sort();
		user.subscribe("changed", this._onUserChanged.bind(this));
		this.eventEmitter.emit("userAdded", {
			user: user
		})
	};

	UserRegistry.prototype._onUserChanged = function(event)
	{
		if (event.data.fieldName === 'order')
		{
			this._sort();
		}
		this.eventEmitter.emit("userChanged", event.data)
	};

	UserRegistry.prototype._sort = function()
	{
		this.users = this.users.sort(function(a, b)
		{
			return a.order - b.order;
		});
	};

	function createSVG(elementName, config)
	{
		var element = document.createElementNS('http://www.w3.org/2000/svg', elementName);

		if ("attrNS" in config && BX.type.isObject(config.attrNS))
		{
			for (var key in config.attrNS)
			{
				if (config.attrNS.hasOwnProperty(key))
				{
					element.setAttributeNS(null, key, config.attrNS[key]);
				}
			}
		}

		BX.adjust(element, config);
		return element;
	}

	function logPlaybackError(error)
	{
		console.error("Playback start error: ", error);
	}

	BX.Call.View.Layout = Layouts;

	BX.Call.View.Size = {
		Folded: 'folded',
		Full: 'full'
	};

	BX.Call.View.RecordState = {
		Started: 'started',
		Resumed: 'resumed',
		Paused: 'paused',
		Stopped: 'stopped'
	};

	BX.Call.View.RecordType = {
		None: 'none',
		Video: 'video',
		Audio: 'audio',
	};

	BX.Call.View.RecordSource = {
		Chat: 'BXCLIENT_CHAT'
	};

	BX.Call.View.UiState = UiState;
	BX.Call.View.Event = EventName;
	BX.Call.View.RoomState = RoomState;

	BX.Call.View.DeviceSelector = DeviceSelector;
	BX.Call.View.NotificationManager = NotificationManager;

	Object.defineProperty(BX.Call.View, 'MIN_WIDTH', {
		value: MIN_WIDTH,
		writable: false
	});
})();
