// @flow
import {Type, Text, Loc} from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';
import type { ProcessOptions, OptionsField, QueueAction, ProcessResult, ControllerResponse } from './process-types';
import { ProcessResultStatus, ProcessState } from './process-types';
import { Dialog } from './dialog';

/**
 * @namespace {BX.UI.StepProcessing}
 */
export const ProcessEvent = {
	StateChanged: 'BX.UI.StepProcessing.StateChanged',
	BeforeRequest: 'BX.UI.StepProcessing.BeforeRequest'
}

/**
 * @namespace {BX.UI.StepProcessing}
 */
export const ProcessCallback = {
	StateChanged: 'StateChanged',
	RequestStart: 'RequestStart',
	RequestStop: 'RequestStop',
	RequestFinalize: 'RequestFinalize',
	StepCompleted: 'StepCompleted'
}

export const ProcessDefaultLabels = {
	AuthError: Loc.getMessage('UI_STEP_PROCESSING_AUTH_ERROR'),
	RequestError: Loc.getMessage('UI_STEP_PROCESSING_REQUEST_ERR'),
	DialogStartButton: Loc.getMessage('UI_STEP_PROCESSING_BTN_START'),
	DialogStopButton: Loc.getMessage('UI_STEP_PROCESSING_BTN_STOP'),
	DialogCloseButton: Loc.getMessage('UI_STEP_PROCESSING_BTN_CLOSE'),
	RequestCanceling: Loc.getMessage('UI_STEP_PROCESSING_CANCELING'),
	RequestCanceled: Loc.getMessage('UI_STEP_PROCESSING_CANCELED'),
	RequestCompleted: Loc.getMessage('UI_STEP_PROCESSING_COMPLETED'),
	DialogExportDownloadButton: Loc.getMessage('UI_STEP_PROCESSING_FILE_DOWNLOAD'),
	DialogExportClearButton: Loc.getMessage('UI_STEP_PROCESSING_FILE_DELETE'),
	WaitingResponse: Loc.getMessage('UI_STEP_PROCESSING_WAITING'),
};

const EndpointType = {
	Controller: 'controller',
	Component: 'component'
};

/**
 * Long running process.
 *
 * @namespace {BX.UI.StepProcessing}
 * @event BX.UI.StepProcessing.StateChanged
 * @event BX.UI.StepProcessing.BeforeRequest
 */
export class Process
{
	options: ProcessOptions;

	id: string;

	// Ajax endpoint
	endpointType: EndpointType.Controller|EndpointType.Component;
	controller: string;
	controllerDefault: string;
	component: string;
	componentMode: 'class'|'ajax';
	hash: string;
	action: string = '';
	method: 'POST'|'GET' = 'POST';
	params: {[name: string]: any} = {};

	/**
	 * @private
	 */
	xhr: ?XMLHttpRequest;
	ajaxPromise: ?Promise;
	isRequestRunning: boolean = false;
	networkErrorCount: 0;

	// Queue
	queue: Array<QueueAction> = [];
	currentStep: number = -1;
	state: $Values<ProcessState> = ProcessState.intermediate;

	// Dialog
	dialog: Dialog;
	initialOptionValues: {[id: string]: any} = {};
	optionsFields: {[id: string]: OptionsField} = {};

	// Events
	handlers: {[event: string]: any => {}} = {};

	// Messages
	messages: Map<string, string> = new Map;

	constructor(options: ProcessOptions)
	{
		this.options = Type.isPlainObject(options) ? options : {};

		this.id = this.getOption('id', '');
		if (!Type.isStringFilled(this.id))
		{
			this.id = 'Process_' + Text.getRandom().toLowerCase();
		}

		const controller = this.getOption('controller', '');
		const component = this.getOption('component', '');
		if (Type.isStringFilled(controller))
		{
			this.controller = controller;
			this.controllerDefault = controller;
			this.endpointType = EndpointType.Controller;
		}
		else if (Type.isStringFilled(component))
		{
			this.component = component;
			this.endpointType = EndpointType.Component;
			this.componentMode = this.getOption('componentMode', 'class');
		}
		if (!Type.isStringFilled(this.controller))
		{
			if (!Type.isStringFilled(this.component))
			{
				throw new TypeError("BX.UI.StepProcessing: There no any ajax endpoint was defined.");
			}
		}

		this
			.setQueue(this.getOption('queue', []))
			.setParams(this.getOption('params', {}))
			.setOptionsFields(this.getOption('optionsFields', {}))
			.setHandlers(this.getOption('handlers', {}))
			.setMessages(ProcessDefaultLabels)
			.setMessages(this.getOption('messages', {}))
		;
	}

