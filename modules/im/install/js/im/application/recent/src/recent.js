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

// vue components
import 'im.component.recent';

export class RecentApplication
{
	/* region 01. Initialize */

	constructor(params = {})
	{
		this.inited = false;
		this.initPromise = new BX.Promise;

		this.params = params;

		this.template = null;
		this.rootNode = this.params.node || document.createElement('div');

		this.isMessenger = params.hasDialog === true;

		this.templateTemp = null;
		this.rootNodeTemp = this.params.nodeTemp || document.createElement('div');

		this.eventBus = new VueVendorV2;

		this.initCore()
			.then(result => this.initParams(result))
			.then(() => this.initComponent())
			.then(() => this.initComplete())
		;
	}

	initCore()
	{
		return new Promise((resolve, reject) => {
			Core.ready().then(controller => {
				this.controller = controller;
				resolve();
			})
		});
	}

	initParams(controller)
	{
		return new Promise((resolve, reject) => resolve());
	}

	initComponent()
	{
		return this.controller.createVue(this, {
			el: this.rootNode,
			template: `<bx-im-component-recent :hasDialog="${this.isMessenger}"/>`,
		})
		.then(vue => {
			this.template = vue;
			return new Promise((resolve, reject) => resolve());
		})
	}

	initComplete()
	{
		this.inited = true;
		this.initPromise.resolve(this);
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