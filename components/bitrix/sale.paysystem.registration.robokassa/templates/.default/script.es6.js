import {Reflection, Type, Event} from 'main.core';
import {EventEmitter} from "main.core.events";

const namespace = Reflection.namespace('BX.Sale.Component');

class RegistrationRobokassa
{
	#rkReg = true;

	#originUrl = 'https://reg.robokassa.ru';
	#url = 'https://reg.robokassa.ru/form_register_merch_bitrix.php';

	#robokassaWindow = null;
	#button = null;

	#siteUrl = '';
	#resultUrl = '';
	#successUrl = '';
	#failUrl = '';
	#callbackUrl = '';

	constructor(options: {
		buttonId: string,
		siteUrl: string,
		resultUrl: string,
		successUrl: string,
		failUrl: string,
		callbackUrl: string,
	})
	{
		if (Type.isPlainObject(options))
		{
			this.#siteUrl = options.siteUrl;
			this.#resultUrl = options.resultUrl;
			this.#successUrl = options.successUrl;
			this.#failUrl = options.failUrl;
			this.#callbackUrl = options.callbackUrl;

			this.#button = BX(options.buttonId);
		}
	}

	run()
	{
		RegistrationRobokassa.#subscribeToEvent();

		Event.ready(() => {
			window.addEventListener('message', event => {
				if (
					event.origin === this.#originUrl
					&& event.data.rk_reg_ready === true
					&& this.#robokassaWindow
				)
				{
					this.#sendData(this.#robokassaWindow);
				}
			}, false);


			if (this.#button)
			{
				this.#button.onclick = () => this.#openForm();
			}
		});
	}

	#openForm()
	{
		this.#robokassaWindow = BX.util.popup(this.#url, 800, 600);
		this.#robokassaWindow.focus({ preventScroll: true });
	}

	#sendData(robokassaWindow)
	{
		const data = {
			rk_reg: this.#rkReg,
			site_url: this.#siteUrl,
			result_url: this.#resultUrl,
			success_url: this.#successUrl,
			fail_url: this.#failUrl,
			callback_url: this.#callbackUrl,
		};
		robokassaWindow.postMessage(data, '*');
	}

	static #subscribeToEvent()
	{
		const inCompatMode = {compatMode: true};

		EventEmitter.subscribe('onPullEvent-sale', (command, params) => {
			if (command !== 'on_add_paysystem_settings_robokassa')
			{
				return;
			}

			EventEmitter.emit(window, 'BX.Sale.PaySystem.Registration.Robokassa:onAddSettings', params);
		}, inCompatMode);
	}
}

namespace.RegistrationRobokassa = RegistrationRobokassa;