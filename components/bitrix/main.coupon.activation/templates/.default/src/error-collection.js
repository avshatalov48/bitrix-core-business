import {Loc, Type} from "main.core";

export class ErrorCollection
{
	#errors: Array = [];
	#balloon: ?BX.UI.Notification.Balloon;

	constructor(errors: Array = []) {
		this.addErrors(errors);
	}

	addErrors(errors: Array)
	{
		this.#errors = [...this.#errors, ...errors];
	}

	cleanErrors()
	{
		this.#errors = [];
	}

	hideErrors()
	{
		if (!Type.isNil(this.#balloon))
		{
			this.#balloon.activateAutoHide();
		}
	}

	show()
	{
		if (this.#errors.length <= 0)
		{
			return;
		}

		this.#balloon = BX.UI.Notification.Center.notify({
			content: [
				`<strong>${Loc.getMessage('MAIN_COUPON_ACTIVATION_TITLE_ERROR')}</strong><br>`,
				this.#errors.map((value) => {
					return value.message;
				}).join('</br>'),
			].join(''),
			position: 'top-right',
			category: 'menu-self-item-popup',
			autoHideDelay: 300000
		});

	}
}