import { Loc, Type, Runtime } from 'main.core';
import { BaseEvent, EventEmitter } from 'main.core.events';

class Informer
{
	constructor()
	{
		this.container = null;
		this.wrap = null;
		this.plus = null;
		this.value = null;

		this.currentSiteId = null;
		this.currentCounterType = null;

		this.counterDecrementStack = 0;
		this.counterValue = 0;

		this.lockCounterAnimation = null;

		this.class = {
			informerFixed: 'feed-new-message-informer-fixed',
			informerAnimation: 'feed-new-message-informer-anim',
			informerFixedAnimation: 'feed-new-message-informer-fix-anim',
			counterText: 'feed-new-message-inf-text',
			counterContainer: 'feed-new-message-inf-text-counter',
			reloadContainer: 'feed-new-message-inf-text-reload',
			icon: 'feed-new-message-icon',
			iconRotating: 'new-message-balloon-icon-rotating',
			plusHidden: 'feed-new-message-informer-counter-plus-hidden',
		};
	}

	init()
	{
		this.initNodes();
		this.initEvents();
	}

	initNodes()
	{
		this.currentCounterType = Loc.getMessage('sonetLCounterType') ? Loc.getMessage('sonetLCounterType') : '**';
		this.currentSiteId = Loc.getMessage('SITE_ID');

		this.container = document.getElementById('sonet_log_counter_2_container');
		if (this.container)
		{
			this.container.addEventListener('click', this.showReloadAnimation.bind(this));
		}

		this.wrap = document.getElementById('sonet_log_counter_2_wrap');
		this.plus = document.getElementById('sonet_log_counter_2_plus');
		this.value = document.getElementById('sonet_log_counter_2');
	}

	initEvents()
	{
		EventEmitter.subscribe('onGoUp', (event: BaseEvent) =>
		{
			this.unfixWrap();
		});

		EventEmitter.subscribe('onPullEvent-main', (event: BaseEvent) =>
		{
			const [ command, eventParams ] = event.getData();

			if (
				command !== 'user_counter'
				|| !eventParams[this.currentSiteId]
				|| !eventParams[this.currentSiteId][this.currentCounterType]
			)
			{
				return;
			}

			this.changeCounter(Runtime.clone(eventParams[this.currentSiteId][this.currentCounterType]));
		});

		EventEmitter.subscribe('onImUpdateCounter', (event: BaseEvent) =>
		{
			const [ counterData ] = event.getData();

			if (
				!Type.isObjectLike(counterData)
				|| Type.isUndefined(counterData[this.currentCounterType])
			)
			{
				return;
			}

			this.changeCounter(counterData[this.currentCounterType]);
		});

		EventEmitter.subscribe('OnUCCommentWasRead', (event: BaseEvent) =>
		{
			const [ xmlId, id, options ] = event.getData();

			if (
				!Type.isObjectLike(options)
				|| !options.live
				|| !options.new
			)
			{
				return;
			}

			EventEmitter.emit('onCounterDecrement', new BaseEvent({
				compatData: [1],
			}));

			this.decrementCounter(1);
		});
	};

	changeCounter(count)
	{
		this.counterValue = parseInt(count);

		if (this.counterValue <= 0)
		{
			this.counterDecrementStack = 0;
		}

		const valueToShow = this.counterValue - this.counterDecrementStack;

		this.changeAnimate({
			show: (valueToShow > 0),
			counter: valueToShow,
			zeroCounterFromDb: (valueToShow <= 0),
		});
	};

