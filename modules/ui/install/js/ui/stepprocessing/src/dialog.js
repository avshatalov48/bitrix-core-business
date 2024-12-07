// @flow

import 'ui.design-tokens';
import 'ui.progressbar';
import {Type, Tag, Loc, Dom, Event} from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import { Popup, PopupManager, PopupOptions } from 'main.popup';
import { Alert, AlertColor, AlertIcon, AlertSize } from 'ui.alerts';
import {Button, CancelButton} from 'ui.buttons';
import type { OptionsField } from './process-types';
import { BaseField } from './fields/base-field';
import { TextField } from './fields/text-field';
import { FileField } from './fields/file-field';
import { CheckboxField } from './fields/checkbox-field';
import { SelectField } from './fields/select-field';
import { RadioField } from './fields/radio-field';

/**
 * @namespace {BX.UI.StepProcessing}
 */
export type DialogOptions = {
	id: string,
	messages?: {
		title?: string,
		summary?: string,
		startButton?: string,
		stopButton?: string,
		closeButton?: string
	},
	optionsFields?: OptionsField[],
	optionsFieldsValue?: Object,
	showButtons?: {
		start?: boolean,
		stop?: boolean,
		close?: boolean
	},
	handlers?: {
		start?: any => void,
		stop?: any => void,
		dialogShown?: any => void,
		dialogClosed?: any => void
	},
	minWidth?: number,
	maxWidth?: number,
	popupOptions?: PopupOptions,
};

export const DialogStyle = {
	ProcessWindow: 'bx-stepprocessing-dialog-process',
	ProcessPopup: 'bx-stepprocessing-dialog-process-popup',
	ProcessSummary: 'bx-stepprocessing-dialog-process-summary',
	ProcessProgressbar: 'bx-stepprocessing-dialog-process-progressbar',
	ProcessOptions: 'bx-stepprocessing-dialog-process-options',
	ProcessOptionContainer: 'bx-stepprocessing-dialog-process-option-container',
	ProcessOptionsTitle: 'bx-stepprocessing-dialog-process-options-title',
	ProcessOptionsInput: 'bx-stepprocessing-dialog-process-options-input',
	ProcessOptionsObligatory: 'ui-alert ui-alert-xs ui-alert-warning',
	ProcessOptionText: 'bx-stepprocessing-dialog-process-option-text',
	ProcessOptionCheckbox: 'bx-stepprocessing-dialog-process-option-checkbox',
	ProcessOptionMultiple: 'bx-stepprocessing-dialog-process-option-multiple',
	ProcessOptionFile: 'bx-stepprocessing-dialog-process-option-file',
	ProcessOptionSelect: 'bx-stepprocessing-dialog-process-option-select',
	ButtonStart: 'popup-window-button-accept',
	ButtonStop: 'popup-window-button-disable',
	ButtonCancel: 'popup-window-button-link-cancel',
	ButtonDownload: 'popup-window-button-link-download',
	ButtonRemove: 'popup-window-button-link-remove'
};

export const DialogEvent = {
	Shown: 'BX.UI.StepProcessing.Dialog.Shown',
	Closed: 'BX.UI.StepProcessing.Dialog.Closed',
	Start: 'BX.UI.StepProcessing.Dialog.Start',
	Stop: 'BX.UI.StepProcessing.Dialog.Stop',
};

/**
 * UI of process dialog
 *
 * @namespace {BX.UI.StepProcessing}
 * @event BX.UI.StepProcessing.Dialog.Shown
 * @event BX.UI.StepProcessing.Dialog.Closed
 * @event BX.UI.StepProcessing.Dialog.Start
 * @event BX.UI.StepProcessing.Dialog.Stop
 */
export class Dialog
{
	id: string = '';

	/**
	 * @type {DialogOptions}
	 * @private
	 */
	_settings: DialogOptions = {};

	//popup
	popupWindow: Popup;
	isShown: boolean = false;

	//UI
	error: Alert;
	warning: Alert;
	progressBar: BX.UI.ProgressBar;
	buttons: {[type: 'start'|'stop'|'close']: Button} = {};
	fields: {[name: string]: BaseField} = {};

	//DOM
	optionsFieldsBlock: HTMLElement;
	summaryBlock: HTMLElement;
	errorBlock: HTMLElement;
	warningBlock: HTMLElement;
	progressBarBlock: HTMLElement;

	/**
	 * @private
	 */
	_messages = {};

	/**
	 * @private
	 */
	_handlers = {};

