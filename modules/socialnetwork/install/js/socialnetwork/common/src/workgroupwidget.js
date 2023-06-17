import {Type, Tag, Loc, Event} from 'main.core';
import {PopupComponentsMaker} from 'ui.popupcomponentsmaker';

import {Widget} from './widget';

export class WorkgroupWidget extends Widget
{
	constructor(params) {
		super();

		this.groupId = (!Type.isUndefined(params.groupId) ? parseInt(params.groupId) : 0);
		this.avatarPath = (Type.isStringFilled(params.avatarPath) ? params.avatarPath : '');
		this.avatarType = (Type.isStringFilled(params.avatarType) ? params.avatarType : '');
		this.projectTypeCode = (Type.isStringFilled(params.projectTypeCode) ? params.projectTypeCode : '');
		this.urls = (Type.isPlainObject(params.urls) ? params.urls : {});
		this.perms = (Type.isPlainObject(params.perms) ? params.perms : {});
	}

	getData(params)
	{
		let data = null;

		const targetNode = params.targetNode;
		if (!Type.isDomNode(targetNode))
		{
			return data;
		}

		data = targetNode.getAttribute('data-workgroup');
		try
		{
			data = JSON.parse(data);
		}
		catch (err)
		{
			data = null;
		}

		return data;
	}

	getWidget(params)
	{
		const targetNode = (Type.isDomNode(params.targetNode) ? params.targetNode : null);
		if (!targetNode)
		{
			return null;
		}

		const data = (Type.isPlainObject(params.data) ? params.data : {});

		return new PopupComponentsMaker({
			target: targetNode,
			content: [
				{
					html: [
						{
							html: this.renderAbout(data)
						}
					]
				},
				{
					html: [
						{
							html: this.renderMembers(data),
						},
						{
							html: this.renderRoles(data),
						}
					]
				},
			]
		});
	}

	renderAbout()
	{
		let avatar = '<i></i>';
		if (Type.isStringFilled(this.avatarPath))
		{
			avatar = `<i style="background: #fff url('${encodeURI(this.avatarPath)}') no-repeat; background-size: cover;"></i>`;
		}

		let title = '';
		let description = '';

		switch (this.projectTypeCode.toLowerCase())
		{
			case 'project':
				title = Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_TITLE_PROJECT');
				description = Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_DESCRIPTION_PROJECT');
				break;
			case 'scrum':
				title = Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_TITLE_SCRUM');
				description = Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_DESCRIPTION_SCRUM');
				break;
			default:
				title = Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_TITLE_GROUP');
				description = Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ABOUT_DESCRIPTION_GROUP');
		}

		const classList = [
			'sonet-common-widget-avatar',
		];
		if (
			!Type.isStringFilled(this.avatarPath)
			&& Type.isStringFilled(this.avatarType)
		)
		{
			classList.push('sonet-common-workgroup-avatar');
			classList.push(`--${this.avatarType}`);
		}
		else
		{
			classList.push('ui-icon');
			classList.push('ui-icon-common-user-group');
		}

		const node = Tag.render`
			<div class="sonet-common-widget-item">
				<div class="sonet-common-widget-item-container">
					<div class="${classList.join(' ')}">${avatar}</div>
					<div class="sonet-common-widget-item-content">
						<div class="sonet-common-widget-item-title">${title}</div>
						<div class="sonet-common-widget-item-description">${description}</div>						
					</div>
				</div>
			</div>
		`;

		Event.bind(node, 'click', () => {
			if (!Type.isStringFilled(this.urls.card))
			{
				return;
			}

			BX.SidePanel.Instance.open(this.urls.card, {
				width: 900,
				loader: 'socialnetwork:group-card',
			});

			this.hide();
		});

		return node;
	}

	renderMembers()
	{
		const node = Tag.render`
			<div class="sonet-common-widget-item">
				<div class="sonet-common-widget-item-container">
					<div class="sonet-common-widget-icon ui-icon ui-icon-common-light-company"><i></i></div>
					<div class="sonet-common-widget-item-content">
						<div class="sonet-common-widget-item-title">${Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_MEMBERS_TITLE')}</div>
					</div>
				</div>
			</div>
		`;

		Event.bind(node, 'click', () => {
			if (!Type.isStringFilled(this.urls.members))
			{
				return;
			}

			BX.SidePanel.Instance.open(this.urls.members, {
				width: 1200,
				loader: 'group-users-loader'
			});

			this.hide();
		});

		return node;
	}

	renderRoles()
	{
		const canOpen = (
			Type.isBoolean(this.perms.canModify)
			&& this.perms.canModify
		);

		const hint = (!canOpen ? `data-hint="${Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ROLES_TITLE_NO_PERMISSIONS')}" data-hint-no-icon` : '');

		const node = Tag.render`
			<div class="sonet-common-widget-item" ${hint}>
				<div class="sonet-common-widget-item-container">
					<div class="sonet-common-widget-icon ui-icon ui-icon-service-light-roles-rights"><i></i></div>
					<div class="sonet-common-widget-item-content">
						<div class="sonet-common-widget-item-title">${Loc.getMessage('SONET_EXT_COMMON_WORKGROUP_WIDGET_ROLES_TITLE')}</div>
					</div>
				</div>
			</div>
		`;

		Event.bind(node, 'click', () => {

			if (
				!canOpen
				|| !Type.isStringFilled(this.urls.features)
			)
			{
				return;
			}

			BX.SidePanel.Instance.open(this.urls.features, {
				width: 800,
				loader: 'group-features-loader'
			});

			this.hide();
		});

		return node;
	}
}
