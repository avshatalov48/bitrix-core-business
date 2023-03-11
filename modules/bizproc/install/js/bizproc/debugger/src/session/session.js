import {Type, Text, ajax} from 'main.core';
import {SessionOptions} from './session-options';
import {Mode, ModeOptions} from './mode';
import {Document} from './document';
import {Helper} from '../helper';
import {BaseEvent, EventEmitter} from "main.core.events";
import {Manager} from "bizproc.debugger";

export default class Session extends EventEmitter
{
	#id: string = '';
	#mode: ModeOptions = {};
	#startedBy: number = 0;
	#active: boolean;
	#fixed: boolean;
	#documents: Array<Document> = [];
	#shortDescription: string = '';
	#categoryId: number = 0;

	#documentSigned: string = '';

	#finished: boolean = false;
	#pullFinishHandler: Function;
	#pullDocumentValuesHandler: Function;

	constructor(options: SessionOptions)
	{
		super();
		this.setEventNamespace('BX.Bizproc.Debugger.Session');

		options = Type.isPlainObject(options) ? options : {};

		this.#id = options.Id;
		this.#setMode(options.Mode);
		this.#startedBy = parseInt(options.StartedBy) >= 0 ? parseInt(options.StartedBy) : 0;
		this.#shortDescription = String(options.ShortDescription);
		this.#active = Boolean(options.Active);
		this.#fixed = Boolean(options.Fixed);
		this.#categoryId = Text.toInteger(options.CategoryId);

		this.#setDocuments(options.Documents);

		if (this.isActive())
		{
			this.#pullFinishHandler = this.#handleExternalFinished.bind(this);
			Manager.Instance.pullHandler.subscribe('sessionFinish', this.#pullFinishHandler);

			this.#pullDocumentValuesHandler = this.#handleExternalDocumentValues.bind(this);
			Manager.Instance.pullHandler.subscribe('documentValues', this.#pullDocumentValuesHandler);
		}
	}

	set documentSigned(documentSigned: string)
	{
		if (this.isFixed() && this.activeDocument)
		{
			this.activeDocument.documentSigned = documentSigned;
		}

		this.#documentSigned = documentSigned;
	}

	get documentSigned(): string
	{
		if (this.activeDocument)
		{
			const documentSigned = this.activeDocument.documentSigned;
			if (Type.isStringFilled(documentSigned))
			{
				return documentSigned;
			}
		}

		return this.#documentSigned;
	}

	#setMode(modeId: string | number)
	{
		modeId = Helper.isNumeric(modeId) ? Number(modeId) : null;

		if (Mode.isMode(modeId))
		{
			this.#mode = Mode.getMode(modeId);
		}
	}

	#setDocuments(documents: Array): this
	{
		if (Type.isArrayFilled(documents))
		{
			documents.forEach((document) => {
				this.#documents.push(
					new Document(document)
				);
			})
		}

		return this;
	}

	get id(): string
	{
		return this.#id;
	}

	get startedBy(): number
	{
		return this.#startedBy;
	}

	get activeDocument(): ?Document
	{
		if (this.#documents.length === 1)
		{
			return this.#documents[0];
		}

		return null;
	}

	get modeId(): number
	{
		return this.#mode.id;
	}

	get shortDescription(): string
	{
		return this.#shortDescription;
	}

	get initialCategoryId(): number
	{
		return this.#categoryId;
	}

	isActive(): boolean
	{
		return this.#active;
	}

	isFixed(): boolean
	{
		return this.#fixed;
	}

	isSessionStartedByUser(userId: number): boolean
	{
		return this.startedBy === userId;
	}

	isAutomation(): boolean
	{
		return true;
	}

	isInterceptionMode(): boolean
	{
		return this.modeId === Mode.interception.id;
	}

	isExperimentalMode(): boolean
	{
		return this.modeId === Mode.experimental.id;
	}

	static start(documentSigned: string, modeId: number): Promise
	{
		return new Promise((resolve, reject) => {
			ajax.runAction(
				'bizproc.debugger.startSession',
				{
					data: {
						documentSigned,
						mode: modeId,
					},
					analyticsLabel: {
						automation_select_debug_mode: 'Y',
						mode_type: Mode.getMode(modeId).code,
					}
				}
			).then(
				(response) => {
					const session = new Session(response.data.session);
					session.documentSigned = response.data.documentSigned;

					resolve(session);
				},
				reject
			)
		});
	}

	finish(options: Object = {}): Promise
	{
		return this.#innerFinish(options).then(response => {
			this.#handleFinish();

			return response;
		});
	}

	#innerFinish(options: Object = {}): Promise
	{
		return ajax.runAction(
			'bizproc.debugger.finishDebugSession',
			{
				json: {
					sessionId: this.id,
					...options
				}
			}
		);
	}

	fixateDocument(id: number): Promise
	{
		return ajax.runAction(
			'bizproc.debugger.fixateSessionDocument',
			{
				data: {
					documentId: id
				}
			}
		).then(response => {
			this.documentSigned = response.data.documentSigned;
			this.#updateSession(response.data.session);

			this.emit('onAfterDocumentFixed');

			return response;
		});
	}

	removeDocuments(ids: Array = []): Promise
	{
		return ajax.runAction(
			'bizproc.debugger.removeSessionDocument',
			{
				data:{
					documentIds: ids,
				}
			}
		).then(response => {
			this.#updateSession(response.data.session);

			return response;
		});
	}

	#handleExternalFinished(event: BaseEvent)
	{
		const sessionId: string = event.getData().sessionId;
		if (sessionId === this.id)
		{
			this.#handleFinish();
		}
	}

	#handleExternalDocumentValues(event: BaseEvent)
	{
		if (!this.activeDocument)
		{
			return;
		}

		const values: {} = event.getData().rawValues;
		const categoryId = values['CATEGORY_ID'];

		if (categoryId)
		{
			this.activeDocument.categoryId = Text.toInteger(categoryId);

			//TODO: refactoring candidate
			Manager.Instance.requireSetFilter(this);
		}
	}

	#handleFinish()
	{
		if (!this.#finished)
		{
			this.#finished = true;
			this.emit('onFinished');
			this.unsubscribeAll();
			if (this.#pullFinishHandler)
			{
				Manager.Instance.pullHandler.unsubscribe('sessionFinish', this.#pullFinishHandler);
				this.#pullFinishHandler = null;
			}
			if (this.#pullDocumentValuesHandler)
			{
				Manager.Instance.pullHandler.unsubscribe('documentValues', this.#pullDocumentValuesHandler);
				this.#pullDocumentValuesHandler = null;
			}
		}
	}

	#updateSession(options ={})
	{
		if (Object.keys(options).length <= 0)
		{
			return;
		}

		this.#active = Boolean(options.Active);
		this.#fixed = Boolean(options.Fixed);
		this.#categoryId = Text.toInteger(options.CategoryId);

		this.#setDocuments(options.Documents);
	}
}