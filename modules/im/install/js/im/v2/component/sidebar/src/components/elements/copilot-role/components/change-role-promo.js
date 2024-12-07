import { PromoVideoPopup, PromoVideoPopupEvents } from 'ui.promo-video-popup';
import { Loc } from 'main.core';

import { MessengerPopup } from 'im.v2.component.elements';

import '../css/change-role-promo.css';

// @vue/component
export const ChangeRolePromo = {
	name: 'ChangeRolePromo',
	components: { MessengerPopup },
	props:
	{
		bindElement: {
			type: Object,
			required: true,
		},
	},
	emits: ['hide', 'accept'],
	computed:
	{
		text(): string
		{
			return Loc.getMessage('IM_SIDEBAR_COPILOT_CHANGE_ROLE_PROMO_TEXT', {
				'[copilot_color]': '<em class="bx-im-copilot-change-role-promo__copilot">',
				'[/copilot_color]': '</em>',
			});
		},
		videoSource(): string
		{
			const basePath = '/bitrix/js/im/v2/component/sidebar/src/components/elements/copilot-role/css/videos/';
			const sources = {
				ru: 'copilot-roles-promo-ru.webm',
				en: 'copilot-roles-promo-en.webm',
			};

			const language = Loc.getMessage('LANGUAGE_ID');

			return language === 'ru' ? `${basePath}${sources.ru}` : `${basePath}${sources.en}`;
		},
	},
	created()
	{
		this.promoPopup = new PromoVideoPopup({
			videoSrc: this.videoSource,
			title: 'Copilot',
			text: this.text,
			targetOptions: this.bindElement,
			angleOptions: {
				position: BX.UI.AnglePosition.RIGHT,
				offset: 98,
			},
			colors: {
				iconBackground: '#8e52ec',
				title: '#b095dc',
			},
			icon: BX.UI.IconSet.Main.COPILOT_AI,
			offset: {
				top: -125,
				left: -510,
			},
		});

		this.promoPopup.subscribe(PromoVideoPopupEvents.ACCEPT, this.onAccept);
		this.promoPopup.subscribe(PromoVideoPopupEvents.HIDE, this.onHide);
	},
	mounted()
	{
		this.promoPopup.show();
	},
	beforeUnmount()
	{
		if (!this.promoPopup)
		{
			return;
		}
		this.promoPopup.hide();

		this.promoPopup.unsubscribe(PromoVideoPopupEvents.ACCEPT, this.onAccept);
		this.promoPopup.unsubscribe(PromoVideoPopupEvents.HIDE, this.onHide);
	},
	methods:
	{
		onHide()
		{
			this.$emit('hide');
			this.promoPopup.hide();
		},
		onAccept()
		{
			this.$emit('accept');
			this.promoPopup.hide();
		},
		loc(phraseCode: string, replacements: {[string]: string} = {}): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode, replacements);
		},
	},
	template: `
		<template></template>
	`,
};
