import { Dom, Loc, Tag, Text, Type } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { HelpMessage } from 'ui.section';

export class BaseField extends EventEmitter
{
	#id: string;
	#inputName: string;
	field: HTMLElement;
	#isEnable: boolean;
	#bannerCode: ?string;
	#helpDeskCode: ?string;
	#label: string = '';
	#helpMessageProvider: function;
	#helpMessage: ?HelpMessage = null;
	#errorContainer: HTMLElement;
	#isFieldDisabled: boolean = false;

	constructor(params)
	{
		super();
		this.setEventNamespace('UI.Section');

		this.#label = Type.isStringFilled(params.label) ? params.label : '';
		if (Type.isStringFilled(params.id))
		{
			this.#id = params.id;
		}
		else if (!this.id)
		{
			this.#id = this.prefixId() + Text.getRandom(8);
		}

		if (Type.isStringFilled(params.inputName))
		{
			this.#inputName = params.inputName;
		}
		else if (!this.#inputName)
		{
			this.#inputName = Text.getRandom(8);
		}
		this.#isEnable = params.isEnable !== false;
		this.#bannerCode = Type.isStringFilled(params.bannerCode) ? params.bannerCode : null;
		this.#helpDeskCode = Type.isStringFilled(params.helpDesk) ? params.helpDesk : null;
		this.#helpMessageProvider = params.helpMessageProvider;
		this.#isFieldDisabled = Type.isBoolean(params.isFieldDisabled) ? params.isFieldDisabled : false;
	}

	getHelpMessage(): ?HelpMessage
	{
		if (this.#helpMessage instanceof HelpMessage)
		{
			return this.#helpMessage;
		}
		this.#helpMessage = Type.isFunction(this.#helpMessageProvider)
			? this.#helpMessageProvider(this.getId(), this.getInputNode())
			: null;

		return this.#helpMessage;
	}

	cleanError()
	{
		Dom.clean(this.#errorContainer);
		Dom.removeClass(this.getErrorBox(), '--error');
	}

	setErrors(errorMessages): void
	{
		this.cleanError();
		Dom.addClass(this.getErrorBox(), '--error');
		for (let message of errorMessages)
		{
			let error = Tag.render`
				<div class="ui-section__error-message">
					<span class="ui-icon-set --warning"></span>
					<span>${message}</span>
				</div>
			`;
			Dom.append(error, this.renderErrors());
		}
	}

	getErrorBox(): HTMLElement
	{
		return this.getInputNode();
	}

	renderErrors()
	{
		if (this.#errorContainer)
		{
			return this.#errorContainer;
		}

		this.#errorContainer = Tag.render`<div class="ui-section__error-container"></div>`;

		return this.#errorContainer;
	}

	getId(): string
	{
		return this.#id;
	}

	getLabel(): string
	{
		return this.#label;
	}

	prefixId(): string
	{
		return '';
	}

	getValue(): string
	{
		return '';
	}

	getName(): string
	{
		return this.#inputName;
	}

	getInputNode(): HTMLElement
	{
		return null;
	}

	setName(name: string): void
	{
		this.#inputName = name;
	}

	cancel(): void {}

	render(): HTMLElement
	{
		if (this.field)
		{
			return this.field;
		}
		this.field = this.renderContentField();

		return this.field;
	}

	renderContentField(): HTMLElement
	{
		return Tag.render``;
	}

	isEnable(): boolean
	{
		return this.#isEnable;
	}

	getBannerCode(): ?string
	{
		return this.#bannerCode;
	}

	showBanner(): void
	{
		if (this.getBannerCode())
		{
			BX.UI.InfoHelper.show(this.getBannerCode());
		}
	}

	getHelpdeskCode(): ?string
	{
		return this.#helpDeskCode;
	}

	showHelpdesk(): void
	{
		if (this.getHelpdeskCode())
		{
			top.BX.Helper.show(this.getHelpdeskCode());
		}
	}

	renderLockElement(): HTMLElement
	{
		const lockElement = Tag.render`<span class="ui-icon-set --lock field-has-lock"></span>`;

		lockElement.addEventListener('click', () => {
			this.showBanner()
		});

		return lockElement
	}

	renderMoreElement(helpdeskCode): HTMLElement
	{
		return Tag.render`
			${this.getMoreElement(helpdeskCode)}
		`;
	}

	getMoreElement(helpdeskCode): string
	{
		return `
			<a class="more" href="javascript:top.BX.Helper.show('${helpdeskCode}');">
				${Loc.getMessage('INTRANET_SETTINGS_CANCEL_MORE')}
			</a>
		`;
	}

	isFieldDisabled(): boolean
	{
		return this.#isFieldDisabled;
	}
}
