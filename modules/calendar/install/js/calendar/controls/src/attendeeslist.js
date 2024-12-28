import {Loc, Type} from "main.core";
import {MenuManager, MenuItem} from "main.popup";
import {UserPlannerSelector} from "calendar.controls";

export class AttendeesList
{
	constructor(node, attendeesList = {})
	{
		this.attendeesList = attendeesList;
		this.node = node;
	}

	setAttendeesList(attendeesList)
	{
		this.attendeesList = attendeesList;

		return this;
	}

	showPopup()
	{
		if (this.popup)
		{
			this.popup.destroy();
		}

		const menuItems = this.getMenuItems();

		this.popup = this.getPopup(menuItems);
		this.popup.show();

		this.addAvatarToMenuItems();
	}

	addAvatarToMenuItems()
	{
		this.popup.menuItems.forEach((item) =>
		{
			const icon = item.layout.item.querySelector('.menu-popup-item-icon');
			if (Type.isPlainObject(item.dataset))
			{
				icon.appendChild(UserPlannerSelector.getUserAvatarNode(item.dataset.user))
			}
		});
	}

	getPopup(menuItems)
	{
		return MenuManager.create(
			'compact-event-form-attendees' + Math.round(Math.random() * 100000),
			this.node,
			menuItems,
			{
				closeByEsc: true,
				autoHide: true,
				zIndex: this.zIndex,
				offsetTop: 0,
				offsetLeft: 15,
				angle: true,
				cacheable: false,
				className: 'calendar-popup-user-menu'
			}
		);
	}

	getMenuItems()
	{
		const menuItems = [];
		[
			{
				code: 'accepted', // Accepted
				title: Loc.getMessage('EC_ATTENDEES_Y_NUM'),
			},
			{
				code: 'requested', // Still thinking about
				title: Loc.getMessage('EC_ATTENDEES_Q_NUM'),
			},
			{
				code: 'declined', // Declined
				title: Loc.getMessage('EC_ATTENDEES_N_NUM'),
			},
		].forEach((group: { code: string, title: string }): void =>
		{
			const groupUsers = this.attendeesList[group.code];
			if (groupUsers.length > 0)
			{
				menuItems.push(new MenuItem({
					text: group.title.replace('#COUNT#', groupUsers.length),
					delimiter: true,
				}));

				groupUsers.forEach((user): void =>
				{
					user.toString = () => user.ID;
					menuItems.push(
						{
							text: BX.util.htmlspecialchars(user.DISPLAY_NAME),
							dataset: { user },
							className: `calendar-add-popup-user-menu-item ${user.COLLAB_USER ? 'calendar-collab-user' : ''}`,
							onclick: (): void =>
								BX.SidePanel.Instance.open(
									user.URL,
									{
										loader: 'intranet:profile',
										cacheable: false,
										allowChangeHistory: false,
										contentClassName: 'bitrix24-profile-slider-content',
										width: 1100,
									},
								),
						},
					);
				});
			}
		});

		return menuItems;
	}

	static sortAttendees(attendees)
	{
		return {
			accepted: attendees.filter((user) => ['H', 'Y'].includes(user.STATUS)),
			requested: attendees.filter((user) => user.STATUS === 'Q' || user.STATUS === ''),
			declined: attendees.filter((user) => user.STATUS === 'N'),
		};
	}
}
