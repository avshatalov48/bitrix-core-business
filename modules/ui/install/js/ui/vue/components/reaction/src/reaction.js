/**
 * Bitrix UI
 * Reaction picker Vue component
 *
 * @package bitrix
 * @subpackage ui
 * @copyright 2001-2019 Bitrix
 */

import "./reaction.css";
import "./icons.css";

import {Vue} from 'ui.vue';

const ReactionType = Object.freeze({
	none: 'none',
	like: 'like',
	kiss: 'kiss',
	laugh: 'laugh',
	wonder: 'wonder',
	cry: 'cry',
	angry: 'angry',
});

const ReactionOrder = ['like', 'kiss', 'laugh', 'wonder', 'cry', 'angry'];

Vue.component('bx-reaction',
{
	/**
	 * @emits 'set' {values: object}
	 * @emits 'list' {action: string, type: string}
	 */
	props:
	{
		values: { default: {}},
		userId: { default: 0},
		openList: { default: true},
	},
	data()
	{
		return {
			localValues: {},
			userReaction: ReactionType.none,
			buttonAnimate: false,
		}
	},
	created()
	{
		this.localValues = Object.assign({}, this.values);
	},
	watch:
	{
		values(values)
		{
			this.localValues = Object.assign({}, values);
		},
	},
	methods:
	{
		list()
		{
			if (this.openList)
			{
				// todo open list
			}
			this.$emit('list', {values: this.localValues});
		},

		likeIt(emotion = ReactionType.like)
		{
			if (this.userReaction === ReactionType.none)
			{
				emotion = ReactionType.like;
				if (!this.localValues[emotion])
				{
					this.localValues = Object.assign({}, this.localValues, {[emotion]: []});
				}

				this.localValues[emotion].push(this.userId);

				this.buttonAnimate = true;
				setTimeout(() => this.buttonAnimate = false, 400);

				this.$emit('set', {action: 'set', type: emotion});
			}
			else
			{

				if (this.localValues[this.userReaction])
				{
					this.localValues[this.userReaction] = this.localValues[this.userReaction].filter(element => element !== this.userId);
				}

				this.$emit('set', {action: 'remove', type: this.userReaction});
			}
		}
	},
	computed:
	{
		types()
		{
			this.userReaction = ReactionType.none;

			return ReactionOrder.filter(type =>
			{
				if (
					typeof this.localValues[type] === 'undefined'
					|| !(this.localValues[type] instanceof Array)
					|| this.localValues[type].length <= 0
				)
				{
					return false;
				}

				if (
					this.userId > 0
					&& this.userReaction === ReactionType.none
					&& this.localValues[type].includes(this.userId)
				)
				{
					this.userReaction = type;
				}

				return true;

			}).map(type => {
				return {type, count: this.localValues[type].length}
			});
		},

		counter()
		{
			return this.types.map(element => element.count).reduce((result, value) => result + value, 0);
		},

		isTypesShowed()
		{
			if (this.counter <= 0)
			{
				return false;
			}

			if (this.userReaction !== ReactionType.none && this.counter === 1)
			{
				return false;
			}

			return true;
		},

		isMobile()
		{
			const UA = navigator.userAgent.toLowerCase();

			return (
				UA.includes('android')
				|| UA.includes('iphone')
				|| UA.includes('ipad')
				|| UA.includes('bitrixmobile')
			)
		},
	},
	template: `
		<div :class="['ui-vue-reaction', {'ui-vue-reaction-mobile': isMobile}]">
			<transition name="ui-vue-reaction-result-animation">
				<div v-if="isTypesShowed" class="ui-vue-reaction-result" @click="list">
					<transition-group tag="div" class="ui-vue-reaction-result-types" name="ui-vue-reaction-result-type-animation" >
						<span v-for="element in types" :class="['ui-vue-reaction-result-type', 'ui-vue-reaction-icon-'+element.type]" :key="element.type"></span>
					</transition-group>	
					<div class="ui-vue-reaction-result-counter">{{counter}}</div>
				</div>
			</transition>
			<div v-if="userId > 0"  class="ui-vue-reaction-button" @click="likeIt">
				<div class="ui-vue-reaction-button-container">
					<div :class="['ui-vue-reaction-button-icon', 'ui-vue-reaction-icon-'+userReaction, {'ui-vue-reaction-button-pressed': buttonAnimate}]"></div>
				</div>
			</div>
		</div>
	`
});
