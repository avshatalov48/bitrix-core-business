import SyncItemTemplate from "./syncitemtemplate";
import {Loc, Tag} from "main.core";

export default class MacItem extends SyncItemTemplate
{
	constructor(options)
	{
		super(options);

		this.helpdeskCode = '5684075';
		this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_MAC');
		this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_MAC_CALENDAR_TITLE');
		this.titleActiveHeader = Loc.getMessage('CAL_MAC_CALENDAR_IS_CONNECT_TITLE');
		this.descriptionInfoHeader = this.selected ? Loc.getMessage('CAL_MAC_SELECTED_DESCRIPTION') : Loc.getMessage('CAL_MAC_CONNECT_DESCRIPTION');
		this.sliderIconClass = 'calendar-sync-slider-header-icon-mac';
		this.image = '/bitrix/images/calendar/sync/mac.svg';
		this.color = '#ff5752';
		this.title = Loc.getMessage('CALENDAR_TITLE_MAC');
		this.portalAddress = BX.util.htmlspecialchars(options.data.portalAddress);
	}

	getPortalAddress()
	{
		return this.portalAddress;
	}

	getConnectSliderContent()
	{
		return Tag.render `
			${this.getSliderContentInfoBlock()}
			<div class="calendar-sync-slider-section calendar-sync-slider-section-col">
				<div class="calendar-sync-slider-header calendar-sync-slider-header-divide">
					<div class="calendar-sync-slider-subtitle">${Loc.getMessage('CAL_MAC_INSTRUCTION_HEADER')}</div>
				</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_DESCRIPTION')}:</span>
					<ol class="calendar-sync-slider-info-list">
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIRST')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SECOND')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_THIRD')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FOURTH')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_FIFTH').replace(/#PORTAL_ADDRESS#/gi, this.portalAddress)}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SIXTH')}</span>
						</li>
						<li class="calendar-sync-slider-info-item">
							<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_POINT_SEVENTH')}</span>
						</li>
					</ol>
					<span class="calendar-sync-slider-info-text">${Loc.getMessage('CAL_MAC_INSTRUCTION_CONCLUSION')}</span>
				</div>
			</div>
		`;
	}

	getSelectedSliderContent(options)
	{
		return this.getConnectSliderContent();
	}
}