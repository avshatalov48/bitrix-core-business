import { Dom, Loc, Runtime, Tag, Text, Type } from 'main.core';
import Automation from "../automation";
import {Menu, MenuItem, Popup} from "main.popup";
import {
	getGlobalContext,
	Template,
	Robot,
	Tracker,
	ViewMode,
	TriggerManager,
	HelpHint,
	WorkflowStatus,
	TrackingStatus,
} from 'bizproc.automation';
import {BaseEvent, EventEmitter} from "main.core.events";
import { Button, ButtonSize, ButtonColor, ButtonIcon} from 'ui.buttons';
import 'ui.buttons.icons';
import {EntitySelector} from "ui.entity-selector";
import "bp_field_type";
import 'ui.layout-form';
import 'ui.hint';
import {DebuggerState} from "../workflow/types";
import {MessageBox, MessageBoxButtons} from "ui.dialogs.messagebox";
import {Manager} from "bizproc.debugger";
import {Helper} from "../helper";
import {Loader} from "main.loader";

export default class AutomationMainView extends EventEmitter
{
	#debuggerInstance: Automation;

	#popupInstance;
	#loaded = false;
	#node;
	#triggerManager: ?TriggerManager;
	#template;
	#tracker;
	#tabs = ['doc', 'log'];

	#expandedMinWidth = 781;
	#expandedMinHeight = 612;

	#collapsedMinWidth = 465;
	#collapsedMinHeight = 187;

	#changingViewTimeout;

	#buttonPlay: Button;

	constructor(debuggerInstance: Automation)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Debugger.AutomationMainView');

		this.#debuggerInstance = debuggerInstance;

