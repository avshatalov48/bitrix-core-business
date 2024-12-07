import { Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Analytics } from 'calendar.sharing.analytics';
import type { RuleParams } from '../model/rule';
import type { Context, User } from '../model/index';
import { SettingsModel, CalendarSettings } from '../model/settings';
import { Layout } from './layout';

import 'main.qrcode';
import 'ui.design-tokens';

type DialogOptions = {
	bindElement: HTMLElement,
	userInfo: ?User,
	sharingUrl: string,
	linkHash: string,
	sharingRule: RuleParams,
	calendarSettings: CalendarSettings,
	context: Context,
	readOnly: boolean,
	settingsCollapsed: boolean,
	sortJointLinksByFrequentUse: boolean,
};

export default class DialogNew
{
	#popup: Popup;
	#layout: {
		wrapper: HTMLElement,
		bindElement: HTMLElement,
	};

	#dialogLayout: Layout;
	#settingsModel: SettingsModel;

	constructor(options: DialogOptions)
	{
		this.#layout = {};

		this.#layout.bindElement = options.bindElement;

		this.#settingsModel = new SettingsModel({
			context: options.context,
			linkHash: options.linkHash,
			sharingUrl: options.sharingUrl,
			userInfo: options.userInfo,
			rule: options.sharingRule,
			calendarSettings: options.calendarSettings,
			collapsed: options.settingsCollapsed,
			sortJointLinksByFrequentUse: options.sortJointLinksByFrequentUse,
		});

		this.bindEvents();
	}

	bindEvents(): void
	{
		EventEmitter.subscribe('CalendarSharing:LinkCopied', this.onSuccessfulCopyingLink.bind(this));
	}

	getPopup(): Popup
	{
		if (!this.#popup)
		{
			this.#popup = new Popup({
				bindElement: this.#layout.bindElement,
				className: 'calendar-sharing__dialog',
				closeByEsc: true,
				autoHide: true,
				padding: 0,
				width: 470,
				angle: {
					offset: this.#layout.bindElement.offsetWidth / 2 + 16,
				},
				autoHideHandler: (event) => this.canBeClosed(event),
				content: this.getPopupWrapper(),
				animation: 'fading-slide',
				events: {
					onPopupShow: this.onPopupShow.bind(this),
					onPopupClose: this.onPopupClose.bind(this),
				},
			});
		}

		return this.#popup;
	}

	onPopupShow(): void
	{
		Dom.addClass(this.#layout.bindElement, 'ui-btn-hover');

		Analytics.sendPopupOpened(this.#settingsModel.getContext());
	}

	onPopupClose(): void
	{
		Dom.removeClass(this.#layout.bindElement, 'ui-btn-hover');
		this.#dialogLayout.reset();
	}

	canBeClosed(event): boolean
	{
		const isClickInside = this.#layout.wrapper.contains(event.target);
		const layoutHasShownPopups = this.#dialogLayout.hasShownPopups();
		const checkTopSlider = (this.#settingsModel.getContext() === 'calendar') ? BX.SidePanel.Instance.getTopSlider() : false;

		return !isClickInside
			&& !layoutHasShownPopups
			&& !checkTopSlider
		;
	}

	getPopupWrapper(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#dialogLayout = new Layout({
				settingsModel: this.#settingsModel,
			});
			this.#layout.wrapper = this.#dialogLayout.render();
		}

		return this.#layout.wrapper = this.#dialogLayout.render();
	}

	onSuccessfulCopyingLink(): void
	{
		this.getPopup().close();
	}

	isShown(): boolean
	{
		return this.getPopup().isShown();
	}

	show(): void
	{
		this.#settingsModel.sortRanges();

		this.getPopup().show();
	}

	destroy(): void
	{
		this.getPopup().destroy();
	}

	getSettingsControlRule(): RuleParams
	{
		return this.#settingsModel?.getRule().toArray();
	}
}
