import {Type, Reflection} from 'main.core';
import Automation from "./automation";
import Session from './session/session';
import {CommandHandler} from "./pull/command-handler";
import Settings from "./settings";
import {Mode} from "./session/mode";
import {CrmDebuggerGuide} from "./tourguide/crm-debugger-guide";

let instance = null;

export default class Manager
{
	pullHandler: CommandHandler;
	#settings: Settings;

	static get Instance(): Manager
	{
		if (instance === null)
		{
			instance = new Manager();
		}

		return instance;
	}

	constructor()
	{
		this.pullHandler = new CommandHandler();
		this.#settings = new Settings('manager');
	}

	initializeDebugger(parameters = {session: {}, documentSigned: ''})
	{
		const session = Type.isPlainObject(parameters.session) ? new Session(parameters.session) : null;
		if (!session)
		{
			return;
		}

		session.documentSigned = parameters.documentSigned;

		this.requireSetFilter(session);
		this.#showDebugger(session);
	}

	startSession(documentSigned: string, modeId: number): Promise
	{
		return new Promise((resolve, reject) => {
			Session.start(documentSigned, modeId).then(
				(session: Session) => {
					this.#lastFilterId = null;

					this.#setDebugFilter(session);
					const debuggerInstance = this.#showDebugger(session, true);

					this.#showGuide(debuggerInstance);

					resolve();
				},
				reject
			);
		});
	}

	finishSession(session: Session): Promise
	{
		return new Promise((resolve, reject) => {
			session.finish().then(
				(response) => {
					this.#removeDebugFilter(session);
					resolve(response);
				},
				reject
			);
		});
	}

	#showDebugger(session: Session, isFirstShow: boolean = false): ?Automation
	{
		let debuggerInstance = null;

		if (session.isAutomation())
		{
			debuggerInstance = this.createAutomationDebugger({
				session: session
			});
		}

		if (debuggerInstance)
		{
			const initialShowState = session.isExperimentalMode() ? 'showExpanded' : 'showCollapsed';
			debuggerInstance.getMainView()[isFirstShow ? initialShowState : 'show']();

			return debuggerInstance;
		}

		return debuggerInstance;
	}

	#showGuide(debuggerInstance: Automation)
	{
		const guide = new CrmDebuggerGuide({
			grid: Reflection.getClass('BX.CRM.Kanban.Grid') ? BX.CRM.Kanban.Grid.getInstance() : null,
			showFilterStep: (this.#settings.get('filter-guide-shown') !== true),
			showStageStep: (this.#settings.get('stage-guide-shown') !== true) && debuggerInstance.session.isInterceptionMode(),
			reserveFilterIds: this.#getFilterIds(debuggerInstance.session),
		});

		guide.subscribe(
			'onFilterGuideStepShow', (function () {
				this.#settings.set('filter-guide-shown', true)
			}).bind(this)
		);
		guide.subscribe(
			'onStageGuideStepShow', (function () {
				this.#settings.set('stage-guide-shown', true)
			}).bind(this)
		);

		guide.start();
	}

	requireSetFilter(session: Session, force: boolean = false)
	{
		const lastId = this.#getFilterIds(session).pop();

		if (lastId !== this.#lastFilterId || force)
		{
			this.#setDebugFilter(session);
		}
	}

	#setDebugFilter(session: Session)
	{
		const ids = this.#getFilterIds(session);

		this.#getFilterApis(ids).forEach(
			({id, api}) => {
				api.setFilter({preset_id: 'filter_robot_debugger'});
				this.#lastFilterId = id;
			}
		);
	}

	#removeDebugFilter(session: Session)
	{
		const ids = this.#getFilterIds(session);

		this.#getFilterApis(ids).forEach(
			({api}) => {
				api.setFilter({preset_id: 'default_filter'});
				this.#lastFilterId = null;
			}
		);
	}

	get #lastFilterId()
	{
		return this.#settings.get('last-filter-id');
	}

	set #lastFilterId(value: string)
	{
		return this.#settings.set('last-filter-id', value);
	}

	/**
	 * @return BX.Filter.Api | null
	 */
	#getFilterApis(ids: []): []
	{
		const apis = [];

		ids.forEach(id => {
			const filter = BX.Main.filterManager?.getById(id);
			if (filter)
			{
				apis.push({id, api: filter.getApi()});
			}
		});

		return apis;
	}

	#getFilterIds(session: Session): string[]
	{
		let categoryId;
		if (session && (session.modeId === Mode.interception.id) && !session.isFixed())
		{
			categoryId = session.initialCategoryId;
		}
		else
		{
			categoryId = session?.activeDocument?.categoryId
		}

		const filterId = 'CRM_DEAL_LIST_V12';

		if (!categoryId)
		{
			return [filterId, `${filterId}_C_0`];
		}

		return [`${filterId}_C_${categoryId}`];
	}

	createAutomationDebugger(parameters = {}): Automation
	{
		return new Automation(parameters);
	}

	openDebuggerStartPage(documentSigned: string, parameters = {}): Promise
	{
		const url = BX.Uri.addParam(
			'/bitrix/components/bitrix/bizproc.debugger.start/',
			{
				documentSigned: documentSigned,
				analyticsLabel: {
					automation_enter_debug: 'Y',
					start_type: parameters.analyticsStartType || 'default',
				}
			}
		);

		const options = {
			width: 745,
			cacheable: false,
			allowChangeHistory: true,
			events: {},
		};

		return Manager.openSlider(url, options);
	}

	openSessionLog(sessionId: string): Promise
	{
		const url = BX.Uri.addParam(
			'/bitrix/components/bitrix/bizproc.debugger.log/',
			{
				'setTitle': 'Y',
				'sessionId': sessionId,
			}
		);

		const options = {
			width: 720,
			cacheable: false,
			allowChangeHistory: true,
			events: {},
			newWindowLabel: true
		};

		return Manager.openSlider(url, options);
	}

	static openSlider(url, options): Promise
	{
		if(!Type.isPlainObject(options))
		{
			options = {};
		}
		options = {...{cacheable: false, allowChangeHistory: true, events: {}}, ...options};

		return new Promise((resolve, reject) => {
			if (Type.isStringFilled(url))
			{
				if (BX.SidePanel.Instance.open(url, options))
				{
					return resolve();
				}

				return reject();
			}

			return reject();
		});
	}
}
