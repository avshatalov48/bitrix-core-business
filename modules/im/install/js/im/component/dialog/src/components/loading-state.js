export const LoadingState = {
	data()
	{
		return {
			placeholdersComposition: [],
			placeholderTypes: [0,1],
			placeholderModes: ['self', 'opponent'],
			placeholdersCount: 20
		}
	},
	created()
	{
		for (let i = 0; i < this.placeholdersCount; i++)
		{
			const randomType = Math.floor(Math.random() * this.placeholderTypes.length);
			const randomMode = Math.floor(Math.random() * this.placeholderModes.length);
			this.placeholdersComposition.push({
				index: i,
				type: randomType,
				mode: this.placeholderModes[randomMode],
				classes: this.getItemClasses(randomType, randomMode)
			});
		}
	},
	methods:
	{
		getItemClasses(type, modeIndex)
		{
			const itemClasses = ['im-skeleton-item'];
			if (this.placeholderModes[modeIndex] === 'self')
			{
				itemClasses.push('im-skeleton-item-self');
			}
			else
			{
				itemClasses.push('im-skeleton-item-opponent');
			}

			if (type === 0)
			{
				itemClasses.push('im-skeleton-item--sm');
			}
			else
			{
				itemClasses.push('im-skeleton-item--md');
			}

			return itemClasses;
		}
	},
	// language=Vue
	template: `
		<div class="bx-mobilechat-placeholder-wrap">
			<div class="bx-mobilechat-placeholder-wrap-visible">
				<template v-for="item in placeholdersComposition">
					<div :class="item.classes" :key="item.index">
						<div v-if="item.mode === 'opponent'" class="im-skeleton-logo"></div>
						<div class="im-skeleton-content">
							<template v-if="item.type === 0">
								<div class="im-skeleton-line-row">
									<div style="max-width: 70%" class="im-skeleton-line"></div>
								</div>
								<div class="im-skeleton-line-row">
									<div style="max-width: 100%" class="im-skeleton-line"></div>
									<div style="max-width: 26px; margin-left: auto;" class="im-skeleton-line"></div>
								</div>
							</template>
							<template v-else>
								<div class="im-skeleton-line-row">
									<div style="max-width: 35%" class="im-skeleton-line"></div>
								</div>
								<div class="im-skeleton-line-row">
									<div style="max-width: 100%" class="im-skeleton-line"></div>
								</div>
								<div class="im-skeleton-line-row">
									<div style="max-width: 55%" class="im-skeleton-line"></div>
									<div style="max-width: 26px; margin-left: auto;" class="im-skeleton-line"></div>
								</div>
							</template>
							<div class="im-skeleton-like"></div>
						</div>
					</div>
				</template>
			</div>
		</div>
	`
};