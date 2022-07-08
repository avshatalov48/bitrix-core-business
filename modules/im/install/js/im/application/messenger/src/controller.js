/**
 * Bitrix Im
 * Messenger application
 *
 * @package bitrix
 * @subpackage im
 * @copyright 2001-2020 Bitrix
 */
import {Core} from "im.application.core";
import {Controller} from "im.controller";
import {DialogRestHandler} from "im.provider.rest";

import "./view";

type MessengerApplicationParams = {
	node?: string | HTMLElement,
	userId?: number,
	dialogId?: string | number,
	hasDialog?: boolean
}

export class MessengerApplication
{
	params: MessengerApplicationParams;
	inited: boolean = false;
	initPromise: Promise = null;
	initPromiseResolver: Function = null;
	vueInstance: Object = null;
	controller: Controller = null;
	rootNode: string | HTMLElement = null;

	/* region 01. Initialize */
	constructor(params = {})
	{
		this.initPromise = new Promise((resolve) => {
			this.initPromiseResolver = resolve;
		});
		this.params = params;
		this.rootNode = this.params.node || document.createElement('div');

		this.initCore()
			.then(() => this.initComponent())
			.then(() => this.initComplete())
		;
	}

	initCore()
	{
		return new Promise((resolve) => {
			Core.ready().then(controller => {
				this.controller = controller;
				resolve();
			});
		});
	}

	initComponent()
	{
		this.setInitialApplicationInfo();
		this.setDialogRestHandler();
		this.setApplicationDialogInfo();

		return this.controller.createVue(this, {
			el: this.rootNode,
			data: () =>
			{
				return {
					userId: this.getUserId()
				};
			},
			// language=Vue
			template: `<bx-im-application-messenger :userId="userId" />`,
		})
		.then(vue => {
			this.vueInstance = vue;
			return Promise.resolve();
		});
	}

	initComplete()
	{
		this.inited = true;
		this.initPromiseResolver(this);
	}

	ready()
	{
		if (this.inited)
		{
			return Promise.resolve(this);
		}

		return this.initPromise;
	}

/* endregion 01. Initialize */

/* region 02. Methods */
	setInitialApplicationInfo()
	{
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
	}

	setApplicationDialogInfo()
	{
		const dialog = this.controller.getStore().getters['dialogues/get'](this.getDialogId());
		if (!dialog)
		{
			return false;
		}

		this.controller.getStore().commit('application/set', {
			dialog: {
				chatId: dialog.chatId,
				diskFolderId: dialog.diskFolderId || 0
			}
		});
	}

	setDialogRestHandler()
	{
		this.controller.addRestAnswerHandler(
			DialogRestHandler.create({
				store: this.controller.getStore(),
				controller: this.controller,
				context: this,
			})
		);
	}

	getUserId()
	{
		const userId = this.params.userId || this.getLocalize('USER_ID');

		return userId? Number.parseInt(userId, 10): 0;
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

	addLocalize(phrases)
	{
		return this.controller.addLocalize(phrases);
	}

	getLocalize(name)
	{
		return this.controller.getLocalize(name);
	}
/* endregion 02. Methods */
}