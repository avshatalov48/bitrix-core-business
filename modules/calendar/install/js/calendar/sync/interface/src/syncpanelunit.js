// @flow
'use strict';

import 'ui.tilegrid';
import 'ui.forms';
import { Dom, Loc, Tag, Type, Event} from 'main.core';

export default class SyncPanelUnit
{
	logoClassName = '';

	constructor(options)
	{
		this.options = options;
		this.connectionProvider = this.options.connection;
	}

	getConnectionTemplate()
	{
		if (!this.connectionTemplate)
		{
			this.connectionTemplate = this.connectionProvider.getClassTemplateItem().createInstance(this.connectionProvider);
		}
		return this.connectionTemplate;
	}

	renderTo(outerWrapper)
	{
		if (Type.isElementNode(outerWrapper))
		{
			outerWrapper.appendChild(this.getContent());
		}
	}

	getContent()
	{
		let className = this.connectionProvider.getStatus() === 'success' ? '--active' : '';
		if (this.connectionProvider.getStatus() === 'pending')
		{
			className += '--pending';
		}

		this.unitNode = Tag.render`
			<div class="calendar-sync__calendar-item ${className}">
				<div class="calendar-sync__calendar-item--logo">
					${this.getLogoNode()}
				</div>
				<div class="calendar-sync__calendar-item--container">
					<div class="calendar-sync__calendar-item--title">${this.getTitle()}</div>
					${this.getButtonsWrap()}
				</div>
			</div>
		`;

		Event.bind(this.unitNode, 'click', this.handleItemClick.bind(this))

		return this.unitNode;
	}

	getLogoNode()
	{
		return Tag.render`<div class="calendar-sync__calendar-item--logo-image ${this.connectionProvider.getSyncPanelLogo()}"></div>`;
	}

	getTitle()
	{
		return this.connectionProvider.getSyncPanelTitle();
	}

	getButtonsWrap()
	{
		this.buttonsWrap = Tag.render`<div class="calendar-sync__calendar-item--buttons">
			${this.getButton()}
			<!--<div class="calendar-sync__calendar-item--more"></div>-->
		</div>`;
		// Event.bind(this.buttonsWrap, 'click', this.handleButtonClick.bind(this))

		return this.buttonsWrap;
	}

	refreshButton()
	{
		Dom.clean(this.buttonsWrap);
		this.button = this.buttonsWrap.appendChild(this.getButton());
	}

	getButton()
	{
		switch (this.connectionProvider.getStatus())
		{
			case 'success':
				this.button = Tag.render`
					<a data-role="status-success" class="ui-btn ui-btn-icon-success ui-btn-light-border ui-btn-round">
						${Loc.getMessage('CAL_BUTTON_STATUS_SUCCESS')}
					</a>`;
				break;
			case 'failed':
				this.button = Tag.render`
					<a data-role="status-failed" class="ui-btn ui-btn-icon-fail ui-btn-light-border ui-btn-round">
						${Loc.getMessage('CAL_BUTTON_STATUS_FAILED')}
					</a>`;
				break;
			case 'pending':
				this.button = Tag.render`
					<a data-role="status-pending" class="ui-btn ui-btn-disabled ui-btn-round">
						${Loc.getMessage('CAL_BUTTON_STATUS_PENDING')}
					</a>`;
				break;
			case 'not_connected':
				this.button = Tag.render`
					<a data-role="status-not_connected" class="ui-btn ui-btn-success ui-btn-round">
						${Loc.getMessage('CAL_BUTTON_STATUS_NOT_CONNECTED')}
					</a>`;
				break;

		}
		return this.button;
	}

	// handleButtonClick(e)
	// {
	// 	const target = e.target || e.srcElement;
	// 	if (Type.isElementNode(target))
	// 	{
	// 		const role = target.getAttribute('data-role');
	//
	// 		if (role === 'status-not_connected')
	// 		{
	// 			this.getConnectionTemplate().handleConnectButton();
	// 		}
	// 	}
	// }

	handleItemClick(e)
	{
		const status = this.connectionProvider.getStatus();
		if (['failed', 'success'].includes(status))
		{
			if (this.connectionProvider.hasMenu())
			{
				this.connectionProvider.showMenu(this.button);
			}
			else if (this.connectionProvider.getConnectStatus())
			{
				this.connectionProvider.openActiveConnectionSlider(this.connectionProvider.getConnection());
			}
			else
			{
				this.connectionProvider.openInfoConnectionSlider();
			}
		}
		else if(status === 'not_connected')
		{
			this.getConnectionTemplate().handleConnectButton();
		}
	}
}
