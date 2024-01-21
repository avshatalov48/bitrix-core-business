import { Loc } from 'main.core';

export class ButtonClickHandler
{
	constructor(props)
	{
		this.props = props;
		this.isUsed = props.isUsed;
		this.hasErrors = false;

		this.isPlanRestricted = props.isPlanRestricted;
		this.isUsed1C = props.isUsed1C;
		this.isWithOrdersMode = props.isWithOrdersMode;
		this.isRestrictedAccess = props.isRestrictedAccess;
	}

	handle()
	{
		if (this.isUsed)
		{
			this.handleDisableInventoryManagement();
		}
		else
		{
			this.handleEnableInventoryManagement();
		}
	}

	handleEnableInventoryManagement()
	{
		this.checkAccess();
		this.checkPlanRestriction();
		this.checkUsage1C();
		this.checkWithOrdersMode();
		if (!this.hasErrors)
		{
			this.showEnablePopup();
		}

		this.hasErrors = false;
	}

	handleDisableInventoryManagement()
	{
		this.checkAccess();
		if (!this.hasErrors)
		{
			this.showConfirmDisablePopup();
		}

		this.hasErrors = false;
	}

	showEnablePopup()
	{
		/**
		 * @see DialogEnable.popup()
		 */
		(new BX.Catalog.StoreUse.DialogEnable()).popup();
	}

	showErrorPopup(options)
	{
		/**
		 * @see DialogError.popup()
		 */
		(new BX.Catalog.StoreUse.DialogError(options)).popup();
	}

	showPlanRestrictionSlider()
	{
		top.BX.UI.InfoHelper.show('limit_store_inventory_management');
	}

	showConfirmDisablePopup()
	{
		/**
		 * @see DialogDisable.disablePopup()
		 */
		const dialogDisable = new BX.Catalog.StoreUse.DialogDisable();
		dialogDisable.disablePopup();
	}

	checkAccess(): void
	{
		if (
			this.hasErrors
			|| !this.isRestrictedAccess
		)
		{
			return;
		}

		this.hasErrors = true;
		const helpArticleId = '16556596';
		this.showErrorPopup({
			text: Loc.getMessage(
				'CAT_WAREHOUSE_MASTER_CLEAR_RIGHTS_RESTRICTED_MSGVER_1',
				{
					'#LINK_START#': '<a href="#" class="ui-link ui-link-dashed documents-grid-link">',
					'#LINK_END#': '</a>',
				},
			),
			helpArticleId,
		});
	}

	checkPlanRestriction(): void
	{
		if (
			this.hasErrors
			|| !this.isPlanRestricted
		)
		{
			return;
		}

		this.hasErrors = true;
		this.showPlanRestrictionSlider();
	}

	checkUsage1C(): void
	{
		if (
			this.hasErrors
			|| !this.isUsed1C
		)
		{
			return;
		}

		this.hasErrors = true;
		this.showErrorPopup({
			text: Loc.getMessage('CAT_WAREHOUSE_MASTER_CLEAR_ERROR_1C_USED_MSGVER_1'),
		});
	}

	checkWithOrdersMode(): void
	{
		if (
			this.hasErrors
			|| !this.isWithOrdersMode
		)
		{
			return;
		}

		this.hasErrors = true;
		const helpArticleId = '15718276';
		this.showErrorPopup({
			text: Loc.getMessage(
				'CAT_WAREHOUSE_MASTER_CLEAR_ERROR_ORDER_MODE_MSGVER_1',
				{
					'#LINK_START#': '<a href="#" class="ui-link ui-link-dashed documents-grid-link">',
					'#LINK_END#': '</a>',
				},
			),
			helpArticleId,
		});
	}
}
