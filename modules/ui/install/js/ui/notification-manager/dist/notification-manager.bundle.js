/* eslint-disable */
this.BX = this.BX || {};
this.BX.UI = this.BX.UI || {};
(function (exports,pull_client,main_core_events,main_core,ui_notification,ui_buttons) {
	'use strict';

	class Uuid {
	  static getV4() {
	    return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function (c) {
	      var r = Math.random() * 16 | 0,
	        v = c == 'x' ? r : r & 0x3 | 0x8;
	      return v.toString(16);
	    });
	  }
	}

	/**
	 * @memberof BX.UI.NotificationManager
	 */
	class Notification {
	  constructor(options) {
	    this.setUid(options.id);
	    this.setCategory(options.category);
	    this.setTitle(options.title);
	    this.setText(options.text);
	    this.setIcon(options.icon);
	    this.setInputPlaceholderText(options.inputPlaceholderText);
	    this.createButtons(options.button1Text, options.button2Text);
	  }
	  static encodeIdToUid(id) {
	    return id + Notification.SEPARATOR + Uuid.getV4();
	  }
	  static decodeUidToId(uid) {
	    let id = uid.split(Notification.SEPARATOR);
	    id.pop();
	    return id.join();
	  }
	  setUid(id) {
	    if (!main_core.Type.isStringFilled(id)) {
	      throw new Error(`NotificationManager: Cannot create a notification without an ID`);
	    }
	    this.uid = Notification.encodeIdToUid(id);
	  }
	  getUid() {
	    return this.uid;
	  }
	  getId() {
	    return Notification.decodeUidToId(this.uid);
	  }
	  setCategory(category) {
	    this.category = main_core.Type.isStringFilled(category) ? category : '';
	  }
	  getCategory() {
	    return this.category;
	  }
	  setTitle(title) {
	    this.title = main_core.Type.isStringFilled(title) ? title : '';
	  }
	  getTitle() {
	    return this.title;
	  }
	  setText(text) {
	    this.text = main_core.Type.isStringFilled(text) ? text : '';
	  }
	  getText() {
	    return this.text;
	  }
	  setIcon(icon) {
	    this.icon = main_core.Type.isStringFilled(icon) ? icon : '';
	  }
	  getIcon() {
	    return this.icon;
	  }
	  setInputPlaceholderText(inputPlaceholderText) {
	    if (main_core.Type.isString(inputPlaceholderText)) {
	      this.inputPlaceholderText = inputPlaceholderText;
	    }
	  }
	  getInputPlaceholderText() {
	    return this.inputPlaceholderText;
	  }
	  createButtons(button1Text, button2Text) {
	    if (this.getInputPlaceholderText()) {
	      this.setButton1Text(main_core.Loc.getMessage('UI_NOTIFICATION_MANAGER_REPLY'));
	      this.setButton2Text(main_core.Loc.getMessage('UI_NOTIFICATION_MANAGER_CLOSE'));
	    } else {
	      this.setButton1Text(button1Text);
	      this.setButton2Text(button2Text);
	    }
	  }
	  setButton1Text(button1Text) {
	    if (main_core.Type.isStringFilled(button1Text)) {
	      this.button1Text = button1Text;
	    }
	  }
	  getButton1Text() {
	    return this.button1Text;
	  }
	  setButton2Text(button2Text) {
	    if (main_core.Type.isStringFilled(button2Text)) {
	      this.button2Text = button2Text;
	    }
	  }
	  getButton2Text() {
	    return this.button2Text;
	  }
	}
	Notification.SEPARATOR = 'u1F9D1';

	class PushNotification extends Notification {
	  setUid(id) {
	    if (!main_core.Type.isStringFilled(id)) {
	      throw new Error(`NotificationManager: Cannot create a notification without an ID`);
	    }
	    this.uid = id;
	  }
	}

	class PullHandler {
	  getModuleId() {
	    return 'ui';
	  }
	  handleNotify(params, extra, command) {
	    const notification = params.notification;
	    if (!notification) {
	      throw new Error('NotificationManager: Incorrect notification format');
	    }
	    const notificationOptions = notification;
	    const pushNotification = new PushNotification(notificationOptions);
	    notifier.sendNotification(pushNotification);
	  }
	}

	class DesktopHelper {
	  static isSupportedDesktopApp() {
	    return DesktopHelper.isBitrixDesktop() && DesktopHelper.geApiVersion() >= 67;
	  }
	  static isBitrixDesktop() {
	    return navigator.userAgent.toLowerCase().includes('bitrixdesktop');
	  }
	  static geApiVersion() {
	    if (typeof BXDesktopSystem === 'undefined') {
	      return 0;
	    }
	    return Number(BXDesktopSystem.GetProperty('versionParts')[3]);
	  }
	  static isMainTab() {
	    if (typeof BXDesktopSystem === 'undefined') {
	      return false;
	    }
	    return typeof BX.desktop !== 'undefined' && BX.desktop.apiReady;
	  }
	  static isMac() {
	    return main_core.Browser.isMac();
	  }
	  static isLinux() {
	    return main_core.Browser.isLinux();
	  }
	  static isWindows() {
	    return main_core.Browser.isWin() || !main_core.Browser.isMac() && !main_core.Browser.isLinux();
	  }
	  static isRunningOnAnyDevice() {
	    return BXIM && BXIM.desktopStatus;
	  }
	  static checkRunningOnThisDevice() {
	    return new Promise(resolve => {
	      const turnedOnCallback = () => {
	        resolve(true);
	      };
	      const turnedOffCallback = () => {
	        resolve(false);
	      };
	      BX.desktopUtils.runningCheck(turnedOnCallback, turnedOffCallback);
	    });
	  }
	}

	class BrowserHelper {
	  static isSupportedBrowser() {
	    return BrowserHelper.isChrome() || BrowserHelper.isFirefox() || BrowserHelper.isSafari();
	  }
	  static isNativeNotificationAllowed() {
	    return window.Notification && window.Notification.permission && window.Notification.permission.toLowerCase() === 'granted';
	  }
	  static isSafari() {
	    if (BrowserHelper.isChrome()) {
	      return false;
	    }
	    if (!navigator.userAgent.toLowerCase().includes('safari')) {
	      return false;
	    }
	    return !BrowserHelper.isSafariBased();
	  }
	  static isSafariBased() {
	    if (!navigator.userAgent.toLowerCase().includes('applewebkit')) {
	      return false;
	    }
	    return navigator.userAgent.toLowerCase().includes('yabrowser') || navigator.userAgent.toLowerCase().includes('yaapp_ios_browser') || navigator.userAgent.toLowerCase().includes('crios');
	  }
	  static isChrome() {
	    return navigator.userAgent.toLowerCase().includes('chrome');
	  }
	  static isFirefox() {
	    return navigator.userAgent.toLowerCase().includes('firefox');
	  }
	}

	class NotificationEvent extends main_core_events.BaseEvent {
	  static getTypes() {
	    return [NotificationEvent.CLICK, NotificationEvent.ACTION, NotificationEvent.CLOSE];
	  }
	  static isSupported(eventType) {
	    return NotificationEvent.getTypes().includes(eventType);
	  }
	}
	NotificationEvent.CLICK = 'click';
	NotificationEvent.ACTION = 'action';
	NotificationEvent.CLOSE = 'close';

	class NotificationAction {
	  static getTypes() {
	    return [NotificationAction.BUTTON_1, NotificationAction.BUTTON_2, NotificationAction.USER_INPUT];
	  }
	  static isSupported(action) {
	    return NotificationAction.getTypes().includes(action);
	  }
	}
	NotificationAction.BUTTON_1 = 'button_1';
	NotificationAction.BUTTON_2 = 'button_2';
	NotificationAction.USER_INPUT = 'user_input';

	class NotificationCloseReason {
	  static getTypes() {
	    return [NotificationCloseReason.CLOSED_BY_USER, NotificationCloseReason.EXPIRED];
	  }
	  static isSupported(closeReason) {
	    return NotificationCloseReason.getTypes().includes(closeReason);
	  }
	}
	NotificationCloseReason.CLOSED_BY_USER = 'closed_by_user';
	NotificationCloseReason.EXPIRED = 'expired';

	class BaseProvider extends main_core_events.EventEmitter {
	  //The lifetime of the notification is 4 hours

	  constructor(options = {}) {
	    super();
	    if (main_core.Type.isStringFilled(options.eventNamespace)) {
	      this.setEventNamespace(options.eventNamespace);
	    }
	  }
	  convertNotificationToNative(notification) {
	    throw new Error('convertNotificationToNative() method must be implemented.');
	  }
	  sendNotification(nativeNotification) {
	    throw new Error('sendNotification() method must be implemented.');
	  }
	  canSendNotification(notification) {
	    return true;
	  }
	  notify(notification) {
	    if (!this.canSendNotification(notification)) {
	      return;
	    }
	    const nativeNotification = this.convertNotificationToNative(notification);
	    this.sendNotification(nativeNotification);
	  }
	  notificationClick(uid = '') {
	    const eventOptions = {
	      data: {
	        id: Notification.decodeUidToId(uid)
	      }
	    };
	    this.emit(NotificationEvent.CLICK, new NotificationEvent(eventOptions));
	  }
	  notificationAction(uid = '', action = '', userInput = null) {
	    if (!NotificationAction.isSupported(action)) {
	      console.warn(`NotificationManager: Unknown notification action "${action}".`);
	    }
	    const eventOptions = {
	      data: {
	        id: Notification.decodeUidToId(uid),
	        action
	      }
	    };
	    if (userInput) {
	      eventOptions.data.userInput = userInput;
	    }
	    this.emit(NotificationEvent.ACTION, new NotificationEvent(eventOptions));
	  }
	  notificationClose(uid = '', reason = '') {
	    if (!NotificationCloseReason.isSupported(reason)) {
	      console.warn(`NotificationManager: Unknown notification close reason "${reason}".`);
	    }
	    const eventOptions = {
	      data: {
	        id: Notification.decodeUidToId(uid),
	        reason
	      }
	    };
	    this.emit(NotificationEvent.CLOSE, new NotificationEvent(eventOptions));
	  }
	}
	BaseProvider.NOTIFICATION_LIFETIME = 14400000;

	class DesktopProvider extends BaseProvider {
	  constructor(options = {}) {
	    super(options);
	    if (this.getEventNamespace()) {
	      this.registerEvents();
	    }
	  }
	  convertNotificationToNative(notification) {
	    throw new Error('convertNotificationToNative() method must be implemented.');
	  }
	  canSendNotification(notification) {
	    //Desktop push & pull notifications, unlike regular ones, can be sent from only one tab to avoid duplication.
	    return DesktopHelper.isMainTab() || !(notification instanceof PushNotification);
	  }
	  sendNotification(notificationUid) {
	    BXDesktopSystem.NotificationShow(notificationUid);
	  }
	  registerEvents() {
	    window.addEventListener('BXNotificationClick', event => this.onNotificationClick(event));
	    window.addEventListener('BXNotificationAction', event => this.onNotificationAction(event));
	    window.addEventListener('BXNotificationDismissed', event => this.onNotificationClose(event));
	  }
	  onNotificationClick(event) {
	    const [id] = event.detail;
	    BXDesktopSystem.SetActiveTab();
	    this.notificationClick(id);
	  }
	  onNotificationAction(event) {
	    const [id, action, userInput] = event.detail;
	    this.notificationAction(id, action, userInput);
	  }
	  onNotificationClose(event) {
	    const [id, reason] = event.detail;
	    this.notificationClose(id, reason);
	  }
	}

	class MacProvider extends DesktopProvider {
	  convertNotificationToNative(notification) {
	    if (!main_core.Type.isStringFilled(notification.getId())) {
	      throw new Error(`NotificationManager: You cannot send a notification without an ID.`);
	    }
	    const notificationUid = notification.getUid();
	    BXDesktopSystem.NotificationCreate(notificationUid);
	    if (main_core.Type.isStringFilled(notification.getTitle())) {
	      BXDesktopSystem.NotificationAddText(notificationUid, notification.getTitle());
	    }
	    if (main_core.Type.isStringFilled(notification.getText())) {
	      //this.addTextToNotification(notificationUid, notification.getText());
	      BXDesktopSystem.NotificationAddText(notificationUid, notification.getText());
	    }
	    if (main_core.Type.isStringFilled(notification.getIcon())) {
	      BXDesktopSystem.NotificationAddImage(notificationUid, notification.getIcon());
	    }
	    if (notification.getInputPlaceholderText() && main_core.Type.isString(notification.getInputPlaceholderText())) {
	      BXDesktopSystem.NotificationAddInput(notificationUid, notification.getInputPlaceholderText(), NotificationAction.USER_INPUT);
	    }
	    if (notification.getButton1Text() && main_core.Type.isStringFilled(notification.getButton1Text())) {
	      BXDesktopSystem.NotificationAddAction(notificationUid, notification.getButton1Text(), NotificationAction.BUTTON_1);
	    }
	    if (notification.getButton2Text() && main_core.Type.isStringFilled(notification.getButton2Text())) {
	      BXDesktopSystem.NotificationAddAction(notificationUid, main_core.Loc.getMessage('UI_NOTIFICATION_MANAGER_CLOSE'), NotificationAction.BUTTON_2);
	    }
	    BXDesktopSystem.NotificationSetExpiration(notificationUid, BaseProvider.NOTIFICATION_LIFETIME);
	    return notificationUid;
	  }
	  addTextToNotification(notificationUid, text) {
	    if (text.trim() === '') {
	      return;
	    }
	    const languageSafeRowLength = 44;
	    if (text.length <= languageSafeRowLength) {
	      BXDesktopSystem.NotificationAddText(notificationUid, text);
	      return;
	    }
	    const space = ' ';
	    let firstRow = '';
	    let words = text.split(space);
	    while (words.length > 0) {
	      if (firstRow.length + words[0].length + 1 > languageSafeRowLength) {
	        break;
	      }
	      firstRow += words.shift() + space;
	    }
	    BXDesktopSystem.NotificationAddText(notificationUid, firstRow);
	    let secondRow = words.join(space);
	    if (secondRow !== '') {
	      BXDesktopSystem.NotificationAddText(notificationUid, secondRow);
	    }
	  }
	}

	class WindowsProvider extends DesktopProvider {
	  convertNotificationToNative(notification) {
	    if (!main_core.Type.isStringFilled(notification.getId())) {
	      throw new Error(`NotificationManager: You cannot send a notification without an ID.`);
	    }
	    const notificationUid = notification.getUid();
	    BXDesktopSystem.NotificationCreate(notificationUid);
	    if (main_core.Type.isStringFilled(notification.getTitle())) {
	      BXDesktopSystem.NotificationAddText(notificationUid, notification.getTitle());
	    }
	    if (main_core.Type.isStringFilled(notification.getText())) {
	      BXDesktopSystem.NotificationAddText(notificationUid, notification.getText());
	    }
	    if (main_core.Type.isStringFilled(notification.getIcon())) {
	      BXDesktopSystem.NotificationAddImage(notificationUid, notification.getIcon());
	    }
	    if (notification.getInputPlaceholderText() && main_core.Type.isString(notification.getInputPlaceholderText())) {
	      BXDesktopSystem.NotificationAddInput(notificationUid, notification.getInputPlaceholderText(), NotificationAction.USER_INPUT);
	    }
	    if (notification.getButton1Text() && main_core.Type.isStringFilled(notification.getButton1Text())) {
	      BXDesktopSystem.NotificationAddAction(notificationUid, notification.getButton1Text(), NotificationAction.BUTTON_1);
	    }
	    if (notification.getButton2Text() && main_core.Type.isStringFilled(notification.getButton2Text())) {
	      BXDesktopSystem.NotificationAddAction(notificationUid, notification.getButton2Text(), NotificationAction.BUTTON_2);
	    }
	    BXDesktopSystem.NotificationSetExpiration(notificationUid, BaseProvider.NOTIFICATION_LIFETIME);
	    return notificationUid;
	  }
	}

	class LinuxProvider extends DesktopProvider {
	  convertNotificationToNative(notification) {
	    if (!main_core.Type.isStringFilled(notification.getId())) {
	      throw new Error(`NotificationManager: You cannot send a notification without an ID.`);
	    }
	    const notificationUid = notification.getUid();
	    BXDesktopSystem.NotificationCreate(notificationUid);
	    if (main_core.Type.isStringFilled(notification.getTitle())) {
	      BXDesktopSystem.NotificationAddText(notificationUid, notification.getTitle());
	    }
	    if (main_core.Type.isStringFilled(notification.getText())) {
	      BXDesktopSystem.NotificationAddText(notificationUid, notification.getText());
	    }
	    if (main_core.Type.isStringFilled(notification.getIcon())) {
	      BXDesktopSystem.NotificationAddImage(notificationUid, notification.getIcon());
	    }
	    if (notification.getInputPlaceholderText() && main_core.Type.isString(notification.getInputPlaceholderText())) {
	      BXDesktopSystem.NotificationAddInput(notificationUid, notification.getInputPlaceholderText(), NotificationAction.USER_INPUT);
	    }
	    if (notification.getButton1Text() && main_core.Type.isStringFilled(notification.getButton1Text())) {
	      BXDesktopSystem.NotificationAddAction(notificationUid, notification.getButton1Text(), NotificationAction.BUTTON_1);
	    }
	    if (notification.getButton2Text() && main_core.Type.isStringFilled(notification.getButton2Text())) {
	      BXDesktopSystem.NotificationAddAction(notificationUid, notification.getButton2Text(), NotificationAction.BUTTON_2);
	    }
	    BXDesktopSystem.NotificationSetExpiration(notificationUid, BaseProvider.NOTIFICATION_LIFETIME);
	    return notificationUid;
	  }
	}

	class BrowserProvider extends BaseProvider {
	  convertNotificationToNative(notification) {
	    const notificationOptions = {
	      title: notification.getTitle() ? notification.getTitle() : '',
	      options: {
	        body: '',
	        tag: notification.getUid(),
	        renotify: true
	      },
	      onclick: event => {
	        event.preventDefault();
	        window.focus();
	        this.notificationClick(notification.getUid());
	      }
	    };
	    if (main_core.Type.isStringFilled(notification.getIcon())) {
	      notificationOptions.options.icon = notification.getIcon();
	    }
	    if (main_core.Type.isStringFilled(notification.getText())) {
	      notificationOptions.options.body = notification.getText();
	    }
	    return notificationOptions;
	  }
	  sendNotification(notificationOptions) {
	    if (!DesktopHelper.isRunningOnAnyDevice()) {
	      return;
	    }
	    DesktopHelper.checkRunningOnThisDevice().then(isRunningOnThisDevice => {
	      if (isRunningOnThisDevice) {
	        return;
	      }
	      const notification = new window.Notification(notificationOptions.title, notificationOptions.options);
	      notification.onclick = notificationOptions.onclick;
	    });
	  }
	}

	class BrowserNotificationAction extends BX.UI.Notification.Action {
	  constructor(balloon, options) {
	    super(balloon, options);
	    this.setButtonClass(options.buttonType);
	  }
	  getContainer() {
	    if (this.container !== null) {
	      return this.container;
	    }
	    let buttonOptions = {
	      text: this.getTitle()
	    };
	    if (main_core.Type.isFunction(this.events.click)) {
	      buttonOptions.onclick = (button, event) => {
	        event.stopPropagation();
	        this.events.click(button, event);
	      };
	    }
	    const button = new ui_buttons.Button(buttonOptions);
	    button.removeClass('ui-btn');
	    button.addClass(BrowserNotificationAction.BASE_BUTTON_CLASS);
	    button.addClass(this.getButtonClass());
	    this.container = button.getContainer();
	    return this.container;
	  }
	  static getButtonTypes() {
	    return [BrowserNotificationAction.TYPE_ACCEPT];
	  }
	  static isSupportedButtonType(buttonType) {
	    return BrowserNotificationAction.getButtonTypes().includes(buttonType);
	  }
	  setButtonClass(buttonType) {
	    this.buttonClass = BrowserNotificationAction.isSupportedButtonType(buttonType) ? BrowserNotificationAction.BASE_BUTTON_CLASS + '-' + buttonType : '';
	  }
	  getButtonClass() {
	    return this.buttonClass;
	  }
	}
	BrowserNotificationAction.BASE_BUTTON_CLASS = 'ui-notification-manager-browser-button';
	BrowserNotificationAction.TYPE_ACCEPT = 'accept';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5,
	  _t6;
	class BrowserNotification extends BX.UI.Notification.Balloon {
	  constructor(options) {
	    super(options);
	    this.userInputContainerNode = null;
	    this.userInputNode = null;
	  }
	  setActions(actions) {
	    this.actions = [];
	    if (main_core.Type.isArray(actions)) {
	      actions.forEach(action => this.actions.push(new BrowserNotificationAction(this, action)));
	    }
	  }
	  getContainer() {
	    if (this.container !== null) {
	      return this.container;
	    }
	    const onMouseEnter = () => this.handleMouseEnter();
	    const onMouseLeave = () => this.handleMouseLeave();
	    this.container = main_core.Tag.render(_t || (_t = _`
			<div
				class="ui-notification-manager-browser-balloon"
				onmouseenter="${0}"
				onmouseleave="${0}"
			>
				${0}
			</div>
		`), onMouseEnter, onMouseLeave, this.render());
	    return this.container;
	  }
	  render() {
	    this.animationClassName = "ui-notification-manager-browser-balloon-animate";
	    const contentWidth = main_core.Type.isNumber(this.getWidth()) ? this.getWidth() + 'px' : this.getWidth();
	    return main_core.Tag.render(_t2 || (_t2 = _`
			<div
				class="ui-notification-manager-browser-content"
				style="width: ${0}"
			>
				<div
					class="ui-notification-manager-browser-message"
					onclick="${0}"
					oncontextmenu="${0}"
				>
					${0}
					<div class="ui-notification-manager-browser-column">
						${0}
						${0}
						${0}
						${0}
					</div>
				</div>
				${0}
			</div>
		`), contentWidth, this.handleContentClick.bind(this), this.handleContextClick.bind(this), this.getIconNode(), this.getTitleNode(), this.getTextNode(), this.getUserInputContainerNode(), this.getActionsNode(), this.getCloseButtonNode());
	  }
	  getTitleNode() {
	    if (!main_core.Type.isStringFilled(this.getData().title)) {
	      return '';
	    }
	    const title = main_core.Dom.create({
	      tag: 'span',
	      attrs: {
	        className: 'ui-notification-manager-browser-title'
	      },
	      text: this.getData().title
	    }).outerHTML;
	    return main_core.Tag.render(_t3 || (_t3 = _`<div class="ui-notification-manager-browser-title">${0}<div>`), title);
	  }
	  getTextNode() {
	    if (!main_core.Type.isStringFilled(this.getData().text)) {
	      return '';
	    }
	    return main_core.Dom.create({
	      tag: 'div',
	      attrs: {
	        className: 'ui-notification-manager-browser-text'
	      },
	      text: this.getData().text
	    });
	  }
	  getIconNode() {
	    if (!main_core.Type.isStringFilled(this.getData().icon)) {
	      return '';
	    }
	    return main_core.Dom.create({
	      tag: 'div',
	      className: 'ui-notification-manager-browser-column',
	      children: [main_core.Dom.create({
	        tag: 'img',
	        style: {
	          height: '44px',
	          width: '44px'
	        },
	        attrs: {
	          className: 'ui-notification-manager-browser-icon',
	          src: this.getData().icon
	        }
	      })]
	    });
	  }
	  getActionsNode() {
	    const actions = this.getActions().map(action => action.getContainer());
	    if (!main_core.Type.isArrayFilled(actions)) {
	      return '';
	    }
	    return main_core.Tag.render(_t4 || (_t4 = _`
			<div class="ui-notification-manager-browser-actions">
				${0}
			</div>
		`), actions);
	  }
	  getUserInputContainerNode() {
	    if (!main_core.Type.isString(this.getData().inputPlaceholderText)) {
	      return '';
	    }
	    const onInputReplyClick = event => event.stopPropagation();
	    const id = main_core.Text.encode(this.getId());
	    const placeholderText = main_core.Text.encode(this.getData().inputPlaceholderText);
	    return main_core.Tag.render(_t5 || (_t5 = _`
			<div class="ui-notification-manager-browser-actions">
				<div class="ui-notification-manager-browser-column ui-notification-manager-browser-column-wide">
					<div class="ui-notification-manager-browser-row">
						<button
							class="ui-notification-manager-browser-button"
							id="ui-notification-manager-browser-reply-toggle-${0}"
							onclick="${0}"
						>
							<span class="ui-btn-text">${0}</span>
						</button>
					</div>
					<div
						class="ui-notification-manager-browser-row ui-notification-manager-browser-row-reply"
						id="ui-notification-manager-browser-reply-container-${0}"
					>
						<div class="ui-notification-manager-browser-reply-wrapper">
							<input
								type="text"
								class="ui-notification-manager-browser-input-reply"
								placeholder="${0}"
								id="ui-notification-manager-browser-reply-${0}"
								onkeyup="${0}"
								onclick="${0}"
								disabled
							>
						</div>
						<div
							class="ui-notification-manager-browser-button-reply"
							onclick="${0}"
						/>
					</div>
				</div>
			</div>
		`), id, this.toggleUserInputContainerNode.bind(this), main_core.Loc.getMessage('UI_NOTIFICATION_MANAGER_REPLY'), id, placeholderText, id, this.handleUserInputEnter.bind(this), onInputReplyClick, this.handleUserInputClick.bind(this));
	  }
	  toggleUserInputContainerNode(event) {
	    event.stopPropagation();
	    const id = main_core.Text.encode(this.getId());
	    if (!this.userInputContainerNode) {
	      this.userInputContainerNode = document.getElementById('ui-notification-manager-browser-reply-container-' + id);
	    }
	    if (!this.userInputNode) {
	      this.userInputNode = document.getElementById('ui-notification-manager-browser-reply-' + id);
	    }
	    if (!this.replyToggleButton) {
	      this.replyToggleButton = document.getElementById('ui-notification-manager-browser-reply-toggle-' + id);
	    }
	    this.showUserInput = !this.showUserInput;
	    if (this.showUserInput) {
	      this.setAutoHide(false);
	      this.deactivateAutoHide();
	      this.replyToggleButton.style.display = 'none';
	      this.userInputContainerNode.classList.add('ui-notification-manager-browser-row-reply-animate');
	      this.userInputNode.disabled = false;
	      this.userInputNode.focus();
	    } else {
	      this.setAutoHide(true);
	      this.activateAutoHide();
	      this.replyToggleButton.style.display = 'block';
	      this.userInputContainerNode.classList.remove('ui-notification-manager-browser-row-reply-animate');
	      this.userInputNode.disabled = true;
	    }
	  }
	  getCloseButtonNode() {
	    if (!this.isCloseButtonVisible()) {
	      return '';
	    }
	    return main_core.Tag.render(_t6 || (_t6 = _`
			<div
				class="ui-notification-manager-browser-button-close"
				onclick="${0}"
			/>
		`), this.handleCloseBtnClick.bind(this));
	  }
	  handleCloseBtnClick(event) {
	    event.stopPropagation();
	    if (main_core.Type.isFunction(this.getData().closedByUserHandler)) {
	      this.getData().closedByUserHandler();
	    }
	    super.handleCloseBtnClick();
	  }
	  handleContentClick() {
	    if (main_core.Type.isFunction(this.getData().clickHandler)) {
	      this.getData().clickHandler();
	    }
	    this.close();
	  }
	  handleContextClick(event) {
	    event.preventDefault();
	    if (main_core.Type.isFunction(this.getData().contextClickHandler)) {
	      this.getData().contextClickHandler();
	    }
	  }
	  handleUserInputEnter(event) {
	    if (!main_core.Type.isFunction(this.getData().userInputHandler)) {
	      return;
	    }
	    const userInput = event.target.value;
	    if (event.keyCode === BrowserNotification.KEY_CODE.ENTER && userInput !== '') {
	      this.getData().userInputHandler(userInput);
	      this.close();
	      return;
	    }
	    if (event.keyCode === BrowserNotification.KEY_CODE.ESC && userInput === '') {
	      if (main_core.Type.isFunction(this.getData().closedByUserHandler)) {
	        this.getData().closedByUserHandler();
	      }
	      this.close();
	    }
	  }
	  handleUserInputClick(event) {
	    event.stopPropagation();
	    if (!main_core.Type.isFunction(this.getData().userInputHandler)) {
	      return;
	    }
	    const userInput = this.userInputNode.value;
	    if (userInput !== '') {
	      this.getData().userInputHandler(userInput);
	      this.close();
	    }
	  }
	}
	BrowserNotification.KEY_CODE = {
	  ENTER: 13,
	  ESC: 27
	};

	class BrowserPageProvider extends BaseProvider {
	  constructor(options = {}) {
	    super(options);
	    this.broadcastChannel = null;
	    this.setBroadcast(options);
	  }
	  setBroadcast(options) {
	    this.broadcastChannel = new BroadcastChannel(BrowserPageProvider.BROADCAST_CHANNEL);
	    this.broadcastChannel.onmessage = event => this.handleMessageEvent(event);
	    this.postMessageToBroadcast(BrowserPageProvider.MESSAGE_TYPE.closeAllNotifications);
	  }
	  convertNotificationToNative(notification) {
	    if (!main_core.Type.isStringFilled(notification.getId())) {
	      throw new Error(`NotificationManager: You cannot send a notification without an ID.`);
	    }
	    const closedByUserHandler = () => {
	      this.notificationClose(notification.getUid(), NotificationCloseReason.CLOSED_BY_USER);
	    };
	    const clickHandler = () => {
	      this.notificationClick(notification.getUid());
	    };
	    const contextClickHandler = () => {
	      this.closeAllNotifications();
	    };
	    const userInputHandler = userInput => {
	      this.notificationAction(notification.getUid(), NotificationAction.BUTTON_1, userInput);
	    };
	    const balloonOptions = {
	      id: notification.getUid(),
	      category: notification.getCategory(),
	      type: BrowserNotification,
	      data: {
	        title: notification.getTitle(),
	        text: notification.getText(),
	        icon: notification.getIcon(),
	        closedByUserHandler,
	        clickHandler,
	        contextClickHandler,
	        userInputHandler
	      },
	      actions: [],
	      width: 380,
	      position: 'top-right',
	      autoHideDelay: BrowserPageProvider.autoHideDelay,
	      events: {
	        onClose: event => {
	          this.onBalloonClose(event);
	        }
	      }
	    };
	    if (notification.getInputPlaceholderText()) {
	      balloonOptions.data.inputPlaceholderText = notification.getInputPlaceholderText();
	      return balloonOptions;
	    }
	    const showButton1 = notification.getButton1Text() && main_core.Type.isStringFilled(notification.getButton1Text());
	    const showButton2 = notification.getButton2Text() && main_core.Type.isStringFilled(notification.getButton2Text());
	    if (showButton1) {
	      const action1Options = {
	        id: NotificationAction.BUTTON_1,
	        title: notification.getButton1Text(),
	        events: {
	          click: (event, balloon, action) => this.onNotificationAction(event, balloon, action)
	        }
	      };
	      if (showButton2) {
	        action1Options.buttonType = BrowserNotificationAction.TYPE_ACCEPT;
	      }
	      balloonOptions.actions.push(action1Options);
	    }
	    if (showButton2) {
	      const action2Options = {
	        id: NotificationAction.BUTTON_2,
	        title: notification.getButton2Text(),
	        events: {
	          click: (event, balloon, action) => this.onNotificationAction(event, balloon, action)
	        }
	      };
	      balloonOptions.actions.push(action2Options);
	    }
	    return balloonOptions;
	  }
	  onBalloonClose(event) {
	    const id = event.getBalloon().id;
	    this.postMessageToBroadcast(BrowserPageProvider.MESSAGE_TYPE.closeNotification, id);
	  }
	  postMessageToBroadcast(action, uid = '') {
	    if (action === BrowserPageProvider.MESSAGE_TYPE.closeNotification && !uid) {
	      return;
	    }
	    this.broadcastChannel.postMessage({
	      action,
	      ...(uid ? {
	        uid
	      } : {})
	    });
	  }
	  handleMessageEvent(event) {
	    if (event.data.action === BrowserPageProvider.MESSAGE_TYPE.closeNotification) {
	      const uid = event.data.uid;
	      const id = Notification.decodeUidToId(uid);
	      const balloon = this.findBalloonById(id);
	      if (balloon === null) {
	        return;
	      }
	      this.closeNotification(balloon);
	    } else if (event.data.action === BrowserPageProvider.MESSAGE_TYPE.closeAllNotifications) {
	      this.closeAllNotifications();
	    }
	  }
	  findBalloonById(id) {
	    const balloonsKeys = Object.keys(BX.UI.Notification.Center.balloons);
	    for (const uid of balloonsKeys) {
	      if (uid.startsWith(id)) {
	        return BX.UI.Notification.Center.balloons[uid];
	      }
	    }
	    return null;
	  }
	  closeNotification(balloon) {
	    this.notificationClose(balloon.id, NotificationCloseReason.CLOSED_BY_USER);
	    balloon.close();
	  }
	  closeAllNotifications() {
	    var _BX$UI$Notification$C;
	    (_BX$UI$Notification$C = BX.UI.Notification.Center.getDefaultStack()) == null ? void 0 : _BX$UI$Notification$C.clear();
	  }
	  sendNotification(notification) {
	    BX.UI.Notification.Center.notify(notification);
	  }
	  onNotificationAction(event, balloon, action) {
	    balloon.close();
	    this.notificationAction(balloon.id, action.id);
	  }
	}
	BrowserPageProvider.BROADCAST_CHANNEL = 'ui-notification-manager-channel';
	BrowserPageProvider.MESSAGE_TYPE = {
	  closeNotification: 'close-notification',
	  closeAllNotifications: 'close-all-notifications'
	};
	BrowserPageProvider.autoHideDelay = 6000;

	var _getBrowserPageProvider = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getBrowserPageProvider");
	/**
	 * @memberof BX.UI.NotificationManager
	 */
	class Notifier {
	  constructor() {
	    var _BX$PULL;
	    Object.defineProperty(this, _getBrowserPageProvider, {
	      value: _getBrowserPageProvider2
	    });
	    this.provider = this.createProvider();
	    (_BX$PULL = BX.PULL) == null ? void 0 : _BX$PULL.subscribe(new PullHandler());
	  }
	  createProvider() {
	    if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isMac() && DesktopHelper.geApiVersion() >= 73) {
	      return new MacProvider(Notifier.PROVIDER_OPTIONS);
	    }
	    if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isWindows()) {
	      return new WindowsProvider(Notifier.PROVIDER_OPTIONS);
	    }
	    if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isLinux()) {
	      return new LinuxProvider(Notifier.PROVIDER_OPTIONS);
	    }
	    if (BrowserHelper.isSupportedBrowser() && BrowserHelper.isNativeNotificationAllowed()) {
	      return new BrowserProvider(Notifier.PROVIDER_OPTIONS);
	    }
	    return babelHelpers.classPrivateFieldLooseBase(this, _getBrowserPageProvider)[_getBrowserPageProvider]();
	  }
	  notify(notificationOptions) {
	    const notification = new Notification(notificationOptions);
	    this.sendNotification(notification);
	  }
	  sendNotification(notification) {
	    this.provider.notify(notification);
	  }
	  notifyViaBrowserProvider(notificationOptions) {
	    const notification = new Notification(notificationOptions);
	    babelHelpers.classPrivateFieldLooseBase(this, _getBrowserPageProvider)[_getBrowserPageProvider]().notify(notification);
	  }
	  notifyViaDesktopProvider(notification) {
	    if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isMac()) {
	      new MacProvider().notify(notification);
	      return;
	    }
	    if (DesktopHelper.isSupportedDesktopApp() && DesktopHelper.isMac()) {
	      new WindowsProvider().notify(notification);
	      return;
	    }
	    throw new Error(`NotificationManager: unsupported environment for sending through a desktop provider.`);
	  }
	  subscribe(eventName, handler) {
	    if (!NotificationEvent.isSupported(eventName)) {
	      throw new Error(`NotificationManager: event "${eventName}" is not supported.`);
	    }
	    this.provider.subscribe(eventName, handler);
	    if (this.provider !== babelHelpers.classPrivateFieldLooseBase(this, _getBrowserPageProvider)[_getBrowserPageProvider]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _getBrowserPageProvider)[_getBrowserPageProvider]().subscribe(eventName, handler);
	    }
	  }
	}
	function _getBrowserPageProvider2() {
	  if (!this.browserProvider) {
	    this.browserProvider = new BrowserPageProvider(Notifier.PROVIDER_OPTIONS);
	  }
	  return this.browserProvider;
	}
	Notifier.EVENT_NAMESPACE = 'BX.UI.NotificationManager';
	Notifier.PROVIDER_OPTIONS = {
	  eventNamespace: Notifier.EVENT_NAMESPACE
	};
	const notifier = new Notifier();

	exports.Notifier = notifier;
	exports.Notification = Notification;

}((this.BX.UI.NotificationManager = this.BX.UI.NotificationManager || {}),BX,BX.Event,BX,BX,BX.UI));
//# sourceMappingURL=notification-manager.bundle.js.map
