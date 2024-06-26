/*!
	Slimbox v1.71 - The ultimate lightweight Lightbox clone
	(c) 2007-2009 Christophe Beyls <http://www.digitalia.be>
	MIT-style license.
*/

var _slimboxIsEditing = false;

var Slimbox = (function() {

	// Global variables, accessible to Slimbox only
	var win = window, ie6 = Browser.Engine.trident4, options, images, activeImage = -1, activeURL, prevImage, nextImage, compatibleOverlay, middle, centerWidth, centerHeight,

	_zoomed=0,
	_currentIdx=0,

	// Preload images
	preload = {}, preloadPrev = new Image(), preloadNext = new Image(),

	// DOM elements
	overlay, center, image, sizer, prevLink, nextLink, bottomContainer, bottom, caption, number, editBtn,

	// Effects
	fxOverlay, fxResize, fxImage, fxBottom;

	/*
		Initialization
	*/

	win.addEvent("domready", function() {
		// Append the Slimbox HTML code at the bottom of the document
		$(document.body).adopt(
			$$(
				overlay = new Element("div", {id: "lbOverlay", events: {click: close}}),
				center = new Element("div", {id: "lbCenter"}),
				bottomContainer = new Element("div", {id: "lbBottomContainer"})
			).setStyle("display", "none")
		);

		image = new Element("div", {id: "lbImage"}).injectInside(center).adopt(
			sizer = new Element("div", {styles: {position: "relative"}}).adopt(
				prevLink = new Element("a", {id: "lbPrevLink", href: "#", events: {click: previous}}),
				nextLink = new Element("a", {id: "lbNextLink", href: "#", events: {click: next}})
			)
		);

		bottom = new Element("div", {id: "lbBottom"}).injectInside(bottomContainer).adopt(
			new Element("a", {id: "lbCloseLink", href: "#", events: {click: close}}),
			new Element("a", {id: "lbZoomLink", href: "#", events: {click: zoom}}),
			editBtn = new Element("a", {id: "lbEditLink", href: "#", events: {click: edit}}),
			caption = new Element("div", {id: "lbCaption"}),
			number = new Element("div", {id: "lbNumber"}),
			new Element("div", {styles: {clear: "both"}})
		);
	});

	function zoom(){
		//close();
		//openImageZoomed();
		_zoomed = 1-_zoomed;
		changeImage(activeImage);
		return false;
	}

	function edit(){
		var link = $('link'+_currentIdx);

		if (link){
			var newFilename = $('renameEdit').value.trim();

			if (newFilename){
				var oldName = link.rel;

				lockPage();
				new Request({
					url: siteroot+'xml/filemgr.php?cmd=rename&path='+urlEncodeFilename(oldName)+'&newname='+urlEncodeFilename(newFilename),
					method:'get',
					onSuccess: function(t){
						if (t!=''){
							// error handling
							if (t=='E_CHARS') {
								alert('Neatļauti simboli nosaukumā (/,\\,*,:,?,>,<,|,")');
							} else if (t=='E_EXISTS') {
								alert('Tāds fails vai direktorija jau eksistē');
							} else {
								alert(t);
							}
						} else {
							var link = $('link'+_currentIdx);
							link.innerHTML = newFilename;

							var pathparts = oldName.split('/');
							if (pathparts.length>1){
								var filename = pathparts.splice(-1,1);
							} else {
								var filename = oldName;
							}

							images[activeImage][0] = images[activeImage][0].replace(urlEncodeFilename(filename),urlEncodeFilename(newFilename));

							var oldPath = link.rel;
							var pathparts = oldPath.split('/');
							if (pathparts.length>1){
								//pathparts[pathparts.length-1] = '';
								//pathparts = pathparts.splice(-1,1);
								pathparts.splice(-1,1);
								var parts = pathparts.join('/') + '/' + newFilename;
								link.rel = parts;
							} else {
								link.rel = newFilename;
							}
						}

						unlockPage();
					},
					onFailure: function(t){
						unlockPage();
					}
				}).send();
			}
		}
	}

	/*
		Internal functions
	*/

	function position() {
		var scroll = win.getScroll(), size = win.getSize();
		$$(center, bottomContainer).setStyle("left", scroll.x + (size.x / 2));
		if (compatibleOverlay) overlay.setStyles({left: scroll.x, top: scroll.y, width: size.x, height: size.y});
	}

	function setup(open) {
		["object", ie6 ? "select" : "embed"].forEach(function(tag) {
			Array.forEach(document.getElementsByTagName(tag), function(el) {
				if (open) el._slimbox = el.style.visibility;
				el.style.visibility = open ? "hidden" : el._slimbox;
			});
		});

		overlay.style.display = open ? "" : "none";

		var fn = open ? "addEvent" : "removeEvent";
		win[fn]("scroll", position)[fn]("resize", position);
		document[fn]("keydown", keyDown);
	}

	function keyDown(event) {
		var code = event.code;
		if (_slimboxIsEditing) {
			return true;
		} else {
			// Prevent default keyboard action (like navigating inside the page)
			return options.closeKeys.contains(code) ? close()
				: options.nextKeys.contains(code) ? next()
				: options.previousKeys.contains(code) ? previous()
				: false;
		}
	}

	function previous() {
		return changeImage(prevImage);
	}

	function next() {
		return changeImage(nextImage);
	}

	function changeImage(imageIndex) {
		if (imageIndex >= 0) {
			activeImage = imageIndex;
			activeURL = images[imageIndex][0];
			if (_zoomed) activeURL+='&fullheight=1';
			//alert(activeURL);
			prevImage = (activeImage || (options.loop ? images.length : 0)) - 1;
			nextImage = ((activeImage + 1) % images.length) || (options.loop ? 0 : -1);

			stop();
			center.className = "lbLoading";

			preload = new Image();
			preload.onload = animateBox;
			preload.src = activeURL;
		}

		return false;
	}

	function animateBox() {
		center.className = "";
		fxImage.set(0);
		image.setStyles({backgroundImage: "url(" + activeURL + ")", display: ""});
		sizer.setStyle("width", preload.width);
		$$(sizer, prevLink, nextLink).setStyle("height", preload.height);

		var url = images[activeImage][0].split("&");
		var path = url[1].replace("path=","");
		
		var idx = images[activeImage][2];		
		_currentIdx = idx;
		
		_slimboxIsEditing=false;
		
//		alert(images[activeImage][0]);

		//caption.set("html", images[activeImage][1] || "");

		var pathparts = path.split('/');
		if (pathparts.length>1){
			var filename = pathparts.splice(-1,1);
		} else {
			var filename = path;
		}
		
		if (options.allowEdit){
			caption.set("html", "<input class=\"renameEdit\" type=\"text\" id=\"renameEdit\" value=\""+(urlDecodeFilename(filename) || "")+"\" onfocus=\"_slimboxIsEditing=true\" onblur=\"_slimboxIsEditing=false\" />");
			editBtn.style.display = "";
		} else {
			caption.set("html", filename || "");			
			editBtn.style.display = "none";
		}
		
		number.set("html", (((images.length > 1) && options.counterText) || "").replace(/{x}/, activeImage + 1).replace(/{y}/, images.length));

		if (prevImage >= 0) preloadPrev.src = images[prevImage][0];
		if (nextImage >= 0) preloadNext.src = images[nextImage][0];

		centerWidth = image.offsetWidth;
		if (centerWidth<200) centerWidth=200;
		centerHeight = image.offsetHeight;

		var bottomContainerHeight = 22+10+10;

		var top = Math.max(0, middle - (centerHeight / 2)), check = 0, fn;
		if (center.offsetHeight != centerHeight) {
			//check = fxResize.start({height: centerHeight, top: top});
			check = fxResize.start({height: centerHeight, top: top+22+10+10});
		}
		if (center.offsetWidth != centerWidth) {
			check = fxResize.start({width: centerWidth, marginLeft: -centerWidth/2});
		}
		fn = function() {
			//bottomContainer.setStyles({width: centerWidth, top: top + centerHeight, marginLeft: -centerWidth/2, visibility: "hidden", display: ""});
			bottomContainer.setStyles({width: centerWidth, top: top, marginLeft: -centerWidth/2, visibility: "hidden", display: ""});
			fxImage.start(1);
		};
		if (check) {
			fxResize.chain(fn);
		}
		else {
			fn();
		}
	}

	function animateCaption() {
		if (prevImage >= 0) prevLink.style.display = "";
		if (nextImage >= 0) nextLink.style.display = "";
		fxBottom.set(-bottom.offsetHeight).start(0);
		bottomContainer.style.visibility = "";
	}

	function stop() {
		preload.onload = $empty;
		preload.src = preloadPrev.src = preloadNext.src = activeURL;
		fxResize.cancel();
		fxImage.cancel();
		fxBottom.cancel();
		$$(prevLink, nextLink, image, bottomContainer).setStyle("display", "none");
	}

	function close() {
		if (activeImage >= 0) {
			stop();
			activeImage = prevImage = nextImage = -1;
			center.style.display = "none";
			fxOverlay.cancel().chain(setup).start(0);
		}

		return false;
	}

	/*
		API
	*/

	Element.implement({
		slimbox: function(_options, linkMapper) {
			// The processing of a single element is similar to the processing of a collection with a single element
			$$(this).slimbox(_options, linkMapper);

			return this;
		}
	});

	Elements.implement({
		/*
			options:	Optional options object, see Slimbox.open()
			linkMapper:	Optional function taking a link DOM element and an index as arguments and returning an array containing 2 elements:
					the image URL and the image caption (may contain HTML)
			linksFilter:	Optional function taking a link DOM element and an index as arguments and returning true if the element is part of
					the image collection that will be shown on click, false if not. "this" refers to the element that was clicked.
					This function must always return true when the DOM element argument is "this".
		*/
		slimbox: function(_options, linkMapper, linksFilter) {
			linkMapper = linkMapper || function(el) {
				return [el.href, el.title];
			};

			linksFilter = linksFilter || function() {
				return true;
			};

			var links = this;

			links.removeEvents("click").addEvent("click", function() {
				// Build the list of images that will be displayed
				var filteredLinks = links.filter(linksFilter, this);
				return Slimbox.open(filteredLinks.map(linkMapper), filteredLinks.indexOf(this), _options);
			});

			return links;
		}
	});

	return {
		open: function(_images, startImage, _options) {
			options = $extend({
				loop: false,				// Allows to navigate between first and last images
				overlayOpacity: 0.8,			// 1 is opaque, 0 is completely transparent (change the color in the CSS file)
				overlayFadeDuration: 0,		// Duration of the overlay fade-in and fade-out animations (in milliseconds)
				resizeDuration: 0,			// Duration of each of the box resize animations (in milliseconds)
				resizeTransition: false,		// false uses the mootools default transition
				initialWidth: 250,			// Initial width of the box (in pixels)
				initialHeight: 250,			// Initial height of the box (in pixels)
				imageFadeDuration: 0,			// Duration of the image fade-in animation (in milliseconds)
				captionAnimationDuration: 0,		// Duration of the caption animation (in milliseconds)
				counterText: "Attēls {x} no {y}",	// Translate or change as you wish, or set it to false to disable counter text for image groups
				closeKeys: [27, 88, 67],		// Array of keycodes to close Slimbox, default: Esc (27), 'x' (88), 'c' (67)
				previousKeys: [37, 80],			// Array of keycodes to navigate to the previous image, default: Left arrow (37), 'p' (80)
				nextKeys: [39, 78],			// Array of keycodes to navigate to the next image, default: Right arrow (39), 'n' (78)
				allowEdit: false
			}, _options || {});

			// Setup effects
			fxOverlay = new Fx.Tween(overlay, {property: "opacity", duration: options.overlayFadeDuration});
			fxResize = new Fx.Morph(center, $extend({duration: options.resizeDuration, link: "chain"}, options.resizeTransition ? {transition: options.resizeTransition} : {}));
			fxImage = new Fx.Tween(image, {property: "opacity", duration: options.imageFadeDuration, onComplete: animateCaption});
			fxBottom = new Fx.Tween(bottom, {property: "margin-top", duration: options.captionAnimationDuration});

			// The function is called for a single image, with URL and Title as first two arguments
			if (typeof _images == "string") {
				_images = [[_images, startImage]];
				startImage = 0;
			}

			middle = win.getScrollTop() + (win.getHeight() / 2);
			centerWidth = options.initialWidth;
			centerHeight = options.initialHeight;
			center.setStyles({top: Math.max(0, middle - (centerHeight / 2)), width: centerWidth, height: centerHeight, marginLeft: -centerWidth/2, display: ""});
			compatibleOverlay = ie6 || (overlay.currentStyle && (overlay.currentStyle.position != "fixed"));
			if (compatibleOverlay) overlay.style.position = "absolute";
			fxOverlay.set(0).start(options.overlayOpacity);
			position();
			setup(1);

			_slimboxIsEditing = false;

			images = _images;
			options.loop = options.loop && (images.length > 1);
			return changeImage(startImage);
		}
	};

})();
