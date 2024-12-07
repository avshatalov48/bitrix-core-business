import { Counter } from 'ui.cnt';

import '../css/comment-counter.css';

export const CommentCounter = {
	props: {
		commentsCount: Number,
	},
	methods: {
		renderCounter(): void
		{
			const value = this.commentsCount;
			const color = value ? Counter.Color.PRIMARY : Counter.Color.GRAY;

			this.$refs.counter.innerHTML = '';
			new Counter({ value, color, size: Counter.Size.LARGE }).renderTo(this.$refs.counter);
		},
	},
	mounted(): void
	{
		this.renderCounter();
	},
	watch: {
		commentsCount(): void
		{
			this.renderCounter();
		},
	},
	template: `
		<div class="calendar-open-events-list-item-comment-counter">
			<div class="ui-icon-set --chats-1"></div>
			<div ref="counter"></div>
		</div>
	`,
};
