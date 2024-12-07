import {Loc, Tag, Type, Event, Dom} from "main.core";
import {InterfaceTemplate} from "./interfacetemplate";
import MobileSyncBanner from "../controls/mobilesyncbanner";
import {Util} from "calendar.util";

export default class MobileInterfaceTemplate extends InterfaceTemplate
{
	constructor(options)
	{
		super(options);

		this.banner = new MobileSyncBanner({
			type: this.provider.getType(),
			helpDeskCode: options.helpDeskCode,
		});

		if (this.status)
		{
			this.syncDate = Type.isDate(this.data.syncDate) ? this.data.syncDate : Util.parseDate(this.data.syncDate);
		}
	}

	getContentInfoBody()
	{
		return Tag.render`
			${this.getContentInfoBodyHeader()}
			${this.getContentInfoWarning()}
		`;
	}

	getContentInfoBodyHeaderHelper()
	{
		if (!this.headerHelper)
		{
			this.headerHelper = Tag.render`
				<div class="calendar-sync-slider-info">
					${this.getContentInfoBodyHeaderHelperConnect()}
				</div>
			`;
		}

		return this.headerHelper;
	}

	getContentInfoBodyHeaderHelperConnect()
	{
		if (!this.headerHelperConnect)
		{
			this.headerHelperConnect = Tag.render`
				<div class="calendar-sync-slider-info-text">
					<a class="calendar-sync-slider-info-link">
						${Loc.getMessage('CAL_CONNECT_PHONE')}
					</a>
				</div>
			`;

			Event.bind(this.headerHelperConnect, 'click', this.showMobileSyncBanner.bind(this));
		}

		return this.headerHelperConnect;
	}

	showMobileSyncBanner()
	{
		this.banner.show();
	}

	getContentActiveBody()
	{
		return Tag.render`
			${this.getContentActiveBodyHeader()}			
			${this.getContentInfoWarning()}
		`;
	}

	getContentActiveBodyHeader()
	{
		const timestamp = this.connection.getSyncDate().getTime() / 1000;
		const syncTime = timestamp
			? Util.formatDateUsable(timestamp) + ' ' + BX.date.format(Util.getTimeFormatShort(), timestamp)
			: '';

		return Tag.render `
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${this.sliderIconClass}"></div>
				<div class="calendar-sync-slider-header">
					<div class="calendar-sync-slider-title">${this.titleActiveHeader}</div>
					<div class="calendar-sync-slider-info">
						<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_SYNC_LAST_SYNC_DATE')}</span>
						<span class="calendar-sync-slider-info-time">${syncTime}</span>
					</div>
					<div class="calendar-sync-slider-desc">${Loc.getMessage('CAL_SYNC_DISABLE')}</div>
					${this.getContentInfoBodyHeaderHelper()}
				</div>
			</div>`;
	}

	getActiveConnectionContent()
	{
		return Tag.render`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header" style="justify-content: start;">
					<span class="calendar-sync-header-text">${this.getHeaderTitle()}</span>
					${this.getHeaderHint()}
				</div>
				${this.getContentActiveBody()}
			</div>
		`;
	}

	getInfoConnectionContent()
	{
		return Tag.render`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				<div class="calendar-sync-header" style="justify-content: start;">
					<span class="calendar-sync-header-text">${this.getHeaderTitle()}</span>
					${this.getHeaderHint()}
				</div>
				${this.getContentInfoBody()}
			</div>
		`;
	}

	getHeaderHint()
	{
		this.hintNode ??= BX.UI.Hint.createNode(Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC'));
		Event.bind(this.hintNode, 'click', this.showHelp.bind(this));

		return this.hintNode;
	}
}
