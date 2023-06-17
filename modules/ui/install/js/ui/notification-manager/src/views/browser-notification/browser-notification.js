import {Type, Tag, Loc, Dom, Text} from 'main.core';
import {UI} from 'ui.notification';
import BrowserNotificationAction from './browser-notification-action';

import 'ui.design-tokens';
import './browser-notification.css';

export default class BrowserNotification extends UI.Notification.Balloon
{
	static KEY_CODE = {
		ENTER: 13,
		ESC: 27,
	};

	constructor(options: UI.Notification.BalloonOptions)
	{
		super(options);

		this.userInputContainerNode = null;
		this.userInputNode = null;
	}

	setActions(actions)
	{
		this.actions = [];

		if (Type.isArray(actions))
		{
			actions.forEach(action => this.actions.push(new BrowserNotificationAction(this, action)));
		}
	}

	getContainer(): HTMLElement
	{
		if (this.container !== null)
		{
			return this.container;
		}

		const onMouseEnter = () => this.handleMouseEnter();
		const onMouseLeave = () => this.handleMouseLeave();

		this.container = Tag.render`
			<div
				class="ui-notification-manager-browser-balloon"
				onmouseenter="${onMouseEnter}"
				onmouseleave="${onMouseLeave}"
			>
				${this.render()}
			</div>
		`;

		return this.container;
	}

	render(): HTMLElement
	{
		this.animationClassName = "ui-notification-manager-browser-balloon-animate";

		const contentWidth = Type.isNumber(this.getWidth()) ? this.getWidth() + 'px' : this.getWidth();

		return Tag.render`
			<div
				class="ui-notification-manager-browser-content"
				style="width: ${contentWidth}"
			>
				<div
					class="ui-notification-manager-browser-message"
					onclick="${this.handleContentClick.bind(this)}"
				>
					${this.getIconNode()}
					<div class="ui-notification-manager-browser-column">
						${this.getTitleNode()}
						${this.getTextNode()}
						${this.getUserInputContainerNode()}
						${this.getActionsNode()}
					</div>
				</div>
				${this.getCloseButtonNode()}
			</div>
		`;
	}

	getTitleNode(): HTMLElement | string
	{
		if (!Type.isStringFilled(this.getData().title))
		{
			return '';
		}

		const title = Dom.create({
			tag: 'span',
			attrs: {className: 'ui-notification-manager-browser-title'},
			text: this.getData().title
		}).outerHTML;

		return Tag.render`<div class="ui-notification-manager-browser-title">${title}<div>`;
	}

	getTextNode(): HTMLElement | string
	{
		if (!Type.isStringFilled(this.getData().text))
		{
			return '';
		}

		return Dom.create({
			tag: 'div',
			attrs: {className: 'ui-notification-manager-browser-text'},
			text: this.getData().text,
		});
	}

	getIconNode(): HTMLElement | string
	{
		if (!Type.isStringFilled(this.getData().icon))
		{
			return '';
		}

		return Dom.create({
			tag: 'div',
			className: 'ui-notification-manager-browser-column',
			children: [
				Dom.create({
					tag: 'img',
					style: {height: '44px', width: '44px'},
					attrs: {
						className: 'ui-notification-manager-browser-icon',
						src: this.getData().icon,
					},
				})
			]
		});
	}

	getActionsNode(): HTMLElement | string
	{
		const actions = this.getActions().map(action => action.getContainer());
		if (!Type.isArrayFilled(actions))
		{
			return '';
		}

		return Tag.render`
			<div class="ui-notification-manager-browser-actions">
				${actions}
			</div>
		`;
	}

