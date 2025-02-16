import { Type, Event, Runtime, Reflection } from 'main.core';
import { BaseEvent } from 'main.core.events';
import type { Player } from './player';

export class PlayerManager
{
	static #isStarted: false;
	static #players: Array<Player> = [];

	static addPlayer(player)
	{
		this.#players.push(player);

		this.#bindPlayerEvents(player);

		if (player.autostart || player.lazyload)
		{
			this.#init();
		}
	}

	static removePlayer(playerToRemove)
	{
		this.#players = this.#players.filter((player: Player) => player !== playerToRemove);
	}

	static #init(): void
	{
		if (this.#isStarted)
		{
			return;
		}

		this.#isStarted = true;

		Event.ready(() => {
			Event.bind(window, 'scroll', Runtime.throttle(this.#handleScroll, 300, this));

			setTimeout(() => {
				this.#handleScroll();
			}, 50);

			/** @type {BX.SidePanel.Manager} */
			const sliderManager = Reflection.getClass('top.BX.SidePanel.Instance');
			if (window !== window.top && sliderManager !== null)
			{
				// When players are inside an iframe
				const currentSlider = sliderManager.getSliderByWindow(window);
				if (currentSlider)
				{
					Event.EventEmitter.subscribe(currentSlider, 'SidePanel.Slider:onCloseComplete', () => {
						this.#players.forEach((player: Player) => {
							player.pause();
						});
					});
				}
			}
		});
	}

	static #bindPlayerEvents(player)
	{
		const events = player.getEventList();
		for (const eventName of events)
		{
			Event.EventEmitter.subscribe(player, eventName, () => {
				Event.EventEmitter.emit(this, `PlayerManager.${eventName}`, new BaseEvent({ compatData: [player] }));
			});
		}
	}

	static #handleScroll(): void
	{
		if (this.#players.length === 0)
		{
			return;
		}

		let topVisiblePlayer = null;

		const players = [...this.#players];
		for (const [index, player] of players.entries())
		{
			if (!document.getElementById(player.id))
			{
				this.#players.splice(index, 1);

				continue;
			}

			if (player.lazyload && !player.isInited() && this.isVisibleOnScreen(player.id, 2))
			{
				player.init();
			}

			if (!player.autostart)
			{
				continue;
			}

			if (this.isVisibleOnScreen(player.id, 1))
			{
				if (topVisiblePlayer === null)
				{
					topVisiblePlayer = player;
				}
			}
		}

		if (topVisiblePlayer !== null && !topVisiblePlayer.isPlayed() && !topVisiblePlayer.hasStarted)
		{
			if (!topVisiblePlayer.isInited())
			{
				topVisiblePlayer.autostart = true;
			}
			else if (topVisiblePlayer.isReady() && !topVisiblePlayer.isEnded())
			{
				for (const [, player] of players.entries())
				{
					if (player === topVisiblePlayer || !player.autostart)
					{
						continue;
					}

					if (player.isPlaying())
					{
						player.pause();
					}
				}

				topVisiblePlayer.mute(true);
				topVisiblePlayer.play();
			}
		}
	}

	static getElementCoords(element: HTMLElement): Object
	{
		const VISIBLE_OFFSET = 0.25;

		const box = element.getBoundingClientRect();

		const elementHeight = box.bottom - box.top;
		const top = box.top + VISIBLE_OFFSET * elementHeight;
		const bottom = box.bottom - VISIBLE_OFFSET * elementHeight;

		const elementWidth = box.right - box.left;
		const left = box.left + VISIBLE_OFFSET * elementWidth;
		const right = box.right - VISIBLE_OFFSET * elementWidth;

		return {
			top: top + window.pageYOffset,
			bottom: bottom + window.pageYOffset,
			left: left + window.pageXOffset,
			right: right + window.pageXOffset,
			originTop: top,
			originLeft: left,
			originBottom: bottom,
			originRight: right,
		};
	}

	static isVisibleOnScreen(id: string, screens: number): boolean
	{
		let visible = false;

		const element = document.getElementById(id);
		if (element === null)
		{
			return false;
		}

		const coords = this.getElementCoords(element);
		const clientHeight = document.documentElement.clientHeight;

		let windowTop = window.pageYOffset || document.documentElement.scrollTop;
		let windowBottom = windowTop + clientHeight;

		const numberOfScreens = screens ? parseInt(screens, 10) : 1;

		if (numberOfScreens > 1)
		{
			windowTop -= clientHeight * (numberOfScreens - 1);
			windowBottom += clientHeight * (numberOfScreens - 1);
		}

		const topVisible = coords.top > windowTop && coords.top < windowBottom;
		const bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

		const onScreen = topVisible || bottomVisible;

		if (onScreen && screens > 1)
		{
			return true;
		}

		if (!onScreen)
		{
			return false;
		}

		const playerElement = document.getElementById(id);
		const playerCenterX = coords.originLeft + (coords.originRight - coords.originLeft) / 2;
		const playerCenterY = coords.originTop + (coords.originBottom - coords.originTop) / 2 + 20;

		const currentPlayerCenterElement = document.elementFromPoint(playerCenterX, playerCenterY);

		if (
			currentPlayerCenterElement !== null
			&& (
				currentPlayerCenterElement === playerElement
				|| currentPlayerCenterElement.parentNode === playerElement
				|| currentPlayerCenterElement.parentNode.parentNode === playerElement
			)
		)
		{
			visible = true;
		}

		return (onScreen && visible);
	}

	static getPlayerById(id): Player | null
	{
		if (!Type.isStringFilled(id))
		{
			return null;
		}

		for (const player of this.#players)
		{
			if (player.id === id)
			{
				return player;
			}
		}

		return null;
	}
}
