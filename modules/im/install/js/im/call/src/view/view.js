import {Browser, Dom, Runtime, Text, Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';
import {Popup} from 'main.popup';

import {UserModel, UserRegistry} from './user-registry'
import * as Buttons from './buttons';
import {BackgroundDialog} from '../dialogs/background_dialog';
import {CallUserMobile, MobileMenu, MobileSlider, UserSelectorMobile} from './mobile';
import {CallUser} from './call-user';
import {Hardware} from '../hardware';
import {FloorRequest} from './floor-request';
import {NotificationManager} from './notifications';
import {DeviceSelector} from './device-selector';
import {EndpointDirection, UserState} from '../engine/engine';
import Util from '../util';
import {UserSelector} from './user-selector';

import '../css/view.css';

const Layouts = {
	Grid: 1,
	Centered: 2,
	Mobile: 3
};

const UiState = {
	Preparing: 1,
	Initializing: 2,
	Calling: 3,
	Connected: 4,
	Error: 5
};

const Size = {
	Folded: 'folded',
	Full: 'full'
};

const RecordState = {
	Started: 'started',
	Resumed: 'resumed',
	Paused: 'paused',
	Stopped: 'stopped'
};

const RecordType = {
	None: 'none',
	Video: 'video',
	Audio: 'audio',
};

const RoomState = {
	None: 1,
	Speaker: 2,
	NonSpeaker: 3,
}

const EventName = {
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
	onOpenAdvancedSettings: 'onOpenAdvancedSettings',
};

const newUserPosition = 999;
const localUserPosition = 1000;
const addButtonPosition = 1001;

const MIN_WIDTH = 250;

const SIDE_USER_WIDTH = 160; // keep in sync with .bx-messenger-videocall-user-block .bx-messenger-videocall-user width
const SIDE_USER_HEIGHT = 90; // keep in sync with .bx-messenger-videocall-user height

const MAX_USERS_PER_PAGE = 15;
const MIN_GRID_USER_WIDTH = 249;
const MIN_GRID_USER_HEIGHT = 140;

type ViewOptions = {
	title: ?string,
	container: HTMLElement,
	baseZIndex: number,
	cameraId: string,
	microphoneId: string,
	showChatButtons: boolean,
	showUsersButton: boolean,
	showShareButton: boolean,
	showRecordButton: boolean,
	showDocumentButton: boolean,
	showButtonPanel: boolean,
	broadcastingMode: boolean,
	broadcastingPresenters: number[],
	language: string,
	userData: {},
	userLimit: number,
	isIntranetOrExtranet: boolean,
	mediaSelectionBlocked: boolean,
	blockedButtons: string[],
	hiddenButtons: string[],
	hiddenTopButtons: string[],
	uiState: string,
	layout: string,
	userStates: {},
}

export class View
{
	centralUser: CallUser
	callMenu: ?MobileMenu
	participantsMenu: ?MobileMenu
	pinnedUser: ?CallUser
	userMenu: ?MobileMenu
	userRegistry: UserRegistry
	users: { [key: number]: CallUser; }
	screenUsers: { [key: number]: CallUser; }

	constructor(config: ViewOptions)
	{
		this.title = config.title;
		this.container = config.container;
		this.baseZIndex = config.baseZIndex;
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
		if (config.userData)
		{
			this.updateUserData(config.userData);
		}
		this.userLimit = config.userLimit || 1;
		this.userId = BX.message('USER_ID');
		this.isIntranetOrExtranet = BX.prop.getBoolean(config, "isIntranetOrExtranet", true);
		this.users = {}; // Call participants. The key is the user id.
		this.screenUsers = {}; // Screen sharing participants. The key is the user id.
		this.userRegistry = new UserRegistry();

		let localUserModel = new UserModel({
			id: this.userId,
			state: BX.prop.getString(config, "localUserState", UserState.Connected),
			localUser: true,
			order: localUserPosition,
			name: this.userData[this.userId] ? this.userData[this.userId].name : '',
			avatar: this.userData[this.userId] ? this.userData[this.userId].avatar_hr : '',
		});
		this.userRegistry.push(localUserModel);

		this.localUser = new CallUser({
			parentContainer: this.container,
			userModel: localUserModel,
			allowBackgroundItem: BackgroundDialog.isAvailable() && this.isIntranetOrExtranet,
			allowMaskItem: BackgroundDialog.isMaskAvailable() && this.isIntranetOrExtranet,
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

		this.size = Size.Full;
		this.maxWidth = null;
		this.isMuted = false;
		this.isCameraOn = false;
		this.isFullScreen = false;
		this.isUserBlockFolded = false;

		this.recordState = this.getDefaultRecordState();

		this.blockedButtons = {};
		let configBlockedButtons = BX.prop.getArray(config, "blockedButtons", []);
		configBlockedButtons.forEach(buttonCode => this.blockedButtons[buttonCode] = true);

		this.hiddenButtons = {};
		this.overflownButtons = {};
		if (!this.showUsersButton)
		{
			this.hiddenButtons['users'] = true;
		}
		let configHiddenButtons = BX.prop.getArray(config, "hiddenButtons", []);
		configHiddenButtons.forEach(buttonCode => this.hiddenButtons[buttonCode] = true);

		this.hiddenTopButtons = {};
		let configHiddenTopButtons = BX.prop.getArray(config, "hiddenTopButtons", []);
		configHiddenTopButtons.forEach(buttonCode => this.hiddenTopButtons[buttonCode] = true);

		this.uiState = config.uiState || UiState.Calling;
		this.layout = config.layout || Layouts.Centered;
		this.roomState = RoomState.None;

		this.eventEmitter = new EventEmitter(this, 'BX.Call.View');

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
			all: Util.isDesktop(),
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
		if (Type.isPlainObject(config.userStates))
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

	init()
	{
		if (this.isFullScreenSupported())
		{
			if (Browser.isChrome() || Browser.isSafari())
			{
				window.addEventListener("fullscreenchange", this._onFullScreenChangeHandler);
				window.addEventListener("webkitfullscreenchange", this._onFullScreenChangeHandler);
			}
			else if (Browser.isFirefox())
			{
				window.addEventListener("mozfullscreenchange", this._onFullScreenChangeHandler);
			}
		}
		if (Browser.isMobile())
		{
			document.documentElement.style.setProperty('--view-height', window.innerHeight + 'px');
			window.addEventListener("orientationchange", this._onOrientationChangeHandler);
		}

		this.elements.audioContainer = Dom.create("div", {
			props: {className: "bx-messenger-videocall-audio-container"}
		});

		if (Hardware.initialized)
		{
			this.setSpeakerId(Hardware.defaultSpeaker);
		}
		else
		{
			Hardware.subscribe(Hardware.Events.initialized, function ()
			{
				this.setSpeakerId(Hardware.defaultSpeaker);
			}.bind(this))
		}

		window.addEventListener("keydown", this._onKeyDownHandler);
		window.addEventListener("keyup", this._onKeyUpHandler);

		if (Browser.isMac())
		{
			this.keyModifier = '&#8984; + Shift';
		}
		else
		{
			this.keyModifier = 'Ctrl + Shift';
		}

		this.container.appendChild(this.elements.audioContainer);
	};

	subscribeEvents(config)
	{
		for (let event in EventName)
		{
			if (EventName.hasOwnProperty(event) && Type.isFunction(config[event]))
			{
				this.setCallback(event, config[event]);
			}
		}
	};

	setCallback(name, cb)
	{
		if (Type.isFunction(cb) && EventName.hasOwnProperty(name))
		{
			this.eventEmitter.subscribe(name, function (event)
			{
				cb(event.data);
			});
		}
	};

	subscribe(eventName, listener)
	{
		return this.eventEmitter.subscribe(eventName, listener);
	};

	unsubscribe(eventName, listener)
	{
		return this.eventEmitter.unsubscribe(eventName, listener);
	};

	getNextPosition()
	{
		return this.lastPosition++;
	};

	/**
	 * @param {object} userStates {userId -> state}
	 */
	appendUsers(userStates)
	{
		if (!Type.isPlainObject(userStates))
		{
			return;
		}

		let userIds = Object.keys(userStates);
		for (let i = 0; i < userIds.length; i++)
		{
			let userId = userIds[i];
			this.addUser(userId, userStates[userId] ? userStates[userId] : UserState.Idle);
		}
	};

	setCentralUser(userId)
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

		const previousCentralUser = this.centralUser;
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
					onClick: () => this.showUserMenu(this.centralUser.id)
				})
				this.centralUserMobile.mount(this.elements.pinnedUserContainer);
			}
		}
		this.userRegistry.users.forEach(userModel => userModel.centralUser = (userModel.id == userId));
		this.eventEmitter.emit(EventName.onSetCentralUser, {
			userId: userId,
			stream: userId == this.userId ? this.localUser.stream : this.users[userId].stream
		})
	};

	getLeftUser(userId)
	{
		let candidateUserId = null;
		for (let i = 0; i < this.userRegistry.users.length; i++)
		{
			const userModel = this.userRegistry.users[i];
			if (userModel.id == userId && candidateUserId)
			{
				return candidateUserId
			}

			if (!userModel.localUser && userModel.state == UserState.Connected)
			{
				candidateUserId = userModel.id
			}
		}

		return candidateUserId;
	};

	getRightUser(userId)
	{
		let candidateUserId = null;
		for (let i = this.userRegistry.users.length - 1; i >= 0; i--)
		{
			const userModel = this.userRegistry.users[i];
			if (userModel.id == userId && candidateUserId)
			{
				return candidateUserId
			}

			if (!userModel.localUser && userModel.state == UserState.Connected)
			{
				candidateUserId = userModel.id
			}
		}

		return candidateUserId;
	};

	getUserCount()
	{
		return Object.keys(this.users).length;
	};

	getConnectedUserCount(withYou)
	{
		let count = this.getConnectedUsers().length;

		if (withYou)
		{
			const userId = parseInt(this.userId);
			if (!this.broadcastingMode || this.broadcastingPresenters.includes(userId))
			{
				count += 1;
			}
		}

		return count;
	};

	getUsersWithVideo()
	{
		let result = [];

		for (let userId in this.users)
		{
			if (this.users[userId].hasVideo())
			{
				result.push(userId);
			}
		}
		return result;
	};

	getConnectedUsers()
	{
		let result = [];
		for (let i = 0; i < this.userRegistry.users.length; i++)
		{
			const userModel = this.userRegistry.users[i];
			if (userModel.id != this.userId && userModel.state == UserState.Connected)
			{
				result.push(userModel.id);
			}
		}
		return result;
	};

	getDisplayedUsers()
	{
		let result = [];
		for (let i = 0; i < this.userRegistry.users.length; i++)
		{
			const userModel = this.userRegistry.users[i];
			if (userModel.id != this.userId && (userModel.state == UserState.Connected || userModel.state == UserState.Connecting))
			{
				result.push(userModel.id);
			}
		}
		return result;
	};

	hasUserWithScreenSharing()
	{
		return this.userRegistry.users.some(function (userModel)
		{
			return userModel.screenState;
		})
	};

	getPresenterUserId()
	{
		let currentPresenterId = this.presenterId || 0;
		if (currentPresenterId == this.localUser.id)
		{
			currentPresenterId = 0;
		}
		let userId; // for usage in iterators

		let currentPresenterModel = this.userRegistry.get(currentPresenterId);

		// 1. Current user, who is sharing screen has top priority
		if (currentPresenterModel && currentPresenterModel.screenState === true)
		{
			return currentPresenterId;
		}

		// 2. If current user is not sharing screen, but someone is sharing - he should become presenter
		for (userId in this.users)
		{
			if (this.users.hasOwnProperty(userId) && this.userRegistry.get(userId).screenState === true)
			{
				return parseInt(userId);
			}
		}

		// 3. If current user is talking, or stopped talking less then one second ago - he should stay presenter
		if (currentPresenterModel && currentPresenterModel.wasTalkingAgo() < 1000)
		{
			return currentPresenterId;
		}

		// 4. Return currently talking user
		let minTalkingAgo = 0;
		let minTalkingAgoUserId = 0;
		for (userId in this.users)
		{
			if (!this.users.hasOwnProperty(userId))
			{
				continue;
			}
			const userWasTalkingAgo = this.userRegistry.get(userId).wasTalkingAgo();
			if (userWasTalkingAgo < 1000)
			{
				return parseInt(userId);
			}
			if (userWasTalkingAgo < minTalkingAgo)
			{
				minTalkingAgoUserId = parseInt(userId);
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

	switchPresenter()
	{
		const newPresenterId = this.getPresenterUserId();

		if (!newPresenterId)
		{
			return;
		}

		this.presenterId = newPresenterId;
		this.userRegistry.users.forEach(userModel => userModel.presenter = (userModel.id == this.presenterId));

		if (this.pinnedUser === null)
		{
			this.setCentralUser(newPresenterId);
		}

		if (this.layout == Layouts.Grid)
		{
			const presentersPage = this.findUsersPage(this.presenterId);
			if (presentersPage)
			{
				this.setCurrentPage(presentersPage);
			}
		}
	};

	switchPresenterDeferred()
	{
		clearTimeout(this.switchPresenterTimeout);
		this.switchPresenterTimeout = setTimeout(this.switchPresenter.bind(this), 1000);
	};

	cancelSwitchPresenter()
	{
		clearTimeout(this.switchPresenterTimeout);
	};

	setUiState(uiState)
	{
		if (this.uiState == uiState)
		{
			return;
		}

		this.uiState = uiState;
		if (this.uiState == UiState.Error && this.elements.container)
		{
			Dom.clean(this.elements.container);
			this.elements.container.appendChild(this.elements.overlay);
		}
		if (!this.elements.root)
		{
			return;
		}
		this.updateButtons();
		this.elements.wrap.classList.toggle("with-clouds", (this.uiState == UiState.Preparing));
	};

	setLayout(newLayout)
	{
		if (newLayout == this.layout)
		{
			return;
		}

		this.layout = newLayout;

		if (this.layout == Layouts.Centered || this.layout == Layouts.Mobile)
		{
			this.elements.root.classList.remove("bx-messenger-videocall-grid");
			this.elements.root.classList.add("bx-messenger-videocall-centered");
			this.centralUser.mount(this.elements.center);
			this.elements.container.appendChild(this.elements.userBlock);

			if (this.layout != Layouts.Mobile)
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
					foldButtonState: Buttons.ParticipantsButton.FoldButtonState.Hidden
				});
			}
			this.unpinUser();
		}
		if (this.layout == Layouts.Centered && this.isFullScreen)
		{
			this.setUserBlockFolded(true);
		}

		this.elements.root.classList.toggle("bx-messenger-videocall-fullscreen-mobile", (this.layout == Layouts.Mobile));

		this.renderUserList();
		this.toggleEars();
		this.updateButtons();
		this.eventEmitter.emit(EventName.onLayoutChange, {
			layout: this.layout
		});
	};

	setRoomState(roomState)
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

	getMicrophoneSideIcon(roomState)
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

	setCurrentPage(pageNumber)
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

	calculateUsersPerPage()
	{
		if (!this.elements.userList)
		{
			return 1000;
		}

		const containerSize = this.elements.userList.container.getBoundingClientRect();
		let columns = Math.floor(containerSize.width / MIN_GRID_USER_WIDTH) || 1;
		let rows = Math.floor(containerSize.height / MIN_GRID_USER_HEIGHT) || 1;
		const usersPerPage = columns * rows - 1;

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
			let elementSize = Util.findBestElementSize(
				containerSize.width,
				containerSize.height,
				MAX_USERS_PER_PAGE + 1,
				MIN_GRID_USER_WIDTH,
				MIN_GRID_USER_HEIGHT
			);
			// console.log('Optimal element size: width '+elementSize.width+' height '+elementSize.height);
			columns = Math.floor(containerSize.width / elementSize.width);
			rows = Math.floor(containerSize.height / elementSize.height);
			return columns * rows - 1;
		}
	};

	calculatePagesCount(usersPerPage)
	{
		const pages = Math.ceil((this.getDisplayedUsers().length) / usersPerPage);
		return pages > 0 ? pages : 1;
	};

	recalculatePages()
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
	findUsersPage(userId)
	{
		if (userId == this.userId || this.usersPerPage === 0)
		{
			return 0;
		}
		const displayedUsers = this.getDisplayedUsers();
		let userPosition = 0;

		for (let i = 0; i < displayedUsers.length; i++)
		{
			if (displayedUsers[i] == userId)
			{
				userPosition = i + 1;
				break;
			}
		}

		return (userPosition ? Math.ceil(userPosition / this.usersPerPage) : 0);
	};

	setCameraId(cameraId)
	{
		if (this.cameraId == cameraId)
		{
			return;
		}

		if (this.localUser.stream && this.localUser.stream.getVideoTracks().length > 0)
		{
			throw new Error("Can not set camera id while having active stream")
		}
		this.cameraId = cameraId;
	};

	setMicrophoneId(microphoneId)
	{
		if (this.microphoneId == microphoneId)
		{
			return;
		}

		if (this.localUser.stream && this.localUser.stream.getAudioTracks().length > 0)
		{
			throw new Error("Can not set microphone id while having active stream")
		}
		this.microphoneId = microphoneId;
	};

	setMicrophoneLevel(level)
	{
		this.microphoneLevel = level;
		this.buttons.microphone?.setLevel(level);
	};

	setCameraState(newCameraState)
	{
		newCameraState = !!newCameraState;
		if (this.isCameraOn == newCameraState)
		{
			return;
		}

		this.isCameraOn = newCameraState;

		if (this.buttons.camera)
		{
			if (this.isCameraOn)
			{
				this.buttons.camera.enable();
			}
			else
			{
				this.buttons.camera.disable();
			}
		}
	};

	setMuted(isMuted)
	{
		isMuted = !!isMuted;
		if (this.isMuted == isMuted)
		{
			return;
		}

		this.isMuted = isMuted;
		if (this.buttons.microphone)
		{
			if (this.isMuted)
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

	setLocalUserId(userId)
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

	setUserBlockFolded(isUserBlockFolded)
	{
		this.isUserBlockFolded = isUserBlockFolded;

		this.elements.userBlock?.classList.toggle("folded", this.isUserBlockFolded);
		this.elements.root?.classList.toggle("bx-messenger-videocall-userblock-folded", this.isUserBlockFolded);
		if (this.isUserBlockFolded)
		{
			if (this.buttons.participants && this.layout == Layouts.Centered)
			{
				this.buttons.participants.update({
					foldButtonState: Buttons.ParticipantsButton.FoldButtonState.Unfold
				});
			}
		}
		else
		{
			if (this.buttons.participants)
			{
				this.buttons.participants.update({
					foldButtonState: (this.isFullScreen && this.layout == Layouts.Centered) ? Buttons.ParticipantsButton.FoldButtonState.Fold : Buttons.ParticipantsButton.FoldButtonState.Hidden
				});
			}
		}
	};

	addUser(userId, state, direction)
	{
		userId = Number(userId);
		if (this.users[userId])
		{
			return;
		}

		state = state || UserState.Idle;
		if (!direction)
		{
			if (this.broadcastingPresenters.length > 0 && !this.broadcastingPresenters.includes(userId))
			{
				direction = EndpointDirection.RecvOnly;
			}
			else
			{
				direction = EndpointDirection.SendRecv
			}
		}

		let userModel = new UserModel({
			id: userId,
			name: this.userData[userId] ? this.userData[userId].name : '',
			avatar: this.userData[userId] ? this.userData[userId].avatar_hr : '',
			state: state,
			order: state == UserState.Connected ? this.getNextPosition() : newUserPosition,
			direction: direction
		});

		this.userRegistry.push(userModel);

		if (!this.elements.audio[userId])
		{
			this.elements.audio[userId] = Dom.create("audio");
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

	setUserDirection(userId, direction)
	{
		const user = this.userRegistry.get(userId);
		if (!user || user.direction == direction)
		{
			return;
		}

		user.direction = direction;
		this.updateUserList();
	};

	setLocalUserDirection(direction)
	{
		if (this.localUser.userModel.direction != direction)
		{
			this.localUser.userModel.direction = direction;
			this.updateUserList();
		}
	};

	setUserState(userId, newState)
	{
		const user = this.userRegistry.get(userId);
		if (!user)
		{
			return;
		}

		if (newState === UserState.Connected && this.uiState === UiState.Calling)
		{
			this.setUiState(UiState.Connected);
		}

		user.state = newState;

		// maybe switch central user
		if (this.centralUser.id == this.userId && newState == UserState.Connected)
		{
			this.setCentralUser(userId);
		}
		else if (userId == this.centralUser.id)
		{
			if (newState == UserState.Connecting || newState == UserState.Failed)
			{
				this.centralUser.blurVideo();
			}
			else if (newState == UserState.Connected)
			{
				this.centralUser.blurVideo(false);
			}
			else if (newState == UserState.Idle)
			{
				const usersWithVideo = this.getUsersWithVideo();
				const connectedUsers = this.getConnectedUsers();

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

		if (newState == UserState.Connected && user.order == newUserPosition)
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

	setTitle(title)
	{
		this.title = title;
	};

	getUserTalking(userId)
	{
		const user = this.userRegistry.get(userId);
		if (!user)
		{
			return false;
		}

		return !!user.talking;
	}

	setUserTalking(userId, talking)
	{
		const user = this.userRegistry.get(userId);
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

	setUserMicrophoneState(userId, isMicrophoneOn)
	{
		const user = this.userRegistry.get(userId);
		if (user)
		{
			user.microphoneState = isMicrophoneOn;
		}
	};

	setUserCameraState(userId, cameraState)
	{
		const user = this.userRegistry.get(userId);
		if (user)
		{
			user.cameraState = cameraState;
		}
	};

	setUserVideoPaused(userId, videoPaused)
	{
		const user = this.userRegistry.get(userId);
		if (user)
		{
			user.videoPaused = videoPaused;
		}
	};

	getUserFloorRequestState(userId)
	{
		const user = this.userRegistry.get(userId);

		return user && user.floorRequestState;
	};

	setUserFloorRequestState(userId, userFloorRequestState)
	{
		const user: UserModel = this.userRegistry.get(userId);
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

	pinUser(userId)
	{
		if (!(userId in this.users))
		{
			console.error("User " + userId + " is not known");
			return;
		}
		this.pinnedUser = this.users[userId];
		this.userRegistry.users.forEach(userModel => userModel.pinned = userModel.id == userId);
		this.setCentralUser(userId);
		this.eventEmitter.emit(EventName.onUserPinned, {
			userId: userId
		});
	};

	unpinUser()
	{
		this.pinnedUser = null;
		this.userRegistry.users.forEach(userModel => userModel.pinned = false);

		this.eventEmitter.emit(EventName.onUserPinned, {
			userId: null
		});
		this.switchPresenterDeferred();
	};

	showFloorRequestNotification(userId)
	{
		const userModel: ?UserModel = this.userRegistry.get(userId);
		if (!userModel)
		{
			return;
		}
		let notification = FloorRequest.create({
			userModel: userModel
		});

		notification.mount(this.elements.notificationPanel);
		NotificationManager.Instance.addNotification(notification);
	};

	setUserScreenState(userId, screenState)
	{
		const user: ?UserModel = this.userRegistry.get(userId);
		if (!user)
		{
			return;
		}

		user.screenState = screenState;
		if (userId != this.userId)
		{
			if (screenState === true && this.layout === View.Layout.Grid)
			{
				this.setLayout(Layouts.Centered);
				this.returnToGridAfterScreenStopped = true;
			}
			if (screenState === false
				&& this.layout === Layouts.Centered
				&& !this.hasUserWithScreenSharing()
				&& !this.pinnedUser
				&& this.returnToGridAfterScreenStopped)
			{
				this.returnToGridAfterScreenStopped = false;
				this.setLayout(Layouts.Grid);
			}
			this.switchPresenter();
		}
	};

	flipLocalVideo(flipVideo)
	{
		this.localUser.flipVideo = !!flipVideo;
	}

	setLocalStream(mediaStream: MediaStream, flipVideo: ?boolean)
	{
		this.localUser.videoTrack = mediaStream.getVideoTracks().length > 0 ? mediaStream.getVideoTracks()[0] : null;
		if (!Type.isUndefined(flipVideo))
		{
			this.flipLocalVideo(flipVideo);
		}
		this.setCameraState(this.localUser.hasVideo());
		this.localUser.userModel.cameraState = this.localUser.hasVideo();

		const videoTracks = mediaStream.getVideoTracks();
		if (videoTracks.length > 0)
		{
			const videoTrackSettings = videoTracks[0].getSettings();
			this.cameraId = videoTrackSettings.deviceId || '';
		}
		else
		{
			this.cameraId = '';
		}

		const audioTracks = mediaStream.getAudioTracks();
		if (audioTracks.length > 0)
		{
			const audioTrackSettings = audioTracks[0].getSettings();
			this.microphoneId = audioTrackSettings.deviceId || '';
		}

		/*if(!this.localUser.hasVideo())
		{
			return false;
		}*/

		if (this.layout !== Layouts.Grid && this.centralUser.id == this.userId)
		{
			if (videoTracks.length > 0 || Object.keys(this.users).length === 0)
			{
				this.centralUser.videoTrack = videoTracks[0];
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

	setSpeakerId(speakerId)
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
		for (let userId in this.elements.audio)
		{
			this.elements.audio[userId].setSinkId(this.speakerId);
		}
	};

	muteSpeaker(mute)
	{
		this.speakerMuted = !!mute;

		for (let userId in this.elements.audio)
		{
			this.elements.audio[userId].volume = this.speakerMuted ? 0 : 1;
		}

		if (!this.buttons.speaker)
		{
			return;
		}

		if (this.speakerMuted)
		{
			this.buttons.speaker.disable();
			this.buttons.speaker.hideArrow()
		}
		else
		{
			this.buttons.speaker.enable();
			if (Hardware.canSelectSpeaker())
			{
				this.buttons.speaker.showArrow()
			}
		}
	};

	setVideoRenderer(userId, mediaRenderer)
	{
		if (!this.users[userId])
		{
			throw Error("User " + userId + " is not a part of this call");
		}
		if (mediaRenderer === null)
		{
			this.users[userId].videoRenderer = null;
			return;
		}

		if (!("render" in mediaRenderer) || !Type.isFunction(mediaRenderer.render))
		{
			throw Error("mediaRenderer should have method render");
		}
		if (!("kind" in mediaRenderer) || (mediaRenderer.kind !== "video" && mediaRenderer.kind !== "sharing"))
		{
			throw Error("mediaRenderer should be of video kind");
		}

		this.users[userId].videoRenderer = mediaRenderer;
	};

	setUserMedia(userId, kind, track)
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

	applyIncomingVideoConstraints()
	{
		let userId;
		let user: CallUser;
		if (this.layout === Layouts.Grid)
		{
			for (userId in this.users)
			{
				user = this.users[userId];
				user.setIncomingVideoConstraints(this.userSize.width, this.userSize.height);
			}
		}
		else if (this.layout === Layouts.Centered)
		{
			for (userId in this.users)
			{
				user = this.users[userId];
				if (userId == this.centralUser.id)
				{
					const containerSize = this.elements.center.getBoundingClientRect();
					user.setIncomingVideoConstraints(Math.floor(containerSize.width), Math.floor(containerSize.height));
				}
				else
				{
					user.setIncomingVideoConstraints(SIDE_USER_WIDTH, SIDE_USER_HEIGHT);
				}
			}
		}
	};

	getDefaultRecordState()
	{
		return {
			state: RecordState.Stopped,
			userId: 0,
			date: {
				start: null,
				pause: []
			},
		};
	};

	setRecordState(recordState)
	{
		this.recordState = recordState;
		if (this.buttons.recordStatus)
		{
			this.buttons.recordStatus.update(this.recordState);
		}

		if (this.recordState.userId != this.userId)
		{
			if (this.recordState.state === RecordState.Stopped)
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
			if (this.recordState.state === RecordState.Stopped)
			{
				delete (this.elements.topPanel.dataset.recordState);
			}
			else
			{
				this.elements.topPanel.dataset.recordState = recordState.state;
			}
		}
	};

	show()
	{
		if (!this.elements.root)
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

	hide()
	{
		if (this.overflownButtonsPopup)
		{
			this.overflownButtonsPopup.close();
		}
		Dom.remove(this.elements.root);
		this.visible = false;
	};

	startIntersectionObserver()
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
	observeIntersections(callUser)
	{
		if (this.intersectionObserver && callUser.elements.root)
		{
			this.intersectionObserver.observe(callUser.elements.root);
		}
	};

	/**
	 * @param {CallUser} callUser
	 */
	unobserveIntersections(callUser)
	{
		if (this.intersectionObserver && callUser.elements.root)
		{
			this.intersectionObserver.unobserve(callUser.elements.root);
		}
	};

	showDeviceSelector(bindElement)
	{
		if (this.deviceSelector)
		{
			return;
		}

		this.deviceSelector = new DeviceSelector({
			viewElement: this.container,
			parentElement: bindElement,
			zIndex: this.baseZIndex + 500,
			microphoneEnabled: !this.isMuted,
			microphoneId: this.microphoneId || Hardware.defaultMicrophone,
			cameraEnabled: this.isCameraOn,
			cameraId: this.cameraId,
			speakerEnabled: !this.speakerMuted,
			speakerId: this.speakerId,
			allowHdVideo: Hardware.preferHdQuality,
			faceImproveEnabled: Util.isDesktop() && typeof (BX.desktop) !== 'undefined' && BX.desktop.cameraSmoothingStatus(),
			allowFaceImprove: Util.isDesktop() && typeof (BX.desktop) !== 'undefined' && BX.desktop.enableInVersion(64),
			allowBackground: BackgroundDialog.isAvailable() && this.isIntranetOrExtranet,
			allowMask: BackgroundDialog.isMaskAvailable() && this.isIntranetOrExtranet,
			allowAdvancedSettings: typeof (BXIM) !== 'undefined' && this.isIntranetOrExtranet,
			showCameraBlock: !this.isButtonBlocked('camera'),
			events: {
				[DeviceSelector.Events.onMicrophoneSelect]: this._onMicrophoneSelected.bind(this),
				[DeviceSelector.Events.onMicrophoneSwitch]: this._onMicrophoneButtonClick.bind(this),
				[DeviceSelector.Events.onCameraSelect]: this._onCameraSelected.bind(this),
				[DeviceSelector.Events.onCameraSwitch]: this._onCameraButtonClick.bind(this),
				[DeviceSelector.Events.onSpeakerSelect]: this._onSpeakerSelected.bind(this),
				[DeviceSelector.Events.onSpeakerSwitch]: this._onSpeakerButtonClick.bind(this),
				[DeviceSelector.Events.onChangeHdVideo]: this._onChangeHdVideo.bind(this),
				[DeviceSelector.Events.onChangeMicAutoParams]: this._onChangeMicAutoParams.bind(this),
				[DeviceSelector.Events.onChangeFaceImprove]: this._onChangeFaceImprove.bind(this),
				[DeviceSelector.Events.onAdvancedSettingsClick]: () => this.eventEmitter.emit(EventName.onOpenAdvancedSettings),
				[DeviceSelector.Events.onDestroy]: () => this.deviceSelector = null,
				[DeviceSelector.Events.onShow]: () => this.eventEmitter.emit(EventName.onDeviceSelectorShow, {})
			}
		});
		this.deviceSelector.show();
	};

	showCallMenu()
	{
		let menuItems = [
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
					onClick: () =>
					{
						this.callMenu.close();
						setTimeout(this.showRenameSlider.bind(this), 100);
					}
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
			onClose: () => this.callMenu.destroy(),
			onDestroy: () => this.callMenu = null,
		});

		this.callMenu.show();
	};

	showUserMenu(userId)
	{
		const userModel: ?UserModel = this.userRegistry.get(userId);
		if (!userModel)
		{
			return false;
		}

		let pinItem = null;
		if (this.pinnedUser && this.pinnedUser.id == userId)
		{
			pinItem = {
				text: BX.message("IM_M_CALL_MOBILE_MENU_UNPIN"),
				iconClass: "unpin",
				onClick: () =>
				{
					this.userMenu.close();
					this.unpinUser();
				}
			};
		}
		else if (this.userId != userId)
		{
			pinItem = {
				text: BX.message("IM_M_CALL_MOBILE_MENU_PIN"),
				iconClass: "pin",
				onClick: () =>
				{
					this.userMenu.close();
					this.pinUser(userId);
				}
			};
		}

		let menuItems = [
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
					onClick: () =>
					{
						this.userMenu.close();
						setTimeout(this.showRenameSlider.bind(this), 100)
					}
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
				onClick: () => this.userMenu.close(),
			}
		];

		this.userMenu = new MobileMenu({
			parent: this.elements.root,
			items: menuItems,
			onClose: () => this.userMenu.destroy(),
			onDestroy: () => this.userMenu = null,
		});
		this.userMenu.show();
	};

	showParticipantsMenu()
	{
		if (this.participantsMenu)
		{
			return;
		}
		let menuItems = [];
		menuItems.push({
			userModel: this.localUser.userModel,
			showSubMenu: true,
			onClick: function ()
			{
				this.participantsMenu.close();
				this.showUserMenu(this.localUser.userModel.id);
			}.bind(this)
		});
		this.userRegistry.users.forEach((userModel: UserModel) =>
		{
			if (userModel.localUser || userModel.state != UserState.Connected)
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
				onClick: () =>
				{
					this.participantsMenu.close();
					this.showUserMenu(userModel.id);
				}
			})
		});

		if (menuItems.length === 0)
		{
			return false;
		}

		this.participantsMenu = new MobileMenu({
			parent: this.elements.root,
			items: menuItems,
			header: BX.message("IM_M_CALL_PARTICIPANTS").replace("#COUNT#", this.getConnectedUserCount(true)),
			largeIcons: true,

			onClose: function ()
			{
				this.participantsMenu.destroy();
			}.bind(this),
			onDestroy: function ()
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
	showMessage(params)
	{
		if (!this.elements.root)
		{
			this.render();
			this.container.appendChild(this.elements.root);
		}
		const statusNode = Dom.create("div", {
			props: {className: "bx-messenger-videocall-user-status bx-messenger-videocall-user-status-wide"},
		});

		if (Type.isStringFilled(params.text))
		{
			const textNode = Dom.create("div", {
				props: {className: "bx-messenger-videocall-status-text"},
				text: params.text
			});
			statusNode.appendChild(textNode);
		}

		if (this.elements.overlay.childElementCount)
		{
			Dom.clean(this.elements.overlay);
		}
		this.elements.overlay.appendChild(statusNode);
	};

	hideMessage()
	{
		this.elements.overlay.textContent = '';
	};

	/**
	 * @param {Object} params
	 * @param {string} params.text
	 * @param {string} [params.subText]
	 */
	showFatalError(params)
	{
		this.showMessage(params);
		this.setUiState(UiState.Error);
	};

	close()
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

	setSize(size)
	{
		if (this.size == size)
		{
			return;
		}

		this.size = size;

		if (this.size == Size.Folded)
		{
			if (this.overflownButtonsPopup)
			{
				this.overflownButtonsPopup.close();
			}
			if (this.elements.panel)
			{
				this.elements.panel.classList.add('bx-messenger-videocall-panel-folded');
			}
			Dom.remove(this.elements.container);
			Dom.remove(this.elements.topPanel);
			this.elements.root.style.removeProperty('max-width');
			this.updateButtons();
		}
		else
		{
			if (this.elements.panel)
			{
				this.elements.panel.classList.remove('bx-messenger-videocall-panel-folded');
			}
			this.elements.wrap.appendChild(this.elements.topPanel);
			this.elements.wrap.appendChild(this.elements.container);
			if (this.maxWidth > 0)
			{
				this.elements.root.style.maxWidth = Math.max(this.maxWidth, MIN_WIDTH) + 'px';
			}
			this.updateButtons();
			this.updateUserList();
			this.resumeVideo();
		}
	};

	isButtonBlocked(buttonName)
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

	isButtonHidden(buttonName)
	{
		return this.hiddenButtons[buttonName] === true;
	};

	showButton(buttonCode)
	{
		this.showButtons([buttonCode]);
	};

	hideButton(buttonCode)
	{
		this.hideButtons([buttonCode]);
	};

	/**
	 * @return {bool} Returns true if buttons update is required
	 */
	checkPanelOverflow()
	{
		const delta = this.elements.panel.scrollWidth - this.elements.panel.offsetWidth
		const mediumButtonMinWidth = 55; // todo: move to constants maybe? or maybe even calculate dynamically somehow?
		if (delta > 0)
		{
			let countOfButtonsToHide = Math.ceil(delta / mediumButtonMinWidth);
			if (Object.keys(this.overflownButtons).length === 0)
			{
				countOfButtonsToHide += 1;
			}

			const buttons = this.getButtonList();

			for (let i = buttons.length - 1; i > 0; i--)
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
			const hiddenButtonsCount = Object.keys(this.overflownButtons).length;
			if (hiddenButtonsCount > 0)
			{
				const unusedPanelSpace = this.calculateUnusedPanelSpace();
				if (unusedPanelSpace > mediumButtonMinWidth)
				{
					let countOfButtonsToShow = Math.min(Math.floor(unusedPanelSpace / mediumButtonMinWidth), hiddenButtonsCount);
					let buttonsLeftHidden = hiddenButtonsCount - countOfButtonsToShow;
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
						for (let i = 0; i < countOfButtonsToShow; i++)
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
	showButtons(buttons)
	{
		if (!Type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach((buttonName) =>
		{
			if (this.hiddenButtons.hasOwnProperty(buttonName))
			{
				delete this.hiddenButtons[buttonName];
			}
		})

		this.updateButtons();
	};

	/**
	 * @param {string[]} buttons Array of buttons names to hide
	 */
	hideButtons(buttons)
	{
		if (!Type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach((buttonName) => this.hiddenButtons[buttonName] = true);
		this.updateButtons();
	};

	blockAddUser()
	{
		this.blockedButtons['add'] = true;

		if (this.elements.userList.addButton)
		{
			Dom.remove(this.elements.userList.addButton);
			this.elements.userList.addButton = null;
		}
	};

	blockSwitchCamera()
	{
		this.blockedButtons['camera'] = true;
	};

	unblockSwitchCamera()
	{
		delete this.blockedButtons['camera'];
	};

	blockScreenSharing()
	{
		this.blockedButtons['screen'] = true;
	};

	blockHistoryButton()
	{
		this.blockedButtons['history'] = true;
	};

	/**
	 * @param {string[]} buttons Array of buttons names to block
	 */
	blockButtons(buttons)
	{
		if (!Type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach((buttonName) =>
		{
			this.blockedButtons[buttonName] = true;
			if (this.buttons[buttonName])
			{
				this.buttons[buttonName].setBlocked(true);
			}
		})
	};

	/**
	 * @param {string[]} buttons Array of buttons names to unblock
	 */
	unblockButtons(buttons)
	{
		if (!Type.isArray(buttons))
		{
			console.error("buttons should be array")
		}

		buttons.forEach((buttonName) =>
		{
			delete this.blockedButtons[buttonName];
			if (this.buttons[buttonName])
			{
				this.buttons[buttonName].setBlocked(this.isButtonBlocked(buttonName));
			}
		})
	};

	disableMediaSelection()
	{
		this.mediaSelectionBlocked = true;
	};

	enableMediaSelection()
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

	isMediaSelectionAllowed()
	{
		return this.layout != Layouts.Mobile && (this.uiState == UiState.Preparing || this.uiState == UiState.Connected) && !this.mediaSelectionBlocked && !this.isFullScreen;
	};

	getButtonList()
	{
		if (this.uiState == UiState.Error)
		{
			return ['close'];
		}
		if (this.uiState == UiState.Initializing)
		{
			return ['hangup'];
		}

		if (this.size == Size.Folded)
		{
			return ['title', 'spacer', 'returnToCall', 'hangup'];
		}

		let result = [];

		result.push('microphone');
		result.push('camera');

		if (this.layout != Layouts.Mobile)
		{
			result.push('speaker');
		}
		else
		{
			result.push('mobileMenu');
		}

		result.push('chat');
		result.push('users');

		if (this.layout != Layouts.Mobile)
		{
			result.push('floorRequest');
			result.push('screen');
			result.push('record');
			result.push('document');
		}

		result = result.filter((buttonCode) =>
		{
			return !this.hiddenButtons.hasOwnProperty(buttonCode) && !this.overflownButtons.hasOwnProperty(buttonCode);
		});

		if (Object.keys(this.overflownButtons).length > 0)
		{
			result.push('more');
		}

		if (this.uiState == UiState.Preparing)
		{
			result.push('close');
		}
		else
		{
			result.push('hangup');
		}

		return result;
	};

	getTopButtonList()
	{
		let result = [];

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

		let separatorNeeded = false;
		if (this.uiState === UiState.Connected && this.layout != Layouts.Mobile)
		{
			result.push('grid');
			separatorNeeded = true;
		}
		if (this.uiState != UiState.Preparing && this.isFullScreenSupported() && this.layout != Layouts.Mobile)
		{
			result.push('fullscreen');
			separatorNeeded = true;
		}

		if (this.uiState != UiState.Preparing)
		{
			if (separatorNeeded)
			{
				result.push('separator');
			}
			result.push('participants');
		}

		let previousButtonCode = '';
		result = result.filter((buttonCode) =>
		{
			if (previousButtonCode === 'spacer' && buttonCode === 'separator')
			{
				return true;
			}

			previousButtonCode = buttonCode;

			return !this.hiddenTopButtons.hasOwnProperty(buttonCode);
		});

		return result;
	};

	render()
	{
		this.elements.root = Dom.create("div", {
			props: {className: "bx-messenger-videocall"},
			children: [
				this.elements.wrap = Dom.create("div", {
					props: {className: "bx-messenger-videocall-wrap"},
					children: [
						this.elements.container = Dom.create("div", {
							props: {className: "bx-messenger-videocall-inner"},
							children: [
								this.elements.center = Dom.create("div", {
									props: {className: "bx-messenger-videocall-central-user"},
									events: {
										touchstart: this._onCenterTouchStart.bind(this),
										touchend: this._onCenterTouchEnd.bind(this),
									}
								}),
								this.elements.pageNavigatorLeft = Dom.create("div", {
									props: {className: "bx-messenger-videocall-page-navigator left"},
									children: [
										this.elements.pageNavigatorLeftCounter = Dom.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-counter left"},
											html: (this.currentPage - 1) + '&nbsp;/&nbsp;' + this.pagesCount
										}),
										Dom.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-icon left"}
										}),
									],
									events: {
										click: this._onLeftPageNavigatorClick.bind(this)
									}
								}),
								this.elements.pageNavigatorRight = Dom.create("div", {
									props: {className: "bx-messenger-videocall-page-navigator right"},
									children: [
										this.elements.pageNavigatorRightCounter = Dom.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-counter right"},
											html: (this.currentPage + 1) + '&nbsp;/&nbsp;' + this.pagesCount
										}),
										Dom.create("div", {
											props: {className: "bx-messenger-videocall-page-navigator-icon right"}
										})
									],
									events: {
										click: this._onRightPageNavigatorClick.bind(this)
									}
								}),
							]
						}),
						this.elements.topPanel = Dom.create("div", {
							props: {className: "bx-messenger-videocall-top-panel"},
						}),
						this.elements.notificationPanel = Dom.create("div", {
							props: {className: "bx-messenger-videocall-notification-panel"},
						}),
						this.elements.bottom = Dom.create("div", {
							props: {className: "bx-messenger-videocall-bottom"},
							children: [
								this.elements.userSelectorContainer = Dom.create("div", {
									props: {className: "bx-messenger-videocall-bottom-user-selector-container"}
								}),
								this.elements.pinnedUserContainer = Dom.create("div", {
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

		if (this.showButtonPanel)
		{
			this.elements.panel = Dom.create("div", {
				props: {className: "bx-messenger-videocall-panel"},
			});
			this.elements.bottom.appendChild(this.elements.panel);
		}
		else
		{
			this.elements.root.classList.add("bx-messenger-videocall-no-button-panel");
		}

		if (this.layout == Layouts.Mobile)
		{
			this.userSelector = new UserSelectorMobile({
				userRegistry: this.userRegistry
			});
			this.userSelector.mount(this.elements.userSelectorContainer);

			this.elements.ear.left = Dom.create("div", {
				props: {
					className: "bx-messenger-videocall-mobile-ear left"
				},
				events: {
					click: this._onLeftEarClick.bind(this)
				}
			});
			this.elements.ear.right = Dom.create("div", {
				props: {
					className: "bx-messenger-videocall-mobile-ear right"
				},
				events: {
					click: this._onRightEarClick.bind(this)
				}
			});
			this.elements.localUserMobile = Dom.create("div", {
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

		this.elements.overlay = Dom.create("div", {
			props: {className: "bx-messenger-videocall-overlay"}
		});

		this.elements.userBlock = Dom.create("div", {
			props: {className: "bx-messenger-videocall-user-block"},
			children: [
				this.elements.ear.top = Dom.create("div", {
					props: {className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-top"},
					children: [
						Dom.create("div", {
							props: {className: "bx-messenger-videocall-ear-icon"}
						})
					],
					events: {
						mouseenter: this.scrollUserListUp.bind(this),
						mouseleave: this.stopScroll.bind(this)
					}
				}),
				this.elements.ear.bottom = Dom.create("div", {
					props: {className: "bx-messenger-videocall-ear bx-messenger-videocall-ear-bottom"},
					children: [
						Dom.create("div", {
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

		this.elements.userList.container = Dom.create("div", {
			props: {
				className: "bx-messenger-videocall-user-list"
			},
			events: {
				scroll: Runtime.debounce(this.toggleEars.bind(this), 300),
				wheel: (e) => this.elements.userList.container.scrollTop += e.deltaY
			}
		});

		this.elements.userList.addButton = Dom.create("div", {
			props: {className: "bx-messenger-videocall-user-add"},
			children: [
				Dom.create("div", {
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

		if (this.layout == Layouts.Centered || this.layout == Layouts.Mobile)
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

	renderUserList()
	{
		const showLocalUser = this.shouldShowLocalUser();
		let userCount = 0;
		let skipUsers = 0;
		let skippedUsers = 0;
		let renderedUsers = 0;

		if (this.layout == Layouts.Grid && this.pagesCount > 1)
		{
			skipUsers = (this.currentPage - 1) * this.usersPerPage;
		}

		for (let i = 0; i < this.userRegistry.users.length; i++)
		{
			const userModel: UserModel = this.userRegistry.users[i];
			const userId = userModel.id;
			if (!this.users.hasOwnProperty(userId))
			{
				continue;
			}

			const user: CallUser = this.users[userId];
			const screenUser: CallUser = this.screenUsers[userId];
			if (userId == this.centralUser.id && (this.layout == Layouts.Centered || this.layout == Layouts.Mobile))
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
			const userState = userModel.state;
			let userActive = (userState != UserState.Idle
				&& userState != UserState.Declined
				&& userState != UserState.Unavailable
				&& userState != UserState.Busy
				&& userModel.direction != EndpointDirection.RecvOnly
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

			if (!userActive)
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
		if (showLocalUser)
		{
			if (this.layout == Layouts.Centered && this.userId == this.centralUser.id || this.layout == Layouts.Mobile)
			{
				// this.unobserveIntersections(this.localUser);
				this.localUser.mount(this.elements.center, true);
				this.localUser.visible = true;
			}
			else
			{
				// using force true to always move self to the end of the list
				this.localUser.mount(this.elements.userList.container);
				if (this.layout == Layouts.Centered && this.intersectionObserver)
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

		const showAdd = this.layout == Layouts.Centered && userCount > 0 /*&& !this.isFullScreen*/ && this.uiState === UiState.Connected && !this.isButtonBlocked("add") && this.getConnectedUserCount() < this.userLimit - 1;
		if (showAdd && !this.isFullScreen)
		{
			this.elements.userList.container.appendChild(this.elements.userList.addButton);
		}
		else
		{
			Dom.remove(this.elements.userList.addButton);
		}

		this.elements.root.classList.toggle("bx-messenger-videocall-user-list-empty", (this.elements.userList.container.childElementCount === 0));
		this.localUser.updatePanelDeferred();
	};

	shouldShowLocalUser()
	{
		return (
			this.localUser.userModel.state != UserState.Idle
			&& this.localUser.userModel.direction != EndpointDirection.RecvOnly
		);
	};

	updateGridUserSize(userCount)
	{
		const containerSize = this.elements.userList.container.getBoundingClientRect();
		this.userSize = Util.findBestElementSize(
			containerSize.width,
			containerSize.height,
			userCount,
			MIN_GRID_USER_WIDTH,
			MIN_GRID_USER_HEIGHT
		);

		const avatarSize = Math.round(this.userSize.height * 0.45);
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

	updateCentralUserAvatarSize()
	{
		let containerSize;
		let avatarSize;
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

	renderButtons(buttons): HTMLElement
	{
		let panelInner, left, center, right;

		panelInner = Dom.create("div", {
			props: {className: "bx-messenger-videocall-panel-inner"}
		});

		if (this.layout === Layouts.Mobile || this.size === Size.Folded)
		{
			left = panelInner;
			center = panelInner;
			right = panelInner;
		}
		else
		{
			left = Dom.create("div", {
				props: {className: "bx-messenger-videocall-panel-inner-left"},
			});
			center = Dom.create("div", {
				props: {className: "bx-messenger-videocall-panel-inner-center"},
			});
			right = Dom.create("div", {
				props: {className: "bx-messenger-videocall-panel-inner-right"},
			});
			panelInner.appendChild(left);
			panelInner.appendChild(center);
			panelInner.appendChild(right);
		}

		for (let i = 0; i < buttons.length; i++)
		{
			switch (buttons[i])
			{
				case "title":
					this.buttons.title = new Buttons.TitleButton({
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
					this.buttons.share = new Buttons.SimpleButton({
						class: "share",
						text: BX.message("IM_M_CALL_BTN_LINK"),
						onClick: this._onShareButtonClick.bind(this)
					});
					center.appendChild(this.buttons.share.render());
					break;
				case "microphone":
					this.buttons.microphone = new Buttons.DeviceButton({
						class: "microphone",
						text: BX.message("IM_M_CALL_BTN_MIC"),
						enabled: !this.isMuted,
						arrowHidden: this.layout == Layouts.Mobile,
						arrowEnabled: this.isMediaSelectionAllowed(),
						showPointer: true, //todo
						blocked: this.isButtonBlocked("microphone"),
						showLevel: true,
						sideIcon: this.getMicrophoneSideIcon(this.roomState),
						onClick: (e) =>
						{
							this._onMicrophoneButtonClick(e);
							this._showMicrophoneHint(e);
						},
						onArrowClick: this._onMicrophoneArrowClick.bind(this),
						onMouseOver: this._showMicrophoneHint.bind(this),
						onMouseOut: () => this._destroyHotKeyHint(),
						onSideIconClick: this._onMicrophoneSideIconClick.bind(this),
					});
					left.appendChild(this.buttons.microphone.render());
					break;
				case "camera":
					this.buttons.camera = new Buttons.DeviceButton({
						class: "camera",
						text: BX.message("IM_M_CALL_BTN_CAMERA"),
						enabled: this.isCameraOn,
						arrowHidden: this.layout == Layouts.Mobile,
						arrowEnabled: this.isMediaSelectionAllowed(),
						blocked: this.isButtonBlocked("camera"),
						onClick: this._onCameraButtonClick.bind(this),
						onArrowClick: this._onCameraArrowClick.bind(this),
						onMouseOver: (e) =>
						{
							this._showHotKeyHint(e.currentTarget.firstChild, "camera", this.keyModifier + " + V");
						},
						onMouseOut: () =>
						{
							this._destroyHotKeyHint();
						}
					});
					left.appendChild(this.buttons.camera.render());
					break;
				case "screen":
					if (!this.buttons.screen)
					{
						this.buttons.screen = new Buttons.SimpleButton({
							class: "screen",
							text: BX.message("IM_M_CALL_BTN_SCREEN"),
							blocked: this.isButtonBlocked("screen"),
							onClick: this._onScreenButtonClick.bind(this),
							onMouseOver: (e) =>
							{
								this._showHotKeyHint(e.currentTarget, "screen", this.keyModifier + " + S");
							},
							onMouseOut: () =>
							{
								this._destroyHotKeyHint();
							}
						});
					}
					else
					{
						this.buttons.screen.setBlocked(this.isButtonBlocked("screen"));
					}
					center.appendChild(this.buttons.screen.render());
					break;
				case "users":
					if (!this.buttons.users)
					{
						this.buttons.users = new Buttons.SimpleButton({
							class: "users",
							backgroundClass: "calm-counter",
							text: BX.message("IM_M_CALL_BTN_USERS"),
							blocked: this.isButtonBlocked("users"),
							onClick: this._onUsersButtonClick.bind(this),
							onMouseOver: function (e)
							{
								this._showHotKeyHint(e.currentTarget, "users", this.keyModifier + ' + U');
							}.bind(this),
							onMouseOut: function ()
							{
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
					if (!this.buttons.record)
					{
						this.buttons.record = new Buttons.SimpleButton({
							class: "record",
							backgroundClass: "bx-messenger-videocall-panel-background-record",
							text: BX.message("IM_M_CALL_BTN_RECORD"),
							blocked: this.isButtonBlocked("record"),
							onClick: this._onRecordToggleClick.bind(this),
							onMouseOver: (e) =>
							{
								if (this.isRecordingHotKeySupported())
								{
									this._showHotKeyHint(e.currentTarget, "record", this.keyModifier + " + R");
								}
							},
							onMouseOut: () =>
							{
								if (this.isRecordingHotKeySupported())
								{
									this._destroyHotKeyHint();
								}
							}
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
						this.buttons.document = new Buttons.SimpleButton({
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
					this.buttons.returnToCall = new Buttons.SimpleButton({
						class: "returnToCall",
						text: BX.message("IM_M_CALL_BTN_RETURN_TO_CALL"),
						onClick: this._onBodyClick.bind(this)
					});
					right.appendChild(this.buttons.returnToCall.render());
					break;
				case "hangup":
					this.buttons.hangup = new Buttons.SimpleButton({
						class: "hangup",
						backgroundClass: "bx-messenger-videocall-panel-icon-background-hangup",
						text: Object.keys(this.users).length > 1 ? BX.message("IM_M_CALL_BTN_DISCONNECT") : BX.message("IM_M_CALL_BTN_HANGUP"),
						onClick: this._onHangupButtonClick.bind(this)
					});
					right.appendChild(this.buttons.hangup.render());
					break;
				case "close":
					this.buttons.close = new Buttons.SimpleButton({
						class: "close",
						backgroundClass: "bx-messenger-videocall-panel-icon-background-hangup",
						text: BX.message("IM_M_CALL_BTN_CLOSE"),
						onClick: this._onCloseButtonClick.bind(this)
					});
					right.appendChild(this.buttons.close.render());
					break;
				case "speaker":
					/*this.buttons.speaker = new Buttons.DeviceButton({
						class: "speaker",
						text: BX.message("IM_M_CALL_BTN_SPEAKER"),
						enabled: !this.speakerMuted,
						arrowEnabled: Hardware.canSelectSpeaker() && this.isMediaSelectionAllowed(),
						onClick: this._onSpeakerButtonClick.bind(this),
						onArrowClick: this._onSpeakerArrowClick.bind(this)
					});
					rightSubPanel.appendChild(this.buttons.speaker.render());*/
					break;
				case "mobileMenu":
					if (!this.buttons.mobileMenu)
					{
						this.buttons.mobileMenu = new Buttons.SimpleButton({
							class: "sandwich",
							text: BX.message("IM_M_CALL_BTN_MENU"),
							onClick: this._onMobileMenuButtonClick.bind(this)
						})
					}
					center.appendChild(this.buttons.mobileMenu.render());
					break;
				case "chat":
					if (!this.buttons.chat)
					{
						this.buttons.chat = new Buttons.SimpleButton({
							class: "chat",
							text: BX.message("IM_M_CALL_BTN_CHAT"),
							blocked: this.isButtonBlocked("chat"),
							onClick: this._onChatButtonClick.bind(this),
							onMouseOver: (e) =>
							{
								this._showHotKeyHint(e.currentTarget, "chat", this.keyModifier + " + C");
							},
							onMouseOut: () =>
							{
								this._destroyHotKeyHint();
							}
						});
					}
					else
					{
						this.buttons.chat.setBlocked(this.isButtonBlocked('chat'));
					}
					center.appendChild(this.buttons.chat.render());
					break;
				case "floorRequest":
					if (!this.buttons.floorRequest)
					{
						this.buttons.floorRequest = new Buttons.SimpleButton({
							class: "floor-request",
							backgroundClass: "bx-messenger-videocall-panel-background-floor-request",
							text: BX.message("IM_M_CALL_BTN_WANT_TO_SAY"),
							blocked: this.isButtonBlocked("floorRequest"),
							onClick: this._onFloorRequestButtonClick.bind(this),
							onMouseOver: (e) =>
							{
								this._showHotKeyHint(e.currentTarget, "floorRequest", this.keyModifier + " + H");
							},
							onMouseOut: () => this._destroyHotKeyHint()

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
						this.buttons.more = new Buttons.SimpleButton({
							class: "more",
							onClick: this._onMoreButtonClick.bind(this)
						})
					}
					center.appendChild(this.buttons.more.render());
					break;
				case "spacer":
					panelInner.appendChild(Dom.create("div", {
						props: {className: "bx-messenger-videocall-panel-spacer"}
					}));
					break;
				/*case "history":
					this.buttons.history = new Buttons.SimpleButton({
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

	renderTopButtons(buttons)
	{
		let result = BX.createFragment();

		for (let i = 0; i < buttons.length; i++)
		{
			switch (buttons[i])
			{
				case "watermark":
					this.buttons.waterMark = new Buttons.WaterMarkButton({
						language: this.language
					});
					result.appendChild(this.buttons.waterMark.render());
					break;
				case "hd":
					this.buttons.hd = new Buttons.TopFramelessButton({
						iconClass: "hd"
					});
					result.appendChild(this.buttons.hd.render());
					break;
				case "protected":
					this.buttons.protected = new Buttons.TopFramelessButton({
						iconClass: "protected",
						textClass: "protected",
						text: BX.message("IM_M_CALL_PROTECTED"),
						onMouseOver: (e) =>
						{
							this.hintManager.show(e.currentTarget, BX.message("IM_M_CALL_PROTECTED_HINT"));
						},
						onMouseOut: () =>
						{
							this.hintManager.hide();
						}
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
						this.buttons.recordStatus = new Buttons.RecordStatusButton({
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
					this.buttons.grid = new Buttons.TopButton({
						iconClass: this.layout == Layouts.Grid ? "speaker" : "grid",
						text: this.layout == Layouts.Grid ? BX.message("IM_M_CALL_SPEAKER_MODE") : BX.message("IM_M_CALL_GRID_MODE"),
						onClick: this._onGridButtonClick.bind(this),
						onMouseOver: (e) =>
						{
							this._showHotKeyHint(e.currentTarget, "grid", this.keyModifier + " + W", {position: "bottom"});
						},
						onMouseOut: () =>
						{
							this._destroyHotKeyHint();
						}
					});
					result.appendChild(this.buttons.grid.render());
					break;
				case "fullscreen":
					this.buttons.fullscreen = new Buttons.TopButton({
						iconClass: this.isFullScreen ? "fullscreen-leave" : "fullscreen-enter",
						text: this.isFullScreen ? BX.message("IM_M_CALL_WINDOW_MODE") : BX.message("IM_M_CALL_FULLSCREEN_MODE"),
						onClick: this._onFullScreenButtonClick.bind(this)
					});
					result.appendChild(this.buttons.fullscreen.render());
					break;
				case "participants":
					let foldButtonState;

					if (this.isFullScreen && this.layout == Layouts.Centered)
					{
						foldButtonState = this.isUserBlockFolded ? Buttons.ParticipantsButton.FoldButtonState.Unfold : Buttons.ParticipantsButton.FoldButtonState.Fold
					}
					else if (this.showUsersButton)
					{
						foldButtonState = Buttons.ParticipantsButton.FoldButtonState.Active;
					}
					else
					{
						foldButtonState = Buttons.ParticipantsButton.FoldButtonState.Hidden;
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
						this.buttons.participants = new Buttons.ParticipantsButton({
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
					this.buttons.participantsMobile = new Buttons.ParticipantsButtonMobile({
						count: this.getConnectedUserCount(true),
						onClick: this._onParticipantsButtonMobileListClick.bind(this),
					});
					result.appendChild(this.buttons.participantsMobile.render());
					break;
				case "separator":
					result.appendChild(Dom.create("div", {
						props: {className: "bx-messenger-videocall-top-separator"}
					}));
					break;
				case "spacer":
					result.appendChild(Dom.create("div", {
						props: {className: "bx-messenger-videocall-top-panel-spacer"}
					}));
					break;
			}
		}
		return result;
	};

	calculateUnusedPanelSpace(buttonList)
	{
		if (!buttonList)
		{
			buttonList = this.getButtonList();
		}

		let totalButtonWidth = 0;
		for (let i = 0; i < buttonList.length; i++)
		{
			const button = this.buttons[buttonList[i]];
			if (!button)
			{
				continue;
			}
			const buttonWidth = button.elements.root ? button.elements.root.getBoundingClientRect().width : 0;
			totalButtonWidth += buttonWidth;
		}
		return this.elements.panel.scrollWidth - totalButtonWidth - 32;
	};

	setButtonActive(buttonName, isActive)
	{
		if (!this.buttons[buttonName])
		{
			return;
		}

		this.buttons[buttonName].setActive(isActive);
	};

	getButtonActive(buttonName)
	{
		if (!this.buttons[buttonName])
		{
			return false;
		}

		return this.buttons[buttonName].isActive;
	};

	setButtonCounter(buttonName, counter)
	{
		if (!this.buttons[buttonName])
		{
			return;
		}

		this.buttons[buttonName].setCounter(counter);
	};

	updateUserList()
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
		if (this.layout == Layouts.Grid && this.size == Size.Full)
		{
			this.recalculatePages();
		}
		this.renderUserList();

		if (this.layout == Layouts.Centered)
		{
			if (!this.elements.userList.container.parentElement)
			{
				this.elements.userBlock.appendChild(this.elements.userList.container);
			}
			//this.centralUser.setFullSize(this.elements.userList.container.childElementCount === 0);

		}
		else if (this.layout == Layouts.Grid)
		{
			if (!this.elements.userList.container.parentElement)
			{
				this.elements.container.appendChild(this.elements.userList.container);
			}
		}
		this.toggleEars();
	};

	showOverflownButtonsPopup()
	{
		if (this.overflownButtonsPopup)
		{
			this.overflownButtonsPopup.show();
			return;
		}

		const bindElement = this.buttons.more && this.buttons.more.elements.root ? this.buttons.more.elements.root : this.elements.panel;

		this.overflownButtonsPopup = new Popup({
			id: 'bx-call-buttons-popup',
			bindElement: bindElement,
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
				onPopupDestroy: () =>
				{
					this.overflownButtonsPopup = null;
					this.buttons.more.setActive(false);
				},
			}
		});
		this.overflownButtonsPopup.show();
	}

	resumeVideo()
	{
		for (let userId in this.users)
		{
			const user = this.users[userId];
			user.playVideo()
			const screenUser = this.screenUsers[userId];
			screenUser.playVideo();
		}
		this.localUser.playVideo();
	};

	updateUserButtons()
	{
		for (let userId in this.users)
		{
			if (this.users.hasOwnProperty(userId))
			{
				this.users[userId].allowPinButton = this.getConnectedUserCount() > 1;
			}
		}
	};

	updateButtons()
	{
		if (!this.elements.panel)
		{
			return;
		}
		Dom.clean(this.elements.panel);
		Dom.clean(this.elements.topPanel);
		this.elements.panel.appendChild(this.renderButtons(this.getButtonList()));
		if (this.elements.topPanel)
		{
			this.elements.topPanel.appendChild(this.renderTopButtons(this.getTopButtonList()));
		}
		if (this.buttons.participantsMobile)
		{
			this.buttons.participantsMobile.setCount(this.getConnectedUserCount(true));
		}
	};

	updateUserData(userData)
	{
		for (let userId in userData)
		{
			if (!this.userData[userId])
			{
				this.userData[userId] = {
					name: '',
					avatar_hr: '',
					gender: 'M'
				}
			}
			if (userData[userId].name)
			{
				this.userData[userId].name = userData[userId].name;
			}

			if (userData[userId].avatar_hr)
			{
				this.userData[userId].avatar_hr = Util.isAvatarBlank(userData[userId].avatar_hr) ? '' : userData[userId].avatar_hr;
			}
			else if (userData[userId].avatar)
			{
				this.userData[userId].avatar_hr = Util.isAvatarBlank(userData[userId].avatar) ? '' : userData[userId].avatar;
			}

			if (userData[userId].gender)
			{
				this.userData[userId].gender = userData[userId].gender === 'F' ? 'F' : 'M';
			}

			const userModel = this.userRegistry.get(userId);
			if (userModel)
			{
				userModel.name = this.userData[userId].name;
				userModel.avatar = this.userData[userId].avatar_hr;
			}
		}
	};

	isScreenSharingSupported()
	{
		return navigator.mediaDevices && typeof (navigator.mediaDevices.getDisplayMedia) === "function" || typeof (BXDesktopSystem) !== "undefined";
	};

	isRecordingHotKeySupported()
	{
		return typeof (BXDesktopSystem) !== "undefined" && BXDesktopSystem.ApiVersion() >= 60;
	};

	isFullScreenSupported()
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

	toggleEars()
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

	toggleTopEar()
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

	toggleBottomEar()
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

	scrollUserListUp()
	{
		this.stopScroll();
		this.scrollInterval = setInterval(
			() => this.elements.userList.container.scrollTop -= 10,
			20
		);
	};

	scrollUserListDown()
	{
		this.stopScroll();
		this.scrollInterval = setInterval(
			() => this.elements.userList.container.scrollTop += 10,
			20
		);
	};

	stopScroll()
	{
		if (this.scrollInterval)
		{
			clearInterval(this.scrollInterval);
			this.scrollInterval = 0;
		}
	};

	toggleRenameSliderInputLoader()
	{
		this.elements.renameSlider.button.classList.add('ui-btn-wait');
	};

	setHotKeyTemporaryBlock(isActive, force)
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

	setHotKeyActive(name, isActive)
	{
		if (typeof this.hotKey[name] === 'undefined')
		{
			return;
		}

		this.hotKey[name] = !!isActive;
	};

	isHotKeyActive(name)
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

	_onBodyClick()
	{
		this.eventEmitter.emit(EventName.onBodyClick);
	};

	_onCenterTouchStart(e)
	{
		this.centerTouchX = e.pageX;
	};

	_onCenterTouchEnd(e)
	{
		const delta = e.pageX - this.centerTouchX;

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

	_onFullScreenChange()
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
		setTimeout(function ()
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

	_onIntersectionChange(entries)
	{
		let t = {};
		entries.forEach(function (intersectionEntry)
		{
			t[intersectionEntry.target.dataset.userId] = intersectionEntry.isIntersecting;
		});
		for (let userId in t)
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

	_onResize()
	{
		// this.resizeCalled++;
		// this.reportResizeCalled();

		if (!this.elements.root)
		{
			return;
		}
		if (this.centralUser)
		{
			//this.centralUser.updateAvatarWidth();
		}
		if (BX.browser.IsMobile())
		{
			document.documentElement.style.setProperty('--view-height', window.innerHeight + 'px');
		}
		if (this.layout == Layouts.Grid)
		{
			this.updateUserList();
		}
		else
		{
			this.updateCentralUserAvatarSize();
			this.toggleEars();
		}

		const rootDimensions = this.elements.root.getBoundingClientRect()
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

	_onOrientationChange()
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

	_showHotKeyHint(targetNode, name, text, options)
	{
		const existingHint = BX.PopupWindowManager.getPopupById('ui-hint-popup');
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
			onShow: function (event)
			{
				const popup = event.getTarget();
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

	_destroyHotKeyHint()
	{
		if (!Util.isDesktop())
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

	_showMicrophoneHint(e)
	{
		this.hintManager.hide();

		if (!this.isHotKeyActive("microphone"))
		{
			return;
		}

		let micHotkeys = '';
		if (this.isMuted && this.isHotKeyActive("microphoneSpace"))
		{
			micHotkeys = BX.message("IM_SPACE_HOTKEY") + '<br>';
		}
		micHotkeys += this.keyModifier + ' + A';

		this._showHotKeyHint(e.currentTarget.firstChild, "microphone", micHotkeys);
	}

	_onKeyDown(e)
	{
		if (!Util.isDesktop())
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

		const callMinimized = this.size === View.Size.Folded;

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
				this.microphoneHotkeyTimerId = setTimeout(function ()
				{
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

	_onKeyUp(e)
	{
		if (!Util.isDesktop())
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

	_onUserClick(e)
	{
		const userId = e.userId;
		if (userId == this.userId)
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

	_onUserRename(newName)
	{
		this.eventEmitter.emit(EventName.onUserRename, {newName: newName});
	};

	_onUserRenameInputFocus()
	{
		this.setHotKeyTemporaryBlock(true);
	};

	_onUserRenameInputBlur()
	{
		this.setHotKeyTemporaryBlock(false);
	};

	_onUserPin(e)
	{
		if (this.layout == Layouts.Grid)
		{
			this.setLayout(Layouts.Centered)
		}
		this.pinUser(e.userId);
	};

	_onUserUnPin()
	{
		this.unpinUser();
	};

	_onRecordToggleClick(e)
	{
		if (this.recordState.state === View.RecordState.Stopped)
		{
			this._onRecordStartClick(e);
		}
		else
		{
			this._onRecordStopClick(e);
		}
	}

	_onForceRecordToggleClick(e)
	{
		if (this.recordState.state === View.RecordState.Stopped)
		{
			this._onForceRecordStartClick(View.RecordType.Video);
		}
		else
		{
			this._onRecordStopClick(e);
		}
	}

	_onForceRecordStartClick(recordType)
	{
		if (typeof recordType === 'undefined')
		{
			recordType = View.RecordType.None;
		}

		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: View.RecordState.Started,
			forceRecord: recordType, // none, video, audio
			node: null
		});
	}

	_onRecordStartClick(e)
	{
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: View.RecordState.Started,
			node: e.currentTarget
		});
	}

	_onRecordPauseClick(e)
	{
		let recordState;
		if (this.recordState.state === View.RecordState.Paused)
		{
			this.recordState.state = View.RecordState.Started;
			recordState = View.RecordState.Resumed;
		}
		else
		{
			this.recordState.state = View.RecordState.Paused;
			recordState = this.recordState.state;
		}

		this.buttons.recordStatus.update(this.recordState);

		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: recordState,
			node: e.currentTarget
		});
	};

	_onRecordStopClick(e)
	{
		this.recordState.state = View.RecordState.Stopped;
		this.buttons.recordStatus.update(this.recordState);

		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "record",
			recordState: this.recordState.state,
			node: e.currentTarget
		});
	};

	_onRecordMouseOver(e)
	{
		if (this.recordState.userId == this.userId || !this.userData[this.recordState.userId])
		{
			return;
		}

		const recordingUserName = Text.encode(this.userData[this.recordState.userId].name);
		this.hintManager.show(e.currentTarget, BX.message("IM_M_CALL_RECORD_HINT").replace("#USER_NAME#", recordingUserName));
	};

	_onRecordMouseOut()
	{
		this.hintManager.hide();
	};

	_onDocumentButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'document',
			node: e.target
		});
	};

	_onGridButtonClick()
	{
		this.setLayout(this.layout == Layouts.Centered ? Layouts.Grid : Layouts.Centered);
	};

	_onAddButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "inviteUser",
			node: e.currentTarget
		});
	};

	_onShareButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "share",
			node: e.currentTarget
		});
	};

	_onMicrophoneButtonClick(e)
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

	_onMicrophoneArrowClick(e)
	{
		e.stopPropagation();
		this.showDeviceSelector(e.currentTarget);
	};

	_onMicrophoneSideIconClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "microphoneSideIcon",
		});
	};

	_onMicrophoneSelected(e)
	{
		if (e.data.deviceId === this.microphoneId)
		{
			return;
		}

		this.eventEmitter.emit(EventName.onReplaceMicrophone, {
			deviceId: e.data.deviceId
		});
	};

	_onCameraButtonClick(e)
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

	_onCameraArrowClick(e)
	{
		e.stopPropagation();
		this.showDeviceSelector(e.currentTarget);
	};

	_onCameraSelected(e)
	{
		if (e.data.deviceId === this.cameraId)
		{
			return;
		}

		this.eventEmitter.emit(EventName.onReplaceCamera, {
			deviceId: e.data.deviceId
		});
	};

	_onSpeakerButtonClick()
	{
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "toggleSpeaker",
			speakerMuted: this.speakerMuted
		});
	};

	_onChangeHdVideo(e)
	{
		this.eventEmitter.emit(EventName.onChangeHdVideo, e.data);
	};

	_onChangeMicAutoParams(e)
	{
		this.eventEmitter.emit(EventName.onChangeMicAutoParams, e.data);
	};

	_onChangeFaceImprove(e)
	{
		this.eventEmitter.emit(EventName.onChangeFaceImprove, e.data);
	};

	_onSpeakerSelected(e)
	{
		this.setSpeakerId(e.data.deviceId);

		this.eventEmitter.emit(EventName.onReplaceSpeaker, {
			deviceId: e.data.deviceId
		});
	};

	_onScreenButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'toggleScreenSharing',
			node: e.target
		});
	};

	_onChatButtonClick(e)
	{
		this.hintManager.hide();
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'showChat',
			node: e.target
		});
	};

	_onUsersButtonClick(e)
	{
		this.hintManager.hide();
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'toggleUsers',
			node: e.target
		});
	};

	_onMobileMenuButtonClick(e)
	{
		e.stopPropagation();
		this.showCallMenu();
	};

	_onFloorRequestButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'floorRequest',
			node: e.target
		});
	};

	_onMoreButtonClick(e)
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

	_onHistoryButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'showHistory',
			node: e.target
		});
	};

	_onHangupButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'hangup',
			node: e.target
		});
	};

	_onCloseButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'close',
			node: e.target
		});
	};

	_onFullScreenButtonClick(e)
	{
		e.stopPropagation();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'fullscreen',
			node: e.target
		});
	};

	_onParticipantsButtonListClick(event)
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

	_onParticipantsListButtonClick(e)
	{
		e.stopPropagation();

		const viewEvent = new BaseEvent({
			data: {
				buttonName: 'participantsList',
				node: e.target
			},
			compatData: ['participantsList', e.target],
		});
		this.eventEmitter.emit(EventName.onButtonClick, viewEvent);

		if (viewEvent.isDefaultPrevented())
		{
			return;
		}

		UserSelector.create({
			parentElement: e.currentTarget,
			zIndex: this.baseZIndex + 500,
			userList: Object.values(this.users),
			current: this.centralUser.id,
			onSelect: (userId) => this.setCentralUser(userId)
		}).show();
	};

	_onParticipantsButtonMobileListClick()
	{
		this.showParticipantsMenu();
	};

	_onMobileCallMenuFloorRequestClick()
	{
		this.callMenu.close();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: 'floorRequest',
		});
	};

	_onMobileCallMenShowParticipantsClick()
	{
		this.callMenu.close();
		this.showParticipantsMenu();
	};

	_onMobileCallMenuCopyInviteClick()
	{
		this.callMenu.close();
		this.eventEmitter.emit(EventName.onButtonClick, {
			buttonName: "share",
			node: null
		})
	};

	showRenameSlider()
	{
		if (!this.renameSlider)
		{
			this.renameSlider = new MobileSlider({
				parent: this.elements.root,
				content: this.renderRenameSlider(),
				onClose: () => this.renameSlider.destroy(),
				onDestroy: () => this.renameSlider = null,
			});
		}

		this.renameSlider.show();
		setTimeout(
			() =>
			{
				this.elements.renameSlider.input.focus();
				this.elements.renameSlider.input.select();
			},
			400
		);
	};

	renderRenameSlider()
	{
		return Dom.create("div", {
			props: {
				className: "bx-videocall-mobile-rename-slider-wrap"
			},
			children: [
				Dom.create("div", {
					props: {
						className: "bx-videocall-mobile-rename-slider-title"
					},
					text: BX.message("IM_M_CALL_MOBILE_MENU_CHANGE_MY_NAME")
				}),
				this.elements.renameSlider.input = Dom.create("input", {
					props: {
						className: "bx-videocall-mobile-rename-slider-input"
					},
					attrs: {
						type: "text",
						value: this.localUser.userModel.name
					}
				}),
				this.elements.renameSlider.button = Dom.create("button", {
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

	_onMobileUserRename(event)
	{
		event.stopPropagation();

		const inputValue = this.elements.renameSlider.input.value;
		const newName = inputValue.trim();
		let needToUpdate = true;
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

	_onMobileCallMenuCancelClick()
	{
		this.callMenu.close();
	};

	_onLeftEarClick()
	{
		this.pinUser(this.getLeftUser(this.centralUser.id));
	};

	_onRightEarClick()
	{
		this.pinUser(this.getRightUser(this.centralUser.id));
	};

	_onLeftPageNavigatorClick(e)
	{
		e.stopPropagation();
		this.setCurrentPage(this.currentPage - 1)
	};

	_onRightPageNavigatorClick(e)
	{
		e.stopPropagation();
		this.setCurrentPage(this.currentPage + 1)
	};

	setMaxWidth(maxWidth)
	{
		if (this.maxWidth !== maxWidth)
		{
			const MAX_WIDTH_SPEAKER_MODE = 650;
			if (maxWidth < MAX_WIDTH_SPEAKER_MODE
				&& (!this.maxWidth || this.maxWidth > MAX_WIDTH_SPEAKER_MODE)
				&& this.layout === Layouts.Centered
			)
			{
				this.setLayout(Layouts.Grid)
			}

			const animateUnsetProperty = this.maxWidth === null;
			this.maxWidth = maxWidth;
			if (this.size !== View.Size.Folded)
			{
				this._applyMaxWidth(animateUnsetProperty);
			}
		}
	};

	removeMaxWidth()
	{
		this.setMaxWidth(null);
	}

	_applyMaxWidth(animateUnsetProperty)
	{
		const containerDimensions = this.container.getBoundingClientRect();
		if (this.maxWidth !== null)
		{
			if (!this.elements.root.style.maxWidth && animateUnsetProperty)
			{
				this.elements.root.style.maxWidth = containerDimensions.width + 'px';
			}
			setTimeout(
				() => this.elements.root.style.maxWidth = Math.max(this.maxWidth, MIN_WIDTH) + 'px',
				0,
			);
		}
		else
		{
			this.elements.root.style.maxWidth = containerDimensions.width + 'px';
			this.elements.root.addEventListener('transitionend',
				() => this.elements.root.style.removeProperty('max-width'),
				{once: true},
			)
		}
	};

	releaseLocalMedia()
	{
		this.localUser.releaseStream();
		if (this.centralUser.id == this.userId)
		{
			this.centralUser.releaseStream();
		}
	};

	destroy()
	{
		if (this.overflownButtonsPopup)
		{
			this.overflownButtonsPopup.close();
		}
		if (this.elements.root)
		{
			Dom.remove(this.elements.root);
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
		for (let userId in this.users)
		{
			if (this.users.hasOwnProperty(userId))
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

	static Layout = Layouts;
	static Size = Size;
	static RecordState = RecordState;
	static RecordType = RecordType;

	static RecordSource = {
		Chat: 'BXCLIENT_CHAT'
	};

	static UiState = UiState;
	static Event = EventName;
	static RoomState = RoomState;
	static DeviceSelector = DeviceSelector;
	static NotificationManager = NotificationManager;
	static MIN_WIDTH = MIN_WIDTH
}