	destroy()
	{
		if (this.dialog instanceof Dialog)
		{
			this.dialog.close().destroy();
			this.dialog = null;
		}

		this._closeConnection();
	}

	//region Run

	start(startStep?: number = 1)
	{
		this._refreshHash();

		startStep = startStep || 1;
		if (
			this.state === ProcessState.intermediate ||
			this.state === ProcessState.stopped ||
			this.state === ProcessState.completed
		)
		{
			if (!this.getDialog().checkOptionFields())
			{
				return;
			}

			this.getDialog().clearErrors().clearWarnings();

			this.networkErrorCount = 0;

			if (this.getQueueLength() > 0)
			{
				this.currentStep = 0;
				if (startStep > 1)
				{
					this.currentStep = startStep - 1;
				}

				if (this.endpointType === EndpointType.Controller)
				{
					if (Type.isStringFilled(this.queue[this.currentStep].controller))
					{
						this.setController(this.queue[this.currentStep].controller);
					}
				}

				if (!Type.isStringFilled(this.queue[this.currentStep].action))
				{
					throw new Error("BX.UI.StepProcessing: Could not find controller action at the queue position.");
				}
				this.setAction(this.queue[this.currentStep].action);

				this.startRequest();

				if (this.queue[this.currentStep].title)
				{
					this.getDialog().setSummary(this.queue[this.currentStep].title);
				}
				else
				{
					this.getDialog().setSummary(this.getMessage('WaitingResponse'));
				}
			}
			else
			{
				this.startRequest();
			}
		}
		return this;
	}

	stop()
	{
		if (this.state === ProcessState.running)
		{
			this.stopRequest();
			this.currentStep = -1;
		}
		return this;
	}

	//endregion

	//region Request

	startRequest(): ?Promise
	{
		if (this.isRequestRunning || this.state === ProcessState.canceling)
		{
			return this.ajaxPromise;
		}
		this.isRequestRunning = true;
		this.ajaxPromise = null;

		let actionData = new FormData();

		let appendData = (data, prefix) => {
			if (Type.isPlainObject(data))
			{
				Object.keys(data).forEach(name => {
					let id = name;
					if (prefix)
					{
						id = prefix+'['+name+']';
					}
					if (Type.isArray(data[name]) || Type.isPlainObject(data[name]))
					{
						appendData(data[name], id);
					}
					else
					{
						actionData.append(id, data[name]);
					}
				});
			}
			else if (Type.isArray(data))
			{
				data.forEach(element => actionData.append(prefix+'[]', element));
			}
		};

		appendData(this.params);
		if (this.queue[this.currentStep].params)
		{
			appendData(this.queue[this.currentStep].params);
		}

		let initialOptions = this.getDialog().getOptionFieldValues();
		if (BX.type.isNotEmptyObject(initialOptions))
		{
			appendData(initialOptions);
			this.initialOptionValues = initialOptions;
			this.storeOptionFieldValues(initialOptions);
		}
		else
		{
			Object.keys(this.initialOptionValues).forEach(name => {
				// don't repeat file uploading
				if (this.initialOptionValues[name] instanceof File)
				{
					delete (this.initialOptionValues[name]);
				}
			});
			appendData(this.initialOptionValues);
		}

		this.setState(ProcessState.running);

		if (this.hasActionHandler(ProcessCallback.RequestStart))
		{
			this.callActionHandler(ProcessCallback.RequestStart, [actionData]);
		}
		else if (this.hasHandler(ProcessCallback.RequestStart))
		{
			this.callHandler(ProcessCallback.RequestStart, [actionData]);
		}

		EventEmitter.emit(ProcessEvent.BeforeRequest, new BaseEvent({data: {process: this, actionData: actionData}}));

		let params = {
			data: actionData,
			method: this.method,
			onrequeststart: this._onRequestStart.bind(this)
		};
		if (this.endpointType === EndpointType.Controller)
		{
			this.ajaxPromise =
				BX.ajax.runAction(this.controller + '.' + this.getAction(), params)
					.then(
						this._onRequestSuccess.bind(this),
						this._onRequestFailure.bind(this)
					);
		}
		else if (this.endpointType === EndpointType.Component)
		{
			params.data.mode = this.componentMode;
			if ('signedParameters' in params.data)
			{
				params.signedParameters = params.data.signedParameters;
				delete params.data.signedParameters;
			}
			this.ajaxPromise =
				BX.ajax.runComponentAction(this.component, this.getAction(), params)
					.then(
						this._onRequestSuccess.bind(this),
						this._onRequestFailure.bind(this)
					);
		}

		return this.ajaxPromise;
	}

