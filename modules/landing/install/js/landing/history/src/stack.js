import {Text, Type} from 'main.core';
import {Backend} from 'landing.backend';
import {HISTORY_TYPES} from './internal/constants';

type StackItem = {
	entityId: number,
	command: string,
}

export default class Stack
{
	/**
	 * ID and type of main entity (landing or design block)
	 */
	mainEntityId: number;
	entityType: string;

	items: [StackItem] = [];
	step: number;

	/**
	 * All entities in stack and them current steps
	 */
	entitySteps: {[number]: number} = {};

	constructor(entityId: number, entityType: string = HISTORY_TYPES.landing)
	{
		this.mainEntityId = entityId;
		this.entityType = entityType;
	}

	init(): Promise
	{
		return this.#loadFromBackend()
			.then(this.#adjustMultiPage.bind(this));
	}

	reload(): Promise
	{
		this.items = [];
		this.step = 0;

		return this.#loadFromBackend();
	}

	#loadFromBackend(): Promise
	{
		return BX.Landing.Backend.getInstance()
			.action(
				this.#getLoadBackendActionName(),
				this.#getLoadBackendParams(),
			)

			.then((data: {stack: [], step: number}) => {
				const items = Type.isArray(data.stack) ? data.stack : [];
				items.forEach(item =>
				{
					if (
						item.entityId
						&& Type.isNumber(item.entityId)
						&& item.command
						&& Type.isString(item.command)
					)
					{
						this.items.push({
							entityId: item.entityId,
							command: item.command,
						});

						if (item.current && item.current === true)
						{
							this.entitySteps[item.entityId] = this.items.length;
						}
					}
				});

				const step = Text.toNumber(data.step);
				this.step = Math.min(this.items.length, step);
				this.step = Math.max(0, this.step);
			})

			.catch((e) => {
				console.error('History load error', e);

				return history;
			});
	}

	#getLoadBackendActionName(): string
	{
		if (this.entityType === HISTORY_TYPES.designerBlock)
		{
			return "History::getForDesignerBlock";
		}

		return "History::getForLanding";
	}

	#getLoadBackendParams(): string
	{
		if (this.entityType === HISTORY_TYPES.designerBlock)
		{
			return {blockId: this.mainEntityId};
		}

		return {lid: this.mainEntityId};
	}

	#adjustMultiPage(): Promise
	{
		const currentItem = this.items[this.step - 1];
		if (
			currentItem
			&& this.entityType === HISTORY_TYPES.landing
			&& this.#isMultiPage()
		)
		{
			const entitiesToClearFuture = [];
			this.items.forEach((item, index) =>
			{
				const step = index + 1;
				if (step >= this.step)
				{
					return;
				}

				// Clear future for all entities, except current, that have future (have steps after own current)
				if (
					item.entityId !== currentItem.entityId
					&& this.entitySteps[item.entityId] < step
				)
				{
					entitiesToClearFuture.push(item.entityId);
				}
			});

			if (entitiesToClearFuture.length > 0)
			{
				const backend = Backend.getInstance();
				const promises = [];
				entitiesToClearFuture.forEach(entityId => {
					promises.push(backend.action('History::clearFutureForLanding', {
						landingId: entityId
					}));
				});

				return Promise.all(promises)
					.then(this.reload.bind(this));
			}
		}

		return Promise.resolve();
	}

	#isMultiPage(): boolean
	{
		return Object.keys(this.entitySteps).length > 1;
	}

	setTypeDesignerBlock(blockId: number): Promise
	{
		this.mainEntityId = blockId;
		this.entityType = HISTORY_TYPES.designerBlock;

		return this.reload();
	}

	getCommandName(undo: boolean = true): ?string
	{
		let step = undo ? this.step : this.step + 1;
		step--; // array index correction

		return this.items[step] ? this.items[step].command : null;
	}

	getCommandEntityId(undo: boolean = true): ?number
	{
		let step = undo ? this.step : this.step + 1;
		step--; // array index correction

		return this.items[step] ? this.items[step].entityId : null;
	}

	/**
	 * Check is stack undoable
	 * @return {boolean}
	 */
	canUndo(): boolean
	{
		return this.step > 0 && this.step <= this.items.length;
	}

	/**
	 * Check is stack reduable
	 * @return {boolean}
	 */
	canRedo(): boolean
	{
		return this.step >= 0 && this.step < this.items.length;
	}

	/**
	 * Change stack when undo or redo
	 * @param undo - if false - redo
	 * @return {Promise}
	 */
	offset(undo: boolean = true)
	{
		const newStep = undo ? this.step - 1 : this.step + 1;
		if (newStep >= 0 && newStep <= this.items.length)
		{
			this.step = newStep;
		}

		return Promise.resolve();
	}

	push(): Promise
	{
		// For some types actions history.push called before backend changes. Need add input timeout
		return new Promise(resolve => {
			setTimeout(() => {
				// change values before load
				if (this.step < this.items.length)
				{
					this.items = this.items.slice(0, this.step - 1);
				}
				this.step++;
				this.items.push(this.items[this.step - 1]);

				return this.reload()
					.then(resolve);
			}, 500);
		})
	}
}