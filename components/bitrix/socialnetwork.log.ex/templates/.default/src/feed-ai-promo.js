import { AnglePosition, PromoVideoPopup, PromoVideoPopupEvents } from 'ui.promo-video-popup';
import { CopilotPromoPopup } from 'ai.copilot-promo-popup';
import { ajax } from 'main.core';
import { BannerDispatcher } from 'ui.banner-dispatcher';

type Promo = {
	type: string,
	isShown: boolean,
};

type Params = {
	feedPromo: Promo,
	chatPromo: Promo,
};

export class FeedAiPromo
{
	#params: Params;

	constructor(params: Params)
	{
		this.#params = params;
	}

	show(): void
	{
		this.#bindPromo(this.#params.feedPromo, this.#getFeedPopup());
		this.#bindPromo(this.#params.chatPromo, this.#getChatPopup());
	}

	#bindPromo(promo: Promo, popup: PromoVideoPopup): void
	{
		if (promo.isShown)
		{
			return;
		}

		BannerDispatcher.high.toQueue((onDone) => {
			popup.subscribe(PromoVideoPopupEvents.HIDE, this.onCopilotPromoHide.bind(this, promo, onDone));
			popup.show();
		});
	}

	#getFeedPopup(): ?PromoVideoPopup
	{
		const blogContainer = document.querySelector('#sonet_log_microblog_container');

		if (!blogContainer)
		{
			return null;
		}

		return CopilotPromoPopup.createByPresetId({
			presetId: CopilotPromoPopup.Preset.LIVE_FEED_EDITOR,
			targetOptions: blogContainer,
			offset: {
				left: 65,
				top: 0,
			},
			angleOptions: {
				position: AnglePosition.TOP,
				offset: 73,
			},
		});
	}

	#getChatPopup(): ?PromoVideoPopup
	{
		const copilotChatButton = document.querySelector('#bx-im-bar-copilot');

		if (!copilotChatButton)
		{
			return null;
		}

		return CopilotPromoPopup.createByPresetId({
			presetId: CopilotPromoPopup.Preset.CHAT,
			targetOptions: copilotChatButton,
			offset: {
				left: -(PromoVideoPopup.getWidth()),
				top: -67,
			},
			angleOptions: {
				position: AnglePosition.RIGHT,
				offset: 30,
			},
		});
	}

	onCopilotPromoHide(promo: Promo, onDone: Function): void
	{
		ajax.runAction('socialnetwork.promotion.setViewed', { data: { promotion: promo.type } })
			.catch((err) => {
				console.error(err);
			})
		;

		onDone();
	}
}
