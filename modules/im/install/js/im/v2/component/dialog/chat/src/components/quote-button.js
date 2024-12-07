import { Event } from 'main.core';

import { Quote } from 'im.v2.lib.quote';
import { Utils } from 'im.v2.lib.utils';
import { MessengerSlider } from 'im.v2.lib.slider';

import '../css/quote-button.css';

import type { JsonObject } from 'main.core';
import type { ImModelMessage } from 'im.v2.model';

const CONTAINER_HEIGHT = 44;
const CONTAINER_WIDTH = 60;
const CONTAINER_OFFSET = 10;
const slider = MessengerSlider.getInstance().getCurrent();
const sliderRect = slider?.layout.container.getBoundingClientRect();
const offsetY = sliderRect?.top ?? 0;

const MESSAGE_TEXT_NODE_CLASS = '.bx-im-message-default-content__text';

// @vue/component
export const QuoteButton = {
	name: 'QuoteButton',
	props: {
		dialogId: {
			type: String,
			default: '',
		},
	},
	data(): JsonObject
	{
		return {
			text: '',
			message: null,
			mouseX: 0,
			mouseY: 0,
		};
	},
	computed:
	{
		containerStyle(): {top: string, left: string, width: string, height: string}
		{
			return {
				top: `${this.mouseY - CONTAINER_HEIGHT - CONTAINER_OFFSET - offsetY}px`,
				left: `${this.mouseX - CONTAINER_WIDTH / 2}px`,
				width: `${CONTAINER_WIDTH}px`,
				height: `${CONTAINER_HEIGHT}px`,
			};
		},
	},
	mounted()
	{
		Event.bind(window, 'mousedown', this.onMouseDown);
	},
	methods:
	{
		onMessageMouseUp(message: ImModelMessage, event: MouseEvent)
		{
			if (event.button === 2)
			{
				return;
			}

			this.prepareSelectedText();
			this.message = message;
			this.mouseX = event.clientX;
			this.mouseY = event.clientY;
		},
		onMouseDown(event: MouseEvent)
		{
			const container = this.$refs.container;
			if (!container || container.contains(event.target))
			{
				return;
			}

			this.$emit('close');
		},
		prepareSelectedText(): string
		{
			if (Utils.browser.isFirefox())
			{
				this.text = window.getSelection().toString();

				return;
			}

			const range = window.getSelection().getRangeAt(0);
			const rangeContents = range.cloneContents();
			let nodesToIterate = rangeContents.childNodes;

			const messageNode = rangeContents.querySelector(MESSAGE_TEXT_NODE_CLASS);
			if (messageNode)
			{
				nodesToIterate = messageNode.childNodes;
			}

			for (const node of nodesToIterate)
			{
				if (this.isImage(node))
				{
					this.text += node.getAttribute('data-code') ?? node.getAttribute('alt');
				}
				else if (this.isLineBreak(node))
				{
					this.text += '\n';
				}
				else
				{
					this.text += node.textContent;
				}
			}
		},
		isImage(node: HTMLElement): boolean
		{
			if (!(node instanceof HTMLElement))
			{
				return false;
			}

			return node.tagName.toLowerCase() === 'img';
		},
		isLineBreak(node: HTMLElement): boolean
		{
			return node.nodeName.toLowerCase() === 'br';
		},
		isText(node: HTMLElement): boolean
		{
			return node.nodeName === '#text';
		},
		isMessageTextNode(node: HTMLElement): boolean
		{
			if (!(node instanceof HTMLElement))
			{
				return false;
			}
			const textNode = node.matches(MESSAGE_TEXT_NODE_CLASS);

			return Boolean(textNode);
		},
		extractTextFromMessageNode(node: HTMLElement): string
		{
			const textNode = node.querySelector(MESSAGE_TEXT_NODE_CLASS);
			if (!textNode)
			{
				return node.textContent;
			}

			return textNode.textContent;
		},
		onQuoteClick()
		{
			Quote.sendQuoteEvent(this.message, this.text, this.dialogId);
			this.$emit('close');
		},
	},
	template: `
		<div ref="container" @click="onQuoteClick" :style="containerStyle" class="bx-im-dialog-chat__quote-button">
			<div class="bx-im-dialog-chat__quote-icon"></div>
			<div class="bx-im-dialog-chat__quote-icon --hover"></div>
		</div>
	`,
};
