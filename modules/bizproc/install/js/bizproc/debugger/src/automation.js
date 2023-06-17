import AutomationMainView from './views/automation-main';
import Session from './session/session';
import {ajax, Loc, Type, Text, Reflection} from "main.core";
import {BaseEvent, EventEmitter} from "main.core.events";
import AutomationLogView from "./views/automation-log";
import { Settings } from 'bizproc.local-settings';
import {MessageBox, MessageBoxButtons} from "ui.dialogs.messagebox";
import {Manager} from "./index";
import {
	setGlobalContext,
	getGlobalContext,
	Context,
	Document,
	WorkflowStatus,
	AutomationGlobals
} from 'bizproc.automation';
import {CustomCrmActionPanel} from "./actionpanel/custom-crm-action-panel";

export default class Automation extends EventEmitter
{
	session: Session = null;
	#pullHandlers: ?[] = null;

	#settings: Settings;

	#mainView;

	#triggers: Array;
	#template: Array;
	#documentStatus: string;
	#statusList: Array;
	#documentCategoryId: Number = 0;
	#documentFields: Array;
	#documentValues: Object = {};

	#workflowId: string;
	#workflowStatus: number;
	#workflowEvents: Array<{name: string, sourceId: string}> = [];
	#workflowTrack: Array = [];
	#debuggerState: number;

	#customActionPanel: CustomCrmActionPanel = null;