	changeAnimate(params)
	{
		const show = (!!params.show);
		const counterValue = parseInt(params.counter);
		const zeroCounterFromDb = !!params.zeroCounterFromDb;

		if (!this.container)
		{
			return;
		}

		const counterTextNode = this.container.querySelector(`span.${this.class.counterText}`);
		const reloadNode = this.container.querySelector(`span.${this.class.reloadContainer}`);

		if (this.lockCounterAnimation)
		{
			setTimeout(() => {
				this.changeAnimate({
					show: show,
					counter: counterValue,
				})
			}, 200);

			return false;
		}

		if (show)
		{
			if (this.value)
			{
				this.value.innerHTML = counterValue;
			}

			this.showWrapAnimation();

			if (
				this.plus
				&& reloadNode
				&& !reloadNode.classList.contains('--hidden')
				&& counterTextNode
			)
			{
				reloadNode.classList.add('--hidden');
				counterTextNode.classList.remove('--hidden');
				this.plus.classList.remove(`${this.class.plusHidden}`);
			}
		}
		else if (this.wrap)
		{
			if (
				zeroCounterFromDb
				&& this.wrap.classList.contains(`${this.class.informerAnimation}`)
			)
			{
				if (
					counterTextNode
					&& reloadNode
				)
				{
					counterTextNode.classList.add('--hidden');
					reloadNode.classList.remove('--hidden');

					this.hideReloadAnimation();
				}
			}
			else
			{
				setTimeout(() => {
					this.hideWrapAnimation();
				}, 400);
			}
		}
	};

	showWrapAnimation()
	{
		if (!this.wrap)
		{
			return;
		}

		this.wrap.style.visibility = 'visible';
		this.wrap.classList.add(`${this.class.informerAnimation}`);
	};

	hideWrapAnimation()
	{
		if (!this.wrap)
		{
			return;
		}

		this.wrap.classList.remove(`${this.class.informerAnimation}`);
		this.wrap.style.visibility = 'hidden';
	}

	showReloadAnimation()
	{
		if (!this.container)
		{
			return;
		}

		const counterWaiterNode = this.container.querySelector(`span.${this.class.icon}`);
		if (counterWaiterNode)
		{
			counterWaiterNode.classList.add(this.class.iconRotating);
		}
	}

	hideReloadAnimation()
	{
		if (!this.container)
		{
			return;
		}

		const counterNodeWaiter = this.container.querySelector(`span.${this.class.icon}`);
		if (counterNodeWaiter)
		{
			counterNodeWaiter.classList.remove(this.class.iconRotating);
		}
	}

	onFeedScroll()
	{
		if (
			!this.container
			|| !this.wrap
		)
		{
			return;
		}

		const top = this.wrap.parentNode.getBoundingClientRect().top;
//		const counterRect = this.container.getBoundingClientRect();

		if (top <= 53)
		{
/*
			if (!this.wrap.classList.contains(`${this.class.informerFixed}`))
			{
				this.container.style.left = `${(counterRect.left + (counterRect.width / 2))}px`;
			}
*/
			this.fixWrap();
		}
		else
		{
			this.unfixWrap();
//			this.container.style.left = 'auto';
		}
	};

	fixWrap()
	{
		if (!this.wrap)
		{
			return;
		}

		this.wrap.classList.add(`${this.class.informerFixed}`, `${this.class.informerFixedAnimation}`);
	}

	unfixWrap()
	{
		if (!this.wrap)
		{
			return;
		}

		this.wrap.classList.remove(`${this.class.informerFixed}`, `${this.class.informerFixedAnimation}`);
	};

	recover()
	{
		if (!this.container)
		{
			return;
		}

		const counterContainerNode = this.container.querySelector(`span.${this.class.counterContainer}`);

		if (!counterContainerNode)
		{
			return;
		}

		counterContainerNode.classList.remove('--hidden');
		this.hideReloadNode();

		if (this.plus)
		{
			this.plus.classList.add(`${this.class.plusHidden}`);
		}
	};

	hideReloadNode()
	{
		if (!this.container)
		{
			return;
		}

		const reloadNode = this.container.querySelector(`span.${this.class.reloadContainer}`);

		if (!reloadNode)
		{
			return;
		}

		reloadNode.classList.add('--hidden');
	};

	decrementCounter(value)
	{
		this.counterDecrementStack += parseInt(value);

		if (!this.value)
		{
			return;
		}

		const counterValue = this.counterValue - this.counterDecrementStack;
		if (counterValue > 0)
		{
			this.value.innerHTML = counterValue;
		}
		else
		{
			this.changeAnimate({
				show: false,
				counter: 0,
			});
		}
	}

	getWrap()
	{
		return this.wrap;
	}
}

export {
	Informer
};