	getUserInputContainerNode(): HTMLElement | string
	{
		if (!Type.isString(this.getData().inputPlaceholderText))
		{
			return '';
		}

		const onInputReplyClick = (event: Event) => event.stopPropagation();

		const id = Text.encode(this.getId());
		const placeholderText = Text.encode(this.getData().inputPlaceholderText);

		return Tag.render`
			<div class="ui-notification-manager-browser-actions">
				<div class="ui-notification-manager-browser-column ui-notification-manager-browser-column-wide">
					<div class="ui-notification-manager-browser-row">
						<button
							class="ui-notification-manager-browser-button"
							id="ui-notification-manager-browser-reply-toggle-${id}"
							onclick="${this.toggleUserInputContainerNode.bind(this)}"
						>
							<span class="ui-btn-text">${Loc.getMessage('UI_NOTIFICATION_MANAGER_REPLY')}</span>
						</button>
					</div>
					<div
						class="ui-notification-manager-browser-row ui-notification-manager-browser-row-reply"
						id="ui-notification-manager-browser-reply-container-${id}"
					>
						<div class="ui-notification-manager-browser-reply-wrapper">
							<input
								type="text"
								class="ui-notification-manager-browser-input-reply"
								placeholder="${placeholderText}"
								id="ui-notification-manager-browser-reply-${id}"
								onkeyup="${this.handleUserInputEnter.bind(this)}"
								onclick="${onInputReplyClick}"
								disabled
							>
						</div>
						<div
							class="ui-notification-manager-browser-button-reply"
							onclick="${this.handleUserInputClick.bind(this)}"
						/>
					</div>
				</div>
			</div>
		`;
	}

	toggleUserInputContainerNode(event: Event): void
	{
		event.stopPropagation();

		const id = Text.encode(this.getId());

		if (!this.userInputContainerNode)
		{
			this.userInputContainerNode =
				document.getElementById('ui-notification-manager-browser-reply-container-' + id)
			;
		}

		if (!this.userInputNode)
		{
			this.userInputNode =
				document.getElementById('ui-notification-manager-browser-reply-' + id)
			;
		}

		if (!this.replyToggleButton)
		{
			this.replyToggleButton =
				document.getElementById('ui-notification-manager-browser-reply-toggle-' + id)
			;
		}

		this.showUserInput = !this.showUserInput;
		if (this.showUserInput)
		{
			this.setAutoHide(false);
			this.deactivateAutoHide();

			this.replyToggleButton.style.display = 'none';
			this.userInputContainerNode.classList.add('ui-notification-manager-browser-row-reply-animate');
			this.userInputNode.disabled = false;
			this.userInputNode.focus();
		}
		else
		{
			this.setAutoHide(true);
			this.activateAutoHide();

			this.replyToggleButton.style.display = 'block';
			this.userInputContainerNode.classList.remove('ui-notification-manager-browser-row-reply-animate');
			this.userInputNode.disabled = true;
		}
	}

	getCloseButtonNode(): HTMLElement | string
	{
		if (!this.isCloseButtonVisible())
		{
			return '';
		}

		return Tag.render`
			<div
				class="ui-notification-manager-browser-button-close"
				onclick="${this.handleCloseBtnClick.bind(this)}"
			/>
		`;
	}

	handleCloseBtnClick(event: Event): void
	{
		event.stopPropagation();

		if (Type.isFunction(this.getData().closedByUserHandler))
		{
			this.getData().closedByUserHandler();
		}

		super.handleCloseBtnClick();
	}

	handleContentClick(): void
	{
		if (Type.isFunction(this.getData().clickHandler))
		{
			this.getData().clickHandler();
		}

		this.close();
	}

	handleUserInputEnter(event: Event): void
	{
		if (!Type.isFunction(this.getData().userInputHandler))
		{
			return;
		}

		const userInput = event.target.value;

		if (event.keyCode === BrowserNotification.KEY_CODE.ENTER && userInput !== '')
		{
			this.getData().userInputHandler(userInput);
			this.close();

			return;
		}

		if (event.keyCode === BrowserNotification.KEY_CODE.ESC && userInput === '')
		{
			if (Type.isFunction(this.getData().closedByUserHandler))
			{
				this.getData().closedByUserHandler();
			}

			this.close();
		}
	}

	handleUserInputClick(event: Event): void
	{
		event.stopPropagation();

		if (!Type.isFunction(this.getData().userInputHandler))
		{
			return;
		}

		const userInput = this.userInputNode.value;

		if (userInput !== '')
		{
			this.getData().userInputHandler(userInput);
			this.close();
		}
	}
}
