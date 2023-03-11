import {Loc, Tag, Type} from 'main.core';
import {MembersPopup} from './memberspopup';

import './css/popup.css';
import './css/scrum-members.css';

export class ScrumMembersPopup extends MembersPopup
{
	renderContainer()
	{
		return Tag.render`
			<span class="sonet-ui-members-popup-container">
				<span class="sonet-ui-members-popup-head">
					${this.popupData.all.tab}
					${this.popupData.scrumTeam.tab}
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

	resetPopupData()
	{
		this.popupData = {
			all: {
				currentPage: 1,
				renderedUsers: [],
				tab: Tag.render`
					<span
						class="sonet-ui-members-popup-head-item"
						onclick="${this.changeType.bind(this, 'all')}"
					>
						<span class="sonet-ui-members-popup-head-text">
							${Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_ALL')}
						</span>
					</span>
				`,
				innerContainer: Tag.render`<div class="sonet-ui-members-popup-inner"></div>`,
			},
			scrumTeam: {
				currentPage: 1,
				renderedUsers: [],
				tab: Tag.render`
					<span
						class="sonet-ui-members-popup-head-item"
						onclick="${this.changeType.bind(this, 'scrumTeam')}"
					>
						<span class="sonet-ui-members-popup-head-text">
							${Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_HEADS_SCRUM_1')}
						</span>
					</span>
				`,
				innerContainer: Tag.render`<div class="sonet-ui-members-popup-inner"></div>`,
			},
			members: {
				currentPage: 1,
				renderedUsers: [],
				tab: Tag.render`
					<span
						class="sonet-ui-members-popup-head-item"
						onclick="${this.changeType.bind(this, 'members')}"
					>
						<span class="sonet-ui-members-popup-head-text">
							${Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_TITLE_MEMBERS_SCRUM')}
						</span>
					</span>
				`,
				innerContainer: Tag.render`<div class="sonet-ui-members-popup-inner"></div>`,
			}
		};
	}

	renderUsers(users)
	{
		if (this.currentType === 'scrumTeam')
		{
			this.renderLabels(users);

			Object.values(users).forEach((user) => {
				if (
					this.getCurrentPopupData().renderedUsers.indexOf(user.ID) >= 0
					&& user.ROLE !== 'M'
				)
				{
					return;
				}
				this.getCurrentPopupData().renderedUsers.push(user.ID);

				const containersMap = new Map();
				containersMap.set('A', 'sonet-ui-scrum-members-popup-owner-container');
				containersMap.set('M', 'sonet-ui-scrum-members-popup-master-container');
				containersMap.set('E', 'sonet-ui-scrum-members-popup-team-container');

				if (Type.isUndefined(containersMap.get(user.ROLE)))
				{
					return;
				}

				this.getCurrentPopupData()
					.innerContainer
					.querySelector('.' + containersMap.get(user.ROLE))
					.appendChild(
						Tag.render`
							<a
								class="sonet-ui-members-popup-item"
								href="${user['HREF']}"
								target="_blank"
							>
								<span class="sonet-ui-members-popup-avatar-new">
									${this.getAvatar(user)}
									<span
										class="sonet-ui-members-popup-avatar-status-icon"
									></span>
								</span>
								<span
									class="sonet-ui-scrum-members-popup-name"
								>${user['FORMATTED_NAME']}</span>
							</a>
						`
					)
				;
			});
		}
		else
		{
			super.renderUsers(users);
		}
	}

	renderLabels(users)
	{
		const hasOwner = users.find((user) => user.ROLE === 'A');
		const hasMaster = users.find((user) => user.ROLE === 'M');
		const hasTeam = users.find((user) => user.ROLE === 'E');

		if (hasOwner)
		{
			if (
				Type.isNull(
					this.getCurrentPopupData().innerContainer
						.querySelector('.sonet-ui-scrum-members-popup-owner-container')
				)
			)
			{
				this.getCurrentPopupData().innerContainer.appendChild(
					Tag.render`
					<div class="sonet-ui-scrum-members-popup-owner-container">
						<span class="sonet-ui-scrum-members-popup-label">
							<span class="sonet-ui-scrum-members-popup-label-text">
								${Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_LABEL_SCRUM_OWNER')}
							</span>
						</span>
					</div>
				`
				);
			}
		}

		if (hasMaster)
		{
			if (
				Type.isNull(
					this.getCurrentPopupData().innerContainer
						.querySelector('.sonet-ui-scrum-members-popup-master-container')
				)
			)
			{
				this.getCurrentPopupData().innerContainer.appendChild(
					Tag.render`
					<div class="sonet-ui-scrum-members-popup-master-container">
						<span class="sonet-ui-scrum-members-popup-label">
							<span class="sonet-ui-scrum-members-popup-label-text">
								${Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_LABEL_SCRUM_MASTER')}
							</span>
						</span>
					</div>
				`
				);
			}
		}

		if (hasTeam)
		{
			if (
				Type.isNull(
					this.getCurrentPopupData().innerContainer
						.querySelector('.sonet-ui-scrum-members-popup-team-container')
				)
			)
			{
				this.getCurrentPopupData().innerContainer.appendChild(
					Tag.render`
					<div class="sonet-ui-scrum-members-popup-team-container">
						<span class="sonet-ui-scrum-members-popup-label">
							<span class="sonet-ui-scrum-members-popup-label-text">
								${Loc.getMessage('SONET_EXT_UI_GRID_MEMBERS_POPUP_LABEL_SCRUM_TEAM')}
							</span>
						</span>
					</div>
				`
				);
			}
		}
	}
}