	stopRequest(): ?Promise
	{
		if (this.state === ProcessState.canceling)
		{
			return this.ajaxPromise;
		}

		this.setState(ProcessState.canceling);

		this._closeConnection();

		let actionData = BX.clone(this.params);

		actionData.cancelingAction = this.getAction();

		this.getDialog().setSummary(this.getMessage("RequestCanceling"));

		let proceedAction = true;
		if (this.hasActionHandler(ProcessCallback.RequestStop))
		{
			proceedAction = false;
			this.callActionHandler(ProcessCallback.RequestStop, [actionData]);
		}
		else if (this.hasHandler(ProcessCallback.RequestStop))
		{
			proceedAction = false;
			this.callHandler(ProcessCallback.RequestStop, [actionData]);
		}

		EventEmitter.emit(ProcessEvent.BeforeRequest, new BaseEvent({data: {process: this, actionData: actionData}}));

		this.ajaxPromise = null;

		if (proceedAction)
		{
			let params = {
				data: actionData,
				method: this.method,
				onrequeststart: this._onRequestStart.bind(this)
			};
			if (this.endpointType === EndpointType.Controller)
			{
				this.setController(this.controllerDefault);
				this.ajaxPromise =
					BX.ajax.runAction(this.controller + '.cancel', params)
						.then(
							this._onRequestSuccess.bind(this),
							this._onRequestFailure.bind(this)
						);
			}
			else if (this.endpointType === EndpointType.Component)
			{
				params.data.mode = this.componentMode;
				if ('signedParameters' in params.data)
				{
					params.signedParameters = params.data.signedParameters;
					delete params.data.signedParameters;
				}
				this.ajaxPromise =
					BX.ajax.runComponentAction(this.component, 'cancel', params)
						.then(
							this._onRequestSuccess.bind(this),
							this._onRequestFailure.bind(this)
						);
			}
		}

		return this.ajaxPromise;
	}

	finalizeRequest(): ?Promise
	{
		if (this.state === ProcessState.canceling)
		{
			return this.ajaxPromise;
		}

		let actionData = BX.clone(this.params);

		let proceedAction = true;
		if (this.hasActionHandler(ProcessCallback.RequestFinalize))
		{
			proceedAction = false;
			this.callActionHandler(ProcessCallback.RequestFinalize, [actionData]);
		}
		else if (this.hasHandler(ProcessCallback.RequestFinalize))
		{
			proceedAction = false;
			this.callHandler(ProcessCallback.RequestFinalize, [actionData]);
		}

		EventEmitter.emit(ProcessEvent.BeforeRequest, new BaseEvent({data: {process: this, actionData: actionData}}));

		this.ajaxPromise = null;

		if (proceedAction)
		{
			let params = {
				data: actionData,
				method: this.method,
				onrequeststart: this._onRequestStart.bind(this)
			};
			if (this.endpointType === EndpointType.Controller)
			{
				this.setController(this.controllerDefault);
				this.ajaxPromise = BX.ajax.runAction(this.controller + '.finalize', params);
			}
			else if (this.endpointType === EndpointType.Component)
			{
				params.data.mode = this.componentMode;
				if ('signedParameters' in params.data)
				{
					params.signedParameters = params.data.signedParameters;
					delete params.data.signedParameters;
				}
				this.ajaxPromise = BX.ajax.runComponentAction(this.component, 'finalize', params);
			}
		}

		return this.ajaxPromise;
	}

