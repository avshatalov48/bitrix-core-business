import { DialogReferenceClassName } from "im.const";

export const Placeholder1 = {
	props: ['element'],
	created()
	{
		const modes = ['self', 'opponent'];
		const randomIndex = Math.floor(Math.random() * modes.length);
		this.mode = modes[randomIndex];
	},
	computed:
	{
		itemClasses()
		{
			const itemClasses = ['im-skeleton-item', 'im-skeleton-item--sm', `${DialogReferenceClassName.listItem}-${this.element.id}`];
			if (this.mode === 'self')
			{
				itemClasses.push('im-skeleton-item-self');
			}
			else
			{
				itemClasses.push('im-skeleton-item-opponent');
			}

			return itemClasses;
		}
	},
	template: `
		<div :class="itemClasses" :key="element.templateId">
			<div v-if="mode === 'opponent'" class="im-skeleton-logo"></div>
			<div class="im-skeleton-content">
				<div class="im-skeleton-line-row">
					<div style="max-width: 70%" class="im-skeleton-line"></div>
				</div>
				<div class="im-skeleton-line-row">
					<div style="max-width: 100%" class="im-skeleton-line"></div>
					<div style="max-width: 26px; margin-left: auto;" class="im-skeleton-line"></div>
				</div>
				<div class="im-skeleton-like"></div>
			</div>
		</div>
	`
};