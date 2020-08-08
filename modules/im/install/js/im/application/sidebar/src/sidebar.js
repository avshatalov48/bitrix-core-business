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
import './component/bx-messenger-sidebar';
import './sidebar.css';
import "im.view.list.recent";
import "im.view.list.sidebar";

export class SidebarApplication
{
	/* region 01. Initialize */

	constructor(params = {})
	{
		this.inited = false;
		this.initPromise = new BX.Promise;

		this.params = params;

		this.template = null;
		this.rootNode = this.params.node || document.createElement('div');

		this.templateTemp = null;
		this.rootNodeTemp = this.params.nodeTemp || document.createElement('div');

		this.eventBus = new VueVendorV2;

		this.initCore()
			.then(() => this.initParams())
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

	initParams()
	{
		return new Promise((resolve, reject) => resolve());
	}

	initComponent()
	{
		return this.controller.createVue(this, {
			el: this.rootNode,
			template: `<bx-im-component-sidebar/>`,
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

		return this.requestData();
	}

	requestData()
	{
		this.controller.recent.drawPlaceholders();
		this.controller.recent.getRecentData();

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