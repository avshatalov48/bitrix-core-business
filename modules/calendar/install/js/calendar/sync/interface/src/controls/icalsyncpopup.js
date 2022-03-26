// @flow
'use strict';


import {Popup} from 'main.popup';
import {Util} from 'calendar.util';
import {Loc, Tag} from "main.core";
import "../css/icalpopup.css"

export default class IcalSyncPopup
{
	LINK_LENGTH = 112;

	constructor(options)
	{
		this.link = this.getIcalLink(options);
	}

	static createInstance(options)
	{
		return new this(options);
	}

	show()
	{
		this.createPopup().show();
		this.startSync();
	}

	startSync()
	{
		BX.ajax.get(this.link + '&check=Y', "", (result) =>
		{
			setTimeout(() =>
			{
				if (!result || result.length <= 0 || result.toUpperCase().indexOf('BEGIN:VCALENDAR') === -1)
				{
					this.showPopupWithSyncDataError();
				}
			}, 300);
		});
	}

	getContent()
	{
		return Tag.render`
			<div class="calendar-ical-popup-wrapper">
				<h3>${Loc.getMessage('EC_JS_EXPORT_TILE')}</h3>
				<div class="calendar-ical-popup-label-text"><span>${Loc.getMessage('EC_EXP_TEXT')}</span></div>
				${this.getLinkBlock()}
			</div>
		`;
	}

	createPopup()
	{
		return this.popup = new Popup({
			width: 400,
			zIndexOptions: 4000,
			autoHide: false,
			closeByEsc: true,
			draggable: true,
			closeIcon: {right: "12px", top: "10px"},
			className: "bxc-popup-window",
			content: this.getContent(),
			buttons: [
				new BX.UI.Button({
					text : Loc.getMessage('EC_JS_ICAL_COPY_ICAL_SYNC_LINK'),
					color: BX.UI.Button.Color.PRIMARY,
					onclick: () => {
						this.copyLink(event);
					},
				}),
				new BX.UI.Button({
					text : Loc.getMessage('EC_SEC_SLIDER_CLOSE'),
					color: BX.UI.Button.Color.LINK,
					onclick: () => {
						this.popup.close();
					},
				}),
			],
		});
	}

	getIcalLink(options)
	{
		return options.calendarPath
			+ ((options.calendarPath.indexOf('?') >= 0) ? '&' : '?')
			+ 'action=export'
			+ options.sectionLink;
	}

	getLinkBlock()
	{
		return Tag.render`
				<div class="calendar-ical-popup-link-block">
					<a class="ui-link ui-link-primary " target="_blank" href="${BX.util.htmlspecialchars(this.link)}">
						${BX.util.htmlspecialchars(this.getShortenLink(this.link))}
					</a>
				</div>
			`;
	}

	static checkPathes(options)
	{
		return (!!options.sectionLink || !!options.calendarPath)
	}

	static showPopupWithPathesError()
	{
		BX.UI.Dialogs.MessageBox.alert(Loc.getMessage('EC_JS_ICAL_ERROR_WITH_PATHES'));
	}

	showPopupWithSyncDataError()
	{
		BX.UI.Dialogs.MessageBox.alert(Loc.getMessage('EC_EDEV_EXP_WARN'));
	}

	copyLink(event)
	{
		window.BX.clipboard.copy(this.link)
			? this.#showSuccessCopyNotification()
			: this.#showFailedCopyNotification();

		event.preventDefault();
		event.stopPropagation();
	}

	getShortenLink(link)
	{
		return link.length < this.LINK_LENGTH ? link : link.substr(0, 105) + '...' + link.slice(-7);
	}

	#showSuccessCopyNotification()
	{
		this.#showResultNotification(Loc.getMessage('EC_JS_ICAL_COPY_ICAL_SYNC_LINK_SUCCESS'));
	}

	#showFailedCopyNotification()
	{
		this.#showResultNotification(Loc.getMessage('EC_JS_ICAL_COPY_ICAL_SYNC_LINK_FAILED'));
	}

	#showResultNotification(message)
	{
		Util.showNotification(message);
	}
}
