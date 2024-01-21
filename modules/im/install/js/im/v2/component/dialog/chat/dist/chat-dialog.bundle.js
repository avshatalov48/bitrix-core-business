/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_popup,pull_vue3_status,im_v2_component_messageList,im_v2_component_entitySelector,im_v2_lib_call,im_v2_provider_service,im_v2_lib_logger,im_v2_lib_animation,main_polyfill_intersectionobserver,main_core_events,im_v2_application_core,im_v2_const,im_v2_lib_rest,im_v2_lib_parser,main_core,im_v2_lib_quote,im_v2_lib_utils,im_v2_lib_slider) {
	'use strict';

	const EVENT_NAMESPACE = 'BX.Messenger.v2.Dialog.ScrollManager';
	const SCROLLING_THRESHOLD = 1500;
	const POSITION_THRESHOLD = 40;
	const SCROLLED_UP_THRESHOLD = 400;
	class ScrollManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    this.isScrolling = false;
	    this.currentScroll = 0;
	    this.lastScroll = 0;
	    this.chatIsScrolledUp = false;
	    this.scrollButtonClicked = false;
	    this.setEventNamespace(EVENT_NAMESPACE);
	  }
	  setContainer(container) {
	    this.container = container;
	  }
	  onScroll(event) {
	    // if (this.isScrolling || !event.target || this.currentScroll === event.target.scrollTop)
	    if (this.isScrolling || !event.target) {
	      return false;
	    }
	    this.currentScroll = event.target.scrollTop;
	    const isScrollingDown = this.lastScroll < this.currentScroll;
	    const isScrollingUp = !isScrollingDown;
	    if (isScrollingUp) {
	      this.scrollButtonClicked = false;
	    }
	    const leftSpaceBottom = event.target.scrollHeight - event.target.scrollTop - event.target.clientHeight;
	    if (isScrollingDown && this.lastScroll > 0 && leftSpaceBottom < SCROLLING_THRESHOLD) {
	      this.emit(ScrollManager.events.onScrollTriggerDown);
	    } else if (isScrollingUp && this.currentScroll <= SCROLLING_THRESHOLD) {
	      this.emit(ScrollManager.events.onScrollTriggerUp);
	    }
	    this.lastScroll = this.currentScroll;
	    this.checkIfChatIsScrolledUp();
	  }
	  checkIfChatIsScrolledUp() {
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
	  scrollToMessage(messageId, offset = -10) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: scroll to message - ', messageId);
	    const element = this.getDomElementById(messageId);
	    if (!element) {
	      im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
	      return;
	    }
	    const position = element.offsetTop + offset;
	    this.forceScrollTo(position);
	  }
	  animatedScrollToMessage(messageId, offset = -10) {
	    im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: animated scroll to message - ', messageId);
	    const element = this.getDomElementById(messageId);
	    if (!element) {
	      im_v2_lib_logger.Logger.warn('Dialog: ScrollManager: message not found - ', messageId);
	      return;
	    }
	    const position = element.offsetTop + offset;
	    return this.animatedScrollTo(position);
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
	    return this.container.scrollHeight - this.container.scrollTop - this.container.clientHeight < POSITION_THRESHOLD;
	  }
	  getDomElementById(id) {
	    return this.container.querySelector(`[data-id="${id}"]`);
	  }
	}
	ScrollManager.events = {
	  onScrollTriggerUp: 'onScrollTriggerUp',
	  onScrollTriggerDown: 'onScrollTriggerDown',
	  onScrollThresholdPass: 'onScrollThresholdPass'
	};

	const EVENT_NAMESPACE$1 = 'BX.Messenger.v2.Dialog.ObserverManager';
	var _observer = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observer");
	var _observedElements = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("observedElements");
	var _visibleMessages = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("visibleMessages");
	var _messagesToRead = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messagesToRead");
	var _dialogInited = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogInited");
	var _initObserver = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initObserver");
	var _getMessageIdFromElement = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("getMessageIdFromElement");
	var _messageIsViewed = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageIsViewed");
	class ObserverManager extends main_core_events.EventEmitter {
	  constructor() {
	    super();
	    Object.defineProperty(this, _messageIsViewed, {
	      value: _messageIsViewed2
	    });
	    Object.defineProperty(this, _getMessageIdFromElement, {
	      value: _getMessageIdFromElement2
	    });
	    Object.defineProperty(this, _initObserver, {
	      value: _initObserver2
	    });
	    Object.defineProperty(this, _observer, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _observedElements, {
	      writable: true,
	      value: {}
	    });
	    Object.defineProperty(this, _visibleMessages, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _messagesToRead, {
	      writable: true,
	      value: new Set()
	    });
	    Object.defineProperty(this, _dialogInited, {
	      writable: true,
	      value: false
	    });
	    this.setEventNamespace(EVENT_NAMESPACE$1);
	    babelHelpers.classPrivateFieldLooseBase(this, _initObserver)[_initObserver]();
	  }
	  setDialogInited(flag) {
	    Object.values(babelHelpers.classPrivateFieldLooseBase(this, _observedElements)[_observedElements]).forEach(element => {
	      this.unobserveMessage(element);
	      this.observeMessage(element);
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogInited)[_dialogInited] = flag;
	  }
	  observeMessage(messageElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].observe(messageElement);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getMessageIdFromElement)[_getMessageIdFromElement](messageElement)) {
	      babelHelpers.classPrivateFieldLooseBase(this, _observedElements)[_observedElements][messageElement.dataset.id] = messageElement;
	    }
	  }
	  unobserveMessage(messageElement) {
	    babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer].unobserve(messageElement);
	    if (babelHelpers.classPrivateFieldLooseBase(this, _getMessageIdFromElement)[_getMessageIdFromElement](messageElement)) {
	      delete babelHelpers.classPrivateFieldLooseBase(this, _observedElements)[_observedElements][messageElement.dataset.id];
	    }
	  }
	  onReadMessage(messageId) {
	    babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead].delete(messageId);
	  }
	  getMessagesToRead() {
	    return [...babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead]];
	  }
	  getFirstVisibleMessage() {
	    if (babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].size === 0) {
	      return 0;
	    }
	    const [firstVisibleMessage] = [...babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages]].sort((a, b) => a - b);
	    return firstVisibleMessage;
	  }
	}
	function _initObserver2() {
	  babelHelpers.classPrivateFieldLooseBase(this, _observer)[_observer] = new IntersectionObserver(entries => {
	    entries.forEach(entry => {
	      const messageId = babelHelpers.classPrivateFieldLooseBase(this, _getMessageIdFromElement)[_getMessageIdFromElement](entry.target);
	      if (!messageId || !entry.rootBounds || !babelHelpers.classPrivateFieldLooseBase(this, _dialogInited)[_dialogInited]) {
	        return;
	      }
	      const messageIsFullyVisible = entry.isIntersecting && entry.intersectionRatio >= 0.99;
	      const messageTakesHalfOfViewport = entry.intersectionRect.height >= entry.rootBounds.height / 2.2;
	      // const messageIsBiggerThanViewport = entry.boundingClientRect.height + 20 > entry.rootBounds.height;
	      // const messageCountsAsVisible = messageIsBiggerThanViewport && messageTakesMostOfViewport;
	      if (messageIsFullyVisible || messageTakesHalfOfViewport) {
	        babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].add(messageId);
	        if (!babelHelpers.classPrivateFieldLooseBase(this, _messageIsViewed)[_messageIsViewed](entry.target)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead].add(messageId);
	          this.emit(ObserverManager.events.onMessageIsVisible);
	        }
	      } else {
	        babelHelpers.classPrivateFieldLooseBase(this, _visibleMessages)[_visibleMessages].delete(messageId);
	        if (babelHelpers.classPrivateFieldLooseBase(this, _messageIsViewed)[_messageIsViewed](entry.target)) {
	          babelHelpers.classPrivateFieldLooseBase(this, _messagesToRead)[_messagesToRead].delete(messageId);
	        }
	      }
	    });
	  }, {
	    threshold: Array.from({
	      length: 101
	    }).fill(0).map((zero, index) => index * 0.01)
	  });
	}
	function _getMessageIdFromElement2(messageElement) {
	  return +messageElement.dataset.id;
	}
	function _messageIsViewed2(messageElement) {
	  return messageElement.dataset['viewed'] === 'true';
	}
	ObserverManager.events = {
	  onMessageIsVisible: 'onMessageIsVisible'
	};

	const TAG_PREFIX = 'IM_PUBLIC_';
	var _dialog = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialog");
	var _pullClient = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("pullClient");
	var _requestWatchStart = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("requestWatchStart");
	var _isGuest = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("isGuest");
	class PullWatchManager {
	  constructor(dialogId) {
	    Object.defineProperty(this, _isGuest, {
	      value: _isGuest2
	    });
	    Object.defineProperty(this, _requestWatchStart, {
	      value: _requestWatchStart2
	    });
	    Object.defineProperty(this, _dialog, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _pullClient, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog] = im_v2_application_core.Core.getStore().getters['chats/get'](dialogId);
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient] = im_v2_application_core.Core.getPullClient();
	  }
	  onChatLoad() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isGuest)[_isGuest]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(`${TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	  }
	  onChatExit() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isGuest)[_isGuest]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].clearWatch(`${TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`);
	  }
	  onLoadedChatEnter() {
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _isGuest)[_isGuest]()) {
	      return;
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _requestWatchStart)[_requestWatchStart]();
	    babelHelpers.classPrivateFieldLooseBase(this, _pullClient)[_pullClient].extendWatch(`${TAG_PREFIX}${babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].chatId}`, true);
	  }
	}
	function _requestWatchStart2() {
	  im_v2_lib_rest.runAction(im_v2_const.RestMethod.imV2ChatExtendPullWatch, {
	    data: {
	      dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog].dialogId
	    }
	  });
	}
	function _isGuest2() {
	  var _babelHelpers$classPr, _babelHelpers$classPr2;
	  return ((_babelHelpers$classPr = babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog]) == null ? void 0 : _babelHelpers$classPr.role) === im_v2_const.UserRole.guest && ((_babelHelpers$classPr2 = babelHelpers.classPrivateFieldLooseBase(this, _dialog)[_dialog]) == null ? void 0 : _babelHelpers$classPr2.dialogId) !== 'settings';
	}

	// @vue/component
	const PinnedMessage = {
	  props: {
	    message: {
	      type: Object,
	      required: true
	    }
	  },
	  data() {
	    return {};
	  },
	  computed: {
	    internalMessage() {
	      return this.message;
	    },
	    text() {
	      return im_v2_lib_parser.Parser.purifyMessage(this.internalMessage);
	    },
	    authorId() {
	      return this.internalMessage.authorId;
	    },
	    author() {
	      return this.$store.getters['users/get'](this.authorId);
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__pinned_item">
			<span v-if="author" class="bx-im-dialog-chat__pinned_item_user">{{ author.name }}:</span> {{ text }}
		</div>
	`
	};

	// @vue/component
	const PinnedMessages = {
	  components: {
	    PinnedMessage
	  },
	  props: {
	    messages: {
	      type: Array,
	      required: true
	    }
	  },
	  emits: ['messageClick', 'messageUnpin'],
	  data() {
	    return {};
	  },
	  computed: {
	    firstMessage() {
	      return this.messagesToShow[0];
	    },
	    messagesToShow() {
	      return this.messages.slice(-1);
	    }
	  },
	  methods: {
	    loc(phraseCode) {
	      return this.$Bitrix.Loc.getMessage(phraseCode);
	    }
	  },
	  template: `
		<div @click="$emit('messageClick', firstMessage.id)" class="bx-im-dialog-chat__pinned_container">
			<div class="bx-im-dialog-chat__pinned_title">{{ loc('IM_DIALOG_CHAT_PINNED_TITLE') }}</div>
			<PinnedMessage
				v-for="message in messagesToShow"
				:message="message"
				:key="message.id"
				@click="$emit('messageClick', message.id)"
			/>
			<div @click.stop="$emit('messageUnpin', firstMessage.id)" class="bx-im-dialog-chat__pinned_unpin"></div>
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
	      im_v2_lib_quote.Quote.sendQuoteEvent(this.message, this.text);
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

	const FLOATING_DATE_OFFSET = 52;
	const LOAD_MESSAGE_ON_EXIT_DELAY = 200;

	// @vue/component
	const ChatDialog = {
	  name: 'ChatDialog',
	  components: {
	    MessageList: im_v2_component_messageList.MessageList,
	    PinnedMessages,
	    QuoteButton,
	    PullStatus: pull_vue3_status.PullStatus,
	    ForwardPopup: im_v2_component_entitySelector.ForwardPopup
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    textareaHeight: {
	      type: Number,
	      default: 0
	    }
	  },
	  data() {
	    return {
	      forwardPopup: {
	        show: false,
	        messageId: 0
	      },
	      contextMode: {
	        active: false,
	        messageIsLoaded: false
	      },
	      initialScrollCompleted: false,
	      isScrolledUp: false,
	      windowFocused: false,
	      showQuoteButton: false,
	      selectedText: null,
	      quoteButtonStyles: {},
	      quoteButtonMessage: 0
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
	      return this.$store.getters['messages/get'](this.dialog.chatId);
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
	      const SCROLLING_DEBOUNCE_DELAY = 200;
	      return main_core.Runtime.debounce(this.getScrollManager().onScroll, SCROLLING_DEBOUNCE_DELAY, this.getScrollManager());
	    },
	    debouncedReadHandler() {
	      return main_core.Runtime.debounce(this.readVisibleMessages, 50, this);
	    },
	    formattedCounter() {
	      if (this.dialog.counter === 0) {
	        return '';
	      }
	      if (this.dialog.counter > 99) {
	        return '99+';
	      }
	      return String(this.dialog.counter);
	    },
	    messageListComponent() {
	      return im_v2_component_messageList.MessageList;
	    },
	    showScrollButton() {
	      return this.isScrolledUp || this.dialog.hasNextPage;
	    }
	  },
	  watch: {
	    dialogInited(newValue, oldValue) {
	      if (!newValue || oldValue) {
	        return;
	      }
	      // first opening
	      this.getPullWatchManager().onChatLoad();
	      this.onChatInited();
	    },
	    textareaHeight() {
	      if (this.isScrolledUp || !this.dialogInited) {
	        return;
	      }
	      void this.$nextTick(() => {
	        this.getScrollManager().scrollToBottom();
	      });
	    }
	  },
	  created() {
	    im_v2_lib_logger.Logger.warn('Dialog: Chat created', this.dialogId);
	    this.initObserverManager();
	    this.initContextMode();
	  },
	  mounted() {
	    this.getScrollManager().setContainer(this.getContainer());
	    if (this.dialogInited) {
	      // second+ opening
	      this.getPullWatchManager().onLoadedChatEnter();
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
	      this.loadMessagesOnExit();
	    }
	    this.getPullWatchManager().onChatExit();
	    this.closeDialogPopups();
	    this.forwardPopup.show = false;
	  },
	  methods: {
	    readVisibleMessages() {
	      if (!this.dialogInited || !this.windowFocused || this.hasVisibleCall() || this.isGuest) {
	        return;
	      }
	      this.getObserverManager().getMessagesToRead().forEach(messageId => {
	        this.getChatService().readMessage(this.dialog.chatId, messageId);
	        this.getObserverManager().onReadMessage(messageId);
	      });
	    },
	    scrollOnStart() {
	      void this.$nextTick(() => {
	        // we loaded chat with context
	        if (this.contextMode.active && this.contextMode.messageIsLoaded) {
	          this.getScrollManager().scrollToMessage(this.layout.contextId, -FLOATING_DATE_OFFSET);
	          void this.$nextTick(() => {
	            this.highlightMessage(this.layout.contextId);
	          });
	        }
	        // chat was loaded before
	        else if (this.contextMode.active && !this.contextMode.messageIsLoaded) {
	          this.goToMessageContext(this.layout.contextId);
	        }
	        // marked message
	        else if (this.dialog.markedId) {
	          this.getScrollManager().scrollToMessage(im_v2_const.DialogBlockType.newMessages, -FLOATING_DATE_OFFSET);
	        }
	        // saved position
	        else if (this.dialog.savedPositionMessageId) {
	          im_v2_lib_logger.Logger.warn('Dialog: saved scroll position, scrolling to', this.dialog.savedPositionMessageId);
	          this.getScrollManager().scrollToMessage(this.dialog.savedPositionMessageId);
	        }
	        // unread message
	        else if (this.$store.getters['chats/getLastReadId'](this.dialogId)) {
	          this.getScrollManager().scrollToMessage(im_v2_const.DialogBlockType.newMessages, -FLOATING_DATE_OFFSET);
	        }
	        // new chat with unread messages
	        else if (this.$store.getters['messages/getFirstUnread'](this.dialog.chatId)) {
	          im_v2_lib_logger.Logger.warn('Dialog: new chat with unread messages, dont scroll');
	        } else {
	          this.getScrollManager().scrollToBottom();
	        }
	      });
	    },
	    async goToMessageContext(messageId) {
	      const hasMessage = this.$store.getters['messages/hasMessage']({
	        chatId: this.dialog.chatId,
	        messageId
	      });
	      if (hasMessage) {
	        im_v2_lib_logger.Logger.warn('Dialog: we have this message, scrolling to it', messageId);
	        await this.getScrollManager().animatedScrollToMessage(messageId, -FLOATING_DATE_OFFSET);
	        this.highlightMessage(messageId);
	        return;
	      }
	      await this.getMessageService().loadContext(messageId).catch(error => {
	        im_v2_lib_logger.Logger.error('goToMessageContext error', error);
	      });
	      await this.$nextTick();
	      this.getScrollManager().scrollToMessage(messageId, -FLOATING_DATE_OFFSET);
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
	      let savedPositionMessageId = this.getObserverManager().getFirstVisibleMessage();
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
	    loadMessagesOnExit() {
	      setTimeout(() => {
	        void this.getMessageService().reloadMessageList();
	      }, LOAD_MESSAGE_ON_EXIT_DELAY);
	    },
	    /* region Init methods */
	    initContextMode() {
	      if (!this.layout.contextId) {
	        return;
	      }
	      this.contextMode.active = true;
	      // chat was loaded before, we didn't load context specifically
	      // if chat wasn't loaded before - we load it with context
	      this.contextMode.messageIsLoaded = !this.dialogInited;
	    },
	    initObserverManager() {
	      this.observerManager = new ObserverManager();
	      this.observerManager.subscribe(ObserverManager.events.onMessageIsVisible, () => {
	        this.debouncedReadHandler();
	      });
	    },
	    getObserverManager() {
	      return this.observerManager;
	    },
	    getMessageService() {
	      if (!this.messageService) {
	        this.messageService = new im_v2_provider_service.MessageService({
	          chatId: this.dialog.chatId
	        });
	      }
	      return this.messageService;
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service.ChatService();
	      }
	      return this.chatService;
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
	    /* endregion Init methods */
	    /* region Event handlers */
	    onChatInited() {
	      if (!this.dialog.loading) {
	        this.scrollOnStart();
	        this.readVisibleMessages();
	        this.getObserverManager().setDialogInited(true);
	      }
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
	      await this.getMessageService().loadHistory();
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
	      await this.getMessageService().loadUnread();
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
	          this.getScrollManager().scrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
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
	    },
	    onScroll(event) {
	      this.closeDialogPopups();
	      this.debouncedScrollHandler(event);
	    },
	    async onScrollButtonClick() {
	      if (this.getScrollManager().scrollButtonClicked) {
	        this.handleSecondScrollButtonClick();
	        return;
	      }
	      this.getScrollManager().scrollButtonClicked = true;
	      if (this.dialog.counter === 0) {
	        await this.getMessageService().loadInitialMessages();
	        this.getScrollManager().scrollToBottom();
	        return;
	      }
	      const firstUnreadId = this.$store.getters['messages/getFirstUnread'](this.dialog.chatId);
	      if (!firstUnreadId) {
	        await this.getMessageService().loadInitialMessages();
	        await this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
	      }
	      await this.getScrollManager().animatedScrollToMessage(firstUnreadId, -FLOATING_DATE_OFFSET);
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
	    onChatClick(event) {
	      if (this.isGuest) {
	        event.stopPropagation();
	      }
	    },
	    async onShowQuoteButton(message, event) {
	      this.showQuoteButton = true;
	      await this.$nextTick();
	      this.$refs.quoteButton.onMessageMouseUp(message, event);
	    },
	    handleSecondScrollButtonClick() {
	      this.getScrollManager().scrollButtonClicked = false;
	      if (this.dialog.hasNextPage) {
	        this.getMessageService().loadContext(this.dialog.lastMessageId).then(() => {
	          main_core_events.EventEmitter.emit(im_v2_const.EventType.dialog.scrollToBottom, {
	            chatId: this.dialog.chatId
	          });
	        }).catch(error => {
	          im_v2_lib_logger.Logger.error('ChatDialog: scroll to chat end loadContext error', error);
	        });
	        return;
	      }
	      void this.getScrollManager().animatedScrollToMessage(this.dialog.lastMessageId);
	    },
	    /* endregion Event handlers */
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
	      main_core.Event.bind(window, 'focus', this.onWindowFocus);
	      main_core.Event.bind(window, 'blur', this.onWindowBlur);
	    },
	    unsubscribeFromEvents() {
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.scrollToBottom, this.onScrollToBottom);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.goToMessageContext, this.onGoToMessageContext);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.call.onFold, this.onCallFold);
	      main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.dialog.showForwardPopup, this.onShowForwardPopup);
	      main_core.Event.unbind(window, 'focus', this.onWindowFocus);
	      main_core.Event.unbind(window, 'blur', this.onWindowBlur);
	    },
	    getContainer() {
	      return this.$refs.container;
	    },
	    onShowForwardPopup(event) {
	      const {
	        messageId
	      } = event.getData();
	      this.forwardPopup.messageId = messageId;
	      this.forwardPopup.show = true;
	    },
	    onCloseForwardPopup() {
	      this.forwardPopup.messageId = 0;
	      this.forwardPopup.show = false;
	    }
	  },
	  template: `
		<div class="bx-im-dialog-chat__block bx-im-dialog-chat__scope">
			<PinnedMessages
				v-if="pinnedMessages.length > 0"
				:messages="pinnedMessages"
				@messageClick="onPinnedMessageClick"
				@messageUnpin="onPinnedMessageUnpin"
			/>
			<PullStatus/>
			<div @scroll="onScroll" @click.capture="onChatClick" class="bx-im-dialog-chat__scroll-container" ref="container">
				<component
					:is="messageListComponent"
					:dialogId="dialogId"
					:messages="messageCollection"
					:observer="getObserverManager()"
					@readMessages="debouncedReadHandler"
					@showQuoteButton="onShowQuoteButton"
				/>
			</div>
			<Transition name="scroll-button-transition">
				<div v-if="showScrollButton" @click="onScrollButtonClick" class="bx-im-dialog-chat__scroll-button">
					<div v-if="dialog.counter" class="bx-im-dialog-chat__scroll-button_counter">{{ formattedCounter }}</div>
				</div>
			</Transition>
			<ForwardPopup
				:showPopup="forwardPopup.show"
				:messageId="forwardPopup.messageId"
				@close="onCloseForwardPopup"
			/>
			<Transition name="fade-up">
				<QuoteButton 
					v-if="showQuoteButton" 
					ref="quoteButton"
					@close="showQuoteButton = false" 
					class="bx-im-message-base__quote-button" 
				/>
			</Transition>
		</div>
	`
	};

	exports.ChatDialog = ChatDialog;

}((this.BX.Messenger.v2.Component.Dialog = this.BX.Messenger.v2.Component.Dialog || {}),BX.Main,window,BX.Messenger.v2.Component,BX.Messenger.v2.Component.EntitySelector,BX.Messenger.v2.Lib,BX.Messenger.v2.Provider.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Event,BX.Messenger.v2.Application,BX.Messenger.v2.Const,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib));
//# sourceMappingURL=chat-dialog.bundle.js.map
