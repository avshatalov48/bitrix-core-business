import { Dom } from 'main.core';
import { CardBoxTrait } from './card-box-trait';
import { ReplaceLangPhraseTrait } from './replace-lang-phrase-tait';

export const CardBoxB24Solo = {
	mixins: [
		CardBoxTrait,
		ReplaceLangPhraseTrait,
	],
	created()
	{
		this.setBodyClass();
	},
	methods: {
		getHelpLink(): string
		{
			return 'redirect=detail&code=15992592';
		},
		setBodyClass(): void
		{
			Dom.addClass(document.body, 'inventory-management__solo');
		},
	},
	template: `
		<div class="inventory-management__card-box-solo">
			<div class="inventory-management__card-solo-icon" :class="langClass"></div>
			<div
				@click="onClick"
				@mouseenter="mouseenter"
				@mouseleave="mouseleave"
				class="inventory-management__card-item"
				:class="cardItemClass"
				:style="cardItemStyle"
			>
				<div class="inventory-management__card-logo" :class="langClass"></div>
				<div class="inventory-management__card-title">
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_TITLE_B24_SOLO')}}
				</div>
				<ul class="inventory-management__card-list">
					<li class="inventory-management__card-list-item">
						<span class="ui-icon-set --check"></span>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_1_SOLO')}}
					</li>
					<li class="inventory-management__card-list-item">
						<span class="ui-icon-set --check"></span>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_2_SOLO')}}
					</li>
					<li class="inventory-management__card-list-item">
						<span class="ui-icon-set --check"></span>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_3_SOLO')}}
					</li>
				</ul>
				<div class="inventory-management__card-action-box">
					<div class="inventory-management__card-action-item">
						<div class="ui-icon-set --play"></div>
						<div
							v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_1')"
							class="inventory-management__card-action-text"
						>
						</div>
						<action-hint
							:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_1_HINT')"
						></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --refresh-6"></div>
					<div class="inventory-management__card-action-text">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_2')}}
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_2_HINT')"
					></action-hint>
				</div>
				<div class="inventory-management__card-action-item">
					<div class="ui-icon-set --mobile-2"></div>
					<div class="inventory-management__card-action-text">
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_3')}}
					</div>
					<action-hint
						:title="$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_ACTION_ITEM_B24_3_HINT')"
					></action-hint>
				</div>
				</div>
				<div class="inventory-management__card-select-box">
					<div
						v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_SELECT_TEXT_B24_SOLO')"
						class="inventory-management__card-select-text"
					>
					</div>
					<div class="inventory-management__card-select-icon">
						<div class="ui-icon-set --check"></div>
					</div>
				</div>
				<div class="inventory-management__card-control-box">
					<button
						v-if="!isActive"
						class="ui-btn ui-btn-primary ui-btn-round ui-btn-lg"
					>
						{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON_ENABLE_SOLO')}}
					</button>
					<card-box-help
						:link="getHelpLink()"
					>
					</card-box-help>
				</div>
			</div>
		</div>
	`,
};
