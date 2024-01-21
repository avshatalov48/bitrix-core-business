import { Dom, ajax } from 'main.core';
import { EventTypes } from '../../const/event';
import { SpacesListStates } from '../../const/spaces-list-state';

// @vue/component

const MAX_QUANTITY_SHOW_BTN_TOGGLE_BLOCK = 3;

export const CollapsedModeToggleBlock = {
	name: 'ToggleBlock',
	data(): Object
	{
		return {
			dataSpacesContent: document.getElementById('sn-spaces__content'),
			counterImpressionsBtnShowToggleBlock: 0,
			showBtnShowToggleBlock: false,
		};
	},
	created()
	{
		this.$bitrix.eventEmitter.subscribe(EventTypes.showBtnToggleBlock, this.showBtnToggleBlockHandler);
	},
	beforeUnmount()
	{
		this.$bitrix.eventEmitter.unsubscribe(EventTypes.showBtnToggleBlock, this.showBtnToggleBlockHandler);
	},
	computed: {
		isShowHintBtn(): boolean
		{
			return !(this.counterImpressionsBtnShowToggleBlock > MAX_QUANTITY_SHOW_BTN_TOGGLE_BLOCK);
		},
		btnClassName(): string
		{
			return this.showBtnShowToggleBlock ? '' : '--hide';
		},
	},
	mounted() {
		if (localStorage.counterImpressionsBtnShowToggleBlock)
		{
			this.counterImpressionsBtnShowToggleBlock = localStorage.counterImpressionsBtnShowToggleBlock;
		}
	},
	methods: {
		showBtnToggleBlockHandler()
		{
			if (this.isShowHintBtn && !this.showBtnShowToggleBlock)
			{
				this.showBtnShowToggleBlock = true;
			}
		},
		hideBtnToggleBlockHandler()
		{
			this.showBtnShowToggleBlock = false;
		},
		hideHintBtn()
		{
			localStorage.counterImpressionsBtnShowToggleBlock = ++this.counterImpressionsBtnShowToggleBlock;
			this.showBtnShowToggleBlock = false;
		},
		toggleList()
		{
			this.showBtnShowToggleBlock = false;

			if (this.$store.getters.spacesListState === SpacesListStates.collapsed)
			{
				this.$bitrix.eventEmitter.emit(EventTypes.changeSpaceListState, SpacesListStates.default);
				this.saveState(SpacesListStates.default);
				Dom.removeClass(this.dataSpacesContent, '--list-collapsed-mode');
			}
			else
			{
				this.$bitrix.eventEmitter.emit(EventTypes.changeSpaceListState, SpacesListStates.collapsed);
				this.saveState(SpacesListStates.collapsed);
				Dom.addClass(this.dataSpacesContent, '--list-collapsed-mode');
			}
		},
		saveState(state: string)
		{
			ajax.runAction('socialnetwork.api.space.saveListSate', {
				data: { spacesListState: state },
			});
		},
	},
	template: `
		<div class="sn-spaces__toggle-block">
			<div class="sn-spaces__toggle-image"></div>
			<div 
				class="sn-spaces__toggle-wrapper"
				@click="toggleList"
			>
				<div 
					ref="toggle-btn"
					class="sn-spaces__toggle-btn" 
					id="sn-spaces__toggle-btn"
					data-id="sn-spaces__toggle-btn"
				>
					<div class="ui-icon-set --chevron-left" style="--ui-icon-set__icon-size: 15px;"></div>
				</div>
			</div>
			<div
				class="sn-spaces__btn-show-toggle-block"
				:class="btnClassName"
				data-id="sn-spaces__btn-hint_show-toggle-block"
				v-if="isShowHintBtn"
				@click="hideHintBtn"
				@mouseleave="hideBtnToggleBlockHandler"
			>
				<div class="ui-icon-set --chevron-left" style="--ui-icon-set__icon-size: 15px;"></div>
			</div>
		</div>
	`,
};
