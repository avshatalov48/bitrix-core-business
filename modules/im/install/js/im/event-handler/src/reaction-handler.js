import { EventType, RestMethod } from "im.const";
import {Logger} from "im.lib.logger";
import { EventEmitter } from "main.core.events";

export class ReactionHandler
{
	static types = {
		none: 'none',
		like: 'like',
		kiss: 'kiss',
		laugh: 'laugh',
		wonder: 'wonder',
		cry: 'cry',
		angry: 'angry'
	};

	static actions = {
		auto: 'auto',
		plus: 'plus',
		minus: 'minus',
		set: 'set'
	};

	constructor($Bitrix)
	{
		this.store = $Bitrix.Data.get('controller').store;
		this.restClient = $Bitrix.RestClient.get();

		this.onSetMessageReactionHandler = this.onSetMessageReaction.bind(this);
		this.onOpenMessageReactionListHandler = this.onOpenMessageReactionList.bind(this);
		EventEmitter.subscribe(EventType.dialog.setMessageReaction, this.onSetMessageReactionHandler);
		EventEmitter.subscribe(EventType.dialog.openMessageReactionList, this.onOpenMessageReactionListHandler);
	}

	onSetMessageReaction({data})
	{
		this.reactToMessage(data.message.id, data.reaction);
	}

	onOpenMessageReactionList({data})
	{
		this.openMessageReactionList(data.message.id, data.values);
	}

	reactToMessage(messageId, reaction)
	{
		// let type = reaction.type || ReactionHandler.types.like;
		let action = reaction.action || ReactionHandler.actions.auto;
		if (action !== ReactionHandler.actions.auto)
		{
			action = action === ReactionHandler.actions.set ? ReactionHandler.actions.plus : ReactionHandler.actions.minus;
		}
		this.restClient.callMethod(RestMethod.imMessageLike, {
			'MESSAGE_ID': messageId,
			'ACTION': action
		});
	}

	openMessageReactionList(messageId, values)
	{
		Logger.warn('Message reaction list not implemented yet!', messageId, values);
	}

	destroy()
	{
		EventEmitter.unsubscribe(EventType.dialog.setMessageReaction, this.onSetMessageReactionHandler);
		EventEmitter.unsubscribe(EventType.dialog.openMessageReactionList, this.onOpenMessageReactionListHandler);
	}
}