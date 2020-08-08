/**
 * Bitrix Im
 * Core application
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */
import {Core} from "im.application.core";
import {VueVendorV2} from "ui.vue";
import {Logger} from "im.lib.logger";

export class ExampleApplication
{
	/* region 01. Initialize */

	constructor(params = {})
	{
		this.inited = false;
		this.initPromise = new BX.Promise;

		this.params = params;

		this.rootNode = this.params.node || document.createElement('div');

		this.template = null;

		this.eventBus = new VueVendorV2;

		Core.ready()
			.then(result => this.initParams(result))
			.then(() => this.initComponent())
			.then(() => this.initComplete())
		;
	}

	initParams(controller)
	{
		this.controller = controller;

		return new Promise((resolve, reject) => resolve());
	}

	initComponent()
	{
		return this.controller.createVue(this, {
			el: this.rootNode,
			template: `<div>test2 {{store.application.common.host}}</div>`,
			computed:
			{
				store()
				{
					return this.$store.state;
				}
			},
		}).then(vue => {
			this.template = vue;
			return new Promise((resolve, reject) => resolve());
		});
	}

	initComplete()
	{
		this.inited = true;
		this.initPromise.resolve(this);

		return this.requestData();
	}

	requestData()
	{
		Logger.log('Requested data!');

		return new Promise((resolve, reject) => resolve());
	}

	ready()
	{
		if (this.inited)
		{
			let promise = new BX.Promise;
			promise.resolve(this);

			return promise;
		}

		return this.initPromise;
	}

	/* endregion 01. Initialize */

	/* region 02. Event Bus */
	emit(eventName, params = {})
	{
		this.eventBus.$emit(eventName, params);

		return true;
	}

	listen(eventName, callback)
	{
		if (typeof callback !== 'function')
		{
			return false;
		}

		this.eventBus.$on(eventName, callback);

		return true;
	}
	/* endregion 02. Event Bus */
}