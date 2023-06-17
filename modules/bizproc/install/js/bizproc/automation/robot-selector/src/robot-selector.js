import { Type, Loc, Runtime, Text, Event, Tag, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { MenuManager, PopupOptions } from 'main.popup';
import { Context } from 'bizproc.automation';
import { EntityCatalog, GroupData, ItemData } from 'ui.entity-catalog';
import { Settings } from 'bizproc.local-settings';

import './css/robot-selector.css';
import GroupIcon from './groups/group-icon';
import { Manager as GroupManager } from './groups/manager';
import { B24Robots as B24RobotsFilter } from './filters/b24-robots';
import { B24Triggers as B24TriggersFilter } from './filters/b24-triggers';
import { RecentGroup } from './groups/recent-group';

import { EmptyGroupStub } from './stubs/empty-group-stub';

export class RobotSelector extends EventEmitter
{
	static RECENT_GROUP_ID = 'recent';
	static DEFAULT_GROUP_NAME = 'other';

	static MAX_SIZE_OF_RECENT_GROUP = 10;

	#context: Context;
	#stageId: string;
	#catalog: ?EntityCatalog;
	#cache: Settings;

	#showNewGroups: boolean = false;

	recentGroupIdsSort: Map<string, number>;

	constructor(props: {
		context: Context,
		stageId: string,
		events: ?{[eventName: string]: Function},
	})
	{
		super();
		// TODO - fix namespace
		this.setEventNamespace('BX.Bizproc.Automation.RobotSelector');

		this.#context = props.context;
		this.#stageId = props.stageId;
		this.#cache = new Settings('robot-selector');
		this.recentGroupIdsSort = new Map(this.#getRecentEntitiesIds().map((id, index) => [id, index]));

		this.#context.set('recentAutomationEntities', new Map());
		this.#context.subsribeValueChanges('recentAutomationEntities', (event) => {
			const {value: newRecentGroupIds} = event.getData();
			this.recentGroupIdsSort = new Map(newRecentGroupIds.map((item, index) => [item.id, index]));
		})

		this.subscribeFromOptions(props.events);

		if (this.#context.document.getRawType()[0] === 'crm')
		{
			this.#showNewGroups = true;
		}
	}

	#getRecentEntitiesIds(): Array<string>
	{
		return this.#getRecentEntities().map(item => item.id);
	}

	#getRecentEntities(): Array<{entity: string, id: string}>
	{
		const recentEntitiesByDocumentType = this.#cache.remember('recentAutomationEntities', {});

		return recentEntitiesByDocumentType[this.#context.document.getRawType()[2]] ?? [];
	}

	setStageId(stageId: string)
	{
		if (Type.isStringFilled(stageId))
		{
			this.#stageId = stageId;
			this.#onStageIdChanged();
		}
	}

	show()
	{
		this.#getCatalog().show();
		this.emit('onAfterShow');
	}

	close()
	{
		this.#getCatalog().close();
	}

	isShown(): boolean
	{
		return this.#getCatalog().isShown();
	}

	#getCatalog(): EntityCatalog
	{
		if (Type.isNil(this.#catalog))
		{
			this.#catalog = new EntityCatalog({
				groups: this.#getDefaultRobotGroups(),
				items: this.#getItems(),
				recentGroupData:
					(new RecentGroup())
						.setSelected(this.#getRecentEntities().length > 0)
						.setCompare((lhsItem, rhsItem) => (
							this.recentGroupIdsSort.get(lhsItem.id) - this.recentGroupIdsSort.get(rhsItem.id)
						))
						.getData()
				,
				canDeselectGroups: false,
				customTitleBar: this.#getTitleBar(),
				slots: this.#getSlots(),
				showEmptyGroups: false,
				showRecentGroup: true,
				showSearch: true,
				filterOptions: this.#getFilterOptions(),
				popupOptions: this.#getPopupOptions(),
				customComponents: {EmptyGroupStub},
			});
		}

		return this.#catalog;
	}

	#getDefaultRobotGroups(): Array<Array<GroupData>>
	{
		return (
			this.#showNewGroups
				? [GroupManager.Instance.getAutomationGroupsData()]
				: [GroupManager.Instance.getAutomationCategoriesData()]
		);
	}

	#getItems(): Array<ItemData>
	{
		const getButtonHandler = (robotData) => {
			return (event) => {

				if (robotData.LOCKED)
				{
					if (top.BX.UI && top.BX.UI.InfoHelper && robotData.LOCKED.INFO_CODE)
					{
						top.BX.UI.InfoHelper.show(robotData.LOCKED.INFO_CODE);
					}

					return;
				}

				if (!event.getData().eventData.groupIds.includes(this.constructor.RECENT_GROUP_ID))
				{
					event.getData().eventData.groupIds.push(this.constructor.RECENT_GROUP_ID);
				}

				this.#addToRecentGroup({
					entity: 'robot',
					id: event.getData().eventData.id,
				});

				const originalEvent: PointerEvent = event.getData().originalEvent;
				this.emit('robotSelected', {
					robotData,
					originalEvent,
					item: event.getData().eventData,
					stageId: this.#stageId,
				});
			}
		};

		const availableRobots = this.#context.availableRobots;
		const recentRobotIds = this.#getRecentRobotIds();
		const triggers = this.#getTriggerItems();

		let items = [];
		const restRobots = [];
		let restTriggers = [];

		for (const robot of availableRobots)
		{
			const settings = Type.isPlainObject(robot['ROBOT_SETTINGS']) ? robot['ROBOT_SETTINGS'] : {};

			if (robot['EXCLUDED'])
			{
				if (Type.isPlainObject(settings['ASSOCIATED_TRIGGERS']))
				{
					items = items.concat(this.#getAssociatedTriggers(settings, triggers));
				}

				continue;
			}

			const isRestRobot = robot['CATEGORY'] && robot['CATEGORY']['ID'] && robot['CATEGORY']['ID'] === 'rest';

			const robotItem = this.#getRobotItemData(robot);
			robotItem.button = {
				action: getButtonHandler(robot),
				locked: !!robot.LOCKED,
			};

			const isRecentRobot = recentRobotIds.includes(robotItem.id);
			if (isRecentRobot && !robotItem.groupIds.includes(this.constructor.RECENT_GROUP_ID))
			{
				robotItem.groupIds.push(this.constructor.RECENT_GROUP_ID);
			}
			else if (!isRecentRobot)
			{
				robotItem.groupIds = robotItem.groupIds.filter(id => id !== RobotSelector.RECENT_GROUP_ID);
			}

			if (isRestRobot)
			{
				restRobots.push(robotItem);

				if (Type.isPlainObject(settings['ASSOCIATED_TRIGGERS']))
				{
					restTriggers = restTriggers.concat(this.#getAssociatedTriggers(settings, triggers));
				}
			}
			else
			{
				const useGroupKeys = this.#showNewGroups && settings['TITLE_GROUP'];
				const useCategoryKeys = !this.#showNewGroups && settings['TITLE_CATEGORY'];

				if (useGroupKeys || useCategoryKeys)
				{
					const titleGroupKey = useGroupKeys ? 'TITLE_GROUP' : 'TITLE_CATEGORY';
					const descriptionGroupKey = useGroupKeys ? 'DESCRIPTION_GROUP' : 'DESCRIPTION_CATEGORY';

					const groupIdsByTitle = {}
					Object.entries(settings[titleGroupKey]).forEach(([key, value]) => {
						if (!Type.isArray(groupIdsByTitle[value]))
						{
							groupIdsByTitle[value] = [];
						}
						groupIdsByTitle[value].push(key);
					});

					for (const groupTitle in groupIdsByTitle)
					{
						const firstGroupId = groupIdsByTitle[groupTitle][0];
						const groupIds = groupIdsByTitle[groupTitle];

						const item = Runtime.clone(robotItem);
						item.id = robot['CLASS'] + '@' + firstGroupId;
						item.title = groupTitle;
						item.groupIds = this.#getGroupIds({'GROUP': groupIds, 'CATEGORY': groupIds}, 'robot');
						item.description = settings[descriptionGroupKey] ? settings[descriptionGroupKey][firstGroupId] : robot['DESCRIPTION'];
						item.customData.contextGroup = firstGroupId;

						if (recentRobotIds.includes(item.id))
						{
							if (!item.groupIds.includes(this.constructor.RECENT_GROUP_ID))
							{
								item.groupIds.push(this.constructor.RECENT_GROUP_ID);
							}
						}

						items.push(item);
					}
				}
				else
				{
					items.push(robotItem);
				}

				if (Type.isPlainObject(settings['ASSOCIATED_TRIGGERS']))
				{
					items = items.concat(this.#getAssociatedTriggers(settings, triggers));
				}
			}
		}

		for (const key in triggers)
		{
			if (triggers[key].customData.owner === 'rest')
			{
				restTriggers.push(triggers[key]);

				continue;
			}

			items.push(triggers[key]);
		}

		items = items.concat(restRobots, restTriggers);

		if (this.#showNewGroups)
		{
			items.sort(this.#sortItems.bind(this));
		}
		else
		{
			items.sort(this.#oldSortItems.bind(this));
		}

		return items;
	}

	#getAssociatedTriggers(settings, triggers): Array
	{
		const associatedTriggers = [];

		if (Type.isPlainObject(settings['ASSOCIATED_TRIGGERS']))
		{
			for (const code in settings['ASSOCIATED_TRIGGERS'])
			{
				const trigger = triggers[code];

				if (trigger)
				{
					trigger.customData.sort = settings['SORT'] + settings['ASSOCIATED_TRIGGERS'][code];
					associatedTriggers.push(Runtime.clone(trigger));

					delete triggers[code];
				}
			}
		}

		return associatedTriggers;
	}

	#getGroupIds(settings, type): Array
	{
		if (this.#showNewGroups)
		{
			return Type.isArrayFilled(settings['GROUP']) ? settings['GROUP'] : [RobotSelector.DEFAULT_GROUP_NAME];
		}

		if (type === 'robot')
		{
			const categories = Type.isArray(settings['CATEGORY']) ? settings['CATEGORY'] : [settings['CATEGORY']];

			return categories.map(category => category + '_category');
		}

		return ['trigger_category'];
	}

	#getRobotItemData(robot): ItemData
	{
		const settings = Type.isPlainObject(robot['ROBOT_SETTINGS']) ? robot['ROBOT_SETTINGS'] : {};
		const title = settings['TITLE'] ?? robot['NAME'];

		const isRestRobot = robot['CATEGORY'] && robot['CATEGORY']['ID'] && robot['CATEGORY']['ID'] === 'rest';

		return {
			id: robot['CLASS'],
			title: title,
			groupIds: this.#getGroupIds(settings, 'robot'),
			description: robot['DESCRIPTION'],
			tags: [
				Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_ROBOTS'),
				Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_ROBOT'),
			],
			customData: {
				robotData: Runtime.clone(robot),
				contextGroup: null,
				type: 'robot',
				owner: isRestRobot ? 'rest' : 'bitrix24',
				sort: settings['SORT'] ?? null,
			},
		};
	}

	#getRecentRobotIds(): Array<string>
	{
		return (
			this
				.#getRecentEntities()
				.filter(item => item.entity === 'robot')
				.map(item => item.id)
		);
	}

	#getTriggerItems(): Object<string, ItemData>
	{
		const getButtonHandler = (triggerData) => {
			return (event) => {

				if (triggerData.LOCKED)
				{
					if (top.BX.UI && top.BX.UI.InfoHelper && triggerData.LOCKED.INFO_CODE)
					{
						top.BX.UI.InfoHelper.show(triggerData.LOCKED.INFO_CODE);
					}

					return;
				}

				if (!event.getData().eventData.groupIds.includes(this.constructor.RECENT_GROUP_ID))
				{
					event.getData().eventData.groupIds.push(this.constructor.RECENT_GROUP_ID);
				}

				this.#addToRecentGroup({
					entity: 'trigger',
					id: triggerData.CODE,
				});

				const originalEvent: PointerEvent = event.getData().originalEvent;
				this.emit('triggerSelected', {
					triggerData,
					originalEvent,
					item: event.getData().eventData,
					stageId: this.#stageId,
				});
			}
		};

		const availableTriggers = this.#context.availableTriggers;
		const recentTriggerIds = this.#getRecentTriggersIds();

		const triggerItems = {};
		for (const key in availableTriggers)
		{
			const trigger = availableTriggers[key];
			const isRecentTrigger = recentTriggerIds.includes(trigger.CODE);

			let groupIds = this.#getGroupIds(trigger, 'trigger');

			if (isRecentTrigger && !groupIds.includes(this.constructor.RECENT_GROUP_ID))
			{
				groupIds.push(RobotSelector.RECENT_GROUP_ID);
			}
			else if (!isRecentTrigger)
			{
				groupIds = groupIds.filter(id => id !== this.constructor.RECENT_GROUP_ID);
			}

			if (trigger.CODE !== 'APP')
			{
				triggerItems[trigger.CODE] ={
					groupIds,
					id: trigger.CODE,
					title: trigger.NAME,
					subtitle: Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEM_SUBTITLE_TRIGGER'),
					description: trigger['DESCRIPTION'],
					tags: [
						Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGERS'),
						Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGER'),
					],
					customData: {
						triggerData: {
							CODE: trigger.CODE,
						},
						type: 'trigger',
						owner: 'bitrix24',
						sort: null,
					},
					button: {
						action: getButtonHandler(trigger),
						locked: !!trigger.LOCKED,
					}
				};

				continue;
			}

			for (const i in trigger['APP_LIST'])
			{
				const item = trigger['APP_LIST'][i];
				const id = item['CODE'] + '@' + item['APP_ID'] + '@' + i;
				const itemName = '[' + item['APP_NAME'] + '] ' + item['NAME'];
				const groupIds = this.#getGroupIds(trigger, 'trigger');
				if (groupIds.includes(id))
				{
					groupIds.push(RobotSelector.RECENT_GROUP_ID);
				}

				triggerItems[id] = {
					id,
					title: itemName,
					subtitle: Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEM_SUBTITLE_TRIGGER'),
					groupIds,
					description: trigger['DESCRIPTION'],
					tags: [
						Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGERS'),
						Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TAGS_TRIGGER'),
					],
					customData: {
						triggerData: {
							NAME: itemName,
							CODE: trigger.CODE,
							APPLY_RULES: {
								APP_ID: item['APP_ID'],
								CODE: item['CODE']
							}
						},
						type: 'trigger',
						owner: 'rest',
						sort: null,
					},
					button: {
						action: getButtonHandler(item),
					},
				};
			}
		}

		return triggerItems;
	}

	#getRecentTriggersIds(): Array<string>
	{
		return (
			this
				.#getRecentEntities()
				.filter(item => item.entity === 'trigger')
				.map(item => item.id)
		);
	}

	#addToRecentGroup(newItem: {entity: string, id: string}): void
	{
		let recentGroupItems = this.#getRecentEntities().filter(item => item.id !== newItem.id);
		if (recentGroupItems.length >= RobotSelector.MAX_SIZE_OF_RECENT_GROUP)
		{
			recentGroupItems = recentGroupItems.slice(0, RobotSelector.MAX_SIZE_OF_RECENT_GROUP - 1);
		}

		recentGroupItems = [newItem, ...recentGroupItems];
		const entitiesByDocType = this.#cache.remember('recentAutomationEntities', {});
		entitiesByDocType[this.#context.document.getRawType()[2]] = recentGroupItems;

		this.#cache.set('recentAutomationEntities', entitiesByDocType);
		this.#context.set('recentAutomationEntities', recentGroupItems);

		this.#getCatalog().setItems(this.#getItems());
	}

	#getSlots(): Object
	{
		return {
			[EntityCatalog.SLOT_GROUP_LIST_HEADER]: this.#getGroupsHeader(),
			[EntityCatalog.SLOT_MAIN_CONTENT_HEADER]: this.#getItemsHeader(),
			[EntityCatalog.SLOT_MAIN_CONTENT_WELCOME_STUB]: this.#getItemsStub(),
			[EntityCatalog.SLOT_GROUP_LIST_FOOTER]: this.#getGroupsFooter(),
			[EntityCatalog.SLOT_MAIN_CONTENT_SEARCH_NOT_FOUND]: this.#getSearchNotFoundStub(),
			[EntityCatalog.SLOT_MAIN_CONTENT_EMPTY_GROUP_STUB]: `<EmptyGroupStub/>`,
			[EntityCatalog.SLOT_MAIN_CONTENT_FILTERS_STUB_TITLE]: Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_EMPTY_GROUP_STUB_TITLE'),
		};
	}

	#getGroupsHeader(): string
	{
		return `
			<div class="bizproc-creating-robot__head">
				<div class="bizproc-creating-robot__head_title">
					<div class="bizproc-creating-robot__head_name">${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUPS_HEADER_TITLE')}</div>
					<Hint text="${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUPS_HEADER_TITLE_HINT')}"/>
				</div>
			</div>
		`;
	}

	#getItemsHeader(): string
	{
		const helpFeedbackParams = {
			id: String(Math.random()),
			portalUri: 'https://bitrix24.team',
			forms: [
				{zones: ['ru'], id: 1922, lang: 'ru', sec: 'frsxzd'},
				{zones: ['kz'], id: 1923, lang: 'ru', sec: 'skbmjc'},
				{zones: ['by'], id: 1931, lang: 'ru', sec: 'om1f4c'},
				{zones: ['en'], id: 1937, lang: 'en', sec: 'yu3ljc'},
				{zones: ['la', 'co', 'mx'], id: 1947, lang: 'es', sec: 'wuezi9'},
				{zones: ['br'], id: 1948, lang: 'br', sec: 'j5gglp'},
				{zones: ['de'], id: 1946, lang: 'de', sec: '6tpoy4'},
			],
		};

		return `
			<div class="bizproc-creating-robot__head_title">
				<div class="bizproc-creating-robot__head_name">${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEMS_HEADER_TITLE_1')}</div>
				<Hint text="${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_ITEMS_HEADER_TITLE_HINT_1')}"/>
				<a class="bizproc-creating-robot__help-link" v-feedback="${Text.encode(JSON.stringify(helpFeedbackParams))}" href="javascipt:none">
					${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_HELP_SET_UP_AUTOMATION')}
				</a>
			</div>
		`;
	}

	#getItemsStub(): string
	{
		return `
			<div class="bizproc-creating-robot__content --help-block --select-grouping">
				<div class="bizproc-creating-robot__empty-content">
					<div class="bizproc-creating-robot__empty-content_icon">
						<img src="/bitrix/js/bizproc/automation/robot-selector/images/bizproc-creating-robot--select-grouping.svg" alt="">
					</div>
					<div class="bizproc-creating-robot__empty-content_text">
						${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_ITEM_LIST_STUB_TITLE')}
					</div>
				</div>
			</div>
		`;
	}

	#getSearchNotFoundStub(): string
	{
		const title = Text.encode(Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_SEARCH_NOT_FOUND_TITLE')) ?? ''
		let msg = Text.encode(Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_SEARCH_NOT_FOUND')) ?? '';

		const feedbackParams = {
			id: Math.random()+'',
			portalUri: 'https://product-feedback.bitrix24.com/',
			forms: [
				{zones: ['by', 'kz', 'ru'], id: 438, lang: 'ru', sec: 'odyyl1'},
				{zones: ['com.br'], id: 436, lang: 'br', sec: '8fb4et'},
				{zones: ['la', 'co', 'mx'], id: 434, lang: 'es', sec: 'ze9mqq'},
				{zones: ['de'], id: 432, lang: 'de', sec: 'm8isto'},
				{zones: ['en', 'eu', 'in', 'uk'], id: 430, lang: 'en', sec: 'etg2n4'},
			],
		};

		msg = msg
			.replace(
				'#A1#',
				`<a v-feedback="${Text.encode(JSON.stringify(feedbackParams))}" href="#feedback">`
			)
			.replace('#A2#', '</a>')
		;

		return `<b>${title}</b><br/>${msg}`;
	}

	#getGroupsFooter(): string
	{
		const url = '/marketplace/category/%category%/'.replace('%category%',  this.#context.get('marketplaceRobotCategory'));

		return `
			<a class="bizproc-creating-robot__menu-market" href="${url}">
				<div class='bizproc-creating-robot__menu_item-market'>
					<span class="bizproc-creating-robot__menu_item-icon-market">
						${GroupIcon.COMMERCE}
					</span>
					<span class="bizproc-creating-robot__menu_item-text-market">${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_GROUP_MARKETPLACE')}</span>
				</div>
			</a>
		`;
	}

	#getTitleBar(): Element
	{
		if (this.#context.document.statusList.length <= 1)
		{
			return Tag.render`
				<div>
					${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_POPUP_TITLE_1')}
				</div>
			`;
		}

		return Tag.render`
			<div>
				${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_POPUP_TITLE_1')}
			</div>
			<div class="bizproc-creating-robot__titlebar_subtitle">
				${Loc.getMessage('BIZPROC_AUTOMATION_ROBOT_SELECTOR_TITLEBAR_SUBTITLE')}
			</div>
			${this.#getTitleBarStageBlock() ?? ''}
		`;
	}

	#getTitleBarStageBlock(): ?Element
	{
		const statusList = this.#context.document.statusList;

		for (const key in statusList)
		{
			if (String(statusList[key]['STATUS_ID']) === String(this.#stageId))
			{
				const currentStageColor = this.constructor.#getColor(statusList[key]['COLOR']);
				const currentStageName = statusList[key]['NAME'] ?? statusList[key]['TITLE'];

				const stageBlock = Tag.render`
					<div class="bizproc-creating-robot__stage-block" data-role="bp-robot-selector-stage-block">
						<div class="bizproc-creating-robot__stage-block_title">
							${this.#createTitleBarStageBlockColor(currentStageColor, {width: 13, height: 12})}
							<div class="bizproc-creating-robot__stage-block_title-block">
								<span
									class="bizproc-creating-robot__stage-block_title-text"
									data-role="bp-robot-selector-stage-block-title"
								>
									${Text.encode(currentStageName)}
								</span>
							</div>
						</div>
						<div class="bizproc-creating-robot__stage-block_bg">
							<span class="bizproc-creating-robot__stage-block_bg-arrow"></span>
						</div>
					</div>
				`;

				Event.bind(stageBlock, 'click', this.#onTitleBarStageBlockClick.bind(this));

				return stageBlock;
			}
		}

		return null;
	}

	#onTitleBarStageBlockClick(event)
	{
		const statusList = this.#context.document.statusList;

		const stageMenuItems = [];
		for (const key in statusList)
		{
			stageMenuItems.push({
				html: `
					<div class="bizproc-creating-robot__stage-block_title-menu">
						${this.#createTitleBarStageBlockColor(statusList[key]['COLOR'], {width: 14, height: 12})}
						<span class="bizproc-creating-robot__stage-block_title-text-menu">
							${Text.encode(statusList[key]['NAME'] ?? statusList[key]['TITLE'])}
						</span>
						${
							(String(statusList[key]['STATUS_ID']) === String(this.#stageId))
								? `<div class="bizproc-creating-robot__stage-block_selected"></div>` 
								: ''
						}
					</div>
				`,
				onclick: (event, item) => {
					this.#stageId = statusList[key]['STATUS_ID'];
					this.#onStageIdChanged();

					item.getMenuWindow().close();
				}
			});
		}

		MenuManager.create({
			id: 'bizproc-automation-robot-selector-menu-stages',
			bindElement: event.target,
			items: stageMenuItems,
			minWidth: 228,
			autoHide: true,
			contentColor: 'white',
			draggable: false,
			cacheable: false,
		}).show();
	}

	#onStageIdChanged()
	{
		const status = this.#context.document.statusList.find((status) => {
			return (String(status.STATUS_ID) === String(this.#stageId))
		});

		const stageBlock =
			this.#getCatalog()
				.getPopup()
				.getTitleContainer()
				.querySelector('[data-role="bp-robot-selector-stage-block"]')
		;

		if (!stageBlock)
		{
			return;
		}

		const stageColorBlock = stageBlock.querySelector('[data-role="bp-robot-selector-stage-block-color-block"]');
		if (stageColorBlock)
		{
			Dom.replace(
				stageColorBlock,
				Tag.render`${this.#createTitleBarStageBlockColor(status['COLOR'])}`
			);
		}

		const stageBlockTitle = stageBlock.querySelector('[data-role="bp-robot-selector-stage-block-title"]');
		if (stageBlockTitle)
		{
			stageBlockTitle.innerText = status['NAME'] ?? status['TITLE'];
		}
	}

	#createTitleBarStageBlockColor(color: ?string, size={width: 13, height: 12}): string
	{
		color = this.constructor.#getColor(color);

		return `
			<svg 
				class="bizproc-creating-robot__stage-block_color"
				width="${size.width}"
				height="${size.height}"
				viewBox="0 0 13 12"
				fill="none"
				xmlns="http://www.w3.org/2000/svg"
				data-role="bp-robot-selector-stage-block-color-block"
			>
				<path 
					fill="${color}"
					d="M0 2.25C0 1.00736 1.02835 0 2.29689 0H8.68575C9.25708 0 9.80141 0.20818 10.2184 0.574156C10.465 0.790543 10.6254 1.08387 10.7737 1.37649L12.6727 5.12357C12.7468 5.26988 12.8412 5.40624 12.9071 5.55648C13.031 5.83933 13.031 6.16066 12.9071 6.44352C12.8412 6.59376 12.7468 6.73012 12.6727 6.87643L10.7737 10.6235C10.6254 10.9161 10.465 11.2095 10.2184 11.4258C9.80141 11.7918 9.25708 12 8.68575 12L2.29689 12C1.02835 12 0 10.9926 0 9.75V2.25Z"
				/>
			</svg>
		`;
	}

	#getFilterOptions(): Object
	{
		return {
			filterItems: [
				(new B24RobotsFilter()).getData(),
				(new B24TriggersFilter()).getData(),
			],
			multiple: false,
		};
	}

	#getPopupOptions(): PopupOptions
	{
		return {
			overlay: { backgroundColor: 'transparent' },
			events: {
				onPopupAfterClose: () => {
					this.emit('onAfterClose');
				}
			},
			zIndexOptions: {
				alwaysOnTop: false,
			}
		};
	}

	#sortItems(item1: ItemData, item2: ItemData): number
	{
		const sortItem1 = item1.customData.sort;
		const sortItem2 = item2.customData.sort;

		return (
			(sortItem1 && sortItem2)
				? sortItem1 - sortItem2
				: Text.toNumber(sortItem2) - Text.toNumber(sortItem1)
		);
	}

	#oldSortItems(item1: ItemData, item2: ItemData): number
	{
		const sortItem1 = item1.customData.robotData?.SORT;
		const sortItem2 = item2.customData.robotData?.SORT;

		return (
			(sortItem1 && sortItem2)
				? sortItem1 - sortItem2
				: Text.toNumber(sortItem2) - Text.toNumber(sortItem1)
		);
	}

	static #getColor(color: ?string): string
	{
		if (Type.isStringFilled(color))
		{
			return color.startsWith('#') ? color : '#' + color;
		}

		return '#ACF2FA';
	}
}