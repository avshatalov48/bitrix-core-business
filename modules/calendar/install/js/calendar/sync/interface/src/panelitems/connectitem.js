import SyncItemTemplate from "./syncitemtemplate";
import {Tag, Loc} from "main.core";

export default class ConnectItem extends SyncItemTemplate
{
	constructor(options)
	{
		super(options);

		this.layout = {
			container: null,
			header: null,
			content: null
		};

		this.helpdeskCode = '5697365';
		this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_CALDAV');
		this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_CALDAV_CALENDAR');
		this.descriptionInfoHeader = Loc.getMessage('CAL_CALDAV_CONNECT_INFO');

	}

	getInnerContent(): string
	{
		this.layout.innerContent = Tag.render`
			<div class="calendar-sync-item calendar-sync-item-add" onclick="${this.onClickItem.bind(this)}">
				<div class="calendar-sync-item-content">
					<span class="calendar-sync-item-add-inner">
						<span class="calendar-sync-item-add-icon"></span>
						<span class="calendar-sync-item-add-text">${Loc.getMessage('SYNC_SLIDER_MY_CALENDAR')}</span>
					</span>
				</div>
			</div>
		`;

		return this.layout.innerContent;
	}

	getSliderIconClass()
	{
		return 'calendar-sync-slider-header-icon-caldav';
	}
}