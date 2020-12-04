import SyncItemTemplate from "./syncitemtemplate";
import {Loc, Tag, Type} from "main.core";
import MobileSyncBanner from "../controls/mobilesyncbanner";
import {Util} from "calendar.util";

export default class MobileItem extends SyncItemTemplate
{
	constructor(options)
	{
		super(options);
		this.title = options.title;
		this.sliderTitle = this.title;
		this.sliderWidth = 605;
		if (this.status)
		{
			this.syncDate = Type.isDate(this.data.syncDate) ? this.data.syncDate : Util.parseDate(this.data.syncDate);
		}

		this.banner = new MobileSyncBanner({type: options.id});
		this.setItemAttribute();
	}

	getConnectSliderContent(options)
	{
		return Tag.render `
			${this.getMobileStatusContent()}
			<div class="calendar-sync-slider-section calendar-sync-slider-section-banner">
				${this.getMobileBannerContent()}
			</div>
		`;
	}

	getSelectedSliderContent(options)
	{
		return this.getConnectSliderContent(options);
	}

	getMobileBannerContent()
	{
		return this.banner.getContainer();
	}

	getMobileStatusContent()
	{
		if (!this.status || !Type.isDate(this.syncDate))
		{
			return this.getSliderContentInfoBlock();
		}

		return Tag.render `
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${this.sliderIconClass}"></div>
				<div class="calendar-sync-slider-header">
				<div class="calendar-sync-slider-title">${Loc.getMessage('CAL_SYNC_CONNECTED_' + (this.id === 'iphone' ? 'IPHONE' : 'ANDROID') + '_TITLE')}</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_SYNC_LAST_SYNC_DATE')}</span>
					<span class="calendar-sync-slider-info-time">${Util.formatDateUsable(this.syncDate) + ' ' + Util.formatTime(this.syncDate)}</span>
				</div>
				<div class="calendar-sync-slider-desc">${Loc.getMessage('CAL_SYNC_DISABLE')}</div>
					<a class="calendar-sync-slider-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}</a>
				</div>
			</div>`;
	}

	openConnectSlider()
	{
		super.openConnectSlider();
		this.banner.initQrCode().then(this.banner.drawQRCode.bind(this.banner));
	}

	getAdditionalHelpUrl()
	{
		return this.helpdeskUrl;
	}

	setItemAttribute()
	{
		if (this.id === 'iphone')
		{
			this.image = '/bitrix/images/calendar/sync/iphone.svg';
			this.color = '#2fc6f6';
			this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_IPHONE');
			this.title = Loc.getMessage('CALENDAR_TITLE_IPHONE');
			this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_IPHONE_CALENDAR_TITLE');
			this.descriptionInfoHeader = this.selected ? Loc.getMessage('CAL_IPHONE_SELECTED_DESCRIPTION') : Loc.getMessage('CAL_IPHONE_CONNECT_DESCRIPTION');
			this.sliderIconClass = 'calendar-sync-slider-header-icon-iphone';
			this.helpdeskCode = '5686207';
		}
		else
		{
			this.image = '/bitrix/images/calendar/sync/android.svg';
			this.color = '#9ece03';
			this.title = Loc.getMessage('CALENDAR_TITLE_ANDROID');
			this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_ANDROID');
			this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_ANDROID_CALENDAR_TITLE');
			this.descriptionInfoHeader = this.selected ? Loc.getMessage('CAL_ANDROID_SELECTED_DESCRIPTION') : Loc.getMessage('CAL_ANDROID_CONNECT_DESCRIPTION')
			this.sliderIconClass = 'calendar-sync-slider-header-icon-android';
			this.helpdeskCode = '5686179';
		}
	}
}