	/**
	 * @private
	 */
	_refreshHash()
	{
		this.hash = this.id + Date.now();
		this.setParam("PROCESS_TOKEN", this.hash);
		return this;
	}

	/**
	 * @private
	 */
	_onRequestSuccess(response: ControllerResponse)
	{
		this.isRequestRunning = false;
		this.xhr = null;
		this.ajaxPromise = null;

		if (!response)
		{
			this.getDialog().setError(this.getMessage('RequestError'));
			this.setState(ProcessState.error);
			return;
		}

		if (Type.isArrayFilled(response.errors))
		{
			const errors = response.errors.slice(-10);
			let errMessages = [];
			errors.forEach(err => errMessages.push(err.message));

			this.getDialog().setErrors(errMessages, true);
			this.setState(ProcessState.error);
			return;
		}

		this.networkErrorCount = 0;

		const result = response.data;

		const status = Type.isStringFilled(result.STATUS) ? result.STATUS : "";
		let summary = "";
		if (Type.isStringFilled(result.SUMMARY))
		{
			summary = result.SUMMARY;
		}
		else if (Type.isStringFilled(result.SUMMARY_HTML))
		{
			summary = result.SUMMARY_HTML;
		}
		const processedItems = Type.isNumber(result.PROCESSED_ITEMS) ? result.PROCESSED_ITEMS : 0;
		const totalItems = Type.isNumber(result.TOTAL_ITEMS) ? result.TOTAL_ITEMS : 0;
		let finalize = !!result.FINALIZE;

		if (this.hasActionHandler(ProcessCallback.StepCompleted))
		{
			this.callActionHandler(ProcessCallback.StepCompleted, [status, result]);
		}

		if (Type.isStringFilled(result.WARNING))
		{
			this.getDialog().setWarning(result.WARNING);
		}

		if (status === ProcessResultStatus.progress || status === ProcessResultStatus.completed)
		{
			if (totalItems > 0)
			{
				if (this.queue[this.currentStep].progressBarTitle)
				{
					this.getDialog()
						.setProgressBar(totalItems, processedItems, this.queue[this.currentStep].progressBarTitle);
				}
				else
				{
					this.getDialog()
						.setProgressBar(totalItems, processedItems);
				}
			}
			else
			{
				this.getDialog().hideProgressBar();
			}
		}

		if (status === ProcessResultStatus.progress)
		{
			if (summary !== "")
			{
				this.getDialog().setSummary(summary, true);
			}

			if (this.state === ProcessState.canceling)
			{
				this.setState(ProcessState.stopped);
			}
			else
			{
				if (this.endpointType === EndpointType.Controller)
				{
					const nextController = Type.isStringFilled(result.NEXT_CONTROLLER) ? result.NEXT_CONTROLLER : "";
					if (nextController !== "")
					{
						this.setController(nextController);
					}
					else if (Type.isStringFilled(this.queue[this.currentStep].controller))
					{
						this.setController(this.queue[this.currentStep].controller);
					}
					else
					{
						this.setController(this.controllerDefault);
					}
				}

				const nextAction = Type.isStringFilled(result.NEXT_ACTION) ? result.NEXT_ACTION : "";
				if (nextAction !== "")
				{
					this.setAction(nextAction);
				}

				setTimeout(
					BX.delegate(this.startRequest, this),
					100
				);
			}
			return;
		}

		if (this.state === ProcessState.canceling)
		{
			this.getDialog().setSummary(this.getMessage("RequestCanceled"));
			this.setState(ProcessState.completed);
		}
		else if (status === ProcessResultStatus.completed)
		{
			if (this.getQueueLength() > 0 && this.currentStep + 1 < this.getQueueLength())
			{
				// next
				this.currentStep ++;

				if (this.endpointType === EndpointType.Controller)
				{
					if (Type.isStringFilled(this.queue[this.currentStep].controller))
					{
						this.setController(this.queue[this.currentStep].controller);
					}
					else
					{
						this.setController(this.controllerDefault);
					}
				}

				if (!Type.isStringFilled(this.queue[this.currentStep].action))
				{
					throw new Error("BX.UI.StepProcessing: Could not find controller action at the queue position.");
				}

				if ('finalize' in this.queue[this.currentStep])
				{
					finalize = true;
					this.setAction(this.queue[this.currentStep].action);
				}
				else
				{
					this.setAction(this.queue[this.currentStep].action);

					this.getDialog().setSummary(this.queue[this.currentStep].title);

					setTimeout(
						BX.delegate(this.startRequest, this),
						100
					);
					return;
				}
			}

			if (summary !== "")
			{
				this.getDialog().setSummary(summary, true);
			}
			else
			{
				this.getDialog().setSummary(this.getMessage("RequestCompleted"));
			}

			if (Type.isStringFilled(result.DOWNLOAD_LINK))
			{
				if (Type.isStringFilled(result.DOWNLOAD_LINK_NAME))
				{
					this.getDialog().setMessage('downloadButton', result.DOWNLOAD_LINK_NAME);
				}
				if (Type.isStringFilled(result.CLEAR_LINK_NAME))
				{
					this.getDialog().setMessage('clearButton', result.CLEAR_LINK_NAME);
				}
				this.getDialog().setDownloadButtons(
					result.DOWNLOAD_LINK,
					result.FILE_NAME,
					BX.delegate(function(){
						this.getDialog().resetButtons({stop: true, close: true});
						this.callAction('clear'); //.then
						setTimeout(BX.delegate(function(){
								this.getDialog().resetButtons({close: true});
							}, this), 1000);
					}, this)
				);
			}

			this.setState(ProcessState.completed, result);

			if (finalize)
			{
				setTimeout(
					BX.delegate(this.finalizeRequest, this),
					100
				);
			}
		}
		else
		{
			this.getDialog().setSummary("").setError(this.getMessage("RequestError"));
			this.setState(ProcessState.error);
		}
	}

