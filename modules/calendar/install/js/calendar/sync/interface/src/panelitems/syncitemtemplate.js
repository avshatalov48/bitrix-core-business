import {ajax, Dom, Loc, Tag, Type} from "main.core";
import {Menu} from "main.popup";
import { MessageBox } from 'ui.dialogs.messagebox';
import ConnectionControls from "../controls/connectioncontrols";
import {EventEmitter} from "main.core.events";
import SyncStatusPopup from "../controls/syncstatuspopup";

export default class SyncItemTemplate
{
	sliderWidth = 840;
	contentSliderWidth = 606;
	menuWidth = 200;
	menuPadding = 7;
	menuIndex = 3020;

	constructor(options)
	{
		this.id = options.id;
		this.title = options.title;
		this.image = options.image;
		this.status = options.status;
		this.selected = options.itemSelected;
		this.color = options.color;

		this.layout = {
			container: null,
			image: null,
			title: null,
			status: false,
			innerContent: '',
		};
		this.data = options.data || {};

		this.sliderTitle = Loc.getMessage('CAL_CALENDAR_TITLE');
		this.sliderIconClass = 'calendar-sync-slider-header-icon-caldav';
		this.titleActiveHeader = Loc.getMessage('CAL_CALENDAR_IS_CONNECT');
	}

	getInnerContent()
	{
		this.layout.innerContent = Tag.render`<div class="calendar-sync-item ${this.getAdditionalContentClass()}" onclick="${this.onClickItem.bind(this)}" style="${this.getContentStyles()}">
			<div class="calendar-item-content">
				${this.getImage()}
				${this.getTitle()}
				${(this.isActive() ? this.getStatus() : '')}
			</div>
		</div>`;

		return this.layout.innerContent;
	}

	getTitle()
	{
		if (!this.layout.title)
		{
			this.layout.title = Tag.render `
				<div class="calendar-sync-item-title">${BX.util.htmlspecialchars(this.title)}</div>`;
		}

		return this.layout.title;
	}

	getImage()
	{
		if (!this.layout.image)
		{
			this.layout.image = Tag.render `
				<div class="calendar-sync-item-image">
					<div class="calendar-sync-item-image-item" style="background-image: ${this.image ? 'url(' + this.image + ')' : ''}"></div>
				</div>`;
		}

		return this.layout.image;
	}

	getStatus()
	{
		if (this.isActive())
		{
			this.layout.status = Tag.render `
				<div class="calendar-sync-item-status"></div>
			`;
		}

		return this.layout.status;
	}

	isActive()
	{
		return (this.selected === true);
	}

	getAdditionalContentClass()
	{
		if (this.isActive())
		{
			if (this.status)
			{
				return 'calendar-sync-item-selected';
			}
			else
			{
				return 'calendar-sync-item-failed';
			}
		}
		else
		{
			return '';
		}
	}

	getContentStyles()
	{
		if (this.selected)
		{
			return 'background-color:' + this.color + ';';
		}
		else
		{
			return '';
		}
	}

	onClickItem()
	{
		if (this.data.hasMenu)
		{
			this.showMenu();
		}
		else
		{
			this.openConnectSlider();
		}
	}

	getMenuItems()
	{
		return this.data.menu;
	}

	showMenu()
	{
		if (!this.menu)
		{
			let menuItems = this.getMenuItems();

			menuItems.forEach(item => {
				item.type = this.id;
				item.currentObject = this;
				item.onclick = () => {
					this.openSlider({
						sliderId: 'calendar:item-sync-' + item.id,
						content: this.getItemSliderContent(item),
						width: this.contentSliderWidth,
						cacheable: false,
						data: item,
					});
				};
			});

			menuItems.push(
				{delimiter: true},
				{
					id: 'connect',
					text: Loc.getMessage('ADD_MENU_CONNECTION'),
					onclick: () => {
						this.openConnectSlider();
					},
				}
			);

			this.menu = new Menu({
				className: 'calendar-sync-popup-status',
				bindElement: this.layout.innerContent,
				items: menuItems,
				width: this.menuWidth,
				padding: this.menuPadding,
				autoHide: true,
				closeByEsc: true,
				zIndexAbsolute: this.menuIndex,
				id: this.id + '-menu',
			});

			this.menu.getMenuContainer().addEventListener('click', () => {
				this.menu.close();
			});

			this.menu.show();
		}
		else
		{
			this.menu.show();
		}
	}

	openSlider(options)
	{
		BX.SidePanel.Instance.open(options.sliderId, {
			contentCallback(slider)
			{
				return new Promise((resolve, reject) => {
					resolve(options.content);
				});
			},
			data: options.data || {},
			cacheable: options.cacheable,
			width: options.width || this.sliderWidth,
			allowChangeHistory: false,
			events: {
				onLoad: event => {
					this.itemSlider = event.getSlider();
				}
			}
		});
	}

