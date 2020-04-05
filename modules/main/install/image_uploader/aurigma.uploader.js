(function(window, undefined) {
    var axVERSION = '7.0.37.0', jVERSION = '7.0.37.0';
    var uploaderClassID = '7ECB1A47-6647-4B2C-A8DA-675569C9FF15', uploaderProgID='Aurigma.Uploader.7037.1';
    var thumbnailClassID = '493B5A90-6B34-44BF-9CB4-37B22E511415', thumbnailProgID = 'Aurigma.Thumbnail.7037.1';
var
// Global entry
AU = window.Aurigma ? (window.Aurigma.ImageUploader || {}) : {},

undefinedStr = 'undefined',

objectCache = AU._objectCache || (AU._objectCache = {
    _cache: {},
    _uid: 0,
    put: function (obj) {
        if (typeof obj != undefinedStr) {
            var id = obj.id() || ('_obj' + (++this._uid));
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
}),

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
    this.isIE9 = this.isIE && (a.indexOf("msie 9.0") > -1);
    this.isBeforeIE6XPSP2 = this.isIE && !this.isIE6XPSP2 && !this.isIE7 && !this.isIE8 && !this.isIE9;
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

getCurrentUrl = window.getCurrentUrl = function () {
    return document.location.href;
},

// get handler name for event
// it will be global function called from uploader control
getGlobalHandlerName = function (uploader, eventName) {
    // pattern: '__uploaderID_eventName'
    return '__' + uploader.id() + '_' + eventName;
},

// get uploader parameters in array of { name: 'name', value: 'value' } objects
getParams = function () {
    ///	<summary>
    ///		For internal use only! Get params array for rendering control.
    ///	</summary>
    var params = [], i, cnt, value, properties = this._simpleProperties;
    //simple properties just copy to array
    if (properties && properties.length > 0) {
        for (i = 0, cnt = this._simpleProperties.length; i < cnt; i++) {
            if (!properties[i].isAttribute) {
                value = this[properties[i].name]() + '';
                if (value != 'null' && value != undefinedStr) {
                    var name = properties[i].render || properties[i].name;
                    name = name.charAt(0).toUpperCase() + name.substring(1);
                    params.push({ name: name, value: value });
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

helpers = {
    prop: function (obj, name, setter, value, options) {
        options = options || {};
        if (options.func) {
            var p;
            if (setter) {
                p = 'set' + name;
                if (!(p in obj)) {
                    showError('Control have not "' + p + '" property.');
                    return null;
                }
                obj[p](fixValue(value));
            } else {
                p = 'get' + name;
                if (!(p in obj)) {
                    showError('Control have not "' + p + '" property.');
                    return null;
                }
                return javaUtil.convertToJS(obj[p]());
            }
        } else {
            if (setter) {
                obj[name] = value;
            } else {
                return obj[name];
            }
        }
    },
    objProp: function (obj, name, setter, value, options) {
        if (setter) {
            this.set(obj[name], value);
        } else {
            return obj[name];
        }
    },
    set: function (obj, options) {
        for (var name in options) {
            if (options.hasOwnProperty(name)) {
                if (typeof obj[name] === 'function') {
                    obj[name](options[name]);
                } else {
                    showError('"' + name + '" property is not defined.');
                }
            }
        }
    }
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
            var field = '_' + property.name;
            obj[field] = property.defaultValue;
            obj[property.name] = function () {
                return helpers.prop(this, field, arguments.length, arguments[0]);
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
            return helpers.objProp(this, field, arguments.length, arguments[0]);
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
                }

                // Enable property detection only for windows
                if (AU.browser.isWindowsOS) {
                    if (getter === 1) {
                        getter = "get" + propName;
                    }
                    if (setter === 1) {
                        setter = "set" + propName;
                    }
                    // Check if get method exist in control
                    if (getter && !(getter in element)) {
                        showInfo('Function "' + getter + '" undefined');
                    }
                    // Check if set method exist in control
                    // Note it may be just a read only property
                    if (setter && !(setter in element)) {
                        showInfo('Function "' + setter + '" undefined');
                    }
                }

                obj[property.name] = function () {
                    return helpers.prop(element, propName, arguments.length, arguments[0], { func: true });
                };

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
        var element = obj._uploader.getElement(), name = property.name;
        obj[name] = function () {
            return helpers.prop(element, name, arguments.length, arguments[0]);
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
                    return element[controlMethodName]();
                case 1:
                    return element[controlMethodName](fixValue(arg[0]));
                case 2:
                    return element[controlMethodName](fixValue(arg[0]), fixValue(arg[1]));
                case 3:
                    return element[controlMethodName](fixValue(arg[0]), fixValue(arg[1]), fixValue(arg[2]));
                case 4:
                    return element[controlMethodName](fixValue(arg[0]), fixValue(arg[1]), fixValue(arg[2]), fixValue(arg[3]));
                case 5:
                    return element[controlMethodName](fixValue(arg[0]), fixValue(arg[1]), fixValue(arg[2]), fixValue(arg[3]), fixValue(arg[4]));
                default:
                    // use eval :(
                    showInfo('Method "' + controlMethodName + '" called using "eval" expression.');
                    var js = 'element[controlMethodName](';
                    for (var i = 0, imax = arg.length; i < imax; i++) {
                        js += 'arg[' + i + ']';
                        if (i = 1 < imax) {
                            js += ', ';
                        }
                    }
                    js += ')';
                    return eval(js);
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

function ax2jsArray(axArray) {
    ///	<summary>
    ///		Convert activex array-like object to js array.
    ///	</summary>
    ///	<param name="axArray" type="object">
    ///     Object returned from acrivex method.
    ///	</param>
    ///	<returns type="Array" />
    return new VBArray(axArray).toArray();
}

function js2axArray(jsArray) {
    ///	<summary>
    ///		Convert js array to array-like object for activex.
    ///	</summary>
    ///	<param name="jsArray" type="Array">
    ///	</param>
    ///	<returns type="Object" />
    var dict = new ActiveXObject("Scripting.Dictionary");
    if (!dict) {
        showError('Scripting.Dictionary object not available.');
    }
    for (var i = 0, imax = jsArray.length; i < imax; i++) {
        dict.add(i, jsArray[i]);
    }
    return dict.Items();
}

function java2jsArray(javaArray) {
    ///	<summary>
    ///		Convert java array-like object to js array.
    ///	</summary>
    ///	<param name="javaArray" type="object">
    ///     Object returned from java method.
    ///	</param>
    ///	<returns type="Array" />
    var arr = [];
    for (var i = 0, imax = javaArray.length; i < imax; i++) {
        arr.push(javaArray[i]);
    }
    return arr;
}

function fixValue(value) {
    // BUG13680: Convert arguments to string
    return value + '';
}
var javaUtil = {
    convertToJS: function (value) {
        ///	<summary>
        ///		Convert java object to native javascript type.
        ///	</summary>
        var type = typeof value;
        if (type !== 'string' && type !== 'number' && type !== 'boolean') {
            try {
                if ('intValue' in value) {
                    value = value.intValue() * 1; // Convert to number
                } else if ('booleanValue' in value) {
                    value = !!value.booleanValue(); // Convert to boolean
                } else {
                    value += ''; // Convert to string
                }
            } catch (err) { }
        }
        return value;
    }
};
// Based on Java Deployment Toolkit script
AU.javaDetector = {
    // mime-type of the DeployToolkit plugin object
    oldMimeType: 'application/npruntime-scriptable-plugin;DeploymentToolkit',
    mimeType: 'application/java-deployment-toolkit',
    list: null,

    getJREs: function () {
        if (this.list == null) {

            if (!this._written) {
                this.writePluginTag();
            }

            var list = [], version, matches;
            var ua = navigator.userAgent.toLowerCase();

            var javaPlugin = document.getElementById('deployJavaPlugin');
            if (javaPlugin && javaPlugin.jvms) {
                // Try to detect java using Java Deployment Toolkit.
                version = this.testUsingDeploymentPlugin(javaPlugin);
            } else if (window.ActiveXObject) {
                // Detect java version for IE without Java Deployment Toolkit.
                version = this.testUsingActiveX();
            } else {
                if (typeof window.java !== undefinedStr && window.java.lang) {
                    // The second method: check window.java property.
                    version = this.testUsingJavaProperty();
                } else {
                    // Test using mime-types array
                    version = this.testUsingMimeTypes();

                    if (!version) {
                        // Test using plugis array
                        version = this.testUsingPluginsArray();

                    }
                }
            }
            if (version) {
                // Convert version number from string values to numbers.
                for (var i = 0, imax = version.length; i < imax; i++) {
                    var n = parseInt(version[i], 10);
                    if (!isNaN(n)) {
                        version[i] = n;
                    }
                }
                list.push(version);
            }
            this.list = list;
            return list;
        } else {
            return this.list;
        }
    },

    // return version array or null if java is not detected.
    testUsingDeploymentPlugin: function (javaPlugin) {
        var jvms = javaPlugin.jvms, version = [];
        for (var i = 0, imax = jvms.getLength(); i < imax; i++) {
            var v = jvms.get(i).version;
            if (v) {
                matches = /^(\d+)\.(\d+)\.(\d+)_(\d+)$/.exec(v);
                if (!matches || matches.length === 0) {
                    matches = /^(\d+)\.(\d+)\.(\d+)$/.exec(v);
                    if (matches && matches.length > 0) {
                        matches[4] = 0;
                    } else {
                        continue;
                    }
                }
                v = matches.slice(1);
                if (version.length == 0 || (
                    parseInt(version[0], 10) < parseInt(v[0], 10) ||
                    parseInt(version[1], 10) < parseInt(v[1], 10) ||
                    parseInt(version[2], 10) < parseInt(v[2], 10) ||
                    parseInt(version[3], 10) < parseInt(v[3], 10))) {
                    version = v;
                }
            }
        }
        if (version.length > 0) {
            return version;
        } else {
            return null;
        }
    },

    // return version array or null if java is not detected.
    testUsingActiveX: function () {
        // Try to detect java in IE using ActiveX
        var versions = ['1.8.0.0', '1.7.0.0', '1.6.0.0', '1.5.0.0', '1.4.2.0'], version;
        for (var i = 0, imax = versions.length; i < imax; i++) {
            var v = versions[i]; objectName = 'JavaWebStart.isInstalled.' + v;
            try {
                if (new ActiveXObject(objectName) != null) {
                    version = v.split('.');
                }
            } catch (e) { }
        }
        // We can get only two numbers of version, the third and forth we set to undefined.
        if (version) {
            version[3] = undefined;
            if (version[2] == '0') { // For 1.4.2 it will be 2
                version[2] = undefined;
            }
        }

        return version;
    },

    // return version array or null if java is not detected.
    testUsingJavaProperty: function () {
        var j = window.java, version = null;
        try {
            var v = j.lang.System.getProperty("java.version");
            matches = /^(\d+)\.(\d+)\.(\d+)_(\d+)$/.exec(v);
            version = matches.slice(1);
        } catch (e) { }
        return version;
    },

    // Return version array or null if java is not detected.
    testUsingMimeTypes: function () {
        // Test using mime-types array
        var mtypes = navigator.mimeTypes, version = null, version1 = '', mathes;
        if (mtypes && mtypes.length > 0) {
            for (var i = 0, imax = mtypes.length; i < imax; i++) {
                var mtype = mtypes[i].type;
                // The jpi-version is the plug-in version.  This is the best version to use.
                matches = /^application\/x-java-applet;jpi-version=(.*)$/.exec(mtype);
                if (matches && matches.length > 0) {
                    version = matches[1];
                    // Parse version string
                    matches = /^(\d+)\.(\d+)\.?(\d+)?_?(\d+)?$/.exec(version);
                    version = matches.slice(1);
                    break;
                }
                matches = /^application\/x-java-applet\x3Bversion=(1\.8|1\.7|1\.6|1\.5|1\.4\.2)$/.exec(mtype);
                if (matches && matches.length > 0) {
                    // The possible string values are: "1.8", "1.7", "1.6", "1.5", "1.4.2".
                    // We can compare them without without splitting by dot and converting to number.
                    version1 = version1 < matches[1] ? matches[1] : version1;
                }
            }

            if (!version && version1) {
                // We get version from application\/x-java-applet;version=... mime-type.
                version = version1.split('.');

                // This method is not so precise and we can get only first two number of version
                // (or three for 1.4.2).
                // The third and fourth numbers set to undefined.
                while (version.length < 4) {
                    version.push(undefined);
                }
            }
        }

        return version;
    },

    testUsingPluginsArray: function () {
        var plugins, version = [];
        if ((plugins = navigator.plugins) && plugins.length > 0) {
            var m, s;
            for (var i = 0, imax = plugins.length; i < imax; i++) {
                var d = plugins[i].description;
                var matches = /^Java (1\.4|1\.5|1\.6|1\.7|1\.8)\.?(\d+)?_?(\d+)?.* Plug-in/.exec(d);
                if (matches && matches.length > 0) {
                    // From regular expressin we get first and second numbers in one value and need to split it.
                    var v = matches.slice(0);
                    v[0] = matches[1].split('.')[0];
                    v[1] = matches[1].split('.')[1];
                    if (version.length == 0 || (
                        parseInt(version[0], 10) < parseInt(v[0], 10) ||
                        parseInt(version[1], 10) < parseInt(v[1], 10) ||
                        parseInt(version[2], 10) < parseInt(v[2], 10) ||
                        parseInt(version[3], 10) < parseInt(v[3], 10))) {
                        version = v;
                    }
                }
            }
        }

        if (version.length > 0) {
            return version;
        } else {
            return null;
        }
    },

    // return true if 'installed' (considered as a JRE version string) is
    // greater than or equal to 'required' (again, a JRE version string).
    compareVersions: function (installed, required) {

        var a = installed;
        var b = required;

        for (var i = 0, imax = Math.min(a.length, b.length); i < imax; i++) {
            if (a[i] < b[i]) {
                return false;
            }
        }

        return true;
    },

    writePluginTag: function () {
        //check if ActiveX enabled
        if (typeof ActiveXObject != undefinedStr) {
            try {
                xmlhttp = new ActiveXObject("Msxml2.XMLHTTP");
                this.isActiveXEnabled = true;
            } catch (e) {
                try {
                    xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
                    this.isActiveXEnabled = true;
                }
                catch (e1) {
                    this.isActiveXEnabled = false;
                }
            }
        } else {
            this.isActiveXEnabled = false;
        }

        if (this.isActiveXEnabled) {
            var o = document.createElement('object');
            o.id = 'deployJavaPlugin';
            var style = o.style;
            style.width = 0;
            style.height = 0;
            style.position = 'absolute';
            style.left = '-10px';
            style.padding = 0;
            style.margin = 0;
            style.border = '0 none';
			// BUG0013497 put classid assignment into try-catch block
			try {
				o.classid = 'clsid:CAFEEFAC-DEC7-0000-0001-ABCDEFFEDCBA';
			} catch (ex) {
				try {
					// try old classid
					o.classid = 'clsid:CAFEEFAC-DEC7-0000-0000-ABCDEFFEDCBA';
				} catch (ex) {}
			}
			if (o.classid) {
				var body = document.getElementsByTagName('body')[0];
				body.insertBefore(o, body.firstChild);
			}
        } else if (AU.browser.isSafari || AU.browser.isOpera) {
            //don't have plugin for Safari or Opera
        } else {
            this.writeEmbedTag();
        }

        this._written = true;
    },

    writeEmbedTag: function () {
        var o = null;
        if (navigator.mimeTypes != null) {
            for (var i = 0; i < navigator.mimeTypes.length; i++) {
                if (navigator.mimeTypes[i].type == this.mimeType) {
                    if (navigator.mimeTypes[i].enabledPlugin) {
                        o = document.createElement('embed');
                        o.type = this.mimeType;
                        break;
                    }
                }
            }
            // if we ddn't find new mimeType, look for old mimeType
            if (o == null) {
                for (var i = 0; i < navigator.mimeTypes.length; i++) {
                    if (navigator.mimeTypes[i].type == this.oldMimeType) {
                        if (navigator.mimeTypes[i].enabledPlugin) {
                            o = document.createElement('embed');
                            o.type = this.oldMimeType;
                            break;
                        }
                    }
                }
            }

            if (o != null) {
                o.id = 'deployJavaPlugin';
                o.hidden = true;
                var style = o.style;
                style.padding = 0;
                style.margin = 0;
                style.border = '0 none';
                style.width = 0;
                style.height = 0;
                var body = document.getElementsByTagName('body')[0];
                body.insertBefore(o, body.firstChild);
            }
        }
    }
};
(function ff(jd) {
    if (jd._written) {
        return;
    }

    var ready;
    if (!document && document.getElementsByTagName) {
        ready = false;
    } else {
        var body = document.getElementsByTagName('body');
        if (body && body.length > 0 && (body = body[0]) != null && body.firstChild) {
            ready = true;
        }
    }
    if (ready) {
        jd.writePluginTag();
    } else {
        setTimeout(function () { ff(AU.javaDetector); }, 50);
    }
})(AU.javaDetector);
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
                    if (typeof window.console != undefinedStr && console.log) {
                        // print message to console
                        console.log(msg);
                        return;
                    }
                    break;
                case 'popup':
                    if (!this._popupWindow || this._popupWindow.closed) {
                        // open popup window
                        this._popupWindow = window.open('', 'ImageUploaderDebugWindow',
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
            else if (v !== null && typeof v != undefinedStr)
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

    type: function () {
        function detectType(typeString) {
            var types = (typeString + "").split('|');
            // Check availability for all except last one.
            // The last will be used if all previous are not available.
            for (var i = 0, imax = types.length - 1; i < imax; i++) {
                var type = types[i].toLowerCase();

                if (type === 'activex') {
                    // Can we use ActiveX?
                    if (this.activeXControl().isActiveXSupported()) {
                        return type;
                    }
                } else if (type === 'java') {
                    // Can we use Java?
                    if (this.javaControl().isJavaSupported()) {
                        return type;
                    }
                }

            }
            // Can't detect what platform available. Choose the last one.
            return types[types.length - 1];
        }
        if (arguments.length > 0) {
            // Set value if we pass parameter
            this._platform = detectType.call(this, arguments[0]);
        } else {
            if (!this._platform) {
                var type = 'activex|java';
                this._platform = detectType.call(this, type);
            }

            // Return value if call method without parameter.
            return this._platform;
        }
    },
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
                showError('Control have not ' + name + ' property.');
            }
        }
    },

    writeHtml: function () {
        window.document.write(this.getHtml());
    },

    getHtml: function () {
        var self = this, id = this.id(), events = this.events();

        /* create events function*/
        var createEventHandler = function (event, eventName) {
            var h = function () {
                var result1 = true;
                if (event._handlers && event._handlers.length > 0) {
                    var args = [];
                    for (var j = 0, jmax = arguments.length; j < jmax; j++) {
                        args.push(javaUtil.convertToJS(arguments[j]));
                    }
                    for (var i = 0, imax = event._handlers.length; i < imax; i++) {
                        try {
                            var handler = event._handlers[i];
                            var result;
                            if (typeof handler === 'function') {
                                result = handler.apply(self, args);
                            } else if (typeof window[handler] === 'function') {
                                result = window[handler].apply(self, args);
                            } else {
                                showError(eventName + " error:\n \"" + handler + "\" handler is not defined.");
                            }
                            if (eventName === 'beforeUpload' || eventName === 'afterPackageUpload') {
                                // for beforeUpload and afterPackageUpload events
                                // we returns false if any event handler returns false
                                if (!result1) {
                                    // already get false in prev handler
                                    result = result1;
                                } else if (result != null) {
                                    // check if method returns false
                                    result1 = !(result === false);
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
            if (eventName == 'initComplete') {
                return function () { setTimeout(h, 500); }
            } else {
                return h;
            }
        };

        // Bug 0012013: Workaround property for FF on Mac OS.
        var enableResumeUploadCallback = /mac/i.test(navigator.platform) && /firefox/i.test(navigator.userAgent);
        if (enableResumeUploadCallback) {
            showInfo('ResumeUploadCallback mode is turned on.');
            createEventHandler = function (event, eventName) {
                var h = function () {
                    var result1 = true;
                    if (event._handlers && event._handlers.length > 0) {
                        var args = [];
                        for (var j = 0, jmax = arguments.length; j < jmax; j++) {
                            args.push(javaUtil.convertToJS(arguments[j]));
                        }
                        for (var i = 0, imax = event._handlers.length; i < imax; i++) {
                            try {
                                var handler = event._handlers[i];
                                var result;
                                if (typeof handler === 'function') {
                                    result = handler.apply(self, args);
                                } else if (typeof window[handler] === 'function') {
                                    result = window[handler].apply(self, args);
                                } else {
                                    showError(eventName + " error:\n \"" + handler + "\" handler is not defined.");
                                }
                                if (eventName === 'beforeUpload' || eventName === 'afterPackageUpload') {
                                    // for beforeUpload and afterPackageUpload events
                                    // we returns false if any event handler returns false
                                    if (!result1) {
                                        // already get false in prev handler
                                        result = result1;
                                    } else if (result != null) {
                                        // check if method returns false
                                        result1 = !(result === false);
                                    }
                                }
                            }
                            catch (err) {
                                showError(eventName + " error:\n" + (err.message || err.description || err));
                                throw err;
                            }
                        }
                    }

                    if (eventName === 'beforeUpload' || eventName === 'beforePackageUpload' || eventName === 'afterPackageUpload' || eventName === 'beforeSendRequest') {
                        setTimeout(function () {
                            // Call uploader method to return value from event handler
                            var el = document.getElementById(id);
                            if (el && 'ResumeUpload' in el) {
                                showInfo('Call ResumeUpload for ' + eventName + ' event.');
                                el.ResumeUpload(eventName, result);
                            }
                        }, 50);
                    }
                    // return result from last handler
                    return result;
                };
                if (eventName == 'initComplete') {
                    return function () { setTimeout(h, 500); }
                } else {
                    return h;
                }
            };
        }

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
                    window[getGlobalHandlerName(this, i)] = createEventHandler(event(), i);
                }
            }
        }

        var html, type = this.type();
        if (type == "java") {
            html = javaRenderer(this, { enableResumeUploadCallback: enableResumeUploadCallback }).html();
        } else if (type == "activex") {
            html = activeXRenderer(this).html();
        } else {
            html = 'Not supported.';
        }
        return html;
    },

    getElement: function () {
        return window.document.getElementById(this.id());
    },

    getParams: function () {
        var params = getParams.call(this);
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
        ///		InitComplete event.
        ///	</summary>
        ///	<returns type="$au.event" />
        /* will be created while initialization*/
    },
    preRender: function () {
        ///	<summary>
        ///		PreRender event.
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
AU.activeXControl = function (uploader) {
    this._uploader = uploader;
    this.codeBase('Scripts/ImageUploader7.cab');
    this.codeBase64('Scripts/ImageUploader7_x64.cab');
};

AU.activeXControl.prototype = {
    __class: true,

    getActiveXInstalledToUpdate: function () {
        var progId = this.progId();
        if (progId) {
            try {
                // Get installed control version
                var a = new ActiveXObject(progId);
                var currVersion = a.Version.split(',');
                delete a;

                // Get required control version
                var requiredVersion = this.version().split('.');

                // Compare versions
                for (var i = 0; i < 4; i++) {
                    if (parseInt(currVersion[i], 10) < parseInt(requiredVersion[i], 10)) {
                        //installed version is older then the current one
                        //need to update control
                        return true;
                    }
                }
            }
            catch (e) {
            }
        }
        //no update required
        return false;
    },

    isActiveXSupported: function () {
        return (typeof window.ActiveXObject !== undefinedStr); // True if window.ActiveXObject exists.
    },

    actualVersion: function () {
        var el = this._uploader.getElement();
        if (el) {
            return el.getVersion();
        } else {
            return null;
        }
    },

    codeBase: function (value) {
        ///	<summary>
        ///		Get or set URL to .cab file
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        return helpers.prop(this, '_codeBase', arguments.length, value);
    },
    codeBase64: function (value) {
        ///	<summary>
        ///		Get or set URL to x64 .cab file
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        return helpers.prop(this, '_codeBase64', arguments.length, value);
    },
    classId: function (value) {
        ///	<summary>
        ///		Get or set clsid of activex control.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     classId
        ///	</param>
        ///	<returns type="String" />
        return helpers.prop(this, '_classId', arguments.length, value);
    },
    progId: function (value) {
        ///	<summary>
        ///		Get or set progid of activex control.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     progId
        ///	</param>
        ///	<returns type="String" />
        return helpers.prop(this, '_progId', arguments.length, value);
    },
    version: function (value) {
        ///	<summary>
        ///		Gets or sets minimum required version of the ActiveX control (ImageUploader7.cab file).
        ///	</summary>
        ///	<param name="value" type="String">
        ///     Version string in the x.x.x.x format
        ///	</param>
        ///	<returns type="String" />
        return helpers.prop(this, '_version', arguments.length, value);
    }
};
AU.addFilesProgressDialog = function () {
    ///	<summary>
    ///		Get or set addFolderDialog properties
    ///	</summary>
    ///	<param name="addFilesProgressDialog" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.addFilesProgressDialog" />
}

AU.addFilesProgressDialog.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cancelButtonText", getter: 1, setter: 1, render: "AddFilesProgressDialogCancelButtonText" },
        { name: "currentFileText", getter: 1, setter: 1, render: "AddFilesProgressDialogCurrentFileText" },
        { name: "titleText", getter: 1, setter: 1, render: "AddFilesProgressDialogTitleText" },
        { name: "totalFilesText", getter: 1, setter: 1, render: "AddFilesProgressDialogTotalFilesText" },
        { name: "waitText", getter: 1, setter: 1, render: "AddFilesProgressDialogWaitText" }
    ],
    getParams: getParams,

    cancelButtonText: function (value) {
        ///	<summary>
        ///		Gets or sets cancel button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    currentFileText: function (value) {
        ///	<summary>
        ///		Gets or sets message like "Processing file: [fileName]".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    titleText: function (value) {
        ///	<summary>
        ///		Gets or sets title text of the add folder dialog window.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    totalFilesText: function (value) {
        ///	<summary>
        ///		Gets or sets message like "already processed files: [fileCount]".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    waitText: function (value) {
        ///	<summary>
        ///		Gets or sets message like "Please wait, it may take some time...".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    }
}

AU.addFilesProgressDialog.init = propertyMaker._typicalInit;
AU.addFilesProgressDialog.reinit = propertyMaker._typicalReinit;
AU.authenticationDialog = function () {
    ///	<summary>
    ///		Get or set authenticationDialog properties
    ///	</summary>
    ///	<param name="authenticationDialog" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.authenticationDialog" />
}

AU.authenticationDialog.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cancelButtonText", getter: 1, setter: 1, render: "AuthenticationDialogCancelButtonText" },
        { name: "loginText", getter: 1, setter: 1, render: "AuthenticationDialogLoginText" },
        { name: "okButtonText", getter: 1, setter: 1, render: "AuthenticationDialogOkButtonText" },
        { name: "passwordText", getter: 1, setter: 1, render: "AuthenticationDialogPasswordText" },
        { name: "realmText", getter: 1, setter: 1, render: "AuthenticationDialogRealmText" },
        { name: "text", getter: 1, setter: 1, render: "AuthenticationDialogText" }
    ],
    getParams: getParams,

    cancelButtonText: function (value) {
        ///	<summary>
        ///		Gets or sets cancel button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    loginText: function (value) {
        ///	<summary>
        ///		Gets or sets login label text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    okButtonText: function (value) {
        ///	<summary>
        ///		Gets or sets ok button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    passwordText: function (value) {
        ///	<summary>
        ///		Gets or sets password label text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    realmText: function (value) {
        ///	<summary>
        ///		Gets or sets realm label text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    text: function (value) {
        ///	<summary>
        ///		Gets or sets text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    }
}

AU.authenticationDialog.init = propertyMaker._typicalInit;
AU.authenticationDialog.reinit = propertyMaker._typicalReinit;
AU.contextMenu = function () {
    ///	<summary>
    ///		Get or set contextMenu properties
    ///	</summary>
    ///	<param name="contextMenu" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.contextMenu" />
}

AU.contextMenu.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "addFilesText", getter: 1, setter: 1, render: "AddFilesMenuText" },
        { name: "addFolderText", getter: 1, setter: 1, render: "AddFolderMenuText" },
        { name: "arrangeByDimensionsText", getter: 1, setter: 1, render: "ArrangeByDimensionsMenuText" },
        { name: "arrangeByModifiedText", getter: 1, setter: 1, render: "ArrangeByModifiedMenuText" },
        { name: "arrangeByNameText", getter: 1, setter: 1, render: "ArrangeByNameMenuText" },
        { name: "arrangeByPathText", getter: 1, setter: 1, render: "ArrangeByPathMenuText" },
        { name: "arrangeBySizeText", getter: 1, setter: 1, render: "ArrangeBySizeMenuText" },
        { name: "arrangeByText", getter: 1, setter: 1, render: "ArrangeByMenuText" },
        { name: "arrangeByTypeText", getter: 1, setter: 1, render: "ArrangeByTypeMenuText" },
        { name: "checkAllText", getter: 1, setter: 1, render: "CheckAllMenuText" },
        { name: "checkText", getter: 1, setter: 1, render: "CheckMenuText" },
        { name: "detailsViewText", getter: 1, setter: 1, render: "DetailsViewMenuText" },
        { name: "editText", getter: 1, setter: 1, render: "EditMenuText" },
        { name: "editDescriptionText", getter: 1, setter: 1, render: "EditDescriptionMenuText" },
        { name: "listViewText", getter: 1, setter: 1, render: "ListViewMenuText" },
        { name: "navigateToFolderText", getter: 1, setter: 1, render: "NavigateToFolderMenuText" },
        { name: "openText", getter: 1, setter: 1, render: "OpenMenuText" },
        { name: "pasteText", getter: 1, setter: 1, render: "PasteMenuText" },
        { name: "removeAllText", getter: 1, setter: 1, render: "RemoveAllMenuText" },
        { name: "removeText", getter: 1, setter: 1, render: "RemoveMenuText" },
        { name: "thumbnailsViewText", getter: 1, setter: 1, render: "ThumbnailsViewMenuText" },
        { name: "tilesViewText", getter: 1, setter: 1, render: "TilesViewMenuText" },
        { name: "uncheckAllText", getter: 1, setter: 1, render: "UncheckAllMenuText" },
        { name: "uncheckText", getter: 1, setter: 1, render: "UncheckMenuText" }
    ],
    getParams: getParams,

    addFilesText: function (value) {
        ///	<summary>
        ///		Gets or sets "Add Files..." menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    addFolderText: function (value) {
        ///	<summary>
        ///		Gets or sets "Add Folder..." menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    checkAllText: function (value) {
        ///	<summary>
        ///		Gets or sets "Check all" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    checkText: function (value) {
        ///	<summary>
        ///		Gets or sets "Check" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    arrangeByDimensionsText: function (value) {
        ///	<summary>
        ///		Gets or sets "Arrange by" -> "Dimensions" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    arrangeByModifiedText: function (value) {
        ///	<summary>
        ///		Gets or sets "Arrange by" -> "Modified" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    arrangeByNameText: function (value) {
        ///	<summary>
        ///		Gets or sets "Arrange by" -> "Name" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    arrangeByPathText: function (value) {
        ///	<summary>
        ///		Gets or sets "Arrange by" -> "Path" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    arrangeBySizeText: function (value) {
        ///	<summary>
        ///		Gets or sets "Arrange by" -> "Size" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    arrangeByText: function (value) {
        ///	<summary>
        ///		Gets or sets "Arrange by" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    arrangeByTypeText: function (value) {
        ///	<summary>
        ///		Gets or sets "Arrange by" -> "Type" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    detailsViewText: function (value) {
        ///	<summary>
        ///		Gets or sets "Details" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    editText: function (value) {
        ///	<summary>
        ///		Gets or sets "Edit" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    editDescriptionText: function (value) {
        ///	<summary>
        ///		Gets or sets "Edit Description" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    listViewText: function (value) {
        ///	<summary>
        ///		Gets or sets "List" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    navigateToFolderText: function (value) {
        ///	<summary>
        ///		Gets or sets "Navigate to folder..." menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    openText: function (value) {
        ///	<summary>
        ///		Gets or sets "Open" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    pasteText: function (value) {
        ///	<summary>
        ///		Gets or sets "Paste" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    removeAllText: function (value) {
        ///	<summary>
        ///		Gets or sets "Remove All" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    removeText: function (value) {
        ///	<summary>
        ///		Gets or sets "Remove" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    thumbnailsViewText: function (value) {
        ///	<summary>
        ///		Gets or sets "Thumbnails" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    tilesViewText: function (value) {
        ///	<summary>
        ///		Gets or sets "Tiles" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    uncheckAllText: function (value) {
        ///	<summary>
        ///		Gets or sets "Uncheck all" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    uncheckText: function (value) {
        ///	<summary>
        ///		Gets or sets "Uncheck" menu item text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    }
}

AU.contextMenu.init = propertyMaker._typicalInit;
AU.contextMenu.reinit = propertyMaker._typicalReinit;
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
        { name: "hash", getter: 1, setter: 1, render: "ConverterHash" },
        { name: "thumbnailApplyCrop", getter: 1, setter: 1, render: "ConverterThumbnailApplyCrop" },
        { name: "thumbnailBgColor", getter: 1, setter: 1, render: "ConverterThumbnailBgColor" },
        { name: "thumbnailCompressOversizedOnly", getter: 1, setter: 1, render: "ConverterThumbnailCompressOversizedOnly" },
        { name: "thumbnailCopyExif", getter: 1, setter: 1, render: "ConverterThumbnailCopyExif" },
        { name: "thumbnailCopyIptc", getter: 1, setter: 1, render: "ConverterThumbnailCopyIptc" },
        { name: "thumbnailFitMode", getter: 1, setter: 1, render: "ConverterThumbnailFitMode" },
        { name: "thumbnailHeight", getter: 1, setter: 1, render: "ConverterThumbnailHeight" },
        { name: "thumbnailJpegQuality", getter: 1, setter: 1, render: "ConverterThumbnailJpegQuality" },
        { name: "thumbnailKeepColorSpace", getter: 1, setter: 1, render: "ConverterThumbnailKeepColorSpace" },
        { name: "thumbnailResizeQuality", getter: 1, setter: 1, render: "ConverterThumbnailResizeQuality" },
        { name: "thumbnailResolution", getter: 1, setter: 1, render: "ConverterThumbnailResolution" },
        { name: "thumbnailWatermark", getter: 1, setter: 1, render: "ConverterThumbnailWatermark" },
        { name: "thumbnailWidth", getter: 1, setter: 1, render: "ConverterThumbnailWidth" }
    ],
    // for mode need additional validation
    mode: function () {
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

    hash: function (value) {
        ///	<summary>
        ///		Gets or sets an algorithm to generate a hash value for converted file.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     The algorithm to generate a hash value. The "SHA", "MD2", "MD5" algorithms are supported.
        /// </param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },

    thumbnailApplyCrop: function (value) {
        ///	<summary>
        ///		Enable or disable crop thumbnail if crop settings specified for the file.
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
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
    thumbnailCompressOversizedOnly: function (value) {
        ///	<summary>
        ///		Switch that specifies whether an original file should be
        ///     uploaded as the specified thumbnail in case when original
        ///     image dimensions and file size are not bigger than dimensions
        ///     and file size of the thumbnail.
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    thumbnailCopyExif: function (value) {
        ///	<summary>
        ///		Enable or disable copying EXIF data from original file to thumbnail.
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    thumbnailCopyIptc: function (value) {
        ///	<summary>
        ///		Enable or disable copying IPTC data from original file to thumbnail.
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
        ///     One of the values "Fit", "Width", "Height", "ActualSize" or their number equivalent.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    thumbnailHeight: function (value) {
        ///	<summary>
        ///		Get or set maximum thumbnail height.
        ///	</summary>
        ///	<param name="value" type="Number" />
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
    thumbnailKeepColorSpace: function (value) {
        ///	<summary>
        ///		Keep original colorspace when create thumbnail
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    thumbnailResizeQuality: function (value) {
        ///	<summary>
        ///		Get or set thumbnail resize quality.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the values "ResizeQualityMedium", "ResizeQualityHigh" or their number equivalent.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    thumbnailResolution: function (value) {
        ///	<summary>
        ///		Get or set thumbnail resolution.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    thumbnailWatermark: function (value) {
        ///	<summary>
        ///		Get or set watermark format for thumbnail.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     Watermark format string
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    thumbnailWidth: function (value) {
        ///	<summary>
        ///		Get or set maximum thumbnail width.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },

    set: function (json) {
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
    toJson: function () {
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
    _validateMode: function (modeString) {
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

//change converter to work with rendered control
AU.converter.reinit = function (converter, element) {
    converter.mode = function () {
        if (arguments.length > 0) {
            if (arguments[0]) {
                this._validateMode(arguments[0]);
                element.setConverterMode(fixValue(this._index), fixValue(arguments[0]));
            }
        } else {
            return javaUtil.convertToJS(element.getConverterMode(fixValue(this._index)));
        }
    };
    converter.mode.isUploaderProperty = true;

    if (converter._simpleProperties) {
        for (var i = 0, imax = converter._simpleProperties.length; i < imax; i++) {
            var property = converter._simpleProperties[i];
            if (!property.isAttribute) {
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
                                element[setter](fixValue(this._index), fixValue(arguments[0]));
                            }
                        } else {
                            return javaUtil.convertToJS(element[getter](fixValue(this._index)));
                        }
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

    add: function (converter) {
        ///	<summary>
        ///		Add new converter to uploader.
        ///	</summary>
        ///	<returns type="$au.converter" />
        if (converter !== undefined) {
            if (!(converter instanceof AU.converter)) {
                converter = new AU.converter(converter);
            }
            this._items.push(converter);
            return converter;
        }
    },
    count: function () {
        ///	<summary>
        ///		Get count of added converters.
        ///	</summary>
        ///	<returns type="Number" />
        return this._items.length;
    },
    get: function (index) {
        ///	<summary>
        ///		Get added converter.
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Converter index
        ///	</param>
        ///	<returns type="$au.converter" />
        return this._items[index];
    },
    remove: function (index) {
        ///	<summary>
        ///		Remove converter.
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Converter index
        ///	</param>
        this._items.splice(index, 1);
    },
    removeAll: function () {
        ///	<summary>
        ///		Remove all converters.
        ///	</summary>
        this._items.length = 0;
        while (this.count() > 0) {
            this.remove(0);
        }
    }
};

AU.converters.init = function (obj, uploader) {
    obj._uploader = uploader;
    obj._items = [new AU.converter({ mode: "*.*=SourceFile" })];
}

// change prototype to work with rendered control
// for internal use
AU.converters.reinit = function (converters) {
    try {
        showInfo("[js_info] Reinit converters.");

        var element = converters._uploader.getElement();

        if (AU.debug().level() >= 3) {
            // check element converter methods existance
            if (!('getConverterCount' in element)) {
                showError('getConverterCount method is not defined.');
            }
            if (!('AddConverter' in element)) {
                showError('addConverter method is not defined.');
            }
            if (!('RemoveConverter' in element)) {
                showError('removeConverter method is not defined.');
            }
        }

        //save converters added before
        var convertersArr = [], i, imax;
        for (i = 0, imax = converters._items.length; i < imax; i++) {
            var cv = converters._items[i];
            convertersArr.push(cv.toJson());
        }

        // remove converters from control
        while (element.getConverterCount() > 0) {
            element.RemoveConverter(fixValue(0));
        }

        //clear internal converters array
        converters._itemsCache = [];

        converters.add = function () {
            ///	<summary>
            ///		Add converter to uploader
            ///	</summary>
            ///	<returns type="$au.converter" added converter />
            if (arguments[0] === undefined) {
                return;
            }
            var index = element.AddConverter("*.*=SourceFile");
            if (arguments[0]) {
                var conv = this.get(index);
                conv.set(arguments[0]);
                return conv;
            }
        };

        converters.count = function () {
            return javaUtil.convertToJS(element.getConverterCount());
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
                if (this._itemsCache && this._itemsCache[index]) {
                    return this._itemsCache[index];
                } else {
                    this._itemsCache = this._itemsCache || [];
                    //create object to provide methods to work with thumbnail
                    var converter = new AU.converter();
                    converter._index = index;
                    AU.converter.reinit(converter, element);
                    this._itemsCache[index] = converter;
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
            element.RemoveConverter(fixValue(index));
            //remove from internal array
            this._itemsCache.splice(index, 1);
            // re-index items
            for (var i in this._items) {
                this._itemsCache[i]._index = i;
            }
        };

        // add saved converters to uploader control
        for (i = 0, imax = convertersArr.length; i < imax; i++) {
            converters.add(convertersArr[i]);
        }
    } catch (err) {
        showError('Converters initialization failed.\n' + (err.message || err.description || err));
        throw err;
    }
};
AU.deleteFilesDialog = function () {
    ///	<summary>
    ///		Get or set deleteFilesDialog properties
    ///	</summary>
    ///	<param name="deleteFilesDialog" type="Object">
    ///		An object with parameters
    ///	</param>
    ///	<returns type="$au.deleteFilesDialog" />
}

AU.deleteFilesDialog.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "message", getter: 1, setter: 1, render: "DeleteFilesDialogMessage" },
        { name: "titleText", getter: 1, setter: 1, render: "DeleteFilesDialogTitleText" }
    ],
    getParams: getParams,

    message: function (value) {
        ///	<summary>
        ///		Gets or sets message text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    titleText: function (value) {
        ///	<summary>
        ///		Gets or sets dialog title.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    }
}

AU.deleteFilesDialog.init = propertyMaker._typicalInit;
AU.deleteFilesDialog.reinit = propertyMaker._typicalReinit;
AU.descriptionEditor = function () {
    ///	<summary>
    ///		Get or set addFolderDialog properties
    ///	</summary>
    ///	<param name="addFolderDialog" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.addFolderDialog" />
}

AU.descriptionEditor.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cancelHyperlinkText", getter: 1, setter: 1, render: "DescriptionEditorCancelHyperlinkText" },
        { name: "maxTextLength", getter: 1, setter: 1, render: "DescriptionEditorMaxTextLength" },
        { name: "orEscLabelText", getter: 1, setter: 1, render: "DescriptionEditorOrEscLabelText" },
        { name: "saveButtonText", getter: 1, setter: 1, render: "DescriptionEditorSaveButtonText" }
    ],
    getParams: getParams,

    cancelHyperlinkText: function (value) {
        ///	<summary>
        ///		Cancel button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    maxTextLength: function (value) {
        ///	<summary>
        ///		Maximum length of the description string
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    orEscLabelText: function (value) {
        ///	<summary>
        ///		"(or Esc)" label text
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
AU.detailsViewColumns = function () {
    ///	<summary>
    ///		Get or set pane columns properties
    ///	</summary>
    ///	<param name="paneColumns" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.paneColumns" />
}

AU.detailsViewColumns.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "dimensionsText", getter: 1, setter: 1, render: "DetailsViewColumnDimensionsText" },
        { name: "fileNameText", getter: 1, setter: 1, render: "DetailsViewColumnFileNameText" },
        { name: "fileSizeText", getter: 1, setter: 1, render: "DetailsViewColumnSizeText" },
        { name: "fileTypeText", getter: 1, setter: 1, render: "DetailsViewColumnTypeText" },
        { name: "infoText", getter: 1, setter: 1, render: "DetailsViewColumnInfoText" },
        { name: "lastModifiedText", getter: 1, setter: 1, render: "DetailsViewColumnLastModifiedText" }
    ],
    getParams: getParams,

    dimensionsText: function (value) {
        ///	<summary>
        ///		Get or set title of the Dimensions column in the Details view.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    fileSizeText: function (value) {
        ///	<summary>
        ///		Get or set title of the file size column in the Details view.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    fileTypeText: function (value) {
        ///	<summary>
        ///		Get or set title of the file type column in the Details view.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    infoText: function (value) {
        ///	<summary>
        ///		Get or set title of the Info column in the Details view.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    lastModifiedText: function (value) {
        ///	<summary>
        ///		Get or set title of the last modification date column in the Details view.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
}

AU.detailsViewColumns.init = propertyMaker._typicalInit;
AU.detailsViewColumns.reinit = propertyMaker._typicalReinit;
AU.events = function (initObj) {
    ///	<summary>
    ///		Get or set uploader events
    ///	</summary>
    ///	<param name="initObj" type="Object">
    ///		Events
    ///	</param>
    ///	<returns type="$au.events" />
    /* will be created while initialization */
};

AU.events.prototype = new baseEvents();

extend(AU.events.prototype, {
    _eventNames: [undefined /*base event - initComplete*/, undefined /*base event - preRender*/,
        "afterPackageUpload",
        "afterSendRequest",
        "afterUpload",
        "beforePackageUpload",
        "beforeSendRequest",
        "beforeUpload",
        "folderChange",
        "imageEditorClose",
        "imageRotated",
        "restrictionFailed",
        "error",
        "progress",
        "selectionChange",
        "trace",
        "uploadFileCountChange",
        "viewChange"
    ],

    afterPackageUpload: function () {
        ///	<summary>
        ///		AfterPackageUpload event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    afterSendRequest: function () {
        ///	<summary>
        ///		AfterSendRequest event
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
    beforeSendRequest: function () {
        ///	<summary>
        ///		BeforeSendRequest event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    beforeUpload: function () {
        ///	<summary>
        ///		BeforeUpload event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    folderChange: function () {
        ///	<summary>
        ///		FolderChange event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    imageEditorClose: function () {
        ///	<summary>
        ///		ImageEditorClose event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    imageRotated: function () {
        ///	<summary>
        ///		ImageRotated event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    restrictionFailed: function () {
        ///	<summary>
        ///		RestrictionFailed event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    error: function () {
        ///	<summary>
        ///		Error event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    progress: function () {
        ///	<summary>
        ///		Progress event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    selectionChange: function () {
        ///	<summary>
        ///		SelectionChange event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    trace: function () {
        ///	<summary>
        ///		Trace event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    uploadFileCountChange: function () {
        ///	<summary>
        ///		UploadFileCountChange event
        ///	</summary>
        ///	<returns type="$au.event" />
    },
    viewChange: function () {
        ///	<summary>
        ///		ViewChange event
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

    angle: function (value) {
        ///	<summary>
        ///		Get or set rotate angle.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    cropBounds: function (value) {
        ///	<summary>
        ///		Get or set crop settings of the file in the upload pane.
        ///     When specified, all thumbnails from converters with enabled thumbnailCrop property will be cropped.
        ///	</summary>
        ///	<param name="value" type="Array">
        ///     Array with 4 integer values: [left, top, width, height].
        ///	</param>
        ///	<returns type="Array" />
        /* will be created while initialization */
    },
    description: function (value) {
        ///	<summary>
        ///		Get or set description for uploaded file.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    focused: function () {
        ///	<summary>
        ///		Check if file focused in the upload pane.
        ///	</summary>
        ///	<returns type="Boolen" />
        /* will be created while initialization */
    },
    format: function () {
        ///	<summary>
        ///		Get type of the file in the upload pane.
        ///	</summary>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    guid: function () {
        ///	<summary>
        ///		Get guid of the file in the upload pane.
        ///	</summary>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    height: function () {
        ///	<summary>
        ///		Get height of the file in the upload pane. Returns 0 for non-image file.
        ///	</summary>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    horizontalResolution: function () {
        ///	<summary>
        ///		Get image horizontal image resolution of the file in the upload pane. Returns 0 for non-image file.
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
    selected: function (selected) {
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
    tag: function (value) {
        ///	<summary>
        ///		Get or set tag for uploaded file.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    tileTemplate: function (value) {
        ///	<summary>
        ///		Get or set template text to show for the file in tiles view.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    type: function () {
        ///	<summary>
        ///		Get type of the file in the upload pane.
        ///	</summary>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    verticalResolution: function () {
        ///	<summary>
        ///		Get image vertical image resolution of the file in the upload pane. Returns 0 for non-image file.
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
            element.setUploadFileAngle(this._index, fixValue(angle));
        } else {
            return element.getUploadFileAngle(this._index);
        }
    };
    obj.cropBounds = function (bounds) {
        // TODO: use more elegant way to detect uploader type
        var ax = (element.getAttribute('classid') + '').toLowerCase() ===
                ('clsid:' + this._uploader.activeXControl().classId()).toLowerCase();
        if (bounds !== undefined) {
            // accept null or false to reset crop
            if (!bounds) {
                bounds = [0, 0, 1000000, 1000000];
            }
            // accept two values to set crop from (0, 0) coordinates
            if (bounds.length === 2) {
                bounds = [0, 0].concat(bounds);
            }
            if (!bounds || bounds.length !== 4) {
                // invalid arguments
                showError('Invalid crop argument.');
                return;
            }
            if (ax) {
                element.setUploadFileCropBounds(this._index, js2axArray(bounds));
            } else {
                element.setUploadFileCropBounds(this._index, bounds);
            }

        } else {
            bounds = element.getUploadFileCropBounds(this._index);
            if (ax) {
                return ax2jsArray(bounds);
            } else {
                return java2jsArray(bounds);
            }
        }
    };

    obj.description = function (description) {
        if (description !== undefined) {
            element.setUploadFileDescription(this._index, fixValue(description));
        } else {
            return javaUtil.convertToJS(element.getUploadFileDescription(this._index));
        }
    };
    obj.focused = function () {
        return javaUtil.convertToJS(element.getUploadFileFocused(this._index));
    };
    obj.format = function () {
        return javaUtil.convertToJS(element.getUploadFileFormat(this._index));
    };
    obj.guid = function () {
        return javaUtil.convertToJS(element.getUploadFileGuid(this._index));
    };
    obj.height = function () {
        return javaUtil.convertToJS(element.getUploadFileHeight(this._index));
    };
    obj.horizontalResolution = function () {
        return javaUtil.convertToJS(element.getUploadFileHorizontalResolution(this._index));
    };
    obj.name = function () {
        return javaUtil.convertToJS(element.getUploadFileName(this._index));
    };
    obj.selected = function (selected) {
        if (selected !== undefined) {
            element.setUploadFileSelected(this._index, selected);
        } else {
            return javaUtil.convertToJS(element.getUploadFileSelected(this._index));
        }

    };
    obj.size = function () {
        return javaUtil.convertToJS(element.getUploadFileSize(this._index));
    };
    obj.tag = function (tag) {
        if (tag !== undefined) {
            element.setUploadFileTag(this._index, fixValue(tag));
        } else {
            return javaUtil.convertToJS(element.getUploadFileTag(this._index));
        }
    };
    obj.tileTemplate = function (value) {
        if (value !== undefined) {
            element.setUploadFileTileTemplate(this._index, value);
        } else {
            return javaUtil.convertToJS(element.getUploadFileTileTemplate(this._index));
        }
    };
    obj.type = function () {
        return javaUtil.convertToJS(element.getUploadFileType(this._index));
    };
    obj.verticalResolution = function () {
        return javaUtil.convertToJS(element.getUploadFileVerticalResolution(this._index));
    };
    obj.width = function () {
        return javaUtil.convertToJS(element.getUploadFileWidth(this._index));
    };

    obj.remove = function () {
        return element.UploadFileRemove(this._index);
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

    count: function () {
        ///	<summary>
        ///		Get count of files selected to upload.
        ///	</summary>
        ///	<returns type="Number" />

        // can't select files before uploader initialization
        return 0;
    },
    get: function (index) {
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
    totalFileSize: function () {
        ///	<summary>
        ///		Get total size of all files selected for upload.
        ///	</summary>
        ///	<returns type="Number" />
        return 0;
    },
    remove: function (index) {
        ///	<summary>
        ///		Remove file from upload list
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Index of files in the upload list
        ///	</param>
    },
    removeByGuid: function (guid) {
        ///	<summary>
        ///		Remove file from upload list by file guid
        ///	</summary>
        ///	<param name="guid" type="String">
        ///     File guid
        ///	</param>
    }
};

AU.files.init = propertyMaker._typicalInit;

// change object to work with rendered control
// for internal use
AU.files.reinit = function (obj) {
    showInfo("[js_info] Reinit files API.");

    var element = obj._uploader.getElement();

    obj.count = function () {
        return javaUtil.convertToJS(element.getUploadFileCount());
    };

    obj.totalFileSize = function () {
        return javaUtil.convertToJS(element.getTotalFileSize());
    };

    obj.get = function (index) {
        if (index < this.count()) {
            if (this._items && this._items[index]) {
                return this._items[index];
            } else {
                this._items = this._items || [];
                //create object to provide methods to work with file
                var f = new AU.file();
                f._uploader = this._uploader;
                f._index = index;
                AU.file.reinit(element, f);
                this._items[index] = f;
                return f;
            }
        } else {
            return null;
        }
    };

    obj.remove = function (index) {
        return element.UploadFileRemove(index);
    };

    obj.removeByGuid = function (guid) {
        return element.UploadFileRemoveByGuid(guid);
    };
};
AU.imageEditor = function () {
    ///	<summary>
    ///		Get or set image editor properties
    ///	</summary>
    ///	<param name="imageEditor" type="Object">
    ///		An object with restrictions parameters
    ///	</param>
    ///	<returns type="$au.imageEditor" />
}

AU.imageEditor.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cancelButtonImageFormat", getter: 1, setter: 1, render: "ImageEditorCancelButtonImageFormat" },
        { name: "cancelButtonText", getter: 1, setter: 1, render: "ImageEditorCancelButtonText" },
        { name: "cancelCropButtonImageFormat", getter: 1, setter: 1, render: "ImageEditorCancelCropButtonImageFormat" },
        { name: "cancelCropButtonText", getter: 1, setter: 1, render: "ImageEditorCancelCropButtonText" },
        { name: "cropButtonImageFormat", getter: 1, setter: 1, render: "ImageEditorCropButtonImageFormat" },
        { name: "cropButtonText", getter: 1, setter: 1, render: "ImageEditorCropButtonText" },
        { name: "cropMinSize", getter: 1, setter: 1, render: "ImageEditorCropMinSize" },
        { name: "cropRatio", getter: 1, setter: 1, render: "ImageEditorCropRatio" },
        { name: "descriptionHintText", getter: 1, setter: 1, render: "ImageEditorDescriptionHintText" },
        { name: "enableCrop", getter: 1, setter: 1, render: "ImageEditorEnableCrop" },
        { name: "rotateButtonImageFormat", getter: 1, setter: 1, render: "ImageEditorRotateButtonImageFormat" },
        { name: "rotateButtonText", getter: 1, setter: 1, render: "ImageEditorRotateButtonText" },
        { name: "saveButtonImageFormat", getter: 1, setter: 1, render: "ImageEditorSaveButtonImageFormat" },
        { name: "saveButtonText", getter: 1, setter: 1, render: "ImageEditorSaveButtonText" }
    ],
    _methods: [
        { name: "show", controlMethodName: "ShowImageEditor" }
    ],
    getParams: getParams,

    cancelButtonImageFormat: function (value) {
        ///	<summary>
        ///		Cancel button image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    cancelButtonText: function (value) {
        ///	<summary>
        ///		Cancel button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    cancelCropButtonImageFormat: function (value) {
        ///	<summary>
        ///		Cancel Crop button image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    cancelCropButtonText: function (value) {
        ///	<summary>
        ///		Cancel Crop button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    cropButtonImageFormat: function (value) {
        ///	<summary>
        ///		Crop button image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    cropButtonText: function (value) {
        ///	<summary>
        ///		Crop button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    cropMinSize: function (value) {
        ///	<summary>
        ///		Minimum crop size
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
    },
    cropRatio: function (value) {
        ///	<summary>
        ///		Crop ratio
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
    },
    descriptionHintText: function (value) {
        ///	<summary>
        ///		Text watermark in the description textbox
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    enableCrop: function (value) {
        ///	<summary>
        ///		Enable or disable crop
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    rotateButtonImageFormat: function (value) {
        ///	<summary>
        ///		Rotate button image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    rotateButtonText: function (value) {
        ///	<summary>
        ///		Rotate button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    saveButtonImageFormat: function (value) {
        ///	<summary>
        ///		Save button image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    saveButtonText: function (value) {
        ///	<summary>
        ///		Save button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    show: function (index) {
        ///	<summary>
        ///		Open image editor dialog.
        ///	</summary>
        ///	<param name="index" type="Number">
        ///     Index of the image file in the upload pane.
        ///	</param>
        /* will be created while initialization */
    }
}

AU.imageEditor.init = propertyMaker._typicalInit;
AU.imageEditor.reinit = propertyMaker._typicalReinit;

AU.imageEditor.prototype.constructor = AU.imageEditor;
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
        { name: "closeIconImageFormat", getter: 1, setter: 1, render: "InformationBarCloseIconImageFormat" },
        { name: "errorColor", getter: 1, setter: 1, render: "InformationBarErrorColor" },
        { name: "messageColor", getter: 1, setter: 1, render: "InformationBarMessageColor" },
        { name: "warningColor", getter: 1, setter: 1, render: "InformationBarWarningColor" }
    ],
    _methods: [
        { name: "show", controlMethodName: "ShowInformationBar" }
    ],
    getParams: getParams,

    closeIconImageFormat: function (value) {
        ///	<summary>
        ///		Close information bar icon image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    errorColor: function (value) {
        ///	<summary>
        ///		Color of the information bar with error messages
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    messageColor: function (value) {
        ///	<summary>
        ///		Color of the information bar with notification messages
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    warningColor: function (value) {
        ///	<summary>
        ///		Color of the information bar with warning messages
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    show: function (text, type) {
        ///	<summary>
        ///		Show message on the information bar.
        ///	</summary>
        ///	<param name="text" type="String">
        ///     Message text
        /// </param>
        ///	<param name="type" type="String">
        ///     "Error" or "Message"
        /// </param>
    }
}

AU.informationBar.init = propertyMaker._typicalInit;
AU.informationBar.reinit = propertyMaker._typicalReinit;

AU.informationBar.prototype.constructor = AU.informationBar;
AU.javaControl = function (uploader) {
    this._uploader = uploader;
    this.codeBase('Scripts/ImageUploader7.jar');
    this.cached(true);
};

AU.javaControl.prototype = {
    __class: true,

    getJarFileName: function () {
        var codeBase = this.codeBase();
        var parts = codeBase.split('/');
        if (parts.length > 0) {
            return parts[parts.length - 1];
        } else {
            showError('Incorrect codebase string: "' + codeBase + '"');
            return codeBase;
        }
    },

    isJavaSupported: function () {
        if (this.getJREInstalled() >= 0) {
            return true;
        } else {
            return false;
        }
    },
    // -2 - Java not installed or not enabled
    // -1 - Installed Java version is less then required
    //  0 - Unable determine java
    //  1 - Required java version available
    getJREInstalled: function () {
        var result = -2;
        if (navigator.javaEnabled()) {
            var ver = AU.javaDetector.getJREs();
            if (ver == null) {
                //we can't get information about java in this browser
                result = 0;
            } else if (ver.length > 0) {
                //java installed and we have its version
                result = -1;
                for (var i = 0, maxi = ver.length; i < maxi; i++) {
                    if (AU.javaDetector.compareVersions(ver[i], this.javaVersionRequired)) {
                        result = 1;
                        break;
                    }
                }
            }
        }
        return result;
    },

    javaVersionRequired: [1, 5, 0, 0],

    actualVersion: function () {
        var el = this._uploader.getElement();
        if (el) {
            return el.getVersion();
        } else {
            return null;
        }
    },

    codeBase: function (value) {
        ///	<summary>
        ///		Get or set URL to JAR file
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        return helpers.prop(this, '_codeBase', arguments.length, value);
    },

    className: function (value) {
        ///	<summary>
        ///		Get or set uploader java class name.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        return helpers.prop(this, '_className', arguments.length, value);
    },

    cached: function (value) {
        ///	<summary>
        ///		Enable or disable cache java applet.
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        return helpers.prop(this, '_cached', arguments.length, value);
    },

    version: function (value) {
        ///	<summary>
        ///		Gets or sets minimum required version of the Java applet (ImageUploader7.jar file).
        ///	</summary>
        ///	<param name="value" type="String">
        ///     Version string in the x.x.x.x format
        ///	</param>
        ///	<returns type="String" />
        return helpers.prop(this, '_version', arguments.length, value);
    }
};
AU.messages = function () {
    ///	<summary>
    ///		Get status panel
    ///	</summary>
    ///	<returns type="$au.messages" />
}

AU.messages.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cmykImagesNotAllowed", getter: 1, setter: 1, render: "CmykImagesNotAllowedMessage" },
        { name: "deletingFilesError", getter: 1, setter: 1, render: "DeletingFilesErrorMessage" },
        { name: "dimensionsTooLarge", getter: 1, setter: 1, render: "DimensionsTooLargeMessage" },
        { name: "dimensionsTooSmall", getter: 1, setter: 1, render: "DimensionsTooSmallMessage" },
        { name: "fileNameNotAllowed", getter: 1, setter: 1, render: "FileNameNotAllowedMessage" },
        { name: "fileSizeTooSmall", getter: 1, setter: 1, render: "FileSizeTooSmallMessage" },
        { name: "filesNotAdded", getter: 1, setter: 1, render: "FilesNotAddedMessage" },
        { name: "maxFileCountExceeded", getter: 1, setter: 1, render: "MaxFileCountExceededMessage" },
        { name: "maxFileSizeExceeded", getter: 1, setter: 1, render: "MaxFileSizeExceededMessage" },
        { name: "maxTotalFileSizeExceeded", getter: 1, setter: 1, render: "MaxTotalFileSizeExceededMessage" },
        { name: "noResponseFromServer", getter: 1, setter: 1, render: "NoResponseFromServerMessage" },
        { name: "serverError", getter: 1, setter: 1, render: "ServerErrorMessage" },
        { name: "serverNotFound", getter: 1, setter: 1, render: "ServerNotFoundMessage" },
        { name: "unexpectedError", getter: 1, setter: 1, render: "UnexpectedErrorMessage" },
        { name: "uploadCancelled", getter: 1, setter: 1, render: "UploadCancelledMessage" },
        { name: "uploadCompleted", getter: 1, setter: 1, render: "UploadCompletedMessage" },
        { name: "uploadFailed", getter: 1, setter: 1, render: "UploadFailedMessage" }
    ],
    getParams: getParams,

    cmykImagesNotAllowed: function (value) {
        ///	<summary>
        ///		CMYK denied message text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    deletingFilesError: function (value) {
        ///	<summary>
        ///		Deleting files error message text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    dimensionsTooLarge: function (value) {
        ///	<summary>
        ///		Get or set text of the dimensions too large message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    dimensionsTooSmall: function (value) {
        ///	<summary>
        ///		Get or set text of the dimensions too small message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    fileNameNotAllowed: function (value) {
        ///	<summary>
        ///		Get or set text of the file name not allowed message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    fileSizeTooSmall: function (value) {
        ///	<summary>
        ///		Get or set text of the file size too small message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    filesNotAdded: function (value) {
        ///	<summary>
        ///		Get or set text of the message, showing after adding files
        ///     if several files were not added due restrictions.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    maxFileCountExceeded: function (value) {
        ///	<summary>
        ///		Get or set text of the maximum file count exceeded message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    maxFileSizeExceeded: function (value) {
        ///	<summary>
        ///		Get or set text of the maximum file size exceeded message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    maxTotalFileSizeExceeded: function (value) {
        ///	<summary>
        ///		Get or set text of the maximum total file size exceeded message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    noResponseFromServer: function (value) {
        ///	<summary>
        ///		Get or set text of the no response from server message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    serverError: function (value) {
        ///	<summary>
        ///		Get or set text of the server side error message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    serverNotFound: function (value) {
        ///	<summary>
        ///		Get or set text of the server not found message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    unexpectedError: function (value) {
        ///	<summary>
        ///		Get or set text of the unexpected error message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    uploadCancelled: function (value) {
        ///	<summary>
        ///		Get or set text of the upload cancelled message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    uploadCompleted: function (value) {
        ///	<summary>
        ///		Get or set text of the upload completed message.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    uploadFailed: function (value) {
        ///	<summary>
        ///		Get or set text of the upload failed message.
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
        { name: "cookie", getter: 1, setter: 1 },
        { name: "exif", getter: 1, setter: 1, render: "ExtractExif" },
        { name: "hash", getter: 1, setter: 1, render: "HashAlgorithm" },
        { name: "iptc", getter: 1, setter: 1, render: "ExtractIptc" },
        { name: "userAgent", getter: 1, setter: 1, defaultValue: navigator.userAgent },
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
    enableStandardField: function (name, enable) {
        ///	<summary>
        ///		Enable or diable standard field. If field disabled it is not sended to server.
        ///	</summary>
        ///	<param name="name" type="String">
        ///		Standard field name
        ///	</param>
        ///	<param name="enable" type="Boolean">
        ///	</param>
        this._standardFields = this._standardFields || [];
        if (name) {
            this._standardFields.push({ action: "enable", name: name, enable: enable });
        }
    },
    renameStandardField: function (oldName, newName) {
        ///	<summary>
        ///		Rename standard field.
        ///	</summary>
        ///	<param name="oldName" type="String">
        ///		Original standard field name.
        ///	</param>
        ///	<param name="newName" type="String">
        ///     New standard field name.
        ///	</param>
        this._standardFields = this._standardFields || [];
        if (oldName && newName) {
            this._standardFields.push({ action: "rename", oldName: oldName, newName: newName });
        }
    },
    enableAllStandardFields: function (enable) {
        ///	<summary>
        ///		Enable or disable all standard fields. Disabled fields are not sended to server.
        ///	</summary>
        ///	<param name="enable" type="Boolean">
        ///	</param>
        this._standardFields = this._standardFields || [];
        this._standardFields.push({ action: "enableAll", enable: enable });
    },
    addCookie: function (value) {
        ///	<summary>
        ///		Add cookie.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     Cookie string
        /// </param>
        AU.debug().showMessage('Call to obsolete method "uploader.metadata.addCookie". Use "uploader.metadata.cookie" instead.', 2);
        this.cookie(value);
    },
    resetCookie: function () {
        ///	<summary>
        ///		Reset cookie
        ///	</summary>
        AU.debug().showMessage('Call to obsolete method "uploader.metadata.resetCookie". Use "uploader.metadata.cookie" with empty string parameter instead.', 2);
        this.cookie('');
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

    cookie: function (value) {
        ///	<summary>
        ///		Get or set cookies string.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    exif: function (value) {
        ///	<summary>
        ///		Get or set EXIF values to extract and upload along with files.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		EXIF field names separated with a semicolon.
        ///	</param>
        ///	<returns type="String" />
    },
    hash: function (value) {
        ///	<summary>
        ///		Get or set algorithm to generate a hash value for each original file selected for upload.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		Algorithm name. The "SHA", "MD2", "MD5" algorithms are supported.
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
    userAgent: function (value) {
        ///	<summary>
        ///		Get or set user agent string for uploader requests.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		User agent string
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
    showInfo("[js_info] Reinit metadata.");

    var element = obj._uploader.getElement();

    obj.addCustomField = function (name, value, add) {
        element.AddCustomField(name, value, !!add);
    };

    obj.removeCustomField = function (name) {
        if (name != null) {
            element.RemoveCustomField(name);
        }
    };

    obj.enableStandardField = function (name, enable) {
        element.EnableStandardField(name, enable);
    };

    obj.renameStandardField = function (oldName, newName) {
        element.RenameStandardField(oldName, newName);
    };

    obj.enableAllStandardFields = function (enable) {
        element.EnableAllStandardFields(enable);
    }

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

    // Enable, disable, rename standard fields
    if (obj._standardFields && obj._standardFields.length > 0) {
        for (var i = 0, max = obj._standardFields.length; i < max; i++) {
            var f = obj._standardFields[i];
            switch (f.action) {
                case 'enable':
                    obj.enableStandardField(f.name, f.enable);
                    break;
                case 'rename':
                    obj.renameStandardField(f.oldName, f.newName);
                    break;
                case 'enableAll':
                    obj.enableAllStandardFields(f.enable);
                    break;
            }
        }
    }

    propertyMaker._typicalReinit(obj);
}
AU.folderPane = function () {
    ///	<summary>
    ///		Get or set folder pane properties
    ///	</summary>
    ///	<param name="folderPane" type="Object">
    ///		An object with tree pane parameters
    ///	</param>
    ///	<returns type="$au.folderPane" />
}

AU.folderPane.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "filterHintText", getter: 1, setter: 1, render: "FolderPaneFilterHintText" },
        { name: "filterClearIconImageFormat", getter: 1, setter: 1 },
        { name: 'headerText', getter: 1, setter: 1, render: "FolderPaneHeaderText" },
        { name: "height", getter: 1, setter: 1, render: "FolderPaneHeight" },
        { name: "previewSize", getter: 1, setter: 1, render: "FolderPanePreviewSize" },
        { name: "showAllowedItemsOnly", getter: 1, setter: 1 },
        { name: "sortMode", getter: 1, setter: 1, render: "FolderPaneSortMode" },
        { name: "tileHeight", getter: 1, setter: 1, render: "FolderPaneTileHeight" },
        { name: "tilePreviewSize", getter: 1, setter: 1, render: "FolderPaneTilePreviewSize" },
        { name: "tileTemplate", getter: 1, setter: 1, render: "FolderPaneTileTemplate" },
        { name: "tileWidth", getter: 1, setter: 1, render: "FolderPaneTileWidth" },
        { name: "viewMode", getter: 1, setter: 1, render: "FolderPaneViewMode" }
    ],
    _methods: [
        { name: "canGoToFolder", controlMethodName: "CanGoToFolder" },
        { name: "deselectAll", controlMethodName: "DeselectAll" },
        { name: "goToFolder", controlMethodName: "GoToFolder" },
        { name: "goToParentFolder", controlMethodName: "GoToParentFolder" },
        { name: "goToPreviousFolder", controlMethodName: "GoToPreviousFolder" },
        { name: "selectAll", controlMethodName: "SelectAll" }
    ],
    getParams: getParams,

    filterHintText: function (value) {
        ///	<summary>
        ///		"Search" textbox watermark text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    filterClearIconImageFormat: function (value) {
        ///	<summary>
        ///		Clear filter icon image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    headerText: function (value) {
        ///	<summary>
        ///		Header text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    height: function (value) {
        ///	<summary>
        ///		Gets or sets height of the folder pane.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    previewSize: function (value) {
        ///	<summary>
        ///		Thumbnail preview size
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    showAllowedItemsOnly: function (value) {
        ///	<summary>
        ///		Enable or disable display of the files which can not be select to upload.
        ///	</summary>
        ///	<param name="value" type="Boolean">
        ///	</param>
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    sortMode: function (value) {
        ///	<summary>
        ///		Folder pane sort mode.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the values "Name", "Size", "Type", "Modified", "Path"
        ///     or their equivalent number.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    tileHeight: function (value) {
        ///	<summary>
        ///		Folder pane tile height.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    tilePreviewSize: function (value) {
        ///	<summary>
        ///		Folder pane tile preview size.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    tileTemplate: function (value) {
        ///	<summary>
        ///		Folder pane tile template text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    tileWidth: function (value) {
        ///	<summary>
        ///		Folder pane tile width.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    viewMode: function (value) {
        ///	<summary>
        ///		Folder pane view.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the values "Thumbnails", "Icons", "List", "Details"
        ///     or their equivalent number.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    canGoToFolder: function (folder) {
        ///	<summary>
        ///		Verifies whether the specified special folder is available.
        ///	</summary>
        ///	<param name="folder" type="String">
        ///     One of the following values: "BitBucket", "Desktop", "MyComputer", "MyDocuments", "MyMusic",
        ///     "MyPictures", "MyVideo", "Network", "Recent".
        ///	</param>
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    deselectAll: function () {
        ///	<summary>
        ///		Deselect all files in the folder pane.
        ///	</summary>
        /* will be created while initialization */
    },
    goToFolder: function (path) {
        ///	<summary>
        ///		Navigates to a specified folder.
        ///	</summary>
        ///	<param name="path" type="String">
        ///     Path to the folder
        ///	</param>
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    goToParentFolder: function () {
        ///	<summary>
        ///		Navigates to the parent folder.
        ///	</summary>
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    goToPreviousFolder: function () {
        ///	<summary>
        ///		Navigates to the previous visited folder.
        ///	</summary>
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    selectAll: function () {
        ///	<summary>
        ///		Select all files in the upload pane.
        ///	</summary>
        /* will be created while initialization */
    }
}

AU.folderPane.init = propertyMaker._typicalInit;
AU.folderPane.reinit = propertyMaker._typicalReinit;
AU.qualityMeter = function(json) {
    ///	<summary>
    ///		Get or set Quality Meter properties.
    ///	</summary>
    ///	<param name="json" type="Object">
    ///		An object with quality meter properties
    ///	</param>
    ///	<returns type="$au.qualityMeter" />
}

AU.qualityMeter.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "acceptableQualityColor", getter: 1, setter: 1, render: "QualityMeterAcceptableQualityColor" },
        { name: "backgroundColor", getter: 1, setter: 1, render: "QualityMeterBackgroundColor" },
        { name: "borderColor", getter: 1, setter: 1, render: "QualityMeterBorderColor" },
        { name: "formats", getter: 1, setter: 1, render: "QualityMeterFormats" },
        { name: "height", getter: 1, setter: 1, render: "QualityMeterHeight" },
        { name: "highQualityColor", getter: 1, setter: 1, render: "QualityMeterHighQualityColor" },
        { name: "lowQualityColor", getter: 1, setter: 1, render: "QualityMeterLowQualityColor" }
    ],
    getParams: getParams,

    acceptableQualityColor: function (value) {
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    backgroundColor: function (value) {
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    borderColor: function (value) {
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    formats: function (value) {
        ///	<summary>
        ///		A value which defines print formats for the quality meter.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     The string in the following format:
        ///     "Format1 name,format1 width (pixels),format1 height (pixels),format1 acceptable ratio;Format2 name,format2 width (pixels),format2 height (pixels),format2 acceptable ratio;..."
        ///	</param>
        ///	<returns type="String" />
    },
    height: function (value) {
        ///	<summary>
        ///		Height of the quality meter bar.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
    },
    highQualityColor: function (value) {
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    lowQualityColor: function (value) {
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
}

AU.qualityMeter.init = propertyMaker._typicalInit;
AU.qualityMeter.reinit = propertyMaker._typicalReinit;
AU.paneItem = function() {
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
        { name: "checkedItemBorderColor", getter: 1, setter: 1 },
        { name: "checkedItemColor", getter: 1, setter: 1 },
        { name: "checkedItemTextColor", getter: 1, setter: 1 },
        { name: "descriptionAddedIconImageFormat", getter: 1, setter: 1 },
        { name: "descriptionEditorIconImageFormat", getter: 1, setter: 1 },
        { name: "descriptionEditorIconTooltip", getter: 1, setter: 1 },
        { name: "enableDisproportionalExifThumbnails", getter: 1, setter: 1 },
        { name: "enableFileNameTooltip", getter: 1, setter: 1 },
        { name: "hoverBorderColor", getter: 1, setter: 1 },
        { name: "hoverColor", getter: 1, setter: 1 },
        { name: "imageCroppedIconImageFormat", getter: 1, setter: 1 },
        { name: "imageCroppedIconTooltip", getter: 1, setter: 1 },
        { name: "imageEditorIconImageFormat", getter: 1, setter: 1 },
        { name: "imageEditorIconTooltip", getter: 1, setter: 1 },
        { name: "inactiveCheckedItemBorderColor", getter: 1, setter: 1 },
        { name: "inactiveCheckedItemColor", getter: 1, setter: 1 },
        { name: "inactiveSelectionBorderColor", getter: 1, setter: 1 },
        { name: "inactiveSelectionColor", getter: 1, setter: 1 },
        { name: "previewQuality", getter: 1, setter: 1 },
        { name: "removalIconImageFormat", getter: 1, setter: 1 },
        { name: "removalIconTooltip", getter: 1, setter: 1 },
        { name: "rotationIconImageFormat", getter: 1, setter: 1 },
        { name: "rotationIconTooltip", getter: 1, setter: 1 },
        { name: "selectedTextColor", getter: 1, setter: 1 },
        { name: "selectionBorderColor", getter: 1, setter: 1 },
        { name: "selectionColor", getter: 1, setter: 1 },
        { name: "selectionHoverBorderColor", getter: 1, setter: 1 },
        { name: "selectionHoverColor", getter: 1, setter: 1 },
        { name: "showFileNameInThumbnailsView", getter: 1, setter: 1 },
        { name: "uploadIndicatorBorderColor", getter: 1, setter: 1 },
        { name: "uploadIndicatorColor", getter: 1, setter: 1 }
    ],
    _objectProperties: [
        { name: "qualityMeter", type: AU.qualityMeter }
    ],
    qualityMeter: AU.qualityMeter,
    getParams: getParams,

    checkedItemBorderColor: function (value) {
        /// <summary>
        ///     Get or set color of the pane item border in checked state.
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    checkedItemColor: function (value) {
        /// <summary>
        ///     Get or set color of the pane item in checked state.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    checkedItemTextColor: function (value) {
        /// <summary>
        ///     Get or set color of the text on the selected pane item when item is checked.
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    descriptionAddedIconImageFormat: function (value) {
        /// <summary>
        ///     Description icon image format
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    descriptionEditorIconImageFormat: function (value) {
        /// <summary>
        ///     Description editor icon image format
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
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
    enableFileNameTooltip: function (value) {
        /// <summary>
        ///     Enable file name tooltip
        /// </summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    hoverBorderColor: function (value) {
        ///	<summary>
        ///		Get or set color of the pane item border in highlighted state.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    hoverColor: function (value) {
        ///	<summary>
        ///		Get or set color of the pane item in highlighted state.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    imageCroppedIconImageFormat: function (value) {
        /// <summary>
        ///     Image format of the icon, showing when image cropped
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    imageCroppedIconTooltip: function (value) {
        /// <summary>
        ///     Tooltip of the icon, showed when image cropped
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    imageEditorIconImageFormat: function (value) {
        /// <summary>
        ///     Image editor icon image format
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    imageEditorIconTooltip: function (value) {
        /// <summary>
        ///     Image editor icon tooltip
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    inactiveCheckedItemBorderColor: function (value) {
        /// <summary>
        ///     Get or set color of the checked pane item border in inactive state.
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    inactiveCheckedItemColor: function (value) {
        /// <summary>
        ///     Get or set color of the checked pane item in inactive state.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    inactiveSelectionBorderColor: function (value) {
        ///	<summary>
        ///		Get or set color of the selected pane item border in inactive state.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    inactiveSelectionColor: function (value) {
        ///	<summary>
        ///		Get or set color of the selected pane item in inactive state.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    previewQuality: function (value) {
        /// <summary>
        ///     Preview quality
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    selectedTextColor: function (value) {
        ///	<summary>
        ///		Get or set color of the text on the selected pane item when item is focused.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    selectionBorderColor: function (value) {
        ///	<summary>
        ///		Get or set color of the pane item border when this item selected and focused.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    selectionColor: function (value) {
        ///	<summary>
        ///		Get or set color of the pane item when this item selected and focused.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    selectionHoverBorderColor: function (value) {
        ///	<summary>
        ///		Get or set color of the pane item border when this item selected and mouse pointer is hovered over it.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    selectionHoverColor: function (value) {
        ///	<summary>
        ///		Get or set color of the pane item when this item selected and mouse pointer is hovered over it.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    removalIconImageFormat: function (value) {
        /// <summary>
        ///     Removal icon image format
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    removalIconTooltip: function (value) {
        /// <summary>
        ///     Removal icon tooltip
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    rotationIconImageFormat: function (value) {
        /// <summary>
        ///     Rotation icon image format
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
    showFileNameInThumbnailsView: function (value) {
        /// <summary>
        ///     Show file name in thumbnails view
        /// </summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    uploadIndicatorBorderColor: function (value) {
        /// <summary>
        ///     Border color of uploaded items.
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    uploadIndicatorColor: function (value) {
        /// <summary>
        ///     Color of uploaded items.
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
}

AU.paneItem.init = propertyMaker._typicalInit;
AU.paneItem.reinit = propertyMaker._typicalReinit;
AU.restrictions = function() {
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
            { name: "deniedFileMask", getter: 1, setter: 1 },
            { name: "enableCmyk", getter: 1, setter: 1 },
            { name: "fileMask", getter: 1, setter: 1 },
            { name: "maxFileCount", getter: 1, setter: 1 },
            { name: "maxFileSize", getter: 1, setter: 1 },
            { name: "maxImageHeight", getter: 1, setter: 1 },
            { name: "maxImageWidth", getter: 1, setter: 1 },
            { name: "maxTotalFileSize", getter: 1, setter: 1 },
            { name: "minFileCount", getter: 1, setter: 1 },
            { name: "minFileSize", getter: 1, setter: 1 },
            { name: "minImageHeight", getter: 1, setter: 1 },
            { name: "minImageWidth", getter: 1, setter: 1 }
        ],
    deniedFileMask: function (value) {
        ///	<summary>
        ///		Get or set DeniedFileMask property
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    enableCmyk: function (value) {
        ///	<summary>
        ///		Enable or disable CMYK images to upload.
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
        /* will be created while initialization */
    },
    fileMask: function (mask) {
        ///	<summary>
        ///		Get or set FileMask property
        ///	</summary>
        ///	<param name="mask" type="String" />
        ///	<returns type="String" />
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
    minFileSize: function() {
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
    getParams: getParams
};

AU.restrictions.init = propertyMaker._typicalInit;
AU.restrictions.reinit = propertyMaker._typicalReinit;
AU.statusPane = function () {
    ///	<summary>
    ///		Get or set status pane properties
    ///	</summary>
    ///	<param name="statusPane" type="Object">
    ///		An object with status pane parameters
    ///	</param>
    ///	<returns type="$au.statusPane" />
}

AU.statusPane.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "clearAllHyperlinkText", getter: 1, setter: 1, render: "StatusPaneClearAllHyperlinkText" },
        { name: "color", getter: 1, setter: 1, render: "StatusPaneColor" },
        { name: "filesToUploadText", getter: 1, setter: 1, render: "StatusPaneFilesToUploadText" },
        { name: "noFilesToUploadText", getter: 1, setter: 1, render: "StatusPaneNoFilesToUploadText" },
        { name: "progressBarText", getter: 1, setter: 1, render: "StatusPaneProgressBarText" }
    ],
    getParams: getParams,

    clearAllHyperlinkText: function (value) {
        /// <summary>
        ///     Get or set text of the "Clear all" hyperlink
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    color: function (value) {
        ///	<summary>
        ///		Get or set status pane color.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    filesToUploadText: function (value) {
        /// <summary>
        ///     Get or set "Selected N file(s) to upload" text
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    noFilesToUploadText: function (value) {
        /// <summary>
        ///     Get or set "No files to upload" text
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    progressBarText: function (value) {
        /// <summary>
        ///     Get or set text on the progress bar on status pane
        /// </summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    }
}

AU.statusPane.init = propertyMaker._typicalInit;
AU.statusPane.reinit = propertyMaker._typicalReinit;

AU.statusPane.prototype.constructor = AU.statusPane;
AU.treePane = function () {
    ///	<summary>
    ///		Get or set tree pane properties
    ///	</summary>
    ///	<param name="treePane" type="Object">
    ///		An object with tree pane parameters
    ///	</param>
    ///	<returns type="$au.treePane" />
}

AU.treePane.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "titleText", getter: 1, setter: 1, render: "TreePaneTitleText" },
        { name: "unixFileSystemRootText", getter: 1, setter: 1 },
        { name: "unixHomeDirectoryText", getter: 1, setter: 1 },
        { name: "width", getter: 1, setter: 1, render: "TreePaneWidth" }
    ],
    getParams: getParams,

    titleText: function (value) {
        ///	<summary>
        ///		Gets or sets tree pane title text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    unixFileSystemRootText: function (value) {
        ///	<summary>
        ///		Gets or sets caption of the folder tree node which
        ///     specifies the system directory at *NIX systems. .
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    unixHomeDirectoryText: function (value) {
        ///	<summary>
        ///		Gets or sets caption of the folder tree node which specifies the home directory at *NIX systems.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    width: function (value) {
        ///	<summary>
        ///		Get or set width of the tree pane.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///     Width
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    }
}

AU.treePane.init = propertyMaker._typicalInit;
AU.treePane.reinit = propertyMaker._typicalReinit;
AU.uploadPane = function () {
    ///	<summary>
    ///		Get or set upload pane properties
    ///	</summary>
    ///	<param name="uploadPane" type="Object">
    ///		An object with upload pane parameters
    ///	</param>
    ///	<returns type="$au.uploadPane" />
}

AU.uploadPane.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "dropFilesHereImageFormat", getter: 1, setter: 1 },
        { name: "dropFilesHereText", getter: 1, setter: 1 },
        { name: "previewSize", getter: 1, setter: 1, render: "UploadPanePreviewSize" },
        { name: "sortMode", getter: 1, setter: 1, render: "UploadPaneSortMode" },
        { name: "tileHeight", getter: 1, setter: 1, render: "UploadPaneTileHeight" },
        { name: "tilePreviewSize", getter: 1, setter: 1, render: "UploadPaneTilePreviewSize" },
        { name: "tileTemplate", getter: 1, setter: 1, render: "UploadPaneTileTemplate" },
        { name: "tileWidth", getter: 1, setter: 1, render: "UploadPaneTileWidth" },
        { name: "viewMode", getter: 1, setter: 1, render: "UploadPaneViewMode" }
    ],
    _methods: [
        { name: "addAllFiles", controlMethodName: "AddAllFiles" }, // private label only
        {name: "addFiles", controlMethodName: "AddFiles" },
        { name: "addFileByPath", controlMethodName: "AddFileByPath" }, // private label only
        {name: "addFolders", controlMethodName: "AddFolders" },
        { name: "addFolderByPath", controlMethodName: "AddFolderByPath" }, // private label only
        {name: "addSelectedFiles", controlMethodName: "AddSelectedFiles" }, // private label only
        {name: "loadUploadList", controlMethodName: "LoadUploadList" },
        { name: "removeAllFiles", controlMethodName: "RemoveAllFiles" },
        { name: "removeSelectedFiles", controlMethodName: "RemoveSelectedFiles" },
        { name: "resetUploadList", controlMethodName: "ResetUploadList" },
        { name: "saveUploadList", controlMethodName: "SaveUploadList" }
    ],
    getParams: getParams,

    dropFilesHereImageFormat: function (value) {
        ///	<summary>
        ///		Get or set image format for "Drop file here" label.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    dropFilesHereText: function (value) {
        ///	<summary>
        ///		Get or set "Drop files here" label text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    previewSize: function (value) {
        ///	<summary>
        ///		Size of the image previews in the upload pane
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    sortMode: function (value) {
        ///	<summary>
        ///		Get or set upload pane sort mode.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the values "Name", "Size", "Type", "Modified", "Path"
        ///     or their equivalent number.
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    tileHeight: function (value) {
        ///	<summary>
        ///		Folder pane tile height.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    tilePreviewSize: function (value) {
        ///	<summary>
        ///		Folder pane tile preview size.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    tileTemplate: function (value) {
        ///	<summary>
        ///		Folder pane tile template text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    tileWidth: function (value) {
        ///	<summary>
        ///		Folder pane tile width.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    viewMode: function (value) {
        ///	<summary>
        ///		Get or set folder pane view mode.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     One of the values "Thumbnails", "Icons", "List", "Details"
        ///     or their equivalent number.
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    // addAllFiles is not available in standard Image Uploader version due the security reasons
    addFiles: function () {
        ///	<summary>
        ///		Show Open File Dialog to add files to upload list
        ///	</summary>
        /* will be created while initialization */
    },
    // addFileByPath is not available in standard Image Uploader version due the security reasons
    addFolders: function () {
        ///	<summary>
        ///		Show Browse Folder Dialog and add all files from the selected folder to upload pane
        ///	</summary>
        /* will be created while initialization */
    },
    // addFolderByPath is not available in standard Image Uploader version due the security reasons.
    // addSelectedFiles is not available in standard Image Uploader version due the security reasons.
    loadUploadList: function (id) {
        ///	<summary>
        ///		Loads the upload list.
        ///     Before load the list you need to save it with saveUploadList method.
        ///	</summary>
        ///	<param name="id" type="Number">
        ///     Integer value from 1 to 50.
        ///	</param>
        /* will be created while initialization */
    },
    removeAllFiles: function () {
        ///	<summary>
        ///		Remove all files from upload list.
        ///	</summary>
        /* will be created while initialization */
    },
    removeSelectedFiles: function () {
        ///	<summary>
        ///		Remove selected files from upload list.
        ///	</summary>
        /* will be created while initialization */
    },
    resetUploadList: function (id) {
        ///	<summary>
        ///		Erase saved upload list.
        ///	</summary>
        ///	<param name="id" type="Number">
        ///     An id of the saved upload list. Integer value from 1 to 50.
        ///	</param>
        /* will be created while initialization */
    },
    saveUploadList: function (id) {
        ///	<summary>
        ///		Save upload list.
        ///	</summary>
        ///	<param name="id" type="Number">
        ///     An id of the saved upload list. Integer value from 1 to 50.
        ///	</param>
        /* will be created while initialization */
    }
}

AU.uploadPane.init = propertyMaker._typicalInit;
AU.uploadPane.reinit = propertyMaker._typicalReinit;
AU.uploadProgressDialog = function(properties) {
    ///	<summary>
    ///		Get or set upload progress dialog properties
    ///	</summary>
    ///	<param name="properties" type="Object">
    ///		An object with dialog properties
    ///	</param>
    ///	<returns type="$au.uploadProgressDialog" />
}

AU.uploadProgressDialog.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "cancelUploadButtonText", getter: 1, setter: 1, render: "UploadProgressDialogCancelUploadButtonText" },
        { name: "estimationText", getter: 1, setter: 1, render: "UploadProgressDialogEstimationText" },
        { name: "hideButtonText", getter: 1, setter: 1, render: "UploadProgressDialogHideButtonText" },
        { name: "hoursText", getter: 1, setter: 1 },
        { name: "infoText", getter: 1, setter: 1, render: "UploadProgressDialogInfoText" },
        { name: "kilobytesText", getter: 1, setter: 1 },
        { name: "megabytesText", getter: 1, setter: 1 },
        { name: "minutesText", getter: 1, setter: 1 },
        { name: "preparingText", getter: 1, setter: 1, render: 'UploadProgressDialogPreparingText' },
        { name: "reconnectionText", getter: 1, setter: 1, render: 'UploadProgressDialogReconnectionText' },
        { name: "secondsText", getter: 1, setter: 1 },
        { name: "timeFormat", getter: 1, setter: 1 },
        { name: "titleText", getter: 1, setter: 1, render: "UploadProgressDialogTitleText" }
    ],
    getParams: getParams,

    cancelUploadButtonText: function (value) {
        ///	<summary>
        ///		Get or set Cancel button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    estimationText: function (value) {
        ///	<summary>
        ///		Get or set estimation text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    hideButtonText: function (value) {
        ///	<summary>
        ///		Get or set Hide button text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    hoursText: function (value) {
        ///	<summary>
        ///		Get or set text for the hours unit.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    infoText: function (value) {
        ///	<summary>
        ///		Get or set info text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    kilobytesText: function (value) {
        ///	<summary>
        ///		Get or set text for the kilobytes unit.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    megabytesText: function (value) {
        ///	<summary>
        ///		Get or set text for the megabytes unit.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    minutesText: function (value) {
        ///	<summary>
        ///		Get or set text for the minutes unit.
        ///	</summary>
        ///	<param name="value" type="String /">
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    preparingText: function (value) {
        ///	<summary>
        ///		Get or set preparing text.
        ///	</summary>
        ///	<param name="value" type="String /">
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    reconnectionText: function (value) {
        ///	<summary>
        ///		Get or set reconnection text.
        ///	</summary>
        ///	<param name="value" type="String /">
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    secondsText: function (value) {
        ///	<summary>
        ///		Get or set text for the seconds unit.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    timeFormat: function (value) {
        ///	<summary>
        ///		Get or set time format string.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     A string value that specifies a format using the following syntax:
        ///     "variable1=value1;variable2=value2;...",
        ///     where variable is one of the string "Appearance", "PresentDirection", "DigitsFormat", "HideZero".
        ///	</param>
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    titleText: function (value) {
        ///	<summary>
        ///		Get or set title text.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    }
}

AU.uploadProgressDialog.init = propertyMaker._typicalInit;
AU.uploadProgressDialog.reinit = propertyMaker._typicalReinit;
AU.uploadSettings = function () {
    ///	<summary>
    ///		Upload options.
    ///	</summary>
    ///	<returns type="$au.uploadSettings" />
}

AU.uploadSettings.prototype = {
    __class: true,

    _simpleProperties: [
        { name: "actionUrl", getter: 1, setter: 1 },
        { name: "autoRecoveryMaxAttemptCount", getter: 1, setter: 1 },
        { name: "autoRecoveryTimeout", getter: 1, setter: 1 },
        { name: "charset", getter: 1, setter: 1 },
        { name: "chunkSize", getter: 1, setter: 1 },
        { name: "connectionTimeout", getter: 1, setter: 1 },
        { name: "enableInstantUpload", getter: 1, setter: 1 },
        { name: "filesPerPackage", getter: 1, setter: 1 },
        { name: "progressBytesMode", getter: 1, setter: 1 },
        { name: "uploadConverterOutputSeparately", getter: 1, setter: 1 }
    ],
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
                var self = this;
                this._afterUpload = function () {
                    setTimeout(function () { window.location = self._redirectUrl; }, 100);
                }
                uploader.events().afterUpload().add(this._afterUpload);

            }
        } else {
            return this._redirectUrl;
        }
    },
    getParams: getParams,

    actionUrl: function (url) {
        ///	<summary>
        ///		Get or set URL where to upload files.
        ///	</summary>
        ///	<param name="url" type="String">
        ///     URL to to upload
        ///	</param>
        ///	<returns type="String" />
    },
    autoRecoveryMaxAttemptCount: function (value) {
        ///	<summary>
        ///		Get or set number of tries that should be performed to submit files.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///	</param>
        ///	<returns type="Number" />
    },
    autoRecoveryTimeout: function (value) {
        ///	<summary>
        ///		Get or set interval in which Image Uploader should try to resume the upload if it was interrupted.
        ///	</summary>
        ///	<param name="value" type="Number">
        ///	</param>
        ///	<returns type="Number" />
    },
    charset: function (value) {
        ///	<summary>
        ///		Get or set charset used to encode the text data submitted by Image Uploader.
        ///	</summary>
        ///	<param name="value" type="String">
        ///	</param>
        ///	<returns type="String" />
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
    connectionTimeout: function (value) {
        ///	<summary>
        ///		Get or set timeout of the HTTP connection.
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
    },
    enableInstantUpload: function (value) {
        ///	<summary>
        ///		Enable or disable instant upload feature.
        ///	</summary>
        ///	<param name="value" type="Boolean">
        ///	</param>
        ///	<returns type="Boolean" />
    },
    filesPerPackage: function (value) {
        ///	<summary>
        ///		Get or set max number of files in one package.
        ///	</summary>
        ///	<param name="count" type="Number" />
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
        ///	<returns type="Number" />
    },
    uploadConverterOutputSeparately: function (value) {
        ///	<summary>
        ///		Enable or disable sending file for every converter in separate request.
        ///     Using this we can send many thumbnails for every selected to upload file.
        ///	</summary>
        ///	<param name="value" type="Boolean">
        ///	</param>
        ///	<returns type="Boolean" />
    }
};

AU.uploadSettings.init = propertyMaker._typicalInit;
AU.uploadSettings.reinit = propertyMaker._typicalReinit;
AU.uploader = function (obj) {
    ///	<summary>
    ///		1: uploader(id) - Get created uploader.
    ///		2: uploader(obj) - Create new uploader.
    ///	</summary>
    ///	<param name="obj" type="Object">
    ///		1: obj - Init object for new uploader.
    ///		2: id - An id of existing uploader.
    ///	</param>
    ///	<returns type="$au.uploader" />
    return new AU.uploader.fn.init(obj);
};

AU.uploader.fn = AU.uploader.prototype = new baseControl();

// Extend uploader prototype with new object
extend(AU.uploader.fn, {
    _simpleProperties: [
        undefined,
        { name: "auxiliaryTextColor", getter: 1, setter: 1 },
        { name: "backgroundColor", getter: 1, setter: 1 },
        { name: "borderStyle", getter: 1, setter: 1 },
        { name: "cancelUploadButtonImageFormat", getter: 1, setter: 1 },
        { name: "cancelUploadButtonText", getter: 1, setter: 1 },
        { name: "dialogColor", getter: 1, setter: 1 },
        { name: "dialogBorderColor", getter: 1, setter: 1 },
        { name: "enableAutoRotation", getter: 1, setter: 1 },
        { name: "enableCheckByClick", getter: 1, setter: 1 },
        { name: "enableContextMenu", getter: 1, setter: 1 },
        { name: "enableDescriptionEditor", getter: 1, setter: 1 },
        { name: "enableDragAndDrop", getter: 1, setter: 1 },
        { name: "enableImageEditor", getter: 1, setter: 1 },
        { name: "enableFileViewer", getter: 1, setter: 1 },
        { name: "enableOriginalFilesDeletion", getter: 1, setter: 1 },
        { name: "enableRotation", getter: 1, setter: 1 },
        { name: "enableStatusPane", getter: 1, setter: 1 },
		{ name: "enableUploadPane", getter: 1, setter: 1 },
        { name: "enableUploadProgress", getter: 1, setter: 1 },
        { name: "folderProcessingMode", getter: 1, setter: 1 },
        { name: "headerColor", getter: 1, setter: 1 },
        { name: "headerTextColor", getter: 1, setter: 1 },
        { name: "height", isAttribute: true, defaultValue: "400px" },
        { name: "hyperlinkColor", getter: 1, setter: 1 },
        { name: "licenseKey", getter: 1, setter: 1 },
        { name: "loadingFolderContentText", getter: 1, setter: 1 },
        { name: "paneFont", getter: 1, setter: 1 },
        { name: "paneLayout", getter: 1, setter: 1 },
        { name: "panelColor", getter: 1, setter: 1 },
        { name: "panelBorderColor", getter: 1, setter: 1 },
        { name: "pasteFileNameTemplate", getter: 1, setter: 1 },
        { name: "textColor", getter: 1, setter: 1 },
        { name: "titleFont", getter: 1, setter: 1 },
        { name: "uploadButtonImageFormat", getter: 1, setter: 1 },
        { name: "uploadButtonText", getter: 1, setter: 1 },
	    { name: "width", isAttribute: true, defaultValue: "600px" }
    ],
    _objectProperties: [
        { name: "addFilesProgressDialog", type: AU.addFilesProgressDialog },
        { name: "authenticationDialog", type: AU.authenticationDialog },
        { name: "contextMenu", type: AU.contextMenu },
        { name: "converters", type: AU.converters },
        { name: "deleteFilesDialog", type: AU.deleteFilesDialog },
        { name: "descriptionEditor", type: AU.descriptionEditor },
        { name: "detailsViewColumns", type: AU.detailsViewColumns },
        { name: "events", type: AU.events },
        { name: "files", type: AU.files },
        { name: "folderPane", type: AU.folderPane },
        { name: "imageEditor", type: AU.imageEditor },
        { name: "informationBar", type: AU.informationBar },
        { name: "messages", type: AU.messages },
        { name: "metadata", type: AU.metadata },
        { name: "paneItem", type: AU.paneItem },
        { name: "restrictions", type: AU.restrictions },
        { name: "statusPane", type: AU.statusPane },
        { name: "treePane", type: AU.treePane },
        { name: "uploadPane", type: AU.uploadPane },
        { name: "uploadProgressDialog", type: AU.uploadProgressDialog },
        { name: "uploadSettings", type: AU.uploadSettings }
    ],
    _methods: [
        { name: "refresh", controlMethodName: "Refresh" },
        { name: "upload", controlMethodName: "Upload" },
        { name: "cancelUpload", controlMethodName: "CancelUpload" }
    ],
    init: function (initObj) {
        ///	<returns type="$au.uploader" />

        if (typeof initObj === 'string') {
            return objectCache.get(initObj);
        }

        this._uploader = this;

        this._activeXControl = new AU.activeXControl(this);
        this.activeXControl({
            classId: uploaderClassID,
            progId: uploaderProgID,
            version: axVERSION
        });

        this._javaControl = new AU.javaControl(this);
        this.javaControl({
            className: 'com.aurigma.imageuploader.ImageUploader.class',
            version: jVERSION
        });

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
                        convertersObj._items.length = 0;
                        // and remove from actual control
                        while (convertersObj.count() > 0) {
                            convertersObj.remove(0);
                        }
                        // BUG 0014064: Remove last item in IE browser if it is undefined.
                        // This item is appears if there is a comma after last element in array.
                        if (AU.browser.isIE && newConverters[newConverters.length - 1] === undefined) {
                            newConverters.pop();
                        }
                        // add new converters
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

        showInfo('[js_info] Start apply uploader init object.');
        this.set(initObj);
        showInfo('[js_info] Finish apply uploader init object.');

        // put object into cache
        objectCache.put(this);
        return this;
    },
    reinit: function () {
        showInfo("[js_info] Start control re-initialization.");

        if (this.state()) {
            showInfo("Control tries to reinit twice");
        }

        var element = document.getElementById(this.id()), i, cnt;
        var type = this.type();

        if (type === 'activex') {
            // Special ActiveX actions

            var o = element;
            var props = new Array();
            for (propName in o) {
                var c = propName.charAt(0);
                if (c == c.toUpperCase()) {
                    props.push(propName);
                }
            }

            var createIndexedProperty = function (obj, propName) {
                obj["set" + propName] = function (i, v) { this[propName](i) = v; };
                obj["get" + propName] = function (i) { return this[propName](i); };
            };

            var createProperty = function (obj, propName) {
                obj["set" + propName] = function (v) { this[propName] = v; };
                obj["get" + propName] = function () { return this[propName]; };
            };

            for (i = 0; i < props.length; i++) {
                //Check whether property is indexed
                if (typeof (o[props[i]]) == "unknown") {
                    createIndexedProperty(o, props[i]);
                }
                else {
                    createProperty(o, props[i]);
                }
            }
        } else if (type == 'java') {
            // Special java actions

        }

        //create methods
        showInfo("[js_info] Creating methods.");
        if (this._methods && this._methods.length > 0) {
            for (var i = 0, imax = this._methods.length; i < imax; i++) {
                propertyMaker.createMethod(this, this._methods[i]);
            }
        }

        showInfo("[js_info] Reinit control properties.");

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

        showInfo("[js_info] Control re-initialization completed.");
        this.state(1);
    },

    auxiliaryTextColor: function (value) {
        ///	<summary>
        ///		Get or set auxiliary text color
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    backgroundColor: function (value) {
        ///	<summary>
        ///		Get or set background color
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	</param>
        ///	<returns type="String" />
    },
    borderStyle: function (value) {
        ///	<summary>
        ///		Get or set border style
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    cancelUploadButtonImageFormat: function (value) {
        ///	<summary>
        ///		Cancel upload button image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    cancelUploadButtonText: function (value) {
        ///	<summary>
        ///		Cancel upload button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    dialogColor: function (value) {
        ///	<summary>
        ///		Dialog color.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    dialogBorderColor: function (value) {
        ///	<summary>
        ///		Dialog border color.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    enableAutoRotation: function (value) {
        ///	<summary>
        ///		Enable auto rotation
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableCheckByClick: function (value) {
        ///	<summary>
        ///		Enable checking files by single click
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableContextMenu: function (value) {
        ///	<summary>
        ///		Enable context menu
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableDescriptionEditor: function (value) {
        ///	<summary>
        ///		Enable description editor
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableDragAndDrop: function (value) {
        ///	<summary>
        ///		Enable drag and drop
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableImageEditor: function (value) {
        ///	<summary>
        ///		Enable image editor
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableFileViewer: function (value) {
        ///	<summary>
        ///		Enable file viewer
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableOriginalFilesDeletion: function (value) {
        ///	<summary>
        ///		Enable original file deletion
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
    enableStatusPane: function (value) {
        ///	<summary>
        ///		Enable status pane
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableUploadPane: function (value) {
        ///	<summary>
        ///		Enable upload pane
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    enableUploadProgress: function (value) {
        ///	<summary>
        ///		Enable upload progress dialog
        ///	</summary>
        ///	<param name="value" type="Boolean" />
        ///	<returns type="Boolean" />
    },
    folderProcessingMode: function (value) {
        ///	<summary>
        ///		Get or set the way for folder processing
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    headerColor: function (value) {
        ///	<summary>
        ///		Get or set header color.
        ///     Specify two colors separated by semicolon to set gradient, e.g. "#000000;#FFFFFF".
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    headerTextColor: function (value) {
        ///	<summary>
        ///		Get or set text color of panes headers
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    height: function (value) {
        ///	<summary>
        ///		Get or set control height
        ///	</summary>
        ///	<param name="value" type="Number" />
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    hyperlinkColor: function (value) {
        ///	<summary>
        ///		Get or set hyperlink color
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    licenseKey: function (value) {
        ///	<summary>
        ///		Get or set license key
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    loadingFolderContentText: function (value) {
        ///	<summary>
        ///		Get or set "Loading content... " text in folder pane and tree pane
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    paneFont: function (value) {
        ///	<summary>
        ///		Get or set pane font. Pane font uses as primary font for all panes.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    paneLayout: function (value) {
        ///	<summary>
        ///		Get or set pane layout
        ///	</summary>
        ///	<param name="value" type="String">
        ///		One of the values "OnePane", "TwoPanes", "ThreePanes" or equivalent number value
        ///	</param>
        ///	<returns type="Number" />
    },
    panelColor: function (value) {
        ///	<summary>
        ///		Get or set panel color
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    panelBorderColor: function (value) {
        ///	<summary>
        ///		Get or set panel border color
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    pasteFileNameTemplate: function (value) {
        ///	<summary>
        ///		Get or set file name template for files pasted from clipboard. Default value is "Paste_[Date]_[Time]".
        ///     The [Date] and [Time] placeholders available.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    textColor: function (value) {
        ///	<summary>
        ///		Primary color for all text labels.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    titleFont: function (value) {
        ///	<summary>
        ///		Get or set font of the panes title.
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
    },
    uploadButtonImageFormat: function (value) {
        ///	<summary>
        ///		Upload button image format
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    uploadButtonText: function (value) {
        ///	<summary>
        ///		Upload button text
        ///	</summary>
        ///	<param name="value" type="String" />
        ///	<returns type="String" />
        /* will be created while initialization */
    },
    width: function (width) {
        ///	<summary>
        ///		Get or set control width
        ///	</summary>
        ///	<param name="width" type="Number">
        ///		Width
        ///	</param>
        ///	<returns type="Number" />
    },

    activeXControl: function () {
        ///	<summary>
        ///		Get or set ActiveX control parameters
        ///	</summary>
        ///	<returns type="$au.activeXControl" />
        return helpers.objProp(this, '_activeXControl', arguments.length, arguments[0]);
    },
    addFilesProgressDialog: AU.addFilesProgressDialog,
    authenticationDialog: AU.authenticationDialog,
    contextMenu: AU.contextMenu,
    converters: AU.converters,
    deleteFilesDialog: AU.deleteFilesDialog,
    descriptionEditor: AU.descriptionEditor,
    detailsViewColumns: AU.detailsViewColumns,
    events: AU.events,
    files: AU.files,
    folderPane: AU.folderPane,
    imageEditor: AU.imageEditor,
    informationBar: AU.informationBar,
    javaControl: function () {
        ///	<summary>
        ///		Get or set java control parameters
        ///	</summary>
        ///	<returns type="$au.javaControl" />
        return helpers.objProp(this, '_javaControl', arguments.length, arguments[0]);
    },
    messages: AU.messages,
    metadata: AU.metadata,
    paneItem: AU.paneItem,
    restrictions: AU.restrictions,
    statusPane: AU.statusPane,
    treePane: AU.treePane,
    uploadPane: AU.uploadPane,
    uploadProgressDialog: AU.uploadProgressDialog,
    uploadSettings: AU.uploadSettings,

    refresh: function () {
        ///	<summary>
        ///		Refresh folder pane content
        ///	</summary>
    },
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

AU.uploader.__class = true;
AU.uploader.prototype.constructor = AU.uploader;

// Give the init function the Aurigma.uploader prototype for later instantiation
AU.uploader.fn.init.prototype = AU.uploader.fn;
AU.thumbnailEvents = function () {
    ///	<summary>
    ///		Thumbnail event object. You do not need to create it directly.
    ///	</summary>
    ///	<returns type="$au.thumbnailEvents" />
    /* will be created while initialization */
};

AU.thumbnailEvents.prototype = new baseEvents();

extend(AU.thumbnailEvents.prototype, {
    _eventNames: [undefined /*base event - initComplete*/, undefined /*base event - preRender*/,
        "click"
    ],

    click: function () {
        ///	<summary>
        ///		Click event
        ///	</summary>
        ///	<returns type="$au.event" />
    }
}, true);

AU.thumbnailEvents.init = baseEvents.init;

AU.thumbnailEvents.prototype.constructor = AU.thumbnailEvents;
AU.thumbnail = function (initObj) {
    ///	<summary>
    ///		1: $au.thumbnail(id) - Get created thumbnail control.
    ///		2: $au.thumbnail(obj) - Create new thumbnail control.
    ///	</summary>
    ///	<param name="initObj" type="Object">
    ///		1: obj - Init object for thumbnail control.
    ///		2: id - An id of existing thumbnail control.
    ///	</param>
    ///	<returns type="$au.thumbnail" />
    return new AU.thumbnail.fn.init(initObj);
};

AU.thumbnail.fn = AU.thumbnail.prototype = new baseControl();

extend(AU.thumbnail.fn, {
    _simpleProperties: [
        undefined,
        { name: "backgroundColor", getter: 1, setter: 1 },
        { name: "guid", getter: 1, setter: 1 },
        { name: "height", isAttribute: true, defaultValue: "100px" },
        { name: "parentControlName", getter: 1, setter: 1 },
        { name: "width", isAttribute: true, defaultValue: "100px" }
    ],
    _objectProperties: [
        { name: "events", type: AU.thumbnailEvents }
    ],
    init: function (initObj) {
        ///	<returns type="$au.thumbnail" />

        if (typeof initObj === 'string') {
            return objectCache.get(initObj);
        }

        this._uploader = this;

        this._activeXControl = new AU.activeXControl(this);
        this.activeXControl({
            classId: thumbnailClassID,
            progId: thumbnailProgID,
            version: axVERSION
        });

        this._javaControl = new AU.javaControl(this);
        this.javaControl({
            className: 'com.aurigma.imageuploader.Thumbnail.class',
            version: jVERSION
        });

        // Init simple properties
        for (var i = 0, imax = this._simpleProperties.length; i < imax; i++) {
            propertyMaker.createSimpleProperty(this, this._simpleProperties[i]);
        }

        // Init object properties
        for (var i = 0, imax = this._objectProperties.length; i < imax; i++) {
            var property = this._objectProperties[i];
            propertyMaker.createObjectProperty(this, property, this);
        }

        //add InitComplete event to know that control rendered
        this.events().initComplete(function () {
            this.reinit();
        });

        showInfo('[js_info] Start apply uploader init object.');
        this.set(initObj);
        showInfo('[js_info] Finish apply uploader init object.');

        // put object into cache
        objectCache.put(this);
        return this;
    },
    reinit: function () {
        showInfo("[js_info] Start control re-initialization.");

        if (this.state()) {
            showInfo("Control tries to reinit twice");
            return;
        }

        var element = document.getElementById(this.id()), i, cnt;
        var type = this.type();

        if (type === 'activex') {
            // Special ActiveX actions

            var o = element;
            var props = new Array();
            for (propName in o) {
                var c = propName.charAt(0);
                if (c == c.toUpperCase()) {
                    props.push(propName);
                }
            }

            var createIndexedProperty = function (obj, propName) {
                obj["set" + propName] = function (i, v) { this[propName](i) = v; };
                obj["get" + propName] = function (i) { return this[propName](i); };
            };

            var createProperty = function (obj, propName) {
                obj["set" + propName] = function (v) { this[propName] = v; };
                obj["get" + propName] = function () { return this[propName]; };
            };

            for (i = 0; i < props.length; i++) {
                //Check whether property is indexed
                if (typeof (o[props[i]]) == "unknown") {
                    createIndexedProperty(o, props[i]);
                }
                else {
                    createProperty(o, props[i]);
                }
            }
        } else if (type == 'java') {
            // Special java actions

        }

        showInfo("[js_info] Reinit control properties.");

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
        showInfo("[js_info] Creating methods.");
        if (this._methods && this._methods.length > 0) {
            for (var i = 0, imax = this._methods.length; i < imax; i++) {
                propertyMaker.createMethod(this, this._methods[i]);
            }
        }

        showInfo("[js_info] Control re-initialization completed.");
        this.state(1);
    },

    activeXControl: function () {
        ///	<summary>
        ///		Get or set ActiveX control parameters
        ///	</summary>
        ///	<returns type="$au.activeXControl" />
        return helpers.objProp(this, '_activeXControl', arguments.length, arguments[0]);
    },
    javaControl: function () {
        ///	<summary>
        ///		Get or set java control parameters
        ///	</summary>
        ///	<returns type="$au.javaControl" />
        return helpers.objProp(this, '_javaControl', arguments.length, arguments[0]);
    },
    events: AU.thumbnailEvents,

    backgroundColor: function (value) {
        ///	<summary>
        ///		Get or set control background color
        ///	</summary>
        ///	<param name="value" type="String">
        ///		Color
        ///	</param>
        ///	<returns type="String" />

        /* will be created while initialization */
    },
    guid: function (value) {
        ///	<summary>
        ///		Get or set identifier (GUID) of the item which is represented with this Thumbnail control.
        ///	</summary>
        ///	<param name="value" type="String">
        ///     Guid ({XXXXXXXX-XXXX-XXXX-XXXX-XXXXXXXXXXXX}) of the item in the uploader's upload list.
        ///	</param>
        ///	<returns type="String" />

        /* will be created while initialization */
    },
    height: function (value) {
        ///	<summary>
        ///		Get or set control height
        ///	</summary>
        ///	<param name="value" type="Number">
        ///		control height
        ///	</param>
        ///	<returns type="Number" />
        /* will be created while initialization */
    },
    parentControlName: function (value) {
        ///	<summary>
        ///		Get or set name of the ImageUploader control instance this thumbnail is associated with.
        ///	</summary>
        ///	<param name="value" type="String">
        ///		Id of the ImageUploader control.
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
    }
}, true);

AU.thumbnail.__class = true;
AU.thumbnail.prototype.constructor = AU.thumbnail;

// Give the init function the Aurigma.uploader prototype for later instantiation
AU.thumbnail.fn.init.prototype = AU.thumbnail.fn;
AU.event = function evt() {
    ///	<summary>
    ///		Event object. You do not need to directly create it.
    ///	</summary>
    ///	<returns type="$au.event" />
    this._handlers = this._handlers || [];
};

AU.event.prototype = {
    __class: true,

    add: function (handler) {
        if (handler instanceof Array) {
            for (var i = 0, cnt = handler.length; i < cnt; i++)
                this._handlers.push(handler[i]);
        } else {
            this._handlers.push(handler);
        }
    },
    remove: function (handler) {
        for (var i in this._handlers) {
            if (this._handlers[i] === handler) {
                this._handlers.splice(i, 1);
                return true;
            }
        }
        return false;
    },
    clear: function () {
        this._handlers = [];
    },
    count: function () {
        return this._handlers.length;
    }
};

AU.event.prototype.constructor = AU.event;
/*********************
** ActiveX renderer **
*********************/
var activeXRenderer = function (uploader) {

    if (!uploader)
        return;

    function createEventHtml(name, value) {
        name = name.substr(0, name.length - "listener".length);
        var id = uploader.id(), scriptId = id + name, isNew = false;
        var scriptElement = document.getElementById(scriptId);
        if (scriptElement == null) {
            scriptElement = document.createElement('script');
            scriptElement.id = scriptId;
            scriptElement.type = 'text/javascript';
            scriptElement.htmlFor = id;
            scriptElement.event = name;
            isNew = true;
        }
        var s = 'return ' + value + '.apply(this, arguments);';
        try {
            scriptElement.appendChild(document.createTextNode(s));
        } catch (e) {
            scriptElement.text = s;
        }
        if (isNew) {
            var head = document.getElementsByTagName('head')[0];
            scriptElement = head.insertBefore(scriptElement, head.firstChild);
        }
    }

    //create browser specific activex uploader markup
    var getHtml = function () {

        var html = [], events = [], i,
            id = uploader.id(),
            objectAttrs = {
                id: id,
                name: id,
                codebase: browser.isIE64 ? uploader.activeXControl().codeBase64() : uploader.activeXControl().codeBase(),
                classid: "clsid:" + uploader.activeXControl().classId()
            };

        var v = uploader.activeXControl().version();
        if (v) {
            v = (v + '').replace(/\./g, ",");
            objectAttrs.codebase += ("#version=" + v);
        }

        // object tag for IE browser
        if (browser.isIE && browser.isWindowsOS && !browser.isOpera) {

            // invoke activeXBeforeOpenTagRender callback
            var activeXBeforeOpenTagRenderArgs = [uploader, { resultHtml: ''}], result;
            uploader._invokeCallback('activeXBeforeOpenTagRender', activeXBeforeOpenTagRenderArgs);

            // add before open tag html if any
            if (result = activeXBeforeOpenTagRenderArgs[1].resultHtml) {
                html.push(result);
            }

            html.push('<object ');

            // add uploader width and height
            objectAttrs.width = uploader.width();
            objectAttrs.height = uploader.height();

            for (i in objectAttrs) {
                html.push(i + '="' + htmlencode(objectAttrs[i]) + '" ');
            }
            html.push('>');
            var objectParams = uploader.getParams();
            for (i in objectParams) {
                var p = objectParams[i];
                if (/listener$/.test(p.name)) {
                    createEventHtml(p.name, p.value);
                } else {
                    html.push('<param name="' + p.name + '" value="' + htmlencode(p.value) + '" /> ');
                }
            }

            // invoke activeXBeforeCloseTagRender callback
            var activeXBeforeCloseTagRenderArgs = [uploader, { resultHtml: ''}], result;
            uploader._invokeCallback('activeXBeforeCloseTagRender', activeXBeforeCloseTagRenderArgs);

            // add before close tag html if any
            if (result = activeXBeforeCloseTagRenderArgs[1].resultHtml) {
                html.push(result);
            }

            html.push('</object>');

            // invoke activeXBeforeCloseTagRender callback
            var activeXAfterCloseTagRenderArgs = [uploader, { resultHtml: ''}], result;
            uploader._invokeCallback('activeXAfterCloseTagRender', activeXAfterCloseTagRenderArgs);

            // add before close tag html if any
            if (result = activeXAfterCloseTagRenderArgs[1].resultHtml) {
                html.push(result);
            }
        } else {
            // ActiveX available for IE only.
            var msg = "Browser doesn't support activex.";
            showError(msg);
            throw new Error(msg);
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
/******************
** Java renderer **
******************/
var javaRenderer = function (uploader, options) {

    var getHtml = function () {

        var html = [], events = [], i, tagName, id = uploader.id();
        var attributes = {
            id: id
        };
        var params = uploader.getParams();

        if (options.enableResumeUploadCallback) {
            params.push({ name: 'EnableResumeUploadCallback', value: true });
        }

        var jarFileName = uploader.javaControl().getJarFileName();
        var codeBase = uploader.javaControl().codeBase() + "";
        // cut jar filename
        codeBase = codeBase.split("/");
        codeBase = codeBase.slice(0, codeBase.length - 1);
        if (codeBase.length == 0) {
            codeBase = '.';
        } else if (codeBase.length == 1 && codeBase[0] == '') {
            codeBase = '/';
        } else {
            codeBase = codeBase.join("/");
        }

        // all resources contain in .jar archive
        params.push({ name: "codebase_lookup", value: "false" });

        // set cache options
        if (uploader.javaControl().cached() && uploader.javaControl().version()) {
            params.push({ name: "cache_archive", value: jarFileName });
            params.push({ name: "cache_version", value: (uploader.javaControl().version() + "").replace(/,/g, ".") });
        }

		// BUGBUG 0014000 Add control id in param for IE browser
		params.push({ name: "control_id", value: id });

        var javaVersion = uploader.javaControl().javaVersionRequired;

        // I think we can use <object> tag for most browsers.
        // IE always supports <object> tag
        // Chrome, FF on Windows - work
        // Safari, Chrome, FF on Mac - work
        // FF on Linux - work

        var useObjectTag = true;

        if (useObjectTag) {
            tagName = 'object';
            attributes.classid = "clsid:8AD9C840-044E-11D1-B3E9-00805F499D93"; // java classId
            // IE-x64 need to manual download and install x64 version of Java.
            if (!browser.isIE64) {
                javaVersion = javaVersion.join(',');
                attributes.codebase = window.location.protocol +
                        "//java.sun.com/update/1.6.0/jinstall-6-windows-i586.cab#Version=" + javaVersion;
            }
            params.push({ name: "archive", value: jarFileName });
            if (codeBase) {
                params.push({ name: "codebase", value: codeBase });
            }
            // remove ".class" from class name
            var className = uploader.javaControl().className();
            if (className.substring(className.length - 6) == '.class') {
                className = className.substring(0, className.length - 6);
            }
            params.push({ name: "code", value: className });
            params.push({ name: "mayscript", value: "true" });
            params.push({ name: "scriptable", value: "true" });

            params.push({ name: "java_version", value: "1.5+" });

            if (!browser.isIE) {
                attributes.archive = uploader.javaControl().getJarFileName();
                attributes.codebase = codeBase;
                javaVersion = uploader.javaControl().javaVersionRequired;
                javaVersion = javaVersion[0] + '.' + javaVersion[1];
                attributes.type = "application/x-java-applet;version=" + javaVersion;
                params.push({ name: "type", value: "application/x-java-applet;version=" + javaVersion });
                delete attributes.classid;
            }
        }
        else {
            tagName = "applet";
            attributes.code = uploader.javaControl().className();
            attributes.archive = uploader.javaControl().getJarFileName();
            if (codeBase) {
                attributes.codebase = codeBase;
            }
            attributes.mayscript = "true";
            attributes.scriptable = "true";
        }

        // invoke javaBeforeOpenTagRender callback
        var javaBeforeOpenTagRenderArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('javaBeforeOpenTagRender', javaBeforeOpenTagRenderArgs);
        // add before open tag html if any
        if (result = javaBeforeOpenTagRenderArgs[1].resultHtml) {
            html.push(result);
        }

        html.push('<' + tagName);

        // invoke javaRenderStyleAttribute callback
        var javaRenderStyleAttributeArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('javaRenderStyleAttribute', javaRenderStyleAttributeArgs);
        // add style attributes if any
        if (result = javaRenderStyleAttributeArgs[1].resultHtml) {
            html.push(' style="' + result + '" ');
        }

        attributes.width = uploader.width();
        attributes.height = uploader.height();
        for (var attrName in attributes) {
            if (attributes.hasOwnProperty(attrName)) {
                html.push(' ' + attrName + '="' + attributes[attrName] + '"');
            }
        }
        if (tagName === 'embed') {
            for (var i = 0, maxi = params.length; i < maxi; i++) {
                var param = params[i];
                html.push(param.name + '="' + htmlencode(param.value) + '" ');
            }
            html.push('>');
        } else {
            html.push('>');
            for (var i = 0, maxi = params.length; i < maxi; i++) {
                var param = params[i];
                html.push('<param name="' + param.name + '" value="' + htmlencode(param.value) + '" />');
            }
        }

        // invoke javaBeforeCloseTagRender callback
        var javaBeforeCloseTagRenderArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('javaBeforeCloseTagRender', javaBeforeCloseTagRenderArgs);
        // add before close tag html if any
        if (result = javaBeforeCloseTagRenderArgs[1].resultHtml) {
            html.push(result);
        }

        html.push('</' + tagName + '>');

        // invoke javaAfterCloseTagRender callback
        var javaAfterCloseTagRenderArgs = [uploader, { resultHtml: ''}], result;
        uploader._invokeCallback('javaAfterCloseTagRender', javaAfterCloseTagRenderArgs);
        // add after close tag html if any
        if (result = javaAfterCloseTagRenderArgs[1].resultHtml) {
            html.push(result);
        }

        // Fix LiveConnect bug in Firefox on Mac OS.
        // By unknown reason java code can't call
        // javascript code, until we call java code
        // from javascript.
        if (/mac/i.test(window.navigator.platform) && /firefox/i.test(window.navigator.userAgent) && ('java' in window)) {
            window.java.lang.System.getProperty('java.version');
        }

        return html.join("");
    }

    return {
        html: getHtml,
        write: function () {
            ///	<summary>
            ///		write java uploader markup
            ///	</summary>
            document.write(this.html());
        }
    };
};
// set namespace property for VS intelisense
AU.__namespace = true;

//expose to global
window.Aurigma = window.Aurigma || { __namespace: true };
window.Aurigma.ImageUploader = AU;
// short alias
window.$au = AU;

})(window);

