// @flow
'use strict';

import ConnectionItem from "./connectionitem";
import { Event, Reflection, Type } from 'main.core';
import {EventEmitter} from "main.core.events";
import { Util } from 'calendar.util';

export class ConnectionProvider extends EventEmitter
{
	MENU_WIDTH = 200;
	MENU_PADDING = 7;
	MENU_INDEX = 3020;
	SLIDER_WIDTH = 606;
	STATUS_SYNCHRONIZING = 'synchronizing';
	STATUS_SUCCESS = 'success';
	STATUS_FAILED = 'failed';
	STATUS_PENDING = 'pending';
	STATUS_NOT_CONNECTED = 'not_connected';
	ERROR_CODE = 'error';

	STATUS_LIST = [
		this.STATUS_SYNCHRONIZING,
		this.STATUS_SUCCESS,
		this.STATUS_FAILED,
		this.STATUS_PENDING,
		this.STATUS_NOT_CONNECTED
	];
	WAITING_MODE_MAX_TIME = 360000; // 6 min

	constructor(options)
	{
		super();
		this.setEventNamespace('BX.Calendar.Sync.Manager.ConnectionProvider');

		this.status = options.status;
		this.connected = options.connected;
		this.userName = options.userName || '';
		this.mainPanel = options.mainPanel === true;
		this.pendingStatus = options.pendingStatus === true;
		this.gridTitle = options.gridTitle;
		this.gridColor = options.gridColor;
		this.gridIcon = options.gridIcon;
		this.type = options.type;
		this.viewClassification = options.viewClassification;
		this.templateClass = options.templateClass;
		// this.wizardClassName = options.wizardClass || null;
		this.connections = [];
		this.id = options.id || '';
	}

	static createInstance(options)
	{
		return new this(options);
	}

	isActive()
	{
		return this.connected;
	}

	hasMenu()
	{
		return false;
	}

	setAdditionalParams(options)
	{
		this.additionalParams = options;
	}

	setSyncDate(offset)
	{
		offset = parseInt(offset);
		if (offset > 60)
		{
			this.syncDate = new Date(new Date().getTime() - offset * 1000);
		}
		else if (!isNaN(offset))
		{
			this.syncDate = new Date();
		}
		else
		{
			this.syncDate = null;
		}

		if (this.getConnection())
		{
			this.getConnection().syncDate = this.syncDate;
		}
	}

	getSyncDate()
	{
		return this.syncDate;
	}

	setSections(sections)
	{
		this.sections = sections;
	}

	setStatus(status)
	{
		if (this.STATUS_LIST.includes(status))
		{
			this.status = status;
			if (!this.connected && (status === this.STATUS_SUCCESS || status === this.STATUS_FAILED))
			{
				this.connected = true;
			}
			else if(this.connected && status === this.STATUS_NOT_CONNECTED)
			{
				this.connected = false;
			}
		}

		return this;
	}

	getGridTitle()
	{
		return this.gridTitle;
	}

	getGridColor()
	{
		return this.gridColor;
	}

	getGridIcon()
	{
		return this.gridIcon;
	}
	
	clearConnections()
	{
		this.connections = [];
	}

	setConnections()
	{
		this.connections.push(ConnectionItem.createInstance({
			syncDate: this.getSyncDate(),
			connectionName: this.connectionName,
			status: this.status,
			connected: this.connected,
			userName: this.userName,
			addParams: {
				sections: this.sections,
				id: this.id || this.type,
			},
			type: this.type,
		}));
	}

	setInterfaceUnit(interfaceUnit): void
	{
		this.interfaceUnit = interfaceUnit;
	}

	getInterfaceUnit()
	{
		return this.interfaceUnit;
	}

	getConnections()
	{
		return this.connections;
	}

	getConnection()
	{
		return this.connections[0];
	}

	getType()
	{
		return this.type;
	}

	getViewClassification()
	{
		return this.viewClassification;
	}

	getConnectStatus()
	{
		return this.connected;
	}

	getSyncStatus()
	{
		return this.status;
	}

	getStatus()
	{
		if (this.getWizardSyncMode())
		{
			return 'synchronizing';
		}

		if (this.connected)
		{
			return this.status
				? "success"
				: "failed";
		}
		else if (this.pendingStatus)
		{
			return 'pending';
		}
		else
		{
			return 'not_connected';
		}
	}

