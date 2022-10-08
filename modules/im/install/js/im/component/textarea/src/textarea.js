/**
 * Bitrix Messenger
 * Textarea Vue component
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2019 Bitrix
 */

import 'ui.design-tokens';
import './textarea.css';
import {BitrixVue} from "ui.vue";
import {LocalStorage} from "im.lib.localstorage";
import {Utils} from "im.lib.utils";
import {Browser} from 'main.core';
import {Vuex} from "ui.vue.vuex";

import { EventEmitter } from 'main.core.events';
import { EventType, DeviceType } from "im.const";

BitrixVue.component('bx-im-component-textarea',
{
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

	props:
	{
		siteId: { default: 'default' },
		userId: { default: 0 },
		dialogId: { default: 0 },
		enableCommand: { default: true },
		enableMention: { default: true },
		desktopMode: { default: false },
		enableEdit: { default: false },
		enableFile: { default: false },
		sendByEnter: { default: true },
		autoFocus: { default: null },
		writesEventLetter: { default: 0 },
		styles: {
			type: Object,
			default: function () {
				return {}
			}
		},
	},
	data()
	{
		return {
			placeholderMessage: '',
			currentMessage: '',
			previousMessage: '',
			commandListen: false,
			mentionListen: false,
			stylesDefault: Object.freeze({button: { backgroundColor: null, iconColor: null }})
		}
	},
	created()
	{
		EventEmitter.subscribe(EventType.textarea.insertText, this.onInsertText);
		EventEmitter.subscribe(EventType.textarea.setFocus, this.onFocusSet);
		EventEmitter.subscribe(EventType.textarea.setBlur, this.onFocusClear);

		this.localStorage = LocalStorage;

		this.textareaHistory = this.localStorage.get(this.siteId, this.userId, 'textarea-history', {});
		this.currentMessage = this.textareaHistory[this.dialogId] || '';
		this.placeholderMessage = this.currentMessage;
	},
	beforeDestroy()
	{
		EventEmitter.unsubscribe(EventType.textarea.insertText, this.onInsertText);
		EventEmitter.unsubscribe(EventType.textarea.setFocus, this.onFocusSet);
		EventEmitter.unsubscribe(EventType.textarea.setBlur, this.onFocusClear);

		clearTimeout(this.messageStoreTimeout);
		this.localStorage.set(this.siteId, this.userId, 'textarea-history', this.textareaHistory);
		this.localStorage = null;
	},
	computed:
	{
		textareaClassName()
		{
			return ['bx-im-textarea', {
				'bx-im-textarea-dark-background': this.isDarkBackground,
				'bx-im-textarea-mobile': this.isMobile,
			}];
		},

		buttonStyle()
		{
			let styles = Object.assign({}, this.stylesDefault, this.styles);

			let isIconDark = false;
			if (styles.button.iconColor)
			{
				isIconDark = Utils.isDarkColor(styles.button.iconColor);
			}
			else
			{
				isIconDark = !Utils.isDarkColor(styles.button.backgroundColor);
			}

			styles.button.className = isIconDark? 'bx-im-textarea-send-button': 'bx-im-textarea-send-button bx-im-textarea-send-button-bright-arrow';
			styles.button.style = styles.button.backgroundColor? 'background-color: '+styles.button.backgroundColor+';': '';

			return styles;
		},
		isDarkBackground()
		{
			return this.application.options.darkBackground;
		},
		isMobile()
		{
			return this.application.device.type === DeviceType.mobile;
		},
		localize()
		{
			return BitrixVue.getFilteredPhrases('BX_MESSENGER_TEXTAREA_', this)
		},
		isIE11()
		{
			return Browser.isIE11();
		},
		...Vuex.mapState({
			application: state => state.application,
		})
	},
	directives: {
		'bx-im-focus':
		{
			inserted(element, params)
			{
				if (
					params.value === true
					|| params.value === null && !this.isMobile
				)
				{
					element.focus();
				}
			}
		}
	},
	methods:
	{
		/**
		 *
		 * @param text
		 * @param breakline - true/false (default)
		 * @param position - start, current (default), end
		 * @param cursor - start, before, after (default), end
		 * @param focus - set focus on textarea
		 */
		insertText(text, breakline = false, position = 'current', cursor = 'after', focus = true)
		{
			let textarea = this.$refs.textarea;
			let selectionStart = textarea.selectionStart;
			let selectionEnd = textarea.selectionEnd;

			if (position == 'start')
			{
				if (breakline)
				{
					text = text+"\n";
				}
				textarea.value = text + textarea.value;

				if (focus)
				{
					if (cursor == 'after')
					{
						textarea.selectionStart = text.length;
						textarea.selectionEnd = textarea.selectionStart;
					}
					else if (cursor == 'before')
					{
						textarea.selectionStart = 0;
						textarea.selectionEnd = textarea.selectionStart;
					}
				}
			}
			else if (position == 'current')
			{
				if (breakline)
				{
					if (textarea.value.substring(0, selectionStart).trim().length > 0)
					{
						text = "\n"+text;
					}
					text = text+"\n";
				}
				else
				{
					if (textarea.value && !textarea.value.endsWith(' '))
					{
						text = ' '+text;
					}
				}

				textarea.value = textarea.value.substring(0, selectionStart) + text + textarea.value.substring(selectionEnd, textarea.value.length);

				if (focus)
				{
					if (cursor == 'after')
					{
						textarea.selectionStart = selectionStart+text.length;
						textarea.selectionEnd = textarea.selectionStart;
					}
					else if (cursor == 'before')
					{
						textarea.selectionStart = selectionStart;
						textarea.selectionEnd = textarea.selectionStart;
					}
				}
			}
			else if (position == 'end')
			{
				if (breakline)
				{
					if (textarea.value.substring(0, selectionStart).trim().length > 0)
					{
						text = "\n"+text;
					}
					text = text+"\n";
				}
				else
				{
					if (textarea.value && !textarea.value.endsWith(' '))
					{
						text = ' '+text;
					}
				}

				textarea.value = textarea.value+text;

				if (focus)
				{
					if (cursor == 'after')
					{
						textarea.selectionStart = textarea.value.length;
						textarea.selectionEnd = textarea.selectionStart;
					}
					else if (cursor == 'before')
					{
						textarea.selectionStart = textarea.value.length-text.length;
						textarea.selectionEnd = textarea.selectionStart;
					}
				}
			}

			if (focus)
			{
				if (cursor == 'start')
				{
					textarea.selectionStart = 0;
					textarea.selectionEnd = 0;
				}
				else if (cursor == 'end')
				{
					textarea.selectionStart = textarea.value.length;
					textarea.selectionEnd = textarea.selectionStart;
				}

				textarea.focus();
			}

			this.textChangeEvent();
		},

		sendMessage(event)
		{
			event.preventDefault();

			EventEmitter.emit(EventType.textarea.sendMessage, {text: this.currentMessage.trim()});

			let textarea = this.$refs.textarea;
			if (textarea)
			{
				textarea.value = '';
			}

			if (this.autoFocus === null || this.autoFocus)
			{
				textarea.focus();
			}

			this.textChangeEvent();
		},

		textChangeEvent()
		{
			let textarea = this.$refs.textarea;
			if (!textarea)
			{
				return;
			}

			let text = textarea.value.trim();
			if (this.currentMessage === text)
			{
				return;
			}

			if (this.writesEventLetter <= text.length)
			{
				EventEmitter.emit(EventType.textarea.startWriting, {text});
			}

			this.previousMessage = this.currentMessage;
			this.previousSelectionStart = textarea.selectionStart;
			this.previousSelectionEnd = this.previousSelectionStart;
			this.currentMessage = text;

			if (text.toString().length > 0)
			{
				this.textareaHistory[this.dialogId] = text;
			}
			else
			{
				delete this.textareaHistory[this.dialogId];
			}

			clearTimeout(this.messageStoreTimeout);
			this.messageStoreTimeout = setTimeout(() => {
				this.localStorage.set(this.siteId, this.userId, 'textarea-history', this.textareaHistory, this.userId? 0: 10);
			}, 500);
		},

		onKeyDown(event)
		{
			this.$emit('keydown', event);

			let textarea = event.target;
			let text = textarea.value.trim();
			let isMac = Utils.platform.isMac();
			let isCtrlTEnable = Utils.platform.isBitrixDesktop() || !Utils.browser.isChrome();

			// TODO see more im/install/js/im/im.js:12324
			if (this.commandListen)
			{
			}
			else if (this.mentionListen)
			{
			}
			else if (!(event.altKey && event.ctrlKey))
			{
				if (this.enableMention && (event.shiftKey  && (event.keyCode == 61 || event.keyCode == 50 || event.keyCode == 187 || event.keyCode == 187)) || event.keyCode == 107)
				{
					// mention case
				}
				else if (this.enableCommand && (event.keyCode == 191 || event.keyCode == 111 || event.keyCode == 220))
				{
					// command case
				}
			}

			if (event.keyCode == 27)
			{
				if (textarea.value != '' && textarea === document.activeElement)
				{
					event.preventDefault();
					event.stopPropagation();
				}
				if (event.shiftKey)
				{
					textarea.value = '';
				}
			}
			else if (event.metaKey || event.ctrlKey)
			{
				// TODO translit messages
				if (
					isCtrlTEnable && event.key === 't'
					|| !isCtrlTEnable && event.key === 'e'
				)
				{
					// translit case
					event.preventDefault();
				}
				else if (['b','s','i','u'].includes(event.key))
				{
					let selectionStart = textarea.selectionStart;
					let selectionEnd = textarea.selectionEnd;

					let tagStart = '['+event.key.toLowerCase()+']';
					let tagEnd = '[/'+event.key.toLowerCase()+']';
					let selected = textarea.value.substring(selectionStart, selectionEnd);

					if (selected.startsWith(tagStart) && selected.endsWith(tagEnd))
					{
						selected = selected.substring(tagStart.length, selected.indexOf(tagEnd));
					}
					else
					{
						selected = tagStart + selected + tagEnd;
					}

					textarea.value = textarea.value.substring(0, selectionStart) + selected + textarea.value.substring(selectionEnd, textarea.value.length);

					textarea.selectionStart = selectionStart;
					textarea.selectionEnd = selectionStart + selected.length;

					event.preventDefault();
				}
			}

			if (event.keyCode == 9)
			{
				this.insertText("\t");
				event.preventDefault();
			}
			else if (this.enableEdit && event.keyCode == 38 && text.length <= 0)
			{
				EventEmitter.emit(EventType.textarea.edit, {});
			}
			else if (event.keyCode == 13)
			{
				if (this.isMobile)
				{
				}
				else if (this.sendByEnter == true)
				{
					if (event.ctrlKey || event.altKey || event.shiftKey)
					{
						if (!event.shiftKey)
						{
							this.insertText("\n");
						}
					}
					else if (text.length <= 0)
					{
						event.preventDefault();
					}
					else
					{
						this.sendMessage(event);
					}
				}
				else
				{
					if (event.ctrlKey == true)
					{
						this.sendMessage(event);
					}
					else if (isMac && (event.metaKey == true || event.altKey == true))
					{
						this.sendMessage(event);
					}
				}
			}
			else if ((event.ctrlKey || event.metaKey) && event.key == 'z')
			{
				if (this.previousMessage)
				{
					textarea.value = this.previousMessage;
					textarea.selectionStart = this.previousSelectionStart;
					textarea.selectionEnd = this.previousSelectionEnd;

					this.previousMessage = '';
					event.preventDefault();
				}
			}
		},
		onKeyUp(event)
		{
			EventEmitter.emit(EventType.textarea.keyUp, {event, text: this.currentMessage});
			this.textChangeEvent();
		},
		onPaste(event)
		{
			this.$nextTick(this.textChangeEvent);
		},
		onInput(event)
		{
			this.textChangeEvent();
		},
		onFocus(event)
		{
			EventEmitter.emit(EventType.textarea.focus, event);
		},
		onBlur(event)
		{
			EventEmitter.emit(EventType.textarea.blur, event);
		},
		onAppButtonClick(appId, event)
		{
			EventEmitter.emit(EventType.textarea.appButtonClick, {appId, event});
		},
		onInsertText({data: event = {}})
		{
			if (!event.text)
			{
				return false;
			}
			this.insertText(event.text, event.breakline, event.position, event.cursor, event.focus);

			EventEmitter.emit(EventType.textarea.keyUp, {event, text: this.currentMessage});

			return true;
		},
		onFocusSet()
		{
			this.$refs.textarea.focus();

			return true;
		},
		onFocusClear()
		{
			this.$refs.textarea.blur();

			return true;
		},
		onFileClick(event)
		{
			event.target.value = "";
		},
		onFileSelect(event)
		{
			EventEmitter.emit(EventType.textarea.fileSelected, {
				fileChangeEvent: event,
				fileInput: event.target
			});
		},
		log(text, skip, event)
		{
			console.warn(text);
			if (skip == 1)
			{
				event.preventDefault();
			}
		},
		preventDefault(event)
		{
			event.preventDefault();
		}
	},
	// language=Vue
	template: `
		<div :class="textareaClassName">
			<div class="bx-im-textarea-box">
				<textarea ref="textarea" class="bx-im-textarea-input" @keydown="onKeyDown" @keyup="onKeyUp" @paste="onPaste" @input="onInput" @focus="onFocus" @blur="onBlur" v-bx-im-focus="autoFocus" :placeholder="localize.BX_MESSENGER_TEXTAREA_PLACEHOLDER">{{placeholderMessage}}</textarea>
				<transition enter-active-class="bx-im-textarea-send-button-show" leave-active-class="bx-im-textarea-send-button-hide">
					<button 
						v-if="currentMessage" 
						:class="buttonStyle.button.className" 
						:style="buttonStyle.button.style" 
						:title="localize.BX_MESSENGER_TEXTAREA_BUTTON_SEND"
						@click="sendMessage" 
						@touchend="sendMessage" 
						@mousedown="preventDefault" 
						@touchstart="preventDefault" 
					/>
				</transition>
			</div>
			<div class="bx-im-textarea-app-box">
				<label v-if="enableFile && !isIE11" class="bx-im-textarea-app-button bx-im-textarea-app-file" :title="localize.BX_MESSENGER_TEXTAREA_FILE">
					<input type="file" @click="onFileClick($event)" @change="onFileSelect($event)" multiple>
				</label>
				<button class="bx-im-textarea-app-button bx-im-textarea-app-smile" :title="localize.BX_MESSENGER_TEXTAREA_SMILE" @click="onAppButtonClick('smile', $event)"></button>
				<button v-if="false" class="bx-im-textarea-app-button bx-im-textarea-app-gif" :title="localize.BX_MESSENGER_TEXTAREA_GIPHY" @click="onAppButtonClick('giphy', $event)"></button>
			</div>
		</div>
	`
});
