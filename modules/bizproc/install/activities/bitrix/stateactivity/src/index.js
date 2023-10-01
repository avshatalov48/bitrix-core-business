import { Tag, Text, Dom, Event, Runtime, Reflection } from 'main.core';
import { Menu, MenuItem, MenuItemOptions } from 'main.popup';
import { Button } from 'ui.buttons';

import './css/style.css';

export class StateActivity extends window.BizProcActivity
{
	lastDrop = false;
	main: ?HTMLTableElement = null;
	commandTable: ?HTMLTableElement = null;
	h1id;
	h2id;
	menu;

	#sequenceHeader: ?HTMLDivElement = null;
	#sequenceContent: ?HTMLDivElement = null;
	#sequenceFooter: ?HTMLDivElement = null;

	constructor()
	{
		super();
		this.Type = 'StateActivity';

		this.Draw = this.#draw.bind(this);
		this.OnRemoveClick = this.#onRemoveClick.bind(this);
		this.RemoveResources = this.#removeResources.bind(this);

		this.InitStateActivity = this.Init;
		this.Init = this.#init.bind(this);

		// region compatibility
		this.ondragging = this.#onDragging.bind(this);
		this.ondrop = this.#onDrop.bind(this);
		this.reDraw = this.#reDraw.bind(this);
		this.remove = (event: PointerEvent) => {
			const target = event.target;
			const node = target.parentNode.parentNode.parentNode.parentNode.parentNode;
			const id = node.id;
			// eslint-disable-next-line @bitrix24/bitrix24-rules/no-native-dom-methods
			this.#removeChildActivity(node, id);
		};

		this.settings = (event: PointerEvent) => {
			const target = event.target;
			const id = target.parentNode.parentNode.parentNode.parentNode.parentNode.id;
			this.#openChildSetting(id);
		};

		this.clickrow = (event: PointerEvent) => {
			const target = event.target;
			const id = target.parentNode.parentNode.parentNode.parentNode.parentNode.id;
			this.#onClickChildRow(id);
		};
		this.HideRows = this.#hideRows.bind(this);
		this.SequentialShow = this.#showSequence.bind(this);
		this.SequentialHide = this.#hideSequence.bind(this);
		this.AddInitialize = this.#addInitializeChild.bind(this);
		this.AddCommand = this.#addCommandChild.bind(this);
		this.AddDelayActivity = this.#addDelayChild.bind(this);
		this.AddFinilize = this.#addFinalizeChild.bind(this);
		this.ShowAddMenu = (event: PointerEvent) => {
			// eslint-disable-next-line no-undef
			this.menu = new PopupMenu('state_float_menu');
			this.menu.create(2000);

			const target = event.target;
			this.#showAddChildMenu(target);
		};
		// endregion
	}

	#init(activityInfo)
	{
		this.InitStateActivity(activityInfo);
		this.childActivities.forEach((child) => {
			if (child.Type === 'EventDrivenActivity')
			{
				const child0 = child.childActivities[0];
				child.setActivated(child0.Activated);
				child0.setCanBeActivated(child.canBeActivated);
			}
		});
	}

	#draw(wrapper)
	{
		this.#initDragNDropHandlers();

		this.main = Tag.render`
			<table class="bizproc-designer-state-activity-table" cellpadding="0" cellspacing="0">
				<tbody>
					<tr id="${Text.encode(this.Name)}">
						<td style="height: 24px; white-space: nowrap;">
							${this.#renderTitle()}
						</td>
					</tr>
					<tr>
						<td>
							${this.#renderContent()}
						</td>
					</tr>
				</tbody>
			</table>
		`;
		Dom.append(this.main, wrapper);
	}

	#reDraw()
	{
		const parentNode = this.main.parentNode;
		Dom.remove(this.main);
		this.main = null;
		this.commandTable = null;
		this.#draw(parentNode);
	}

	#onRemoveClick()
	{
		this.parentActivity.RemoveChild(this);
	}

	#removeResources()
	{
		window.DragNDrop.RemoveHandler('ondragging', this.h1id);
		window.DragNDrop.RemoveHandler('ondrop', this.h2id);
		Dom.remove(this.main);

		this.h1id = null;
		this.h2id = null;
		this.main = null;
		this.commandTable = null;
	}

