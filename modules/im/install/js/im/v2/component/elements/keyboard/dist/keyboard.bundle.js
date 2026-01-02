/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,main_core,im_v2_lib_logger,im_v2_lib_utils,main_core_events,im_public,im_v2_provider_service_sending,im_v2_lib_phone,im_v2_lib_notifier,im_v2_application_core,im_v2_const) {
	'use strict';

	// @vue/component
	const KeyboardButton = {
	  name: 'KeyboardButton',
	  props: {
	    config: {
	      type: Object,
	      required: true
	    },
	    keyboardBlocked: {
	      type: Boolean,
	      required: true
	    }
	  },
	  emits: ['action', 'customCommand', 'blockKeyboard'],
	  data() {
	    return {};
	  },
	  computed: {
	    button() {
	      return this.config;
	    },
	    buttonClasses() {
	      const {
	        bgColorToken = im_v2_const.ColorToken.base,
	        display,
	        disabled,
	        wait
	      } = this.button;
	      const displayClass = display === im_v2_const.KeyboardButtonDisplay.block ? '--block' : '--line';
	      const classes = [displayClass, bgColorToken];
	      if (this.keyboardBlocked || disabled) {
	        classes.push('--disabled');
	      }
	      if (wait) {
	        classes.push('--loading');
	      }
	      return classes;
	    },
	    buttonStyles() {
	      const styles = {};
	      const {
	        width
	      } = this.button;
	      if (width) {
	        styles.width = `${width}px`;
	      }
	      return styles;
	    }
	  },
	  methods: {
	    onClick() {
	      if (this.keyboardBlocked || this.button.disabled || this.button.wait) {
	        return;
	      }
	      if (this.button.action && this.button.actionValue) {
	        this.handleAction();
	      } else if (this.button.appId) {
	        im_v2_lib_logger.Logger.warn('Messenger keyboard: open app is not implemented.');
	      } else if (this.button.link) {
	        const preparedLink = main_core.Text.decode(this.button.link);
	        im_v2_lib_utils.Utils.browser.openLink(preparedLink);
	      } else if (this.button.command) {
	        this.handleCustomCommand();
	      }
	    },
	    handleAction() {
	      this.$emit('action', {
	        action: this.button.action,
	        payload: this.button.actionValue
	      });
	    },
	    handleCustomCommand() {
	      if (this.button.block) {
	        this.$emit('blockKeyboard');
	      }
	      this.button.wait = true;
	      this.$emit('customCommand', {
	        botId: this.button.botId,
	        command: this.button.command,
	        payload: this.button.commandParams
	      });
	    }
	  },
	  template: `
		<div
			class="bx-im-keyboard-button__container"
			:class="buttonClasses"
			:style="buttonStyles"
			@click="onClick"
		>
			{{ button.text }}
		</div>
	`
	};

	// @vue/component
	const KeyboardSeparator = {
	  name: 'KeyboardSeparator',
	  data() {
	    return {};
	  },
	  template: `
		<div class="bx-im-keyboard-button__separator"></div>
	`
	};

	var _dialogId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	var _actionHandlers = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("actionHandlers");
	var _sendMessage = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("sendMessage");
	var _insertText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("insertText");
	var _startCall = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("startCall");
	var _copyText = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("copyText");
	var _openChat = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("openChat");
	class ActionManager {
	  constructor(dialogId) {
	    Object.defineProperty(this, _openChat, {
	      value: _openChat2
	    });
	    Object.defineProperty(this, _copyText, {
	      value: _copyText2
	    });
	    Object.defineProperty(this, _startCall, {
	      value: _startCall2
	    });
	    Object.defineProperty(this, _insertText, {
	      value: _insertText2
	    });
	    Object.defineProperty(this, _sendMessage, {
	      value: _sendMessage2
	    });
	    Object.defineProperty(this, _dialogId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _actionHandlers, {
	      writable: true,
	      value: {
	        [im_v2_const.KeyboardButtonAction.send]: babelHelpers.classPrivateFieldLooseBase(this, _sendMessage)[_sendMessage].bind(this),
	        [im_v2_const.KeyboardButtonAction.put]: babelHelpers.classPrivateFieldLooseBase(this, _insertText)[_insertText].bind(this),
	        [im_v2_const.KeyboardButtonAction.call]: babelHelpers.classPrivateFieldLooseBase(this, _startCall)[_startCall].bind(this),
	        [im_v2_const.KeyboardButtonAction.copy]: babelHelpers.classPrivateFieldLooseBase(this, _copyText)[_copyText].bind(this),
	        [im_v2_const.KeyboardButtonAction.dialog]: babelHelpers.classPrivateFieldLooseBase(this, _openChat)[_openChat].bind(this)
	      }
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId] = dialogId;
	  }
	  handleAction(event) {
	    const {
	      action,
	      payload
	    } = event;
	    if (!babelHelpers.classPrivateFieldLooseBase(this, _actionHandlers)[_actionHandlers][action]) {
	      // eslint-disable-next-line no-console
	      console.error('Keyboard: action not found');
	    }
	    babelHelpers.classPrivateFieldLooseBase(this, _actionHandlers)[_actionHandlers][action](payload);
	  }
	}
	function _sendMessage2(payload) {
	  im_v2_provider_service_sending.SendingService.getInstance().sendMessage({
	    text: payload,
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId]
	  });
	}
	function _insertText2(payload) {
	  main_core_events.EventEmitter.emit(im_v2_const.EventType.textarea.insertText, {
	    text: payload,
	    dialogId: babelHelpers.classPrivateFieldLooseBase(this, _dialogId)[_dialogId]
	  });
	}
	function _startCall2(payload) {
	  im_v2_lib_phone.PhoneManager.getInstance().startCall(payload);
	}
	function _copyText2(payload) {
	  var _BX$clipboard;
	  if ((_BX$clipboard = BX.clipboard) != null && _BX$clipboard.copy(payload)) {
	    im_v2_lib_notifier.Notifier.onCopyTextComplete();
	  }
	}
	function _openChat2(payload) {
	  im_public.Messenger.openChat(payload);
	}

	var _messageId = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("messageId");
	var _dialogId$1 = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("dialogId");
	class BotService {
	  constructor(params) {
	    Object.defineProperty(this, _messageId, {
	      writable: true,
	      value: void 0
	    });
	    Object.defineProperty(this, _dialogId$1, {
	      writable: true,
	      value: void 0
	    });
	    const {
	      messageId,
	      dialogId
	    } = params;
	    babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId] = messageId;
	    babelHelpers.classPrivateFieldLooseBase(this, _dialogId$1)[_dialogId$1] = dialogId;
	  }
	  sendCommand(event) {
	    const {
	      botId,
	      command,
	      payload
	    } = event;
	    const queryParams = {
	      MESSAGE_ID: babelHelpers.classPrivateFieldLooseBase(this, _messageId)[_messageId],
	      DIALOG_ID: babelHelpers.classPrivateFieldLooseBase(this, _dialogId$1)[_dialogId$1],
	      BOT_ID: botId,
	      COMMAND: command,
	      COMMAND_PARAMS: payload
	    };
	    im_v2_application_core.Core.getRestClient().callMethod(im_v2_const.RestMethod.imMessageCommand, queryParams).catch(result => {
	      console.error('BotService: error sending command:', result.error());
	    });
	  }
	}

	const Keyboard = {
	  props: {
	    buttons: {
	      type: Array,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    messageId: {
	      type: [Number, String],
	      required: true
	    }
	  },
	  components: {
	    KeyboardButton,
	    KeyboardSeparator
	  },
	  data() {
	    return {
	      keyboardBlocked: false
	    };
	  },
	  emits: ['click'],
	  watch: {
	    buttons() {
	      this.keyboardBlocked = false;
	    }
	  },
	  computed: {
	    ButtonType: () => im_v2_const.KeyboardButtonType,
	    preparedButtons() {
	      return this.buttons.filter(button => {
	        return button.context !== im_v2_const.KeyboardButtonContext.mobile;
	      });
	    }
	  },
	  methods: {
	    onButtonActionClick(event) {
	      this.getActionManager().handleAction(event);
	    },
	    onButtonCustomCommandClick(event) {
	      this.getBotService().sendCommand(event);
	    },
	    getActionManager() {
	      if (!this.actionManager) {
	        this.actionManager = new ActionManager(this.dialogId);
	      }
	      return this.actionManager;
	    },
	    getBotService() {
	      if (!this.botService) {
	        this.botService = new BotService({
	          messageId: this.messageId,
	          dialogId: this.dialogId
	        });
	      }
	      return this.botService;
	    }
	  },
	  template: `
		<div class="bx-im-keyboard__container">
			<template v-for="button in preparedButtons">
				<KeyboardButton
					v-if="button.type === ButtonType.button"
					:config="button"
					:keyboardBlocked="keyboardBlocked"
					@blockKeyboard="keyboardBlocked = true"
					@action="onButtonActionClick"
					@customCommand="onButtonCustomCommandClick"
				/>
				<KeyboardSeparator v-else-if="button.type === ButtonType.newLine" />
			</template>
		</div>
	`
	};

	exports.Keyboard = Keyboard;

}((this.BX.Messenger.v2.Component.Elements = this.BX.Messenger.v2.Component.Elements || {}),BX,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Event,BX.Messenger.v2.Lib,BX.Messenger.v2.Service,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Application,BX.Messenger.v2.Const));
//# sourceMappingURL=keyboard.bundle.js.map