	/**
	 * @private
	 */
	_onRequestFailure (response: ControllerResponse)
	{
		/*
		// check if it's manual aborting
		if (this.state === ProcessState.canceling)
		{
			return;
		}
		*/
		this.isRequestRunning = false;
		this.ajaxPromise = null;

		// check non auth
		if (
			Type.isPlainObject(response) &&
			('data' in response) && Type.isPlainObject(response.data) &&
			('ajaxRejectData' in response.data) && Type.isPlainObject(response.data.ajaxRejectData) &&
			('reason' in response.data.ajaxRejectData) && (response.data.ajaxRejectData.reason === 'status') &&
			('data' in response.data.ajaxRejectData) && (response.data.ajaxRejectData.data === 401)
		)
		{
			this.getDialog().setError(this.getMessage('AuthError'));
		}
		// check errors
		else if (
			Type.isPlainObject(response) &&
			('errors' in response) &&
			Type.isArrayFilled(response.errors)
		)
		{
			let abortingState = false;
			let networkError = false;
			response.errors.forEach(err => {
				if (err.code === 'NETWORK_ERROR')
				{
					if (this.state === ProcessState.canceling)
					{
						abortingState = true;
					}
					else
					{
						networkError = true;
					}
				}
			});

			// ignoring error of manual aborting
			if (abortingState)
			{
				return;
			}

			if (networkError)
			{
				this.networkErrorCount ++;
				// Let's give it more chance to complete
				if (this.networkErrorCount <= 2)
				{
					setTimeout(
						BX.delegate(this.startRequest, this),
						15000
					);
					return;
				}
			}

			const errors = response.errors.slice(-10);
			let errMessages = [];
			errors.forEach(err => {
				if (err.code === 'NETWORK_ERROR')
				{
					errMessages.push(this.getMessage('RequestError'))
				}
				else
				{
					errMessages.push(err.message)
				}
			});

			this.getDialog().setErrors(errMessages, true);
		}
		else
		{
			this.getDialog().setError(this.getMessage('RequestError'));
		}

		this.xhr = null;
		this.currentStep = -1;

		this.setState(ProcessState.error);
	}

	//endregion

	//region Connection

	/**
	 * @private
	 */
	_closeConnection()
	{
		if (this.xhr instanceof XMLHttpRequest)
		{
			try
			{
				this.xhr.abort();
				this.xhr = null;
			}
			catch (e){}
		}
	}
	/**
	 * @private
	 */
	_onRequestStart(xhr: XMLHttpRequest)
	{
		this.xhr = xhr;
	}

	//endregion

	//region Set & Get

	setId(id: string)
	{
		this.id = id;
		return this;
	}
	getId(): string
	{
		return this.id;
	}

