import { Tag } from 'main.core';

export class Skeleton
{
	#size: boolean;

	constructor(size: number = 473)
	{
		this.#size = size;
	}

	get(): HTMLElement
	{
		return Tag.render`
			<div style="height: ${this.#size}px;" class="popup-with-header-skeleton__wrap">
				<div class="popup-with-header-skeleton__header">
					<div class="popup-with-header-skeleton__header-top">
						<div class="popup-with-header-skeleton__header-circle">
							<div class="popup-with-header-skeleton__header-circle-inner"></div>
						</div>
						<div style="width: 100%;">
							<div style="margin-bottom: 12px; max-width: 219px; height: 6px; background: rgba(255,255,255,.8);" class="popup-with-header-skeleton__line"></div>
							<div style="max-width: 119px; height: 4px;" class="popup-with-header-skeleton__line"></div>
						</div>
					</div>
					<div class="popup-with-header-skeleton__header-bottom">
						<div class="popup-with-header-skeleton__header-bottom-circle-box">
							<div class="popup-with-header-skeleton__header-bottom-circle"></div>
							<div class="popup-with-header-skeleton__header-bottom-circle-blue"></div>
						</div>
						<div style="width: 100%;">
							<div style="margin-bottom: 9px; max-width: 193px; height: 5px;" class="popup-with-header-skeleton__line"></div>
							<div style="margin-bottom: 15px; max-width: 163px; height: 5px;" class="popup-with-header-skeleton__line"></div>
							<div style="margin-bottom: 9px; max-width: 156px; height: 2px;" class="popup-with-header-skeleton__line"></div>
							<div style="margin-bottom: 9px; max-width: 93px; height: 2px;" class="popup-with-header-skeleton__line"></div>
						</div>
					</div>
				</div>
				<div class="popup-with-header-skeleton__bottom">
					${this.#getInnerBlock()}
					${this.#getInnerBlock()}
					${this.#getInnerBlock()}
				</div>
			</div>
		`;
	}

	#getInnerBlock(): HTMLElement
	{
		return Tag.render`
			<div class="popup-with-header-skeleton__bottom-inner">
				<div class="popup-with-header-skeleton__bottom-left">
					<div style="margin-bottom: 11px; max-width: 193px; height: 5px;" class="popup-with-header-skeleton__line"></div>
					<div style="margin-bottom: 17px; max-width: 163px; height: 5px;" class="popup-with-header-skeleton__line"></div>
					<div style="margin-bottom: 9px; max-width: 168px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
					<div style="margin-bottom: 9px; max-width: 131px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
					<div style="margin-bottom: 9px; max-width: 150px; height: 3px; background: rgba(149,156,164,.23);" class="popup-with-header-skeleton__line --dark-animation"></div>
					<div style="margin-bottom: 9px; max-width: 56px; height: 5px; background: rgba(32,102,176,.23);" class="popup-with-header-skeleton__line"></div>
				</div>
				<div class="popup-with-header-skeleton__bottom-right">
					<div class="popup-with-header-skeleton-btn"></div>
					<div style="margin: 0 auto; max-width: 36px; height: 3px; background: #d9d9d9;" class="popup-with-header-skeleton__line"></div>
				</div>
			</div>
		`;
	}
}
