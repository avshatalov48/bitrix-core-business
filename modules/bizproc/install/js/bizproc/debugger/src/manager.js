import {Type} from 'main.core';
import Automation from "./automation";
import Session from './session/session';
import {CommandHandler} from "./pull/command-handler";

let instance = null;

export default class Manager
{
	pullHandler: CommandHandler;

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
	}

	initializeDebugger(parameters = {session: {}, documentSigned: ''})
	{
		const session = Type.isPlainObject(parameters.session) ? new Session(parameters.session) : null;
		if (!session)
		{
			return;
		}

		session.documentSigned = parameters.documentSigned;

		this.#showDebugger(session);
	}

	startSession(documentSigned: string, modeId: number): Promise
	{
		return new Promise((resolve, reject) => {
			Session.start(documentSigned, modeId).then(
				(session: Session) => {
					this.setDebugFilter();
					this.#showDebugger(session, true);

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
					this.removeDebugFilter();
					resolve(response);
				},
				reject
			);
		});
	}

	#showDebugger(session: Session, expanded: boolean = false)
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
			debuggerInstance.getMainView()[expanded? 'showExpanded' : 'show']();
		}
	}

	setDebugFilter()
	{
		const filters = BX.Main.filterManager?.getList();
		if (!filters)
		{
			return;
		}

		filters.forEach((filter) => {
			const api = filter.getApi();
			api.setFilter({preset_id: 'filter_robot_debugger'});
		});
	}

	removeDebugFilter()
	{
		const filters = BX.Main.filterManager?.getList();
		if (!filters)
		{
			return;
		}

		filters.forEach((filter) => {
			const api = filter.getApi();
			api.setFilter({preset_id: 'default_filter'});
		});
	}

	createAutomationDebugger(parameters = {}): Automation
	{
		return new Automation(parameters);
	}

	openDebuggerStartPage(documentSigned: string): Promise
	{
		const url = BX.Uri.addParam(
			'/bitrix/components/bitrix/bizproc.debugger.start/',
			{
				documentSigned: documentSigned
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
