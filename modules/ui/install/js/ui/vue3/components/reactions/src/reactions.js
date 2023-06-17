/**
 * Bitrix UI
 * Reaction picker Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import 'ui.fonts.opensans';
import './reactions.css';
import './icons.css';

import {BitrixVue} from 'ui.vue3';
import {Type} from 'main.core';
import {BaseEvent} from 'main.core.events';
import {ReactionsSelect, reactionType, reactionCssClass} from 'ui.reactions-select';
import {Lottie} from 'ui.lottie';

const ReactionTypeNone = 'none';
const ReactionOrder = Object.keys(reactionType);

const ReactionIconClass = {...reactionCssClass, none: 'ui-vue-reactions-icon-none'};

export const Reactions = BitrixVue.mutableComponent('bx-reactions',
{
	props: {
		name: {default: ''},
		values: {default: {}},
		currentUserId: {default: 0},
		canOpenList: {default: true},
	},
	data: () => ({
		localValues: {},
		userReaction: ReactionTypeNone,
		buttonAnimate: false,
	}),
	emits: ['list', 'set'],
	mounted()
	{
		this.userReactionAnimation = null;
		this.selectorPopup = null;
		this.selectorPopupHideTimeout = null;
		this.selectorPopupShowTimeout = null;

		this.localValues = {...this.values};

		if (Type.isStringFilled(this.name))
		{
			this.$Bitrix.eventEmitter.subscribe(`ui:reaction:press:${this.name}`, this.onPress);
		}
	},
	beforeUnmount()
	{
		if (this.selectorPopup)
		{
			this.selectorPopup.hide();
			this.selectorPopup = null;
		}
		if (this.userReactionAnimation)
		{
			this.userReactionAnimation.destroy();
		}
		clearTimeout(this.selectorPopupHideTimeout);
		this.selectorPopupHideTimeout = null;
		clearTimeout(this.selectorPopupShowTimeout);
		this.selectorPopupShowTimeout = null;

		if (Type.isStringFilled(this.name))
		{
			this.$Bitrix.eventEmitter.unsubscribe(`ui:reaction:press:${this.name}`, this.onPress);
		}
	},
	watch:
	{
		values: {
			handler(values) {
				this.localValues = {...values};
			},
			deep: true
		},
	},
	methods:
	{
		list(): Boolean
		{
			if (!this.canOpenList)
			{
				return false;
			}

			this.$emit('list', {values: this.localValues});

			return true;
		},

		set(reaction)
		{
			if (!ReactionOrder.includes(reaction))
			{
				return false;
			}

			if (this.localValues[this.userReaction])
			{
				this.localValues[this.userReaction] = this.localValues[this.userReaction]
					.filter(element => element !== this.currentUserId)
				;
			}

			if (!this.localValues[reaction])
			{
				this.localValues = {...this.localValues, [reaction]: []};
			}

			this.localValues[reaction].push(this.currentUserId);

			this.buttonAnimate = true;
			setTimeout(() => {
				this.buttonAnimate = false;
			}, 400);

			this.$emit('set', {action: 'set', type: reaction});

			this.animateReactionButton(reaction);

			this.hideEmotionSelector(0);

			return true;
		},

		unset()
		{
			if (this.userReaction === ReactionTypeNone)
			{
				return true;
			}

			if (this.localValues[this.userReaction])
			{
				this.localValues[this.userReaction] = this.localValues[this.userReaction]
					.filter(element => element !== this.currentUserId)
				;
			}

			this.$emit('set', {action: 'remove', type: this.userReaction});

			this.animateReactionButton(ReactionTypeNone);

			this.hideEmotionSelector(0);

			return true;
		},

		press(reaction = reactionType.like)
		{
			if (this.userReaction === ReactionTypeNone)
			{
				return this.set(reaction);
			}

			this.unset();
		},

		showEmotionSelector(event)
		{
			if (this.selectorPopup)
			{
				clearTimeout(this.selectorPopupHideTimeout);
				return false;
			}
			const popupName = this.name ?? Date.now();

			this.selectorPopup = new ReactionsSelect({
				name: popupName,
				position: event.target
			})
			.subscribe('select', (selectEvent) => {
				const {reaction} = selectEvent.getData();
				this.set(reaction);
				this.selectorPopup?.hide();
			})
			.subscribe('mouseleave', () => {
				this.hideEmotionSelector(500);
			})
			.subscribe('mouseenter', () => {
				clearTimeout(this.selectorPopupHideTimeout);
			})
			.subscribe('hide', () => {
				clearTimeout(this.selectorPopupHideTimeout);
				this.selectorPopup = null;
			});

			clearTimeout(this.selectorPopupShowTimeout);
			this.selectorPopupShowTimeout = setTimeout(() => this.selectorPopup?.show(), 1000);
		},

		hideEmotionSelector(timeout = 1000)
		{
			clearTimeout(this.selectorPopupShowTimeout);
			clearTimeout(this.selectorPopupHideTimeout);

			if (!timeout)
			{
				this.selectorPopup?.hide();
				return true;
			}
			this.selectorPopupHideTimeout = setTimeout(() => {
				this.selectorPopup?.hide();
			}, timeout);
		},

		onPress(event: BaseEvent): void
		{
			const data = event.getData();
			if (!data.reaction)
			{
				data.reaction = reactionType.like
			}

			this.press(data.reaction);
		},

		animateReactionButton(reaction)
		{
			if (this.currentUserId <= 0)
			{
				return true;
			}

			if (this.userReactionAnimation)
			{
				this.userReactionAnimation.destroy();
			}

			if (reaction === ReactionTypeNone)
			{
				return true;
			}

			this.userReactionAnimation = Lottie.loadAnimation({
				animationData: ReactionsSelect.getLottieAnimation(reaction),
				container: this.$refs['reactions-button-icon'],
				loop: false,
				autoplay: false,
				renderer: 'svg',
				rendererSettings: {
					viewBoxOnly: true,
				}
			});
			this.userReactionAnimation.addEventListener('complete', () => {
				this.userReactionAnimation.destroy();
			});
			this.userReactionAnimation.addEventListener('destroy', () => {
				this.userReactionAnimation = null;
			});
			this.userReactionAnimation.play();

			return true;
		}
	},
	computed:
	{
		types(): Array
		{
			this.userReaction = ReactionTypeNone;

			return ReactionOrder.filter(type =>
			{
				if (
					!Type.isArray(this.localValues[type])
					|| this.localValues[type].length <= 0
				)
				{
					return false;
				}

				if (
					this.currentUserId > 0
					&& this.userReaction === ReactionTypeNone
					&& this.localValues[type].includes(this.currentUserId)
				)
				{
					this.userReaction = type;
				}

				return true;
			}).map(type => {
				return {type, count: this.localValues[type].length};
			});
		},

		counter(): Number
		{
			return this.types.map(element => element.count).reduce((result, value) => result + value, 0);
		},

		isTypesShowed(): Boolean
		{
			if (this.counter <= 0)
			{
				return false;
			}

			return !(
				this.userReaction !== ReactionTypeNone
				&& this.counter === 1
			);
		},

		isMobile(): Boolean
		{
			const UA = navigator.userAgent.toLowerCase();

			return (
				UA.includes('android')
				|| UA.includes('iphone')
				|| UA.includes('ipad')
				|| UA.includes('bitrixmobile')
			);
		},

		ReactionIconClass: () => ReactionIconClass,
	},
	template: `
		<div :class="['ui-vue-reactions', {'ui-vue-reactions-mobile': isMobile}]">
			<transition name="ui-vue-reactions-result-animation">
				<div v-if="isTypesShowed" :class="['ui-vue-reactions-result', {'ui-vue-reactions-result-active': canOpenList}]" @click="list">
					<transition-group tag="div" class="ui-vue-reactions-result-types" name="ui-vue-reactions-result-type-animation" >
						<span v-for="element in types" :class="['ui-vue-reactions-result-type', ReactionIconClass[element.type]]" :key="element.type"></span>
					</transition-group>	
					<div class="ui-vue-reactions-result-counter">{{counter}}</div>
				</div>
			</transition>
			<div v-if="currentUserId > 0"  class="ui-vue-reactions-button" @click.prevent="press()" @mouseenter="showEmotionSelector" @mouseleave="hideEmotionSelector()">
				<div class="ui-vue-reactions-button-container">
					<div :class="['ui-vue-reactions-button-icon', ReactionIconClass[userReaction], {'ui-vue-reactions-button-pressed': buttonAnimate}]" ref="reactions-button-icon"></div>
				</div>
			</div>
		</div>
	`
});
