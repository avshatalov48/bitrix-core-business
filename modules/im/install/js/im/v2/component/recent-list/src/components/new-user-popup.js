import {EventEmitter} from 'main.core.events';
import {EventType} from 'im.v2.const';

// @vue/component
export const NewUserPopup = {
	name: 'NewUserPopup',
	props: {
		title: {type: String, required: true},
		text: {type: String, required: true}
	},
	emits: ['click', 'close'],
	mounted()
	{
		BX.MessengerProxy.playNewUserSound();
		this.setCloseTimer(5000);

		this.onClosePopupHandler = this.onClosePopup.bind(this);
		EventEmitter.subscribe(EventType.dialog.closePopup, this.onClosePopupHandler);
	},
	beforeUnmount()
	{
		EventEmitter.unsubscribe(EventType.dialog.closePopup, this.onClosePopupHandler);
	},
	methods:
	{
		onClick()
		{
			this.$emit('click');
			this.$emit('close');
		},
		onMouseOver()
		{
			clearTimeout(this.closeTimeout);
		},
		onMouseLeave()
		{
			this.setCloseTimer(2000);
		},
		setCloseTimer(time: number)
		{
			this.closeTimeout = setTimeout(() => {
				this.$emit('close');
			}, time);
		},
		onClosePopup()
		{
			this.$emit('close');
		}
	},
	// language=Vue
	template: `
		<Transition name="bx-im-recent-new-user-popup">
			<div @click="onClick" @mouseover="onMouseOver" @mouseleave="onMouseLeave" class="bx-im-recent-new-user-popup">
				<div class="bx-im-recent-new-user-popup-title">{{ title }}</div>
				<div class="bx-im-recent-new-user-popup-text">{{ text }}</div>
			</div>
		</Transition>
	`
};