		debuggerInstance.subscribe('onDocumentStatusChanged', this.#onDocumentStatusChanged.bind(this));
		debuggerInstance.subscribe('onWorkflowEventsChanged', this.#onWorkflowEventsChanged.bind(this));
		debuggerInstance.subscribe('onWorkflowTrackAdded', this.#onWorkflowTrackAdded.bind(this));
		debuggerInstance.subscribe('onDocumentValuesUpdated', this.#onDocumentValuesUpdated.bind(this));
		debuggerInstance.subscribe('onWorkflowStatusChanged', this.#onWorkflowStatusChange.bind(this));
		debuggerInstance.subscribe('onAfterDocumentFixed', this.#onAfterDocumentFixed.bind(this));
	}

	get debugger(): Automation
	{
		return this.#debuggerInstance;
	}

	show()
	{
		if (this.#loaded)
		{
			this.#getPopup().show();
			return;
		}

		this.debugger.loadMainViewInfo().then(() => {
			this.#loaded = true;
			this.#getPopup().setContent(this.#getNode());
			this.#setDebuggerState(this.debugger.getState());

			this.#getPopup().show();
		});
	}

	showExpanded()
	{
		if (!this.#getPopup().isShown())
		{
			this.debugger.settings.set('popup-collapsed', false);
			this.show();

			return;
		}

		this.#handleCollapse();
	}

	showCollapsed()
	{
		this.debugger.settings.set('popup-collapsed', true);
		this.show();
	}

	close()
	{
		this.#getPopup().close();
	}

	destroy()
	{
		this.close();
		//TODO - cleanup
	}

	#getPopup(): Popup
	{
		if (!this.#popupInstance)
		{
			const collapsed = this.debugger.settings.get('popup-collapsed');
			const className = 'bizproc-debugger-automation__main-popup bizproc-debugger-automation__scope';

			this.#popupInstance = new Popup({
				className: className + (collapsed ? ' --collapse' : ''),
				titleBar: this.#getPopupTitleBar(),
				noAllPaddings: true,
				contentBackground: 'white',
				draggable: true,
				zIndexOptions: {
					alwaysOnTop: collapsed
				},
				width: this.#getPopupWidth(collapsed),
				height: this.#getPopupHeight(collapsed),
				events: {
					onResizeStart: ()=> {
						this.#popupInstance.setMinWidth(this.#expandedMinWidth);
						this.#popupInstance.setMinHeight(this.#expandedMinHeight);
					},
					onResizeEnd: ()=> {
						this.#popupInstance.setMinWidth(null);
						this.#popupInstance.setMinHeight(null);

						this.debugger.settings.set('popup-width', this.#popupInstance.getWidth());
						this.debugger.settings.set('popup-height', this.#popupInstance.getHeight());
					}
				}
			});

			this.#popupInstance.setResizeMode(!collapsed);
		}

		return this.#popupInstance;
	}

	#getPopupWidth(collapsed: boolean): number
	{
		if (collapsed)
		{
			return this.#collapsedMinWidth;
		}

		return Math.max(
			this.#expandedMinWidth,
			this.debugger.settings.get('popup-width') || 0
		);
	}

	#getPopupHeight(collapsed: boolean): number
	{
		if (collapsed)
		{
			return this.#collapsedMinHeight;
		}

		return Math.max(
			this.#expandedMinHeight,
			this.debugger.settings.get('popup-height') || 0
		);
	}

	#getPopupTitleBar(): {}
	{
		return {
			content: Tag.render`
				<div class="popup-window-titlebar-text bizproc-debugger-automation__titlebar">
					<div class="bizproc-debugger-automation__titlebar--move-icon"></div>
					${document.createTextNode(Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_POPUP_TITLE'))}
					<div 
						class="bizproc-debugger-automation__titlebar--button-collapse" 
						onclick="${this.#handleCollapse.bind(this)}"
					></div>
					<span 
						class=" popup-window-close-icon 
								popup-window-titlebar-close-icon 
								bizproc-debugger-automation__titlebar--button-close"
						onclick="${this.#handleClose.bind(this)}"
					></span>
				</div>
			`
		};
	}

	#handleCollapse()
	{
		const node = this.#getPopup().getPopupContainer();
		const collapsed = Dom.hasClass(node, '--collapse');

		this.debugger.settings.set('popup-collapsed', !collapsed);
		this.#getPopup().getZIndexComponent().setAlwaysOnTop(!collapsed);

		this.#getPopup().setResizeMode(collapsed);

		Dom.toggleClass(node, '--collapse');

		clearTimeout(this.#changingViewTimeout);
		Dom.addClass(node, '--changing-view');

		this.#getPopup().setWidth(this.#getPopupWidth(!collapsed));
		this.#getPopup().setHeight(this.#getPopupHeight(!collapsed));

		this.#changingViewTimeout = setTimeout(
			() => Dom.removeClass(node, '--changing-view'),
			500
		);
	}

	#handleClose()
	{
		Manager.Instance.askFinishSession(this.debugger.session).catch(() => {/*do nth*/});
	}

	#getNode(): Element
	{
		if (!this.#node)
		{
			this.#node = Tag.render`
				<div class="bizproc-debugger-automation__content">
					<div class="bizproc-debugger-automation-content-collapsed">
						${this.#renderCollapsedMode()}
					</div>
					<div class="bizproc-debugger-automation__content-expanded">
						${this.#renderExpandedMode()}
					</div>
				</div>
			`;

			HelpHint.bindAll(this.#node);
		}

		return this.#node;
	}

	#renderExpandedMode(): Element
	{
		const hasRobots = !this.debugger.isTemplateEmpty();

		const activeTab = this.debugger.settings.get('tab') === 'log' ? 'log' : 'doc';
		const tabDocClass = activeTab === 'doc' ? '--active' : '';
		const tabLogClass = activeTab === 'log' ? '--active' : '';

		const hasActiveDocument = this.debugger.session.isFixed();
		const tabNoDocumentClass = hasActiveDocument ? '' : '--empty --active';

		const fieldListNode = this.#getFieldListNode();
		const hasFields = fieldListNode.querySelector('[data-field-id]') !== null;

		return Tag.render`
				<div class="bizproc-debugger-automation__main">
					<div class="bizproc-debugger-automation__main-robots">
						<div class="bizproc-debugger-automation__main-robots--head">
							${this.#createStageNode()}
						</div>
						<div data-role="automation-content" class="bizproc-debugger-automation__main-robots--main-content">
							${this.#createTriggersHeaderNode() ?? ''}
							${this.#createTriggersNode() ?? ''}
							<div class="bizproc-debugger-automation__head">
								<div class="bizproc-debugger-automation__main--title">
									<div class="bizproc-debugger-automation__main--name">${Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ROBOTS_TITLE')} </div>
									<div class="ui-hint">
										<span class="ui-hint-icon" data-text="${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_ROBOT'))}"></span>
									</div>
								</div>
								<div data-role="no-template" class="bizproc-debugger-automation__main-hint ${hasRobots ? '' : '--active'}">
									<div class="bizproc-debugger-automation__main-hint--title">
										${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_ROBOTS_TITLE'))}
									</div>
									<div class="bizproc-debugger-automation__main-hint--text">
										${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_ROBOTS_SUBTITLE'))}
									</div>
									<a href="${this.debugger.getSettingsUrl()}" class="bizproc-debugger-automation__link">${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_AUTOMATION_SETTINGS'))}</a>
								</div>
							</div>
							${this.#createTemplateNode()}
						</div>
						${this.#createTemplateToolbar()}
					</div>
					<div class="bizproc-debugger-automation__main-fields ${hasActiveDocument ? '' : '--disabled'}">
						<div data-role="tabs-container" class="bizproc-debugger-automation__main-navigation --active-${activeTab}">
							<div class="bizproc-debugger-automation__tab-block">
								<span class="bizproc-debugger-automation__tab ${tabDocClass}" data-tab-item="doc" onclick="${this.#handleChangeTab.bind(this)}">
									${Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_DOCUMENT_TITLE')}
								</span>
								<div class="ui-hint">
									<span class="ui-hint-icon" data-text="${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_FIELD'))}"></span>
								</div>
							</div>
							<div class="bizproc-debugger-automation__tab-block">
								<span class="bizproc-debugger-automation__tab ${tabLogClass}" data-tab-item="log" onclick="${this.#handleChangeTab.bind(this)}">
									${Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_LOG_TITLE')}
								</span>
								<div class="ui-hint">
									<span class="ui-hint-icon" data-text="${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_LOG'))}"></span>
								</div>
							</div>
							
							<div data-tab-item="doc" class="bizproc-debugger-automation__tab-action ${tabDocClass}">
								${this.#getAddFieldNode()}
							</div>
							
							<div data-tab-item="log" class="bizproc-debugger-automation__tab-action ${tabLogClass}">
								<div class="bizproc-debugger-automation__action-btn --icon-search" style="display: none"></div>
								<div class="bizproc-debugger-automation__action-btn --icon-log" onclick="${() => {Manager.Instance.openSessionLog(this.debugger.sessionId);}}"></div>
								<div class="bizproc-debugger-automation__action-btn --icon-note" style="display: none"></div>
							</div>
						</div>
						
						<div data-tab-item="doc" data-role="tab-content-doc" class="bizproc-debugger-tab__content ${hasActiveDocument ? tabDocClass : ''} ${hasFields ? '' : '--empty'}">
							<div class="bizproc-debugger-tab__content--empty">
								${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_FIELD_TITLE'))}
							</div>
							<div class="bizproc-debugger-tab__content--not-empty">
								<div class="bizproc-debugger-tab__content-title">${Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_DOCUMENT_TITLE')}</div>							
								${fieldListNode}
							</div>
						</div>
						<div data-tab-item="log" class="bizproc-debugger-tab__content ${hasActiveDocument ? tabLogClass : ''} bizproc-debugger-automation-main-section-log">
							${this.debugger.getLogView().shouldScrollToBottom(true).shouldLoadPreviousLog(true).render()}
						</div>
						<div data-tab-item="no-document" class="bizproc-debugger-tab__content ${tabNoDocumentClass} bizproc-debugger-automation-main-section-disabled">
							<div class="bizproc-debugger-tab__content--empty">
								${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_FIXED_DOCUMENT'))}
							</div>
						</div>
					</div>
				</div>
			`;
	}

	#renderCollapsedMode(): Element
	{
		return Tag.render`
			<div class="bizproc-debugger-automation-menu__content-body">
				<div class="bizproc-debugger-automation-menu__content-body--logo"></div>
				<div class="bizproc-debugger-automation-menu__content-body--text">${Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_MENU_SUBTITLE')}</div>
			</div>
		`;
	}

	#getAddFieldNode()
	{
		return (new Button({
			size: ButtonSize.EXTRA_SMALL,
			color: ButtonColor.PRIMARY,
			round: true,
			noCaps: true,
			text: Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_ADD_FIELD'),
			onclick: this.#handleAddDocFieldMenu.bind(this)
		})).render();
	}

	#getFieldListNode()
	{
		const form = Tag.render`<div class="ui-form" data-role="doc-field-list">
			</div>`;
		const fields = this.debugger.settings.getSet('watch-fields');

		fields.forEach(value => {
			const node = this.#getFieldNode(value);
			if (node)
			{
				Dom.append(node, form);
			}
		});

		return form;
	}

	#handleChangeTab(event: Event)
	{
		const activeTabName = event.target.dataset.tabItem;
		const hiddenTabName = this.#tabs.filter((tabName) => tabName !== activeTabName)[0]

		const node = this.#getNode();
		const navigationNode = node.querySelector('[data-role="tabs-container"]');

		node.querySelectorAll([`[data-tab-item="${activeTabName}"]`]).forEach(
			(tab) => Dom.addClass(tab, '--active')
		);

		node.querySelectorAll([`[data-tab-item="${hiddenTabName}"]`]).forEach(
			(tab) => Dom.removeClass(tab, '--active')
		);

		Dom.addClass(navigationNode, `--active-${activeTabName}`);
		Dom.removeClass(navigationNode, `--active-${hiddenTabName}`);

		this.debugger.settings.set('tab', activeTabName);

		this.emit('onChangeTab', {tab: activeTabName});
	}

	#handleAddDocFieldMenu(button: Button, event: Event)
	{
		const documentFields = this.debugger.getDocumentFields();
		const selectedFields = this.debugger.settings.getSet('watch-fields');

		const fieldsDialog = new EntitySelector.Dialog({
			targetNode: event.target,
			width: 500,
			height: 300,
			multiple: true,
			dropdownMode: true,
			enableSearch: true,
			cacheable: false,
			items: documentFields
				.filter(field => field.Watchable === true)
				.map((field) => {
					return {
						title: field.Name,
						id: field.Id,
						customData: {field},
						entityId: 'bp',
						tabs: 'recents',
						selected: selectedFields.has(field.Id)
					}
				}),
			showAvatars: false,
			events: {
				'Item:onSelect': event => this.#handleAddField(event.getData().item),
				'Item:onDeselect': event => this.#handleRemoveField(event.getData().item.getId()),
			},
			compactView: true,
		});

		fieldsDialog.show();
	}

	#handleAddField(item)
	{
		const fields = this.debugger.settings.getSet('watch-fields');
		const field = item.getCustomData().get('field');

		if (fields.has(field.Id))
		{
			return;
		}

		const fieldNode = this.#getFieldNode(field);

		Dom.append(
			fieldNode,
			this.#getNode().querySelector('[data-role="doc-field-list"]')
		);

		fields.add(field.Id);
		this.debugger.settings.set('watch-fields', fields);
		this.#handleFieldListChange(fields);
	}

	#handleRemoveField(fieldId: string)
	{
		const fields = this.debugger.settings.getSet('watch-fields');

		if (!fields.has(fieldId))
		{
			return;
		}

		fields.delete(fieldId);
		this.debugger.settings.set('watch-fields', fields);
		Dom.remove(this.#getNode().querySelector(`[data-field-id="${fieldId}"]`));
		this.#handleFieldListChange(fields);
	}

	#handleFieldListChange(fields: Set)
	{
		const contentNode = this.#getNode().querySelector('[data-role="tab-content-doc"]');
		const hasFields = contentNode.querySelector('[data-field-id]') !== null;
		Dom[hasFields ? 'removeClass' : 'addClass'](contentNode, '--empty');
	}

	#getFieldNode(field: string | {}): ?Element
	{
		if (Type.isString(field))
		{
			field = this.debugger.getDocumentField(field);
		}

		if (!field || !field.Id)
		{
			return null;
		}

		const value = this.debugger.getDocumentValue(field.Id) || '';

		return Tag.render`
			<div class="ui-form-row" data-role="field-row" data-field-id="${Text.encode(field.Id)}">
				<div class="ui-form-label">
					<div class="ui-ctl-label-text">${Text.encode(field.Name)}</div>
				</div>
				<div class="ui-form-content">
					<div class="ui-ctl ui-ctl-textbox ui-ctl-w100 ui-ctl-ext-after-icon">
						<input type="text" readonly class="ui-ctl-element"
						 placeholder="${Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_EMPTY_VALUE')}"
						 data-role="field-value-${Text.encode(field.Id)}"
						 value="${Text.encode(value)}"
						 >
						 <a class="ui-ctl-after ui-ctl-icon-clear" onclick="${this.#handleRemoveField.bind(this, field.Id)}"></a>
					</div>
				</div>
			</div>
		`;
	}

	#createTriggersHeaderNode(): ?Element
	{
		const hasTriggers = this.debugger.templateTriggers.length > 0;
		const hasRobots = !this.debugger.isTemplateEmpty();

		if (!hasTriggers && hasRobots)
		{
			return null;
		}

		return Tag.render`
			<div data-role="triggers-header" class="bizproc-debugger-automation__head">
				<div class="bizproc-debugger-automation__main--title">
					<div class="bizproc-debugger-automation__main--name">${Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_TRIGGERS_TITLE')} </div>
					<div class="ui-hint">
						<span class="ui-hint-icon" data-text="${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_HELPTIP_TRIGGER'))}"></span>
					</div>
				</div>
				<div data-role="no-triggers" class="bizproc-debugger-automation__main-hint ${hasTriggers || hasRobots ? '' : '--active'}">
					<div class="bizproc-debugger-automation__main-hint--title">
						${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_TRIGGERS_TITLE'))}
					</div>
					<div class="bizproc-debugger-automation__main-hint--text">
						${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_NO_TRIGGERS_SUBTITLE'))}
					</div>
					<a href="${this.debugger.getSettingsUrl()}" class="bizproc-debugger-automation__link">${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_AUTOMATION_SETTINGS'))}</a>
				</div>
			</div>
		`;
	}

	#createTriggersNode(): ?Element
	{
		const documentStatus = this.debugger.getTemplate().DOCUMENT_STATUS;

		this.#tracker = new Tracker(getGlobalContext().document);
		const logs = {};
		logs[documentStatus] = this.debugger.track;
		this.#tracker.init(logs);
		getGlobalContext().tracker = this.#tracker;

		const triggers = this.debugger.templateTriggers;
		if (triggers.length === 0)
		{
			return null;
		}

		const node = Tag.render`
			<div data-role="triggers" class="bizproc-debugger__template">
				<div class="bizproc-automation-status-list-item" data-type="column-trigger">
					<div data-role="trigger-list" class="bizproc-automation-trigger-list" data-status-id="${documentStatus}"></div>
				</div>
			</div>
		`;

		this.#triggerManager = new TriggerManager(node);
		this.#triggerManager.init({TRIGGERS: triggers}, ViewMode.view());

		return node;
	}

	#createTemplateNode(): Element
	{
		const templateData = this.debugger.getTemplate();

		this.#tracker = new Tracker(getGlobalContext().document);
		const logs = {};
		logs[templateData.DOCUMENT_STATUS] = this.debugger.track;
		this.#tracker.init(logs);
		getGlobalContext().tracker = this.#tracker;

		const node = Tag.render`
			<div data-role="template" class="bizproc-debugger__template">
				<div data-role="automation-template" data-status-id="${templateData.DOCUMENT_STATUS}">
					<div data-role="robot-list" class="bizproc-automation-robot-list"></div>
				</div>
			</div>
		`;

		const template = new Template({
			constants: {},
			variables: {},
			templateContainerNode: node,
			delayMinLimitM: 0,
			// userOptions: this.userOptions,
		});

		template.init(templateData, ViewMode.view().intoRaw());
		this.#updateTemplate(template);
		this.#renderPausedRobots();

		return node;
	}

	#updateTemplate(newTemplate: Template): Template
	{
		if (!Type.isNil(this.#template))
		{
			this.#template.destroy();
		}
		this.#template = newTemplate;

		return this.#template;
	}

	#createTemplateToolbar(): Element
	{
		this.#buttonPlay = new Button({
			size: ButtonSize.EXTRA_SMALL,
			color: ButtonColor.PRIMARY,
			round: true,
			icon: ButtonIcon.START,
			onclick: this.#handleStartTemplate.bind(this)
		});

		const hasEvents = this.debugger.hasWorkflowEvents();

		const hasActiveDocument = this.debugger.session.isFixed();

		return Tag.render`
			<div class="bizproc-debugger-automation__toolbar ${hasActiveDocument ? '' : '--disabled'}">
			<div data-role="external-event-info" class="bizproc-debugger-automation__toolbar--info-waiting ${hasEvents ? '--active' : ''}">
				<div>
					${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_SKIP_WAITING_SUBTITLE'))}
				</div>
				<a onclick="${this.#handleEmulateExternalEvent.bind(this)}" class="bizproc-debugger-automation__link">
					${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_SKIP_WAITING_TITLE'))}
				</a>
			</div>
			<div class="bizproc-debugger-automation__toolbar--btn-block">
				${this.#buttonPlay.render()}
				<div class="bizproc-debugger-automation__toolbar--btn-text">
					${Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ACTION_START')}
				</div>
			</div>
			</div>
		`;
	}

	#handleStartTemplate(button: Button)
	{
		button.setWaiting(true);
		this.debugger.startDebugTemplate().then(() => {
			button.setWaiting(false);
			this.#setDebuggerState(this.debugger.getState());
		});
	}

	#handleEmulateExternalEvent(event: Event)
	{
		const infoNode = this.#getNode().querySelector('[data-role="external-event-info"]');
		Dom.removeClass(infoNode, '--active');
		this.debugger.emulateExternalEvent(event.target.dataset.sourceId);
	}

	#updateTracker(data: Array)
	{
		if (this.#tracker)
		{
			const logs = {};
			logs[this.debugger.getDocumentStatus()] = this.debugger.track;

			this.#tracker.reInit(logs);
			this.#template.reInit(null, ViewMode.view().intoRaw());

			this.#renderPausedRobots();
		}
	}

	#renderPausedRobots(): void
	{
		const pausedRobots = this.#template.robots.filter(robot => robot.getLogStatus() === TrackingStatus.RUNNING);

		pausedRobots.forEach((robot) => {
			const loader = robot.node.lastChild.lastChild;
			const clonedLoader = Runtime.clone(loader);
			HelpHint.bindToNode(clonedLoader);

			Dom.replace(
				loader,
				Tag.render`
					<div class="bizproc-debugger-automation-robot-info-container">
						${clonedLoader}
						<a 
							onclick="${this.#handleEmulateExternalEvent.bind(this)}"
							data-source-id="${robot.getId()}"
							class="bizproc-debugger-automation__link --inside-robot"
						>
							${Text.encode(Loc.getMessage('BIZPROC_JS_DEBUGGER_SKIP_WAITING_TITLE'))}
						</a>
					</div>
				`
			);
		});
	}

	#createStageNode(): Element
	{
		const color = this.#getDocumentStatusColor();
		const title = Text.encode(this.#getDocumentStatusTitle());

		return Tag.render`
			<div 
				class="bizproc-debugger-automation__status --robot-change ${Helper.getBgColorAdditionalClass(color)}"
				data-role="document-status"
				title="${title}"
				onclick="${this.#handleShowStages.bind(this)}"
			>
				<div class="bizproc-debugger-automation__status--title" data-role="document-status-title">
					${title}
				</div>
				<div class="bizproc-debugger-automation__status--bg" data-role="document-status-bg" style="background-color: ${color}; border-color: ${color};">
					<span class="bizproc-debugger-automation__status--bg-arrow"></span>
				</div>
			</div>
		`;
	}

	#handleShowStages(event: MouseEvent)
	{
		event.preventDefault();

		const statusList = this.debugger.getStatusList();

		const menu = new Menu({
			bindElement: event.target,
			items: statusList.map((stage) => {
				return {text: stage.NAME, statusId: stage['STATUS_ID'], onclick: this.#handleChangeStatus.bind(this)}
			}),
		});
		menu.show();
	}

	#handleChangeStatus(event, item: MenuItem)
	{
		item.getMenuWindow().destroy();
		this.debugger.setDocumentStatus(item.statusId);
	}

	#getDocumentStatusTitle(): string
	{
		const statusId = this.debugger.getDocumentStatus();
		const statusList = this.debugger.getStatusList();

		const status = statusList.find((stage) => stage['STATUS_ID'] === statusId);

		return status ? (status.NAME || status.TITLE) : '?'
	}

	#getDocumentStatusColor(): string
	{
		const statusId = this.debugger.getDocumentStatus();
		const statusList = this.debugger.getStatusList();

		const status = statusList.find((stage) => stage['STATUS_ID'] === statusId);

		return status ? status.COLOR : '#9DCF00';
	}

	#onDocumentStatusChanged()
	{
		if (!this.#getPopup().isShown())
		{
			return;
		}

		const automationContentNode = this.#getNode().querySelector('[data-role="automation-content"]');

		const loader = new Loader({
			target: automationContentNode
		});

		Dom.addClass(automationContentNode, '--loading');
		loader.show();

		this.debugger.loadMainViewInfo().then(() => {
			const statusTitleNode = this.#getNode().querySelector('[data-role="document-status-title"]');
			const statusTitle = this.#getDocumentStatusTitle();
			statusTitleNode.textContent = statusTitle;
			statusTitleNode.parentNode.setAttribute('title', statusTitle);

			const statusBgNode = this.#getNode().querySelector('[data-role="document-status-bg"]');
			const color = this.#getDocumentStatusColor();
			Dom.style(statusBgNode, {
				backgroundColor: color,
				borderColor: color,
			});

			const documentStatusNode = this.#getNode().querySelector('[data-role="document-status"]');
			Dom.removeClass(documentStatusNode,['--with-border', '--light-color']);
			Dom.addClass(documentStatusNode, Helper.getBgColorAdditionalClass(color));

			Dom.remove(this.#getNode().querySelector('[data-role="triggers-header"]'));
			Dom.remove(this.#getNode().querySelector('[data-role="triggers"]'));

			Dom.prepend(this.#createTriggersNode(), automationContentNode);
			const triggersHeaderNode = this.#createTriggersHeaderNode();
			if (triggersHeaderNode)
			{
				HelpHint.bindAll(triggersHeaderNode);
				Dom.prepend(triggersHeaderNode, automationContentNode);
			}

			const tplNode = this.#createTemplateNode();
			Dom.replace(this.#node.querySelector('[data-role="template"]'), tplNode);

			const hasTriggers = this.debugger.templateTriggers.length > 0;
			const hasRobots = !this.debugger.isTemplateEmpty()
			Dom[hasTriggers || hasRobots ? 'removeClass' : 'addClass'](this.#node.querySelector('[data-role="no-triggers"]'), '--active');
			Dom[hasRobots ? 'removeClass' :'addClass'](this.#node.querySelector('[data-role="no-template"]'), '--active');

			Dom.removeClass(automationContentNode, '--loading');
			loader.destroy();
		});
	}

	#onWorkflowEventsChanged(event: BaseEvent)
	{
		if (!this.#buttonPlay)
		{
			return;
		}

		const events = event.getData().events;
		const infoNode = this.#getNode().querySelector('[data-role="external-event-info"]');
		Dom[events.length ? 'addClass' : 'removeClass'](infoNode, '--active');
	}

	#onWorkflowTrackAdded(event: BaseEvent)
	{
		this.#updateTracker(this.debugger.track);
	}

	#onDocumentValuesUpdated(event: BaseEvent)
	{
		if (!this.#getPopup().isShown())
		{
			return;
		}

		const values = event.getData().values;
		const node = this.#getNode();

		Object.keys(values).forEach(key => {
			const valueNode = node.querySelector(`[data-role="field-value-${key}"]`);
			if (valueNode)
			{
				valueNode.value = values[key] || '';
			}
		});
	}

	#onWorkflowStatusChange(event: BaseEvent)
	{
		const status = event.getData().status;
		const workflowId = event.getData().workflowId;

		if ([WorkflowStatus.COMPLETED, WorkflowStatus.TERMINATED].includes(status))
		{
			this.debugger.track.forEach((track) => {
				if (track['WORKFLOW_ID'] === workflowId)
				{
					track['WORKFLOW_STATUS'] = WorkflowStatus.COMPLETED;
				}
			});

			this.#updateTracker(this.debugger.track);
		}
	}

	#onAfterDocumentFixed()
	{
		const popupContainer = this.#getPopup().getPopupContainer();

		Dom.removeClass(
			popupContainer.getElementsByClassName('bizproc-debugger-automation__main-fields')[0],
			'--disabled'
		);

		Dom.removeClass(
			popupContainer.getElementsByClassName('bizproc-debugger-automation__toolbar')[0],
			'--disabled'
		);

		const activeTab = this.debugger.settings.get('tab') === 'log' ? 'log' : 'doc';
		popupContainer.querySelectorAll([`[data-tab-item="no-document"]`]).forEach(
			(tab) => Dom.removeClass(tab, ['--empty', '--active'])
		);
		popupContainer.querySelectorAll([`[data-tab-item="${activeTab}"]`]).forEach(
			(tab) => Dom.addClass(tab, '--active')
		);

		this.#onDocumentStatusChanged();
	}

	#setDebuggerState(state: number)
	{
		if (!this.#buttonPlay)
		{
			return;
		}

		switch (state)
		{
			case DebuggerState.Run:
				this.#buttonPlay.setIcon(ButtonIcon.PAUSE);
				this.#buttonPlay.getContainer()
					.nextElementSibling.textContent = Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ACTION_PAUSE');
				break;

			default:
				this.#buttonPlay.setIcon(ButtonIcon.START);
				this.#buttonPlay.getContainer()
					.nextElementSibling.textContent = Loc.getMessage('BIZPROC_DEBUGGER_AUTOMATION_ACTION_START');

		}
	}
}