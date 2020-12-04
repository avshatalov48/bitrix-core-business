import {Dom, Loc, Runtime, Tag} from "main.core";
import SyncItemTemplate from "./syncitemtemplate";
import ConnectionControls from "../controls/connectioncontrols";

export default class GoogleItem extends SyncItemTemplate
{
	constructor(options)
	{
		super(options);

		this.layout = {
			container: null,
			header: null,
			content: null
		};

		this.item = this.data.menu[0];

		this.sections = options.data.sections;
		this.sectionStatusObject = {};
		this.runUpdateInfo = Runtime.debounce(this.runUpdateInfo, 2000);

		this.helpdeskCode = '6030429';
		this.sliderTitle = Loc.getMessage('CALENDAR_TITLE_GOOGLE');
		this.titleInfoHeader = Loc.getMessage('CAL_CONNECT_GOOGLE_CALENDAR');
		this.descriptionInfoHeader = this.selected ? Loc.getMessage('CAL_GOOGLE_SELECTED_DESCRIPTION') : Loc.getMessage('CAL_GOOGLE_CONNECT_DESCRIPTION');
		this.sliderIconClass = 'calendar-sync-slider-header-icon-google';
		this.image = '/bitrix/images/calendar/sync/google.svg';
		this.color = '#387ced';
		this.title = Loc.getMessage('CALENDAR_TITLE_GOOGLE');
	}

	getSelectedSliderContent(options)
	{
		const formObject = new ConnectionControls();
		const disconnectButton = formObject.getDisconnectButton();
		disconnectButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.sendRequestRemoveConnection(options.id)
		});

		return Tag.render`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon calendar-sync-slider-header-icon-google"></div>
				<div class="calendar-sync-slider-header">
					<div class="calendar-sync-slider-title">${Loc.getMessage('CAL_GOOGLE_CALENDAR_IS_CONNECT')}</div>
					<span class="calendar-sync-slider-account">
						<span class="calendar-sync-slider-account-avatar"></span>
						<span class="calendar-sync-slider-account-email">
							${BX.util.htmlspecialchars(options.text)}
						</span>
					</span>
					<div class="calendar-sync-slider-info">
						<span class="calendar-sync-slider-info-text">
							<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">
								${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}
							</a>
						</span>
					</div>
					${disconnectButton}
				</div>
			</div>
			<div class="calendar-sync-slider-section calendar-sync-slider-section-col">
				<div class="calendar-sync-slider-header">
					<div class="calendar-sync-slider-subtitle">${Loc.getMessage('CAL_AVAILABLE_CALENDAR')}</div>
				</div>
				<ul class="calendar-sync-slider-list">
					${this.getItemSectionsContent(options.id)}
				</ul>
			</div>
		`;
	}

	getItemSectionsContent(connectionId)
	{
		let sectionList = [];
		const sections = this.sections;

		sections.forEach(section => {
			if (section['CAL_DAV_CON'] === connectionId)
			{
				sectionList.push(Tag.render`
					<li class="calendar-sync-slider-item">
						<label class="ui-ctl ui-ctl-checkbox ui-ctl-xs">
							<input type="checkbox" class="ui-ctl-element" value="${BX.util.htmlspecialchars(section['ID'])}" onclick="${this.onClickCheckSection.bind(this)}" ${section['ACTIVE'] === 'Y' ? 'checked' : ''}>
							<div class="ui-ctl-label-text">${BX.util.htmlspecialchars(section['NAME'])}</div>
						</label>
					</li>
				`);
			}
		});

		return sectionList;
	}

	createConnection()
	{
		BX.util.popup(this.data.authLink, 500, 600);
	}

	getConnectSliderContent()
	{
		const formObject = new ConnectionControls();
		const button = formObject.getAddButton();
		const buttonWrapper = formObject.getButtonWrapper();
		const bodyHeader = this.getContentInfoBodyHeader();
		const content = bodyHeader.querySelector('.calendar-sync-slider-header');

		button.addEventListener('click', () => {
			this.createConnection();
		});

		Dom.append(button, buttonWrapper);
		Dom.append(buttonWrapper, content);

		return Tag.render`
			${bodyHeader}
		`;
	}

	onClickItem()
	{
		this.item.type = this.id;
		this.item.currentObject = this;

		if (this.data.hasMenu)
		{
			this.openSlider({
				sliderId: 'calendar:item-sync-' + this.item.id,
				content: this.getItemSliderContent(this.item),
				width: this.contentSliderWidth,
				cacheable: false,
				data: this.item,
			});
		}
		else
		{
			this.openConnectSlider();
		}
	}
}