	//region Queue actions

	setQueue(queue: Array<QueueAction>)
	{
		queue.forEach((action: QueueAction) => this.addQueueAction(action));
		return this;
	}
	addQueueAction(action: QueueAction)
	{
		this.queue.push(action);
		return this;
	}
	getQueueLength(): number
	{
		return this.queue.length;
	}

	//endregion

	//region Process options

	setOption(name: $Keys<ProcessOptions>, value: any)
	{
		this.options[name] = value;
		return this;
	}
	getOption(name: $Keys<ProcessOptions>, defaultValue?: any = null): any
	{
		return this.options.hasOwnProperty(name) ? this.options[name] : defaultValue;
	}

	//endregion

	//region Initial fields

	setOptionsFields(optionsFields: {[id: string]: OptionsField})
	{
		Object.keys(optionsFields).forEach(id => this.addOptionsField(id, optionsFields[id]));
		return this;
	}
	addOptionsField(id: string, field: OptionsField)
	{
		this.optionsFields[id] = field;
		return this;
	}
	storeOptionFieldValues(values: {[name: string]: any})
	{
		if ('sessionStorage' in window)
		{
			let valuesToStore = {};
			Object.keys(this.optionsFields).forEach((name: string) => {
				let field: OptionsField = this.optionsFields[name];
				switch (field.type)
				{
					case 'checkbox':
					case 'select':
					case 'radio':
						if (field.name in values)
						{
							valuesToStore[field.name] = values[field.name];
						}
						break;
				}
			});
			window.sessionStorage.setItem('bx.' + this.getId(), JSON.stringify(valuesToStore));
		}
		return this;
	}
	restoreOptionFieldValues()
	{
		let values = {};
		if ('sessionStorage' in window)
		{
			values = JSON.parse(window.sessionStorage.getItem('bx.' + this.getId()));
			if (!Type.isPlainObject(values))
			{
				values = {};
			}
		}
		return values;
	}

	//endregion

	//region Request parameters

	setParams(params: {[name: string]: any})
	{
		this.params = {};
		Object.keys(params).forEach(name => this.setParam(name, params[name]));
		return this;
	}
	getParams(): {[string]:any}
	{
		return this.params;
	}
	setParam(key: string, value: any)
	{
		this.params[key] = value;
		return this;
	}
	getParam(key: string): any | null
	{
		return this.params[key] ? this.params[key] : null;
	}

	//endregion

	//region Process state

	setState(state: $Values<ProcessState>, result?: ProcessResult = {})
	{
		if (this.state === state)
		{
			return this;
		}

		this.state = state;
		if (state === ProcessState.intermediate || state === ProcessState.stopped)
		{
			this.getDialog()
				.lockButton('start', false)
				.lockButton('stop', true)
				.showButton('close', true);
		}
		else if (state === ProcessState.running)
		{
			this.getDialog()
				.lockButton('start', true, true)
				.lockButton('stop', false)
				.showButton('close', false);
		}
		else if (state === ProcessState.canceling)
		{
			this.getDialog()
				.lockButton('start', true)
				.lockButton('stop', true, true)
				.showButton('close', false)
				.hideProgressBar();
		}
		else if (state === ProcessState.error)
		{
			this.getDialog()
				.lockButton('start', true)
				.lockButton('stop', true)
				.showButton('close', true);
		}
		else if (state === ProcessState.completed)
		{
			this.getDialog()
				.lockButton('start', true)
				.lockButton('stop', true)
				.showButton('close', true)
				.hideProgressBar();
		}

		if (this.hasActionHandler(ProcessCallback.StateChanged))
		{
			this.callActionHandler(ProcessCallback.StateChanged, [state, result]);
		}
		else if (this.hasHandler(ProcessCallback.StateChanged))
		{
			this.callHandler(ProcessCallback.StateChanged, [state, result]);
		}

		EventEmitter.emit(ProcessEvent.StateChanged, new BaseEvent({data: {state: state, result: result}}));

		return this;
	}
	getState(): $Values<ProcessState>
	{
		return this.state;
	}

	//endregion

	//region Controller

	setController(controller: string)
	{
		this.controller = controller;
		return this;
	}
	getController(): string
	{
		return this.controller;
	}

