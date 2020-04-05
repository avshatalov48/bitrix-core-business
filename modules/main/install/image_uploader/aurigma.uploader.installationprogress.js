(function (window, undefined) {
    var
    // local variable for global namespace
    AU = window.Aurigma ? (window.Aurigma.ImageUploader || {}) : {};

    AU.installationProgress = function (uploader) {
        if (!(this instanceof AU.installationProgress)) {
            return new AU.installationProgress(uploader);
        }

        this._uploader = uploader;

        //Show Loading Screen and Installation Screen by default.
        this._showDownloadingScreen = this._showInstallationScreen = true;

        // Register ActiveX render callbacks.
        uploader.registerCallback('activeXBeforeOpenTagRender', this.onActiveXBeforeOpenTagRender, this);
        uploader.registerCallback('activeXBeforeCloseTagRender', this.onActiveXBeforeCloseTagRender, this);
        uploader.registerCallback('activeXAfterCloseTagRender', this.onActiveXAfterCloseTagRender, this);

        // Register Java render callbacks.
        uploader.registerCallback('javaBeforeOpenTagRender', this.onJavaBeforeOpenTagRender, this);
        uploader.registerCallback('javaAfterCloseTagRender', this.onJavaAfterCloseTagRender, this);
        uploader.registerCallback('javaRenderStyleAttribute', this.onJavaRenderStyleAttribute, this);
        uploader.registerCallback('javaBeforeCloseTagRender', this.onJavaBeforeCloseTagRender, this);

        // Register Flash render callbacks.
        uploader.registerCallback('flashBeforeOpenTagRender', this.onFlashBeforeOpenTagRender, this);
        uploader.registerCallback('flashRenderStyleAttribute', this.onFlashRenderStyleAttribute, this);
        uploader.registerCallback('flashAfterCloseTagRender', this.onFlashAfterCloseTagRender, this);
        uploader.registerCallback('flashBeforeCloseTagRender', this.onFlashBeforeCloseTagRender, this);

        this._splashscreentimeout = 120000; // hide splash screen after 2 minutes
        var id = uploader.id();
        var f = function () {
            var splash_screen = document.getElementById(id + "-progress");
            var uploader = document.getElementById(id);
            if (splash_screen)
                splash_screen.style.display = "none";
            if (uploader) {
                uploader.style.position = "";
                uploader.style.left = "0";
                uploader.style.visibility = "";
            }
        };

        uploader.events().initComplete().add(f);

        //show control after 2 min timeout
        // even if it is not loaded
        setTimeout(f, this._splashscreentimeout);

        //CSS classes
        this._progressCssClass = "";
        this._instructionsCssClass = "";

        //Common description
        this._commonHtml = "<p>Aurigma Image Uploader control is necessary to upload "
		+ "your files quickly and easily. You will be able to select multiple images "
		+ "in user-friendly interface instead of clumsy input fields with <strong>Browse</strong> button.</p>";

        // Installation progress messages
        this._progressHtml = "<p><img src=\"{0}\" />"
		    + "<br />"
		    + "Loading Aurigma Image Uploader...</p>";
        this._flashProgressHtml = "<p><img src=\"{0}\" />"
		    + "<br />"
		    + "Loading Aurigma Flash Uploader...</p>";

        this._progressImageUrl = "Scripts/InstallationProgress.gif";

        //Before IE 6 Windows XP SP2
        this._beforeIE6XPSP2ProgressHtml = "<p>To install Image Uploader, please wait until the control will be loaded and click "
		+ "the <strong>Yes</strong> button when you see the installation dialog.</p>";
        this._beforeIE6XPSP2InstructionsHtml = "<p>To install Image Uploader, please reload the page and click "
		+ "the <strong>Yes</strong> button when you see the control installation dialog. "
		+ "If you don't see installation dialog, please check your security settings.</p>";

        //IE 6 Windows XP SP2
        this._IE6XPSP2ProgressHtml = "<p>Please wait until the control will be loaded.</p>";
        this._IE6XPSP2InstructionsHtml = "<p>To install Image Uploader, please click on the <strong>Information Bar</strong> and select "
		+ "<strong>Install ActiveX Control</strong> from the dropdown menu. After page reload click <strong>Install</strong> when "
		+ "you see the control installation dialog. If you don't see Information Bar, please try to reload the page and/or check your security settings.</p>";

        //IE 7
        this._IE7ProgressHtml = this._IE6XPSP2ProgressHtml;
        this._IE7InstructionsHtml = "<p>To install Image Uploader, please click on the <strong>Information Bar</strong> "
		+ "and select <strong>Install ActiveX Control</strong> or <strong>Run ActiveX Control</strong> from the dropdown menu.</p>"
		+ "<p>Then either click <strong>Run</strong> or after the page reload click <strong>Install</strong> "
		+ "when you see the control installation dialog. If you don't see Information Bar, please try to reload the page and/or check your security settings.</p>";

        //IE 8
        this._IE8ProgressHtml = this._IE6XPSP2ProgressHtml;
        this._IE8InstructionsHtml = "<p>To install Image Uploader, please click on the <strong>Information Bar</strong> "
		+ "and select <strong>Install This Add-on</strong> or <strong>Run Add-on</strong> from the dropdown menu.</p>"
		+ "<p>Then either click <strong>Run</strong> or after the page reload click <strong>Install</strong> "
		+ "when you see the control installation dialog. If you don't see Information Bar, please try to reload the page and/or check your security settings.</p>";

        //Alternative standalone installer
        this._altInstallerHtml = "";

        /**************************************
        * Update ActiveX control instructions *
        ***************************************/
        this._updateInstructions = "You need to update Image Uploader control. Click <strong>Install</strong> or <strong>Run</strong> button when you see the control installation dialog."
        + " If you don't see installation dialog, please try to reload the page.";

        /****************************
        * Install Java instructions *
        *****************************/
        //Mac
        //On Mac install/update Java from Software Update
        this._macInstallJavaHtml = "<p>Use the <a href=\"http://support.apple.com/kb/HT1338\">Software Update</a> feature "
        + "(available on the Apple menu) to check that you have the most up-to-date version of Java for your Mac.</p>";

        //Other browsers
        this._installJavaHtml = "<p>Please <a href=\"http://www.java.com/getjava/\">download</a> and install Java.</p>";

        /************************************
        * Install flash player instructions *
        *************************************/
        //One message for all browsers.
        this._installFlashPlayerHtml = "<p>You need to install Flash Player for running Aurigma Flash Uploader. Download latest version from <a href='http://www.adobe.com/go/getflashplayer' title='Download Flash Player'>here</a>.</p>";
    }

    AU.installationProgress.prototype.__class = true;

    /*******************************************************
    * Render progress and installation screens for ActiveX *
    ********************************************************/

    AU.installationProgress.prototype.onActiveXBeforeOpenTagRender = function (uploader, args) {
        // Create Loading Screen html for ActiveX control if Loading Screen enabled.
        if (this.showDownloadingScreen()) {
            args.resultHtml = this._createProgressScreen(uploader, 'ax');
        }
    };

    AU.installationProgress.prototype.onActiveXAfterCloseTagRender = function (uploader, args) {
        if (this.showDownloadingScreen() && uploader.activeXControl().isActiveXSupported()) {
            // If Loading Screen enabled we wrap control into div container.
            // This is the closing tag for this div.
            args.resultHtml = '</div>';
        }
    };

    AU.installationProgress.prototype.onActiveXBeforeCloseTagRender = function (uploader, args) {

        // Create Installation instructions html for ActiveX control if Installation Screen enabled.
        if (this.showInstallationScreen()) {

            var html = [];

            html.push('<div ');
            html.push('id="' + uploader.id() + '-install-bg"');
            html.push(' style="background-color:#fff;position:relative;z-index:1000;">');
            html.push('<div ');
            html.push('id="' + uploader.id() + '-install"');
            var w = uploader.width(), h = uploader.height();
            // if value is without units add 'px'
            w = parseInt(w) + '' == w ? w + 'px' : w;
            h = parseInt(h) + '' == h ? h + 'px' : h;

            html.push(' style="margin:0;padding:0;border:0 none;overflow: hidden;width:' + w + ';height:' + h + '"');
            if (this._instructionsCssClass) {
                html.push(' class="' + this._instructionsCssClass + '"');
            }
            html.push(">");

            html.push(this._commonHtml);

            if (uploader.activeXControl().getActiveXInstalledToUpdate()) {

                html.push(this._updateInstructions);

            } else {

                if (AU.browser.isBeforeIE6XPSP2) {
                    html.push(this._beforeIE6XPSP2InstructionsHtml)
                }
                else if (AU.browser.isIE6XPSP2) {
                    html.push(this._IE6XPSP2InstructionsHtml)
                }
                else if (AU.browser.isIE7) {
                    html.push(this._IE7InstructionsHtml)
                }
                else {
                    html.push(this._IE8InstructionsHtml)
                }

                if (this._altInstallerHtml) {
                    html.push(this._altInstallerHtml);
                }
            }

            html.push("</div>");
            html.push("</div>");
            args.resultHtml = html.join('');
        }
    };

    /*******************************************************
    * Render progress and installation screens for Java *
    ********************************************************/

    AU.installationProgress.prototype.onJavaAfterCloseTagRender = function (uploader, args) {
        if (this.showDownloadingScreen() && uploader.javaControl().isJavaSupported()) {
            // If Loading Screen enabled we wrap control into div container.
            // This is the closing tag for this div.
            args.resultHtml = '</div>';
        }

        // Special code for Opera.
        // Opera trying to load applet even if installed version of Java less then
        // required version. Obviously it is fails, but the message inside <object> tag is not showing.
        // So we show this message and comment <object> tag.
        if (this.showInstallationScreen() && AU.browser.isOpera &&
            !uploader.javaControl().isJavaSupported() && AU.javaDetector.getJREs().length > 0) {
            args.resultHtml += " -->";
        }
    };

    AU.installationProgress.prototype.onJavaBeforeCloseTagRender = function (uploader, args) {

        // Create Installation instructions html for Java control if Installation Screen enabled.
        if (this.showInstallationScreen()) {

            var html = [];

            html.push('<div ');
            html.push('id="' + uploader.id() + '-install-bg"');
            html.push(' style="background-color:#fff;position:relative;z-index:1000;">');
            html.push('<div ');
            html.push('id="' + uploader.id() + '-install"');
            var w = uploader.width(), h = uploader.height();
            // if value is without units add 'px'
            w = parseInt(w) + '' == w ? w + 'px' : w;
            h = parseInt(h) + '' == h ? h + 'px' : h;

            html.push(' style="margin:0;padding:0;border:0 none;overflow:hidden;width:' + w + ';height:' + h + '"');
            if (this._instructionsCssClass) {
                html.push(' class="' + this._instructionsCssClass + '"');
            }
            html.push(">");

            if (navigator.platform.toLowerCase().indexOf("mac") > -1) {
                // Java installation instructions for Mac
                html.push(this._macInstallJavaHtml);
            } else {
                // Java installation instructions for other browsers
                html.push(this._installJavaHtml);
            }

            if (!AU.browser.isIE && false) {
                // If we use object tag and define html to show if java is not installed,
                // then FF and Chrome doesn't show standard toolbar with request to install java.
                // So we add empty <embed> to trigger plugin installation tollbar in FF and Chrome.
                var javaVersion = uploader.javaControl().javaVersionRequired;
                javaVersion = javaVersion[0] + '.' + javaVersion[1];
                html.push('<embed code="empty.class" width="0" height="0" java_version="' +
                    javaVersion + '+" type="application/x-java-applet;version=' +
                    javaVersion + '" pluginspage='
                    + '"http://javadl.sun.com/webapps/download/GetFile/1.6.0_16-b01/windows-i586/xpiinstall.exe"></embed>');
            }

            html.push("</div>");
            html.push("</div>");
            args.resultHtml = html.join('');
        }
    };

    AU.installationProgress.prototype.onJavaBeforeOpenTagRender = function (uploader, args) {

        // Create Loading screen html for Java control if Loading Screen enabled.
        if (this.showDownloadingScreen() && uploader.javaControl().isJavaSupported()) {
            // Loading screen html
            args.resultHtml = this._createProgressScreen(uploader, 'j');
        }

        // Special code for Opera.
        // Opera trying to load applet even if installed version of Java less then
        // required version. Obviously it is fails, but the message inside <object> tag is not showing.
        // So we show this message and comment <object> tag.
        // This is the opening tag for comment
        if (this.showInstallationScreen() && AU.browser.isOpera &&
            !uploader.javaControl().isJavaSupported() && AU.javaDetector.getJREs().length > 0) {
            this.onJavaBeforeCloseTagRender(uploader, args);
            args.resultHtml += "<!-- ";
        }
    };

    AU.installationProgress.prototype.onJavaRenderStyleAttribute = function (uploader, args) {

        // Create Loading screen html for Java control if Loading Screen enabled.
        if (this.showDownloadingScreen() && uploader.javaControl().isJavaSupported()) {

            //In IE "visibility:hidden" doesn't work,
            // in FF3 "position:absolute;left:-10000px;" doesn't work properly
            if (AU.browser.isIE) {
                args.resultHtml = "position:absolute;left:-10000px;";
            }
            else {
                args.resultHtml = "visibility:hidden;";
            }
        }
    };

    /*******************************************************
    * Render progress and installation screens for Flash *
    ********************************************************/

    AU.installationProgress.prototype.onFlashAfterCloseTagRender = function (uploader, args) {
        if (this.showDownloadingScreen() && true || uploader.flashControl().isFlashSupported()) {
            // If Loading Screen enabled we wrap control into div container.
            // This is the closing tag for this div.
            args.resultHtml = '</div>';
        }

        // For non-IE browsers we can't specif what flash version required, so if flash installed and
        // but its version is less than required we comment flash object code and show Installation message
        // This is the closing tag for comment
        if (this.showInstallationScreen() && !uploader.flashControl().isFlashSupported() &&
            !AU.browser.isIE && AU.flashDetector.detectVersion()[0] > -1) {
            args.resultHtml = " -->";
        }
    };

    AU.installationProgress.prototype.onFlashBeforeOpenTagRender = function (uploader, args) {
        // Create Loading screen html for Flash control if Loading Screen enabled.
        if (this.showDownloadingScreen() && uploader.flashControl().isFlashSupported()) {
            // Loading screen html
            args.resultHtml = this._createProgressScreen(uploader, 'f');
        }

        // For non-IE browsers we can't specif what flash version required, so if flash installed and
        // but its version is less than required we comment flash object code and show Installation message
        if (this.showInstallationScreen() && !uploader.flashControl().isFlashSupported() &&
            !AU.browser.isIE && AU.flashDetector.detectVersion()[0] > -1) {
            this.onFlashBeforeCloseTagRender(uploader, args);
            args.resultHtml += "<!-- ";
        }
    };

    AU.installationProgress.prototype.onFlashRenderStyleAttribute = function (uploader, args) {
        // Create Loading screen html for Java control if Loading Screen enabled.
        if (this.showDownloadingScreen() && uploader.flashControl().isFlashSupported()) {

            //In IE "visibility:hidden" doesn't work,
            // in FF3 "position:absolute;left:-10000px;" doesn't work properly
            if (AU.browser.isIE) {
                args.resultHtml = "position:absolute;left:-10000px;";
            }
            else {
                args.resultHtml = "visibility:hidden;";
            }
        }
    };

    AU.installationProgress.prototype.onFlashBeforeCloseTagRender = function (uploader, args) {
        // Create Installation instructions html for Java control if Installation Screen enabled.
        if (this.showInstallationScreen()) {

            var html = [];

            html.push('<div ');
            html.push('id="' + uploader.id() + '-install"');
            var w = uploader.width(), h = uploader.height();
            // if value is without units add 'px'
            w = parseInt(w) + '' == w ? w + 'px' : w;
            h = parseInt(h) + '' == h ? h + 'px' : h;

            html.push(' style="margin:0;padding:0;border:0 none;position:relative;z-index:1000;overflow:hidden;width:' + w + ';height:' + h + '"');
            if (this._instructionsCssClass) {
                html.push(' class="' + this._instructionsCssClass + '"');
            }
            html.push(">");

            html.push(this._installFlashPlayerHtml);

            html.push("</div>");

            if (!AU.browser.isIE) {
                // If we use object tag and define html to show if java is not installed,
                // then FF and Chrome doesn't show standard toolbar with request to install java.
                // So we add empty <object> to trigger plugin installation tollbar in FF and Chrome.
                html.push('<object width="0" height="0" type="application/x-shockwave-flash"></object>');
            }

            html.push("</div>");
            args.resultHtml = html.join('');
        }
    };

    AU.installationProgress.prototype._createProgressScreen = function (uploader, type) {
        var html = [], m;
        var w = uploader.width();
        var rg = /^(\d+)([^0-9]+)?$/;
        if (m = rg.exec(w)) {
            if (m[2] == '%') {
                uploader.width('100%');
            }
            // if value is without units add 'px'
            if (!m[2]) {
                w += 'px';
            }
        } else {
            showWarning('InstallationProgress: Can not parse uploader width: ' + w);
        }
        var h = uploader.height();
        if (m = rg.exec(h)) {
            if (m[2] == '%') {
                uploader.height('100%');
            }
            // if value is without units add 'px'
            if (!m[2]) {
                h += 'px';
            }
        } else {
            showWarning('InstallationProgress: Can not parse uploader height: ' + h);
        }

        // wrap progress screen and uloader into div
        html.push('<div ');
        html.push(' style="position: relative;width:' + w + ';height:' + h + ';"');
        html.push('>');

        // progress div
        html.push('<div');
        html.push(' id="' + uploader.id() + '-progress"');
        html.push(' style="border:0 none;margin:0;padding:0;position:absolute;overflow:hidden;width:100%;height:100%"');
        if (this._progressCssClass) {
            html.push(' class="' + this._progressCssClass + '"');
        }
        html.push('>');

        if (type === 'ax') {
            html.push((this._progressHtml + '').replace('{0}', this._progressImageUrl));
            html.push(this._commonHtml);
            if (uploader.activeXControl().getActiveXInstalledToUpdate()) {

                html.push(this._updateInstructions);

            } else {
                if (AU.browser.isBeforeIE6XPSP2) {
                    html.push(this._beforeIE6XPSP2ProgressHtml)
                }
                else if (AU.browser.isIE6XPSP2) {
                    html.push(this._IE6XPSP2ProgressHtml)
                }
                else if (AU.browser.isIE7) {
                    html.push(this._IE7ProgressHtml)
                }
                else {
                    html.push(this._IE8ProgressHtml)
                }
            }
        } else if (type === 'j') {
            html.push((this._progressHtml + '').replace('{0}', this._progressImageUrl));
            html.push(this._commonHtml);
        } else if (type === 'f') {
            html.push((this._flashProgressHtml + '').replace('{0}', this._progressImageUrl));
        }

        html.push("</div>");
        return html.join("");
    };

    AU.installationProgress.prototype._prop = function (name, value, action) {
        if (action) {
            // set passed value
            this['_' + name] = value;
        } else {
            // return current value
            return this['_' + name];
        }
    };

    AU.installationProgress.prototype.showDownloadingScreen = function (value) {
        return this._prop('showDownloadingScreen', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.showInstallationScreen = function (value) {
        return this._prop('showInstallationScreen', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.progressCssClass = function (value) {
        return this._prop('progressCssClass', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.instructionsCssClass = function (value) {
        return this._prop('instructionsCssClass', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.commonHtml = function (value) {
        return this._prop('commonHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.progressHtml = function (value) {
        return this._prop('progressHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.flashProgressHtml = function (value) {
        return this._prop('flashProgressHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.progressImageUrl = function (value) {
        return this._prop('progressImageUrl', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.beforeIE6XPSP2ProgressHtml = function (value) {
        return this._prop('beforeIE6XPSP2ProgressHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.beforeIE6XPSP2InstructionsHtml = function (value) {
        return this._prop('beforeIE6XPSP2InstructionsHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.IE6XPSP2ProgressHtml = function (value) {
        return this._prop('IE6XPSP2ProgressHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.IE6XPSP2InstructionsHtml = function (value) {
        return this._prop('IE6XPSP2InstructionsHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.IE7ProgressHtml = function (value) {
        return this._prop('IE7ProgressHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.IE7InstructionsHtml = function (value) {
        return this._prop('IE7InstructionsHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.IE8ProgressHtml = function (value) {
        return this._prop('IE8ProgressHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.IE8InstructionsHtml = function (value) {
        return this._prop('IE8InstructionsHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.altInstallerHtml = function (value) {
        return this._prop('altInstallerHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.updateInstructions = function (value) {
        return this._prop('updateInstructions', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.macInstallJavaHtml = function (value) {
        return this._prop('macInstallJavaHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.installJavaHtml = function (value) {
        return this._prop('installJavaHtml', arguments[0], arguments.length);
    };

    AU.installationProgress.prototype.installFlashPlayerHtml = function (value) {
        return this._prop('installFlashPlayerHtml', arguments[0], arguments.length);
    };

    //expose to global
    window.Aurigma = window.Aurigma || { __namespace: true };
    window.Aurigma.ImageUploader = AU;

})(window);
