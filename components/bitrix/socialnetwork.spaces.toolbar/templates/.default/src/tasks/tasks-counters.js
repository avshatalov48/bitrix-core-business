import { ajax, AjaxError, AjaxResponse, Dom, Event, Tag, Type } from 'main.core';
import { PULL as Pull } from 'pull.client';
import { Filter } from '../filter';

type Params = {
	filter: Filter,
	isUserSpace: boolean,
	isScrumSpace: boolean,
	userId?: number,
	groupId?: number,
	filterRole: string,
	counters: Counters,
}

export type Counters = {
	expired: Counter,
	new_comments: Counter,
}

type Counter = {
	code: number,
	counter: number,
	filterField: string,
}

import '../css/counters.css';

export class TasksCounters
{
	#filter: Filter;
	#filterRole: string;
	#userId: number;
	#groupId: number;
	#counters: Counters;
	#isUserSpace: boolean;
	#isScrumSpace: boolean;

	#layout: Object<string, HTMLElement>;

	constructor(params: Params)
	{
		this.#userId = Type.isUndefined(params.userId) ? 0 : parseInt(params.userId, 10);
		this.#groupId = Type.isUndefined(params.groupId) ? 0 : parseInt(params.groupId, 10);
		this.#filter = params.filter;
		this.#filterRole = params.filterRole;
		this.#counters = params.counters;
		this.#isUserSpace = params.isUserSpace;
		this.#isScrumSpace = params.isScrumSpace;

		this.#layout = {
			node: null,
			listContainerNode: null,
			listNode: null,
		};

		this.#initPull();
	}

	render(): HTMLElement
	{
		this.#layout.node = Tag.render`
			<div class="sn-spaces__toolbar-space_counters">
				${this.#renderExpired()}
				${this.#renderNewComments()}
			</div>
		`;

		return this.#layout.node;
	}

	readAll()
	{
		if (this.#isUserSpace || this.#filterRole !== 'view_all')
		{
			this.#readAllUser();
		}
		else
		{
			this.#readAllGroup();
		}
	}

	#initPull()
	{
		Pull.subscribe({
			moduleId: 'tasks',
			callback: this.#processPullEvent.bind(this),
		});
	}

	#processPullEvent(data)
	{
		const { command, params } = data;

		const eventHandlers = {
			user_counter: this.#processUserCounter.bind(this),
			project_counter: this.#processProjectCounter.bind(this),
		};

		const has = Object.prototype.hasOwnProperty;
		if (has.call(eventHandlers, command))
		{
			const method = eventHandlers[command];
			if (method)
			{
				method.apply(this, [params]);
			}
		}
	}

	#processUserCounter()
	{
		if (!this.#isUserSpace)
		{
			return;
		}

		this.#updateCounters();
	}

	#processProjectCounter()
	{
		if (this.#isUserSpace)
		{
			return;
		}

		this.#updateCounters();
	}

	#renderExpired(): HTMLElement
	{
		if (this.#isScrumSpace)
		{
			return '';
		}

		const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes';

		const activeClass = this.#getActiveClass(this.#counters.expired.counter);

		this.#layout.expired = Tag.render`
			<button
				data-id="sn-spaces-toolbar-counters-expired"
				class="sn-spaces__toolbar-space_btn-with-counter ${uiClasses} ${activeClass}"
			>
				<div class="ui-icon-set --stopwatch"></div>
				<div class="sn-spaces__toolbar-space_btn-counter">
					${parseInt(this.#counters.expired.counter, 10)}
				</div>
			</button>
		`;

		Event.bind(this.#layout.expired, 'click', this.#click.bind(this, this.#counters.expired));

		return this.#layout.expired;
	}

	#renderNewComments(): HTMLElement
	{
		const uiClasses = 'ui-btn ui-btn-light ui-btn-sm ui-btn-round ui-btn-themes';

		const activeClass = this.#getActiveClass(this.#counters.new_comments.counter);

		this.#layout.newComments = Tag.render`
			<button
				data-id="sn-spaces-toolbar-counters-new-comments"
				class="sn-spaces__toolbar-space_btn-with-counter --green ${uiClasses} ${activeClass}"
			>
				<div class="ui-icon-set --chats-1"></div>
				<div class="sn-spaces__toolbar-space_btn-counter">
					${parseInt(this.#counters.new_comments.counter, 10)}
				</div>
			</button>
		`;

		Event.bind(this.#layout.newComments, 'click', this.#click.bind(this, this.#counters.new_comments));

		return this.#layout.newComments;
	}

	#click(counter: Counter)
	{
		this.#filter.toggleField(counter.filterField, counter.code);
	}

	#update(counters: Counters)
	{
		this.#counters = counters;

		if (!this.#isScrumSpace)
		{
			this.#updateCounter(this.#layout.expired, this.#counters.expired.counter);
		}
		this.#updateCounter(this.#layout.newComments, this.#counters.new_comments.counter);
	}

	#updateCounter(node: HTMLElement, value: number)
	{
		const newCommentsActiveClass = this.#getActiveClass(value);
		if (newCommentsActiveClass)
		{
			Dom.addClass(node, newCommentsActiveClass);
		}
		else
		{
			Dom.removeClass(node, '--active');
		}

		const counterNode = node.querySelector('.sn-spaces__toolbar-space_btn-counter');
		counterNode.textContent = parseInt(value, 10);
	}

	#updateCounters()
	{
		ajax.runComponentAction(
			'bitrix:socialnetwork.spaces.toolbar',
			'getTasksCounters',
			{
				mode: 'class',
				data: {
					groupId: this.#groupId,
				},
			},
		)
			.then((response: AjaxResponse) => {
				this.#update(response.data);
			})
			.catch((error: AjaxError) => {
				this.#consoleError('changePrivacy', error);
			})
		;
	}

	#readAllGroup()
	{
		if (!this.#hasCounters())
		{
			return;
		}

		ajax.runAction(
			'tasks.viewedGroup.project.markAsRead',
			{
				data: {
					fields: {
						groupId: this.#groupId,
					},
				},
			},
		)
			.then((response: AjaxResponse) => {
				this.#updateCounters();
				this.#filter.applyFilter();
			})
			.catch((error: AjaxError) => {
				this.#consoleError('readAllScrum', error);
			})
		;
	}

	#readAllUser()
	{
		if (!this.#hasCounters())
		{
			return;
		}

		ajax.runAction(
			'tasks.viewedGroup.user.markAsRead',
			{
				data: {
					fields: {
						groupId: this.#groupId,
						userId: this.#userId,
						role: this.#filterRole,
					},
				},
			},
		)
			.then((response: AjaxResponse) => {
				this.#updateCounters();
				this.#filter.applyFilter();
			})
			.catch((error: AjaxError) => {
				this.#consoleError('readAllScrum', error);
			})
		;
	}

	#hasCounters(): boolean
	{
		const expiredCounter = parseInt(this.#counters.expired.counter, 10);
		const newCommentsCounter = parseInt(this.#counters.new_comments.counter, 10);

		return expiredCounter > 0 || newCommentsCounter > 0;
	}

	#getActiveClass(value: number): string
	{
		return value > 0 ? '--active' : '';
	}

	#consoleError(action: string, error: AjaxError)
	{
		// eslint-disable-next-line no-console
		console.error(`TasksCounters: ${action} error`, error);
	}
}