	/**
	 * @private
	 */
	isAdminPanel = false;

	constructor(settings: DialogOptions = {})
	{
		this._settings = settings;

		this.id = this.getSetting('id', 'ProcessDialog_' + Math.random().toString().substring(2));

		this._messages = this.getSetting('messages', {});

		let optionsFields = {};
		const fields = this.getSetting('optionsFields');
		if (Type.isArray(fields))
		{
			fields.forEach(option => {
				if (
					Type.isPlainObject(option) &&
					option.hasOwnProperty('name') &&
					option.hasOwnProperty('type')
				)
				{
					optionsFields[option.name] = option;
				}
			});
		}
		else if (Type.isPlainObject(fields))
		{
			Object.keys(fields).forEach(optionName => {
				let option = fields[optionName];
				if (
					Type.isPlainObject(option) &&
					option.hasOwnProperty('name') &&
					option.hasOwnProperty('type')
				)
				{
					optionsFields[option.name] = option;
				}
			});
		}
		this.setSetting('optionsFields', optionsFields);

		const optionsFieldsValue = this.getSetting('optionsFieldsValue');
		if (!optionsFieldsValue)
		{
			this.setSetting('optionsFieldsValue',{});
		}

		const showButtons = this.getSetting('showButtons');
		if (!showButtons)
		{
			this.setSetting('showButtons', {'start':true, 'stop':true, 'close':true});
		}

		this._handlers = this.getSetting('handlers', {});
	}

	destroy()
	{
		if (this.popupWindow)
		{
			this.popupWindow.destroy();
			this.popupWindow = null;
		}
	}

	getId()
	{
		return this.id;
	}

	getSetting(name: $Keys<DialogOptions>, defaultVal: ?any = null)
	{
		return this._settings.hasOwnProperty(name) ? this._settings[name] : defaultVal;
	}
	setSetting(name: $Keys<DialogOptions>, value: any)
	{
		this._settings[name] = value;
		return this;
	}

	getMessage(name: string): string
	{
		return this._messages && this._messages.hasOwnProperty(name) ? this._messages[name] : "";
	}
	setMessage(name: string, text: string)
	{
		this._messages[name] = text;
		return this;
	}

	//region Event handlers

	setHandler(type: string, handler: any => void)
	{
		if (typeof(handler) == 'function')
		{
			this._handlers[type] = handler;
		}
		return this;
	}
	callHandler(type: string, args: {[string]: any})
	{
		if (typeof(this._handlers[type]) == 'function')
		{
			this._handlers[type].apply(this, args);
		}
	}

	//endregion

	//region Run

	start()
	{
		this.callHandler('start');
		EventEmitter.emit(DialogEvent.Start, new BaseEvent({dialog: this}));
	}

	stop()
	{
		this.callHandler('stop');
		EventEmitter.emit(DialogEvent.Stop, new BaseEvent({dialog: this}));
	}

	show()
	{
		if (this.isShown)
		{
			return;
		}

		const optionElement = document.querySelector('#bx-admin-prefix');
		if (optionElement)
		{
			this.isAdminPanel = true;
		}

		this.progressBar = new BX.UI.ProgressBar({
			statusType: BX.UI.ProgressBar.Status.COUNTER,
			size: this.isAdminPanel ? BX.UI.ProgressBar.Size.LARGE : BX.UI.ProgressBar.Size.MEDIUM,
			fill: this.isAdminPanel,
			column: !this.isAdminPanel
		});

		this.error = new Alert({
			color: AlertColor.DANGER,
			icon: AlertIcon.DANGER,
			size: AlertSize.SMALL
		});

		this.warning = new Alert({
			color: AlertColor.WARNING,
			icon: AlertIcon.WARNING,
			size: AlertSize.SMALL
		});

		this.popupWindow = PopupManager.create({
			id: this.getId(),
			cacheable: false,
			titleBar: this.getMessage("title"),
			autoHide: false,
			closeByEsc: false,
			closeIcon: true,
			content: this._prepareDialogContent(),
			draggable: true,
			buttons: this._prepareDialogButtons(),
			className: DialogStyle.ProcessWindow,
			bindOptions: {forceBindPosition: false},
			events: {
				onClose: BX.delegate(this.onDialogClose, this)
			},
			overlay: true,
			resizable: true,
			minWidth: Number.parseInt(this.getSetting('minWidth', 500)),
			maxWidth: Number.parseInt(this.getSetting('maxWidth', 1000)),
			...this._settings.popupOptions,
		});

		if (!this.popupWindow.isShown())
		{
			this.popupWindow.show();
		}

		this.isShown = this.popupWindow.isShown();

		if (this.isShown)
		{
			this.callHandler('dialogShown');
			EventEmitter.emit(DialogEvent.Shown, new BaseEvent({dialog: this}));
		}
		return this;
	}

