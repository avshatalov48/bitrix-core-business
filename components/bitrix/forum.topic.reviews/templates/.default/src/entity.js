import Form from './form';
import {Event, Runtime, Tag, ajax, Uri} from 'main.core';
import {EventEmitter} from 'main.core.events';

export default class Entity
{
	constructor({formId, container, preorder, ajaxPost})
	{
		this.formId = formId;
		this.container = container;
		this.preorder = preorder === true;
		this.ajaxPost = ajaxPost === true;
		this.reply = this.reply.bind(this);
		this.quote = this.quote.bind(this);
		this.parseResponse = this.parseResponse.bind(this);
		this.init();
	}

	init()
	{
		this.container.querySelectorAll("[data-bx-role=add-new-message]").forEach((node) => {
			node.addEventListener('click', () => {
				this.reply({node: null});
			});
		});
		this.bindMessages();
		this.bindNavigation();

		EventEmitter.subscribe(this, 'onForumCommentAdded', this.parseResponse);
		EventEmitter.subscribeOnce(this, 'onForumCommentFormShow', function() {
			this.container.querySelectorAll("[data-bx-role=add-new-message]").forEach((node) => {
				node.parentNode.removeChild(node);
			});
		}.bind(this));
	}

	bindMessages()
	{
		this.container.querySelectorAll('table').forEach((node) => {
			node.querySelectorAll('a[data-bx-act]').forEach((actNode) => {
				const action = actNode.dataset.bxAct;
				if (action === 'reply')
				{
					Event.bind(actNode, 'click', (event) => {
						this.reply({node: node});
					});
				}
				else if (action === 'quote')
				{
					Event.bind(actNode, 'click', (event) => {
						this.quote({node: node});
					});
				}
				else if (action === 'hide' || action === 'show')
				{
					Event.bind(actNode, 'click', (event) => {
						this.moderate({node: node, action: action, actNode: actNode});
						event.stopPropagation();
						event.preventDefault();
					})
				}
				else if (action === 'del')
				{
					Event.bind(actNode, 'click', (event) => {
						this.delete({node: node});
						event.stopPropagation();
						event.preventDefault();
					})
				}
			});
		});
	}

	bindNavigation()
	{
		if (!this.ajaxPost)
		{
			return;
		}

		this.container
			.querySelector('div[data-bx-role=navigation-container-top]')
			.querySelectorAll('a')
			.forEach((node) => {
				Event.bindOnce(node, 'click', (event) => {
					this.navigate({node: node});
					event.stopPropagation();
					event.preventDefault();
				});
			});
		this.container
			.querySelector('div[data-bx-role=navigation-container-bottom]')
			.querySelectorAll('a')
			.forEach((node) => {
				Event.bind(node, 'click', (event) => {
					this.navigate({node: node});
					event.stopPropagation();
					event.preventDefault();
				});
			});
	}

	parseResponse({data})
	{
		Runtime.html(
			this.container.querySelector('div[data-bx-role=messages-container]'),
			data.messages
		);

		Runtime.html(
			this.container.querySelector('div[data-bx-role=navigation-container-top]'),
			data.navigationTop
		);
		Runtime.html(
			this.container.querySelector('div[data-bx-role=navigation-container-bottom]'),
			data.navigationBottom
		);
		setTimeout(function(messageId) {
			this.bindMessages();
			this.bindNavigation();
			if (messageId > 0)
			{
				BX.scrollToNode(this.container.querySelector('table[id=message' + messageId + ']'));
			}
		}.bind(this), 0, data.messageId)
	}

	getPlaceholder(/*messageId*/)
	{
		return this.container.querySelector("[data-bx-role=placeholder]");
	}

	navigate({node})
	{
		return BX.ajax({
			'method': 'GET',
			'dataType': 'json',
			'url': Uri.addParam(node.href, {ajax: 'y'}),
			'onsuccess': this.parseResponse
		});
	}

	reply({node})
	{
		const text = node !== null ? `[USER=${node.dataset.bxAuthorId}]${node.dataset.bxAuthorName}[/USER],&nbsp;` : '';
		Form.makeReply(this.formId, {
			entity: this,
			messageId: 0,
			text: text
		});
	}

	quote({node})
	{
		const text = [
			`[USER=${node.dataset.bxAuthorId}]${node.dataset.bxAuthorName}[/USER]<br>`,
			node.querySelector('div[data-bx-role=text]').innerHTML,
		].join('');

		Form.makeQuote(this.formId, {entity: this, messageId: 0, text: text});
	}

	moderate({node, actNode})
	{
		ajax.runComponentAction(
			'bitrix:forum.topic.reviews',
			actNode.dataset.bxAct + 'Message',
			{
				mode: 'class',
				data: {
					id: node.dataset.bxMessageId
				}
			}
		).then(({data}) => {
			actNode.dataset.bxAct = (data.APPROVED === 'Y' ? 'hide' : 'show');
			if (data.APPROVED === 'Y')
			{
				node.classList.remove('reviews-post-hidden');
			}
			else
			{
				node.classList.add('reviews-post-hidden');
			}
		});
	}

	delete({node})
	{
		ajax.runComponentAction(
			'bitrix:forum.topic.reviews',
			'deleteMessage',
			{
				mode: 'class',
				data: {
					id: node.dataset.bxMessageId
				}
			}
		).then(() => {
			node.parentNode.removeChild(node);
		});
	}
}