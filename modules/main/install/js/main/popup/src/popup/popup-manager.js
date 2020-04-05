import Popup from './popup';
import { type PopupOptions } from './popup-types';
import { Type } from 'main.core';
import { EventEmitter, type BaseEvent } from 'main.core.events';

export default class PopupManager
{
	static _popups: Popup[] = [];
	static _currentPopup: Popup = null;

	constructor()
	{
		throw new Error('You cannot make an instance of PopupManager.');
	}

	static create(options: PopupOptions): Popup
	{
		let [popupId, bindElement, params] = arguments; //compatible arguments

		let id = popupId;
		let compatMode = true;
		if (Type.isPlainObject(popupId) && !bindElement && !params)
		{
			compatMode = false;
			id = popupId.id;
			if (!Type.isStringFilled(id))
			{
				throw new Error('BX.Main.Popup.Manager: "id" parameter is required.');
			}
		}

		let popupWindow = this.getPopupById(id);
		if (popupWindow === null)
		{
			popupWindow = compatMode ? new Popup(popupId, bindElement, params) : new Popup(options);
			popupWindow.subscribe('onShow', this.handlePopupShow);
			popupWindow.subscribe('onClose', this.handlePopupClose);
		}

		return popupWindow;
	}

	/**
	 * @private
	 */
	static handleOnAfterInit(event: BaseEvent)
	{
		event.getTarget().subscribeOnce('onDestroy', this.handlePopupDestroy);

		this._popups.forEach(popup => {
			if (popup.getId() === event.getTarget().getId())
			{
				console.error(`Duplicate id (${popup.getId()}) for the BX.Main.Popup instance.`);
			}
		});

		this._popups.push(event.getTarget());
	}

	/**
	 * @private
	 */
	static handlePopupDestroy(event: BaseEvent)
	{
		this._popups = this._popups.filter(popup => {
			return popup !== event.getTarget();
		});
	}

	/**
	 * @private
	 */
	static handlePopupShow(event: BaseEvent)
	{
		if (this._currentPopup !== null)
		{
			this._currentPopup.close();
		}

		this._currentPopup = event.getTarget();
	}

	/**
	 * @private
	 */
	static handlePopupClose()
	{
		this._currentPopup = null;
	}

	static getCurrentPopup(): Popup | null
	{
		return this._currentPopup;
	}

	static isPopupExists(id): boolean
	{
		return this.getPopupById(id) !== null;
	}

	static isAnyPopupShown(): boolean
	{
		for (let i = 0, length = this._popups.length; i < length; i++)
		{
			if (this._popups[i].isShown())
			{
				return true;
			}
		}

		return false;
	}

	static getPopupById(id): Popup | null
	{
		for (let i = 0; i < this._popups.length; i++)
		{
			if (this._popups[i].getId() === id)
			{
				return this._popups[i];
			}
		}

		return null;
	}

	static getMaxZIndex(): number
	{
		let zIndex = 0;
		for (let i = 0; i < this._popups.length; i++)
		{
			zIndex = Math.max(zIndex, this._popups[i].params.zIndex);
		}

		return zIndex;
	}
}

PopupManager.handlePopupDestroy = PopupManager.handlePopupDestroy.bind(PopupManager);
PopupManager.handlePopupShow = PopupManager.handlePopupShow.bind(PopupManager);
PopupManager.handlePopupClose = PopupManager.handlePopupClose.bind(PopupManager);
PopupManager.handleOnAfterInit = PopupManager.handleOnAfterInit.bind(PopupManager);

EventEmitter.subscribe('BX.Main.Popup:onAfterInit', PopupManager.handleOnAfterInit);