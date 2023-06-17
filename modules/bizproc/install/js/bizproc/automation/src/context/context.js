import { Type, Runtime } from "main.core";
import { BaseContext } from "./base-context";
import {Document, UserOptions, Tracker, AutomationGlobals} from "bizproc.automation";
export class Context extends BaseContext
{
	constructor(props: {
		document: Document,
		signedDocument: string,
		ajaxUrl: string,
		availableRobots?: Array<Object>,
		availableTriggers?: Array<Object>,
		canManage?: boolean,
		canEdit?: boolean,
		userOptions?: UserOptions,
		tracker?: Tracker,

		bizprocEditorUrl?: string,
		constantsEditorUrl?: string,
		parametersEditorUrl?: string,

		marketplaceRobotCategory?: string,
	})
	{
		super(props);
	}

	clone(): this
	{
		// TODO - clone Tracker object when the corresponding method appears
		return (new Context(Runtime.clone(this.getValues())))
			.set('document', this.document.clone())
			.set('userOptions', this.userOptions?.clone())
		;
	}

	get document(): ?Document
	{
		return this.get('document');
	}

	get signedDocument(): string
	{
		return this.get('signedDocument') ?? '';
	}

	get ajaxUrl(): string
	{
		return this.get('ajaxUrl') ?? '';
	}

	get availableRobots(): Array<Object>
	{
		const availableRobots = this.get('availableRobots');
		if (Type.isArray(availableRobots))
		{
			return availableRobots;
		}

		return [];
	}

	get availableTriggers(): Array<Object>
	{
		const availableTriggers = this.get('availableTriggers');
		if (Type.isArray(availableTriggers))
		{
			return availableTriggers;
		}

		return [];
	}

	get canManage(): boolean
	{
		const canManage = this.get('canManage');

		return Type.isBoolean(canManage) && canManage;
	}

	get canEdit(): boolean
	{
		const canEdit = this.get('canEdit');

		return Type.isBoolean(canEdit) && canEdit;
	}

	get userOptions(): ?UserOptions
	{
		return this.get('userOptions');
	}

	get tracker(): ?Tracker
	{
		return this.get('tracker');
	}

	set tracker(tracker: Tracker)
	{
		this.set('tracker', tracker);
	}

	get bizprocEditorUrl(): ?string
	{
		return this.get('bizprocEditorUrl');
	}

	get constantsEditorUrl(): ?string
	{
		return this.get('constantsEditorUrl');
	}

	get parametersEditorUrl(): ?string
	{
		return this.get('parametersEditorUrl');
	}

	getAvailableTrigger(code: string): ?Object
	{
		return this.availableTriggers.find(trigger => trigger['CODE'] === code);
	}

	get automationGlobals(): ?AutomationGlobals
	{
		return this.get('automationGlobals');
	}
}
