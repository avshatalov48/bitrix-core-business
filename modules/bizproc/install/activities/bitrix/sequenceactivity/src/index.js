import { Dom, Tag, Event, Text, Type, Reflection } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Menu, MenuItem } from 'main.popup';

import './css/style.css';

// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
let _SequenceActivityCurClick = null;

// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
function _SequenceActivityClick(activityIndex, i, presetId)
{
	const preset = presetId ? window.arAllActivities[activityIndex].PRESETS.find((item) => item.ID === presetId) : null;
	const defaultProps = preset ? preset.PROPERTIES : {};

	const activity = {
		Properties: {
			Title: (preset && preset.NAME) || Text.encode(window.arAllActivities[activityIndex].NAME),
			...defaultProps,
		},
		Type: window.arAllActivities[activityIndex].CLASS,
		Children: [],
	};

	_SequenceActivityCurClick.AddActivity(window.CreateActivity(activity), i);
}

// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
function _SequenceActivityMyActivityClick(isn, i)
{
	if (
		window.arUserParams
		&& BX.type.isArray(window.arUserParams.SNIPPETS)
		&& window.arUserParams.SNIPPETS[isn]
	)
	{
		_SequenceActivityCurClick.AddActivity(window.CreateActivity(window.arUserParams.SNIPPETS[isn]), i);
	}
}

const BizProcActivity = window.BizProcActivity;

export class SequenceActivity extends BizProcActivity
{
	#numberHeadRows: number = 0;

	childsContainer;
	lastDrop;
	h1id;
	h2id;

	#menuItems: [];

	constructor()
	{
		super();
		this.Type = 'SequenceActivity';
		this.childsContainer = null;

		this.#initDragNDropHandlers();

		// compatibility
		this.LineMouseOver = this.#onLineMouseOver;
		this.LineMouseOut = this.#onLineMouseOut;
		this.OnClick = (event) => {
			this.#onClick(event.target);
		};
		this.ondragging = this.#onDragging.bind(this);
		this.ondrop = this.#onDrop.bind(this);

		this.ActivityRemoveChild = this.RemoveChild;
		this.RemoveChild = this.#removeChild.bind(this);
		this.RemoveResources = this.#removeResources.bind(this);
		this.AddActivity = this.#addActivity.bind(this);
		this.CreateLine = this.#createArrow.bind(this);
		this.ActivityDraw = this.Draw;
		this.Draw = this.#draw.bind(this);
	}

	get iHead(): number
	{
		return this.#numberHeadRows;
	}

	set iHead(value: number)
	{
		if (Type.isInteger(value) && value >= 0)
		{
			this.#numberHeadRows = value;
		}
	}

	#onLineMouseOver()
	{
		Dom.style(this.parentNode, 'backgroundImage', 'url(/bitrix/images/bizproc/arr_over.gif)');
	}

	#onLineMouseOut()
	{
		Dom.style(this.parentNode, 'backgroundImage', 'url(/bitrix/images/bizproc/arr.gif)');
	}

