import { Dom, Tag, ajax, Type, Loc } from 'main.core';
import { Loader } from 'main.loader';
import { EventEmitter } from 'main.core.events';
import { FormConstructor } from 'rest.form-constructor';

export class AppSettings extends EventEmitter
{
	#formConstructor: FormConstructor;
	#handler: string;
	#redirect: ?string
	#clientId: ?string
	#wrapper: HTMLElement;
	#loader: Loader
	#overlay: HTMLElement;

	constructor(options)
	{
		super();
		if (!(options.formConstructor instanceof FormConstructor))
		{
			throw new Error('"formConstructor" is required parameters')
		}

		this.#redirect = null;
		this.#wrapper = Type.isElementNode(options.wrapper) ? options.wrapper : null;
		this.setFormConstructor(options.formConstructor);
		this.#handler = Type.isStringFilled(options.handler) ? options.handler : null;
		this.#clientId = Type.isStringFilled(options.clientId) ? options.clientId : null;
		this.setRedirect(options.redirect);
		this.#loader = new Loader({
			target: this.#wrapper
		});
		this.#overlay = Tag.render`<div class="rest-app-settings-overlay"></div>`
	}

	setRedirect(url: string)
	{
		const reqExp = new RegExp('^(?:/|https?://' + location.host + ')', "g");
		if (Type.isStringFilled(url) && !!url.match(reqExp))
		{
			this.#redirect = url;
		}
	}

	show(): void
	{
		if (Type.isNil(this.#wrapper))
		{
			throw new Error('Property "wrapper" is undefined')
		}

		this.#formConstructor.renderTo(this.#wrapper);
		BX.UI.ButtonPanel.show();
	}

	subscribeEvents(): void
	{
		if (!(this.#formConstructor instanceof FormConstructor))
		{
			return;
		}
		EventEmitter.subscribe(
			EventEmitter.GLOBAL_TARGET,
			'button-click',
			(event) => {
				const [clickedBtn] = event.data;
				if (clickedBtn.TYPE === 'save')
				{
					const data = {
						clientId: this.#clientId,
						settings: this.#formConstructor.getFormData(),
						handler: this.#handler
					};
					this.save(data);
				}
			},
		);
		this.#formConstructor.subscribe('onFieldChange', () => {
			this.reload();
		});
	}

	unsubscribeEvents(): void
	{
		if (!(this.#formConstructor instanceof FormConstructor))
		{
			return;
		}

		EventEmitter.unsubscribeAll(EventEmitter.GLOBAL_TARGET, 'button-click');
		this.#formConstructor.unsubscribeAll('onSave');
		this.#formConstructor.unsubscribeAll('onFieldChange');
	}

	setFormConstructor(formConstructor: FormConstructor): void
	{
		this.unsubscribeEvents();
		this.#formConstructor = formConstructor;
		this.subscribeEvents();
	}

	reload(): void
	{
		Dom.append(this.#overlay,this.#wrapper);
		this.#loader.show();

		if (Type.isNil(this.#clientId))
		{
			console.log('Property "clientId" is undefined');
			return;
		}
		ajax.runComponentAction('bitrix:rest.app.settings', 'reload',{
			mode: 'class',
			data: {
				clientId: this.#clientId,
				settings: this.#formConstructor.getFormData()
			},
		}).then((response) => {
			const data = response.data;
			this.setFormConstructor(new FormConstructor({
				steps: data.STEPS,
			}));
			this.#handler = Type.isStringFilled(data.HANDLER) ? data.HANDLER : this.#handler;
			this.#clientId = Type.isStringFilled(data.CLIENT_ID) ? data.CLIENT_ID : this.#clientId;
			this.setRedirect(data.REDIRECT);

			this.show();
			this.#loader.hide();
			Dom.remove(this.#overlay);
		}).catch((response) => {
			console.log(response.errors);
			this.#formConstructor.showTextInBalloon(Loc.getMessage('REST_APP_SETTINGS_ERROR'));
		});
	}

	isReadySave(): boolean
	{
		let isAllFieldReady = true;

		this.#formConstructor.getFields().forEach((field) => {
			if (!field.isReadySave())
			{
				isAllFieldReady = false;
			}
		});

		return isAllFieldReady;
	}

	save(data): void
	{
		ajax.runAction('rest.einvoice.save', {
			mode: 'class',
			data: data,
		}).then(() => {
			if (Type.isNil(this.#redirect))
			{
				top.BX.SidePanel.Instance.close();
			}
			else
			{
				top.document.location.href = this.#redirect;
			}

			const buttonWaitState = BX.UI.ButtonPanel.getContainer().querySelector('.ui-btn-wait');
			Dom.removeClass(buttonWaitState, 'ui-btn-wait');
		}).catch((response) => {
			const errors = response.errors;
			let { fieldErrors, otherErrors } = AppSettings.formatErrors(errors);
			this.#formConstructor.showFieldErrors(fieldErrors);
			if (Type.isArrayFilled(otherErrors))
			{
				this.#formConstructor.showTextInBalloon(Loc.getMessage('REST_APP_SETTINGS_ERROR'));
			}

			const buttonWaitState = BX.UI.ButtonPanel.getContainer().querySelector('.ui-btn-wait');

			if (buttonWaitState)
			{
				Dom.removeClass(buttonWaitState, 'ui-btn-wait');
			}
		});
	}

	static formatErrors(errors: Array): Object
	{
		let fieldErrors = {};
		let otherErrors = [];
		errors.forEach((error) => {
			if (Type.isStringFilled(error.customData?.fieldName))
			{
				Array.isArray(fieldErrors[error.customData?.fieldName]) ?
					fieldErrors[error.customData?.fieldName].push(error.message) :
					fieldErrors[error.customData?.fieldName] = [error.message];
			}
			else
			{
				otherErrors.push(error.message)
			}
		});

		return {
			fieldErrors: fieldErrors,
			otherErrors: otherErrors
		};
	}
}