	close()
	{
		if (!this.isShown)
		{
			return;
		}
		if (this.popupWindow)
		{
			this.popupWindow.close();
		}
		this.isShown = false;

		this.callHandler('dialogClosed');
		EventEmitter.emit(DialogEvent.Closed, new BaseEvent({dialog: this}));

		return this;
	}

	// endregion

	//region Dialog

	/**
	 * @private
	 */
	_prepareDialogContent()
	{
		this.summaryBlock = Tag.render`<div class="${DialogStyle.ProcessSummary}">${this.getMessage('summary')}</div>`;

		this.errorBlock = this.error.getContainer();
		this.warningBlock = this.warning.getContainer();
		this.errorBlock.style.display = 'none';
		this.warningBlock.style.display = 'none';

		if (this.progressBar)
		{
			this.progressBarBlock =  Tag.render`<div class="${DialogStyle.ProcessProgressbar}" style="display:none"></div>`;
			this.progressBarBlock.appendChild(this.progressBar.getContainer());
		}

		if (!this.optionsFieldsBlock)
		{
			this.optionsFieldsBlock = Tag.render`<div class="${DialogStyle.ProcessOptions}" style="display:none"></div>`;
		}
		else
		{
			Dom.clean(this.optionsFieldsBlock);
		}

		let optionsFields = this.getSetting('optionsFields', {});
		let optionsFieldsValue = this.getSetting('optionsFieldsValue', {});

		Object.keys(optionsFields).forEach(optionName => {
			let optionValue = optionsFieldsValue[optionName] ? optionsFieldsValue[optionName] : null;
			let optionBlock = this._renderOption(optionsFields[optionName], optionValue);
			if (optionBlock instanceof HTMLElement)
			{
				this.optionsFieldsBlock.appendChild(optionBlock);
				this.optionsFieldsBlock.style.display = 'block';
			}
		});

		let dialogContent = Tag.render`<div class="${DialogStyle.ProcessPopup}"></div>`;
		dialogContent.appendChild(this.summaryBlock);
		dialogContent.appendChild(this.warningBlock);
		dialogContent.appendChild(this.errorBlock);

		if (this.progressBarBlock)
		{
			dialogContent.appendChild(this.progressBarBlock);
		}

		if (this.optionsFieldsBlock)
		{
			dialogContent.appendChild(this.optionsFieldsBlock);
		}

		return dialogContent;
	}

	/**
	 * @private
	 */
	_renderOption(option: OptionsField, optionValue: any = null)
	{
		option.id = this.id + '_opt_' + option.name;

		switch (option.type)
		{
			case 'text':
				this.fields[option.name] = new TextField(option);
				break;

			case 'file':
				this.fields[option.name] = new FileField(option);
				break;

			case 'checkbox':
				this.fields[option.name] = new CheckboxField(option);
				break;

			case 'select':
				this.fields[option.name] = new SelectField(option);
				break;

			case 'radio':
				this.fields[option.name] = new RadioField(option);
				break;
		}

		if (optionValue !== null)
		{
			this.fields[option.name].setValue(optionValue);
		}
		const optionBlock = this.fields[option.name].getContainer();

		return optionBlock;
	}

	//endregion

	//region Events

	onDialogClose()
	{
		if (this.popupWindow)
		{
			this.popupWindow.destroy();
			this.popupWindow = null;
		}

		this.buttons = {};
		this.fields = {};
		this.summaryBlock = null;

		this.isShown = false;

		this.callHandler('dialogClosed');
		EventEmitter.emit(DialogEvent.Closed, new BaseEvent({dialog: this}));
	}

	handleStartButtonClick()
	{
		const btn = this.getButton('start');
		if (btn && btn.isDisabled())
		{
			return;
		}

		this.start();
	}

	handleStopButtonClick()
	{
		const btn = this.getButton('stop');
		if (btn && btn.isDisabled())
		{
			return;
		}

		this.stop();
	}

	handleCloseButtonClick()
	{
		this.popupWindow.close();
	}

	//endregion

	//region Buttons

