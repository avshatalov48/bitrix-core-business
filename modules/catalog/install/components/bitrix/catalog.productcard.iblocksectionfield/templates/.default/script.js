(function (exports,main_core,main_core_events) {
	'use strict';

	var SectionSelector = /*#__PURE__*/function () {
	  function SectionSelector() {
	    var settings = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : {};
	    babelHelpers.classCallCheck(this, SectionSelector);
	    this.selectorId = settings.selectorId;
	    this.selectorHiddenId = settings.selectorHiddenId;
	    this.selectedItems = settings.selectedItems;
	    this.iblockId = settings.iblockId;
	    this.initSelector();
	  }

	  babelHelpers.createClass(SectionSelector, [{
	    key: "initSelector",
	    value: function initSelector() {
	      if (!this.selector) {
	        this.selector = new BX.UI.EntitySelector.TagSelector({
	          id: this.selectorId,
	          multiple: true,
	          placeholder: main_core.Loc.getMessage('CATALOG_IBLOCKSECTIONFIELD_PLACEHOLDER'),
	          textBoxWidth: '100%',
	          dialogOptions: {
	            height: 300,
	            id: this.selectorId,
	            context: 'catalog-sections',
	            enableSearch: false,
	            multiple: false,
	            dropdownMode: true,
	            selectedItems: this.selectedItems,
	            searchTabOptions: {
	              stub: true,
	              stubOptions: {
	                title: main_core.Loc.getMessage('CATALOG_IBLOCKSECTIONFIELD_IS_EMPTY_TITLE'),
	                subtitle: main_core.Loc.getMessage('CATALOG_IBLOCKSECTIONFIELD_IS_EMPTY_SUBTITLE'),
	                arrow: true
	              }
	            },
	            searchOptions: {
	              allowCreateItem: true
	            },
	            events: {
	              'Item:onSelect': this.setSelectedInputs.bind(this, 'Item:onSelect'),
	              'Item:onDeselect': this.setSelectedInputs.bind(this, 'Item:onDeselect'),
	              'Search:onItemCreateAsync': this.addNewSection.bind(this)
	            },
	            entities: [{
	              id: 'section',
	              options: {
	                'iblockId': this.iblockId
	              },
	              dynamicSearch: true,
	              dynamicLoad: true
	            }]
	          }
	        });
	      }

	      this.selector.renderTo(document.getElementById(this.selectorId));
	    }
	  }, {
	    key: "setSelectedInputs",
	    value: function setSelectedInputs(eventName, event) {
	      event.target.hide();
	      var selectedSections = event.getData().item.getDialog().getSelectedItems();

	      if (Array.isArray(selectedSections)) {
	        var htmlInputs = '';
	        var selectedItemsId = [];
	        selectedSections.forEach(function (section) {
	          htmlInputs += '<input type="hidden" name="IBLOCK_SECTION[]" value="' + section['id'] + '" />';
	          selectedItemsId.push(section['id']);
	        });
	        document.getElementById(this.selectorHiddenId).innerHTML = htmlInputs;
	        main_core_events.EventEmitter.emit(eventName, selectedItemsId);
	      }
	    }
	  }, {
	    key: "addNewSection",
	    value: function addNewSection(event) {
	      return new Promise(function (resolve, reject) {
	        /** @type  {BX.UI.EntitySelector.Item} */
	        var _event$getData = event.getData(),
	            searchQuery = _event$getData.searchQuery;
	        /** @type  {BX.UI.EntitySelector.Dialog} */


	        var dialog = event.getTarget();
	        main_core.ajax.runComponentAction('bitrix:catalog.productcard.iblocksectionfield', 'addSection', {
	          mode: 'ajax',
	          data: {
	            iblockId: this.iblockId,
	            name: searchQuery.getQuery()
	          }
	        }).then(function (response) {
	          var item = dialog.addItem({
	            id: response.data.id,
	            entityId: 'tag',
	            title: searchQuery.getQuery(),
	            tabs: dialog.getRecentTab().getId()
	          });

	          if (item) {
	            item.select();
	          }
	        }).bind(this);
	        resolve();
	      }.bind(this));
	    }
	  }]);
	  return SectionSelector;
	}();

	main_core.Reflection.namespace('BX.Catalog').SectionSelector = SectionSelector;

}((this.window = this.window || {}),BX,BX.Event));
//# sourceMappingURL=script.js.map
