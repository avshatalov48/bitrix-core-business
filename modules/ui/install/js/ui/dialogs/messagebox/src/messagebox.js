import { Type, Loc, Cache } from 'main.core';
import { Popup } from 'main.popup';
import MessageBoxButtons from './messageboxbuttons';
import 'ui.buttons';

/**
 * @namespace {BX.UI.Dialogs}
 */
export default class MessageBox
{
	/** @var {Popup} */
	popupWindow = null;
	title = null;
	message = null;
	modal = true;
	popupOptions = {};
	minWidth = 300;
	minHeight = 130;
	maxWidth = 400;
	buttons = [];
	mediumButtonSize: false;
	cacheable: false;

	okCallback = null;
	cancelCallback = null;
	yesCallback = null;
	noCallback = null;

	constructor(options = {})
	{
		options = Type.isPlainObject(options) ? options : {};
		this.popupOptions = Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};

		this.cache = new Cache.MemoryCache();
		this.handleButtonClick = this.handleButtonClick.bind(this);

		this.modal = options.modal !== false;
		this.cacheable = options.cacheable === true;

		this.setTitle(options.title);
		this.setMessage(options.message);

		this.setOkCallback(options.onOk);
		this.setCancelCallback(options.onCancel);
		this.setYesCallback(options.onYes);
		this.setNoCallback(options.onNo);

		if (Type.isBoolean(options.mediumButtonSize))
		{
			this.mediumButtonSize = options.mediumButtonSize;
		}
		else if (this.getTitle() !== null)
		{
			this.mediumButtonSize = true;
		}

		if (this.getTitle() !== null)
		{
			this.popupOptions.closeIcon = true;
		}

		if (this.isMediumButtonSize())
		{
			this.minWidth = 400;
			this.minHeight = 200;
			this.maxWidth = 420;
		}

		this.minWidth = Type.isNumber(options.minWidth) ? options.minWidth : this.minWidth;
		this.minHeight = Type.isNumber(options.minHeight) ? options.minHeight : this.minHeight;
		this.maxWidth = Type.isNumber(options.maxWidth) ? options.maxWidth : this.maxWidth;

		this.setOkCaption(options.okCaption);
		this.setCancelCaption(options.cancelCaption);
		this.setYesCaption(options.yesCaption);
		this.setNoCaption(options.noCaption);

