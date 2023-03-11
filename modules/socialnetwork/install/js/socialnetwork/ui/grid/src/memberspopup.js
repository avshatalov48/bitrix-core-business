import {Dom, Event, Loc, Tag, Type, ajax} from 'main.core';
import {PopupWindowManager} from 'main.popup';
import {Loader} from 'main.loader';

import './css/popup.css';

export class MembersPopup
{
	constructor(options)
	{
		this.componentName = options.componentName;
		this.signedParameters = options.signedParameters;
	}

	showPopup(groupId, groupType, bindNode, type = 'all')
	{
		if (this.isPopupShown)
		{
			this.popup.destroy();
		}

		this.groupId = groupId;

		this.resetPopupData(groupType);
		this.changeType(type, false);

		this.popup = PopupWindowManager.create({
			id: 'workgroup-grid-members-popup-menu',
			className: 'sonet-ui-members-popup',
			bindElement: bindNode,
			autoHide: true,
			closeByEsc: true,
			lightShadow: true,
			bindOptions: {
				position: 'bottom',
			},
			animationOptions: {
				show: {
					type: 'opacity-transform',
				},
				close: {
					type: 'opacity',
				},
			},
			events: {
				onPopupDestroy: () => {
					this.loader = null;
					this.isPopupShown = false;
				},
				onPopupClose: () => {
					this.popup.destroy();
				},
				onAfterPopupShow: (popup) => {
					popup.contentContainer.appendChild(this.renderContainer());

					this.showLoader();
					this.showUsers(groupId, type);

					this.isPopupShown = true;
				},
			},
		});
		this.popupScroll(groupId, type);
		this.popup.show();
	}

	renderContainer()
	{
		return Tag.render`
			<span class="sonet-ui-members-popup-container">
				<span class="sonet-ui-members-popup-head">
					${this.popupData.all.tab}
					${this.popupData.heads.tab}
					${this.popupData.members.tab}
				</span>
				<span class="sonet-ui-members-popup-body">
					<div class="sonet-ui-members-popup-content">
						<div class="sonet-ui-members-popup-content-box">
							${this.getCurrentPopupData().innerContainer}
						</div>
					</div>
				</span>
			</span>
		`;
	}

	popupScroll(groupId, type)
	{
		if (!Type.isDomNode(this.getCurrentPopupData().innerContainer))
		{
			return;
		}

		Event.bind(this.getCurrentPopupData().innerContainer, 'scroll', (event) => {
			const area = event.target;
			if (area.scrollTop > (area.scrollHeight - area.offsetHeight) / 1.5)
			{
				this.showUsers(groupId, type);
				Event.unbindAll(this.getCurrentPopupData().innerContainer);
			}
		});
	};

	showUsers(groupId, type)
	{
		ajax.runAction('socialnetwork.api.workgroup.getGridPopupMembers', {
			data: {
				groupId,
				type,
				page: this.getCurrentPopupData().currentPage,
				componentName: this.componentName,
				signedParameters: this.signedParameters,
			},
		}).then(
			(response) => {
				if (this.groupId !== groupId || this.currentType !== type)
				{
					this.hideLoader();
					return;
				}
				if (response.data.length > 0)
				{
					this.renderUsers(response.data);
					this.popupScroll(groupId, this.currentType);
				}
				else if (!this.getCurrentPopupData().innerContainer.hasChildNodes())
				{
					this.getCurrentPopupData().innerContainer.innerText = Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_EMPTY');
				}
				this.getCurrentPopupData().currentPage++;
				this.hideLoader();
			},
			() => this.hideLoader()
		);
	}

	renderUsers(users)
	{
		Object.values(users).forEach((user) => {
			if (this.getCurrentPopupData().renderedUsers.indexOf(user.ID) >= 0)
			{
				return;
			}
			this.getCurrentPopupData().renderedUsers.push(user.ID);

			this.getCurrentPopupData().innerContainer.appendChild(
				Tag.render`
					<a class="sonet-ui-members-popup-item" href="${user['HREF']}" target="_blank">
						<span class="sonet-ui-members-popup-avatar-new">
							${this.getAvatar(user)}
							<span class="sonet-ui-members-popup-avatar-status-icon"></span>
						</span>
						<span class="sonet-ui-members-popup-name">${user['FORMATTED_NAME']}</span>
					</a>
				`
			);
		})
	}

	getAvatar(user)
	{
		if (Type.isStringFilled(user['PHOTO']))
		{
			return Tag.render`
				<div class="ui-icon ui-icon-common-user sonet-ui-members-popup-avatar-img">
					<i style="background-image: url('${encodeURI(user['PHOTO'])}')"></i>
				</div>
			`;
		}

		return Tag.render`
			<div class="ui-icon ui-icon-common-user sonet-ui-members-popup-avatar-img"><i></i></div>
		`;
	}

	showLoader()
	{
		if (!this.loader)
		{
			this.loader = new Loader({
				target: this.popup.getPopupContainer().querySelector('.sonet-ui-members-popup-content'),
				size: 40,
			});
		}
		void this.loader.show();
	}

	hideLoader()
	{
		if (this.loader)
		{
			void this.loader.hide();
			this.loader = null;
		}
	}

	changeType(newType, loadUsers = true)
	{
		const oldType = this.currentType;

		this.currentType = newType;

		Object.values(this.popupData).forEach((item) => {
			Dom.removeClass(item.tab, 'sonet-ui-members-popup-head-item-current');
		});
		Dom.addClass(this.getCurrentPopupData().tab, 'sonet-ui-members-popup-head-item-current');

		if (oldType)
		{
			Dom.replace(this.popupData[oldType].innerContainer, this.getCurrentPopupData().innerContainer);
		}

		if (loadUsers && this.getCurrentPopupData().currentPage === 1)
		{
			this.showLoader();
			this.showUsers(this.groupId, newType);
		}
	}

	resetPopupData(groupType)
	{
		let headTitle = Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_HEADS');
		let membersTitle = Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_MEMBERS');
		if (groupType === 'project')
		{
			headTitle = Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_HEADS_PROJECT');
			membersTitle = Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_MEMBERS_PROJECT');
		}

		this.popupData = {
			all: {
				currentPage: 1,
				renderedUsers: [],
				tab: Tag.render`
					<span class="sonet-ui-members-popup-head-item" onclick="${this.changeType.bind(this, 'all')}">
						<span class="sonet-ui-members-popup-head-text">
							${Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_ALL')}
						</span>
					</span>
				`,
				innerContainer: Tag.render`<div class="sonet-ui-members-popup-inner"></div>`,
			},
			heads: {
				currentPage: 1,
				renderedUsers: [],
				tab: Tag.render`
					<span class="sonet-ui-members-popup-head-item" onclick="${this.changeType.bind(this, 'heads')}">
						<span class="sonet-ui-members-popup-head-text">
							${headTitle}
						</span>
					</span>
				`,
				innerContainer: Tag.render`<div class="sonet-ui-members-popup-inner"></div>`,
			},
			members: {
				currentPage: 1,
				renderedUsers: [],
				tab: Tag.render`
					<span class="sonet-ui-members-popup-head-item" onclick="${this.changeType.bind(this, 'members')}">
						<span class="sonet-ui-members-popup-head-text">
							${membersTitle}
						</span>
					</span>
				`,
				innerContainer: Tag.render`<div class="sonet-ui-members-popup-inner"></div>`,
			},
		};
	}

	getCurrentPopupData()
	{
		return this.popupData[this.currentType];
	}
}
