import 'ui.design-tokens';
import 'ui.buttons';
import 'ui.fonts.opensans';
import './css/style.css';
import { EventEmitter } from 'main.core.events';
import { Dom, Tag, Loc, Type } from 'main.core';

export class MessageGrid
{
	EXPAND_LICENSE_URL = '/settings/license_all.php';
	#loadingMessagesStubInGridWrapper;
	#gridWrapper;
	#gridStub;
	#id;
	#allRowsSelectedStatus = false;
	#panel;
	#checkboxNodeForCheckAll;

	constructor(mailboxIsAvailable = false)
	{
		this.mailboxIsAvailable = mailboxIsAvailable;
		if (typeof MessageGrid.instance === 'object') {
			return MessageGrid.instance
		}
		MessageGrid.instance = this;

		EventEmitter.subscribe('Grid::allRowsSelected', (event) =>
		{
			if(this.#compareGrid(event)) this.#allRowsSelectedStatus = true;
		})

		EventEmitter.subscribe('Grid::allRowsUnselected', (event) =>
		{
			if(this.#compareGrid(event)) this.#allRowsSelectedStatus = false;
		})

		EventEmitter.subscribe('Grid::updated', (event) =>
		{
			if(this.#compareGrid(event) && this.#allRowsSelectedStatus)
			{
				if(this.#checkboxNodeForCheckAll !== undefined)
				{
					this.#checkboxNodeForCheckAll.checked = true;
				}
				this.selectAll();
			}
		})

		EventEmitter.subscribe('Mail::resetGridSelection', (event) =>
		{
			this.#allRowsSelectedStatus = false;
		})

		EventEmitter.subscribe('Mail::directoryChanged', () =>
		{
			this.#allRowsSelectedStatus = false;
		})

		EventEmitter.subscribe('Grid::thereSelectedRows', (event) =>
		{
			if(this.#compareGrid(event)) this.#allRowsSelectedStatus = false;
		})

		EventEmitter.subscribe('Grid::updated', (event) => {
			const [grid] = event.getCompatData();
			if(grid !== undefined && Type.isFunction(grid.getId) && grid.getId() === this.getId()){
				this.replaceTheBlankEmailStub();
			}
		});
		this.replaceTheBlankEmailStub();

		return MessageGrid.instance
	}

	setGridStub(gridStub)
	{
		this.#gridStub = gridStub;
	}

	setGridWrapper(gridWrapper)
	{
		this.#gridWrapper = gridWrapper;
	}

	getGridWrapper()
	{
		return this.#gridWrapper;
	}

	getGridStub()
	{
		return this.#gridStub;
	}

	enableLoadingMessagesStub()
	{
		if(this.getGridWrapper()!==undefined)
		{

			Dom.addClass(this.getGridWrapper(), 'mail-msg-list-grid-hidden');
			this.#loadingMessagesStubInGridWrapper = this.getGridStub().appendChild(
				Tag.render`
					<div class="mail-msg-list-grid-loader mail-msg-list-grid-loader-animate">
						<div class="mail-msg-list-grid-loader-inner">
							<img src="/bitrix/images/mail/mail-loader.svg" alt="Load...">
						</div>
					</div>`
			);

			setTimeout(()=>{
				if(this.#loadingMessagesStubInGridWrapper !== undefined)
				{
					this.#loadingMessagesStubInGridWrapper.remove();
					Dom.removeClass(this.getGridWrapper(), 'mail-msg-list-grid-hidden');
				}
			}, 15000);
		}
	}

	replaceTheBlankEmailStub()
	{
		let blankEmailStubs = document.getElementsByClassName("main-grid-row main-grid-row-empty main-grid-row-body");
		if(blankEmailStubs.length > 0)
		{
			let blankEmailStub = blankEmailStubs[0];
			if(blankEmailStub.firstElementChild.firstElementChild)
			{
				if (this.mailboxIsAvailable)
				{
					blankEmailStub.firstElementChild.firstElementChild.replaceWith(
						Tag.render`
						<div class="mail-msg-list-grid-empty">
						<div class="mail-msg-list-grid-empty-inner">
						<div class="mail-msg-list-grid-empty-title">${Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TITLE")}</div>
						<p class="mail-msg-list-grid-empty-text">${Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TEXT_1")}</p>
						<p class="mail-msg-list-grid-empty-text">${Loc.getMessage("MAIL_MSG_LIST_GRID_EMPTY_TEXT_2")}</p>
						</div>
						</div>`
					);
				}
				else
				{
					let tariffButton = Tag.render`
					<button class="ui-btn ui-btn-round ui-btn-lg ui-btn-success">
						${Loc.getMessage("MAIL_MSG_LIST_MAILBOX_TARIFF_RESTRICTIONS_BUTTON")}
					</button>`;

					tariffButton.onclick = (event) => {
						event.preventDefault();
						window.open(this.EXPAND_LICENSE_URL, '_blank')
					};

					const tariffPlug = Tag.render`
					<div class="mail-msg-list-grid-empty">
						<div class="mail-msg-list-grid-empty-inner">
							<div class="mail-msg-list-grid-empty-title">${Loc.getMessage("MAIL_MSG_LIST_MAILBOX_TARIFF_RESTRICTIONS_TITLE")}</div>
							<p class="mail-msg-list-grid-empty-text">${Loc.getMessage("MAIL_MSG_LIST_MAILBOX_TARIFF_RESTRICTIONS_TEXT_1")}</p>
							<p class="mail-msg-list-grid-empty-text">${Loc.getMessage("MAIL_MSG_LIST_MAILBOX_TARIFF_RESTRICTIONS_TEXT_2")}</p>
						</div>
						<br/>
					</div>`;

					tariffPlug.append(tariffButton);
					blankEmailStub.firstElementChild.firstElementChild.replaceWith(tariffPlug);
				}
			}
		}
	}

	setCheckboxNodeForCheckAll(node)
	{
		this.#checkboxNodeForCheckAll = node;
	}

	setPanel(panel)
	{
		this.#panel = panel;
	}

	getPanel()
	{
		return this.#panel;
	}

	hidePanel()
	{
		const panel = this.getPanel();
		if(panel && Type.isFunction(panel.hidePanel())){
			this.getPanel().hidePanel();
		}
	}

	#compareGrid(eventWithGrid,grid)
	{
		if(this.getId() !== undefined)
		{
			if(grid===undefined && eventWithGrid.getCompatData())
			{
				[grid] = eventWithGrid.getCompatData();
			}
			if(grid !== undefined && Type.isFunction(grid.getId) && grid.getId()===this.getId()) return true;
		}
		return false;
	}

	setAllRowsSelectedStatus()
	{
		this.#allRowsSelectedStatus = true;
	}

	unsetAllRowsSelectedStatus()
	{
		this.#allRowsSelectedStatus = false;
	}

	reloadTable()
	{
		this.getGrid().reloadTable();
		this.getGrid().tableUnfade();
	}

	setGridId(gridId)
	{
		if (this.#id === gridId) {
			return;
		}
		this.#id = gridId;
		this.grid = BX.Main.gridManager.getInstanceById(gridId);

	}

	selectAll()
	{
		this.getGrid().getRows().selectAll();
	}

	getId()
	{
		return this.#id;
	}

	getCountDisplayed()
	{
		if(this.getGrid())
		{
			return this.getGrid().getRows().getCountDisplayed();
		}
	}

	getGrid()
	{
		return this.grid;
	}

	getRows()
	{
		return this.getGrid().getRows().getBodyChild();
	}

	getRowById(id)
	{
		return this.getGrid().getRows().getById(id);
	}

	getRowNodeById(id)
	{
		return this.getRowById(id).getNode();
	}

	getSelectedIds()
	{
		return this.getGrid().getRows().getSelectedIds();
	}

	hideRowByIds(ids)
	{
		for (let i = 0; i < ids.length; i++)
		{
			const rowNode = this.getRowNodeById(ids[i]);
			Dom.style(rowNode, 'display', 'none');
		}
	}

	resetGridSelection()
	{
		EventEmitter.emit(window,'Mail::resetGridSelection');
		this.getGrid().getRows().unselectAll();
		this.getGrid().adjustCheckAllCheckboxes();
		this.hidePanel();
	}

	openGridSettingsWindow()
	{
		this.getGrid().getSettingsWindow()._onSettingsButtonClick();
	}
}