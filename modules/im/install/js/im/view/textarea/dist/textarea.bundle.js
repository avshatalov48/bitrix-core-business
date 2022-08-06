(function (exports,ui_vue,im_lib_localstorage,im_lib_utils,main_core) {
	'use strict';

	/**
	 * Bitrix Messenger
	 * Textarea Vue component
	 *
	 * @package bitrix
	 * @subpackage im
	 * @copyright 2001-2019 Bitrix
	 */
	ui_vue.Vue.component('bx-im-view-textarea', {
	  /**
	   * @emits 'send' {text: string}
	   * @emits 'edit' {}
	   * @emits 'writes' {text: string}
	   * @emits 'focus' {event: object} -- 'event' - focus event
	   * @emits 'blur' {event: object} -- 'event' - blur event
	   * @emits 'keyup' {event: object} -- 'event' - keyup event
	   * @emits 'keydown' {event: object} -- 'event' - keydown event
	   * @emits 'appButtonClick' {appId: string, event: object} -- 'appId' - application name, 'event' - event click
	   * @emits 'fileSelected' {fileInput: domNode} -- 'fileInput' - dom node element
	   */

	  /**
	   * @listens props.listenEventInsertText {text: string, breakline: boolean, position: string, cursor: string, focus: boolean} (global|application) -- insert text to textarea, see more in methods.insertText()
	   * @listens props.listenEventFocus {} (global|application) -- set focus on textarea
	   * @listens props.listenEventBlur {} (global|application) -- clear focus on textarea
	   */
	  props: {
	    siteId: {
	      default: 'default'
	    },
	    userId: {
	      default: 0
	    },
	    dialogId: {
	      default: 0
	    },
	    enableCommand: {
	      default: true
	    },
	    enableMention: {
	      default: true
	    },
	    desktopMode: {
	      default: false
	    },
	    enableEdit: {
	      default: false
	    },
	    enableFile: {
	      default: false
	    },
	    sendByEnter: {
	      default: true
	    },
	    autoFocus: {
	      default: null
	    },
	    writesEventLetter: {
	      default: 0
	    },
	    styles: {
	      type: Object,
	      default: function _default() {
	        return {};
	      }
	    },
	    listenEventInsertText: {
	      default: ''
	    },
	    listenEventFocus: {
	      default: ''
	    },
	    listenEventBlur: {
	      default: ''
	    }
	  },
	  data: function data() {
	    return {
	      placeholderMessage: '',
	      currentMessage: '',
	      previousMessage: '',
	      commandListen: false,
	      mentionListen: false,
	      stylesDefault: Object.freeze({
	        button: {
	          backgroundColor: null,
	          iconColor: null
	        }
	      })
	    };
	  },
	  created: function created() {
	    if (this.listenEventInsertText) {
	      ui_vue.Vue.event.$on(this.listenEventInsertText, this.onInsertText);
	      this.$root.$on(this.listenEventInsertText, this.onInsertText);
	    }

	    if (this.listenEventFocus) {
	      ui_vue.Vue.event.$on(this.listenEventFocus, this.onFocusSet);
	      this.$root.$on(this.listenEventFocus, this.onFocusSet);
	    }

	    if (this.listenEventBlur) {
	      ui_vue.Vue.event.$on(this.listenEventBlur, this.onFocusClear);
	      this.$root.$on(this.listenEventBlur, this.onFocusClear);
	    }

	    this.localStorage = im_lib_localstorage.LocalStorage;
	    this.textareaHistory = this.localStorage.get(this.siteId, this.userId, 'textarea-history', {});
	    this.currentMessage = this.textareaHistory[this.dialogId] || '';
	    this.placeholderMessage = this.currentMessage;
	  },
	  beforeDestroy: function beforeDestroy() {
	    if (this.listenEventInsertText) {
	      ui_vue.Vue.event.$off(this.listenEventInsertText, this.onInsertText);
	      this.$root.$off(this.listenEventInsertText, this.onInsertText);
	    }

	    if (this.listenEventFocus) {
	      ui_vue.Vue.event.$off(this.listenEventFocus, this.onFocusSet);
	      this.$root.$off(this.listenEventFocus, this.onFocusSet);
	    }

	    if (this.listenEventBlur) {
	      ui_vue.Vue.event.$off(this.listenEventBlur, this.onFocusClear);
	      this.$root.$off(this.listenEventBlur, this.onFocusClear);
	    }

	    clearTimeout(this.messageStoreTimeout);
	    this.localStorage.set(this.siteId, this.userId, 'textarea-history', this.textareaHistory);
	    this.localStorage = null;
	  },
	  computed: {
	    textareaClassName: function textareaClassName() {
	      return 'bx-im-textarea' + (im_lib_utils.Utils.device.isMobile() ? ' bx-im-textarea-mobile' : '');
	    },
	    buttonStyle: function buttonStyle() {
	      var styles = Object.assign({}, this.stylesDefault, this.styles);
	      var isIconDark = false;

	      if (styles.button.iconColor) {
	        isIconDark = im_lib_utils.Utils.isDarkColor(styles.button.iconColor);
	      } else {
	        isIconDark = !im_lib_utils.Utils.isDarkColor(styles.button.backgroundColor);
	      }

	      styles.button.className = isIconDark ? 'bx-im-textarea-send-button' : 'bx-im-textarea-send-button bx-im-textarea-send-button-bright-arrow';
	      styles.button.style = styles.button.backgroundColor ? 'background-color: ' + styles.button.backgroundColor + ';' : '';
	      return styles;
	    },
	    localize: function localize() {
	      return ui_vue.Vue.getFilteredPhrases('BX_MESSENGER_TEXTAREA_', this.$root.$bitrixMessages);
	    },
	    isIE11: function isIE11() {
	      return main_core.Browser.isIE11();
	    }
	  },
	  directives: {
	    'bx-im-focus': {
	      inserted: function inserted(element, params) {
	        if (params.value === true || params.value === null && !im_lib_utils.Utils.device.isMobile()) {
	          element.focus();
	        }
	      }
	    }
	  },
	  methods: {
	    /**
	     *
	     * @param text
	     * @param breakline - true/false (default)
	     * @param position - start, current (default), end
	     * @param cursor - start, before, after (default), end
	     * @param focus - set focus on textarea
	     */
	    insertText: function insertText(text) {
	      var breakline = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : false;
	      var position = arguments.length > 2 && arguments[2] !== undefined ? arguments[2] : 'current';
	      var cursor = arguments.length > 3 && arguments[3] !== undefined ? arguments[3] : 'after';
	      var focus = arguments.length > 4 && arguments[4] !== undefined ? arguments[4] : true;
	      var textarea = this.$refs.textarea;
	      var selectionStart = textarea.selectionStart;
	      var selectionEnd = textarea.selectionEnd;

	      if (position == 'start') {
	        if (breakline) {
	          text = text + "\n";
	        }

	        textarea.value = text + textarea.value;

	        if (focus) {
	          if (cursor == 'after') {
	            textarea.selectionStart = text.length;
	            textarea.selectionEnd = textarea.selectionStart;
	          } else if (cursor == 'before') {
	            textarea.selectionStart = 0;
	            textarea.selectionEnd = textarea.selectionStart;
	          }
	        }
	      } else if (position == 'current') {
	        if (breakline) {
	          if (textarea.value.substring(0, selectionStart).trim().length > 0) {
	            text = "\n" + text;
	          }

	          text = text + "\n";
	        } else {
	          if (textarea.value && !textarea.value.endsWith(' ')) {
	            text = ' ' + text;
	          }
	        }

	        textarea.value = textarea.value.substring(0, selectionStart) + text + textarea.value.substring(selectionEnd, textarea.value.length);

	        if (focus) {
	          if (cursor == 'after') {
	            textarea.selectionStart = selectionStart + text.length;
	            textarea.selectionEnd = textarea.selectionStart;
	          } else if (cursor == 'before') {
	            textarea.selectionStart = selectionStart;
	            textarea.selectionEnd = textarea.selectionStart;
	          }
	        }
	      } else if (position == 'end') {
	        if (breakline) {
	          if (textarea.value.substring(0, selectionStart).trim().length > 0) {
	            text = "\n" + text;
	          }

	          text = text + "\n";
	        } else {
	          if (textarea.value && !textarea.value.endsWith(' ')) {
	            text = ' ' + text;
	          }
	        }

	        textarea.value = textarea.value + text;

	        if (focus) {
	          if (cursor == 'after') {
	            textarea.selectionStart = textarea.value.length;
	            textarea.selectionEnd = textarea.selectionStart;
	          } else if (cursor == 'before') {
	            textarea.selectionStart = textarea.value.length - text.length;
	            textarea.selectionEnd = textarea.selectionStart;
	          }
	        }
	      }

	      if (focus) {
	        if (cursor == 'start') {
	          textarea.selectionStart = 0;
	          textarea.selectionEnd = 0;
	        } else if (cursor == 'end') {
	          textarea.selectionStart = textarea.value.length;
	          textarea.selectionEnd = textarea.selectionStart;
	        }

	        textarea.focus();
	      }

	      this.textChangeEvent();
	    },
	    sendMessage: function sendMessage(event) {
	      event.preventDefault();
	      this.$emit('send', {
	        text: this.currentMessage.trim()
	      });
	      var textarea = this.$refs.textarea;

	      if (textarea) {
	        textarea.value = '';
	      }

	      if (this.autoFocus === null || this.autoFocus) {
	        textarea.focus();
	      }

	      this.textChangeEvent();
	    },
	    textChangeEvent: function textChangeEvent() {
	      var _this = this;

	      var textarea = this.$refs.textarea;

	      if (!textarea) {
	        return;
	      }

	      var text = textarea.value.trim();

	      if (this.currentMessage === text) {
	        return;
	      }

	      if (this.writesEventLetter <= text.length) {
	        this.$emit('writes', {
	          text: text
	        });
	      }

	      this.previousMessage = this.currentMessage;
	      this.previousSelectionStart = textarea.selectionStart;
	      this.previousSelectionEnd = this.previousSelectionStart;
	      this.currentMessage = text;

	      if (text.toString().length > 0) {
	        this.textareaHistory[this.dialogId] = text;
	      } else {
	        delete this.textareaHistory[this.dialogId];
	      }

	      clearTimeout(this.messageStoreTimeout);
	      this.messageStoreTimeout = setTimeout(function () {
	        _this.localStorage.set(_this.siteId, _this.userId, 'textarea-history', _this.textareaHistory, _this.userId ? 0 : 10);
	      }, 500);
	    },
	    onKeyDown: function onKeyDown(event) {
	      this.$emit('keydown', event);
	      var textarea = event.target;
	      var text = textarea.value.trim();
	      var isMac = im_lib_utils.Utils.platform.isMac();
	      var isCtrlTEnable = im_lib_utils.Utils.platform.isBitrixDesktop() || !im_lib_utils.Utils.browser.isChrome(); // TODO see more im/install/js/im/im.js:12324

	      if (this.commandListen) ; else if (this.mentionListen) ; else if (!(event.altKey && event.ctrlKey)) {
	        if (this.enableMention && event.shiftKey && (event.keyCode == 61 || event.keyCode == 50 || event.keyCode == 187 || event.keyCode == 187) || event.keyCode == 107) ; else if (this.enableCommand && (event.keyCode == 191 || event.keyCode == 111 || event.keyCode == 220)) ;
	      }

	      if (event.keyCode == 27) {
	        if (textarea.value != '' && textarea === document.activeElement) {
	          event.preventDefault();
	          event.stopPropagation();
	        }

	        if (event.shiftKey) {
	          textarea.value = '';
	        }
	      } else if (event.metaKey || event.ctrlKey) {
	        // TODO translit messages
	        if (isCtrlTEnable && event.key === 't' || !isCtrlTEnable && event.key === 'e') {
	          // translit case
	          event.preventDefault();
	        } else if (['b', 's', 'i', 'u'].includes(event.key)) {
	          var selectionStart = textarea.selectionStart;
	          var selectionEnd = textarea.selectionEnd;
	          var tagStart = '[' + event.key.toLowerCase() + ']';
	          var tagEnd = '[/' + event.key.toLowerCase() + ']';
	          var selected = textarea.value.substring(selectionStart, selectionEnd);

	          if (selected.startsWith(tagStart) && selected.endsWith(tagEnd)) {
	            selected = selected.substring(tagStart.length, selected.indexOf(tagEnd));
	          } else {
	            selected = tagStart + selected + tagEnd;
	          }

	          textarea.value = textarea.value.substring(0, selectionStart) + selected + textarea.value.substring(selectionEnd, textarea.value.length);
	          textarea.selectionStart = selectionStart;
	          textarea.selectionEnd = selectionStart + selected.length;
	          event.preventDefault();
	        }
	      }

	      if (event.keyCode == 9) {
	        this.insertText("\t");
	        event.preventDefault();
	      } else if (this.enableEdit && event.keyCode == 38 && text.length <= 0) {
	        this.$emit('edit', {});
	      } else if (event.keyCode == 13) {
	        if (im_lib_utils.Utils.device.isMobile()) ; else if (this.sendByEnter == true) {
	          if (event.ctrlKey || event.altKey || event.shiftKey) {
	            if (!event.shiftKey) {
	              this.insertText("\n");
	            }
	          } else if (text.length <= 0) {
	            event.preventDefault();
	          } else {
	            this.sendMessage(event);
	          }
	        } else {
	          if (event.ctrlKey == true) {
	            this.sendMessage(event);
	          } else if (isMac && (event.metaKey == true || event.altKey == true)) {
	            this.sendMessage(event);
	          }
	        }
	      } else if ((event.ctrlKey || event.metaKey) && event.key == 'z') {
	        if (this.previousMessage) {
	          textarea.value = this.previousMessage;
	          textarea.selectionStart = this.previousSelectionStart;
	          textarea.selectionEnd = this.previousSelectionEnd;
	          this.previousMessage = '';
	          event.preventDefault();
	        }
	      }
	    },
	    onKeyUp: function onKeyUp(event) {
	      this.$emit('keyup', {
	        event: event,
	        text: this.currentMessage
	      });
	      this.textChangeEvent();
	    },
	    onPaste: function onPaste(event) {
	      this.$nextTick(this.textChangeEvent);
	    },
	    onInput: function onInput(event) {
	      this.textChangeEvent();
	    },
	    onFocus: function onFocus(event) {
	      this.$emit('focus', event);
	    },
	    onBlur: function onBlur(event) {
	      this.$emit('blur', event);
	    },
	    onAppButtonClick: function onAppButtonClick(appId, event) {
	      this.$emit('appButtonClick', {
	        appId: appId,
	        event: event
	      });
	    },
	    onInsertText: function onInsertText() {
	      var event = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};

	      if (!event.text) {
	        return false;
	      }

	      this.insertText(event.text, event.breakline, event.position, event.cursor, event.focus);
	      this.$emit('keyup', {
	        event: event,
	        text: this.currentMessage
	      });
	      return true;
	    },
	    onFocusSet: function onFocusSet() {
	      this.$refs.textarea.focus();
	      return true;
	    },
	    onFocusClear: function onFocusClear() {
	      this.$refs.textarea.blur();
	      return true;
	    },
	    onFileClick: function onFileClick(event) {
	      event.target.value = "";
	    },
	    onFileSelect: function onFileSelect(event) {
	      this.$emit('fileSelected', {
	        fileChangeEvent: event,
	        fileInput: event.target
	      });
	    },
	    log: function log(text, skip, event) {
	      console.warn(text);

	      if (skip == 1) {
	        event.preventDefault();
	      }
	    },
	    preventDefault: function preventDefault(event) {
	      event.preventDefault();
	    }
	  },
	  template: "\n\t\t<div :class=\"textareaClassName\">\n\t\t\t<div class=\"bx-im-textarea-box\">\n\t\t\t\t<textarea ref=\"textarea\" class=\"bx-im-textarea-input\" @keydown=\"onKeyDown\" @keyup=\"onKeyUp\" @paste=\"onPaste\" @input=\"onInput\" @focus=\"onFocus\" @blur=\"onBlur\" v-bx-im-focus=\"autoFocus\" :placeholder=\"localize.BX_MESSENGER_TEXTAREA_PLACEHOLDER\">{{placeholderMessage}}</textarea>\n\t\t\t\t<transition enter-active-class=\"bx-im-textarea-send-button-show\" leave-active-class=\"bx-im-textarea-send-button-hide\">\n\t\t\t\t\t<button \n\t\t\t\t\t\tv-if=\"currentMessage\" \n\t\t\t\t\t\t:class=\"buttonStyle.button.className\" \n\t\t\t\t\t\t:style=\"buttonStyle.button.style\" \n\t\t\t\t\t\t:title=\"localize.BX_MESSENGER_TEXTAREA_BUTTON_SEND\"\n\t\t\t\t\t\t@click=\"sendMessage\" \n\t\t\t\t\t\t@touchend=\"sendMessage\" \n\t\t\t\t\t\t@mousedown=\"preventDefault\" \n\t\t\t\t\t\t@touchstart=\"preventDefault\" \n\t\t\t\t\t/>\n\t\t\t\t</transition>\n\t\t\t</div>\n\t\t\t<div class=\"bx-im-textarea-app-box\">\n\t\t\t\t<label v-if=\"enableFile && !isIE11\" class=\"bx-im-textarea-app-button bx-im-textarea-app-file\" :title=\"localize.BX_MESSENGER_TEXTAREA_FILE\">\n\t\t\t\t\t<input type=\"file\" @click=\"onFileClick($event)\" @change=\"onFileSelect($event)\" multiple>\n\t\t\t\t</label>\n\t\t\t\t<button class=\"bx-im-textarea-app-button bx-im-textarea-app-smile\" :title=\"localize.BX_MESSENGER_TEXTAREA_SMILE\" @click=\"onAppButtonClick('smile', $event)\"></button>\n\t\t\t\t<button v-if=\"false\" class=\"bx-im-textarea-app-button bx-im-textarea-app-gif\" :title=\"localize.BX_MESSENGER_TEXTAREA_GIPHY\" @click=\"onAppButtonClick('giphy', $event)\"></button>\n\t\t\t</div>\n\t\t</div>\n\t"
	});

}((this.window = this.window || {}),BX,BX.Messenger.Lib,BX.Messenger.Lib,BX));
//# sourceMappingURL=textarea.bundle.js.map