	/**
	 * @private
	 */
	_prepareDialogButtons(): Button[]
	{
		const showButtons = this.getSetting('showButtons');
		let ret = [];
		this.buttons = {};

		if (showButtons.start)
		{
			const startButtonText = this.getMessage('startButton');
			this.buttons.start = new Button({
				text: startButtonText || 'Start',
				color: Button.Color.SUCCESS,
				icon: Button.Icon.START,
				//className: DialogStyle.ButtonStart,
				events:
					{
						click: BX.delegate(this.handleStartButtonClick, this)
					}
			});
			ret.push(this.buttons.start);
		}

		if (showButtons.stop)
		{
			const stopButtonText = this.getMessage('stopButton');
			this.buttons.stop = new Button({
				text: stopButtonText || 'Stop',
				color: Button.Color.LIGHT_BORDER,
				icon: Button.Icon.STOP,
				//className: DialogStyle.ButtonStop,
				events:
					{
						click: BX.delegate(this.handleStopButtonClick, this)
					}
			});
			this.buttons.stop.setDisabled();
			ret.push(this.buttons.stop);
		}

		if (showButtons.close)
		{
			const closeButtonText = this.getMessage('closeButton');
			this.buttons.close = new CancelButton({
				text: closeButtonText || 'Close',
				color: Button.Color.LIGHT_BORDER,
				tag: Button.Tag.SPAN,
				events:
					{
						click: BX.delegate(this.handleCloseButtonClick, this)
					}
			});
			ret.push(this.buttons.close);
		}

		return ret;
	}

	/**
	 * @param {String} downloadLink
	 * @param {String} fileName
	 * @param {function} purgeHandler
	 * @return self
	 */
	setDownloadButtons(downloadLink: string, fileName: string, purgeHandler: any => {})
	{
		let ret = [];

		if (downloadLink)
		{
			let downloadButtonText = this.getMessage("downloadButton");
			downloadButtonText = downloadButtonText !== "" ? downloadButtonText : "Download file";
			const downloadButton = new Button({
				text: downloadButtonText,
				color: Button.Color.SUCCESS,
				icon: Button.Icon.DOWNLOAD,
				className: DialogStyle.ButtonDownload,
				tag: Button.Tag.LINK,
				link: downloadLink,
				props: {
					//href: downloadLink,
					download: fileName
				}
			});
			ret.push(downloadButton);
		}

		if (typeof(purgeHandler) == 'function')
		{
			let clearButtonText = this.getMessage("clearButton");
			clearButtonText = clearButtonText !== "" ? clearButtonText : "Delete file";
			const clearButton = new Button({
				text: clearButtonText,
				color: Button.Color.LIGHT_BORDER,
				icon: Button.Icon.REMOVE,
				className: DialogStyle.ButtonRemove,
				events:
					{
						click: purgeHandler
					}
			});
			ret.push(clearButton);
		}

		if (this.buttons.close)
		{
			ret.push(this.buttons.close);
		}

		if (ret.length > 0 && this.popupWindow)
		{
			this.popupWindow.setButtons(ret);
		}
		return this;
	}

	resetButtons(showButtons = {'start':true, 'stop':true, 'close':true})
	{
		this._prepareDialogButtons();

		showButtons = showButtons || this.getSetting('showButtons');

		let ret = [];

		if (showButtons.start)
		{
			ret.push(this.buttons.start);
		}
		if (showButtons.stop)
		{
			ret.push(this.buttons.stop);
		}
		if (showButtons.close)
		{
			ret.push(this.buttons.close);
		}
		if (ret.length > 0 && this.popupWindow)
		{
			this.popupWindow.setButtons(ret);
		}
		return this;
	}

	getButton(bid: string): ?Button
	{
		return this.buttons[bid] ??  null;
	}

	lockButton(bid: string, lock: boolean, wait: boolean)
	{
		const btn = this.getButton(bid);
		if (btn)
		{
			btn.setDisabled(lock);
			if (Type.isBoolean(wait))
			{
				btn.setWaiting(wait);
			}
		}
		return this;
	}

	showButton(bid: string, show: boolean)
	{
		const btn = this.getButton(bid);
		if (btn)
		{
			btn.getContainer().style.display = !!show ? '' : 'none';
		}
		if (bid === 'close')
		{
			if (this.popupWindow && this.popupWindow.closeIcon)
			{
				this.popupWindow.closeIcon.style.display = !!show ? '' : 'none';
			}
		}
		return this;
	}

	// endregion

	//region Summary