	#initDragNDropHandlers()
	{
		this.lastDrop = false;
		this.h1id = window.DragNDrop.AddHandler('ondragging', this.#onDragging.bind(this));
		this.h2id = window.DragNDrop.AddHandler('ondrop', this.#onDrop.bind(this));
	}

	#onDragging(event, x, y)
	{
		if (this.childsContainer)
		{
			for (let i = 0; i <= this.childActivities.length; i++)
			{
				const arrow = this.childsContainer.rows[i * 2 + this.iHead].cells[0].childNodes[0];
				const position = Dom.getPosition(arrow);

				if (position.left < x && x < position.right && position.top < y && y < position.bottom)
				{
					arrow.onmouseover();
					this.lastDrop = arrow;

					return;
				}
			}

			if (this.lastDrop)
			{
				this.lastDrop.onmouseout();
				this.lastDrop = false;
			}
		}
	}

	#onDrop(x, y, event)
	{
		if (this.childsContainer && this.lastDrop)
		{
			if (window.DragNDrop.obj.parentActivity && (event.ctrlKey === false && event.metaKey === false))
			{
				this.#moveActivityToPosition(window.DragNDrop.obj, this.lastDrop);
			}
			else
			{
				this.#copyActivityToPosition(window.DragNDrop.obj, this.lastDrop);
			}
			this.lastDrop.onmouseout();
			this.lastDrop = false;
		}
	}

	#moveActivityToPosition(originalActivity, positionNode)
	{
		const parentActivity = originalActivity.parentActivity;
		const childPosition = parentActivity.childActivities.findIndex((child) => child.Name === originalActivity.Name);
		const position = positionNode.ind;

		if (
			parentActivity.Name !== this.Name
			|| (childPosition !== position && (childPosition + 1 !== position))
		)
		{
			if (this.#checkMovingCycle(originalActivity))
			{
				parentActivity.childsContainer.deleteRow(childPosition * 2 + 1 + parentActivity.iHead);
				parentActivity.childsContainer.deleteRow(childPosition * 2 + 1 + parentActivity.iHead);

				parentActivity.childActivities.splice(childPosition, 1);
				this.#refreshArrows(parentActivity);

				// after refresh arrows position changed
				this.#addActivity(originalActivity, positionNode.ind);
			}
			else
			{
				// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dialogs,no-alert
				alert(window.BPMESS.BPSA_ERROR_MOVE_1);
			}
		}
	}

	#checkMovingCycle(originalActivity): boolean
	{
		// eslint-disable-next-line unicorn/no-this-assignment
		let activity = this;
		while (activity)
		{
			if (originalActivity.Name === activity.Name)
			{
				return false;
			}

			activity = activity.parentActivity;
		}

		return true;
	}

	#copyActivityToPosition(originalActivity, positionNode)
	{
		const copiedActivity = window.CreateActivity(originalActivity);
		this.AddActivity(copiedActivity, positionNode.ind);
	}

	#removeChild(child)
	{
		const index = this.childActivities.indexOf(child);
		if (index >= 0)
		{
			this.ActivityRemoveChild(child);

			if (this.childsContainer)
			{
				this.childsContainer.deleteRow([index * 2 + 1 + this.iHead]);
				this.childsContainer.deleteRow([index * 2 + 1 + this.iHead]);

				this.#refreshArrows(this);
			}
		}
	}

	#refreshArrows(activity)
	{
		for (let i = 0; i <= activity.childActivities.length; i++)
		{
			// eslint-disable-next-line no-param-reassign
			activity.childsContainer.rows[i * 2 + activity.iHead].cells[0].childNodes[0].ind = i;
		}
	}

	#removeResources()
	{
		window.DragNDrop.RemoveHandler('ondragging', this.h1id);
		window.DragNDrop.RemoveHandler('ondrop', this.h2id);

		if (this.childsContainer && this.childsContainer.parentNode)
		{
			Dom.remove(this.childsContainer);
			this.childsContainer = null;
		}
	}

	#onClick(bindElement)
	{
		// eslint-disable-next-line unicorn/no-this-assignment
		_SequenceActivityCurClick = this;

		const menu = new Menu({
			bindElement,
			id: `all-worfklow-activity-${Text.getRandom()}`,
			minWidth: 190,
			autoHide: true,
			zIndexOptions: { alwaysOnTop: true },
			cacheable: false,
			items: this.#getAllActivitiesMenuItems(),
			subMenuOptions: {
				maxWidth: 850,
				maxHeight: 600,
			},
		});
		menu.show();
	}

	#getAllActivitiesMenuItems(): []
	{
		if (this.#menuItems)
		{
			this.#addMyActivitiesMenuItem();

			return this.#menuItems;
		}

		const items = this.#getGroupMenuItems();
		Object.entries(window.arAllActivities).forEach(([id, description]) => {
			if (
				(id !== 'setstateactivity' || window.rootActivity.Type !== this.Type)
				&& !description.EXCLUDED
				&& description.CATEGORY
			)
			{
				const groupId = description.CATEGORY.OWN_ID ?? description.CATEGORY.ID;
				if (items[groupId])
				{
					items[groupId].items.push(this.#getActivityMenuItem(id, description));

					if (Type.isArrayFilled(description.PRESETS))
					{
						description.PRESETS.forEach((preset) => {
							items[groupId].items.push(this.#getActivityMenuItem(id, description, preset));
						});
					}
				}
			}
		});

		if (items.rest && Reflection.getClass('BX.rest.Marketplace'))
		{
			items.rest.items.push({
				className: 'bizproc-designer-sequence-activity-menu-item-icon',
				html: this.#renderActivityMenuItemNode(
					window.BPMESS.BPSA_MARKETPLACE_ADD_TITLE_2,
					window.BPMESS.BPSA_MARKETPLACE_ADD_DESCR_3,
				),
				title: window.BPMESS.BPSA_MARKETPLACE_ADD_DESCR_3,
				dataset: {
					icon: '/bitrix/images/bizproc/act_icon_plus.png',
					name: window.BPMESS.BPSA_MARKETPLACE_ADD_TITLE_2,
				},
				onclick: (event, menuItem: MenuItem) => {
					BX.rest.Marketplace.open({}, 'auto_pb');
					menuItem.getMenuWindow().getParentMenuWindow().close();
				},
			});
		}

		this.#menuItems = Object.values(items).filter((item) => item.items.length > 0);

		this.#addMyActivitiesMenuItem();

		return this.#menuItems;
	}

	#getGroupMenuItems(): {}
	{
		const items = {};
		Object.entries(window.arAllActGroups).forEach(([id, title]) => {
			items[id] = {
				id,
				text: title,
				items: [],
				events: { 'SubMenu:onShow': this.#onGroupItemSubMenuShow },
			};
		});

		return items;
	}

	#addMyActivitiesMenuItem()
	{
		const index = this.#menuItems.findIndex((item) => item.id === 'MyActivity');
		if (index >= 0)
		{
			this.#menuItems.splice(index, 1);
		}

		if (window.arUserParams && Type.isArrayFilled(window.arUserParams.SNIPPETS))
		{
			const item = {
				id: 'MyActivity',
				text: window.BPMESS.BPSA_MY_ACTIVITIES_1,
				items: [],
				events: { 'SubMenu:onShow': this.#onGroupItemSubMenuShow },
			};

			window.arUserParams.SNIPPETS.forEach((snippet, index) => {
				item.items.push({
					className: 'bizproc-designer-sequence-activity-menu-item-icon',
					html: this.#renderActivityMenuItemNode(snippet.Properties.Title, ''),
					dataset: {
						icon: snippet.Icon ?? '/bitrix/images/bizproc/act_icon.gif',
						name: snippet.Properties.Title,
					},
					title: snippet.Properties.Title,
					onclick: (event, menuItem: MenuItem) => {
						_SequenceActivityMyActivityClick(
							index,
							menuItem.getMenuWindow().getParentMenuWindow().bindElement.ind,
						);
						menuItem.getMenuWindow().getParentMenuWindow().close()
					},
				});
			});

			this.#menuItems.push(item);
		}
	}

	#getActivityMenuItem(id: string, description: {}, preset: {}): {}
	{
		const descriptionText = preset && preset.DESCRIPTION ? preset.DESCRIPTION : description.DESCRIPTION;

		return {
			onclick: (event: PointerEvent, menuItem: MenuItem) => {
				_SequenceActivityClick(
					id,
					menuItem.getMenuWindow().getParentMenuWindow().bindElement.ind,
					preset ? preset.ID : null,
				);
				menuItem.getMenuWindow().getParentMenuWindow().close()
			},
			className: 'bizproc-designer-sequence-activity-menu-item-icon',
			html: this.#renderActivityMenuItemNode(preset ? preset.NAME : description.NAME, descriptionText),
			title: descriptionText,
			dataset: {
				icon: description.ICON ?? '/bitrix/images/bizproc/act_icon.gif',
				name: description.NAME,
			},
		};
	}

	#renderActivityMenuItemNode(title, description): HTMLDivElement
	{
		return Tag.render`
			<div style="line-height: normal; overflow: hidden; text-overflow: ellipsis;">
				<span><b>${Text.encode(title)}</b></span>
				<br/>
				<span>${Text.encode(description)}</span>
			</div>
		`;
	}

	#onGroupItemSubMenuShow(event: BaseEvent)
	{
		const groupItem: MenuItem = event.getTarget();
		if (groupItem.getSubMenu() && groupItem.getSubMenu().getMenuItems())
		{
			groupItem.getSubMenu().getMenuItems().forEach((item) => {
				const iconNode = item.layout.item.querySelector('.menu-popup-item-icon');
				Dom.append(
					Tag.render`<img src="${Text.encode(item.dataset.icon)}" alt="${Text.encode(item.dataset.name)}"/>`,
					iconNode,
				);
			});
		}
	}

	#addActivity(newActivity, position)
	{
		this.childActivities.splice(position, 0, newActivity);
		// eslint-disable-next-line no-param-reassign
		newActivity.parentActivity = this;
		newActivity.setCanBeActivated(this.getCanBeActivatedChild());

		const row = this.childsContainer.insertRow(position * 2 + 1 + this.iHead).insertCell(-1);
		Dom.attr(row, { align: 'center', vAlign: 'center' });

		newActivity.Draw(row);

		const row2 = this.childsContainer.insertRow(position * 2 + 2 + this.iHead).insertCell(-1);
		Dom.attr(row2, { align: 'center', vAlign: 'center' });

		this.#createArrow(position + 1);

		this.#refreshArrows(this);

		window.BPTemplateIsModified = true;
	}

	#createArrow(index)
	{
		Dom.style(
			this.childsContainer.rows[index * 2 + this.iHead].cells[0],
			{
				height: '40px',
				background: 'url(/bitrix/images/bizproc/arr.gif) no-repeat scroll 50% 50%',
			},
		);

		const image = BX.Dom.create('img', {
			attrs: {
				src: '/bitrix/images/1.gif',
				width: '28',
				height: '21',
			},
		});
		image.onmouseover = this.#onLineMouseOver;
		image.onmouseout = this.#onLineMouseOut;
		image.ind = index;
		Event.bind(image, 'click', this.#onClick.bind(this, image));
		Dom.append(image, this.childsContainer.rows[index * 2 + this.iHead].cells[0]);
	}

	#draw(wrapper)
	{
		const rows = Array.from(
			{ length: this.iHead + this.childActivities.length * 2 },
			() => Tag.render`
				<tr><td align="center" valign="center"></td></tr>
			`,
		);

		this.childsContainer = Tag.render`
			<table 
				class="seqactivitycontainer"
				id="${Text.encode(this.Name)}"
				width="100%"
				cellspacing="0"
				cellpadding="0"
				border="0"
			>
				<tbody>
					<tr><td align="center" valign="center"></td></tr>
					${rows}
				</tbody>
			</table>
		`;
		Dom.append(this.childsContainer, wrapper);
		this.#createArrow(0);
		this.childActivities.forEach((child, index) => {
			child.Draw(this.childsContainer.rows[index * 2 + 1 + this.iHead].cells[0]);
			this.#createArrow(Text.toInteger(index) + 1);
		});

		if (this.AfterSDraw)
		{
			this.AfterSDraw();
		}
	}
}
