this.BX = this.BX || {};
(function (exports,calendar_sectionmanager,main_core,calendar_util) {
	'use strict';

	class Category {
	  constructor(data) {
	    this.updateData(data);
	    this.calendarContext = calendar_util.Util.getCalendarContext();
	    this.rooms = [];
	  }
	  updateData(data) {
	    this.data = data || {};
	    this.id = parseInt(data.ID, 10);
	    this.name = data.NAME;
	  }
	  addRoom(room) {
	    this.rooms.push(room);
	  }
	  getId() {
	    return this.id;
	  }
	  setCheckboxStatus(checkboxStatus) {
	    this.checkboxStatus = checkboxStatus;
	  }
	}

	class CategoryManager$$1 extends calendar_sectionmanager.SectionManager {
	  constructor(data, config) {
	    super(data, config);
	    this.setCategories(data.categories);
	    this.setConfig(config);
	    this.sortCategories();
	    this.permissions = config.perm;
	    this.locationContext = config.locationContext || null;
	  }
	  sortCategories() {
	    this.categoryIndex = {};
	    this.categories = this.categories.sort((a, b) => {
	      if (a.name.toLowerCase() > b.name.toLowerCase()) {
	        return 1;
	      }
	      if (a.name.toLowerCase() < b.name.toLowerCase()) {
	        return -1;
	      }
	      return 0;
	    });
	    this.categories.forEach((category, i) => {
	      this.categoryIndex[category.getId()] = i;
	    });
	  }
	  setCategories(params = []) {
	    this.categories = [];
	    this.categoryIndex = {};
	    params.forEach(categoryData => {
	      let category = new Category(categoryData);
	      this.categories.push(category);
	      this.categoryIndex[category.getId()] = this.categories.length - 1;
	    });
	  }
	  getCategories() {
	    return this.categories;
	  }
	  getCategory(id) {
	    return this.categories[this.categoryIndex[id]];
	  }
	  createCategory(params) {
	    return new Promise(resolve => {
	      params.name = this.checkName(params.name);
	      BX.ajax.runAction('calendar.api.locationajax.createCategory', {
	        data: {
	          name: params.name,
	          rooms: params.rooms
	        }
	      }).then(response => {
	        const categories = response.data || [];
	        this.setCategories(categories);
	        this.sortCategories();
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms.Categories:create', new main_core.Event.BaseEvent({
	          data: {
	            categoryList: categories
	          }
	        }));
	        this.updateLocationContext(categories);
	        resolve(response.data);
	      }, response => {
	        BX.Calendar.Util.displayError(response.errors);
	        resolve(response.data);
	      });
	    });
	  }
	  updateCategory(params) {
	    return new Promise(resolve => {
	      params.name = this.checkName(params.name);
	      BX.ajax.runAction('calendar.api.locationajax.updateCategory', {
	        data: {
	          id: params.id,
	          name: params.name,
	          rooms: {
	            toAddCategory: params.toAddCategory,
	            toRemoveCategory: params.toRemoveCategory
	          }
	        }
	      }).then(response => {
	        const categories = response.data || [];
	        this.setCategories(categories);
	        this.sortCategories();
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms.Categories:create');
	        this.updateLocationContext(categories);
	        resolve(response.data);
	      }, response => {
	        BX.Calendar.Util.displayError(response.errors);
	        resolve(response.data);
	      });
	    });
	  }
	  deleteCategory(id) {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.locationajax.deleteCategory', {
	        data: {
	          id
	        }
	      }).then(response => {
	        const categories = response.data || [];
	        if (!categories.length) {
	          BX.reload();
	        }
	        this.setCategories(categories);
	        this.sortCategories();
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms.Categories:delete', new main_core.Event.BaseEvent({
	          data: {
	            categoryList: categories
	          }
	        }));
	        this.updateLocationContext(categories);
	        resolve(response.data);
	      }, response => {
	        BX.Calendar.Util.displayError(response.errors);
	        resolve(response.data);
	      });
	    });
	  }
	  checkName(name) {
	    if (typeof name === 'string') {
	      name = name.trim();
	      if (CategoryManager$$1.isEmpty(name)) {
	        name = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_CATEGORY');
	      }
	    } else {
	      name = main_core.Loc.getMessage('EC_SEC_SLIDER_NEW_CATEGORY');
	    }
	    return name;
	  }
	  static isEmpty(param) {
	    if (main_core.Type.isArray(param)) {
	      return !param.length;
	    }
	    return param === null || param === undefined || param === '' || param === [] || param === {};
	  }
	  canDo(action) {
	    //actions:view|edit|access
	    return this.permissions[action];
	  }
	  unsetRooms() {
	    this.categories.map(category => category.rooms = []);
	  }
	  handlePullCategoryChanges(params) {
	    if (params.command === 'delete_category') {
	      const categoryId = parseInt(params.ID, 10);
	      if (this.categoryIndex[categoryId]) {
	        this.reloadCategoriesFromDatabase().then(this.reloadDataDebounce());
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms:pull-delete', new main_core.Event.BaseEvent({
	          data: {
	            categoryId
	          }
	        }));
	      } else {
	        this.reloadCategoriesFromDatabase().then(this.reloadDataDebounce());
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms.Categories:pull-delete');
	        calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
	      }
	    } else if (params.command === 'create_category') {
	      this.reloadCategoriesFromDatabase().then(this.reloadDataDebounce());
	      calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms.Categories:pull-create');
	      calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
	    } else if (params.command === 'update_category') {
	      this.reloadCategoriesFromDatabase().then(this.reloadDataDebounce());
	      calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar.Rooms.Categories:pull-update');
	      calendar_util.Util.getBX().Event.EventEmitter.emit('BX.Calendar:doRefresh');
	    } else {
	      this.reloadCategoriesFromDatabase().then(this.reloadDataDebounce());
	    }
	  }
	  reloadCategoriesFromDatabase() {
	    return new Promise(resolve => {
	      BX.ajax.runAction('calendar.api.locationajax.getCategoryList').then(response => {
	        this.setCategories(response.data.categories || []);
	        this.sortCategories();
	        BX.Calendar.Controls.Location.setLocationList(response.data.rooms);
	        resolve(response.data);
	      },
	      // Failure
	      response => {
	        resolve(response.data);
	      });
	    });
	  }
	  unsetCategoryRooms(categoryId) {
	    this.getCategory(categoryId).rooms = [];
	  }
	  getCategoriesWithRooms(rooms) {
	    this.unsetRooms();
	    const categoriesWithRooms = {
	      'default': [],
	      'categories': this.getCategories()
	    };
	    let categoryIndexForRoom;
	    rooms.forEach(room => {
	      categoryIndexForRoom = this.categoryIndex[room.categoryId];
	      if (categoriesWithRooms['categories'][categoryIndexForRoom]) {
	        categoriesWithRooms['categories'][categoryIndexForRoom].addRoom(room);
	      } else {
	        categoriesWithRooms['default'].push(room);
	      }
	    }, this);
	    return categoriesWithRooms;
	  }
	  updateLocationContext() {
	    if (this.locationContext !== null && this.locationContext.roomsManagerFromDB !== null) {
	      this.locationContext.roomsManagerFromDB.reloadRoomsFromDatabase().then(this.locationContext.setValues.bind(this.locationContext));
	    }
	  }
	  getCategoryRooms(category, rooms) {
	    const categoryRooms = [];
	    rooms.forEach(room => {
	      if (category.id === room.categoryId) {
	        categoryRooms.push(room);
	      }
	    });
	    return categoryRooms;
	  }
	}

	exports.CategoryManager = CategoryManager$$1;

}((this.BX.Calendar = this.BX.Calendar || {}),BX.Calendar,BX,BX.Calendar));
//# sourceMappingURL=categorymanager.bundle.js.map
