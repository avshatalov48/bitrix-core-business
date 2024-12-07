import { CardBoxTrait } from './card-box-trait';
import { ReplaceLangPhraseTrait } from './replace-lang-phrase-tait';

export const CardBoxB24 = {
	mixins: [
		CardBoxTrait,
		ReplaceLangPhraseTrait,
	],
	template: `
		<div
			@mouseenter="mouseenter"
			@mouseleave="mouseleave"
			@click="onClick"
			class="inventory-management__card-item"
			:class="cardItemClass"
			:style="cardItemStyle"
		>
			<div class="inventory-management__card-logo" :class="langClass"></div>
			<div
				v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_TITLE_B24')"
				class="inventory-management__card-title"
			></div>
			<ul class="inventory-management__card-list">
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_1')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_2')}}
				</li>
				<li class="inventory-management__card-list-item">
					<span class="ui-icon-set --check"></span>
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_LIST_ITEM_B24_3')}}
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
					v-html="replaceLangPhrase('CATALOG_INVENTORY_MANAGEMENT_SELECT_TEXT_B24')"
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
					{{$Bitrix.Loc.getMessage('CATALOG_INVENTORY_MANAGEMENT_BUTTON')}}
				</button>
				<card-box-help></card-box-help>
			</div>
		</div>
	`,
};