	setSummary(content: string, isHtml: boolean = false)
	{
		if (this.optionsFieldsBlock)
		{
			BX.clean(this.optionsFieldsBlock);
			this.optionsFieldsBlock.style.display = 'none';
		}
		if (Type.isStringFilled(content))
		{
			if (this.summaryBlock)
			{
				if (!!isHtml)
					this.summaryBlock.innerHTML = content;
				else
					this.summaryBlock.innerHTML = BX.util.htmlspecialchars(content);

				this.summaryBlock.style.display = "block";
			}
		}
		else
		{
			this.summaryBlock.innerHTML = "";
			this.summaryBlock.style.display = "none";
		}
		return this;
	}

	//endregion

	//region Errors

	setErrors(errors: Array<string>, isHtml: bool = false)
	{
		errors.forEach(err => this.setError(err, isHtml));
		return this;
	}
	setError(content, isHtml)
	{
		if (Type.isStringFilled(content))
		{
			this.setSummary('');

			if (this.progressBar)
			{
				this.progressBar.setColor(BX.UI.ProgressBar.Color.DANGER);
			}

			if (!!isHtml)
			{
				this.error.setText(content);
			}
			else
			{
				this.error.setText(BX.util.htmlspecialchars(content));
			}

			this.errorBlock.style.display = "flex";
		}
		return this;
	}
	clearErrors()
	{
		if (this.error)
		{
			this.error.setText('');
		}
		if (this.errorBlock)
		{
			this.errorBlock.style.display = 'none';
		}
		return this;
	}
	setWarning(err: string, isHtml: boolean = false)
	{
		if (Type.isStringFilled(err))
		{
			if (!!isHtml)
			{
				this.warning.setText(err);
			}
			else
			{
				this.warning.setText(BX.util.htmlspecialchars(err));
			}
			this.warningBlock.style.display = "flex";
		}
		return this;
	}
	clearWarnings()
	{
		if (this.warning)
		{
			this.warning.setText("");
		}
		if (this.warningBlock)
		{
			this.warningBlock.style.display = 'none';
		}
		return this;
	}

	//endregion

	//region Progressbar

	setProgressBar(totalItems: number, processedItems: number, textBefore: string)
	{
		if (this.progressBar)
		{
			if (Type.isNumber(processedItems) && Type.isNumber(totalItems) && totalItems > 0)
			{
				BX.show(this.progressBarBlock);
				this.progressBar.setColor(BX.UI.ProgressBar.Color.PRIMARY);
				this.progressBar.setMaxValue(totalItems);
				textBefore = textBefore || "";
				this.progressBar.setTextBefore(textBefore);
				this.progressBar.update(processedItems);
			}
			else
			{
				this.hideProgressBar();
			}
		}
		return this;
	}
	hideProgressBar()
	{
		if (this.progressBar)
		{
			BX.hide(this.progressBarBlock);
		}
		return this;
	}

	//endregion

	//region Initial options

	getOptionField(name: string): ?BaseField
	{
		if (Type.isString(name))
		{
			if (this.fields[name] && this.fields[name] instanceof BaseField)
			{
				return this.fields[name];
			}
		}
		return null;
	}

	getOptionFieldValues()
	{
		let initialOptions = {};
		if (this.optionsFieldsBlock)
		{
			Object.keys(this.fields).forEach(optionName => {
				let field = this.getOptionField(optionName);
				let val = field.getValue();
				if (field.type === 'checkbox' && Type.isBoolean(val))
				{
					initialOptions[optionName] = val ? 'Y' : 'N';
				}
				else if (Type.isArray(val))
				{
					if (Type.isArrayFilled(val))
					{
						initialOptions[optionName] = val;
					}
				}
				else if (val)
				{
					initialOptions[optionName] = val;
				}
			});
		}
		return initialOptions;
	}

	checkOptionFields(): boolean
	{
		let checked = true;
		if (this.optionsFieldsBlock)
		{
			Object.keys(this.fields).forEach(optionName => {
				let field = this.getOptionField(optionName);
				if (field.obligatory)
				{
					if (!field.isFilled())
					{
						field.showWarning();
						checked = false;
					}
					else
					{
						field.hideWarning();
					}
				}
			});
		}
		return checked;
	}

	lockOptionFields(flag: boolean = true)
	{
		if (this.optionsFieldsBlock)
		{
			Object.keys(this.fields).forEach(optionName => {
				let field = this.getOptionField(optionName);
				if (field)
				{
					field.lock(flag);
				}
			});
		}
		return this;
	}
	//endregion
}