	constructor(parameters = {})
	{
		super();
		this.setEventNamespace('BX.Bizproc.Debugger.Automation');

		this.session = parameters.session;

		if (this.session.isActive())
		{
			this.session.subscribeOnce('onAfterDocumentFixed', this.#onAfterDocumentFixed.bind(this));
			this.session.subscribe('onFinished', this.destroy.bind(this));
			this.#subscribePull();
		}

		this.#settings = new Settings('atm-dbg');
		this.#initAutomationContext();

		this.#resumeShowActionPanel();
	}

	#resumeShowActionPanel()
	{
		if (this.session.isInterceptionMode() && !this.session.isFixed())
		{
			if (Reflection.getClass('BX.CRM.Kanban.Grid'))
			{
				const gridInstance = BX.CRM.Kanban.Grid.getInstance();
				if (this.#shouldSetCustomActionPanel(gridInstance))
				{
					this.#customActionPanel = new CustomCrmActionPanel(gridInstance, this);
					gridInstance.stopActionPanel(true);
					gridInstance.resetActionPanel();
					gridInstance.setCustomActionPanel(this.#customActionPanel.actionPanel);
				}
			}
		}
	}

	#shouldSetCustomActionPanel(gridInstance): boolean
	{
		const gridData = gridInstance.getData();
		const entityType = gridData.entityType;

		//todo: modify
		if (entityType !== 'DEAL')
		{
			return false;
		}

		const categoryId =
			gridData.params.hasOwnProperty('CATEGORY_ID')
				? Text.toInteger(gridData.params.CATEGORY_ID)
				: 0
		;

		return (this.session.initialCategoryId === categoryId);
	}

	#initAutomationContext()
	{
		const context = new Context({
			document: new Document({
				rawDocumentType: [],
				documentId: null,
				categoryId: 0,
				statusList: this.getStatusList(),
				statusId: this.getDocumentStatus(),
				documentFields: this.getDocumentFields(),
			}),
			documentSigned: this.documentSigned,
			canEdit: false,
			canManage: false,
			automationGlobals: new AutomationGlobals({
				variables: [],
				constants: [],
			}),
		});

		setGlobalContext(context);
	}

	destroy()
	{
		this.unsubscribeAll();
		this.#unsubscribePull();

		this.#mainView?.destroy();

		this.session = null;
		this.#settings = null;
		this.#mainView = null;

		this.#template = [];
		this.#documentStatus = null;
		this.#statusList = [];
		this.#documentFields = [];
		this.#documentValues = {};

		this.#workflowId = null;
		this.#workflowStatus = 0;
		this.#workflowEvents = [];
		this.#workflowTrack = [];
		this.#customActionPanel?.stopActionPanel();
	}

	get track()
	{
		return this.#workflowTrack;
	}

	get settings(): Settings
	{
		return this.#settings;
	}

	get documentSigned(): string
	{
		return this.session.documentSigned;
	}

	get sessionId(): string
	{
		return this.session.id;
	}

	get workflowId(): string
	{
		return this.#workflowId;
	}

	get pullHandlers(): []
	{
		if (this.#pullHandlers === null)
		{
			this.#pullHandlers = [
				{name: 'documentStatus', func: this.handleExternalDocumentStatus.bind(this)},
				{name: 'documentValues', func: this.handleExternalDocumentValues.bind(this)},
				{name: 'documentDelete', func: this.handleExternalDocumentDelete.bind(this)},
				{name: 'workflowStatus', func: this.handleExternalWorkflowStatus.bind(this)},
				{name: 'workflowEventAdd', func: this.handleExternalWorkflowEventAdd.bind(this)},
				{name: 'workflowEventRemove', func: this.handleExternalWorkflowEventRemove.bind(this)},
				{name: 'trackRow', func: this.handleExternalTrackRow.bind(this)},
			]
		}
		return this.#pullHandlers;
	}

	#subscribePull()
	{
		const pull = Manager.Instance.pullHandler;

		this.pullHandlers.forEach(({name, func}) => {
			pull.subscribe(name, func);
		});
	}

	#unsubscribePull()
	{
		if (this.#pullHandlers === null)
		{
			return;
		}

		const pull = Manager.Instance.pullHandler;

		this.pullHandlers.forEach(({name, func}) => {
			pull.unsubscribe(name, func);
		});

		this.#pullHandlers = null;
	}

	getMainView(): AutomationMainView
	{
		if (!this.#mainView)
		{
			this.#mainView = new AutomationMainView(this);
		}

		return this.#mainView;
	}

	getLogView(): AutomationLogView
	{
		return new AutomationLogView(this);
	}

	getStatusList(): Array
	{
		return this.#statusList;
	}

	getDocumentFields(): Array
	{
		return this.#documentFields;
	}

	getDocumentField(fieldId: string): null | {}
	{
		return this.#documentFields.find((field) => field.Id === fieldId);
	}

	getDocumentValue(fieldId: string): null | any
	{
		return this.#documentValues[fieldId] || null;
	}

	getDocumentStatus(): string
	{
		return this.#documentStatus; //getActiveDocument().getStatus();
	}

	getWorkflowStatus(): number
	{
		return this.#workflowStatus;
	}

	getState(): number
	{
		return this.#debuggerState;
	}

	hasWorkflowEvents(): boolean
	{
		return this.#workflowEvents.length > 0;
	}

	setDocumentStatus(statusId: string): Promise
	{
		return new Promise(resolve => {
			ajax.runAction(
				'bizproc.debugger.setDocumentStatus',
				{
					data: {
						statusId: statusId,
					}
				}
			).then(
				(response) => {

					if (response.data && response.data.newStatus)
					{
						this.#documentStatus = response.data.newStatus;
						this.#template = response.data.template;
						this.#workflowTrack = [];
						this.emit('onDocumentStatusChanged');
					}
					resolve(response);
				},
				this.#handleRejectResponse.bind(this)
			);
		});
	}

	get templateTriggers(): Array
	{
		return this.#triggers.filter(trigger => trigger['DOCUMENT_STATUS'] === this.#template['DOCUMENT_STATUS']);
	}

	getTemplate(): Array
	{
		return this.#template;
	}

	isTemplateEmpty(): boolean
	{
		return this.#template.IS_EXTERNAL_MODIFIED === false && !Type.isArrayFilled(this.#template.ROBOTS)
	}

	startDebugTemplate(): Promise
	{
		return new Promise(resolve => {
			ajax.runAction(
				'bizproc.debugger.resumeAutomationTemplate',
				{
					data: {
						sessionId: this.sessionId,
					},
					analyticsLabel: 'automation_start_debug',
				}
			).then(
				(response) => {

					this.#workflowId = response.data.workflowId;
					this.#debuggerState = response.data.debuggerState;

					resolve(response.data);
				},
				this.#handleRejectResponse.bind(this));
		});
	}

	emulateExternalEvent(robotId: ?string)
	{
		return new Promise(resolve => {
			let eventId = this.#workflowEvents[0]?.name;
			if (Type.isStringFilled(robotId))
			{
				eventId = this.#workflowEvents.find(({sourceId: eventRobotId}) => eventRobotId === robotId)?.name;
			}

			if (!eventId)
			{
				return;
			}

			ajax.runAction(
				'bizproc.debugger.emulateExternalEvent',
				{
					data: {
						workflowId: this.#workflowId,
						eventId,
					}
				}
			).then(
				(response) => {

				resolve(response.data)
				},
				this.#handleRejectResponse.bind(this)
			);
		});
	}

	loadMainViewInfo(): Promise
	{
		return new Promise(resolve => {
			ajax.runAction(
				'bizproc.debugger.fillAutomationView',
				{
					data: {
						sessionId: this.sessionId,
					}
				}
			).then(
				(response) =>
				{
					this.#triggers = response.data.triggers;
					this.#template = response.data.template;
					this.#documentStatus = response.data.documentStatus;
					this.#statusList = response.data.statusList;
					this.#documentCategoryId = response.data.documentCategoryId;
					this.#documentFields = response.data.documentFields;
					this.#documentValues = response.data.documentValues;

					this.#workflowId = response.data.workflowId;
					this.#workflowStatus = response.data.workflowStatus;
					this.#workflowEvents = response.data.workflowEvents;
					this.#workflowTrack = response.data.track;
					this.#debuggerState = response.data.debuggerState;

					getGlobalContext().document
						.setFields(this.getDocumentFields())
						.setStatusList(this.getStatusList())
						.setStatus(this.getDocumentStatus())
					;
					getGlobalContext().automationGlobals.globalConstants =
						Type.isArrayFilled(response.data.globalConstants)
							? response.data.globalConstants
							: []
					;

					getGlobalContext().automationGlobals.globalVariables =
						Type.isArrayFilled(response.data.globalVariables)
							? response.data.globalVariables
							: []
					;

					resolve();
				},
				this.#handleRejectResponse.bind(this)
			);
		});
	}

	get globalConstants(): []
	{
		const context = getGlobalContext();

		return context && context.automationGlobals ? context.automationGlobals.globalConstants : [];
	}

	get globalVariables(): []
	{
		const context = getGlobalContext();

		return context && context.automationGlobals ? context.automationGlobals.globalVariables : [];
	}

	loadAllLog(): Promise
	{
		return new Promise(resolve => {
			ajax.runAction(
				'bizproc.debugger.loadAllLog',
				{
					data: {
						sessionId: this.session.id,
					}
				}
			).then(
				(response) => {
					resolve(response.data);
				},
				this.#handleRejectResponse.bind(this)
			);
		});
	}

	loadRobotsByWorkflowId(workflowId: string): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runAction(
				'bizproc.debugger.loadRobotsByWorkflowId',
				{
					data: {
						sessionId: this.sessionId,
						workflowId
					}
				}
			).then(
				(response) => {
					resolve(response.data);
				},
				(response) => {
					reject(response.data);
					//this.#handleRejectResponse.bind(this);
				}
			);
		});
	}

	handleExternalDocumentStatus(event: BaseEvent)
	{
		const status: string = event.getData().status;

		if (this.getDocumentStatus() === status)
		{
			return;
		}

		console.info('document status: ' + status);
		this.#documentStatus = status
		this.emit('onDocumentStatusChanged');
	}

	handleExternalDocumentValues(event: BaseEvent)
	{
		const values: {} = event.getData().values;

		Object.keys(values).forEach((key) => this.#documentValues[key] = values[key]);

		console.info('document values: ' + Object.keys(values));

		this.emit('onDocumentValuesUpdated', {values});
	}

	handleExternalDocumentDelete()
	{
		MessageBox.show({
			message: Loc.getMessage('BIZPROC_JS_DEBUGGER_CONFIRM_ON_DOCUMENT_DELETE'),
			okCaption: Loc.getMessage('BIZPROC_JS_DEBUGGER_VIEWS_MENU_FINISH_SESSION'),
			onOk: () => {
				return Manager.Instance.finishSession(this.session).then(
					null,
					this.#handleRejectResponse.bind(this)
				);
			},
			buttons: MessageBoxButtons.OK_CANCEL,
		});
	}

	#handleRejectResponse(response)
	{
		if (Type.isArrayFilled(response.errors))
		{
			const noDocumentError = response.errors.find((error) => error.code === 404);

			if (noDocumentError)
			{
				this.handleExternalDocumentDelete();
			}
			else
			{
				const message = response.errors.map(error => error.message).join('\n');

				MessageBox.alert(message);
			}
		}
	}

	handleExternalTrackRow(event: BaseEvent)
	{
		const row: {} = event.getData().row;
		row['WORKFLOW_STATUS'] = this.#workflowStatus;

		this.#workflowTrack.push(row);

		this.emit('onWorkflowTrackAdded', {row});
	}

	handleExternalWorkflowStatus(event: BaseEvent)
	{
		const status: WorkflowStatus = event.getData().status;
		const workflowId: string = event.getData().workflowId;

		this.#workflowStatus = status;

		if (status === WorkflowStatus.RUNNING)
		{
			this.#workflowId = workflowId;
		}

		if (this.#workflowId !== workflowId)
		{
			return;
		}

		console.info('workflow status: ' + status);
		this.emit('onWorkflowStatusChanged', {status, workflowId});
	}

	handleExternalWorkflowEventAdd(event: BaseEvent)
	{
		const eventName: string = event.getData().eventName;
		const robotId: ?string = event.getData().sourceId;

		console.info('workflow event added: ' + eventName);
		this.#workflowEvents.push({ name: eventName, sourceId: robotId });
		console.info('workflow events: ' + this.#workflowEvents.map(event => event.name).join(', '));
		this.emit('onWorkflowEventsChanged', {events: this.#workflowEvents});
	}

	handleExternalWorkflowEventRemove(event: BaseEvent)
	{
		const eventName: string = event.getData().eventName;

		console.info('workflow event removed: ' + eventName);
		this.#workflowEvents = this.#workflowEvents.filter(({name: value}) => value !== eventName);
		console.info('workflow events: ' + this.#workflowEvents.map(({name}) => name).join(', '));
		this.emit('onWorkflowEventsChanged', {events: this.#workflowEvents});
	}

	#onAfterDocumentFixed()
	{
		this.loadMainViewInfo().then(()=> {
			this.emit('onAfterDocumentFixed');
		});
	}

	getField(object, id): object
	{
		let field;

		switch (object)
		{
			case 'Document':
				field = this.#documentFields.find((field) => field.Id === id);
				break;
			case 'Template':
			case 'Parameter':
			case 'Constant':
			case 'GlobalConst':
			case 'GlobalVar':
				// todo: parameter, variable, constant, GlobalConst, GlobalVar, Activity
				break;
		}

		return field || {
			Id: id,
			ObjectId: object,
			Name: id,
			Type: 'string',
			Expression: id,
			SystemExpression: '{='+object+':'+id+'}'
		};
	}

	getSettingsUrl(): string
	{
		//TODO: get actual url
		return `/crm/deal/automation/${this.#documentCategoryId}/`;
	}
}