	openConnectSlider()
	{
		this.openSlider({
			sliderId: 'calendar:item-sync-connect-' + this.id,
			content: this.getItemSliderContent({
				id: this.id,
				status: this.status,
			}),
			width: this.contentSliderWidth,
			cacheable: false,
			data: {
				userName: this.sliderTitle,
				connectionName: this.title,
				syncTimestamp: this.data.syncDate,
				status: this.status,
				type: this.id,
				currentObject: this,
			},
		});
	}

	getItemSliderContent(options = {})
	{
		return Tag.render`
			<div class="calendar-sync-wrap calendar-sync-wrap-detail">
				${this.getSliderHeader(options)}
				${this.getSliderContent(options)}
			</div>
		`;
	}

	getSliderHeader(options)
	{
		return Tag.render`
			<div class="calendar-sync-header">
				<span class="calendar-sync-header-text">${this.sliderTitle}</span>
				${this.getBlockStatus(options)}
			</div>
		`;
	}

	getBlockStatus(options)
	{
		let statusInfoBlock;

		const status = options.id ? this.getStatusBlock(options.status) : 'not_connect';
		if (status === 'success')
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-success calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_SUCCESS')}</span>
				</div>
			`;
		}
		else if (status === 'failed')
		{
			statusInfoBlock = Tag.render `
				<div id="status-info-block" class="ui-alert ui-alert-danger calendar-sync-status-info">
					<span class="ui-alert-message">${Loc.getMessage('SYNC_STATUS_ALERT')}</span>
				</div>
			`;
		}
		else
		{
			statusInfoBlock = '';
		}

		if (statusInfoBlock !== '')
		{
			statusInfoBlock.addEventListener('mouseenter', (event) =>
			{
				this.blockEnterTimeout = setTimeout(() =>
					{
						this.blockEnterTimeout = null;
						this.showStatusPopup(statusInfoBlock);
					}, 150
				);
			}, false);

			statusInfoBlock.addEventListener('mouseleave', event =>
			{
				this.blockLeaveTimeout = setTimeout(() =>
					{
						this.hideStatusPopup();
					}, 150
				);
			});
		}

		return Tag.render `
			<div class="calendar-sync-status-block" id="calendar-sync-status-block">
				${statusInfoBlock}
			</div>
		`;
	}

	getStatusBlock(status)
	{
		if (this.selected === true && Type.isBoolean(status))
		{
			if (status === true)
			{
				return 'success';
			}
			else
			{
				return 'failed';
			}
		}

		return 'not_connect';
	}

	getSliderIconClass()
	{
		return 'calendar-sync-slider-header-icon-caldav';
	}

	getSliderConnectBlock(connectSlider = true)
	{
		return Tag.render`
			<div class="calendar-sync-slider-header">
				<div class="calendar-sync-slider-title">
					${
						(this.selected && connectSlider) 
							? this.titleActiveHeader 
							: this.titleInfoHeader
					}
				</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">
						${
							(this.selected && connectSlider) 
								? this.descriptionActiveHeader 
								: this.descriptionInfoHeader
						}
					</span>
				</div>
				<div class="calendar-sync-slider-info">
					<span class="calendar-sync-slider-info-text">
						<a class="calendar-sync-slider-info-link" href="javascript:void(0);" onclick="${this.showHelp.bind(this)}">
							${Loc.getMessage('CAL_TEXT_ABOUT_WORK_SYNC')}
						</a>
					</span>
				</div>
			</div>
		`;
	}

	getSliderContent(options)
	{
		return options.id ? this.getSelectedSliderContent(options) : this.getConnectSliderContent();
	}

	runUpdateInfo(option)
	{
		ajax.runAction('calendar.api.calendarajax.setSectionStatus', {
			data: {
				sectionStatus: this.sectionStatusObject,
				userId: this.data.userId,
			},
		}).then(response => {
			// this.emit('onRefreshSection', {});
		})
	}

	onClickCheckSection(event)
	{
		this.sectionStatusObject[event.target.value] = event.target.checked;

		this.runUpdateInfo();
	}

	getConnectSliderContent()
	{
		const formObject = new ConnectionControls();
		const formWrapper = formObject.getWrapper();
		const form = formObject.getForm();
		const button = formObject.getAddButton();
		const buttonWrapper = formObject.getButtonWrapper();
		const infoBlock = this.getSliderContentInfoBlock();

		button.addEventListener('click', (event) => {
			Dom.addClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
			event.preventDefault();
			this.sendRequestAddConnection(form);
		});

		Dom.append(button, buttonWrapper);
		Dom.append(buttonWrapper, form);
		Dom.append(form, formWrapper);

		return Tag.render`
			${infoBlock}
			${formWrapper}
		`;
	}

	getSliderContentInfoBlock()
	{
		return Tag.render`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${this.sliderIconClass}"></div>
				${this.getSliderConnectBlock(false)}
			</div>
		`;
	}

	getSelectedSliderContent(options)
	{
		const formObject = new ConnectionControls();
		const formWrapper = formObject.getWrapper();
		const form = formObject.getForm(options);
		const disconnectButton = formObject.getDisconnectButton();
		const buttonWrapper = formObject.getButtonWrapper();

		disconnectButton.addEventListener('click', (event) => {
			event.preventDefault();
			this.sendRequestRemoveConnection(options.id);
		});

		form.addEventListener('input', event => {

		});

		Dom.append(disconnectButton, buttonWrapper);
		Dom.append(buttonWrapper, form);
		Dom.append(form, formWrapper);

		return Tag.render`
			<div class="calendar-sync-slider-section">
				<div class="calendar-sync-slider-header-icon ${this.sliderIconClass}"></div>
				${this.getSliderConnectBlock()}
			</div>
			${formWrapper}
		`;
	}

	sendRequestRemoveConnection(id)
	{
		BX.ajax.runAction('calendar.api.calendarajax.removeConnection', {
			data: {
				userId: this.data.userId,
				connectionId: id,
			}
		}).then(() => {
			// BX.SidePanel.Instance.postMessage(window.BX.SidePanel.Instance.getTopSlider(), "refreshSliderGrid", {});
			// window.BX.SidePanel.Instance.getTopSlider().close();
			BX.reload();
		});
	}

	sendRequestEditConnection(form, options)
	{
		BX.ajax.runAction('calendar.api.calendarajax.editConnection', {
			data: {
				form: new FormData(form),
				connectionId: options.connectionId,
			}
		}).then(() => {
			BX.reload();
		});
	}

	sendRequestAddConnection(form)
	{
		const fd = new FormData(form);
		BX.ajax.runAction('calendar.api.calendarajax.addConnection', {
			data: {
				userId: this.data.userId,
				name: fd.get('name'),
				server: fd.get('server'),
				userName: fd.get('user_name'),
				pass: fd.get('password'),
			}
		}).then((response) => {
			// BX.SidePanel.Instance.postMessage(window.top.BX.SidePanel.Instance.getTopSlider(), "refreshSliderGrid", {});
			// window.BX.SidePanel.Instance.getTopSlider().close();
			BX.reload();
		}, response => {
			const button = form.querySelector('#connect-button');
			this.showAlertPopup(response.errors[0], button);
		});
	}

	showHelp()
	{
		if(BX.Helper)
		{
			BX.Helper.show("redirect=detail&code=" + this.helpdeskCode);
			event.preventDefault();
		}
	}

	getHelpdeskLink()
	{
		return 'https://helpdesk.bitrix24.ru/open/' + this.helpdeskCode;
	}

	showAlertPopup(alert, button)
	{
		let message = '';
		if (alert.code === 'incorrect_parameters')
		{
			message = Loc.getMessage('CAL_TEXT_ALERT_INCORRECT_PARAMETERS');
		}
		else if (alert.code === 'tech_problem')
		{
			message = Loc.getMessage('CAL_TEXT_ALERT_TECH_PROBLEM');
		}
		else
		{
			message = Loc.getMessage('CAL_TEXT_ALERT_DEFAULT');
		}

		const messageBox = new BX.UI.Dialogs.MessageBox({
			message: message,
			title: alert.message,
			buttons: BX.UI.Dialogs.MessageBoxButtons.OK,
			okCaption: Loc.getMessage('CAL_TEXT_BUTTON_RETURN_TO_SETTINGS'),
			minWidth: 358,
			mediumButtonSize: false,
			popupOptions: {
				zIndex: 3021,
				height: 166,
				width: 358,
				className: 'calendar-alert-popup-connection'
			},
			onOk: () => {
				Dom.removeClass(button, ['ui-btn-clock', 'ui-btn-disabled']);
				return true;
			}
		});

		messageBox.show();
	}

	showStatusPopup(elementNode)
	{
		const dataItem = this.itemSlider.getData().entries();

		if(dataItem && dataItem.status !== 'not_connect')
		{
			const options = {
				syncInfo: {
					[this.id]: {
						connected: true,
						type: this.id,
						userName: dataItem.text,
						connectionName: dataItem.connectionName,
						syncTimestamp: dataItem.syncDate,
						status: dataItem.status,
					}
				}
			}
			this.statusPopup = new SyncStatusPopup(options);
			this.statusPopup.createPopup(elementNode);
			this.statusPopup.show();

			this.statusPopup.getPopup().getPopupContainer().addEventListener('mouseenter', e => {
				clearTimeout(this.blockEnterTimeout);
				clearTimeout(this.blockLeaveTimeout);
			});
			this.statusPopup.getPopup().getPopupContainer().addEventListener('mouseleave', () => {
				this.hideStatusPopup();
			});
		}
	}

	hideStatusPopup()
	{
		if (this.statusPopup)
		{
			this.statusPopup.hide();
			this.statusPopup.getPopup().destroy();
		}
	}
}