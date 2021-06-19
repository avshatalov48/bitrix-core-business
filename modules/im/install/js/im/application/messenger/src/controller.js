/**
 * Bitrix Im
 * Messenger application
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */

// vue
import {VueVendorV2} from "ui.vue";

// im
import {Core} from "im.application.core";
import {DialogRestHandler} from "im.provider.rest";

// core
import "promise";

// component
import "./view";

export class MessengerApplication
{
	/* region 01. Initialize */

	constructor(params = {})
	{
		this.inited = false;
		this.initPromise = new BX.Promise;

		this.params = params;

		this.template = null;
		this.rootNode = this.params.node || document.createElement('div');

		this.event = new VueVendorV2;

		this.initCore()
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

	initComponent()
	{
		console.log('2. initComponent');

		this.controller.getStore().commit('application/set', {
			dialog: {
				dialogId: this.getDialogId()
			},
			options: {
				quoteEnable: true,
				autoplayVideo: true,
				darkBackground: false
			}
		});

		this.controller.addRestAnswerHandler(
			DialogRestHandler.create({
				store: this.controller.getStore(),
				controller: this.controller,
				context: this,
			})
		);

		let dialog = this.controller.getStore().getters['dialogues/get'](this.controller.application.getDialogId());
		if (dialog)
		{
			this.controller.getStore().commit('application/set', {dialog: {
				chatId: dialog.chatId,
				diskFolderId: dialog.diskFolderId || 0
			}});
		}

		return this.controller.createVue(this, {
			el: this.rootNode,
			data: () =>
			{
				return {
					userId: this.getUserId(),
					dialogId: this.getDialogId()
				};
			},
			// language=Vue
			template: `<bx-im-application-messenger :userId="userId" :initialDialogId="dialogId"/>`,
		})
		.then(vue => {
			this.template = vue;
			return new Promise((resolve, reject) => resolve());
		});
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

/* region 02. Methods */

	getUserId()
	{
		let userId = this.params.userId || this.getLocalize('USER_ID');
		return userId? parseInt(userId): 0;
	}

	getDialogId()
	{
		return this.params.dialogId? this.params.dialogId.toString(): "0";
	}

	getHost()
	{
		return location.origin || '';
	}

	getSiteId()
	{
		return 's1';
	}

/* endregion 02. Methods */

/* region 03. Utils */

	addLocalize(phrases)
	{
		return this.controller.addLocalize(phrases);
	}

	getLocalize(name)
	{
		return this.controller.getLocalize(name);
	}

/* endregion 03. Utils */
}