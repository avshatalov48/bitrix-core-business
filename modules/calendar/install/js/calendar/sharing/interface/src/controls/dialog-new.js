import { Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { Popup } from 'main.popup';
import { Analytics } from 'calendar.sharing.analytics';
import type { RuleParams } from '../model/rule';
import type { Context, User } from '../model/index';
import { SettingsModel, CalendarSettings } from '../model/settings';
import { Layout } from './layout';
import { CalendarContext } from '../model';

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
	calendarContext: CalendarContext | null,
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
			calendarContext: options.calendarContext,
		});

		this.bindEvents();
	}

	bindEvents(): void
	{
		EventEmitter.subscribe('CalendarSharing:LinkCopied', this.onSuccessfulCopyingLink.bind(this));
		EventEmitter.subscribe('SidePanel.Slider:onClose', (event): void => this.checkAndClosePopupOnSlider(event));
	}

	getPopup(): Popup
	{
		if (!this.#popup)
		{
			this.#popup = new Popup({
				bindElement: this.#layout.bindElement,
				targetContainer: document.body,
				className: 'calendar-sharing__dialog',
				closeByEsc: true,
				autoHide: true,
				padding: 0,
				width: 470,
				angle: this.#getAngleConfig(),
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
		const topSlider = this.getTopSlider();
		const calendarOpenInTopSlider = topSlider && this.getCalendarSliderParams(topSlider);

		return !isClickInside
			&& !layoutHasShownPopups
			&& (
				!topSlider
				|| calendarOpenInTopSlider
				|| this.#isExternalSharing()
			)
		;
	}

	getPopupWrapper(): HTMLElement
	{
		if (!this.#layout.wrapper)
		{
			this.#dialogLayout = new Layout({
				readOnly: this.#settingsModel.getCalendarContext()?.sharingObjectType === 'group',
				settingsModel: this.#settingsModel,
			});
			this.#layout.wrapper = this.#dialogLayout.render();
		}

		this.#layout.wrapper = this.#dialogLayout.render();

		return this.#layout.wrapper;
	}

	onSuccessfulCopyingLink(): void
	{
		this.closePopup();
	}

	closePopup(): void
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

		this.getPopup().adjustPosition({ forceBindPosition: true });
		this.getPopup().show();
	}

	destroy(): void
	{
		this.getPopup().destroy();
	}

	getTopSlider(): BX.SidePanel.Slider | null
	{
		return (this.#settingsModel.getContext() === 'calendar')
			? BX.SidePanel.Instance.getTopSlider()
			: false
		;
	}

	getCalendarSliderParams(slider: BX.SidePanel.Slider): Array | null
	{
		return slider.iframeSrc?.match(/\/workgroups\/group\/(\d+)\/calendar\//i);
	}

	checkAndClosePopupOnSlider(event): void
	{
		if (!this.isShown())
		{
			return;
		}

		const slider = event.getData() && event.getData()[0]?.slider;

		const sliderParams = slider && this.getCalendarSliderParams(slider);
		if (!sliderParams)
		{
			return;
		}

		const groupId = parseInt(sliderParams[1], 10);
		if (!groupId)
		{
			return;
		}

		const currentGroupId = this.#settingsModel.getCalendarContext()?.sharingObjectId;
		if (currentGroupId && groupId !== currentGroupId)
		{
			return;
		}

		this.closePopup();
	}

	#isExternalSharing(): boolean
	{
		return Boolean(this.#settingsModel.getCalendarContext()?.externalSharing);
	}

	#getAngleConfig()
	{
		if (this.#isExternalSharing())
		{
			return null;
		}

		return {
			offset: this.#layout.bindElement.offsetWidth / 2 + 16,
		};
	}
}
