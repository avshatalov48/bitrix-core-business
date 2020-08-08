import {Type} from 'main.core';
import {Animation} from "./animation";

export class Queue
{
	static #items = [];
	static #isRunning = false;

	static add(animation: Animation|Animation[]): Queue
	{
		Queue.#items.push(animation);

		return Queue;
	}

	static run()
	{
		if(Queue.#isRunning)
		{
			return;
		}

		/** @var Animation animation */
		let animations = Queue.#items.shift();
		if(!animations)
		{
			return;
		}
		if(!Type.isArray(animations))
		{
			animations = [animations];
		}
		Queue.#isRunning = true;
		const promises = [];
		animations.forEach((animation: Animation) => {
			if(animation instanceof Animation)
			{
				promises.push(animation.start());
			}
		});

		Promise.all(promises).then(() => {
			Queue.#isRunning = false;
			Queue.run();
		});
	}
}