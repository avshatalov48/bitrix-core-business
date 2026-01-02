/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_popup,pull_vue3_status,im_v2_lib_analytics,im_v2_component_messageList,im_v2_component_entitySelector,im_v2_lib_call,im_v2_lib_layout,im_v2_lib_access,im_v2_lib_feature,im_v2_provider_service_message,im_v2_provider_service_chat,main_core_events,im_v2_lib_logger,im_v2_lib_animation,im_v2_application_core,im_v2_lib_rest,im_v2_lib_channel,im_v2_lib_demo,im_v2_lib_permission,im_v2_component_elements_avatar,im_v2_lib_parser,main_core,im_v2_lib_quote,im_v2_lib_utils,im_v2_lib_slider,im_v2_const) {
	'use strict';

	const DEBOUNCE_TIME = 50;
	var _store = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("store");
	var _timerBeforeSendRequest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("timerBeforeSendRequest");
	var _batchReadMessageAnchors = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("batchReadMessageAnchors");
	class AnchorService {
	  constructor() {
	    Object.defineProperty(this, _batchReadMessageAnchors, {
	      value: _batchReadMessageAnchors2
	    });
	    Object.defineProperty(this, _store, {
	      writable: true,
	      value: void 0
	    });
	    this.messagesToRead = new Set();
	    Object.defineProperty(this, _timerBeforeSendRequest, {
	      writable: true,
	      value: null
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store] = im_v2_application_core.Core.getStore();
	  }
	  readChatAnchors(chatId) {
	    im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatAnchorRead, {
	      data: {
	        chatId
	      }
	    }).then(() => {
	      const userId = im_v2_application_core.Core.getUserId();
	      babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/anchors/removeChatAnchors', {
	        userId,
	        chatId
	      });
	    }).catch(error => {
	      console.error('AnchorService: read chat anchors error', error);
	    });
	  }
	  debouncedReadMessageAnchors(messageId) {
	    const userId = im_v2_application_core.Core.getUserId();
	    this.messagesToRead.add(messageId);
	    babelHelpers.classPrivateFieldLooseBase(this, _store)[_store].dispatch('messages/anchors/removeUserAnchorsFromMessage', {
	      userId,
	      messageId
	    });
	    if (babelHelpers.classPrivateFieldLooseBase(this, _timerBeforeSendRequest)[_timerBeforeSendRequest]) {
	      clearTimeout(babelHelpers.classPrivateFieldLooseBase(this, _timerBeforeSendRequest)[_timerBeforeSendRequest]);
	      babelHelpers.classPrivateFieldLooseBase(this, _timerBeforeSendRequest)[_timerBeforeSendRequest] = null;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _timerBeforeSendRequest)[_timerBeforeSendRequest] = setTimeout(() => {
	      return babelHelpers.classPrivateFieldLooseBase(this, _batchReadMessageAnchors)[_batchReadMessageAnchors]();
	    }, DEBOUNCE_TIME);
	    return Promise.resolve();
	  }
	}
	function _batchReadMessageAnchors2() {
	  if (this.messagesToRead.size === 0) {
	    return;
	  }
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2AnchorRead, {
	    data: {
	      messageIds: [...this.messagesToRead]
	    }
	  }).catch(error => {
	    console.error('AnchorService: read anchor error', error);
	  });
	  this.messagesToRead.clear();
	}

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Dialog.ScrollManager';
	var _getScrollPosition = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getScrollPosition");
	class ScrollManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _getScrollPosition, {
	      value: _getScrollPosition2
	    });
	    this.isScrolling = false;
	    this.currentScroll = 0;
	    this.lastScroll = 0;
	    this.chatIsScrolledUp = false;
	    this.scrollButtonClicked = false;
	    this.startScrollNeeded = true;
	    this.setEventNamespace(EVENT_NAMESPACE);
	  }
	  setContainer(container) {
	    this.container = container;
	  }
	  onScroll(event) {
	    if (this.isScrolling || !event.target) {
	      return;
	    }
	    this.currentScroll = event.target.scrollTop;
	    const isScrollingDown = this.lastScroll < this.currentScroll;
	    const isScrollingUp = !isScrollingDown;
	    if (isScrollingUp) {
	      this.scrollButtonClicked = false;
	    }
	    const SCROLLING_THRESHOLD = 1500;
	    const leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
	    if (isScrollingDown && this.isStartScrollCompleted() && leftSpaceBottom < SCROLLING_THRESHOLD) {
	      this.emit(ScrollManager.events.onScrollTriggerDown);
	    } else if (isScrollingUp && this.currentScroll <= SCROLLING_THRESHOLD) {
	      this.emit(ScrollManager.events.onScrollTriggerUp);
	    }
	    this.lastScroll = this.currentScroll;
	    this.checkIfChatIsScrolledUp();
	  }
	  checkIfChatIsScrolledUp() {
	    const SCROLLED_UP_THRESHOLD = 400;
	    const availableScrollHeight = this.container.scrollHeight - this.container.clientHeight;
	    const newFlag = this.currentScroll + SCROLLED_UP_THRESHOLD < availableScrollHeight;
	    if (newFlag !== this.chatIsScrolledUp) {
	      this.emit(ScrollManager.events.onScrollThresholdPass, newFlag);
	    }
	    this.chatIsScrolledUp = newFlag;
	  }
	  scrollToBottom() {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: scroll to bottom');
	    this.forceScrollTo(this.container.scrollHeight - this.container.clientHeight);
	  }
	  animatedScrollToBottom() {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: animated scroll to bottom');
	    this.animatedScrollTo(this.container.scrollHeight - this.container.clientHeight);
	  }
	  scrollToMessage(messageId, params = {}) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: scroll to message - ', messageId);
	    const element = this.getDomElementById(messageId);
	    if (!element) {
	      im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
	      return;
	    }
	    const scrollPosition = babelHelpers.classPrivateFieldLooseBase(this, _getScrollPosition)[_getScrollPosition](element, params);
	    this.forceScrollTo(scrollPosition);
	  }
	  setStartScrollNeeded(flag) {
	    this.startScrollNeeded = flag;
	  }
	  isStartScrollCompleted() {
	    if (!this.startScrollNeeded) {
	      return true;
	    }
	    return this.lastScroll > 0;
	  }
	  animatedScrollToMessage(messageId, params = {}) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: animated scroll to message - ', messageId);
	    const element = this.getDomElementById(messageId);
	    if (!element) {
	      im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
	      return Promise.resolve();
	    }
	    const scrollPosition = babelHelpers.classPrivateFieldLooseBase(this, _getScrollPosition)[_getScrollPosition](element, params);
	    return this.animatedScrollTo(scrollPosition);
	  }
	  forceScrollTo(position) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: Force scroll to - ', position);
	    this.cancelAnimatedScroll();
	    this.container.scroll({
	      top: position,
	      behavior: 'instant'
	    });
	  }
	  adjustScrollOnHistoryAddition(oldContainerHeight) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: Adjusting scroll after history addition');
	    const newContainerHeight = this.container.scrollHeight - this.container.clientHeight;
	    const newScrollPosition = this.container.scrollTop + newContainerHeight - oldContainerHeight;
	    this.forceScrollTo(newScrollPosition);
	  }
	  animatedScrollTo(position) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: Animated scroll to - ', position);
	    return new Promise(resolve => {
	      im_v2_lib_animation.Animation.start({
	        start: this.container.scrollTop,
	        end: position,
	        element: this.container,
	        elementProperty: 'scrollTop',
	        callback: () => {
	          this.checkIfChatIsScrolledUp();
	          resolve();
	        }
	      });
	    });
	  }
	  cancelAnimatedScroll() {
	    if (!this.isScrolling) {
	      return;
	    }
	    im_v2_lib_animation.Animation.cancel();
	    this.isScrolling = false;
	  }
	  isAtTheTop() {
	    return this.container.scrollTop === 0;
	  }
	  isAtTheBottom() {
	    return this.container.scrollTop + this.container.clientHeight >= this.container.scrollHeight;
	  }
	  isAroundBottom() {
	    const POSITION_THRESHOLD = 40;
	    return this.container.scrollHeight - this.container.scrollTop - this.container.clientHeight < POSITION_THRESHOLD;
	  }
	  getDomElementById(id) {
	    return this.container.querySelector(`[data-id="${id}"]`);
	  }
	}
	function _getScrollPosition2(element, params = {}) {
	  const FLOATING_DATE_OFFSET = 52;
	  const MESSAGE_BOTTOM_OFFSET = 100;
	  const {
	    withDateOffset = true,
	    position = ScrollManager.scrollPosition.messageTop
	  } = params;
	  const offset = withDateOffset ? -FLOATING_DATE_OFFSET : -10;
	  let scrollPosition = element.offsetTop + offset;
	  if (position === ScrollManager.scrollPosition.messageBottom) {
	    scrollPosition += element.clientHeight - MESSAGE_BOTTOM_OFFSET;
	  } else if (position === ScrollManager.scrollPosition.messageCenter && this.container.clientHeight > element.clientHeight) {
	    scrollPosition = scrollPosition - this.container.clientHeight / 2 + element.clientHeight / 2;
	  }
	  return scrollPosition;
	}
	ScrollManager.events = {
	  onScrollTriggerUp: 'onScrollTriggerUp',
	  onScrollTriggerDown: 'onScrollTriggerDown',
	  onScrollThresholdPass: 'onScrollThresholdPass'
	};
	ScrollManager.scrollPosition = {
	  messageTop: 'messageTop',
	  messageBottom: 'messageBottom',
	  messageCenter: 'messageCenter'
	};

	const MESSAGES_TAG_PREFIX = 'IM_PUBLIC_';
	const COMMENTS_TAG_PREFIX = 'IM_PUBLIC_COMMENT_';
	var _dialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialog");
	var _pullClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pullClient");
	var _subscribeChannel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeChannel");
	var _subscribeOpenChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("subscribeOpenChat");
	var _requestWatchStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestWatchStart");
	var _isGuest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isGuest");
	var _isDemoChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isDemoChat");
	var _isChannel = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isChannel");
	var _isCommentsChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isCommentsChat");
	class PullWatchManager {
	  constructor(dialogId) {
	    Object.defineProperty(this, _isCommentsChat, {
	      value: _isCommentsChat2
	    });
	    Object.defineProperty(this, _isChannel, {
	      value: _isChannel2
	    });
	    Object.defineProperty(this, _isDemoChat, {
	      value: _isDemoChat2
	    });
	    Object.defineProperty(this, _isGuest, {
	      value: _isGuest2
	    });
	    Object.defineProperty(this, _requestWatchStart, {
	      value: _requestWatchStart2
	    });
	    Object.defineProperty(this, _subscribeOpenChat, {
	      value: _subscribeOpenChat2
	    });
	    Object.defineProperty(this, _subscribeChannel, {
	      value: _subscribeChannel2
	    });
	    Object.defineProperty(this, _dialog, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pullClient, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog] = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId, true);
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient] = im_v2_application_core.Core.getPullClient();
	  }
	  subscribe() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _isChannel)[_isChannel]()) {
	      babelHelpers.classPrivateFieldLooseBase(this, _subscribeChannel)[_subscribeChannel]();
	      return;
	    }
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isGuest)[_isGuest]() || babelHelpers.classPrivateFieldLooseBase(this, _isCommentsChat)[_isCommentsChat]() || babelHelpers.classPrivateFieldLooseBase(this, _isDemoChat)[_isDemoChat]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _subscribeOpenChat)[_subscribeOpenChat]();
	  }
	  unsubscribe() {
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].clearWatch(`${MESSAGES_TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].clearWatch(`${COMMENTS_TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	  }
	}
	function _subscribeChannel2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _requestWatchStart)[_requestWatchStart]();
	  babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(`${MESSAGES_TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	  babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(`${COMMENTS_TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	}
	function _subscribeOpenChat2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _requestWatchStart)[_requestWatchStart]();
	  babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(`${MESSAGES_TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	}
	function _requestWatchStart2() {
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatExtendPullWatch, {
	    data: {
	      dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].dialogId
	    }
	  });
	}
	function _isGuest2() {
	  var _babelHelpers$classPr;
	  return ((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog]) == null ? void 0 : _babelHelpers$classPr.role) === im_v2_const.UserRole.guest;
	}
	function _isDemoChat2() {
	  var _babelHelpers$classPr2;
	  return im_v2_lib_demo.DemoChatBuilder.isDemoDialogId((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog]) == null ? void 0 : _babelHelpers$classPr2.dialogId);
	}
	function _isChannel2() {
	  var _babelHelpers$classPr3;
	  return im_v2_lib_channel.ChannelManager.isChannel((_babelHelpers$classPr3 = babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog]) == null ? void 0 : _babelHelpers$classPr3.dialogId);
	}
	function _isCommentsChat2() {
	  var _babelHelpers$classPr4;
	  return ((_babelHelpers$classPr4 = babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog]) == null ? void 0 : _babelHelpers$classPr4.type) === im_v2_const.ChatType.comment;
	}

	var _visibleMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("visibleMessages");
	class VisibleMessagesManager {
	  constructor() {
	    Object.defineProperty(this, _visibleMessages, {
	      writable: true,
	      value: new Set()
	    });
	  }
	  setMessageAsVisible(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].add(messageId);
	  }
	  setMessageAsNotVisible(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].delete(messageId);
	  }
	  getVisibleMessages() {
	    return [...babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages]];
	  }
	  getFirstMessageId() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].size === 0) {
	      return 0;
	    }
	    const [firstVisibleMessage] = [...babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages]].sort((a, b) => a - b);
	    return firstVisibleMessage;
	  }
	}

	function findUniqueNumbers(arr1, arr2) {
	  const set1 = new Set(arr1);
	  const set2 = new Set(arr2);
	  return [...arr1.filter(num => !set2.has(num)), ...arr2.filter(num => !set1.has(num))];
	}

	const sharedQueues = new Map();

	/**
	 * @description
	 * Adds a delay between calls to the fn function.
	 * Calls with the same function execute this function in the same sequence
	 * @param fn
	 * @param delay
	 * @param context
	 * @returns Function
	 */
	function sequentializeShared(fn, delay, context = null) {
	  if (sharedQueues.has(fn)) {
	    return sharedQueues.get(fn);
	  }
	  const wrapped = sequentialize(fn, delay, context);
	  sharedQueues.set(fn, wrapped);
	  return wrapped;
	}
	function sequentialize(fn, delay, context = null) {
	  const queue = [];
	  let isRunning = false;
	  async function run() {
	    if (isRunning) {
	      return;
	    }
	    isRunning = true;
	    const task = queue.shift();
	    if (!task) {
	      isRunning = false;
	      return;
	    }
	    const result = fn.apply(context, task.args);
	    await wait(delay);
	    task.resolve(result);
	    isRunning = false;
	    run();
	  }
	  return (...args) => {
	    return new Promise(resolve => {
	      queue.push({
	        args,
	        resolve
	      });
	      run();
	    });
	  };
	}
	function wait(ms) {
	  return new Promise(resolve => {
	    setTimeout(resolve, ms);
	  });
	}

	// @vue/component
	const PinnedMessage = {
	  components: {
	    MessageAvatar: im_v2_component_elements_avatar.MessageAvatar
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    showUnpinIcon: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['messageUnpin'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    typedMessage() {
	      return this.message;
	    },
	    text() {
	      return im_v2_lib_parser.Parser.purifyMessage(this.typedMessage);
	    },
	    authorId() {
	      return this.typedMessage.authorId;
	    },
	    author() {
	      return this.$store.getters['users/get'](this.authorId);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__pinned_item">
			<MessageAvatar
				v-if="typedMessage.authorId"
				:messageId="typedMessage.id"
				:authorId="typedMessage.authorId"
				:size="AvatarSize.M"
			/>
			<div class="bx-im-dialog-chat__pinned_item_content">
				<div v-if="author" class="bx-im-dialog-chat__pinned_item_user">
					{{ author.name }}
				</div>
				<div class="bx-im-dialog-chat__pinned_item_text --ellipsis">
					{{ text }}
				</div>
			</div>
			<button
				v-if="showUnpinIcon"
				class="bx-im-dialog-chat__pinned_icon-item-unpin"
				@click.stop="$emit('messageUnpin', typedMessage.id)"
			></button>
		</div>
	`
	};

	// @vue/component
	const HeaderTitle = {
	  name: 'HeaderTitle',
	  props: {
	    totalPinCounter: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['toggleList'],
	  computed: {
	    title() {
	      return this.loc('IM_DIALOG_CHAT_PINNED_TITLE_MULTIPLE_COUNTER', {
	        '#PINS_COUNT#': `
						<span class="bx-im-dialog-chat__pin-header_counter-total">
							${this.totalPinCounter}
						</span>
					`
	      });
	    }
	  },
	  methods: {
	    loc(phraseCode, replacements = {}) {
	      return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__pin-header_opened">
		    <div v-html="title"></div>
			<button
				class="bx-im-messenger__cross-icon"
				@click="$emit('toggleList')"
			></button>
		</div>
	`
	};

	// @vue/component
	const CounterControl = {
	  name: 'CounterControl',
	  props: {
	    messagePosition: {
	      type: Number,
	      required: true
	    },
	    totalPinCounter: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['toggleList'],
	  template: `
		<button
			@click="$emit('toggleList')"
			class="bx-im-dialog-chat__pinned_counter_control"
		>
			<span class="bx-im-dialog-chat__pinned_icon-dropdown"></span>
			<span>
				{{ messagePosition }}
				<span class="bx-im-dialog-chat__pinned_counter_control-total">
					/ {{ totalPinCounter }}
				</span>
			</span>
		</button>
	`
	};

	// @vue/component
	const HeaderPin = {
	  name: 'HeaderPin',
	  components: {
	    CounterControl
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    messagePosition: {
	      type: Number,
	      required: true
	    },
	    showUnpinIcon: {
	      type: Boolean,
	      required: true
	    },
	    totalPinCounter: {
	      type: Number,
	      required: true
	    }
	  },
	  emits: ['toggleList', 'messageUnpin', 'messageClick'],
	  computed: {
	    typedMessage() {
	      return this.message;
	    },
	    isSinglePin() {
	      return this.totalPinCounter === 1;
	    },
	    authorId() {
	      return this.typedMessage.authorId;
	    },
	    author() {
	      return this.$store.getters['users/get'](this.authorId);
	    },
	    text() {
	      return im_v2_lib_parser.Parser.purifyMessage(this.typedMessage);
	    },
	    title() {
	      return this.loc(this.isSinglePin ? 'IM_DIALOG_CHAT_PINNED_TITLE' : 'IM_DIALOG_CHAT_PINNED_TITLE_MULTIPLE');
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__pin-header">
			<div
				class="bx-im-dialog-chat__pin-header_wrapper"
				@click="$emit('messageClick', typedMessage.id)"
			>
				<div class="bx-im-dialog-chat__pin-header_title">
					{{ title }}
				</div>
				<div class="bx-im-dialog-chat__pin-header_content">
					<div v-if="author" class="bx-im-dialog-chat__pin-header_user">
						{{ author.name + ':' }}
					</div>
					<div class="bx-im-dialog-chat__pin-header_text --ellipsis">
						{{ text }}
					</div>
				</div>
			</div>

			<div class="bx-im-dialog-chat__pin-header_controls">
				<button
					v-if="showUnpinIcon && isSinglePin"
					class="bx-im-dialog-chat__pinned_icon-header-unpin"
					@click="$emit('messageUnpin', typedMessage.id)"
				></button>
				<CounterControl
					v-else-if="!isSinglePin"
					:messagePosition="messagePosition"
					:totalPinCounter="totalPinCounter"
					@toggleList="$emit('toggleList')"
				/>
			</div>
		</div>
	`
	};

	// @vue/component
	const PinnedHeader = {
	  name: 'PinnedHeader',
	  components: {
	    HeaderTitle,
	    HeaderPin
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    messagePosition: {
	      type: Number,
	      required: true
	    },
	    showUnpinIcon: {
	      type: Boolean,
	      required: true
	    },
	    totalPinCounter: {
	      type: Number,
	      required: true
	    },
	    isListOpened: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['toggleList', 'messageUnpin', 'messageClick'],
	  computed: {
	    typedMessage() {
	      return this.message;
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__pinned_header">
			<HeaderTitle
				v-if="isListOpened"
				:totalPinCounter="totalPinCounter"
				@toggleList="$emit('toggleList')"
			/>
			<HeaderPin
				v-else
				:message="typedMessage"
				:messagePosition="messagePosition"
				:totalPinCounter="totalPinCounter"
				:showUnpinIcon="showUnpinIcon"
				@toggleList="$emit('toggleList')"
				@messageUnpin="$emit('messageUnpin', typedMessage.id)"
				@messageClick="$emit('messageClick', typedMessage.id)"
			/>
		</div>
	`
	};

	// @vue/component
	const PinnedMessages = {
	  name: 'PinnedMessages',
	  components: {
	    PinnedMessage,
	    PinnedHeader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    messages: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['messageClick', 'messageUnpin'],
	  data() {
	    return {
	      isListOpened: false,
	      upcomingMessageIndex: 0
	    };
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    sortedPinnedMessages() {
	      return [...this.messages].sort((a, b) => b.id - a.id);
	    },
	    totalPinCounter() {
	      return this.messages.length;
	    },
	    canUnpin() {
	      return im_v2_lib_permission.PermissionManager.getInstance().canPerformActionByRole(im_v2_const.ActionByRole.pinMessage, this.dialogId);
	    },
	    showUnpinIcon() {
	      return !this.isCommentChat && this.canUnpin;
	    },
	    isCommentChat() {
	      return this.dialog.type === im_v2_const.ChatType.comment;
	    },
	    upcomingMessage() {
	      return this.sortedPinnedMessages[this.upcomingMessageIndex];
	    },
	    upcomingMessageDisplayPosition() {
	      return this.upcomingMessageIndex + 1;
	    }
	  },
	  watch: {
	    messages(newValue) {
	      if (newValue.length === 1) {
	        this.toggleList(false);
	      }
	      if (this.shouldResetIndex(this.upcomingMessageIndex)) {
	        this.resetHeaderIndex();
	      }
	    }
	  },
	  methods: {
	    toggleList(flag) {
	      this.isListOpened = main_core.Type.isUndefined(flag) ? !this.isListOpened : flag;
	    },
	    incrementHeaderIndex() {
	      const nextIndex = this.upcomingMessageIndex + 1;
	      if (this.shouldResetIndex(nextIndex)) {
	        this.resetHeaderIndex();
	      } else {
	        this.upcomingMessageIndex = nextIndex;
	      }
	    },
	    resetHeaderIndex() {
	      this.upcomingMessageIndex = 0;
	    },
	    shouldResetIndex(index) {
	      return index >= this.totalPinCounter;
	    },
	    clickOnHeaderMessage() {
	      this.emitMessageClick(this.sortedPinnedMessages[this.upcomingMessageIndex].id);
	      this.incrementHeaderIndex();
	    },
	    clickOnHeaderMessageFromList(index) {
	      // manually setting the next index because the item is selected directly from the list
	      const nextIndex = index + 1;
	      this.upcomingMessageIndex = this.shouldResetIndex(nextIndex) ? 0 : nextIndex;
	      this.emitMessageClick(this.sortedPinnedMessages[index].id);
	    },
	    emitMessageClick(messageId) {
	      this.$emit('messageClick', messageId);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__pinned_container">
			<PinnedHeader
				:message="upcomingMessage"
				:messagePosition="upcomingMessageDisplayPosition"
				:showUnpinIcon="showUnpinIcon"
				:totalPinCounter="totalPinCounter"
				:isListOpened="isListOpened"
				@toggleList="toggleList"
				@messageUnpin="$emit('messageUnpin', upcomingMessage.id)"
				@messageClick="clickOnHeaderMessage"
			/>
			<transition name="pinned-list">
				<div v-if="isListOpened" class="bx-im-dialog-chat__pinned_list">
					<PinnedMessage
						v-for="(message, index) in sortedPinnedMessages"
						:key="message.id"
						:message="message"
						:showUnpinIcon="showUnpinIcon"
						@messageUnpin="$emit('messageUnpin', message.id)"
						@click="clickOnHeaderMessageFromList(index)"
					/>
				</div>
			</transition>
		</div>
	`
	};

	var _sliderRect$top;
	const CONTAINER_HEIGHT = 44;
	const CONTAINER_WIDTH = 60;
	const CONTAINER_OFFSET = 10;
	const slider = im_v2_lib_slider.MessengerSlider.getInstance().getCurrent();
	const sliderRect = slider == null ? void 0 : slider.layout.container.getBoundingClientRect();
	const offsetY = (_sliderRect$top = sliderRect == null ? void 0 : sliderRect.top) != null ? _sliderRect$top : 0;
	const MESSAGE_TEXT_NODE_CLASS = '.bx-im-message-default-content__text';

	// @vue/component
	const QuoteButton = {
	  name: 'QuoteButton',
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    }
	  },
	  data() {
	    return {
	      text: '',
	      message: null,
	      mouseX: 0,
	      mouseY: 0
	    };
	  },
	  computed: {
	    containerStyle() {
	      return {
	        top: `${this.mouseY - CONTAINER_HEIGHT - CONTAINER_OFFSET - offsetY}px`,
	        left: `${this.mouseX - CONTAINER_WIDTH / 2}px`,
	        width: `${CONTAINER_WIDTH}px`,
	        height: `${CONTAINER_HEIGHT}px`
	      };
	    }
	  },
	  mounted() {
	    main_core.Event.bind(window, 'mousedown', this.onMouseDown);
	  },
	  methods: {
	    onMessageMouseUp(message, event) {
	      if (event.button === 2) {
	        return;
	      }
	      this.prepareSelectedText();
	      this.message = message;
	      this.mouseX = event.clientX;
	      this.mouseY = event.clientY;
	    },
	    onMouseDown(event) {
	      const container = this.$refs.container;
	      if (!container || container.contains(event.target)) {
	        return;
	      }
	      this.$emit('close');
	    },
	    prepareSelectedText() {
	      if (im_v2_lib_utils.Utils.browser.isFirefox()) {
	        this.text = window.getSelection().toString();
	        return;
	      }
	      const range = window.getSelection().getRangeAt(0);
	      const rangeContents = range.cloneContents();
	      let nodesToIterate = rangeContents.childNodes;
	      const messageNode = rangeContents.querySelector(MESSAGE_TEXT_NODE_CLASS);
	      if (messageNode) {
	        nodesToIterate = messageNode.childNodes;
	      }
	      for (const node of nodesToIterate) {
	        if (this.isImage(node)) {
	          var _node$getAttribute;
	          this.text += (_node$getAttribute = node.getAttribute('data-code')) != null ? _node$getAttribute : node.getAttribute('alt');
	        } else if (this.isLineBreak(node)) {
	          this.text += '\n';
	        } else {
	          this.text += node.textContent;
	        }
	      }
	    },
	    isImage(node) {
	      if (!(node instanceof HTMLElement)) {
	        return false;
	      }
	      return node.tagName.toLowerCase() === 'img';
	    },
	    isLineBreak(node) {
	      return node.nodeName.toLowerCase() === 'br';
	    },
	    isText(node) {
	      return node.nodeName === '#text';
	    },
	    isMessageTextNode(node) {
	      if (!(node instanceof HTMLElement)) {
	        return false;
	      }
	      const textNode = node.matches(MESSAGE_TEXT_NODE_CLASS);
	      return Boolean(textNode);
	    },
	    extractTextFromMessageNode(node) {
	      const textNode = node.querySelector(MESSAGE_TEXT_NODE_CLASS);
	      if (!textNode) {
	        return node.textContent;
	      }
	      return textNode.textContent;
	    },
	    onQuoteClick() {
	      im_v2_lib_quote.Quote.sendQuoteEvent(this.message, this.text, this.dialogId);
	      this.$emit('close');
	    }
	  },
	  template: `
		<div ref="container" @click="onQuoteClick" :style="containerStyle" class="bx-im-dialog-chat__quote-button">
			<div class="bx-im-dialog-chat__quote-icon"></div>
			<div class="bx-im-dialog-chat__quote-icon --hover"></div>
		</div>
	`
	};

	const FloatButtonColor = Object.freeze({
	  accent: 'accent',
	  alert: 'alert',
	  success: 'success'
	});
	const FloatButtonIcon = Object.freeze({
	  chevronDown: 'chevron-down',
	  heart: 'heart',
	  atSymbol: 'at-symbol',
	  comment: 'comment'
	});

	// @vue/component
	const FloatButton = {
	  name: 'FloatButton',
	  props: {
	    counter: {
	      type: Number,
	      default: 0
	    },
	    color: {
	      type: String,
	      required: false,
	      default: FloatButtonColor.accent,
	      validator: value => {
	        return Object.values(FloatButtonColor).includes(value);
	      }
	    },
	    icon: {
	      type: String,
	      required: true,
	      validator: value => {
	        return Object.values(FloatButtonIcon).includes(value);
	      }
	    }
	  },
	  computed: {
	    buttonClassname() {
	      return ['bx-im-dialog-chat__float-button', `--color-${this.color}`, `--icon-${this.icon}`];
	    },
	    formattedCounter() {
	      if (this.counter === 0) {
	        return '';
	      }
	      if (this.counter > 99) {
	        return '99+';
	      }
	      return String(this.counter);
	    }
	  },
	  template: `
		<div :class="buttonClassname">
			<div class="bx-im-dialog-chat__float-button_icon"></div>
			<div v-if="counter" class="bx-im-dialog-chat__float-button_counter">
				{{ formattedCounter }}
			</div>
		</div>
	`
	};

	// @vue/component
	const ScrollButton = {
	  name: 'ScrollButton',
	  components: {
	    FloatButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    floatButtonProps() {
	      return {
	        color: FloatButtonColor.accent,
	        icon: FloatButtonIcon.chevronDown,
	        counter: this.dialog.counter
	      };
	    }
	  },
	  template: `
		<FloatButton v-bind="floatButtonProps" />
	`
	};

	const ReactionsButton = {
	  name: 'ReactionsButton',
	  components: {
	    FloatButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    reactionCounter() {
	      return this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, im_v2_const.AnchorType.reaction);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    floatButtonProps() {
	      return {
	        color: FloatButtonColor.alert,
	        icon: FloatButtonIcon.heart,
	        counter: this.reactionCounter
	      };
	    }
	  },
	  template: `
		<FloatButton v-bind="floatButtonProps" />
	`
	};

	// @vue/component
	const MentionsButton = {
	  name: 'MentionsButton',
	  components: {
	    FloatButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    mentionCounter() {
	      return this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, im_v2_const.AnchorType.mention);
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    floatButtonProps() {
	      return {
	        color: FloatButtonColor.accent,
	        icon: FloatButtonIcon.atSymbol,
	        counter: this.mentionCounter
	      };
	    }
	  },
	  template: `
		<FloatButton v-bind="floatButtonProps" />
	`
	};

	// @vue/component
	const FloatButtons = {
	  components: {
	    ScrollButton,
	    ReactionsButton,
	    MentionsButton
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    isScrolledUp: {
	      type: Boolean,
	      required: false,
	      default: false
	    }
	  },
	  emits: ['scrollButtonClick', 'reactionsButtonClick', 'mentionsButtonClick'],
	  computed: {
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    showScrollButton() {
	      return this.isScrolledUp || this.dialog.hasNextPage;
	    },
	    showMentionsButton() {
	      return this.dialogInited && this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, im_v2_const.AnchorType.mention);
	    },
	    showReactionsButton() {
	      return this.dialogInited && this.$store.getters['messages/anchors/getCounterInChatByType'](this.dialog.chatId, im_v2_const.AnchorType.reaction);
	    },
	    hasAdditionalButtonSlot() {
	      var _slotChildren$;
	      const slot = this.$slots['additional-float-button'];
	      if (!slot) {
	        return false;
	      }
	      const slotChildren = slot();

	      /**
	       * Check that slot has empty content.
	       * Used for case when parent component passes a slot to this slot
	       * This is important for the animation to work correctly
	       */
	      return ((_slotChildren$ = slotChildren[0]) == null ? void 0 : _slotChildren$.children.length) > 0;
	    }
	  },
	  methods: {
	    onReactionsButtonClick() {
	      this.$emit('reactionsButtonClick');
	    },
	    onMentionsButtonClick() {
	      this.$emit('mentionsButtonClick');
	    },
	    onScrollButtonClick() {
	      this.$emit('scrollButtonClick');
	    }
	  },
	  template: `
		<TransitionGroup
			name="float-button-transition"
			tag="div"
			class="bx-im-dialog-chat__float-buttons"
		>
			<div
				v-if="showReactionsButton"
				key="reaction"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<ReactionsButton :dialogId="dialogId" @click="onReactionsButtonClick" />
			</div>
			<div
				v-if="showMentionsButton"
				key="mention"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<MentionsButton :dialogId="dialogId" @click="onMentionsButtonClick" />
			</div>
			<div
				v-if="hasAdditionalButtonSlot"
				key="additionalButton"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<slot name="additional-float-button"></slot>
			</div>
			<div
				v-if="showScrollButton"
				key="scroll"
				class="bx-im-dialog-chat__float-buttons_button"
			>
				<ScrollButton :dialogId="dialogId" @click="onScrollButtonClick" />
			</div>
		</TransitionGroup>
	`
	};

	// @vue/component
	const ChatDialog = {
	  name: 'ChatDialog',
	  components: {
	    MessageList: im_v2_component_messageList.MessageList,
	    PinnedMessages,
	    QuoteButton,
	    FloatButtons,
	    PullStatus: pull_vue3_status.PullStatus,
	    ForwardPopup: im_v2_component_entitySelector.ForwardPopup
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    saveScrollOnExit: {
	      type: Boolean,
	      default: true
	    },
	    reloadOnExit: {
	      type: Boolean,
	      default: true
	    },
	    clearOnExit: {
	      type: Boolean,
	      default: false
	    }
	  },
	  data() {
	    return {
	      forwardPopup: {
	        show: false,
	        messagesIds: []
	      },
	      contextMode: {
	        active: false,
	        messageIsLoaded: false
	      },
	      isScrolledUp: false,
	      windowFocused: false,
	      showQuoteButton: false,
	      isJumpingToAnchor: false,
	      messagesToRead: new Set(),
	      containerHeight: 0
	    };
	  },
	  computed: {
	    layout() {
	      return this.$store.getters['application/getLayout'];
	    },
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    dialogInited() {
	      return this.dialog.inited;
	    },
	    messageCollection() {
	      return this.$store.getters['messages/getByChatId'](this.dialog.chatId);
	    },
	    pinnedMessages() {
	      return this.$store.getters['messages/pin/getPinned'](this.dialog.chatId);
	    },
	    isOpened() {
	      const openedDialogId = this.$store.getters['application/getLayout'].entityId;
	      return this.dialogId === openedDialogId;
	    },
	    isGuest() {
	      return this.dialog.role === im_v2_const.UserRole.guest;
	    },
	    debouncedScrollHandler() {
	      const SCROLLING_DEBOUNCE_DELAY = 100;
	      return main_core.Runtime.debounce(this.getScrollManager().onScroll, SCROLLING_DEBOUNCE_DELAY, this.getScrollManager());
	    },
	    debouncedReadHandler() {
	      const READING_DEBOUNCE_DELAY = 50;
	      return main_core.Runtime.debounce(this.readQueuedMessages, READING_DEBOUNCE_DELAY, this);
	    },
	    sequentiallyHighlightMessageHandler() {
	      return sequentializeShared(this.highlightMessage, 300, this);
	    },
	    showScrollButton() {
	      return this.isScrolledUp || this.dialog.hasNextPage;
	    },
	    anchorMessages() {
	      return this.$store.getters['messages/anchors/getChatMessageIdsWithAnchors'](this.dialog.chatId);
	    },
	    hasCommentsOnTop() {
	      return this.$store.getters['messages/comments/areOpenedForChannel'](this.dialogId);
	    }
	  },
	  watch: {
	    anchorMessages(newValue, oldValue) {
	      const newMessageIdsWithAnchor = findUniqueNumbers(newValue, oldValue);
	      const visibleMessageIds = this.getVisibleMessagesManager().getVisibleMessages();
	      newMessageIdsWithAnchor.forEach(messageId => {
	        if (visibleMessageIds.includes(messageId)) {
	          this.getAnchorService().debouncedReadMessageAnchors(messageId);
	        }
	      });
	    },
	    dialogInited(newValue, oldValue) {
	      if (!newValue || oldValue) {
	        return;
	      }
	      // first opening
	      this.getPullWatchManager().subscribe();
	      this.onChatInited();
	    },
	    hasCommentsOnTop: {
	      handler(newValue) {
	        const commentsWereClosed = newValue === false;
	        if (!commentsWereClosed) {
	          return;
	        }
	        this.readVisibleMessages();
	      },
	      flush: 'post'
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Dialog: Chat created', this.dialogId);
	    this.initContextMode();
	  },
	  async mounted() {
	    await this.$nextTick();
	    this.containerHeight = this.$refs.container.clientHeight;
	    this.getScrollManager().setContainer(this.getContainer());
	    if (this.dialogInited) {
	      // second+ opening
	      this.getPullWatchManager().subscribe();
	      this.onChatInited();
	    }
	    // there are P&P messages
	    else if (!this.dialogInited && this.messageCollection.length > 0) {
	      this.scrollOnStart();
	    }
	    this.windowFocused = document.hasFocus();
	    this.subscribeToEvents();
	  },
	  beforeUnmount() {
	    this.unsubscribeFromEvents();
	    if (this.dialogInited) {
	      this.saveScrollPosition();
	      void this.handleMessagesOnExit();
	    }
	    this.getPullWatchManager().unsubscribe();
	    this.closeDialogPopups();
	    this.forwardPopup.show = false;
	    this.readAllAnchors();
	  },
	  methods: {
	    async scrollOnStart() {
	      await this.$nextTick();

	      // we loaded chat with context
	      if (this.contextMode.active && this.contextMode.messageIsLoaded) {
	        this.getScrollManager().scrollToMessage(this.layout.contextId);
	        void this.$nextTick(() => {
	          this.highlightMessage(this.layout.contextId);
	        });
	        return;
	      }

	      // chat was loaded before
	      if (this.contextMode.active && !this.contextMode.messageIsLoaded) {
	        this.goToMessageContext(this.layout.contextId);
	        return;
	      }

	      // marked message
	      if (this.dialog.markedId) {
	        this.getScrollManager().scrollToMessage(im_v2_const.DialogBlockType.newMessages);
	        return;
	      }

	      // saved position
	      if (this.dialog.savedPositionMessageId && !this.isGuest) {
	        im_v2_lib_logger.Logger.warn('Dialog: saved scroll position, scrolling to', this.dialog.savedPositionMessageId);
	        this.getScrollManager().scrollToMessage(this.dialog.savedPositionMessageId, {
	          withDateOffset: false
	        });
	        return;
	      }
	      const lastReadId = this.$store.getters['chats/getLastReadId'](this.dialogId);
	      const isLastMessageId = lastReadId === this.dialog.lastMessageId;
	      // unread messages and read messages before them
	      if (lastReadId > 0 && !isLastMessageId) {
	        im_v2_lib_logger.Logger.warn('Dialog: scroll to "New messages" mark, lastReadId -', lastReadId, 'lastMessageId', this.dialog.lastMessageId);
	        this.getScrollManager().scrollToMessage(im_v2_const.DialogBlockType.newMessages);
	        return;
	      }

	      // new chat, unread messages without read messages before them
	      const hasUnread = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	      if (lastReadId === 0 || hasUnread) {
	        this.getScrollManager().setStartScrollNeeded(false);
	        im_v2_lib_logger.Logger.warn('Dialog: dont scroll, hasUnread -', hasUnread, 'lastReadId', lastReadId);
	        return;
	      }

	      // no unread messages
	      this.getScrollManager().scrollToBottom();
	    },
	    showLoadingBar() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.showLoadingBar, {
	        dialogId: this.dialogId
	      });
	    },
	    hideLoadingBar() {
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.hideLoadingBar, {
	        dialogId: this.dialogId
	      });
	    },
	    async goToMessageContext(messageId, params = {}) {
	      const {
	        position = ScrollManager.scrollPosition.messageTop
	      } = params;
	      const hasMessage = this.$store.getters['messages/hasMessage']({
	        chatId: this.dialog.chatId,
	        messageId
	      });
	      if (hasMessage) {
	        im_v2_lib_logger.Logger.warn('Dialog: we have this message, scrolling to it', messageId);
	        await this.getScrollManager().animatedScrollToMessage(messageId, {
	          position
	        });
	        this.highlightMessage(messageId);
	        return;
	      }
	      const {
	        hasAccess,
	        errorCode
	      } = await im_v2_lib_access.AccessManager.checkMessageAccess(messageId);
	      if (!hasAccess && errorCode === im_v2_const.ErrorCode.message.accessDeniedByTariff) {
	        im_v2_lib_analytics.Analytics.getInstance().historyLimit.onGoToContextLimitExceeded({
	          dialogId: this.dialogId
	        });
	        im_v2_lib_feature.FeatureManager.chatHistory.openFeatureSlider();
	        return;
	      }
	      this.showLoadingBar();
	      await this.getMessageService().loadContext(messageId);
	      await this.$nextTick();
	      this.hideLoadingBar();
	      this.getScrollManager().scrollToMessage(messageId, {
	        position
	      });
	      await this.$nextTick();
	      this.highlightMessage(messageId);
	    },
	    highlightMessage(messageId) {
	      const HIGHLIGHT_CLASS = 'bx-im-dialog-chat__highlighted-message';
	      const HIGHLIGHT_DURATION = 2000;
	      const message = this.getScrollManager().getDomElementById(messageId);
	      if (!message) {
	        return;
	      }
	      main_core.Dom.addClass(message, HIGHLIGHT_CLASS);
	      setTimeout(() => {
	        main_core.Dom.removeClass(message, HIGHLIGHT_CLASS);
	      }, HIGHLIGHT_DURATION);
	    },
	    saveScrollPosition() {
	      if (!this.saveScrollOnExit) {
	        return;
	      }
	      let savedPositionMessageId = this.getVisibleMessagesManager().getFirstMessageId();
	      if (this.getScrollManager().isAroundBottom()) {
	        savedPositionMessageId = 0;
	      }
	      this.$store.dispatch('chats/update', {
	        dialogId: this.dialogId,
	        fields: {
	          savedPositionMessageId
	        }
	      });
	    },
	    async handleMessagesOnExit() {
	      if (this.clearOnExit) {
	        void this.getChatService().clearChat(this.dialogId);
	        return;
	      }
	      await this.getChatService().readChatQueuedMessages(this.dialog.chatId);
	      if (this.reloadOnExit) {
	        const LOAD_MESSAGES_ON_EXIT_DELAY = 200;
	        setTimeout(async () => {
	          this.getMessageService().reloadMessageList();
	        }, LOAD_MESSAGES_ON_EXIT_DELAY);
	      }
	    },
	    /* region Reading */
	    readQueuedMessages() {
	      if (!this.messagesCanBeRead()) {
	        return;
	      }
	      [...this.messagesToRead].forEach(messageId => {
	        this.getChatService().readMessage(this.dialog.chatId, messageId);
	        this.messagesToRead.delete(messageId);
	      });
	    },
	    readVisibleMessages() {
	      if (!this.messagesCanBeRead()) {
	        return;
	      }
	      const visibleMessages = this.getVisibleMessagesManager().getVisibleMessages();
	      visibleMessages.forEach(messageId => {
	        const message = this.$store.getters['messages/getById'](messageId);
	        if (!message || message.viewed) {
	          return;
	        }
	        this.getChatService().readMessage(this.dialog.chatId, messageId);
	      });
	    },
	    readAllAnchors() {
	      if (this.$store.getters['messages/anchors/isChatHasAnchors'](this.dialog.chatId)) {
	        this.getAnchorService().readChatAnchors(this.dialog.chatId);
	      }
	    },
	    messagesCanBeRead() {
	      if (!this.dialogInited || !this.isChatVisible()) {
	        return false;
	      }
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      return permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.readMessage, this.dialogId);
	    },
	    /* endregion Reading */
	    /* region Event handlers */
	    onChatInited() {
	      this.scrollOnStart();
	      this.readVisibleMessages();
	      void this.$nextTick(() => {
	        this.getChatService().clearDialogMark(this.dialogId);
	      });
	      main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.onDialogInited, {
	        dialogId: this.dialogId
	      });
	    },
	    async onScrollTriggerUp() {
	      if (!this.dialogInited || !this.getContainer()) {
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll triggered UP');
	      const container = this.getContainer();
	      const oldHeight = container.scrollHeight - container.clientHeight;

	      // Insert messages if there are some
	      if (this.getMessageService().hasPreparedHistoryMessages()) {
	        await this.getMessageService().drawPreparedHistoryMessages();
	        this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
	        return;
	      }

	      // check if already loading or no more history
	      if (this.getMessageService().isLoading() || !this.dialog.hasPrevPage) {
	        return;
	      }

	      // Load messages and save them
	      this.showLoadingBar();
	      await this.getMessageService().loadHistory();
	      this.hideLoadingBar();
	      // Messages loaded and we are at the top
	      if (this.getScrollManager().isAtTheTop()) {
	        im_v2_lib_logger.Logger.warn('Dialog: we are at the top after history request, inserting messages');
	        await this.getMessageService().drawPreparedHistoryMessages();
	        this.getScrollManager().adjustScrollOnHistoryAddition(oldHeight);
	      }
	    },
	    async onScrollTriggerDown() {
	      if (!this.dialogInited || !this.getContainer()) {
	        return;
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll triggered DOWN');
	      // Insert messages if there are some
	      if (this.getMessageService().hasPreparedUnreadMessages()) {
	        await this.getMessageService().drawPreparedUnreadMessages();
	        return;
	      }

	      // check if already loading or no more history
	      if (this.getMessageService().isLoading() || !this.dialog.hasNextPage) {
	        return;
	      }

	      // Load messages and save them
	      this.showLoadingBar();
	      await this.getMessageService().loadUnread();
	      this.hideLoadingBar();
	      // Messages loaded and we are at the bottom
	      if (this.getScrollManager().isAroundBottom()) {
	        im_v2_lib_logger.Logger.warn('Dialog: we are at the bottom after unread request, inserting messages');
	        await this.getMessageService().drawPreparedUnreadMessages();
	        this.getScrollManager().checkIfChatIsScrolledUp();
	      }
	    },
	    async onScrollToBottom(event) {
	      const {
	        chatId,
	        threshold = im_v2_const.DialogScrollThreshold.halfScreenUp,
	        animation = true
	      } = event.getData();
	      if (this.dialog.chatId !== chatId) {
	        return;
	      }
	      if (!this.windowFocused || this.hasVisibleCall()) {
	        const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	        if (firstUnreadId) {
	          await this.$nextTick();
	          this.getScrollManager().scrollToMessage(firstUnreadId);
	          return;
	        }
	      }
	      im_v2_lib_logger.Logger.warn('Dialog: scroll to bottom', chatId, threshold);
	      if (threshold === im_v2_const.DialogScrollThreshold.halfScreenUp && this.isScrolledUp) {
	        return;
	      }
	      if (threshold === im_v2_const.DialogScrollThreshold.nearTheBottom && !this.getScrollManager().isAroundBottom()) {
	        return;
	      }
	      await this.$nextTick();
	      if (animation) {
	        this.getScrollManager().animatedScrollToBottom();
	        return;
	      }
	      this.getScrollManager().scrollToBottom();
	    },
	    onGoToMessageContext(event) {
	      const {
	        dialogId,
	        messageId
	      } = event.getData();
	      if (this.dialog.dialogId !== dialogId) {
	        return;
	      }
	      this.goToMessageContext(messageId);
	    },
	    onPinnedMessageClick(messageId) {
	      this.goToMessageContext(messageId);
	    },
	    onPinnedMessageUnpin(messageId) {
	      this.getMessageService().unpinMessage(this.dialog.chatId, messageId);
	      im_v2_lib_analytics.Analytics.getInstance().messagePins.onUnpin({
	        dialogId: this.dialogId
	      });
	    },
	    onScroll(event) {
	      this.closeDialogPopups();
	      this.debouncedScrollHandler(event);
	    },
	    async onScrollButtonClick() {
	      if (this.getScrollManager().scrollButtonClicked) {
	        void this.handleSecondScrollButtonClick();
	        return;
	      }
	      this.getScrollManager().scrollButtonClicked = true;
	      if (this.dialog.counter === 0) {
	        this.showLoadingBar();
	        await this.getMessageService().loadInitialMessages();
	        this.hideLoadingBar();
	        this.getScrollManager().scrollToBottom();
	        return;
	      }
	      const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	      if (!firstUnreadId) {
	        this.showLoadingBar();
	        await this.getMessageService().loadInitialMessages();
	        this.hideLoadingBar();
	        await this.getScrollManager().animatedScrollToMessage(firstUnreadId);
	      }
	      await this.getScrollManager().animatedScrollToMessage(firstUnreadId);
	    },
	    async onMentionsButtonClick() {
	      if (this.isJumpingToAnchor) {
	        return;
	      }
	      this.isJumpingToAnchor = true;
	      await this.goToNearestMessageWithAnchor(im_v2_const.AnchorType.mention);
	      this.isJumpingToAnchor = false;
	    },
	    async onReactionsButtonClick() {
	      if (this.isJumpingToAnchor) {
	        return;
	      }
	      this.isJumpingToAnchor = true;
	      await this.goToNearestMessageWithAnchor(im_v2_const.AnchorType.reaction);
	      this.isJumpingToAnchor = false;
	    },
	    async goToNearestMessageWithAnchor(anchorType) {
	      const nextMessage = this.$store.getters['messages/anchors/getNextMessageIdWithAnchorType'](this.dialog.chatId, anchorType);
	      if (nextMessage) {
	        await this.goToMessageContext(nextMessage, {
	          position: ScrollManager.scrollPosition.messageTop
	        });
	      }
	    },
	    onWindowFocus() {
	      this.windowFocused = true;
	      this.readVisibleMessages();
	    },
	    onWindowBlur() {
	      this.windowFocused = false;
	    },
	    onCallFold() {
	      const callDialogId = im_v2_lib_call.CallManager.getInstance().getCurrentCallDialogId();
	      if (callDialogId !== this.dialogId) {
	        return;
	      }
	      this.readVisibleMessages();
	    },
	    async onShowQuoteButton(event) {
	      const {
	        message,
	        event: $event
	      } = event.getData();
	      const permissionManager = im_v2_lib_permission.PermissionManager.getInstance();
	      if (!permissionManager.canPerformActionByRole(im_v2_const.ActionByRole.send, this.dialogId)) {
	        return;
	      }
	      this.showQuoteButton = true;
	      await this.$nextTick();
	      this.$refs.quoteButton.onMessageMouseUp(message, $event);
	    },
	    async handleSecondScrollButtonClick() {
	      this.getScrollManager().scrollButtonClicked = false;
	      if (this.dialog.hasNextPage) {
	        this.showLoadingBar();
	        await this.getMessageService().loadContext(this.dialog.lastMessageId);
	        this.hideLoadingBar();
	        main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	          chatId: this.dialog.chatId
	        });
	        return;
	      }
	      void this.getScrollManager().animatedScrollToMessage(this.dialog.lastMessageId, {
	        withDateOffset: false
	      });
	    },
	    onShowForwardPopup(event) {
	      const {
	        messagesIds
	      } = event.getData();
	      this.forwardPopup.messagesIds = messagesIds;
	      this.forwardPopup.show = true;
	    },
	    onCloseForwardPopup() {
	      this.forwardPopup.messagesIds = [];
	      this.forwardPopup.show = false;
	    },
	    onMessageIsVisible(event) {
	      const {
	        messageId,
	        dialogId
	      } = event.getData();
	      if (dialogId !== this.dialogId) {
	        return;
	      }
	      this.getVisibleMessagesManager().setMessageAsVisible(messageId);
	      if (this.isChatVisible() === false) {
	        return;
	      }
	      if (this.$store.getters['messages/anchors/isMessageHasAnchors'](messageId)) {
	        this.readAnchorsIfMessageVisibleLongEnough(messageId);
	      }
	      const message = this.$store.getters['messages/getById'](messageId);
	      if (!message.viewed) {
	        this.messagesToRead.add(messageId);
	        this.debouncedReadHandler();
	      }
	    },
	    readAnchorsIfMessageVisibleLongEnough(messageId) {
	      const messageVisibilityTimeThreshold = 200;
	      if (this.getScrollManager().isScrolling) {
	        this.readMessageAnchorsAfterVisibilityThreshold(messageId, messageVisibilityTimeThreshold);
	      } else {
	        this.getAnchorService().debouncedReadMessageAnchors(messageId);
	      }
	    },
	    readMessageAnchorsAfterVisibilityThreshold(messageId, messageVisibilityTimeThreshold) {
	      setTimeout(() => {
	        if (this.getVisibleMessagesManager().getVisibleMessages().includes(messageId)) {
	          this.sequentiallyHighlightMessageHandler(messageId);
	          this.getAnchorService().debouncedReadMessageAnchors(messageId);
	        }
	      }, messageVisibilityTimeThreshold);
	    },
	    onMessageIsNotVisible(event) {
	      const {
	        messageId,
	        dialogId
	      } = event.getData();
	      if (dialogId !== this.dialogId) {
	        return;
	      }
	      this.getVisibleMessagesManager().setMessageAsNotVisible(messageId);
	    },
	    /* endregion Event handlers */
	    /* region Init methods */
	    initContextMode() {
	      const layoutManager = im_v2_lib_layout.LayoutManager.getInstance();
	      if (!layoutManager.isChatContextAvailable(this.dialogId)) {
	        return;
	      }
	      this.contextMode.active = true;
	      // chat was loaded before, we didn't load context specifically
	      // if chat wasn't loaded before - we load it with context
	      this.contextMode.messageIsLoaded = !this.dialogInited;
	    },
	    getMessageService() {
	      if (!this.messageService) {
	        this.messageService = new im_v2_provider_service_message.MessageService({
	          chatId: this.dialog.chatId
	        });
	      }
	      return this.messageService;
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatService;
	    },
	    getAnchorService() {
	      if (!this.anchorService) {
	        this.anchorService = new AnchorService();
	      }
	      return this.anchorService;
	    },
	    getScrollManager() {
	      if (!this.scrollManager) {
	        this.scrollManager = new ScrollManager();
	        this.scrollManager.subscribe(ScrollManager.events.onScrollTriggerUp, this.onScrollTriggerUp);
	        this.scrollManager.subscribe(ScrollManager.events.onScrollTriggerDown, this.onScrollTriggerDown);
	        this.scrollManager.subscribe(ScrollManager.events.onScrollThresholdPass, event => {
	          this.isScrolledUp = event.getData();
	        });
	      }
	      return this.scrollManager;
	    },
	    getPullWatchManager() {
	      if (!this.pullWatchManager) {
	        this.pullWatchManager = new PullWatchManager(this.dialogId);
	      }
	      return this.pullWatchManager;
	    },
	    getVisibleMessagesManager() {
	      if (!this.visibleMessagesManager) {
	        this.visibleMessagesManager = new VisibleMessagesManager();
	      }
	      return this.visibleMessagesManager;
	    },
	    /* endregion Init methods */
	    isChatVisible() {
	      return this.windowFocused && !this.hasVisibleCall() && !this.hasCommentsOnTop;
	    },
	    hasVisibleCall() {
	      return im_v2_lib_call.CallManager.getInstance().hasVisibleCall();
	    },
	    closeDialogPopups() {
	      var _PopupManager$getPopu, _PopupManager$getPopu2, _PopupManager$getPopu3, _PopupManager$getPopu4, _PopupManager$getPopu5;
	      this.showQuoteButton = false;
	      (_PopupManager$getPopu = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogAvatarMenu)) == null ? void 0 : _PopupManager$getPopu.close();
	      (_PopupManager$getPopu2 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogMessageMenu)) == null ? void 0 : _PopupManager$getPopu2.close();
	      (_PopupManager$getPopu3 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogReactionUsers)) == null ? void 0 : _PopupManager$getPopu3.close();
	      (_PopupManager$getPopu4 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.dialogReadUsers)) == null ? void 0 : _PopupManager$getPopu4.close();
	      (_PopupManager$getPopu5 = main_popup.PopupManager.getPopupById(im_v2_const.PopupType.messageBaseFileMenu)) == null ? void 0 : _PopupManager$getPopu5.close();
	    },
	    subscribeToEvents() {
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.call.onFold, this.onCallFold);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.showForwardPopup, this.onShowForwardPopup);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.showQuoteButton, this.onShowQuoteButton);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onMessageIsVisible, this.onMessageIsVisible);
	      main_core_events.EventEmitter.subscribe(im_v2_const.EventType.dialog.onMessageIsNotVisible, this.onMessageIsNotVisible);
	      main_core.Event.bind(window, 'focus', this.onWindowFocus);
	      main_core.Event.bind(window, 'blur', this.onWindowBlur);
	    },
	    unsubscribeFromEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.call.onFold, this.onCallFold);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.showForwardPopup, this.onShowForwardPopup);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.showQuoteButton, this.onShowQuoteButton);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onMessageIsVisible, this.onMessageIsVisible);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.onMessageIsNotVisible, this.onMessageIsNotVisible);
	      main_core.Event.unbind(window, 'focus', this.onWindowFocus);
	      main_core.Event.unbind(window, 'blur', this.onWindowBlur);
	    },
	    getContainer() {
	      return this.$refs.container;
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__block bx-im-dialog-chat__scope">
			<!-- Top -->
			<slot name="pinned-panel">
				<PinnedMessages
					v-if="pinnedMessages.length > 0"
					:dialogId="dialogId"
					:messages="pinnedMessages"
					@messageClick="onPinnedMessageClick"
					@messageUnpin="onPinnedMessageUnpin"
				/>
			</slot>
			<PullStatus/>
			<!-- Message list -->
			<div @scroll="onScroll" class="bx-im-dialog-chat__scroll-container" ref="container">
				<slot name="message-list">
					<MessageList :dialogId="dialogId" :containerHeight="containerHeight" />
				</slot>
			</div>
			<FloatButtons
				:dialogId="dialogId"
				:isScrolledUp="isScrolledUp"
				@scrollButtonClick="onScrollButtonClick"
				@reactionsButtonClick="onReactionsButtonClick"
				@mentionsButtonClick="onMentionsButtonClick"
			>
				<template #additional-float-button><slot name="additional-float-button" /></template>
			</FloatButtons>
			<!-- Absolute elements -->
			<ForwardPopup
				v-if="forwardPopup.show"
				:messagesIds="forwardPopup.messagesIds"
				:dialogId="dialogId"
				@close="onCloseForwardPopup"
			/>
			<Transition name="fade-up">
				<QuoteButton
					v-if="showQuoteButton"
					:dialogId="dialogId"
					@close="showQuoteButton = false" 
					class="bx-im-message-base__quote-button"
					ref="quoteButton"
				/>
			</Transition>
		</div>
	`
	};

	exports.ChatDialog = ChatDialog;
	exports.ScrollManager = ScrollManager;
	exports.PinnedMessages = PinnedMessages;
	exports.FloatButton = FloatButton;
	exports.FloatButtonIcon = FloatButtonIcon;
	exports.FloatButtonColor = FloatButtonColor;

}((this.BX.Messenger.v2.Component.Dialog = this.BX.Messenger.v2.Component.Dialog || {}),BX.Main,window,BX.Messenger.v2.Lib,BX.Messenger.v2.Component,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Service,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Const));
//# sourceMappingURL=chat-dialog.bundle.js.map
