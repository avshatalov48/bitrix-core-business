/* eslint-disable */
this.BX = this.BX || {};
this.BX.Main = this.BX.Main || {};
(function (exports,main_core,main_core_events) {
	'use strict';

	let _ = t => t,
	  _t,
	  _t2,
	  _t3,
	  _t4,
	  _t5;
	class Lazyloadtotalcount {
	  register(containerNode) {
	    const container = containerNode != null ? containerNode : document.body;
	    container.querySelectorAll('lazy-load-total-count:not([data-registered])').forEach(node => {
	      const gridId = node.getAttribute('grid-id');
	      main_core.Dom.append(this.getCounterLabel(), node);
	      main_core.Dom.append(this.getCounterContainer(gridId), node);
	      main_core_events.EventEmitter.subscribe('Grid::updated', event => {
	        const grid = event.compatData[0];
	        if (grid.getId() === gridId) {
	          this.register(grid.getContainer());
	        }
	      });
	      node.dataset.registered = true;
	    });
	  }
	  getCounterLabel() {
	    return main_core.Tag.render(_t || (_t = _`
			<span class="main-pagination-lazyload-count_label">
				${0}: 
			</span>
		`), main_core.Loc.getMessage('MAIN_PAGE_NAVIGATION_TOTAL_COUNTER_AMOUNT'));
	  }
	  getCounterContainer(gridId) {
	    const counter = main_core.Tag.render(_t2 || (_t2 = _`<span class="main-pagination-lazyload-count_container"></span>`));
	    main_core.Dom.append(main_core.Tag.render(_t3 || (_t3 = _`
				<a class="main-pagination-lazyload-count_counter" onclick="${0}">
					${0}
				</a>
			`), this.handleCounterClick.bind(this, gridId, counter), main_core.Loc.getMessage('MAIN_PAGE_NAVIGATION_TOTAL_COUNTER_SHOW_LINK')), counter);
	    return counter;
	  }
	  handleCounterClick(gridId, counter) {
	    var _BX$Main$gridManager$;
	    main_core.Dom.clean(counter);
	    main_core.Dom.append(main_core.Tag.render(_t4 || (_t4 = _`
				<svg class="main-pagination-lazyload-count_loader" viewBox="25 25 50 50">
					<circle class="main-pagination-lazyload-count_loader-path" r="20" cx="50" cy="50" stroke-width="1" stroke-miterlimit="10" fill="none"></circle>
				</svg>
			`)), counter);
	    const grid = (_BX$Main$gridManager$ = BX.Main.gridManager.getById(gridId)) == null ? void 0 : _BX$Main$gridManager$.instance;
	    if (grid) {
	      grid.getData().request('', null, null, 'get_total_rows_count', response => {
	        const res = JSON.parse(response);
	        main_core.Dom.clean(counter);
	        main_core.Dom.append(main_core.Tag.render(_t5 || (_t5 = _`<span class="main-pagination-lazyload-count_count">${0}</span>`), res.payload.totalCount), counter);
	      });
	    }
	  }
	}

	exports.Lazyloadtotalcount = Lazyloadtotalcount;

}((this.BX.Main.Pagination = this.BX.Main.Pagination || {}),BX,BX.Event));
//# sourceMappingURL=lazyloadtotalcount.bundle.js.map