	getTemplateClass()
	{
		return this.templateClass;
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
			width: this.SLIDER_WIDTH,
			allowChangeHistory: false,
			events: {
				onLoad: event => {
					this.itemSlider = event.getSlider();
				}
			}
		});
	}

	closeSlider()
	{
		if (this.itemSlider)
		{
			this.itemSlider.close();
		}
	}

	openInfoConnectionSlider()
	{
		const content = this.getClassTemplateItem().createInstance(this).getInfoConnectionContent();
		this.openSlider({
			sliderId: 'calendar:item-sync-connect-' + this.type,
			content: content,
			cacheable: false,
			data: {
				provider: this,
			},
		});
	}

	openActiveConnectionSlider(connection)
	{
		const itemInterface = this.getClassTemplateItem().createInstance(this, connection);
		if (this.type === 'google')
		{
			itemInterface.getSectionsForGoogle().then(() => {
				this.openActiveConnectionSliderVendor(itemInterface, connection);
			})
		}
		else if (this.type === 'icloud')
		{
			itemInterface.getSectionsForIcloud().then(() => {
				this.openActiveConnectionSliderVendor(itemInterface, connection);
			});
		}
		else if (this.type === 'office365')
		{
			itemInterface.getSectionsForOffice365().then(() => {
				this.openActiveConnectionSliderVendor(itemInterface, connection);
			});
		}
		else
		{
			this.openActiveConnectionSliderVendor(itemInterface, connection);
		}
	}

	openActiveConnectionSliderVendor(itemInterface, connection)
	{
		const content = itemInterface.getActiveConnectionContent();

		this.openSlider({
			sliderId: 'calendar:item-sync-' + connection.id,
			content: content,
			cacheable: false,
			data: {
				provider: this,
				connection: connection,
				itemInterface: itemInterface,
			},
		});
	}

	getClassTemplateItem()
	{
		const itemClass = Reflection.getClass(this.getTemplateClass());
		if (Type.isFunction(itemClass))
		{
			return itemClass;
		}

		return null;
	}

	getConnectionById(id)
	{
		const connections = this.getConnections();
		if (connections.length > 0)
		{
			const result = connections.filter(connection => {
				return connection.getId() == id;
			});
			if (result)
			{
				return result[0];
			}
		}

		return null;
	}

	getSyncPanelTitle()
	{
		return this.gridTitle;
	}

	getSyncPanelLogo()
	{
		return '--' + this.type;
	}

	setWizardSyncMode(value)
	{
		this.wizardSyncMode = value;
	}

	getWizardSyncMode()
	{
		return this.wizardSyncMode;
	}

	setWizardState(stateData)
	{
		const wizard = this.getActiveWizard();
		if (wizard)
		{
			if (stateData.status === this.ERROR_CODE)
			{
				wizard.setErrorState(stateData);
			}
			else
			{
				wizard.handleUpdateState(stateData);
			}
		}
	}

	setUserName(userName = '')
	{
		this.userName = userName;
		if (this.getConnection())
		{
			this.getConnection().setUserName(userName);
		}
	}

	setActiveWizard(wizard)
	{
		this.activeWizard = wizard;
		wizard.subscribe('onConnectionCreated', this.handleCreatedConnection.bind(this));
		wizard.subscribe('onClose', this.handleCloseWizard.bind(this));
		wizard.subscribe('startWizardWaitingMode', this.startWaitingMode.bind(this));
		wizard.subscribe('endWizardWaitingMode', this.endWaitingMode.bind(this));
	}

	getActiveWizard()
	{
		return this.activeWizard || null;
	}

	startWaitingMode()
	{
		this.emit('onStartWaitingMode');
		this.waitingModeReserveTimeout = setTimeout(() => {
			if (this.getActiveWizard() && this.getActiveWizard().getSlider())
			{
				BX.reload();
			}
			}, this.WAITING_MODE_MAX_TIME
		);
	}

	endWaitingMode()
	{
		this.emit('onEndWaitingMode');
		if (this.waitingModeReserveTimeout)
		{
			clearTimeout(this.waitingModeReserveTimeout);
			this.waitingModeReserveTimeout = null;
		}
	}

	handleCreatedConnection()
	{
		this.setStatus(this.STATUS_SUCCESS);
		this.getInterfaceUnit().setSyncStatus(this.STATUS_SUCCESS);

		BX.ajax.runAction('calendar.api.syncajax.clearSuccessfulConnectionNotifier', {
			data: {
				accountType: this.getType()
			}
		});

		// TODO: It's better to avoid using of calendarContext.
		//  Replace it with eventEmitter events and check for unnecessary requests
		const calendarContext = Util.getCalendarContext();
		if (calendarContext)
		{
			calendarContext.syncInterface.refreshDebounce();
		}
	}

	handleCloseWizard()
	{
		const wizard = this.getActiveWizard();
		this.setWizardSyncMode(false);
		if (wizard && wizard.isSyncFinished())
		{
			this.setStatus(this.STATUS_SUCCESS);
			this.getInterfaceUnit().setSyncStatus(this.STATUS_SUCCESS);
		}
		else
		{
			this.setStatus(this.STATUS_SYNCHRONIZING);
			this.getInterfaceUnit().setSyncStatus(this.STATUS_SYNCHRONIZING);

			BX.SidePanel.Instance.getOpenSliders().forEach(slider =>
			{
				if (['calendar:sync-slider', 'calendar:section-slider']
					.includes(slider.getUrl()))
				{
					slider.close();
				}
			});
		}

		this.getInterfaceUnit().refreshButton();

		this.emit('onEndWaitingMode');

		this.emit('onCloseSyncWizard');

		if (wizard)
		{
			wizard.unsubscribeAll();
		}
	}
	
	refresh(options)
	{
		this.status = options.syncInfo.status || false;
		this.connected = options.syncInfo.connected || false;
		this.id = options.syncInfo.id || null;
		
		if (options.syncLink)
		{
			this.syncLink = options.syncLink;
		}

		this.setSyncDate(options.syncInfo.syncOffset);
		this.setSections(options.sections);
		this.clearConnections();
		this.setConnections();
	}
}
