(function(wnd) {
var 
// Will speed up references to undefined, and allows munging its name.
UNDEF,
// Global entry
AU = wnd.Aurigma ? (wnd.Aurigma.ImageUploaderFlash || {}) : {},

objectCache = {
    _cache: {},
    _uid: 0,
    put: function (obj) {
        if (typeof obj != 'undefined') {
            var id = obj.id();
            if (!id) {
                id = '_obj' + (++this._uid);
                obj.id(id);
            }
            obj._cacheId = id;
            this._cache[id] = obj;
        }
    },
    get: function (id) {
        var c = this._cache;
        if (c[id]) {
            return c[id];
        } else {
            for (var i = 0, imax = c.length; i < imax; i++) {
                if (c[i] && c[i].id() === id) {
                    return c[i];
                }
            }
            return null;
        }
    }
};

var 
browser = AU.browser = new (function () {
    var a = navigator.userAgent.toLowerCase();
    this.isOpera = (a.indexOf("opera") > -1);
    this.isKonq = (a.indexOf("konqueror") > -1);
    this.isChrome = (a.indexOf("chrome") > -1);
    this.isSafari = (a.indexOf("safari") > -1) && !this.isChrome;
    this.isKhtml = this.isSafari || this.isKonq || this.isChrome;
    this.isIE = (a.indexOf("msie") != -1) && !this.isOpera;
    this.isIE6XPSP2 = this.isIE && (a.indexOf("sv1") > -1);
    this.isIE7 = this.isIE && (a.indexOf("msie 7.0") > -1);
    this.isIE8 = this.isIE && (a.indexOf("msie 8.0") > -1);
    this.isBeforeIE6XPSP2 = this.isIE && !this.isIE6XPSP2 && !this.isIE7 && !this.isIE8;
    this.isWinIE = this.isIE && (a.indexOf('mac') == -1);
    this.isIE64 = this.isIE && (a.indexOf('win64') > -1);
    this.isFF = (a.indexOf('firefox') > -1);
    this.isWindowsOS = (navigator.platform.indexOf("Win") > -1);
})(),

showInfo = function (msg) {
    AU.debug().showInfo(msg);
},

showError = function (msg) {
    AU.debug().showError(msg);
},

//encode reserved html symbols
htmlencode = function (text) {
    if (text && typeof text.replace === 'function') {
        var entities = [
            ["\"", "&#34;"],
            ["'", "&#39;"],
            ["&", "&#38;"],
            ["<", "&#60;"],
            [">", "&#62;"]
        ];
        var rg = /\"|\'|&|<|>/g;
        return text.replace(rg, function (c) {
            for (var i = 0, cnt = entities.length; i < cnt; i++) {
                if (c == entities[i][0]) {
                    return entities[i][1];
                }
            }
            return c;
        });
    } else {
        return text;
    }
},

getCurrentUrl = wnd.getCurrentUrl = function () {
    return document.location.href;
},

// get handler name for event
// it will be global function called from uploader control
getGlobalHandlerName = function (uploader, eventName) {
    // pattern: '__uploaderID_eventName'
    return ["__" + uploader.id(), "_", eventName].join("");
},

// get uploader parameters in array of { name: 'name', value: 'value' } objects
getParams = function () {
    ///	<summary>
    ///		For internal use only! Get params array for rendering control.
    ///	</summary>
    var params = [], i, cnt, value;
    //simple properties just copy to array
    if (this._simpleProperties && this._simpleProperties.length > 0) {
        for (i = 0, cnt = this._simpleProperties.length; i < cnt; i++) {
            if (!this._simpleProperties[i].isAttribute) {
                value = this[this._simpleProperties[i].name]() + "";
                if (value != "null" && value != "undefined") {
                    params.push({ name: this._simpleProperties[i].render || this._simpleProperties[i].name, value: value });
                }
            }
        }
    }
    // getParams from object properties
    if (this._objectProperties && this._objectProperties.length > 0) {
        for (i = 0, cnt = this._objectProperties.length; i < cnt; i++) {
            value = this[this._objectProperties[i].name]();
            if (typeof value.getParams === "function") {
                params = params.concat(value.getParams());
            }
        }
    }
    return params;
},

extend = function (target, options, deep) {
    // copy reference to target object
    var name, src, copy;

    // Handle case when target is a string or something (possible in deep copy)
    if (typeof target !== "object" && Object.prototype.toString.call(target) !== "[object Function]") {
        target = {};
    }

    // Only deal with non-null/undefined values
    if (options != null) {
        // Extend the base object
        for (name in options) {
            src = target[name];
            copy = options[name];

            // Prevent never-ending loop
            if (target === copy) {
                continue;
            }

            // Recurse if we're merging object literal values or arrays
            if (deep && (typeof copy === "object")) {
                if (!src) {
                    if (Object.prototype.toString.call(copy) === "[object Array]") {
                        src = [];
                    } else {
                        src = {};
                    }
                }

                // Never move original objects, clone them
                target[name] = extend(src, copy, deep);

                // Don't bring in undefined values
            } else if (copy !== undefined) {
                target[name] = copy;
            }
        }
    }

    // Return the modified object
    return target;
},

propertyMaker = {
    createSimpleProperty: function (obj, property) {
        ///	<summary>
        ///		Create method with the name property.name for object obj.
        ///     Method works like property: if we pass argument it set new value, 
        ///     if we call method without arguments it return stored value.
        ///	</summary>
        ///	<param name="obj" type="Object">
        ///	    The object for which to create a method.
        ///	</param>
        ///	<param name="property" type="Object">
        ///	    The object which contains method name and defaut value.
        ///	</param>
        if (typeof property.name === 'string') {
            var field = "_" + property.name;
            obj[field] = property.defaultValue;
            obj[property.name] = function () {
                if (arguments.length > 0) {
                    // Set value if we pass parameter
                    this[field] = arguments[0];
                } else {
                    // Return value if call method without parameter
                    return this[field];
                }
            };
        }
    },
    createObjectProperty: function (obj, property, uploader) {
        ///	<summary>
        ///		Create method with the name property.name for object obj.
        ///     Method works like property: if we pass argument it set new value, 
        ///     if we call method wothout arguments it return stored value.
        ///	</summary>
        ///	<param name="obj" type="Object">
        ///	    The object for which to create a method.
        ///	</param>
        ///	<param name="property" type="Object">
        ///	    The object which contains method name and defaut value.
        ///	</param>

        // Create new object for this property
        var valueObj = new property.type();
        var field = "_" + property.name;
        obj[field] = valueObj;
        // Init created object
        // In init usually we create object's simple and object properties
        if (typeof property.type.init === 'function') {
            property.type.init(valueObj, uploader);
        } else {
            var errMsg = "Can not create property '" + property.name + "'. 'init' method is not a function.";
            showError(errMsg);
            throw errMsg;
        }

        obj[property.name] = function () {
            if (arguments.length > 0) {
                var initObj = arguments[0];
                for (var name in initObj) {
                    if (typeof this[field][name] === 'function') {
                        this[field][name](initObj[name]);
                    } else {
                        showError('Control have not ' + name + ' propery');
                    }
                }
            } else {
                return this[field];
            }
        }
    },
    createControlProperty: function (obj, property) {
        ///	<summary>
        ///		Create method with the name property.name for object obj.
        ///     Method works like property: if we pass argument it set new value,
        ///     if we call method wothout arguments it return stored value.
        ///     The value get from or set to the underlying flash, activex or java control.
        ///	</summary>
        ///	<param name="obj" type="Object">
        ///	    The object for which to create a method.
        ///	</param>
        ///	<param name="property" type="Object">
        ///	    The object which contains method name and defaut value.
        ///	</param>
        if (typeof property.name === 'string' && !property.renderOnly) {
            try {
                // save current property value
                var oldValue = obj[property.name](),
                    uploader = obj._uploader,
                    element = uploader.getElement();

                var getter = property.getter, setter = property.setter;
                if (getter === 1 || setter === 1) {
                    var propName = property.render || property.name;
                    propName = propName.charAt(0).toUpperCase() + propName.substring(1);
                    if (getter === 1) {
                        getter = "get" + propName;
                    }
                    if (setter === 1) {
                        setter = "set" + propName;
                    }
                }

                obj[property.name] = function () {
                    // set value if we pass parameter
                    if (arguments.length > 0) {
                        return element.callFlash(setter, arguments[0]);
                    } else {
                        return element.callFlash(getter);
                    }
                };

                // Set value
                if (oldValue !== UNDEF && setter) {
                    obj[property.name](oldValue);
                }

            } catch (err) {
                var msg = "Can't create property '" + property.name + "'.\r\n";
                if (typeof err === "string") {
                    err = msg + err;
                } else {
                    err.message = msg + (err.message || err.description || err);
                }
                throw err;
            }
        }
    },
    createAttributeProperty: function (obj, property) {
        ///	<summary>
        ///		Create method with the name property.name for object obj.
        ///     Method works like property: if we pass argument it set new value,
        ///     if we call method wothout arguments it return stored value.
        ///     The value get from or set to the underlying element <b>attribute</b>.
        ///	</summary>
        ///	<param name="obj" type="Object">
        ///	    The object for which to create a method.
        ///	</param>
        ///	<param name="property" type="Object">
        ///	    The object which contains method name and defaut value.
        ///	</param>
        var uploader = obj._uploader, element = uploader.getElement();
        obj[property.name] = function () {
            // set value if we pass parameter
            if (arguments.length > 0) {
                return element[property.name] = arguments[0];
            } else {
                //return value otherwise
                return element[property.name];
            }
        };
    },
    createMethod: function (obj, method) {
        var uploader = obj._uploader, element = uploader.getElement();
        var controlMethodName = method.controlMethodName || method.name;
        obj[method.name] = function () {
            // We need to pass incominag arguments to control's function.
            // 'apply' and 'call' does not work for activex and java functions.
            var arg = arguments;
            switch (arg.length) {
                case 0:
                    return element.callFlash(controlMethodName);
                case 1:
                    return element.callFlash(controlMethodName, arg[0]);
                case 2:
                    return element.callFlash(controlMethodName, arg[0], arg[1]);
                case 3:
                    return element.callFlash(controlMethodName, arg[0], arg[1], arg[2]);
                case 4:
                    return element.callFlash(controlMethodName, arg[0], arg[1], arg[2], arg[3]);
                case 5:
                    return element.callFlash(controlMethodName, arg[0], arg[1], arg[2], arg[3], arg[4]);
            }
        };
    },
    _typicalInit: function (obj, uploader) {
        ///	<summary>
        ///		DO NOT CALL THIS FUNCTION, use 'init' method for particular type
        ///     Usually 'init' should create simple and object properties for particular type,
        ///     and in this case we can use this function in type's init method.
        ///	</summary>
        ///	<param name="obj" type="Object">
        ///	    The object for which to create a method.
        ///	</param>
        ///	<param name="uploader" type="Object">
        ///	    The parent uploader object.
        ///	</param>

        // Add reference to the parent uploader object
        obj._uploader = uploader;

        // Init simple properties
        if (obj._simpleProperties) {
            for (var i = 0, imax = obj._simpleProperties.length; i < imax; i++) {
                propertyMaker.createSimpleProperty(obj, obj._simpleProperties[i]);
            }
        }

        // Init object properties
        if (obj._objectProperties) {
            for (var i = 0, imax = obj._objectProperties.length; i < imax; i++) {
                propertyMaker.createObjectProperty(obj, obj._objectProperties[i], uploader);
            }
        }
    },
    _typicalReinit: function (obj) {
        // Map uploader properties to the control
        if (obj._simpleProperties) {
            for (var i = 0, imax = obj._simpleProperties.length; i < imax; i++) {
                var property = obj._simpleProperties[i];
                if (!property.isAttribute) {
                    propertyMaker.createControlProperty(obj, property);
                }
            }
        }
        if (obj._objectProperties) {
            for (var i = 0, imax = obj._objectProperties.length; i < imax; i++) {
                var property = obj._objectProperties[i];
                if (property.type && typeof property.type.reinit === 'function') {
                    property.type.reinit(obj[property.name]());
                }
            }
        }
        if (obj._methods && obj._methods.length > 0) {
            for (var i = 0, imax = obj._methods.length; i < imax; i++) {
                propertyMaker.createMethod(obj, obj._methods[i]);
            }
        }
    }
};
// Flash Player version detection script.
AU.flashDetector = {
    detectVersion: function() {
        ///	<summary>
        ///	    Detect installed flash version. The method returns array of 
        /// three numbers, where first number - major version number, 
        /// second number - minor version number, third number - number of revision. 
        /// For example the latest flash version now is 10.0.45, so method return 
        /// this array [10, 0, 45]. If flash is not installed we return [-1, -1, -1] array.
        ///	</summary>
        ///	<returns type="Array" Array with installed version number. />
        var version = null,
            plugins = navigator.plugins,
            plg;
        if (plugins && typeof ((plg = plugins["Shockwave Flash 2.0"] || plugins["Shockwave Flash"])) !== 'undefined') {
            var flashDescr = plg.description; // Shockwave Flash 10.0 r42
            var matches = /^.+\s+(\d+).(\d+)\s+r(\d+)$/.exec(flashDescr);
            var verMajor = parseInt(matches[1], 10) || 0;
            var verMinor = parseInt(matches[2], 10) || 0;
            var revVer = parseInt(matches[3], 10) || 0;
            version = [verMajor, verMinor, revVer];
        } else if (wnd.ActiveXObject) {
            try {
                // version will be set for 7.X or greater players
                var axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.7");
            } catch (e) {
                try {
                    // version will be set for 6.X players only
                    axo = new ActiveXObject("ShockwaveFlash.ShockwaveFlash.6");

                    // installed player is some revision of 6.0
                    // GetVariable("$version") crashes for versions 6.0.22 through 6.0.29,
                    // so we have to be careful. 

                    // default to the first public version
                    version = [6, 0, 21]; // "6,0,21,0";

                    // throws if AllowScripAccess does not exist (introduced in 6.0r47)		
                    axo.AllowScriptAccess = "always";

                } catch (e) { }
            }

            if (axo) {
                try {
                    var versionStr = axo.GetVariable("$version");
                    matches = /^\S+\s+(\d+),(\d+),(\d+)(,\d+)?$/.exec(versionStr);
                    verMajor = parseInt(matches[1], 10) || 0;
                    verMinor = parseInt(matches[2], 10) || 0;
                    revVer = parseInt(matches[3], 10) || 0;
                    version = [verMajor, verMinor, revVer];
                } catch (e) { }
            }
        }

        if (!version) {
            version = [-1, -1, -1]; // Flash player is not installed
        }

        return version;
    }
}
AU.debug = function () {
    ///	<summary>
    ///		Get configure debug object.
    ///	</summary>
    ///	<returns type="$au.debug" />
    if (!this._debug) {
        var f = function () {
            this.constructor = AU.debug;
        };
        f.prototype = AU.debug.prototype;
        this._debug = new f();
    }
    return this._debug;
};

AU.debug.prototype = {
    __class: true,

    // disable debug by default
    _level: 0,

    _mode: ['popup', 'console', 'alert'],

    mode: function (mode) {
        ///	<summary>
        ///		Get or set debug mode. Debug mode specifies where 
        ///     the debug messages will be shown.
        ///	</summary>
        ///	<param name="mode" type="Array">
        ///     An array with possible debug places: 'popup', 'console' or 'alert'.
        ///	</param>
        ///	<returns type="Number" />
        if (arguments.length > 0) {
            var v = arguments[0];
            if (v instanceof Array) {
                this._mode = arguments[0];
            } else {
                this._mode = [arguments[0]];
            }
        } else {
            return this._mode;
        }
    },

    level: function (level) {
        ///	<summary>
        ///		Get or set debug level.
        ///     Possible values:
        ///         0 - no debug messages,
        ///         1 - errors,
        ///         2 - errors and debug messages from uploader,
        ///         3 - errors, debug messages from uploader and informaion messages.
        ///     Default value is 0.
        ///	</summary>
        ///	<param name="level" type="Number">
        ///     Debug level
        ///	</param>
        ///	<returns type="Number" />
        if (arguments.length > 0) {
            this._level = arguments[0];
        } else {
            return this._level;
        }
    },

    ///	<summary>
    ///		Show error message.
    ///	</summary>
    ///	<param name="msg" type="String">
    ///     Messsage
    ///	</param>
    showError: function (msg) {
        this.showMessage(msg, 1);
    },

    ///	<summary>
    ///		Show message from Image Uploader. 
    ///     The method called from Image Uploader trace event.
    ///	</summary>
    ///	<param name="msg" type="String">
    ///     Messsage
    ///	</param>
    _showUploaderMessage: function (msg) {
        this.showMessage(msg, 2);
    },

    ///	<summary>
    ///		Show information message.
    ///	</summary>
    ///	<param name="msg" type="String">
    ///     Messsage
    ///	</param>
    showInfo: function (msg) {
        this.showMessage(msg, 3);
    },

    showMessage: function (msg, level) {
        if (level > this._level) {
            return;
        }

        for (var i = 0, imax = this._mode.length; i < imax; i++) {
            switch (this._mode[i]) {
                case 'console':
                    // check the console available
                    if (typeof wnd.console != 'undefined' && console.log) {
                        // print message to console
                        console.log(msg);
                        return;
                    }
                    break;
                case 'popup':
                    if (!this._popupWindow || this._popupWindow.closed) {
                        // open popup window
                        this._popupWindow = wnd.open('', 'ImageUploaderDebugWindow',
                                'width=300,height=200,menubar=1,status=1,scrollbars=1,resizable=1');

                        // popup has been blocked
                        if (!this._popupWindow)
                            break;

                        var list = this._popupWindow.document.getElementsByTagName("ol");
                        if (!list || list.length == 0) {
                            // write log window html
                            var html = [];
                            html.push('<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">\n');
                            html.push('<html xmlns="http://www.w3.org/1999/xhtml">\n');
                            html.push('<head>');
                            html.push('<title>Image Uploader Log Window</title>');
                            html.push('<style type="text/css">* { margin:0; padding: 0; font-family: "Lucida Console", Monaco, monospace; } li { border-bottom:1px solid #aaa; padding: 10px; } li:nth-child(odd) { background-color: #fafafa; }</style>');
                            html.push('</head>');
                            html.push('<body>');
                            html.push('<input type="button" value="Clear" onclick=\'document.getElementsByTagName("ol")[0].innerHTML = "";\' />');
                            html.push('<ol>');
                            html.push('</ol>');
                            html.push('<input type="button" value="Clear" onclick=\'document.getElementsByTagName("ol")[0].innerHTML = "";\' />');
                            html.push('</body>');
                            html.push('</html>');
                            this._popupWindow.document.write(html.join(''));
                        }
                    }
                    // add log message
                    var el = this._popupWindow.document.createElement("li"),
                        pre = this._popupWindow.document.createElement("pre"),
                        text = this._popupWindow.document.createTextNode(msg);
                    pre.appendChild(text);
                    el.appendChild(pre);
                    this._popupWindow.document.getElementsByTagName("ol")[0].appendChild(el);
                    return;
                case 'alert':
                    // show alert
                    alert(msg);
                    return;
            }
        }
    }
};
var formHelper = (function() {

    function fieldValue(el) {
        var n = el.name, t = el.type, tag = el.tagName.toLowerCase();

        if (!n || el.disabled ||
            t == 'reset' || t == 'button' || t == 'file' || t == 'submit' || t == 'image' ||
		    (t == 'checkbox' || t == 'radio') && !el.checked ||
		    tag == 'select' && el.selectedIndex == -1) {
            return null;
        }

        if (tag == 'select') {
            var index = el.selectedIndex;
            if (index < 0) return null;
            var a = [], ops = el.options;
            var one = (t == 'select-one');
            var max = (one ? index + 1 : ops.length);
            for (var i = (one ? index : 0); i < max; i++) {
                var op = ops[i];
                if (op.selected) {
                    var v = op.value;
                    if (!v) // for IE...
                        v = (op.attributes && op.attributes['value'] && !(op.attributes['value'].specified)) ? op.text : op.value;
                    if (one) return v;
                    a.push(v);
                }
            }
            return a;
        }
        return el.value;
    }

    function formToArray(form) {
        var a = [];
        var els = form.elements;
        if (!els) return a;
        for (var i = 0, cnt = els.length; i < cnt; i++) {
            var el = els[i];
            var n = el.name;
            if (!n) continue;

            var v = fieldValue(el);
            if (v && v.constructor == Array) {
                for (var j = 0, jmax = v.length; j < jmax; j++)
                    a.push({ name: n, value: v[j], array: true, first: (j === 0) });
            }
            else if (v !== null && typeof v != 'undefined')
                a.push({ name: n, value: v });
        }
        return a;
    }

    return {
        formToArray: formToArray
    };
})();
// Base prototype for uploader and thumbnail controls
function baseControl() {
    // We inherit from this class, so we can not place this array into prototype
    this._simpleProperties = [{ name: "id", isAttribute: true}];
}

baseControl.prototype = {
    __class: true,

    state: function () {
        if (arguments.length > 0) {
            this._state = arguments[0];
        } else {
            return this._state;
        }
    },

    set: function (obj) {
        ///	<summary>
        ///		Set uploader properties
        ///	</summary>
        ///	<param name="obj" type="Object">
        ///		An object with uploader properties
        ///	</param>
        for (var name in obj) {
            if (typeof this[name] === 'function') {
                this[name](obj[name]);
            }
            else {
                showError('Uploader haven\'t ' + name + ' property.');
            }
        }
    },

    writeHtml: function () {
        wnd.document.write(this.getHtml());
    },

    getHtml: function () {
        var self = this, id = this.id(), events = this.events();

        /* create events function*/
        var createEventHandler = function (event, eventName) {
            return function () {
                var result1 = true;
                if (event._handlers && event._handlers.length > 0) {
                    for (var i = 0, imax = event._handlers.length; i < imax; i++) {
                        try {
                            var handler = event._handlers[i];
                            var result;
                            if (typeof handler === 'function') {
                                result = handler.apply(self, arguments);
                            } else if (typeof wnd[handler] === 'function') {
                                result = wnd[handler].apply(self, arguments);
                            } else {
                                showError(eventName + " error:\n \"" + handler + "\" handler is not defined.");
                            }
                            if (eventName === 'beforeUpload' || eventName === 'afterPackageUpload') {
                                // for beforeUpload and afterPackageUpload events 
                                // we returns false if any event handler returns false or 0 
                                if (!result1) {
                                    // already get false in prev handler
                                    result = result1;
                                } else if (result !== UNDEF) {
                                    // convert to boolean and save to temp var
                                    result1 = (result === false || result === 0 ? false : true);
                                }
                            }
                        }
                        catch (err) {
                            showError(eventName + " error:\n" + (err.message || err.description || err));
                            throw err;
                        }
                    }
                }
                // return result from last handler
                return result;
            };
        };

        // fire preRender event
        if (events.preRender().count() > 0) {
            (createEventHandler(events.preRender(), 'preRender'))();
        }

        if (AU.debug().level() >= 2 && events.trace) {
            // add trace events
            events.trace().add(function (msg) {
                AU.debug()._showUploaderMessage(msg);
            });
        }

        for (var i in events) {
            if (i != 'getParams' && i != 'preRender') {
                var event = events[i];
                if (typeof event === 'function' && event() instanceof AU.event && event().count() > 0) {
                    wnd[getGlobalHandlerName(this, i)] = createEventHandler(event(), i);
                }
            }
        }

        return flashRenderer(this).html();
    },

    getElement: function () {
        return wnd.document.getElementById(this.id());
    },

    getParams: function () {
        var params = getParams.call(this);

        //set debug options
        params.push({ name: 'debugMode', value: (AU.debug().level() >= 2) });
        params.push({ name: 'traceToJS', value: true });

        return params;
    },

    registerCallback: function (name, callback, context) {
        this._clbs = this._clbs || {};
        this._clbs[name] = this._clbs[name] || [];
        this._clbs[name].push({ 'func': callback, 'ctx': context });
    },

    _invokeCallback: function (name, args) {
        if (!this._clbs || !this._clbs[name]) {
            return;
        }
        var clbs = this._clbs[name]
        for (var i = 0, imax = clbs.length; i < imax; i++) {
            var clb = clbs[i];
            clb.func.apply(clb.ctx, args);
        }
    },

    id: function (value) {
        ///	<summary>
        ///		Get or set control id
        ///	</summary>
        ///	<param name="value" type="String">
        ///		id
        ///	</param>
        ///	<returns type="String" />
    }
};
function baseEvents(initObj) {
    // We inherit from this class, so we can not place this array into prototype
    this._eventNames = ["initComplete", "preRender"];
};

baseEvents.prototype = {
    __class: true,

    initComplete: function () {
        ///	<summary>
        ///		Init complete event.
        ///	</summary>
        ///	<returns type="$au.event" />
        /* will be created while initialization*/
    },
    preRender: function () {
        ///	<summary>
        ///		Prerender event.
        ///	</summary>
        ///	<returns type="$au.event" />
        /* will be created while initialization*/
    },

    getParams: function () {
        var params = [];
        for (var i = 0, imax = this._eventNames.length; i < imax; i++) {
            var eventName = this._eventNames[i], evt = this[eventName];
            if (typeof evt === 'function' && evt().count() > 0) {
                params.push({ name: eventName.toLowerCase() + "listener",
                    value: getGlobalHandlerName(this._uploader, eventName)
                });
            }
        }
        return params;
    }
};

baseEvents.init = function (obj, uploader) {
    obj._uploader = uploader;
    var events = obj._eventNames;
    var f = function () {
        var eventObj = new AU.event();
        return function () {
            if (arguments.length > 0) {
                eventObj.add(arguments[0]);
            }
            return eventObj;
        };
    }
    for (var i = 0, imax = events.length; i < imax; i++) {
        obj[events[i]] = f();
    }
};

baseEvents.prototype.constructor = baseEvents;
AU.commonDialog = function() {
    ///	<summary>
    ///		Common dialog window properties.
    ///	</summary>
    ///	<returns type="$au.commonDialog" />
};

AU.commonDialog.prototype = {
    __class: true,
    _simpleProperties: [
        { name: "cancelButtonText", getter: 1, setter: 1, render: "CommonDialogCancelButtonText" },
        { name: "okButtonText", getter: 1, setter: 1, render: "CommonDialogOkButtonText" }
    ],
    getParams: getParams,

    cancelButtonText: function(value) {
        ///	<summary>
        ///		Gets or sets the text of the Cancel button.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    okButtonText: function(value) {
        ///	<summary>
        ///		Gets or sets the text of the OK button.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
};
AU.commonDialog.init = propertyMaker._typicalInit;
AU.commonDialog.reinit = propertyMaker._typicalReinit;
AU.converter = function (json) {
    if (AU.converter.init) {
        AU.converter.init();
    }
    this.mode('*.*=SourceFile');
	if (json) {
        this.set(json);
    }
};

AU.converter.init = function () {
    // Init simple properties
    var obj = AU.converter.prototype;
    if (obj._simpleProperties) {
        for (var i = 0, imax = obj._simpleProperties.length; i < imax; i++) {
            var prop = obj._simpleProperties[i];
            propertyMaker.createSimpleProperty(obj, prop);
        }
    }
    delete AU.converter.init;
}

AU.converter.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "thumbnailBgColor", getter: 1, setter: 1, render: "ConverterThumbnailBgColor" },
        { name: "thumbnailCopyExif", getter: 1, setter: 1, render: "ConverterThumbnailCopyExif" },
        { name: "thumbnailCopyIptc", getter: 1, setter: 1, render: "ConverterThumbnailCopyIptc" },
        { name: "thumbnailFitMode", getter: 1, setter: 1, render: "ConverterThumbnailFitMode" },
        { name: "thumbnailHeight", getter: 1, setter: 1, render: "ConverterThumbnailHeight" },
        { name: "thumbnailJpegQuality", getter: 1, setter: 1, render: "ConverterThumbnailJpegQuality" },
        { name: "thumbnailWidth", getter: 1, setter: 1, render: "ConverterThumbnailWidth" }
    ],
    // for mode need additional validation
    mode: function() {
        ///	<summary>
        ///		Get or set converter mode string.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     "mask11,mask12,...=mode1,mode2,...;mask21,mask22,...=mode1,mode2,...;..."
        ///     Where mask is the file extension, mode is the one of the string "SourceFile", "Thumbnail", "Icon", "Zip".
        ///     For example, "*.*=SourceFile" - source file will be uploaded, 
        ///     "*.jpg,*.png,*.gif=Thumbnail;*.*=Icon" - thumbnail will be uploaded for jpg, png, gif images, 
        ///     and file icon for other files.
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
        if (arguments.length > 0) {
            if (arguments[0]) {
                this._validateMode(arguments[0]);
                this._mode = arguments[0];
            }
        } else {
            return this._mode;
        }
    },

    thumbnailBgColor: function (value) {
        ///	<summary>
        ///		Get or set thumbnail background color
        ///	</summary>
        ///	<param name="value" type="String">
        ///		Color
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    thumbnailCopyExif: function (value) {
        ///	<summary>
        ///		Get or set whether to copy EXIF data from original JPEG image to thumbnail
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    thumbnailCopyIptc: function (value) {
        ///	<summary>
        ///		Get or set whether to copy IPTC data from original JPEG image to thumbnail
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    thumbnailFitMode: function (value) {
        ///	<summary>
        ///		Get or set how the thumbnail will be resized.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the values "Fit", "Width", "Height", "ActualSize" or their number evuivalent.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    thumbnailHeight: function (value) {
        ///	<summary>
        ///		Get or set maximum thumbnail height.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    thumbnailJpegQuality: function (value) {
        ///	<summary>
        ///		Get or set JPEG quality for thumbnail.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     Integer number from 0 to 100.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    thumbnailWidth: function (value) {
        ///	<summary>
        ///		Get or set maximum thumbnail width.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     Width
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },

    set: function(json) {
        if (json) {
            if (typeof json.toJson == 'function')
                json = json.toJson();
            for (var p in json) {
                if (!json.hasOwnProperty || json.hasOwnProperty(p)) {
                    if (typeof this[p] === 'function' && json[p] != null) {
                        this[p](json[p]);
                    }
                }
            }
        }
    },
    toJson: function() {
        var obj = {}, value, prop;
        if (this.mode()) {
            obj.mode = this.mode();
        }
        if (this._simpleProperties) {
            for (var i = 0, imax = this._simpleProperties.length; i < imax; i++) {
                prop = this._simpleProperties[i];
                value = this[prop.name]();
                if (value != null) {
                    obj[prop.name] = value;
                }
            }
        }
        return obj;
    },
    _validateMode: function(modeString) {
        var rg = /^((.*=)?(SourceFile|Thumbnail|Icon|None|Zip);)*(.*=)?(SourceFile|Thumbnail|Icon|None|Zip);?$/;
        if (rg.test(modeString)) {
            return true;
        } else {
            showError("Converter mode string is not valid.\n" + modeString);
            return false;
        }
    }
};
AU.converter.prototype.mode.isUploaderProperty = true;
AU.converter.prototype.constructor = AU.converter;

//change prototype to work with rendered control
AU.converter.reinit = function (converter, element) {
    converter.mode = function () {
        if (arguments.length > 0) {
            if (arguments[0]) {
                this._validateMode(arguments[0]);
                element.callFlash("setConverterMode", this._index, arguments[0]);
            }
        } else {
            return element.callFlash("getConverterMode", this._index);
        }
    };
    converter.mode.isUploaderProperty = true;

    if (converter._simpleProperties) {
        for (var i = 0, imax = converter._simpleProperties.length; i < imax; i++) {
            var property = converter._simpleProperties[i];
            if (!property.isAttribute) {
                // Use closure to capture "getter" and "setter" values
                (function (property) {
                    var getter = property.getter, setter = property.setter;
                    if (getter === 1 || setter === 1) {
                        var p = property.render || property.name;
                        p = p.charAt(0).toUpperCase() + p.substring(1);
                        if (getter === 1) {
                            getter = "get" + p;
                        }
                        if (setter === 1) {
                            setter = "set" + p;
                        }
                    }
                    // replace property function to the new, which get/set value from/to uploader,
                    // instead of internal variable
                    converter[property.name] = function () {
                        if (arguments.length > 0) {
                            if (arguments[0] != null) {
                                return element.callFlash(setter, this._index, arguments[0]);
                            }
                        } else
                            return element.callFlash(getter, this._index);
                    }
                    // mark that function is uploader's property
                    converter[property.name].isUploaderProperty = true;
                })(property);
            }
        }
    }
};
AU.converters = function () {
    ///	<summary>
    ///		Get or set upload converters properties
    ///	</summary>
    ///	<returns type="$au.converters" />
}

AU.converters.prototype = {
    __class: true,

    add: function() {
        ///	<summary>
        ///		Add new converter to uploader.
        ///	</summary>
        ///	<returns type="$au.converter" />
        var th;
        if (arguments[0] instanceof AU.converter) {
            th = arguments[0];
        } else {
            th = new AU.converter(arguments[0]);
        }
        this._converters.push(th);
        return th;
    },
    count: function() {
        ///	<summary>
        ///		Get count of added converters.
        ///	</summary>
        ///	<returns type="Number" />
        return this._converters.length;
    },
    get: function(index) {
        ///	<summary>
        ///		Get added converter.
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Converter index
        ///	</param>
        ///	<returns type="$au.converter" />
        return this._converters[index];
    },
    remove: function(index) {
        ///	<summary>
        ///		Remove converter.
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Converter index
        ///	</param>
        this._converters.splice(index, 1);
    },
    removeAll: function() {
        ///	<summary>
        ///		Remove all converters.
        ///	</summary>
        this._converters.length = 0;
        while (this.count() > 0) {
            this.remove(0);
        }
    }
};

AU.converters.init = function (obj, uploader) {
    obj._uploader = uploader;
    obj._converters = [new AU.converter({ mode: "*.*=SourceFile" })];
}

// change prototype to work with rendered control
// for internal use
AU.converters.reinit = function (converters) {
    try {
        showInfo("Reinit converters.");

        var element = converters._uploader.getElement();

        //save converters added before
        var convertersArr = [], i, cnt;
        for (i = 0, cnt = converters.count(); i < cnt; i++) {
            var cv = converters.get(i);
            convertersArr.push(cv.toJson());
        }

        // remove converters from control
        while (element.callFlash("getConverterCount") > 0) {
            element.callFlash("removeConverter", 0);
        }

        //clear internal converters array
        converters._items = [];

        converters.add = function () {
            ///	<summary>
            ///		Add converter to uploader
            ///	</summary>
            ///	<returns type="$au.converter" added converter />
            var index = element.callFlash("addConverter", "*.*=SourceFile");
            if (index >= 0) {
                if (arguments[0]) {
                    var conv = this.get(index);
                    conv.set(arguments[0]);
                    return conv;
                }
            } else {
                showError("Converter hasn't been added.");
                return null;
            }
        };

        converters.count = function () {
            return element.callFlash("getConverterCount");
        };

        converters.get = function (index) {
            ///	<summary>
            ///		Get converter
            ///	</summary>
            ///	<param name="index" type="Number">
            ///     Converter index
            ///	</param>
            ///	<returns type="$au.converter" />
            if (index < this.count()) {
                if (this._items && this._items[index]) {
                    return this._items[index];
                } else {
                    this._items = this._items || [];
                    //create object to provide methods to work with thumbnail
                    var converter = new AU.converter();
                    converter._index = index;
                    AU.converter.reinit(converter, element);
                    this._items[index] = converter;
                    return converter;
                }
            } else {
                return null;
            }
        };

        converters.remove = function (index) {
            ///	<summary>
            ///		Remove thumbnail
            ///	</summary>
            ///	<param name="index" type="Number">
            ///     thumbnail index
            ///	</param>
            // remove from control
            element.callFlash("removeConverter", index);
            //remove from internal array
            this._items.splice(index, 1);
            // re-index items
            for (var i = 0, imax = this._items.length; i < imax; i++) {
                this._items[i]._index = i;
            }
        };

        // add saved converters to uploader control
        for (i = 0, cnt = convertersArr.length; i < cnt; i++) {
            converters.add(convertersArr[i]);
        }
    } catch (err) {
        showError('Converters initialization failed.\n' + (err.message || err.description || err));
        throw err;
    }
};
AU.descriptionEditor = function () {
    ///	<summary>
    ///		Get or set descriptionEditor properties
    ///	</summary>
    ///	<param name="descriptionEditor" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.descriptionEditor" />
}

AU.descriptionEditor.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cancelButtonText", getter: 1, setter: 1, render: "DescriptionEditorCancelButtonText" },
        { name: "saveButtonText", getter: 1, setter: 1, render: "DescriptionEditorSaveButtonText" }
    ],
    getParams: getParams,

    cancelButtonText: function (value) {
        ///	<summary>
        ///		Cancel button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    saveButtonText: function (value) {
        ///	<summary>
        ///		Save button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    }
    
}

AU.descriptionEditor.init = propertyMaker._typicalInit;
AU.descriptionEditor.reinit = propertyMaker._typicalReinit;
AU.events = function (initObj) {
    ///	<summary>
    ///		Get or set uploader events           
    ///	</summary>
    ///	<param name="initObj" type="Array">
    ///		java control parameters.
    ///	</param>
    ///	<returns type="$au.events" />
    /* will be created while initialization */
};

AU.events.prototype = new baseEvents();

extend(AU.events.prototype, {
    _eventNames: [UNDEF /*base event*/, UNDEF /*base event*/, "beforeUpload", "afterUpload", "beforePackageUpload", "afterPackageUpload",
        "progress", "itemsAdded", "error", "restrictionFailed", "trace"],

    beforeUpload: function () {
        ///	<summary>
        ///		BeforeUpload event            
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    afterUpload: function () {
        ///	<summary>
        ///		AfterUpload event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    beforePackageUpload: function () {
        ///	<summary>
        ///		BeforePackageUpload event            
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    afterPackageUpload: function () {
        ///	<summary>
        ///		AfterPackageUpload event            
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    progress: function () {
        ///	<summary>
        ///		AfterPackageUpload event            
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    itemsAdded: function () {
        ///	<summary>
        ///		AfterPackageUpload event            
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    error: function () {
        ///	<summary>
        ///		Error event            
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    restrictionFailed: function () {
        ///	<summary>
        ///		RestrictionFailed event            
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    trace: function () {
        ///	<summary>
        ///		Trace event. 
        ///     Trace event used to receive debug messages from uploader.
        ///	</summary>
        ///	<returns type="$au.event" />
    }
}, true);

AU.events.init = baseEvents.init;

AU.events.prototype.constructor = AU.events;
AU.file = function (json) {
    if (json) {
        this.set(json);
    }
    this.constructor = AU.file.prototype;
};

AU.file.prototype = {
    __class: true,

    angle: function (angle) {
        ///	<summary>
        ///		Get or set rotate angle.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Angle
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    description: function (description) {
        ///	<summary>
        ///		Get or set description for uploaded file.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		Description text
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    height: function() {
        ///	<summary>
        ///		Get height of the file in the upload pane. Returns 0 for non-image file.
        ///	</summary>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    name: function () {
        ///	<summary>
        ///		Get name of the file in the upload pane.
        ///	</summary>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    selected: function(selected) {
        ///	<summary>
        ///		Check if file seleced in the upload pane.
        ///	</summary>
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    size: function () {
        ///	<summary>
        ///		Get size of the file in the upload pane.
        ///	</summary>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    width: function () {
        ///	<summary>
        ///		Get width of the file in the upload pane. Returns 0 for non-image file.
        ///	</summary>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    remove: function () {
        ///	<summary>
        ///		Remove file from upload list.
        ///	</summary>
        /* will be created while initialization */
    }
};

//change object to work with rendered control
AU.file.reinit = function (element, obj) {

    obj.angle = function (angle) {
        if (angle || angle === 0) {
            element.callFlash("setUploadFileAngle", this._index, angle);
        } else {
            return element.callFlash("getUploadFileAngle", this._index);
        }
    };
    obj.description = function (description) {
        if (description !== UNDEF) {
            element.callFlash("setUploadFileDescription", this._index, description);
        } else {
            return element.callFlash("getUploadFileDescription", this._index);
        }
    };
    obj.height = function () {
        return element.callFlash("getUploadFileHeight", this._index);
    };
    obj.name = function () {
        return element.callFlash("getUploadFileName", this._index);
    };
    obj.selected = function (selected) {
        if (selected !== UNDEF) {
            element.callFlash("setUploadFileSelected", this._index, selected);
        } else {
            return element.callFlash("getUploadFileSelected", this._index);
        }

    };
    obj.size = function () {
        return element.callFlash("getUploadFileSize", this._index);
    };
    obj.width = function () {
        return element.callFlash("getUploadFileWidth", this._index);
    };
    obj.remove = function () {
        return element.callFlash("removeAt", this._index);
    };
};
AU.files = function () {
    ///	<summary>
    ///		Get collection of files to upload.
    ///	</summary>
    ///	<returns type="$au.files" />
};

AU.files.prototype = {
    __class: true,

    count: function() {
        ///	<summary>
        ///		Get count of files selected to upload.
        ///	</summary>
        ///	<returns type="Number" />

        // can't select files before uploader initialization
        return 0;
    },
    get: function(index) {
        ///	<summary>
        ///		Get file to upload
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Index of files in the list view
        ///	</param>
        ///	<returns type="$au.file" />

        // can't select files before uploader initialization
        return null;
    },
    remove: function (index) {
        ///	<summary>
        ///		Remove file from upload list
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Index of files in the upload list
        ///	</param>
    }
};

AU.files.init = propertyMaker._typicalInit;

// change object to work with rendered control
// for internal use
AU.files.reinit = function (obj) {
    showInfo("Reinit files API.");

    var element = obj._uploader.getElement();

    obj.count = function() {
        return element.callFlash("getUploadFileCount");
    };

    obj.removeAll = function() {
        return element.callFlash("removeAll");
    };

    obj.get = function(index) {
        if (index < this.count()) {
            if (this._items && this._items[index]) {
                return this._items[index];
            } else {
                this._items = this._items || [];
                //create object to provide methods to work with file
                var f = new AU.file();
                AU.file.reinit(element, f);
                f._index = index;
                this._items[index] = f;
                return f;
            }
        } else {
            return null;
        }
    };

    obj.remove = function (index) {
        return element.callFlash("removeAt", index);
    };
};
AU.flashControl = function() {
    ///	<summary>
    ///		Get or set flash control parameters            
    ///	</summary>
    ///	<param name="initObj" type="Object">
    ///		flash control parameters.
    ///	</param>
    ///	<returns type="$au.flashControl" />
    /* will be created while initialization */
};
AU.flashControl.prototype = {
    _simpleProperties: [
        { name: "codeBase", defaultValue: "Scripts/aurigma.imageuploaderflash.swf", isAttribute: true },
        { name: "themeUrl", getter: 1, setter: 1, renderOnly: true },
        { name: "bgColor", isAttribute: true },
        { name: "wmode", isAttribute: true },
        { name: "quality", isAttribute: true },
        { name: "maxFileToLoadSize", getter: 1, setter: 1 },
        { name: "version", isAttribute: true }
    ],
    flashVersionRequired: [10, 1, 0],
    codeBase: function(codeBase) {
        ///	<summary>
        ///		Get or set URL to flash file
        ///	</summary>
        ///	<param name="codeBase" type="String">
        ///     URL to file
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    maxFileToLoadSize: function() {
        ///	<summary>
        ///		Configure max file size can be loaded into memory.
        ///     All other converters except sourceFile or None require to load file into memory.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Ma file size able to be loaded into memory.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    themeUrl: function() {
        ///	<summary>
        ///		Gets or sets url to flash uploader theme.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		URL to theme
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    bgColor: function() {
        ///	<summary>
        ///     Specifies the background color of the movie. Use this attribute to
        ///     override the background color setting specified in the Flash file.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		[ hexadecimal RGB value] in the format #RRGGBB.
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    wmode: function (value) {
        ///	<summary>
        ///     Possible values: window, opaque, transparent. 
        ///     Sets the Window Mode property of the Flash movie for transparency, layering, 
        ///     and positioning in the browser.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		window|opaque|transparent
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    quality: function (value) {
        ///	<summary>
        ///     Possible values: low, high, autolow, medium, autohigh, best.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		low|autolow|autohigh|medium|high|best
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    isFlashSupported: function() {
        // Can we use Flash?
        var v = AU.flashDetector.detectVersion();
        var reqVer = this.flashVersionRequired;
        for (var i = 0, imax = reqVer.length; i < imax; i++) {
            if (v[i] < reqVer[i]) {
                return false;
            }
        }
        return true;
    },
    actualVersion: function () {
        var el = this._uploader.getElement();
        if (el) {
            return el.callFlash("getActualVersion");
        } else {
            return null;
        }
    },
    getParams: getParams
};

AU.flashControl.init = propertyMaker._typicalInit;
AU.flashControl.reinit = propertyMaker._typicalReinit;
AU.imagePreviewWindow = function() {
    ///	<summary>
    ///		Get image preview window.
    ///	</summary>
    ///	<returns type="$au.imagePreviewWindow" />
};

AU.imagePreviewWindow.prototype = {
    _simpleProperties: [
        { name: "closePreviewTooltip", getter: 1, setter: 1 }
    ],
    closePreviewTooltip: function(text) {
        ///	<summary>
        ///		Gets or sets the text displayed when the mouse pointer hovers 
        ///     over the image caption on preview window.
        ///	</summary>
        ///	<param name="text" type="String">
        ///     New tooltip text of image caption on preview window.
        ///	</param>
    }
};

AU.imagePreviewWindow.init = propertyMaker._typicalInit;
AU.imagePreviewWindow.reinit = propertyMaker._typicalReinit;
AU.informationBar = function () {
    ///	<summary>
    ///		Get or set information bar properties
    ///	</summary>
    ///	<param name="informationBar" type="Object">
    ///		An object with information bar parameters
    ///	</param>
    ///	<returns type="$au.informationBar" />
}

AU.informationBar.prototype = {
    __class: true,

    _simpleProperties: [
    ],
    _methods: [
        { name: "show", controlMethodName: "showInformationBar" }
    ],
    getParams: getParams,

    show: function (text) {
        ///	<summary>
        ///		Show message on the information bar.
        ///	</summary>
        ///	<param name="text" type="String">
        ///     Message text
        /// </param>
    }
}

AU.informationBar.init = propertyMaker._typicalInit;
AU.informationBar.reinit = propertyMaker._typicalReinit;

AU.informationBar.prototype.constructor = AU.informationBar;
AU.uploadPane = function () {
    ///	<summary>
    ///		Get upload pane
    ///	</summary>
    ///	<returns type="$au.uploadPane" />
};

AU.uploadPane.prototype = {
    _simpleProperties: [
        { name: "addFilesButtonText", getter: 1, setter: 1 },
        { name: "iconItemWidth", getter: 1, setter: 1 },
        { name: "iconSize", getter: 1, setter: 1 },
        { name: "tileItemWidth", getter: 1, setter: 1 },
        { name: "tilePreviewSize", getter: 1, setter: 1 },
        { name: "thumbnailPreviewSize", getter: 1, setter: 1 },
        { name: "viewMode", getter: 1, setter: 1 }
    ],

    addFilesButtonText: function (value) {
        ///	<summary>
        ///		Get or set 'Add Files Button' button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    iconItemWidth: function (value) {
        ///	<summary>
        ///		Gets or sets the icon item width.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     New icon item width.
        ///	</param>
    },
    iconSize: function (value) {
        ///	<summary>
        ///		Gets or sets the file icons size.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     New file icons size.
        ///	</param>
    },
    tileItemWidth: function (value) {
        ///	<summary>
        ///		Gets or sets the tile item width.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     New tile item width.
        ///	</param>
    },
    tilePreviewSize: function (value) {
        ///	<summary>
        ///		Gets or sets the tiles size.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     New tiles size.
        ///	</param>
    },
    thumbnailPreviewSize: function (value) {
        ///	<summary>
        ///		Gets or sets the size of thumbnails in list view.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     New size of thumbnails in list view.
        ///	</param>
    },
    viewMode: function (value) {
        ///	<summary>
        ///		Gets or sets the list view mode.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the values "Thumbnails", "Tiles", "Icons" or their equivalent number.
        ///	</param>
    }
};

AU.uploadPane.init = propertyMaker._typicalInit;
AU.uploadPane.reinit = propertyMaker._typicalReinit;
AU.messages = function () {
    ///	<summary>
    ///		Get status panel
    ///	</summary>
    ///	<returns type="$au.messages" />
}

AU.messages.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cannotReadFile", getter: 1, setter: 1 },
        { name: "dimensionsTooLarge", getter: 1, setter: 1 },
        { name: "dimensionsTooSmall", getter: 1, setter: 1 },
        { name: "fileSizeTooSmall", getter: 1, setter: 1 },
        { name: "filesNotAdded", getter: 1, setter: 1 },
        { name: "maxFileCountExceeded", getter: 1, setter: 1 },
        { name: "maxFileSizeExceeded", getter: 1, setter: 1 },
        { name: "maxTotalFileSizeExceeded", getter: 1, setter: 1 },
        { name: "memoryLimitReached", getter: 1, setter: 1 },
        { name: "previewNotAvailable", getter: 1, setter: 1 },
        { name: "tooFewFiles", getter: 1, setter: 1 },
        { name: "tooManyFilesSelectedToOpen", getter: 1, setter: 1 }
    ],

    cannotReadFile: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when the read file error occurs.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    dimensionsTooLarge: function (value) {
        ///	<summary>
        ///		Gets or sets a message which states that the width or height of the selected image is too large.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    dimensionsTooSmall: function (value) {
        ///	<summary>
        ///		Gets or sets a message which states that the width or height of selected image is too small.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    fileSizeTooSmall: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to add 
        ///     file smaller minFileSize restriction.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    filesNotAdded: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to add files
        ///     and some of them were not added because of restrictions.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    maxFileCountExceeded: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to upload 
        ///     number of files more than the maxFileCount restriction.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    maxFileSizeExceeded: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to add 
        ///     file larger maxFileSize restriction.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    maxTotalFileSizeExceeded: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to upload files 
        ///     with total size bigger than the maxTotalSize restrictions.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    memoryLimitReached: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to 
        ///     add too many images to upload pane.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    previewNotAvailable: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when image preview is not available.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    tooFewFiles: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to upload 
        ///     number of files less than the minFileCount restriction.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    tooManyFilesSelectedToOpen: function (value) {
        ///	<summary>
        ///		Gets or sets the message displayed when user tries to 
        ///     add too many files to upload pane.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
};

AU.messages.init = propertyMaker._typicalInit;
AU.messages.reinit = propertyMaker._typicalReinit;
AU.metadata = function () {
    ///	<summary>
    ///		Get or set additional upload data
    ///	</summary>
    ///	<returns type="$au.metadata" />
}

AU.metadata.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "exif", getter: 1, setter: 1, render: "ExtractExif" },
        { name: "iptc", getter: 1, setter: 1, render: "ExtractIptc" },
        { name: "valueSeparator", getter: 1, setter: 1, render: "MetadataValueSeparator" }
    ],

    addCustomField: function (name, value, add) {
        ///	<summary>
        ///		Add additional field to upload.
        ///	</summary>
        ///	<param name="name" type="String">
        ///		Field name
        ///	</param>
        ///	<param name="value" type="String">
        ///		Field value
        ///	</param>
        ///	<param name="add" type="Boolean">
        ///		If true, then preserve previous value of the field. Several values will be sent to server.
        ///     If false, then previous value of the field will be overwritten with this value. (Default)
        ///	</param>
        this._customFields = this._customFields || [];
        if (name && value != null) {
            this._customFields.push({ action: "add", name: name, value: value, add: add });
        }
    },
    removeCustomField: function (name) {
        ///	<summary>
        ///		Remove additional field. The field wouldn't be sent to server.
        ///	</summary>
        ///	<param name="name" type="String">
        ///		Field name
        ///	</param>
        this._customFields = this._customFields || [];
        if (name) {
            this._customFields.push({ action: "remove", name: name });
        }
    },
    additionalFormName: function (name) {
        ///	<summary>
        ///		Name of the HTML form with additional information which should be sent to the server along with files.
        ///	</summary>
        ///	<param name="name" type="String">
        ///     Form name
        ///	</param>
        if (arguments.length == 0) {
            return this._formName;
        } else {
            if (this._beforeUploadHandler) {
                this._uploader.events().beforeUpload().remove(this._beforeUploadHandler);
                delete this._beforeUploadHandler;
                delete this._formName;
            }
            if (name) {
                this._formName = name;
                this._beforeUploadHandler = function () {
                    var metadata = this.metadata();
                    var form = document.forms[metadata._formName];
                    if (form) {
                        var fields = formHelper.formToArray(form), cnt = fields.length;
                        if (cnt > 0) {
                            for (var i = 0; i < cnt; i++) {
                                var f = fields[i];
                                if (!f.array || f.first) {
                                    metadata.addCustomField(f.name, f.value);
                                } else {
                                    metadata.addCustomField(f.name, f.value, true);
                                }
                            }
                        }
                    }
                };
                this._uploader.events().beforeUpload().add(this._beforeUploadHandler);
            }
        }
    },
    getParams: getParams,

    exif: function (value) {
        ///	<summary>
        ///		Get or set EXIF values to extract and upload along with files.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		EXIF field names separated with a semicolon.
        ///	</param>
        ///	<returns type="String" />
    },
    iptc: function (value) {
        ///	<summary>
        ///		Get or set IPTC values to extract and upload along with files.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		IPTC field names separated with a semicolon.
        ///	</param>
        ///	<returns type="String" />
    },
    valueSeparator: function (value) {
        ///	<summary>
        ///		Get or set value separator for multi-value EXIF and IPTC fields
        ///	</summary>
        ///	<param name="value" type="String">
        ///		Separator character
        ///	</param>
        ///	<returns type="String" />
    }
};

AU.metadata.init = propertyMaker._typicalInit;

AU.metadata.reinit = function (obj) {
    showInfo("Reinit metadata.");

    var element = obj._uploader.getElement();

    obj.addCustomField = function (name, value, add) {
        element.callFlash("addCustomField", name, value, !!add);
    };

    obj.removeCustomField = function (name) {
        if (name != null) {
            element.callFlash("removeCustomField", name);
        }
    };

    // Add or remove custom fields
    if (obj._customFields && obj._customFields.length > 0) {
        for (var i = 0, max = obj._customFields.length; i < max; i++) {
            var f = obj._customFields[i];
            switch (f.action) {
                case "add":
                    obj.addCustomField(f.name, f.value, f.add);
                    break;
                case "remove":
                    obj.removeCustomField(f.name);
                    break;
            }
        }
    }

    propertyMaker._typicalReinit(obj);
}
AU.restrictions = function () {
    ///	<summary>
    ///		Get or set upload restrictions properties
    ///	</summary>
    ///	<param name="restrictions" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.restrictions" />
}

AU.restrictions.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "fileMask", getter: 1, setter: 1 },
        { name: "maxFileCount", getter: 1, setter: 1 },
        { name: "maxFileSize", getter: 1, setter: 1 },
        { name: "maxImageHeight", getter: 1, setter: 1 },
        { name: "maxImageWidth", getter: 1, setter: 1 },
        { name: "maxTotalFileSize", getter: 1, setter: 1 },
        { name: "minFileCount", getter: 1, setter: 1 },
        { name: "minFileSize", getter: 1, setter: 1 },
        { name: "minImageHeight", getter: 1, setter: 1 },
        { name: "minImageWidth", getter: 1, setter: 1 },
        { name: "minImageWidthHeightLogic", getter: 1, setter: 1 }
    ],
    getParams: getParams,

    fileMask: function () {
        ///	<summary>
        ///		Get or set files filter in open files dialog.    
        ///	</summary>
        ///	<param name="value" type="Array">
        ///		File filters.
        ///	</param>
        ///	<returns type="Array" />
        /* will be created while initialization */
    },
    maxFileCount: function (value) {
        ///	<summary>
        ///		Maximum number of files allowed for upload per one session           
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Number of files allowed for upload.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    maxFileSize: function () {
        ///	<summary>
        ///		Maximum file size allowed for upload.           
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Maximum file size.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    maxImageHeight: function () {
        ///	<summary>
        ///		Maximum image height allowed for upload.    
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Maximum image height.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    maxImageWidth: function () {
        ///	<summary>
        ///		Maximum image width allowed for upload.          
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Maximum image width.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    maxTotalFileSize: function () {
        ///	<summary>
        ///		Maximum total file size allowed for upload.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Total file size.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    minFileCount: function (value) {
        ///	<summary>
        ///		Minimum number of files allowed for upload per one session           
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Number of files allowed for upload.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    minFileSize: function () {
        ///	<summary>
        ///		Minimum file size allowed for upload.           
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Minimum file size.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    minImageHeight: function () {
        ///	<summary>
        ///		Minimum image height allowed for upload.       
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Minimum image height.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    minImageWidth: function () {
        ///	<summary>
        ///		Minimum image width allowed for upload.            
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		Minimum image width. 
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    minImageWidthHeightLogic: function () {
        ///	<summary>
        ///     If "or" and image width and image height restrictions 
        ///     set then add image even if only one restriction passed.
        ///     By default "and" - image width and image height should pass restrictions.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		and|or
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    }
};

AU.restrictions.init = propertyMaker._typicalInit;
AU.restrictions.reinit = propertyMaker._typicalReinit;
AU.statusPane = function () {
    ///	<summary>
    ///		Get status panel
    ///	</summary>
    ///	<returns type="$au.statusPane" />
}

AU.statusPane.prototype = {
    _simpleProperties: [
        { name: "dataUploadedText", getter: 1, setter: 1, render: "StatusPaneDataUploadedText" },
        { name: "filesPreparedText", getter: 1, setter: 1, render: "StatusPaneFilesPreparedText" },
        { name: "filesToUploadText", getter: 1, setter: 1 },
        { name: "filesUploadedText", getter: 1, setter: 1, render: "StatusPaneFilesUploadedText" },
        { name: "noFilesToUploadText", getter: 1, setter: 1 },
        { name: "preparingText", getter: 1, setter: 1, render: "StatusPanePreparingText" },
        { name: "sendingText", getter: 1, setter: 1, render: "StatusPaneSendingText" }
    ],
    dataUploadedText: function (value) {
        ///	<summary>
        ///		Get or set bytes uploaded label text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    filesPreparedText: function (value) {
        ///	<summary>
        ///		Get or set prepared label text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    filesToUploadText: function (value) {
        ///	<summary>
        ///		Gets or sets a text on the status pane which is displayed when 
        ///     at least one file is added to the upload pane.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    filesUploadedText: function (value) {
        ///	<summary>
        ///		Get or set files uploaded label text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    noFilesToUploadText: function (value) {
        /// <summary>
        ///     Gets or sets a text on the status pane which is displayed when the upload pane is empty.
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    preparingText: function (value) {
        ///	<summary>
        ///		Get or set preparing header text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    sendingText: function (value) {
        ///	<summary>
        ///		Get or set sending header text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
};

AU.statusPane.init = propertyMaker._typicalInit;
AU.statusPane.reinit = propertyMaker._typicalReinit;
AU.topPane = function () {
    ///	<summary>
    ///		Get top panel
    ///	</summary>
    ///	<returns type="$au.topPane" />
}

AU.topPane.prototype = {
    _simpleProperties: [
        { name: "addFilesHyperlinkText", getter: 1, setter: 1 },
        { name: "clearAllHyperlinkText", getter: 1, setter: 1 },
        { name: "orText", getter: 1, setter: 1 },
        { name: "showViewComboBox", getter: 1, setter: 1 },
        { name: "titleText", getter: 1, setter: 1 },
        { name: "viewComboBox", getter: 1, setter: 1 },
        { name: "viewComboBoxText", getter: 1, setter: 1 }
    ],
    addFilesHyperlinkText: function (value) {
        ///	<summary>
        ///		Get or set 'Add Files' hyperlink text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    clearAllHyperlinkText: function (value) {
        ///	<summary>
        ///		Get or set 'Remove All Files' hyperlink text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    orText: function (value) {
        ///	<summary>
        ///		Get or set 'or' label on top panel.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    showViewComboBox: function (value) {
        ///	<summary>
        ///		Show or hide view combobox.
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    titleText: function (value) {
        ///	<summary>
        ///		Get or set 'Files for upload' label text on top panel.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    viewComboBox: function (value) {
        ///	<summary>
        ///		Get or set Change view combobox values.
        ///	</summary>
        ///	<param name="value" type="Array" />
        ///	<returns type="Array" />
    },
    viewComboBoxText: function (value) {
        ///	<summary>
        ///		Get or set 'Change view' label text on top panel.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
};

AU.topPane.init = propertyMaker._typicalInit;
AU.topPane.reinit = propertyMaker._typicalReinit;
AU.uploadSettings = function () {
    ///	<summary>
    ///		Upload options.
    ///	</summary>
    ///	<returns type="$au.uploadSettings" />
}

AU.uploadSettings.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "_actionInternal", getter: 1, setter: 1, render: "ActionUrl", defaultValue: document.location.href },
        { name: "chunkSize", getter: 1, setter: 1 },
        { name: "progressBytesMode", getter: 1, setter: 1 }
    ],
    actionUrl: function(url) {
        ///	<summary>
        ///		Get or set URL to upload
        ///	</summary>
        ///	<param name="url" type="String">
        ///     URL to to upload
        ///	</param>
        ///	<returns type="String" />
        if (url) {
            if (url == "." || url == "./") {
                // if default value (".") then use current document url
                url = document.location.href;
            }
            return this._actionInternal(url);
        } else {
            return this._actionInternal();
        }
    },
    chunkSize: function (size) {
        ///	<summary>
        ///		Get or set size of the chunk.
        ///	</summary>
        ///	<param name="size" type="Number">
        ///     Max file size per one chunk. Set to zero to disable upload by chunks.
        ///	</param>
        ///	<returns type="Number" />
    },
    progressBytesMode: function (value) {
        ///	<summary>
        ///		Specifies how to calculate the total size of uploaded data.
        ///     "ByPackageSize" - Total bytes equals the size of the currently uploaded package.
        ///     "BySourceSize" -  Total bytes equals the size of the all selected for upload files. 
        ///         Only original file counting, the size of thumbnails is not included.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the strings "ByPackageSize" (0), "BySourceSize" (1) or their number equivalent.
        ///	</param>
        ///	<returns type="String" />
    },
    redirectUrl: function (url) {
        ///	<summary>
        ///		Get or set URL to redirect after upload
        ///	</summary>
        ///	<param name="url" type="String">
        ///     URL to redirect after upload
        ///	</param>
        ///	<returns type="String" />
        if (arguments.length > 0) {
            this._redirectUrl = arguments[0];
            var uploader = this._uploader;
            if (this._afterUpload) {
                uploader.events().afterUpload().remove(this._afterUpload);
                delete this._afterUpload;
            }
            if (this._redirectUrl) {
                var url = this._redirectUrl;
                this._afterUpload = function() {
                    setTimeout(function () { wnd.location = url; }, 100);
                }
                uploader.events().afterUpload().add(this._afterUpload);

            }
        } else {
            return this._redirectUrl;
        }
    },
    getParams: getParams
};

AU.uploadSettings.init = propertyMaker._typicalInit;
AU.uploadSettings.reinit = propertyMaker._typicalReinit;
AU.paneItem = function () {
    ///	<summary>
    ///		Get or set pane item properties
    ///	</summary>
    ///	<param name="paneItem" type="Object">
    ///		An object with pane item parameters
    ///	</param>
    ///	<returns type="$au.paneItem" />
}

AU.paneItem.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "descriptionEditorIconTooltip", getter: 1, setter: 1 },
        { name: "enableDisproportionalExifThumbnails", getter: 1, setter: 1 },
        { name: "imageTooltip", getter: 1, setter: 1 },
        { name: "itemTooltip", getter: 1, setter: 1 },
        { name: "removalIconTooltip", getter: 1, setter: 1 },
        { name: "rotationIconTooltip", getter: 1, setter: 1 },
        { name: "toolbarAlwaysVisible", getter: 1, setter: 1, render: 'paneItemToolbarAlwaysVisible' }
    ],
    getParams: getParams,

    descriptionEditorIconTooltip: function (value) {
        /// <summary>
        ///     Description editor icon tooltip
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    enableDisproportionalExifThumbnails: function (value) {
        /// <summary>
        ///     Use disproportional EXIF thumbnails for image preview
        /// </summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    imageTooltip: function (text) {
        ///	<summary>
        ///		Gets or sets the text displayed when the mouse pointer hovers over the image thumbnail.
        ///	</summary>
        ///	<param name="text" type="String">
        ///     New tooltip text of image thumbnail.
        ///	</param>
    },
    itemTooltip: function (text) {
        ///	<summary>
        ///		Gets or sets the text displayed when the mouse pointer hovers over the non-image item.
        ///	</summary>
        ///	<param name="text" type="String">
        ///     New tooltip text of non-image item.
        ///	</param>
    },
    removalIconTooltip: function (value) {
        /// <summary>
        ///     Removal icon tooltip
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    rotationIconTooltip: function (value) {
        /// <summary>
        ///     Rotation icon tooltip
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    toolbarAlwaysVisible: function (value) {
        /// <summary>
        ///     Switch specifies whether icons toolbar visible for all items or only for the item under cursor.
        /// </summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    }
}

AU.paneItem.init = propertyMaker._typicalInit;
AU.paneItem.reinit = propertyMaker._typicalReinit;
AU.uploadErrorDialog = function () {
    ///	<summary>
    ///		Upload error dialog window.
    ///	</summary>
    ///	<returns type="$au.uploadErrorDialog" />
};
AU.uploadErrorDialog.prototype = {
    _simpleProperties: [
        { name: "hideDetailsButtonText", getter: 1, setter: 1, render: "UploadErrorDialogHideDetailsButtonText" },
        { name: "message", getter: 1, setter: 1, render: "UploadErrorDialogMessage" },
        { name: "showDetailsButtonText", getter: 1, setter: 1, render: "UploadErrorDialogShowDetailsButtonText" },
        { name: "title", getter: 1, setter: 1, render: "UploadErrorDialogTitle" }
    ],
    hideDetailsButtonText: function (value) {
        ///	<summary>
        ///		Get or set "Hide details" button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    message: function (value) {
        ///	<summary>
        ///		Get or set upload error message
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    showDetailsButtonText: function (value) {
        ///	<summary>
        ///		Get or set "Show details" button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    title: function (value) {
        ///	<summary>
        ///		Get or set title of the error dialog window
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    getParams: getParams
};

AU.uploadErrorDialog.init = propertyMaker._typicalInit;
AU.uploadErrorDialog.reinit = propertyMaker._typicalReinit;
AU.addFilesProgressDialog = function () {
    ///	<summary>
    ///		Get add files progress dialog.
    ///	</summary>
    ///	<returns type="$au.addFilesProgressDialog" />
};

AU.addFilesProgressDialog.prototype = {
    _simpleProperties: [
        { name: "text", getter: 1, setter: 1, render: "AddFilesProgressDialogText" }
    ],
    text: function(value) {
        ///	<summary>
        ///		Gets or sets the text displayed on the add files progress dialog.
        ///	</summary>
        ///	<param name="value" type="String" />
    }
};

AU.addFilesProgressDialog.init = propertyMaker._typicalInit;
AU.addFilesProgressDialog.reinit = propertyMaker._typicalReinit;
AU.imageUploaderFlash = function (obj) {
    ///	<summary>
    ///		1: imageUploaderFlash(id) - Get created uploader.
    ///		2: imageUploaderFlash(obj) - Create new uploader.            
    ///	</summary>
    ///	<param name="obj" type="Object">
    ///		1: obj - Init object for new uploader.
    ///		2: id - An id of existing uploader.
    ///	</param>
    ///	<returns type="$au.imageUploaderFlash" />
    return new AU.imageUploaderFlash.fn.init(obj);
};

AU.imageUploaderFlash.fn = AU.imageUploaderFlash.prototype = new baseControl();

extend(AU.imageUploaderFlash.fn, {
    _simpleProperties: [
        UNDEF,
        { name: "cancelUploadButtonText", getter: 1, setter: 1 },
        { name: "enableAutoRotation", getter: 1, setter: 1 },
        { name: "enableDescriptionEditor", getter: 1, setter: 1 },
        { name: "enableRotation", getter: 1, setter: 1 },
        { name: "height", isAttribute: true, defaultValue: "400px" },
        { name: "licenseKey", getter: 1, setter: 1, renderOnly: true },
        { name: "locale", getter: 1, setter: 1 },
        { name: "uploadButtonText", getter: 1, setter: 1 },
        { name: "width", isAttribute: true, defaultValue: "600px" }
    ],
    _objectProperties: [
        { name: "flashControl", type: AU.flashControl },
        { name: "uploadSettings", type: AU.uploadSettings },
        { name: "events", type: AU.events },
        { name: "files", type: AU.files },
        { name: "restrictions", type: AU.restrictions },
        { name: "metadata", type: AU.metadata },
        { name: "topPane", type: AU.topPane },
        { name: "statusPane", type: AU.statusPane },
        { name: "uploadPane", type: AU.uploadPane },
        { name: "messages", type: AU.messages },
        { name: "imagePreviewWindow", type: AU.imagePreviewWindow },
        { name: "uploadErrorDialog", type: AU.uploadErrorDialog },
        { name: "commonDialog", type: AU.commonDialog },
        { name: "converters", type: AU.converters },
        { name: "paneItem", type: AU.paneItem },
        { name: "descriptionEditor", type: AU.descriptionEditor },
        { name: "addFilesProgressDialog", type: AU.addFilesProgressDialog },
        { name: "informationBar", type: AU.informationBar }
    ],
    _methods: [
        { name: "upload", controlMethodName: "upload" },
        { name: "cancelUpload", controlMethodName: "cancelUpload" }
    ],
    init: function (initObj) {
        ///	<summary>
        ///		For internal use.
        ///	</summary>
        ///	<returns type="$au.imageUploaderFlash" />

        if (typeof initObj === 'string') {
            return objectCache.get(initObj);
        }

        this._uploader = this;

        // Init simple properties
        for (var i = 0, imax = this._simpleProperties.length; i < imax; i++) {
            propertyMaker.createSimpleProperty(this, this._simpleProperties[i]);
        }

        // Init object properties
        for (var i = 0, imax = this._objectProperties.length; i < imax; i++) {
            var property = this._objectProperties[i];
            if (property.type === AU.converters) {
                // special create logic for events and converters
                // Create new object for this property
                var convertersObj = new AU.converters();
                // init default value
                AU.converters.init(convertersObj, this);

                this[property.name] = function () {
                    var newConverters = arguments[0];
                    if (newConverters instanceof Array) {
                        // remove old vales from array
                        convertersObj._converters.length = 0;
                        // and remove from actual control
                        while (convertersObj.count() > 0) {
                            convertersObj.remove(0);
                        }
                        //add new converters
                        for (var i = 0, imax = newConverters.length; i < imax; i++) {
                            convertersObj.add(newConverters[i]);
                        }
                    } else {
                        return convertersObj;
                    }
                }
            } else {
                propertyMaker.createObjectProperty(this, property, this);
            }
        }

        //add InitComplete event to know that control rendered
        this.events().initComplete(function () {
            this.reinit();
        });

        showInfo('Start apply uploader init object.');
        this.set(initObj);
        showInfo('Finish apply uploader init object.');

        // put object into cache
        objectCache.put(this);
        return this;
    },
    reinit: function () {
        showInfo("Start control re-initialization.");
        var element = document.getElementById(this.id()), i, cnt;

        // fix scroll wheel
        var f = function (e) {
            if (typeof e.preventDefault === 'function') {
                e.preventDefault();
            }
            if (typeof e.stopPropagation === 'function') {
                e.stopPropagation();
            }
            e.returnValue = false;
            return false;
        };
        if (typeof element.attachEvent !== 'undefined') {
            element.attachEvent("onmousewheel", f);
        } else if (typeof element.addEventListener !== 'undefined') {
            element.addEventListener('DOMMouseScroll', f, false);
            element.addEventListener('mousewheel', f, false);
        }

        showInfo("Reinit control properties.");

        // Map uploader properties to the control
        if (this._simpleProperties) {
            for (var i = 0, imax = this._simpleProperties.length; i < imax; i++) {
                var property = this._simpleProperties[i];
                if (!property.isAttribute) {
                    propertyMaker.createControlProperty(this, property);
                } else {
                    propertyMaker.createAttributeProperty(this, property);
                }
            }
        }
        if (this._objectProperties) {
            for (var i = 0, imax = this._objectProperties.length; i < imax; i++) {
                var property = this._objectProperties[i];
                if (property.type && typeof property.type.reinit === 'function') {
                    property.type.reinit(this[property.name]());
                }
            }
        }

        //create methods
        showInfo("Creating methods.");
        if (this._methods && this._methods.length > 0) {
            for (var i = 0, imax = this._methods.length; i < imax; i++) {
                propertyMaker.createMethod(this, this._methods[i]);
            }
        }

        showInfo("Control re-initialization completed.");
        this.state(1);
    },
    cancelUploadButtonText: function (value) {
        ///	<summary>
        ///		Get or set Cancel Upload button text
        ///	</summary>
        ///	<param name="value" type="String">
        ///     Cancel Upload button text
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    enableAutoRotation: function (value) {
        ///	<summary>
        ///		Enable or disable automatic EXIF-based rotation
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    enableDescriptionEditor: function (value) {
        ///	<summary>
        ///		Enable description editor
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableRotation: function (value) {
        ///	<summary>
        ///		Enable rotation
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    height: function (height) {
        ///	<summary>
        ///		Get or set control height            
        ///	</summary>
        ///	<param name="height" type="Number">
        ///		control height
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    licenseKey: function (licenseKey) {
        ///	<summary>
        ///		Get or set license key
        ///	</summary>
        ///	<param name="licenseKey" type="String">
        ///		License key
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    locale: function (value) {
        ///	<summary>
        ///		Get or set locale
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    uploadButtonText: function (value) {
        ///	<summary>
        ///		Get or set Upload button text
        ///	</summary>
        ///	<param name="value" type="String">
        ///     Upload button text
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    width: function (width) {
        ///	<summary>
        ///		Get or set control width            
        ///	</summary>
        ///	<param name="width" type="Number">
        ///		control width
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    
    imagePreviewWindow: AU.imagePreviewWindow,
    uploadPane: AU.uploadPane,
    messages: AU.messages,
    statusPane: AU.statusPane,
    topPane: AU.topPane,
    flashControl: AU.flashControl,
    uploadSettings: AU.uploadSettings,
    events: AU.events,
    converters: AU.converters,
    restrictions: AU.restrictions,
    metadata: AU.metadata,
    files: AU.files,
    uploadErrorDialog: AU.uploadErrorDialog,
    commonDialog: AU.commonDialog,
    paneItem: AU.paneItem,
    descriptionEditor: AU.descriptionEditor,
    addFilesProgressDialog: AU.addFilesProgressDialog,
    informationBar: AU.informationBar,

    upload: function () {
        ///	<summary>
        ///		Upload selected images to server.
        ///	</summary>
    },

    cancelUpload: function () {
        ///	<summary>
        ///		Cancel upload images.
        ///	</summary>
    }
}, true);

AU.imageUploaderFlash.__class = true;
AU.imageUploaderFlash.prototype.constructor = AU.imageUploaderFlash;

// Give the init function the Aurigma.uploader prototype for later instantiation
AU.imageUploaderFlash.fn.init.prototype = AU.imageUploaderFlash.fn;
AU.event = function evt() {
    ///	<summary>
    ///		Event object. You do not need to directly create it.
    ///	</summary>
    ///	<returns type="$au.event" />
    this._handlers = this._handlers || [];
};

AU.event.prototype = {
    __class: true,

    add: function(handler) {
        if (handler instanceof Array) {
            for (var i = 0, cnt = handler.length; i < cnt; i++)
                this._handlers.push(handler[i]);
        } else {
            this._handlers.push(handler);
        }
    },
    remove: function(handler) {
        for (var i in this._handlers) {
            if (this._handlers[i] === handler) {
                this._handlers.splice(i, 1);
                return true;
            }
        }
        return false;
    },
    clear: function() {
        this._handlers = [];
    },
    count: function() {
        return this._handlers.length;
    }
};

AU.event.prototype.constructor = AU.event;
/*******************
** Flash renderer **
*******************/
var flashRenderer = function (uploader) {

    if (!uploader)
        return;

    //create browser specific flash uploader markup
    var getHtml = function () {

        //build string with uploader params
        function createUploaderParam() {
            var paramsArr = uploader.getParams();
            for (var i = 0, cnt = paramsArr.length; i < cnt; i++) {
                paramsArr[i] = paramsArr[i].name + '=' + paramsArr[i].value;
            }
            return paramsArr.join("&");
        };

        var codeBase = uploader.flashControl().codeBase();
        var v = uploader.flashControl().version();

        if (v) {
            // prevent browser cache swf with different version
            if (codeBase.indexOf('?') > -1) {
                codeBase = codeBase + '&version=' + v;
            } else {
                codeBase += '?version=' + v;
            }
        }

        var html = [], i, tagName, id = uploader.id(),
            flashVersionRequired = uploader.flashControl().flashVersionRequired.join(",");
        var attributes = {
            id: id,
            name: id,
            width: uploader.width(),
            height: uploader.height()
        };
        if (wnd.ActiveXObject) {
            // ActiveX supported, so we use clsid for classid attribute (Internet Explorer).
            attributes.classid = "clsid:D27CDB6E-AE6D-11cf-96B8-444553540000";
            attributes.codebase = window.location.protocol + "//download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version="
                + flashVersionRequired;
        } else {
            attributes.data = codeBase;
            attributes.type = "application/x-shockwave-flash";
        }
        var flashControl = uploader.flashControl();
        var parameters = {
            quality: flashControl.quality() ||"high",
            bgcolor: flashControl.bgColor() || "#869ca7",
            wmode: flashControl.wmode() || 'window',
            allowScriptAccess: "always",
            flashvars: createUploaderParam() // most uploader params here
        };
        if (wnd.ActiveXObject) {
            parameters.movie = codeBase;
        }

        // invoke flashBeforeOpenTagRender callback
        var flashBeforeOpenTagRenderArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('flashBeforeOpenTagRender', flashBeforeOpenTagRenderArgs);
        // add before open tag html if any
        if (result = flashBeforeOpenTagRenderArgs[1].resultHtml) {
            html.push(result);
        }

        /*************************************
        * Use <object> tag for all browsers. *
        *************************************/

        var tagName = 'object';

        html.push('<' + tagName + ' ');

        // invoke flashRenderStyleAttribute callback
        var flashRenderStyleAttributeArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('flashRenderStyleAttribute', flashRenderStyleAttributeArgs);
        // add style attributes if any
        if (result = flashRenderStyleAttributeArgs[1].resultHtml) {
            html.push(' style="' + result + '" ');
        }

        for (var i in attributes) {
            if (!attributes.hasOwnProperty || attributes.hasOwnProperty(i)) {
                html.push(i + '="' + htmlencode(attributes[i]) + '" ');
            }
        }

        html.push('>');

        for (var i in parameters) {
            if (!parameters.hasOwnProperty || parameters.hasOwnProperty(i)) {
                html.push('<param name="' + i + '" value="' + htmlencode(parameters[i]) + '" /> ');
            }
        }

        // invoke flashBeforeCloseTagRender callback
        var flashBeforeCloseTagRenderArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('flashBeforeCloseTagRender', flashBeforeCloseTagRenderArgs);
        // add before close tag html if any
        if (result = flashBeforeCloseTagRenderArgs[1].resultHtml) {
            html.push(result);
        }

        html.push('</' + tagName + '>');

        // invoke flashAfterCloseTagRender callback
        var flashAfterCloseTagRenderArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('flashAfterCloseTagRender', flashAfterCloseTagRenderArgs);
        // add after close tag html if any
        if (result = flashAfterCloseTagRenderArgs[1].resultHtml) {
            html.push(result);
        }

        return html.join("");
    };

    return {
        html: getHtml,
        write: function () {
            ///	<summary>
            ///		write flash uploader markup
            ///	</summary>
            document.write(this.html());
        }
    };
};
// set namespace property for VS intelisense
AU.__namespace = true;

//expose to global
wnd.Aurigma = wnd.Aurigma || { __namespace: true };
wnd.Aurigma.ImageUploaderFlash = AU;
// short alias
wnd.$au = AU;

})(window);