		this.setButtons(options.buttons);
	}

	/**
	 * @param {string} message
	 * @param args
	 * @example
	 * BX.UI.Dialogs.{MessageBox.alert('Message');
	 * BX.UI.Dialogs.MessageBox.alert('Message', (messageBox, button, event) => {});
	 * BX.UI.Dialogs.MessageBox.alert('Message', (messageBox, button, event) => {}, 'Proceed');
	 * BX.UI.Dialogs.MessageBox.alert('Message', 'Title');
	 * BX.UI.Dialogs.MessageBox.alert('Message', 'Title', (messageBox, button, event) => {});
	 * BX.UI.Dialogs.MessageBox.alert('Message', 'Title', (messageBox, button, event) => {}, 'Proceed');
	 */
	static alert(message: string, ...args): MessageBox
	{
		let title = null;
		let okCallback = null;
		let okCaption = null;

		if (args.length > 0)
		{
			if (Type.isString(args[0]))
			{
				[title, okCallback, okCaption] = args;
			}
			else
			{
				[okCallback, okCaption] = args;
			}
		}

		const messageBox = this.create({
			message,
			title,
			okCaption,
			onOk: okCallback,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
		});
		messageBox.show();

		return messageBox;
	}

	/**
	 *
	 * @param {string} message
	 * @param args
	 *
	 * @example
	 * BX.UI.Dialogs.MessageBox.confirm('Message');
	 * BX.UI.Dialogs.MessageBox.confirm('Message', () => {});
	 * BX.UI.Dialogs.MessageBox.confirm('Message', () => {}, 'Proceed');
	 * BX.UI.Dialogs.MessageBox.confirm('Message', () => {}, 'Proceed', () => {});
	 * BX.UI.Dialogs.MessageBox.confirm('Message', () => {}, 'Proceed', () => {}, 'Cancel');
	 * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title');
	 * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {});
	 * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {}, 'Proceed', () => {});
	 * BX.UI.Dialogs.MessageBox.confirm('Message', 'Title', () => {}, 'Proceed', () => {}, 'Cancel');
	 */
	static confirm(message: string, ...args): MessageBox
	{
		let title = null;
		let okCallback = null;
		let okCaption = null;
		let cancelCallback = null;
		let cancelCaption = null;

		if (args.length > 0)
		{
			if (Type.isString(args[0]))
			{
				[title, okCallback, okCaption, cancelCallback, cancelCaption] = args;
			}
			else
			{
				[okCallback, okCaption, cancelCallback, cancelCaption] = args;
			}
		}

		const messageBox = this.create({
			message,
			title,
			okCaption,
			cancelCaption,
			onOk: okCallback,
			onCancel: cancelCallback,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK_CANCEL,
		});
		messageBox.show();

		return messageBox;
	}

	static show(options = {})
	{
		const messageBox = this.create(options);
		messageBox.show();
	}

	static create(options = {})
	{
		return new this(options);
	}

	show()
	{
		if (this.getPopupWindow().isDestroyed())
		{
			this.popupWindow = null;
		}

		this.getPopupWindow().show();
	}

	close()
	{
		this.getPopupWindow().close();
	}

	/**
	 *
	 * @returns {PopupWindow}
	 */
	getPopupWindow()
	{
		if (this.popupWindow === null)
		{
			this.popupWindow = new Popup({
				bindElement: null,
				className: this.isMediumButtonSize() ? 'ui-message-box ui-message-box-medium-buttons' : 'ui-message-box',
				content: this.getMessage(),
				titleBar: this.getTitle(),
				minWidth: this.minWidth,
				minHeight: this.minHeight,
				maxWidth: this.maxWidth,
				overlay: this.modal ? { opacity: 20 } : null,
				cacheable: this.cacheable,
				closeIcon: false,
				contentBackground: 'transparent',
				padding: 0,
				buttons: this.getButtons(),
				...this.popupOptions
			});
		}

		return this.popupWindow;
	}

	setMessage(message: string | Element | Node)
	{
		if (Type.isString(message) || Type.isDomNode(message))
		{
			this.message = message;

			if (this.popupWindow !== null)
			{
				this.popupWindow.setContent(message);
			}
		}
	}

	/**
	 *
	 * @returns {?string|Element|Node}
	 */
	getMessage(): string | Element | Node
	{
		return this.message;
	}

	setTitle(title: string)
	{
		if (Type.isString(title))
		{
			this.title = title;

			if (this.popupWindow !== null)
			{
				this.popupWindow.setTitleBar(title);
			}
		}
	}

	/**
	 *
	 * @returns {?string}
	 */
	getTitle()
	{
		return this.title;
	}

	/**
	 *
	 * @param {string|BX.UI.Button[]} buttons
	 */
	setButtons(buttons)
	{
		if (Type.isArray(buttons))
		{
			this.buttons = buttons;
		}
		else if (Type.isString(buttons))
		{
			this.buttons = this.getButtonsLayout(buttons);
		}

		if (this.popupWindow !== null)
		{
			this.popupWindow.setButtons(this.buttons);
		}
	}

	/**
	 *
	 * @returns {BX.UI.Button[]}
	 */
	getButtons()
	{
		return this.buttons;
	}

	setOkCaption(caption: string)
	{
		if (Type.isString(caption))
		{
			this.getOkButton().setText(caption);
		}
	}

	setCancelCaption(caption: string)
	{
		if (Type.isString(caption))
		{
			this.getCancelButton().setText(caption);
		}
	}

	setYesCaption(caption: string)
	{
		if (Type.isString(caption))
		{
			this.getYesButton().setText(caption);
		}
	}

	setNoCaption(caption: string)
	{
		if (Type.isString(caption))
		{
			this.getNoButton().setText(caption);
		}
	}

	setOkCallback(fn: Function)
	{
		if (Type.isFunction(fn))
		{
			this.okCallback = fn;
		}
	}

	setCancelCallback(fn: Function)
	{
		if (Type.isFunction(fn))
		{
			this.cancelCallback = fn;
		}
	}

	setYesCallback(fn: Function)
	{
		if (Type.isFunction(fn))
		{
			this.yesCallback = fn;
		}
	}

	setNoCallback(fn: Function)
	{
		if (Type.isFunction(fn))
		{
			this.noCallback = fn;
		}
	}

	/**
	 *
	 * @returns {boolean}
	 */
	isMediumButtonSize()
	{
		return this.mediumButtonSize;
	}

	/**
	 *
	 * @returns {BX.UI.Button}
	 */
	getOkButton()
	{
		return this.cache.remember('okBtn', () => {
			return new BX.UI.Button({
				id: MessageBoxButtons.OK,
				size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
				color: BX.UI.Button.Color.PRIMARY,
				text: Loc.getMessage('UI_MESSAGE_BOX_OK_CAPTION'),
				events: {
					click: this.handleButtonClick
				}
			});
		});
	}

	/**
	 *
	 * @returns {BX.UI.Button}
	 */
	getCancelButton()
	{
		return this.cache.remember('cancelBtn', () => {
			return new BX.UI.CancelButton({
				id: MessageBoxButtons.CANCEL,
				size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
				text: Loc.getMessage('UI_MESSAGE_BOX_CANCEL_CAPTION'),
				events: {
					click: this.handleButtonClick
				}
			});
		});
	}

	/**
	 *
	 * @returns {BX.UI.Button}
	 */
	getYesButton()
	{
		return this.cache.remember('yesBtn', () => {
			return new BX.UI.Button({
				id: MessageBoxButtons.YES,
				size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
				color: BX.UI.Button.Color.PRIMARY,
				text: Loc.getMessage('UI_MESSAGE_BOX_YES_CAPTION'),
				events: {
					click: this.handleButtonClick
				}
			});
		});
	}

	/**
	 *
	 * @returns {BX.UI.Button}
	 */
	getNoButton()
	{
		return this.cache.remember('noBtn', () => {
			return new BX.UI.Button({
				id: MessageBoxButtons.NO,
				size: this.isMediumButtonSize() ? BX.UI.Button.Size.MEDIUM : BX.UI.Button.Size.SMALL,
				color: BX.UI.Button.Color.LIGHT_BORDER,
				text: Loc.getMessage('UI_MESSAGE_BOX_NO_CAPTION'),
				events: {
					click: this.handleButtonClick
				}
			});
		});
	}

	/**
	 *
	 * @param buttons
	 * @returns {BX.UI.Button[]}
	 */
	getButtonsLayout(buttons)
	{
		switch (buttons)
		{
			case MessageBoxButtons.OK:
				return [
					this.getOkButton()
				];
			case MessageBoxButtons.CANCEL:
				return [
					this.getCancelButton()
				];
			case MessageBoxButtons.YES:
				return [
					this.getYesButton()
				];
			case MessageBoxButtons.NO:
				return [
					this.getNoButton()
				];
			case MessageBoxButtons.OK_CANCEL:
				return [
					this.getOkButton(),
					this.getCancelButton()
				];
			case MessageBoxButtons.YES_NO:
				return [
					this.getYesButton(),
					this.getNoButton()
				];
			case MessageBoxButtons.YES_CANCEL:
				return [
					this.getYesButton(),
					this.getCancelButton()
				];
			case MessageBoxButtons.YES_NO_CANCEL:
				return [
					this.getYesButton(),
					this.getNoButton(),
					this.getCancelButton()
				];
			default:
				return [];
		}
	}

	/**
	 *
	 * @param {BX.UI.Button} button
	 * @param event
	 */
	handleButtonClick(button, event)
	{
		if (button.isDisabled())
		{
			return;
		}

		button.setDisabled(); // prevent a double click

		const fn = this[`${button.getId()}Callback`];
		if (!fn)
		{
			button.setDisabled(false);
			this.close();
			return;
		}

		const result = fn(this, button, event);

		if (result === true)
		{
			button.setDisabled(false);
			this.close();
		}
		else if (result === false)
		{
			button.setDisabled(false);
		}
		else if (
			result &&
			(
				Object.prototype.toString.call(result) === '[object Promise]' ||
				result.toString() === '[object BX.Promise]'
			)
		)
		{
			button.setWaiting();
			result.then(result => {
					button.setWaiting(false);
					this.close();
				},
				reason => {
					button.setWaiting(false);
				}
			);
		}
	}
}
