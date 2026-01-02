import { Type } from 'main.core';

import { ReactionName } from 'ui.reaction.item';

export class ReactionPickerRanking
{
	static #STORAGE_KEY = 'ui-reaction-picker-ranking';
	static #INITIAL_COUNTER_VALUE = 2;

	#counters: Map<string, number>;

	constructor()
	{
		this.#counters = this.#loadCounters();
		this.#initializeDefaultCounters();
	}

	getRankedReactionsNames(): string[]
	{
		const allReactions = Object.values(ReactionName);
		const sortedReactions = this.#sortReactionsByRank(allReactions);

		const likeIndex = sortedReactions.indexOf(ReactionName.like);
		if (likeIndex > 0)
		{
			sortedReactions.splice(likeIndex, 1);
			sortedReactions.unshift(ReactionName.like);
		}

		return sortedReactions;
	}

	incrementReactionCounter(reactionName: string): void
	{
		if (!this.#isValidReaction(reactionName))
		{
			return;
		}

		const currentCount = this.#counters.get(reactionName) || 0;
		this.#counters.set(reactionName, currentCount + 1);
		this.#saveCounters();
	}

	getReactionCounter(reactionName: string): number
	{
		if (!this.#isValidReaction(reactionName))
		{
			return 0;
		}

		return this.#counters.get(reactionName) || 0;
	}

	resetCounters(): void
	{
		this.#counters.clear();
		this.#initializeDefaultCounters();
		this.#saveCounters();
	}

	#loadCounters(): Map<string, number>
	{
		try
		{
			const stored = localStorage.getItem(ReactionPickerRanking.#STORAGE_KEY);
			if (!stored)
			{
				return new Map();
			}

			const parsed = JSON.parse(stored);
			if (!Type.isObject(parsed) || parsed === null)
			{
				return new Map();
			}

			return new Map(Object.entries(parsed));
		}
		catch (error)
		{
			// eslint-disable-next-line no-console
			console.warn('Failed to load reaction counters from localStorage:', error);

			return new Map();
		}
	}

	#saveCounters(): void
	{
		try
		{
			const countersObject = Object.fromEntries(this.#counters);
			localStorage.setItem(ReactionPickerRanking.#STORAGE_KEY, JSON.stringify(countersObject));
		}
		catch (error)
		{
			// eslint-disable-next-line no-console
			console.warn('Failed to save reaction counters to localStorage:', error);
		}
	}

	#initializeDefaultCounters(): void
	{
		const allReactions = Object.values(ReactionName);
		const topReactions = [
			ReactionName.like,
			ReactionName.faceWithTearsOfJoy,
			ReactionName.redHeart,
			ReactionName.neutralFace,
			ReactionName.fire,
			ReactionName.cry,
		];

		for (const reaction of topReactions)
		{
			if (!this.#counters.has(reaction))
			{
				this.#counters.set(reaction, ReactionPickerRanking.#INITIAL_COUNTER_VALUE);
			}
		}

		for (const reaction of allReactions)
		{
			if (!this.#counters.has(reaction))
			{
				this.#counters.set(reaction, 0);
			}
		}
	}

	#sortReactionsByRank(reactions: string[]): string[]
	{
		return reactions.toSorted((a, b) => {
			const countA = this.#counters.get(a) || 0;
			const countB = this.#counters.get(b) || 0;

			if (countA !== countB)
			{
				return countB - countA;
			}

			return 0;
		});
	}

	#isValidReaction(reactionName: string): boolean
	{
		return Object.values(ReactionName).includes(reactionName);
	}
}
