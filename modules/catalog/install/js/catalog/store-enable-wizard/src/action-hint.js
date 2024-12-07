import { Popup } from 'main.popup';

const ActionHint = {
	props: {
		title: {
			type: String,
		},
	},
	data()
	{
		return {
			timer: null,
		};
	},
	created()
	{
		this.popup = new Popup({
			bindElement: null,
			darkMode: true,
			angle: {
				offset: 82,
			},
			content: this.title,
			maxWidth: 220,
			offsetLeft: 115 / 2 - 57.5,
			animation: 'fading-slide',
		});
	},
	beforeUnmount()
	{
		this.closePopup();
	},
	methods: {
		mouseenter(ev)
		{
			this.timer = setTimeout(() => {
				this.popup.setBindElement(ev.target);
				this.popup.show();
			}, 400);
		},
		mouseleave()
		{
			this.closePopup();
		},
		closePopup()
		{
			clearTimeout(this.timer);
			this.popup.close();
		},
	},
	template: `
		<div
			@mouseenter="mouseenter"
			@mouseleave="mouseleave"
			class="inventory-management__action-hint">
		</div>
	`,
};

export {
	ActionHint,
};
