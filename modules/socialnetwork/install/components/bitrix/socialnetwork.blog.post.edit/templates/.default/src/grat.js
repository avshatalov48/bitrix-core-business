import { Type, Loc, Dom } from 'main.core';
import { Popup } from 'main.popup';
import { TagSelector } from 'ui.entity-selector';
import { EventEmitter } from "main.core.events";
import 'main.date';

export default class PostFormGratSelector extends EventEmitter
{
	static instance = null;

	popupWindow = null;
	sendEvent = true;
	gratsContentElement = null;
	itemSelectedImageItem = {};
	itemSelectedInput = {};
	gratsList = {};

	selector = null;

	config = {
		fields: {
			employeesValue: {
				name: 'GRAT_DEST_DATA',
			},
		},
	};

	static setInstance(instance)
	{
		PostFormGratSelector.instance = instance;
	}

	static getInstance()
	{
		return PostFormGratSelector.instance;
	}

	constructor(params)
	{
		super();
		this.init(params);
		PostFormGratSelector.setInstance(this);
	}

	init(params)
	{
		if(!params.name)
		{
			params.name = 'lm';
		}

		this.itemSelectedImageItem[params.name] = params.itemSelectedImageItem;
		this.itemSelectedInput[params.name] = params.itemSelectedInput;
		this.gratsList[params.name] = params.gratsList;

		this.itemSelectedImageItem[params.name].addEventListener('click', (e) => {
			this.openDialog(params.name);
			e.preventDefault();
		});

		this.createEntitySelector(params.entitySelectorParams);
	};

	openDialog(name): boolean
	{
		if(!name)
		{
			name = 'lm';
		}

		if (this.popupWindow != null)
		{
			this.popupWindow.close();
			return false;
		}

		const gratItems = [];
		for (let i = 0; i < this.gratsList[name].length; i++)
		{
			gratItems[gratItems.length] = Dom.create('span', {
				props: {
					className: `feed-add-grat-box ${this.gratsList[name][i].style}`
				},
				attrs: {
					'data-title': this.gratsList[name][i].title,
					'data-code': this.gratsList[name][i].code,
					'data-style': this.gratsList[name][i].style
				},
				events: {
					click: (e) => {
						const node = e.currentTarget;
						this.selectItem(name, node.getAttribute('data-code'), node.getAttribute('data-style'), node.getAttribute('data-title'));
						e.preventDefault();
					}
				}
			});
		}

		const gratRows = [];
		let rownum = 1;

		for (let i = 0; i < gratItems.length; i++)
		{
			if (i >= gratItems.length/2)
			{
				rownum = 2;
			}

			if (Type.isNil(gratRows[rownum]))
			{
				gratRows[rownum] = Dom.create('div', {
					props: {
						className: 'feed-add-grat-list-row'
					}
				});
			}

			gratRows[rownum].appendChild(gratItems[i]);
		}

		this.gratsContentElement = Dom.create('div', {
			children: [
				Dom.create('div', {
					props: {
						className: 'feed-add-grat-list-title'
					},
					html: Loc.getMessage('BLOG_GRAT_POPUP_TITLE')
				}),
				Dom.create('div', {
					props: {
						className: 'feed-add-grat-list'
					},
					children: gratRows
				})
			]
		});

		this.popupWindow = new Popup('BXSocNetGratSelector', document.getElementById('feed-add-post-grat-type-selected'), {
			autoHide: true,
			offsetLeft: 25,
			bindOptions: { forceBindPosition: true },
			closeByEsc: true,
			closeIcon: {
				top: '5px',
				right: '10px'
			},
			events : {
				onPopupClose: () => {
					this.popupWindow.destroy();
				},
				onPopupDestroy: () => {
					this.popupWindow = null;
				}
			},
			content: this.gratsContentElement,
			angle: {
				position: 'bottom',
				offset: 20
			},
			lightShadow: true
		});
		this.popupWindow.setAngle({});
		this.popupWindow.show();

		return true;
	};

	selectItem(name, code, style, title)
	{
		const gratSpan = this.itemSelectedImageItem[name].querySelector('span');
		if (gratSpan)
		{
			gratSpan.className = `feed-add-grat-box ${style}`;
		}

		this.itemSelectedImageItem[name].title = title;
		this.itemSelectedInput[name].value = code;
		this.popupWindow.close();
	};

	createEntitySelector(params)
	{
		this.selector = new TagSelector({

			id: params.id,
			dialogOptions: {
				id: params.id,
				context: 'GRATITUDE',
				preselectedItems: (Type.isArray(params.preselectedItems) ? params.preselectedItems : []),
				events: {
					'Item:onSelect': (event) => {
						this.recalcValue(event.getTarget().getSelectedItems(), params.inputNodeId);
					},
					'Item:onDeselect': (event) => {
						this.recalcValue(event.getTarget().getSelectedItems(), params.inputNodeId);
					},
				},
				entities: [
					{
						id: 'user',
						options: {
							emailUsers: false,
							inviteEmployeeLink: false,
							intranetUsersOnly: true,
						}
					},
					{
						id: 'department',
						options: {
							selectMode: 'usersOnly'
						}
					}
				]
			},
			addButtonCaption: Loc.getMessage('BLOG_GRATMEDAL_1'),
			addButtonCaptionMore: Loc.getMessage('BLOG_GRATMEDAL_1')
		});

		this.selector.renderTo(document.getElementById(params.tagNodeId));
		this.selector.subscribe('onContainerClick', () => {
			this.emit('Selector::onContainerClick');
		});
	};

	recalcValue(selectedItems, inputNodeId): void
	{
		if (
			!Type.isArray(selectedItems)
			|| !document.getElementById(inputNodeId)
		)
		{
			return;
		}

		const result = [];

		selectedItems.forEach((item) => {
			result.push([ item.entityId, item.id ]);
		});

		document.getElementById(inputNodeId).value = JSON.stringify(result);
	};
}