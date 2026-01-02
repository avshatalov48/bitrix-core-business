import { Tag, Dom, Runtime, Type, bind } from 'main.core';
import { EventEmitter } from 'main.core.events';
import { ReactionName } from './enums/reaction-name';
import { ReactionTitle } from './enums/reaction-title';
import { LottieAnimation } from './enums/animations';

import './reaction.css';

export type EmojiOptions = {
	name: ReactionName;
	size: number;
	animation?: EmojiAnimateOptions;
};

type EmojiAnimateOptions = {
	animate: boolean;
	infinite: ?boolean;
}

export const ReactionEvent = Object.freeze({
	animationFinish: 'animationFinish',
});

export class Reaction extends EventEmitter
{
	#name: string;
	#size: ?number;
	#animate: boolean;
	#infiniteAnimate: boolean;

	#lottieAnimation: ?any = null;
	#wrapper: HTMLElement;
	#animatedReaction: ?HTMLElement;
	#staticReaction: ?HTMLElement;
	#isAnimationPaused: boolean = false;

	constructor(options: EmojiOptions = {})
	{
		super(options);
		this.setEventNamespace('UI.Reaction.Item');
		this.#name = options.name;
		this.#size = Type.isNumber(options.size) ? options.size : null;

		this.#animate = options.animation?.animate === true;
		this.#infiniteAnimate = options.animation?.infinite === true;

		this.#lottieAnimation = null;
		this.#wrapper = null;
		this.#animatedReaction = null;
	}

	render(): HTMLElement
	{
		this.#wrapper = this.#renderEmojiWrapper();
		Dom.append(this.#renderStatic(), this.#wrapper);

		if (this.#animate)
		{
			this.#switchStaticToAnimated();
		}

		return this.#wrapper;
	}

	/**
	 * @deprecated used only for a vue component
	 */
	renderOnNode(node: HTMLElement): void
	{
		const wrapper = this.#renderEmojiWrapper();

		Dom.attr(node, {
			class: Dom.attr(wrapper, 'class'),
			title: Dom.attr(wrapper, 'title'),
		});

		Dom.append(this.#renderStatic(), node);

		this.#wrapper = node;

		if (this.#animate)
		{
			this.#switchStaticToAnimated();
		}
	}

	#renderEmojiWrapper(): HTMLElement
	{
		const title = ReactionTitle[this.#name];

		const wrapper = Tag.render`<span class="ui-reaction" title="${title}"></span>`;

		if (Type.isNumber(this.#size))
		{
			Dom.style(wrapper, '--ui-reaction-size', `${this.#size}px`);
		}

		return wrapper;
	}

	#renderStatic(): HTMLElement
	{
		if (!this.#staticReaction)
		{
			const reaction = Tag.render`<span class="ui-reaction__inner --${this.#name}"></span>`;

			Dom.style(reaction, 'color-scheme', 'only light');
			this.#staticReaction = reaction;
		}

		return this.#staticReaction;
	}

	async #renderAnimated(): Promise<HTMLElement>
	{
		if (!this.#animatedReaction)
		{
			await this.#initAnimatedReaction();
		}

		return this.#animatedReaction;
	}

	async #initAnimatedReaction(): void
	{
		return new Promise((resolve, reject) => {
			Runtime.loadExtension(['ui.lottie'])
				.then(({ Lottie }) => {
					if (!this.#lottieAnimation)
					{
						const wrapper = Tag.render`<span class="ui-reaction__animation-wrapper"></span>`;
						Dom.style(wrapper, 'color-scheme', 'only light');
						this.#lottieAnimation = Lottie.loadAnimation({
							container: wrapper,
							path: LottieAnimation[this.#name],
							loop: true,
							autoplay: true,
							renderer: 'svg',
							rendererSettings: {
								viewBoxOnly: true,
								className: `ui-reaction__lottie-animation --${this.#name}`,
							},
						});

						bind(this.#lottieAnimation, 'DOMLoaded', () => {
							this.#animatedReaction = wrapper;

							resolve();
						});

						bind(this.#lottieAnimation, 'data_failed', () => {
							reject(new Error('UI.ReactionsSelect: V2: Lottie animation data failed to load:'));
						});

						bind(this.#lottieAnimation, 'loopComplete', () => {
							if (this.#infiniteAnimate === false || this.#isAnimationPaused)
							{
								this.#lottieAnimation.pause();
								this.#switchAnimatedToStatic();
								this.emit(ReactionEvent.animationFinish);
							}
						});
					}
				})
				.catch((error) => {
					console.error(error);
					reject(error);
				});
		});
	}

	#switchAnimatedToStatic(): void
	{
		const staticReaction = this.#renderStatic();

		Dom.clean(this.#wrapper);
		Dom.append(staticReaction, this.#wrapper);
	}

	#switchStaticToAnimated(): void
	{
		this.#renderAnimated()
			.then((wrapper) => {
				Dom.clean(this.#wrapper);
				Dom.append(wrapper, this.#wrapper);
			})
			.catch((error) => {
				console.error(error);
			});
	}

	setSize(size: number): void
	{
		this.#size = size;

		if (this.#wrapper)
		{
			Dom.style(this.#wrapper, '--ui-reaction-size', `${this.#size}px`);
		}
	}

	setInfiniteAnimate(value: boolean): void
	{
		this.#infiniteAnimate = value === true;

		if (this.#infiniteAnimate)
		{
			this.playAnimation();
		}
	}

	playAnimation(): void
	{
		this.#isAnimationPaused = false;
		this.#switchStaticToAnimated();
		this.#lottieAnimation?.play();
	}

	pauseAnimation(): void
	{
		this.#isAnimationPaused = true;
	}

	destroy(): void
	{
		this.#lottieAnimation?.destroy();
		Dom.remove(this.#wrapper);
		this.#wrapper = null;
	}
}
