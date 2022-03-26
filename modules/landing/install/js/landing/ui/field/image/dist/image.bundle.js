this.BX = this.BX || {};
this.BX.Landing = this.BX.Landing || {};
this.BX.Landing.UI = this.BX.Landing.UI || {};
(function (exports,main_core,landing_loc,landing_main,landing_ui_field_textfield,landing_ui_panel_iconpanel,landing_imageuploader,landing_ui_button_basebutton,landing_imageeditor) {
	'use strict';

	var Image = /*#__PURE__*/function (_TextField) {
	  babelHelpers.inherits(Image, _TextField);

	  function Image(data) {
	    var _this;

	    babelHelpers.classCallCheck(this, Image);
	    _this = babelHelpers.possibleConstructorReturn(this, babelHelpers.getPrototypeOf(Image).call(this, data));
	    _this.dimensions = babelHelpers["typeof"](data.dimensions) === "object" ? data.dimensions : null;
	    _this.create2xByDefault = data.create2xByDefault !== false;
	    _this.uploadParams = babelHelpers["typeof"](data.uploadParams) === "object" ? data.uploadParams : {};
	    _this.onValueChangeHandler = data.onValueChange ? data.onValueChange : function () {};
	    _this.type = _this.content.type || "image";
	    _this.allowClear = data.allowClear;
	    _this.input.innerText = _this.content.src;
	    _this.input.hidden = true;
	    _this.input2x = _this.createInput();
	    _this.input2x.innerText = _this.content.src2x;
	    _this.input2x.hidden = true;

	    _this.layout.classList.add("landing-ui-field-image");

	    if (data.compactMode === true) {
	      _this.layout.classList.add("landing-ui-field-image--compact");
	    }

	    _this.disableAltField = typeof data.disableAltField === "boolean" ? data.disableAltField : false;
	    _this.fileInput = Image.createFileInput(_this.selector);

	    _this.fileInput.addEventListener("change", _this.onFileInputChange.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.linkInput = Image.createLinkInput();
	    _this.linkInput.onInputHandler = _this.onLinkInput.bind(babelHelpers.assertThisInitialized(_this));
	    _this.dropzone = Image.createDropzone(_this.selector);
	    _this.dropzone.hidden = true;

	    _this.dropzone.insertBefore(_this.fileInput, _this.dropzone.firstElementChild);

	    _this.onDragOver = _this.onDragOver.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDragLeave = _this.onDragLeave.bind(babelHelpers.assertThisInitialized(_this));
	    _this.onDrop = _this.onDrop.bind(babelHelpers.assertThisInitialized(_this));

	    _this.dropzone.addEventListener("dragover", _this.onDragOver);

	    _this.dropzone.addEventListener("dragleave", _this.onDragLeave);

	    _this.dropzone.addEventListener("drop", _this.onDrop);

	    _this.clearButton = Image.createClearButton();

	    _this.clearButton.on("click", _this.onClearClick.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.preview = Image.createImagePreview();

	    _this.preview.appendChild(_this.clearButton.layout);

	    _this.preview.style.backgroundImage = "url(" + _this.input.innerText.trim() + ")";
	    _this.onImageDragEnter = _this.onImageDragEnter.bind(babelHelpers.assertThisInitialized(_this));

	    _this.preview.addEventListener("dragenter", _this.onImageDragEnter);

	    _this.loader = new BX.Loader({
	      target: _this.preview
	    });
	    _this.icon = Image.createIcon();
	    _this.image = Image.createImageLayout();

	    _this.image.appendChild(_this.preview);

	    _this.image.appendChild(_this.icon);

	    _this.image.dataset.fileid = _this.content.id;
	    _this.image.dataset.fileid2x = _this.content.id2x;
	    _this.hiddenImage = main_core.Dom.create("img", {
	      props: {
	        className: "landing-ui-field-image-hidden"
	      }
	    });

	    if (main_core.Type.isPlainObject(_this.content) && "src" in _this.content) {
	      _this.hiddenImage.src = _this.content.src;
	    }

	    _this.altField = Image.createAltField();

	    _this.altField.setValue(_this.content.alt);

	    _this.left = Image.createLeftLayout();

	    _this.left.appendChild(_this.dropzone);

	    _this.left.appendChild(_this.image);

	    _this.left.appendChild(_this.hiddenImage);

	    if (_this.description) {
	      _this.left.appendChild(_this.description);
	    }

	    _this.left.appendChild(_this.altField.layout);

	    _this.left.appendChild(_this.linkInput.layout);

	    _this.uploadButton = Image.createUploadButton();

	    _this.uploadButton.on("click", _this.onUploadClick.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.editButton = Image.createEditButton();

	    _this.editButton.on("click", _this.onEditClick.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.right = Image.createRightLayout();

	    _this.right.appendChild(_this.uploadButton.layout);

	    _this.right.appendChild(_this.editButton.layout);

	    _this.form = Image.createForm();

	    _this.form.appendChild(_this.left);

	    _this.form.appendChild(_this.right);

	    _this.layout.appendChild(_this.form);

	    _this.enableTextOnly();

	    if (!_this.input.innerText.trim() || _this.input.innerText.trim() === window.location.toString()) {
	      _this.showDropzone();
	    }

	    if (_this.disableAltField) {
	      _this.altField.layout.hidden = true;
	      _this.altField.layout.style.display = "none";

	      _this.altField.layout.classList.add("landing-ui-hide");
	    }

	    if (_this.content.type === "icon") {
	      _this.type = "icon";
	      _this.classList = _this.content.classList;

	      _this.showPreview();

	      _this.altField.layout.hidden = true;
	      main_core.Dom.addClass(_this.layout, 'landing-ui-field-image-icon');
	    }

	    _this.makeAsLinkWrapper = main_core.Dom.create("div", {
	      props: {
	        className: "landing-ui-field-image-make-as-link-wrapper"
	      },
	      children: [main_core.Dom.create('div', {
	        props: {
	          className: "landing-ui-field-image-make-as-link-button"
	        },
	        children: []
	      })]
	    });
	    _this.url = new BX.Landing.UI.Field.Link({
	      content: _this.content.url || {
	        text: '',
	        href: ''
	      },
	      options: {
	        siteId: landing_main.Main.getInstance().options.site_id,
	        landingId: landing_main.Main.getInstance().id
	      },
	      contentRoot: _this.contentRoot
	    });
	    _this.urlCheckbox = main_core.Dom.create("input", {
	      props: {
	        type: "checkbox"
	      },
	      attrs: {
	        style: "margin-left: 4px;"
	      }
	    });

	    function onCheckboxChange(checkbox, layout) {
	      if (checkbox.checked) {
	        layout.querySelector(".landing-ui-field-link-right").classList.remove("landing-ui-disabled");
	        layout.querySelector(".landing-ui-field-link-url-grid").classList.remove("landing-ui-disabled");
	      } else {
	        layout.querySelector(".landing-ui-field-link-right").classList.add("landing-ui-disabled");
	        layout.querySelector(".landing-ui-field-link-url-grid").classList.add("landing-ui-disabled");
	      }
	    }

	    _this.urlCheckbox.addEventListener('change', function () {
	      onCheckboxChange(this.urlCheckbox, this.url.layout);
	    }.bind(babelHelpers.assertThisInitialized(_this)));

	    _this.urlCheckbox.checked = _this.content.url && _this.content.url.enabled;
	    onCheckboxChange(_this.urlCheckbox, _this.url.layout);

	    _this.url.hrefInput.header.appendChild(_this.urlCheckbox);

	    _this.url.left.hidden = true;

	    _this.makeAsLinkWrapper.appendChild(_this.url.layout);

	    if (!data.disableLink) {
	      _this.layout.appendChild(_this.makeAsLinkWrapper);
	    }

	    _this.content = _this.getValue();
	    BX.DOM.write(function () {
	      this.adjustPreviewBackgroundSize();
	    }.bind(babelHelpers.assertThisInitialized(_this)));

	    if (_this.getValue().type === "background" || _this.allowClear) {
	      _this.clearButton.layout.classList.add("landing-ui-show");
	    }

	    _this.uploader = new landing_imageuploader.ImageUploader({
	      uploadParams: _this.uploadParams,
	      additionalParams: {
	        context: 'imageeditor'
	      },
	      dimensions: _this.dimensions,
	      sizes: ['1x', '2x']
	    });

	    _this.adjustEditButtonState();

	    return _this;
	  }
	  /**
	   * Creates file input
	   * @return {Element}
	   */


	  babelHelpers.createClass(Image, [{
	    key: "onInputInput",
	    value: function onInputInput() {
	      this.preview.src = this.input.innerText.trim();
	    }
	  }, {
	    key: "onImageDragEnter",
	    value: function onImageDragEnter(event) {
	      event.preventDefault();
	      event.stopPropagation();

	      if (!this.imageHidden) {
	        this.showDropzone();
	        this.imageHidden = true;
	      }
	    }
	  }, {
	    key: "onDragOver",
	    value: function onDragOver(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.dropzone.classList.add("landing-ui-active");
	    }
	  }, {
	    key: "onDragLeave",
	    value: function onDragLeave(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.dropzone.classList.remove("landing-ui-active");

	      if (this.imageHidden) {
	        this.imageHidden = false;
	        this.showPreview();
	      }
	    }
	  }, {
	    key: "onDrop",
	    value: function onDrop(event) {
	      event.preventDefault();
	      event.stopPropagation();
	      this.dropzone.classList.remove("landing-ui-active");
	      this.onFileChange(event.dataTransfer.files[0]);
	      this.imageHidden = false;
	    }
	  }, {
	    key: "onFileChange",
	    value: function onFileChange(file) {
	      this.showLoader();
	      this.upload(file).then(this.setValue.bind(this)).then(this.hideLoader.bind(this))["catch"](function (err) {
	        console.error(err);
	        this.hideLoader();
	      }.bind(this));
	    }
	  }, {
	    key: "onFileInputChange",
	    value: function onFileInputChange(event) {
	      this.onFileChange(event.currentTarget.files[0]);
	    }
	  }, {
	    key: "onUploadClick",
	    value: function onUploadClick(event) {
	      this.bindElement = event.currentTarget;
	      event.preventDefault();

	      if (!this.uploadMenu) {
	        this.uploadMenu = BX.Main.MenuManager.create({
	          id: "upload_" + this.selector + +new Date(),
	          bindElement: this.bindElement,
	          bindOptions: {
	            forceBindPosition: true
	          },
	          items: [{
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_UNSPLASH"),
	            onclick: this.onUnsplashShow.bind(this)
	          }, {
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_GOOGLE"),
	            onclick: this.onGoogleShow.bind(this)
	          }, // {
	          // 	text: Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_PARTNER"),
	          // 	className: "landing-ui-disabled"
	          // },
	          {
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_UPLOAD"),
	            onclick: this.onUploadShow.bind(this)
	          }, {
	            text: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_LINK"),
	            onclick: this.onLinkShow.bind(this)
	          }],
	          events: {
	            onPopupClose: function () {
	              this.bindElement.classList.remove("landing-ui-active");

	              if (this.uploadMenu) {
	                this.uploadMenu.destroy();
	                this.uploadMenu = null;
	              }
	            }.bind(this)
	          },
	          targetContainer: this.contentRoot
	        });

	        if (!this.contentRoot) {
	          this.bindElement.parentNode.appendChild(this.uploadMenu.popupWindow.popupContainer);
	        }
	      }

	      this.bindElement.classList.add("landing-ui-active");
	      this.uploadMenu.toggle();

	      if (!this.contentRoot) {
	        var rect = BX.pos(this.bindElement, this.bindElement.parentNode);
	        this.uploadMenu.popupWindow.popupContainer.style.top = rect.bottom + "px";
	        this.uploadMenu.popupWindow.popupContainer.style.left = "auto";
	        this.uploadMenu.popupWindow.popupContainer.style.right = "5px";
	      }
	    }
	  }, {
	    key: "onUnsplashShow",
	    value: function onUnsplashShow() {
	      this.uploadMenu.close();
	      BX.Landing.UI.Panel.Image.getInstance().show("unsplash", this.dimensions, this.loader, this.uploadParams).then(this.upload.bind(this)).then(this.setValue.bind(this)).then(this.hideLoader.bind(this))["catch"](function (err) {
	        console.error(err);
	        this.hideLoader();
	      }.bind(this));
	    }
	  }, {
	    key: "onGoogleShow",
	    value: function onGoogleShow() {
	      this.uploadMenu.close();
	      BX.Landing.UI.Panel.Image.getInstance().show("google", this.dimensions, this.loader, this.uploadParams).then(this.upload.bind(this)).then(this.setValue.bind(this)).then(this.hideLoader.bind(this))["catch"](function (err) {
	        BX.Landing.ErrorManager.getInstance().add({
	          type: 'error',
	          action: 'BAD_IMAGE',
	          hideSupportLink: true
	        });
	        console.error(err);
	        this.hideLoader();
	      }.bind(this));
	    }
	  }, {
	    key: "onUploadShow",
	    value: function onUploadShow() {
	      this.uploadMenu.close();
	      this.fileInput.click();
	    }
	  }, {
	    key: "onLinkShow",
	    value: function onLinkShow() {
	      this.uploadMenu.close();
	      this.showLinkField();
	      this.linkInput.setValue("");
	    }
	  }, {
	    key: "onEditClick",
	    value: function onEditClick(event) {
	      event.preventDefault();
	      this.edit({
	        src: this.hiddenImage.src
	      });
	    }
	  }, {
	    key: "onClearClick",
	    value: function onClearClick(event) {
	      event.preventDefault();
	      this.setValue({
	        src: ""
	      });
	      this.fileInput.value = "";
	      this.showDropzone();
	    }
	  }, {
	    key: "showDropzone",
	    value: function showDropzone() {
	      this.dropzone.hidden = false;
	      this.image.hidden = true;
	      this.altField.layout.hidden = true;
	      this.linkInput.layout.hidden = true;
	    }
	  }, {
	    key: "showPreview",
	    value: function showPreview() {
	      this.dropzone.hidden = true;
	      this.image.hidden = false;
	      this.altField.layout.hidden = false;
	      this.linkInput.layout.hidden = true;
	    }
	  }, {
	    key: "showLinkField",
	    value: function showLinkField() {
	      this.dropzone.hidden = true;
	      this.image.hidden = true;
	      this.altField.layout.hidden = true;
	      this.linkInput.layout.hidden = false;
	    }
	  }, {
	    key: "onLinkInput",
	    value: function onLinkInput(value) {
	      var tmpImage = main_core.Dom.create("img");
	      tmpImage.src = value;

	      tmpImage.onload = function () {
	        this.showPreview();
	        this.setValue({
	          src: value,
	          src2x: value
	        });
	      }.bind(this);
	    }
	  }, {
	    key: "showLoader",
	    value: function showLoader() {
	      if (this.dropzone && !this.dropzone.hidden) {
	        this.loader.show(this.dropzone);
	        return;
	      }

	      this.loader.show(this.preview);
	    }
	  }, {
	    key: "hideLoader",
	    value: function hideLoader() {
	      this.loader.hide();
	    }
	    /**
	     * Handles click event on input field
	     * @param {MouseEvent} event
	     */

	  }, {
	    key: "onInputClick",
	    value: function onInputClick(event) {
	      event.preventDefault();
	    }
	    /**
	     * @inheritDoc
	     * @return {boolean}
	     */

	  }, {
	    key: "isChanged",
	    value: function isChanged() {
	      var lastValue = BX.Landing.Utils.clone(this.content);
	      var currentValue = BX.Landing.Utils.clone(this.getValue());

	      if (lastValue.url && main_core.Type.isString(lastValue.url)) {
	        lastValue.url = BX.Landing.Utils.decodeDataValue(lastValue.url);
	      }

	      if (currentValue.url && main_core.Type.isString(currentValue.url)) {
	        currentValue.url = BX.Landing.Utils.decodeDataValue(currentValue.url);
	      }

	      return JSON.stringify(lastValue) !== JSON.stringify(currentValue);
	    }
	    /**
	     * Adjusts preview background image size
	     */

	  }, {
	    key: "adjustPreviewBackgroundSize",
	    value: function adjustPreviewBackgroundSize() {
	      var img = main_core.Dom.create("img", {
	        attrs: {
	          src: this.getValue().src
	        }
	      });

	      img.onload = function () {
	        var preview = this.preview.getBoundingClientRect();
	        var position = "cover";

	        if (img.width > preview.width || img.height > preview.height) {
	          position = "contain";
	        }

	        if (img.width < preview.width && img.height < preview.height) {
	          position = "auto";
	        }

	        BX.DOM.write(function () {
	          this.preview.style.backgroundSize = position;
	        }.bind(this));
	      }.bind(this);
	    }
	    /**
	     * @param {object} value
	     * @param {boolean} [preventEvent = false]
	     */

	  }, {
	    key: "setValue",
	    value: function setValue(value, preventEvent) {
	      if (value.type !== "icon") {
	        if (!value || !value.src) {
	          this.input.innerText = "";
	          this.input2x.innerText = "";
	          this.preview.removeAttribute("style");
	          this.input.dataset.ext = "";
	          this.showDropzone();
	        } else {
	          this.input.innerText = value.src;
	          this.input2x.innerText = value.src2x || '';
	          this.preview.style.backgroundImage = "url(\"" + (value.src2x || value.src) + "\")";
	          this.preview.id = BX.util.getRandomString();
	          this.hiddenImage.src = value.src2x || value.src;
	          this.showPreview();
	        }

	        this.image.dataset.fileid = value && value.id ? value.id : -1;
	        this.image.dataset.fileid2x = value && value.id2x ? value.id2x : -1;
	        this.classList = [];
	      } else {
	        this.preview.style.backgroundImage = null;
	        this.classList = value.classList;
	        this.icon.innerHTML = "<span class=\"" + value.classList.join(" ") + "\"></span>";
	        this.showPreview();
	        this.type = "icon";
	        this.altField.layout.hidden = true;
	        this.altField.setValue("");
	        this.input.innerText = "";
	      }

	      if (value.url) {
	        this.url.setValue(value.url);
	      }

	      this.adjustPreviewBackgroundSize();
	      this.adjustEditButtonState();
	      this.hideLoader();
	      this.onValueChangeHandler(this);
	      BX.fireEvent(this.layout, "input");
	      var event = new BX.Event.BaseEvent({
	        data: {
	          value: this.getValue()
	        },
	        compatData: [this.getValue()]
	      });

	      if (!preventEvent) {
	        this.emit('change', event);
	      }
	    }
	  }, {
	    key: "adjustEditButtonState",
	    value: function adjustEditButtonState() {
	      var value = this.getValue();

	      if (BX.Type.isStringFilled(value.src)) {
	        this.editButton.enable();
	      } else {
	        this.editButton.disable();
	      }
	    }
	  }, {
	    key: "reset",
	    value: function reset() {
	      this.setValue({
	        type: this.getValue().type,
	        id: -1,
	        src: "",
	        alt: ""
	      });
	    }
	    /**
	     * Gets field value
	     * @return {{src, [alt]: string, [title]: string, [url]: string, [type]: string}}
	     */

	  }, {
	    key: "getValue",
	    value: function getValue() {
	      var fileId = parseInt(this.image.dataset.fileid);
	      var fileId2x = parseInt(this.image.dataset.fileid2x);
	      fileId = fileId === fileId ? fileId : -1;
	      fileId2x = fileId2x === fileId2x ? fileId2x : -1;
	      var value = {
	        type: "",
	        src: "",
	        id: fileId,
	        id2x: fileId2x,
	        src2x: "",
	        alt: "",
	        url: ""
	      };

	      if (this.type === "background") {
	        value.type = "background";
	        value.src = this.input.innerText.trim();
	        value.src2x = this.input2x.innerText.trim();
	        value.id = fileId;
	        value.id2x = fileId2x;
	      }

	      if (this.type === "image") {
	        value.type = "image";
	        value.src = this.input.innerText.trim();
	        value.src2x = this.input2x.innerText.trim();
	        value.id = fileId;
	        value.id2x = fileId2x;
	        value.alt = this.altField.getValue();
	      }

	      if (this.type === "icon") {
	        value.type = "icon";
	        value.classList = this.classList;
	      }

	      value.url = Object.assign({}, this.url.getValue(), {
	        enabled: this.urlCheckbox.checked
	      });
	      return value;
	    }
	  }, {
	    key: "edit",
	    value: function edit(data) {
	      landing_imageeditor.ImageEditor.edit({
	        image: data.src,
	        dimensions: this.dimensions
	      }).then(function (file) {
	        return this.upload(file, {
	          context: "imageEditor"
	        });
	      }.bind(this)).then(function (result) {
	        this.setValue(result);
	      }.bind(this)); // Analytics hack

	      var tmpImage = document.createElement('img');
	      var imageSrc = "/bitrix/images/landing/close.svg";
	      imageSrc = BX.util.add_url_param(imageSrc, {
	        action: "openImageEditor"
	      });
	      tmpImage.src = imageSrc + "?" + +new Date();
	    }
	    /**
	     * @param {File|Blob} file
	     * @param {object} [additionalParams]
	     */

	  }, {
	    key: "upload",
	    value: function upload(file, additionalParams) {
	      if (file.type && (file.type.includes('text') || file.type.includes('html'))) {
	        BX.Landing.ErrorManager.getInstance().add({
	          type: "error",
	          action: "BAD_IMAGE"
	        });
	        return Promise.reject({
	          type: "error",
	          action: "BAD_IMAGE"
	        });
	      }

	      this.showLoader();
	      var isPng = main_core.Type.isStringFilled(file.type) && file.type.includes('png');
	      var checkSize = new Promise(function (resolve) {
	        var sizes = isPng ? ['2x'] : ['1x', '2x'];

	        if (this.create2xByDefault === false) {
	          var image = document.createElement('img');
	          var objectUrl = URL.createObjectURL(file);
	          var dimensions = this.dimensions;

	          image.onload = function () {
	            URL.revokeObjectURL(objectUrl);

	            if ((this.width >= dimensions.width || this.height >= dimensions.height || this.width >= dimensions.maxWidth || this.height >= dimensions.maxHeight) === false) {
	              sizes = isPng ? ['2x'] : ['1x'];
	            }

	            resolve(sizes);
	          };

	          image.src = objectUrl;
	        } else {
	          resolve(sizes);
	        }
	      }.bind(this));
	      return checkSize.then(function (allowedSizes) {
	        var sizes = function () {
	          if (this.create2xByDefault === false && BX.Type.isArrayFilled(allowedSizes)) {
	            return allowedSizes;
	          }

	          return isPng ? ['2x'] : ['1x', '2x'];
	        }.bind(this)();

	        return this.uploader.setSizes(sizes).upload(file, additionalParams).then(function (result) {
	          this.hideLoader();

	          if (sizes.length === 1) {
	            return result[0];
	          }

	          return Object.assign({}, result[0], {
	            src2x: result[1].src,
	            id2x: result[1].id
	          });
	        }.bind(this));
	      }.bind(this));
	    }
	  }], [{
	    key: "createFileInput",
	    value: function createFileInput(id) {
	      return main_core.Dom.create("input", {
	        props: {
	          className: "landing-ui-field-image-dropzone-input"
	        },
	        attrs: {
	          accept: "image/*",
	          type: "file",
	          id: "file_" + id,
	          name: "picture"
	        }
	      });
	    }
	    /**
	     * Creates link input field
	     * @return {TextField}
	     */

	  }, {
	    key: "createLinkInput",
	    value: function createLinkInput() {
	      var field = new landing_ui_field_textfield.TextField({
	        id: "path_to_image",
	        placeholder: landing_loc.Loc.getMessage("LANDING_IMAGE_UPLOAD_MENU_LINK_LABEL")
	      });
	      field.enableTextOnly();
	      field.layout.hidden = true;
	      return field;
	    }
	    /**
	     * Creates dropzone
	     * @param {string} id
	     * @return {Element}
	     */

	  }, {
	    key: "createDropzone",
	    value: function createDropzone(id) {
	      return main_core.Dom.create("label", {
	        props: {
	          className: "landing-ui-field-image-dropzone"
	        },
	        children: [main_core.Dom.create("div", {
	          props: {
	            className: "landing-ui-field-image-dropzone-text"
	          },
	          html: "<div class=\"landing-ui-field-image-dropzone-title\">" + landing_loc.Loc.getMessage("LANDING_IMAGE_DROPZONE_TITLE") + "</div>" + "<div class=\"landing-ui-field-image-dropzone-subtitle\">" + landing_loc.Loc.getMessage("LANDING_IMAGE_DROPZONE_SUBTITLE") + "</div>"
	        })],
	        attrs: {
	          "for": "file_" + id
	        }
	      });
	    }
	    /**
	     * Creates clear button
	     * @return {BaseButton}
	     */

	  }, {
	    key: "createClearButton",
	    value: function createClearButton() {
	      return new landing_ui_button_basebutton.BaseButton("clear", {
	        className: "landing-ui-field-image-action-button-clear"
	      });
	    }
	    /**
	     * Creates image preview
	     * @return {Element}
	     */

	  }, {
	    key: "createImagePreview",
	    value: function createImagePreview() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-preview-inner"
	        }
	      });
	    }
	    /**
	     * Creates icon layout
	     * @return {Element}
	     */

	  }, {
	    key: "createIcon",
	    value: function createIcon() {
	      return main_core.Dom.create("span", {
	        props: {
	          className: "landing-ui-field-image-preview-icon"
	        }
	      });
	    }
	    /**
	     * Creates image layout
	     * @return {Element}
	     */

	  }, {
	    key: "createImageLayout",
	    value: function createImageLayout() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-preview"
	        }
	      });
	    }
	    /**
	     * Creates alt field
	     * @return {TextField}
	     */

	  }, {
	    key: "createAltField",
	    value: function createAltField() {
	      var field = new landing_ui_field_textfield.TextField({
	        placeholder: landing_loc.Loc.getMessage("LANDING_FIELD_IMAGE_ALT_PLACEHOLDER"),
	        className: "landing-ui-field-image-alt",
	        textOnly: true
	      });
	      return field;
	    }
	    /**
	     * Creates left layout
	     * @return {Element}
	     */

	  }, {
	    key: "createLeftLayout",
	    value: function createLeftLayout() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-left"
	        }
	      });
	    }
	    /**
	     * Creates upload button
	     * @return {BaseButton}
	     */

	  }, {
	    key: "createUploadButton",
	    value: function createUploadButton() {
	      return new landing_ui_button_basebutton.BaseButton("upload", {
	        text: landing_loc.Loc.getMessage("LANDING_FIELD_IMAGE_UPLOAD_BUTTON"),
	        className: "landing-ui-field-image-action-button"
	      });
	    }
	    /**
	     * Creates edit button
	     * @return {BaseButton}
	     */

	  }, {
	    key: "createEditButton",
	    value: function createEditButton() {
	      var field = new landing_ui_button_basebutton.BaseButton("edit", {
	        text: landing_loc.Loc.getMessage("LANDING_FIELD_IMAGE_EDIT_BUTTON"),
	        className: "landing-ui-field-image-action-button"
	      });
	      return field;
	    }
	    /**
	     * Creates right layout
	     * @return {Element}
	     */

	  }, {
	    key: "createRightLayout",
	    value: function createRightLayout() {
	      return main_core.Dom.create("div", {
	        props: {
	          className: "landing-ui-field-image-right"
	        }
	      });
	    }
	    /**
	     * Creates form
	     * @return {Element}
	     */

	  }, {
	    key: "createForm",
	    value: function createForm() {
	      return main_core.Dom.create("form", {
	        props: {
	          className: "landing-ui-field-image-container"
	        },
	        attrs: {
	          method: "post",
	          enctype: "multipart/form-data"
	        },
	        events: {
	          submit: function submit(event) {
	            event.preventDefault();
	          }
	        }
	      });
	    }
	  }]);
	  return Image;
	}(landing_ui_field_textfield.TextField);

	exports.Image = Image;

}((this.BX.Landing.UI.Field = this.BX.Landing.UI.Field || {}),BX,BX.Landing,BX.Landing,BX.Landing.UI.Field,BX.Landing.UI.Panel,BX.Landing,BX.Landing.UI.Button,BX.Landing));
//# sourceMappingURL=image.bundle.js.map