	setComponent(component: string, componentMode: 'class'|'ajax' = 'class')
	{
		this.component = component;
		this.componentMode = componentMode;
		return this;
	}
	getComponent(): string
	{
		return this.component;
	}

	setAction(action: string)
	{
		this.action = action;
		return this;
	}
	getAction(): string
	{
		return this.action;
	}
	callAction(action: string): ?Promise
	{
		this.setAction(action)._refreshHash();
		return this.startRequest();
	}

	//endregion

	//region Event handlers

	setHandlers(handlers: {[$Keys<ProcessCallback>]: any => {}})
	{
		Object.keys(handlers).forEach(type => this.setHandler(type, handlers[type]));
		return this;
	}
	setHandler(type: $Keys<ProcessCallback>, handler: any => {})
	{
		if (Type.isFunction(handler))
		{
			this.handlers[type] = handler;
		}
		return this;
	}
	hasHandler(type: $Keys<ProcessCallback>)
	{
		return Type.isFunction(this.handlers[type]);
	}
	callHandler(type: $Keys<ProcessCallback>, args: any)
	{
		if (this.hasHandler(type))
		{
			this.handlers[type].apply(this, args);
		}
	}
	hasActionHandler(type: $Keys<ProcessCallback>)
	{
		if (this.queue[this.currentStep])
		{
			if ('handlers' in this.queue[this.currentStep])
			{
				return Type.isFunction(this.queue[this.currentStep].handlers[type]);
			}
		}
		return false;
	}
	callActionHandler(type: $Keys<ProcessCallback>, args: any)
	{
		if (this.hasActionHandler(type))
		{
			this.queue[this.currentStep].handlers[type].apply(this, args);
		}
	}

	//endregion

	//region lang messages
	setMessages(messages: {[string]: string})
	{
		Object.keys(messages).forEach((id) => this.setMessage(id, messages[id]));
		return this;
	}
	setMessage(id: string, text: string)
	{
		this.messages.set(id, text);
		return this;
	}
	getMessage(id: string, placeholders?: {[string]: string} = null): string
	{
		let phrase = this.messages.has(id) ? this.messages.get(id) : '';
		if (Type.isStringFilled(phrase) && Type.isPlainObject(placeholders))
		{
			Object.keys(placeholders).forEach((placeholder: string) => {
				phrase = phrase.replace('#'+placeholder+'#', placeholders[placeholder]);
			});
		}
		return phrase;
	}

	//endregion
	//endregion

	//region Dialog

	getDialog(): Dialog
	{
		if (!this.dialog)
		{
			this.dialog = new Dialog({
				id: this.id,
				optionsFields: this.getOption('optionsFields', {}),
				minWidth: Number.parseInt(this.getOption('dialogMinWidth', 500)),
				maxWidth: Number.parseInt(this.getOption('dialogMaxWidth', 1000)),
				optionsFieldsValue: this.restoreOptionFieldValues(),
				messages: {
					title: this.getMessage('DialogTitle'),
					summary: this.getMessage('DialogSummary'),
					startButton: this.getMessage('DialogStartButton'),
					stopButton: this.getMessage('DialogStopButton'),
					closeButton: this.getMessage('DialogCloseButton'),
					downloadButton: this.getMessage('DialogExportDownloadButton'),
					clearButton: this.getMessage('DialogExportClearButton')
				},
				showButtons: this.getOption('showButtons'),
				handlers: {
					start: BX.delegate(this.start, this),
					stop: BX.delegate(this.stop, this),
					dialogShown: (typeof(this.handlers.dialogShown) == 'function' ? this.handlers.dialogShown : null),
					dialogClosed: (typeof(this.handlers.dialogClosed) == 'function' ? this.handlers.dialogClosed : null)
				}
			});
		}

		return this.dialog;
	}

	showDialog ()
	{
		this.getDialog()
			.setSetting('optionsFieldsValue', this.restoreOptionFieldValues())
			.resetButtons(this.getOption('optionsFields'))
			.show();

		if (!this.isRequestRunning)
		{
			this.setState(ProcessState.intermediate);
		}

		return this;
	}

	closeDialog ()
	{
		if (this.isRequestRunning)
		{
			this.stop();
		}
		this.getDialog().close();

		return this;
	}

	//endregion
}
