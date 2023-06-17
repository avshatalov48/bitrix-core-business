import {ajax} from "main.core";
import {EventEmitter} from "main.core.events";

export class Request
{
	#componentName = 'bitrix:main.coupon.activation';
	#action = 'activate';
	#method = 'POST';
	#mode = 'class';

	constructor(action: string, method: string = 'POST', mode: string = 'class') {
		this.#action = action;
		this.#method = method;
		this.#mode = mode;
	}

	send(data = {})
	{
		return ajax.runComponentAction(this.#componentName, this.#action, {
			mode: this.#mode,
			data: data,
			method: this.#method,
		})
	}
}