	#initDragNDropHandlers()
	{
		this.lastDrop = false;
		if (!this.h1id)
		{
			this.h1id = window.DragNDrop.AddHandler('ondragging', this.#onDragging.bind(this));
			this.h2id = window.DragNDrop.AddHandler('ondrop', this.#onDrop.bind(this));
		}
	}

	#onDragging(event, X, Y)
	{
		const arrow = this.main;
		const position = Dom.getPosition(arrow);

		if (position.left < X && X < position.right && position.top < Y && Y < position.bottom)
		{
			this.lastDrop = arrow;
			Dom.style(arrow, 'opacity', '.25');

			return;
		}

		if (this.lastDrop)
		{
			Dom.style(arrow, 'opacity', null);
			this.lastDrop = false;
		}
	}

	#onDrop()
	{
		if (this.lastDrop)
		{
			Dom.style(this.lastDrop, 'opacity', null);
			this.lastDrop = false;

			if (this !== window.DragNDrop.obj && this.parentActivity.ReplaceChild)
			{
				this.parentActivity.ReplaceChild(this, window.DragNDrop.obj);
			}
		}
	}

	#renderTitle(): HTMLTableElement
	{
		const { root, title, setting, remove } = Tag.render`
			<table 
				class="bizproc-designer-state-activity-title-table${this.Activated === 'N' ? ' --deactivated' : ''}"
				cellpadding="0"
				cellspacing="0"
			>
				<tbody>
					<tr>
						<td ref="title">
							<div
								class="bizproc-designer-state-activity-title"
								title="${Text.encode(this.Properties.Title)}"
							><b>${Text.encode(this.Properties.Title)}</b></div>
						</td>
						<td ref='setting' style="cursor: pointer;">
							<div class="ui-icon-set --settings-4 bizproc-designer-state-activity-title-icon"></div>
						</td>
						<td ref='remove' style="cursor: pointer;">
							<div class="ui-icon-set --cross-60 bizproc-designer-state-activity-title-icon"></div>
						</td>
					</tr>
				</tbody>
			</table>
		`;
		Event.bind(title, 'mousedown', (event) => {
			const draggedDiv = window.DragNDrop.StartDrag(event, this);
			draggedDiv.innerHTML = this.main.innerHTML;
			Dom.style(draggedDiv, 'width', `${this.main.offsetWidth}px`);
		});
		Event.bind(setting, 'click', this.OnSettingsClick);
		Event.bind(remove, 'click', this.#onRemoveClick.bind(this));

		return root;
	}

	#renderContent(): HTMLTableElement
	{
		const { root, add } = Tag.render`
			<table 
				class="bizproc-designer-state-activity-children-table${this.Activated === 'N' ? ' --deactivated' : ''}"
				cellpadding="4"
				cellspacing="0"
			>
				<tbody>
					<tr>
						<td style="font-size: 12px; text-align: left; vertical-align: center">
							<a
								ref="add"
								href="javascript:void(0)"
								style="text-decoration: none"
							>
								<span>${Text.encode(window.BPMESS.STATEACT_ADD)}</span>
								<div 
									class="ui-icon-set --chevron-down"
									style="--ui-icon-set__icon-color: #2067b0; --ui-icon-set__icon-size: 10px"
								></div>
							</a>
						</td>
					</tr>
					${this.#renderChildren()}
				</tbody>
			</table>
		`;
		Event.bind(add, 'click', this.#showAddChildMenu.bind(this, add));
		this.commandTable = root;

		return root;
	}

	#renderChildren(): []
	{
		if (this.childActivities.length <= 0)
		{
			return [];
		}

		const nodes = [];
		this.childActivities.forEach((child) => {
			let childTitle = child.Properties.Title;
			let icon = child.Type === 'StateFinalizationActivity' ? 'fin' : 'init';
			let activatedClass = (!child.canBeActivated || child.Activated === 'N') ? ' --deactivated' : '';

			if (child.Type === 'EventDrivenActivity')
			{
				const child0 = child.childActivities[0];
				childTitle = child0.Properties.Title;
				icon = child0.Type === 'DelayActivity' ? 'delay' : 'cmd';
				activatedClass = (!child0.canBeActivated || child0.Activated === 'N') ? ' --deactivated' : '';
			}

			const { iconCode, iconSize, iconColor } = this.constructor.#resolveIcon(icon);
			const { root, title, setting, remove } = Tag.render`
				<tr id="${Text.encode(child.Name)}">
					<td class="bizproc-designer-state-activity-child${activatedClass}">
						<table style="font-size: 12px; width: 100%">
							<tbody>
								<tr>
									<td style="width: 17px">
										<div
											class="ui-icon-set --${iconCode}"
											style="
												--ui-icon-set__icon-size: ${iconSize};
												--ui-icon-set__icon-color: ${iconColor}
											"
										></div>
									</td>
									<td ref="title" title="${Text.encode(window.BPMESS.STATEACT_EDITBP)}">
										${Text.encode(childTitle)}
									</td>
									<td 
										ref="setting" 
										title="${Text.encode(window.BPMESS.STATEACT_SETT)}"
										style="width: 14px"
									>
										<div 
											class="ui-icon-set --settings-4 bizproc-designer-state-activity-child-icon"
										></div>
									</td>
									<td
										ref="remove"
										title="${Text.encode(window.BPMESS.STATEACT_DEL)}"
										style="width: 14px"
									>
										<div 
											class="ui-icon-set --cross-60 bizproc-designer-state-activity-child-icon"
										></div>
									</td>
								</tr>
							</tbody>
						</table>
					</td>
				</tr>
			`;
			Event.bind(title, 'click', this.#onClickChildRow.bind(this, child.Name));
			Event.bind(setting, 'click', this.#openChildSetting.bind(this, child.Name));
			Event.bind(remove, 'click', this.#removeChildActivity.bind(this, root, child.Name));

			nodes.push(root);
		});

		return nodes;
	}

	static #resolveIcon(icon: string): {iconCode: string, iconSize: string, iconColor: string}
	{
		if (icon === 'delay')
		{
			return {
				iconCode: 'hourglass-sandglass',
				iconSize: '17px',
				iconColor: 'rgb(42, 177, 28)', // 'rgb(123, 205, 116)',
			};
		}

		if (icon === 'cmd')
		{
			return {
				iconCode: 'forward',
				iconSize: '17px',
				iconColor: 'rgb(176, 26, 109)',
			};
		}

		if (icon === 'fin')
		{
			return {
				iconCode: 'statefin',
				iconSize: '12px',
				iconColor: 'none',
			};
		}

		if (icon === 'init')
		{
			return {
				iconCode: 'stateinit',
				iconSize: '12px',
				iconColor: '#1a92b7',
			};
		}

		return {};
	}

	#removeChildActivity(childNode: HTMLTableRowElement, childId: string)
	{
		const child = this.findChildById(childId);
		if (child)
		{
			Dom.remove(childNode);
			this.RemoveChild(child);
			this.parentActivity.DrawLines();
		}
	}

	#openChildSetting(childId: string)
	{
		let child = this.findChildById(childId);
		if (child)
		{
			if (child.Type === 'EventDrivenActivity')
			{
				child = child.childActivities[0];
			}
			child.Settings();
		}
	}

	#onClickChildRow(childId: string)
	{
		const child = this.findChildById(childId);
		if (child)
		{
			this.#showSequence(child);
		}
	}

	#showSequence(child)
	{
		// eslint-disable-next-line no-underscore-dangle,@bitrix24/bitrix24-rules/no-pseudo-private
		window.rootActivity._redrawObject = child;
		Dom.style(this.parentActivity.Table, 'display', 'none');

		this.#hideRows();
		this.#drawSequenceHeader(child);
		this.#drawSequenceContent(child);
		this.#drawSequenceFooter();

		if (document.getElementById('bizprocsavebuttons'))
		{
			Dom.style(document.getElementById('bizprocsavebuttons'), 'display', 'none');
		}
		scroll(0, 0);
	}

	#hideRows()
	{
		// eslint-disable-next-line no-underscore-dangle
		for (let i = 0; i < this.parentActivity.__l.length; i++)
		{
			for (let j = 0; j < 5; j++)
			{
				// eslint-disable-next-line no-underscore-dangle
				Dom.style(this.parentActivity.__l[i][j], 'display', 'none');
			}
		}
	}

	#drawSequenceHeader(child)
	{
		const title = (
			child.Type === 'EventDrivenActivity'
				? child.childActivities[0].Properties.Title
				: child.Properties.Title
		);
		const { root, link } = Tag.render`
			<div style="font-size: 12px">
				<a ref="link" href="javascript:void(0)">${Text.encode(this.Properties.Title)}</a>
				<span> - ${Text.encode(title)}</span>
			</div>
		`;
		Event.bind(link, 'click', this.#hideSequence.bind(this));

		this.#sequenceHeader = root;
		Dom.append(this.#sequenceHeader, this.parentActivity.Table.parentNode);
	}

	#drawSequenceContent(child)
	{
		this.#sequenceContent = Tag.render`<div></div>`;
		Dom.append(this.#sequenceContent, this.parentActivity.Table.parentNode);

		child.Draw(this.#sequenceContent);
	}

	#drawSequenceFooter()
	{
		const backButton = new Button({
			text: window.BPMESS.STATEACT_BACK_1,
			size: Button.Size.EXTRA_SMALL,
			color: Button.Color.LIGHT_BORDER,
			noCaps: true,
			onclick: this.#hideSequence.bind(this),
		});
		this.#sequenceFooter = Tag.render`<div>${backButton.render()}</div>`;
		Dom.style(backButton.getContainer(), 'margin', '15px');
		Dom.append(this.#sequenceFooter, this.parentActivity.Table.parentNode);
	}

	#hideSequence()
	{
		Dom.style(this.parentActivity.Table, 'display', 'table');

		Dom.remove(this.#sequenceHeader);
		Dom.remove(this.#sequenceContent);
		Dom.remove(this.#sequenceFooter);

		this.#sequenceHeader = null;
		this.#sequenceContent = null;
		this.#sequenceFooter = null;

		if (document.getElementById('bizprocsavebuttons'))
		{
			Dom.style(document.getElementById('bizprocsavebuttons'), 'display', 'block');
		}

		// eslint-disable-next-line no-underscore-dangle,@bitrix24/bitrix24-rules/no-pseudo-private
		window.rootActivity._redrawObject = null;
		window.arWorkflowTemplate = window.rootActivity.Serialize();
		window.ReDraw();
	}

	#showAddChildMenu(bindElement)
	{
		const showMenuAction = () => {
			(new Menu({
				bindElement,
				id: `state_float_menu-${Text.getRandom()}`,
				minWidth: 277,
				autoHide: true,
				zIndexOptions: { alwaysOnTop: true },
				cacheable: false,
				items: this.#getChildMenuItems(),
			})).show();
		};

		if (!Reflection.getClass('BX.Main.Menu'))
		{
			Runtime.loadExtension('main.popup')
				.then(() => showMenuAction())
				.catch(() => {})
			;

			return;
		}

		showMenuAction();
	}

	#getChildMenuItems(): MenuItemOptions[]
	{
		const getItemHtml = (icon, text) => {
			const { iconCode, iconColor } = this.constructor.#resolveIcon(icon);

			return Tag.render`
				<div style="display: inline-flex; align-items: center">
					<span 
						class="ui-icon-set --${iconCode}"
						style="
							--ui-icon-set__icon-size: 17px;
							--ui-icon-set__icon-color: ${iconColor};
							margin-right: 5px;
						"
					></span>
					<span>${Text.encode(text)}</span>
				</div>
			`;
		};

		const items = [
			{
				id: '2',
				html: getItemHtml('cmd', window.BPMESS.STATEACT_MENU_COMMAND),
				onclick: (event, menuItem: MenuItem) => {
					menuItem.getMenuWindow().close();
					this.#addCommandChild();
					this.#reDraw();
				},
			},
			{
				id: '3',
				html: getItemHtml('delay', window.BPMESS.STATEACT_MENU_DELAY),
				onclick: (event, menuItem: MenuItem) => {
					menuItem.getMenuWindow().close();
					this.#addDelayChild();
					this.#reDraw();
				},
			},
		];

		let hasInitChild = false;
		let hasFinishChild = false;
		this.childActivities.forEach((child) => {
			if (child.Type === 'StateInitializationActivity')
			{
				hasInitChild = true;
			}

			if (child.Type === 'StateFinalizationActivity')
			{
				hasFinishChild = true;
			}
		});

		if (!hasInitChild)
		{
			items.push({
				id: '1',
				html: getItemHtml('init', window.BPMESS.STATEACT_MENU_INIT_1),
				onclick: (event, menuItem: MenuItem) => {
					menuItem.getMenuWindow().close();
					this.#addInitializeChild();
					this.#reDraw();
				},
			});
		}

		if (!hasFinishChild)
		{
			items.push({
				id: '5',
				html: getItemHtml('fin', window.BPMESS.STATEACT_MENU_FIN_1),
				onclick: (event, menuItem: MenuItem) => {
					menuItem.getMenuWindow().close();
					this.#addFinalizeChild();
					this.#reDraw();
				},
			});
		}

		return items;
	}

	#addInitializeChild()
	{
		const row = this.commandTable.insertRow(1);
		const cell = row.insertCell(-1);
		cell.innerHTML = '';

		const activity = window.CreateActivity('StateInitializationActivity');
		this.childActivities.push(activity);
		activity.parentActivity = this;
		activity.setCanBeActivated(this.getCanBeActivatedChild());

		this.#showSequence(activity);
	}

	#addCommandChild()
	{
		const eventDrivenActivity = window.CreateActivity('EventDrivenActivity');
		const handleExternalEventActivity = window.CreateActivity('HandleExternalEventActivity');

		eventDrivenActivity.childActivities.push(handleExternalEventActivity);
		handleExternalEventActivity.parentActivity = eventDrivenActivity;

		const row = this.commandTable.insertRow(1);
		const cell = row.insertCell(-1);
		cell.innerHTML = '';

		this.childActivities.push(eventDrivenActivity);
		eventDrivenActivity.parentActivity = this;
		eventDrivenActivity.setCanBeActivated(this.getCanBeActivatedChild());

		handleExternalEventActivity.Settings();
	}

	#addDelayChild()
	{
		const eventDrivenActivity = window.CreateActivity('EventDrivenActivity');
		const delayActivity = window.CreateActivity('DelayActivity');

		eventDrivenActivity.childActivities.push(delayActivity);
		delayActivity.parentActivity = eventDrivenActivity;

		const row = this.commandTable.insertRow(1);
		const cell = row.insertCell(-1);
		cell.innerHTML = '';

		this.childActivities.push(eventDrivenActivity);
		eventDrivenActivity.parentActivity = this;
		eventDrivenActivity.setCanBeActivated(this.getCanBeActivatedChild());

		delayActivity.Settings();
	}

	#addFinalizeChild()
	{
		const row = this.commandTable.insertRow(1);
		const cell = row.insertCell(-1);
		cell.innerHTML = '';

		const activity = window.CreateActivity('StateFinalizationActivity');
		this.childActivities.push(activity);
		activity.parentActivity = this;
		activity.setCanBeActivated(this.getCanBeActivatedChild());

		this.#showSequence(activity);
	}
}

// eslint-disable-next-line @bitrix24/bitrix24-rules/no-pseudo-private,no-underscore-dangle
window.__StateActivityAdd = function(type, id)
{
	const activity = window.rootActivity.childActivities.find((act) => act.Name === id);
	if (activity)
	{
		switch (type)
		{
			case 'init':
				activity.AddInitialize();
				break;
			case 'command':
				activity.AddCommand();
				break;
			case 'delay':
				activity.AddDelayActivity();
				break;
			case 'finish':
				activity.AddFinilize();
				break;
			default:
				// no default
		}

		if (BX.Type.isFunction(activity.reDraw))
		{
			activity.reDraw();
		}
	}
};
