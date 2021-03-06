/**
 * hex.core.js
 */
(function(window, document, undefined) {

    var
            hex = window.hex = {
                version: '0.22'
            },
    join = Array.prototype.join,
            slice = Array.prototype.slice,
            has = Object.prototype.hasOwnProperty;

    /**
     * Anonymous function used in constructing objects from prototypes.
     */
    function anonymous() {
    }

    /**
     * Extend one object with the properties of any other object(s).
     * @param obj The object to extend.
     * @param args Additional arguments - the objects from which to copy properties.
     * @return The object which was extended.
     */
    function extend(obj /*, args ... */) {
        for (var i = 0, l = arguments.length; i < l; i++) {
            var other = arguments[i];
            if (other) {
                for (var k in other) {
                    if (has.call(other, k)) {
                        obj[k] = other[k];
                    }
                }
            }
        }
        return obj;
    }
    hex.extend = extend;

    extend(hex, {
        /**
         * Creates a new object with the specified prototypal parent, exteded by provided additional object arguments.
         * @param parent The prototypal parent object.
         * @param args Any number of additonal arguments (optional).
         * @return A new object with the prototypal parent set, extended by the provided args.
         */
        create: function create(parent /*, args ... */) {
            if (!parent) {
                throw "no parent supplied";
            }
            var args = slice.call(arguments, 1);
            anonymous.prototype = parent;
            var obj = new anonymous();
            if (!args.length) {
                return obj;
            }
            args.unshift(obj);
            return extend.apply(undefined, args);
        },
        /**
         * Key method, for making a key string out of scalar parameters.
         * @param args Any number of scalar arguments.
         * @return A string containing the arguments concatenated by a separator.
         */
        key: function key( /* args ... */ ) {
            return join.call(arguments, ',');
        },
        /**
         * Log arguments if the browser supports it.
         * @param args Any number of arguments to log.
         */
        log: function log( /* args ... */ ) {
            if (this.debug && window.console) {
                console.log.apply(console, arguments);
            }
        }

    });

})(window, window.document);

/**
 * hex.element.js
 */
(function(hex, undefined) {

    hex.extend(hex, {
        /**
         * Determines the real on-screen position of a DOM element.
         * @see http://www.quirksmode.org/js/findpos.html
         * @param elem The DOM element to inspect.
         * @return An object with x and y properties to represent the position.
         */
        position: function position(elem) {
            var
                    left = elem.offsetLeft,
                    top = elem.offsetTop;
            elem = elem.offsetParent;
            while (elem) {
                left += elem.offsetLeft;
                top += elem.offsetTop;
                elem = elem.offsetParent;
            }
            return {
                x: left,
                y: top
            };
        },
        /**
         * Determines the size of a DOM element.
         * @param elem The DOM element to inspect.
         * @return An object with x and y properties to represent the dimensions.
         */
        size: function size(elem) {
            return {
                x: elem.offsetWidth,
                y: elem.offsetHeight
            };
        },
        /**
         * Retrieves the computed style of a given DOM element.
         * @see http://www.quirksmode.org/dom/getstyles.html
         * @param elem The DOM element to inspect.
         * @param property The CSS property to look up.
         * @return The computed style value.
         */
        style: function style(elem, property) {
            var value;
            if (elem.currentStyle) {
                value = elem.currentStyle[property];
            } else if (window.getComputedStyle) {
                value = document.defaultView.getComputedStyle(elem, null).getPropertyValue(property);
            }
            return value;
        }

    });

})(window.hex);

/**
 * hex.event.js
 * Library methods for DOM and non-DOM events.
 */
(function(hex, undefined) {

    var
            slice = Array.prototype.slice;

    /**
     * The rich event prototype for non-DOM (hex) events.
     */
    var HexEvent = {
    };

    hex.extend(hex, {
        /**
         * The evented prototype, for non-DOM objects which support handling non-DOM events.
         */
        evented: {
            /**
             * Adds an event handler.
             * @param type The type of event to which to respond.
             * @param handler The function to execute.
             * @return this.
             */
            addEvent: function addEvent(type, handler, label) {
                if (!this.events) {
                    this.events = {};
                }
                var handlers = this.events[type];
                if (handlers === undefined) {
                    handlers = this.events[type] = {};
                }
                if (typeof label === 'undefined')
                {
                    var d = new Date();
                    label = "item" + d.getTime();
                }

                handlers[label] = handler;
                return this;
            },
            removeEvent: function removeEvent(type, label) {
                if (typeof this.events[type][label] === 'undefined') {
                    return;
                }
                delete this.events[type][label];

                return this;
            },
            /**
             * Triggers an event to fire.
             * Note: Exceptions thrown in handlers will not interrupt other handlers.
             * @param type The type of event to fire.
             * @param args Any additional arguments to pass to handlers.
             * @return An object containing information about the callback execution, or false if there was nothing to do.
             */
            trigger: function trigger(type /*, args ... */) {

                if (!this.events || !this.events[type]) {
                    return false;
                }

                var
                        timeout = 10,
                        handlers = this.events[type],
                        args = slice.call(arguments, 0),
                        i = 0,
                        l = 0,
                        key,
                        prevented = false,
                        e = args[0] = hex.create(HexEvent, {
                    type: type,
                    preventDefault: function preventDefault() {
                        prevented = true;
                    }
                }),
                errors = [];

                for (key in handlers) {
                    if (handlers.hasOwnProperty(key))
                        l++;
                }

                while (i < l) {
                    try {
                        for (event in handlers)
                        {
                            handlers[event].apply(this, args);
                            i++;
                        }
                    } catch (err) {
                        errors[errors.length] = err;
                        setTimeout(function() {
                            throw err;
                        }, timeout++);
                    }
                }

                return {
                    event: e,
                    errors: errors,
                    prevented: prevented,
                    args: args
                };

            },
            /**
             * Queue up an event to fire later (using the fire method).
             * @param type The type of event to fire.
             * @param args Any additional arguments to pass to handlers.
             */
            queue: function queue(type /*, args ... */) {
                var q = this.eventqueue;
                if (!q) {
                    q = this.eventqueue = [];
                }
                q[q.length] = slice.call(arguments, 0);
            },
            /**
             * Sequentially trigger any previously queued events.
             */
            fire: function fire() {
                var q = this.eventqueue;
                if (!q || !q.length) {
                    return;
                }
                while (q.length) {
                    this.trigger.apply(this, q.shift());
                }
            }

        }

    });

    /**
     * The rich event "prototype" for DOM events.
     */
    var DOMEvent = {
        /**
         * Grab the actual target element of the masked event.
         * @return The target element.
         */
        getTarget: function getTarget() {
            var t = this.target || this.srcElement;
            if (!t) {
                return undefined;
            }
            return (t.nodeType === 3 ? t.parentNode : t);
        },
        /**
         * Determine whether the event ocurred within the bounds of the provided element.
         * @param elem DOM element for relative position calculation (optional).
         * @return Object with an x and y property for the screen location in pixels.
         */
        inside: function inside(elem) {

            // Details about the event coordinates and location/size of the element 
            var
                    pos = this.mousepos(),
                    position = hex.position(elem),
                    size = hex.size(elem);

            // Determine whether the event happened inside the bounds of the element
            return (
                    pos.x > position.x &&
                    pos.x < position.x + size.x &&
                    pos.y > position.y &&
                    pos.y < position.y + size.y
                    );

        },
        /**
         * Determine the screen coordinates for a mouse event (click, mouseover, etc).
         * @see http://www.quirksmode.org/js/events_properties.html#position
         * @see http://developer.apple.com/safari/library/documentation/appleapplications/reference/safariwebcontent/handlingevents/handlingevents.html
         * @param elem DOM element for relative position calculation (optional).
         * @return Object with an x and y property for the screen location in pixels.
         */
        mousepos: function mousepos(elem) {

            var
                    e = this.event,
                    touch,
                    x = 0,
                    y = 0;

            if (e.touches && e.touches.length) {
                touch = e.touches[0];
                x = touch.pageX;
                y = touch.pageY;
            } else if (e.changedTouches && e.changedTouches.length) {
                touch = e.changedTouches[0];
                x = touch.pageX;
                y = touch.pageY;
            } else if (e.pageX !== undefined && e.pageY !== undefined) {
                x = e.pageX;
                y = e.pageY;
            } else if (e.clientX !== undefined && e.clientY !== undefined) {
                x = e.clientX + document.body.scrollLeft + document.documentElement.scrollLeft;
                y = e.clientY + document.body.scrollTop + document.documentElement.scrollTop;
            }

            if (elem) {
                var pos = hex.position(elem);
                x = x - pos.x;
                y = y - pos.y;
            }

            return {
                x: x,
                y: y
            };

        },
        /**
         * Prevent the browser default action.
         */
        preventDefault: function preventDefault() {
            var e = this.event;
            if (e.preventDefault) {
                e.preventDefault();
            } else {
                e.returnValue = false;
            }
        },
        /**
         * Stop the event from propagating.
         */
        stopPropagation: function stopPropagation() {
            var e = this.event;
            if (e.stopPropagation) {
                e.stopPropagation();
            } else {
                e.cancelBubble = true;
            }
        }

    };

    var Handler;

    if (document.addEventListener) {

        /**
         * The Handler prototype.
         */
        Handler = {
            /**
             * Remove the handler from the object to which it was previously attached.
             */
            remove: function remove() {
                return this.elem.removeEventListener(this.type, this.callback);
            }

        };

        hex.extend(hex, {
            /**
             * Adds an event handler to the supplied DOM element.
             * @param elem The DOM element to which to attach the event.
             * @param type String representing the type of event to hook (ex: "click").
             * @param handler Function to handle the event.
             * @return Handler instance .
             */
            addEvent: function addEvent(elem, type, handler) {
                function callback(e) {
                    var wrapperEvent = e;
                    try {
                        wrapperEvent = hex.create(e);
                    } catch (err) {
                    }
                    hex.extend(wrapperEvent, DOMEvent, {event: e});
                    return handler.call(elem, wrapperEvent);
                }
                elem.addEventListener(type, callback, false);
                return hex.create(Handler, {
                    callback: callback,
                    elem: elem,
                    handler: handler,
                    type: type
                });
            },
            /**
             * Removes an event handler from the supplied DOM element.
             * @param elem The DOM element to which to remove the event.
             * @param type String representing the type of event to hook (ex: "click").
             * @param handler Function to remove.
             */
            removeEvent: function removeEvent(elem, type, handler) {
                elem.removeEventListener(type, handler, false);
            }

        });

    } else if (document.attachEvent) {


        /**
         * The Handler prototype.
         */
        Handler = {
            /**
             * Remove the handler from the object to which it was previously attached.
             */
            remove: function remove() {
                return this.elem.detachEvent("on" + this.type, this.callback);
            }

        };

        hex.extend(hex, {
            /**
             * Adds an event handler to the supplied DOM element.
             * @param elem The DOM element to which to attach the event.
             * @param type String representing the type of event to hook (ex: "click").
             * @param handler Function to handle the event.
             * @return Handler instance .
             */
            addEvent: function addEvent(elem, type, handler) {

                function callback() {
                    var e = window.event;
                    return handler.call(elem, hex.extend({}, e, DOMEvent, {event: e}));
                }

                function remove() {
                    elem.detachEvent("on" + type, callback);
                    window.detachEvent("onunload", remove);
                }

                elem.attachEvent("on" + type, callback);
                window.attachEvent("onunload", remove);

                return hex.create(Handler, {
                    callback: callback,
                    elem: elem,
                    handler: handler,
                    type: type
                });

            },
            /**
             * Removes an event handler from the supplied DOM element.
             * @param elem The DOM element to which to remove the event.
             * @param type String representing the type of event to hook (ex: "click").
             * @param handler Function to remove.
             */
            removeEvent: function removeEvent(elem, type, handler) {
                elem.detachEvent("on" + type, handler);
            }

        });

    }

})(window.hex);

/**
 * hex.grid.js
 */
(function(hex, undefined) {

    /**
     * The Grid prototype.
     */
    var Grid = hex.create(hex.evented, {
        /**
         * Default option values.
         */
        defaults: {
            // Type of grid to construct.
            type: "hexagonal",
            // Threshold for tiletap event (ms)
            tapthreshold: 400

        },
        /**
         * Set the origin position for the grid element.
         * @param x The horizontal position from the left in pixels.
         * @param y The vertical position from the top in pixels.
         */
        reorient: function reorient(x, y) {
            this.origin.x = +x;
            this.origin.y = +y;
            this.root.style.left = x + "px";
            this.root.style.top = y + "px";
            this.elem.style.backgroundPosition = x + "px " + y + "px";
            this.mapGrid.style.backgroundPosition = x + "px " + y + "px";
            this.mapLayer.style.backgroundPosition = x + "px " + y + "px";
        },
        moveToCoords: function moveToCoords(x, y) {
            var inv = this.screenpos(x, y);
            this.origin.x = -1 * inv.x;
            this.origin.y = -1 * inv.y;
            this.root.style.left = -1 * inv.x + "px";
            this.root.style.top = -1 * inv.y + "px";
            this.elem.style.backgroundPosition = -1 * inv.x + "px " + -1 * inv.y + "px";
            this.mapGrid.style.backgroundPosition = -1 * inv.x + "px " + -1 * inv.y + "px";
            this.mapLayer.style.backgroundPosition = -1 * inv.x + "px " + -1 * inv.y + "px";
        },
        centerOnCoords: function centerOnCoords(x, y) {
            var inv = this.screenpos(x, y);
            inv.x = (-1 * inv.x) + Math.ceil(parseInt($( "#ww-mapa" ).width()) / 2);
            inv.y = (-1 * inv.y) + Math.ceil(parseInt($( "#ww-mapa" ).height()) / 2);
            this.origin.x = inv.x;
            this.origin.y = inv.y;
            this.elem.style.backgroundPosition = inv.x + "px " + inv.y + "px";
            this.mapGrid.style.backgroundPosition = inv.x + "px " + inv.y + "px";
            this.mapLayer.style.backgroundPosition = inv.x + "px " + inv.y + "px";
            this.root.style.left = -1 * inv.x + "px";
            this.root.style.top = -1 * inv.y + "px";
            this.reorient(inv.x,inv.y);
        }

    });

    hex.extend(hex, {
        /**
         * Create a grid for a particular DOM element.
         * @param elem DOM element over which to superimpose a grid.
         * @param options Options hash defining characteristics of the grid.
         * @return A grid object.
         */
        grid: function grid(elem, options) {

            // Confirm that an element was supplied
            if (!elem || elem.nodeType !== 1) {
                throw "no DOM element supplied";
            }

            // Combine options to default values
            options = hex.extend({}, Grid.defaults, options);

            // Check that the particular grid type provides all reqired functions
            if (hex.grid[options.type] === undefined) {
                throw "hex.grid." + options.type + " does not exist";
            }

            // Setting necessary grid element characteristics
            var position = hex.style(elem, "position");
            if (position !== "relative" && position !== "absolute") {
                elem.style.position = "relative";
            }
            if (hex.style(elem, "overflow") !== "hidden") {
                elem.style.overflow = "hidden";
            }

            // Create and attach the root element
            var root = document.createElement("div");
            root.style.position = "absolute";
            root.style.left = "0px";
            root.style.top = "0px";
            root.style.overflow = "visible";
            root.style.zIndex = 10;
            root.className = "map-assets"
            elem.appendChild(root);

            // Element to show background grid
            var mapGrid = document.createElement("div");
            mapGrid.style.left = "0px";
            mapGrid.style.top = "0px";
            mapGrid.className = mapGrid.className + " map-grid"
            elem.appendChild(mapGrid);

            // Element to show background map
            var mapLayer = document.createElement("div");
            mapLayer.style.left = "0px";
            mapLayer.style.top = "0px";
            mapLayer.className = mapLayer.className + " map-bg"
            elem.appendChild(mapLayer);

            // Create the grid object
            var g = hex.create(
                    Grid, {
                        events: {},
                        origin: {
                            x: 0,
                            y: 0
                        }
                    },
            hex.grid[options.type],
                    options, {
                        elem: elem,
                        mapGrid: mapGrid,
                        mapLayer: mapLayer,
                        root: root
                    }
            );

            g.centerOnCoords(options.startX, options.startY);

            // Keep track of the last tile hovered for mouseover purposes
            var lastTile = {
                x: null,
                y: null
            };

            // Keep track of the panning state
            var pan = {
                enabled: true,
                panning: false,
                x: null,
                y: null
            };

            // Handler for any mouse movement events
            function mousemove(event) {

                var
                        // Determine whether the event happened inside the bounds of the grid element
                        inside = event.inside(elem),
                        // Determine mouse position
                        mousepos = event.mousepos(elem),
                        pos = {
                            x: mousepos.x - g.origin.x,
                            y: mousepos.y - g.origin.y
                        };

                // Handle panning
                if (pan.panning) {
                    if (pan.enabled && inside) {

                        var
                                px = pos.x - pan.x,
                                py = pos.y - pan.y;
                        /*
                         * Ogranicza mo??liwo???? przesuwania mapy do jej obszaru
                         */
                        //if ((px * -1) >= (4800-parseInt(screen.width)) || py >= 0 || px >= 0 || (py * -1) >= (4800-parseInt(screen.height))) return ;

                        root.style.left = px + "px";
                        root.style.top = py + "px";
                        elem.style.backgroundPosition = px + "px " + py + "px";
                        mapGrid.style.backgroundPosition = px + "px " + py + "px";
                        mapLayer.style.backgroundPosition = px + "px " + py + "px";

                        g.trigger("panmove", mousepos.x - pan.x - 2 * g.origin.x, mousepos.y - pan.y - 2 * g.origin.y);
                    }
                    return;
                }

                var
                        tileover = g.events.tileover,
                        tileout = g.events.tileout,
                        gridover = g.events.gridover,
                        gridout = g.events.gridout;

                // Short-circuit if there are no tile or grid events
                if (!tileover && !tileout && !gridover && !gridout) {
                    return;
                }

                var
                        // Determine the grid-centric coordinates of the latest actioned tile
                        trans = g.translate(pos.x, pos.y);

                // Short-circuit if we're inside and there's nothing to do
                // NOTE: For example, on a mouseout or mouseover where the mousemove already covered it
                if (inside && lastTile.x === trans.x && lastTile.y === trans.y) {
                    return;
                }

                // Queue up tileout callbacks if there are any
                if (tileout && lastTile.x !== null && lastTile.y !== null) {
                    g.queue("tileout", lastTile.x, lastTile.y, event);
                }

                // Queue up gridout callbacks if applicable
                if (!inside && gridout && lastTile.x !== null && lastTile.y !== null) {
                    g.queue("gridout", lastTile.x, lastTile.y, event);
                }

                if (inside) {

                    // Queue up gridover callbacks if applicable
                    if (gridover && lastTile.x === null && lastTile.y === null) {
                        g.queue("gridover", trans.x, trans.y, event);
                    }

                    // Queue up tileover callbacks if there are any
                    if (tileover) {
                        g.queue("tileover", trans.x, trans.y, event);
                    }

                    lastTile.x = trans.x;
                    lastTile.y = trans.y;

                } else {

                    lastTile.x = null;
                    lastTile.y = null;

                }

                // Fire off queued events
                g.fire();

            }

            // Add DOM event handlers to grid element for mouse movement
            hex.addEvent(elem, "mousemove", mousemove);
            hex.addEvent(elem, "mouseover", mousemove);
            hex.addEvent(elem, "mouseout", mousemove);
            hex.addEvent(elem, "touchmove", mousemove);
            hex.addEvent(elem, "touchstart", mousemove);
            hex.addEvent(elem, "touchend", mousemove);
            hex.addEvent(elem, "MozTouchDown", mousemove);
            hex.addEvent(elem, "MozTouchMove", mousemove);
            hex.addEvent(elem, "MozTouchUp", mousemove);
            hex.addEvent(elem, "MozTouchRelease", mousemove);

            // Keep track of last tile mousedown'ed on
            var downTile = {
                x: null,
                y: null
            };

            // Keep track of when the last tiledown event happened
            var downTime = null;

            // Handler for any mouse button events
            function mousebutton(e) {

                var event = e.event;

                // Short-circuit if the event happened outside the bounds of the grid element.
                if (!e.inside(elem)) {
                    return;
                }

                // Determine the event type and coordinates
                var
                        type = event.type,
                        mousepos = e.mousepos(elem);

                // Prevents browser-native dragging of child elements (ex: dragging an image)
                if (type === "mouseup" || type === "mousedown") {
                    e.preventDefault();
                }

                // prevent touch-hold-copy behavior
                // also allows multi-touch gestures (like pinch-zoom) to occur unabaited
                if (type === "touchstart") {
                    if (!event.touches || event.touches.length < 2) {
                        e.preventDefault();
                    }
                }

                // Begin panning
                if (!pan.panning && (
                        type === "mousedown" ||
                        type === "touchstart" ||
                        type === "MozTouchDown"
                        )) {
                    tyt = g.translate(mousepos.x - g.origin.x, mousepos.y - g.origin.y);
                    if (tyt.x < 0 || tyt.y > (Math.ceil(tyt.x / 2) * -1) || tyt.y < (-113 + Math.ceil(tyt.x / 2) * -1) || tyt.x > 132)
                        return;

                    pan.panning = true;
                    pan.x = mousepos.x - 2 * g.origin.x;
                    pan.y = mousepos.y - 2 * g.origin.y;
                    elem.style.cursor = "move";
                    g.queue("panstart", event);
                }
                /*
                 * Ogranicza mo??liwo???? przesuwania mapy do jej obszaru
                 */
                //var chpx = mousepos.x - g.origin.x - pan.x;
                //var chpy = mousepos.y - g.origin.y - pan.y;
                //if ((chpx * -1) >= (4800-parseInt(screen.width)) || chpy >= 0 || chpx >= 0 || (chpy * -1) >= (4800-parseInt(screen.height))) 
                //{
                //  return;
                //}
                // Cease panning
                if (pan.panning && (
                        type === "mouseup" ||
                        type === "touchend" ||
                        type === "MozTouchUp" ||
                        type === "MozTouchRelease"
                        )) {

                    // cancel tiletap if mouse has moved too far
                    var
                            diffx = mousepos.x - 2 * g.origin.x - pan.x,
                            diffy = mousepos.y - 2 * g.origin.y - pan.y;
                    diffx = diffx < 0 ? -diffx : diffx;
                    diffy = diffy < 0 ? -diffy : diffy;
                    if (diffx > g.tileWidth || diffy > g.tileHeight) {
                        downTime = null;
                    }

                    // reorient if panning is still enabled
                    if (pan.enabled) {
                        g.queue("panend", mousepos.x - pan.x - 2 * g.origin.x, mousepos.y - pan.y - 2 * g.origin.y, event);
                        g.reorient(
                                mousepos.x - g.origin.x - pan.x,
                                mousepos.y - g.origin.y - pan.y
                                );
                    }

                    pan.enabled = true;
                    pan.panning = false;
                    pan.x = null;
                    pan.y = null;
                    elem.style.cursor = "";
                }

                var
                        tiledown = g.events.tiledown,
                        tileup = g.events.tileup,
                        tileclick = g.events.tileclick,
                        tiletap = g.events.tiletap;

                // Short-circuit if there are no tiledown, tileup, tileclick or tiletap event handlers
                if (!tiledown && !tileup && !tileclick && !tiletap) {
                    g.fire();
                    return;
                }

                var
                        // Adjusted mouse position
                        pos = {
                            x: mousepos.x - g.origin.x,
                            y: mousepos.y - g.origin.y
                        },
                // Grid-centric coordinates of the latest actioned tile
                trans = g.translate(pos.x, pos.y);

                if (
                        type === "mousedown" ||
                        type === "touchstart" ||
                        type === "MozTouchDown"
                        ) {

                    downTime = +new Date();

                    // Trigger tiledown callbacks
                    if (tiledown) {
                        g.fire(); // fire any previously queued events
                        var res = g.trigger("tiledown", trans.x, trans.y, event);
                        if (res && res.prevented) {
                            pan.enabled = false;
                        }
                    }

                    // Remember mousedown target (to test for "click" later)
                    downTile.x = trans.x;
                    downTile.y = trans.y;

                } else if (
                        type === "mouseup" ||
                        type === "touchend" ||
                        type === "MozTouchUp" ||
                        type === "MozTouchRelease"
                        ) {

                    // Queue up tileup callbacks
                    if (tileup) {
                        g.queue("tileup", trans.x, trans.y, event);
                    }

                    // Queue up tileclick and tiletap callbacks
                    if (downTile.x === trans.x && downTile.y === trans.y) {
                        if (tileclick) {
                            g.queue("tileclick", trans.x, trans.y, event);
                        }
                        if (tiletap && downTime && (+new Date()) - downTime < g.tapthreshold) {
                            g.queue("tiletap", trans.x, trans.y, event);
                        }
                    }

                    // Clear mousedown target
                    downTile.x = null;
                    downTile.y = null;

                    // Clear tiledown time
                    downTime = null;

                }

                // Fire off any queued events
                g.fire();

            }

            // Add DOM event handlers to grid element for mouse movement
            hex.addEvent(elem, "mousedown", mousebutton);
            hex.addEvent(elem, "mouseup", mousebutton);
            hex.addEvent(elem, "touchstart", mousebutton);
            hex.addEvent(elem, "touchend", mousebutton);
            hex.addEvent(elem, "MozTouchDown", mousemove);
            hex.addEvent(elem, "MozTouchUp", mousemove);
            hex.addEvent(elem, "MozTouchRelease", mousemove);

            // A mouseup event anywhere on the document outside the grid element while panning should:
            // * cease panning,
            // * fire a gridout event, and
            // * clear the mousedown and lasttile targets
            function mouseup(event) {

                // We only care about the mouseup event if the user was panning
                if (!pan.panning) {
                    return;
                }

                // Reorient the board, and cease panning
                g.reorient(
                        parseInt(root.style.left, 10),
                        parseInt(root.style.top, 10)
                        );
                pan.panning = false;
                pan.x = null;
                pan.y = null;
                elem.style.cursor = "";

                // Queue gridout event handlers if applicable
                if (downTile.x !== null && downTile.y !== null && !event.inside(elem)) {
                    g.queue("gridout", downTile.x, downTile.y, event);
                }

                // Clear previously set downTile and lastTile coordinates
                downTile.x = null;
                downTile.y = null;
                lastTile.x = null;
                lastTile.y = null;

                // Clear tiledown time
                downTime = null;

                // Fire off queued events
                g.fire();

            }
            hex.addEvent(document, "mouseup", mouseup);
            hex.addEvent(document, "touchend", mouseup);
            hex.addEvent(document, "gesturestart", mouseup);
            hex.addEvent(document, "gesturechange", mouseup);
            hex.addEvent(document, "gestureend", mouseup);
            hex.addEvent(document, "MozTouchUp", mouseup);
            hex.addEvent(document, "MozTouchRelease", mouseup);

            // A mousewheel event should be captured, and then reorient up or down the height of a tile
            // @see http://www.switchonthecode.com/tutorials/javascript-tutorial-the-scroll-wheel
            function mousewheel(e) {

                var event = e.event;

                // short-circuit if the ctrl key is being pressed (zoom)
                if (event.ctrlKey) {
                    return;
                }

                var
                        // did the event happen inside the bounds of the grid element?
                        inside = e.inside(elem),
                        // was it up or down?
                        wheelData = event.detail ? event.detail * -1 : event.wheelDelta * 0.025,
                        direction = wheelData > 0 ? 1 : wheelData < 0 ? -1 : 0;

                // scroll it
                if (inside && direction) {
                    e.preventDefault();
                    if (event.wheelDeltaX || event.axis && event.axis === event.HORIZONTAL_AXIS) {
                        var deltax = g.tileWidth * direction;
                        g.queue("panstart", event);
                        g.queue("panmove", deltax, 0, event);
                        g.queue("panend", deltax, 0, event);
                        g.reorient(g.origin.x + deltax, g.origin.y);
                    } else {
                        var deltay = g.tileHeight * direction;
                        g.queue("panstart", event);
                        g.queue("panmove", 0, deltay, event);
                        g.queue("panend", 0, deltay, event);
                        g.reorient(g.origin.x, g.origin.y + deltay);
                    }
                }
            }
            //hex.addEvent(elem, "mousewheel", mousewheel);
            //hex.addEvent(elem, "DOMMouseScroll", mousewheel);

            // Perform initialization if grid supports it
            if (g.init) {
                g.init();
            }

            return g;
        }

    });

})(window.hex);


/**
 * hex.grid.hexagonal.js
 */
(function(hex, undefined) {

    var
            floor = Math.floor;

    /**
     * The hexagonal grid prototype.
     */
    hex.grid.hexagonal = {
        /**
         * Determine to which quadrant a given screen coordinate pair corresponds.
         * @param posx The horizontal screen coordinate.
         * @param posy The vertical screen coordinate.
         * @return An object with an x and y property, mapping to the geometry appropriate coordinates of the grid.
         */
        quadrant: function quadrant(posx, posy) {

            var
                    w = this.tileWidth,
                    h = this.tileHeight,
                    qx = floor((posx - w * 0.25) / (w * 0.75)),
                    qy = floor((posy) / h);

            return {
                x: qx,
                y: qy
            };

        },
        /**
         * Given a pair of hex coordinates, calculates the appropriate screen position.
         * @param hexx The horizontal hexagonal grid coordinate.
         * @param hexy The "vertical" hexagonal grid coordinate (30 degrees up from horizontal).
         * @return An object with an x and y property, mapping to the actual screen coordinates.
         */
        screenpos: function screenpos(hexx, hexy) {

            var
                    w = this.tileWidth * 0.75,
                    h = this.tileHeight,
                    sx = hexx * w,
                    sy = -hexy * h - hexx * h * 0.5;

            return {
                x: sx,
                y: sy
            };

        },
        /**
         * Hexagon tile characteristics.
         */
        tileHeight: 42,
        tileWidth: 48,
        /**
         * Translate a pair of x/y screen coordinates into the geometry appropriate coordinates of this grid.
         * @param posx The horizontal screen coordinate.
         * @param posy The vertical screen coordinate.
         * @return An object with an x and y property, mapping to the geometry appropriate coordinates of the grid.
         */
        translate: function translate(posx, posy) {

            // Useful shorthand values
            var
                    w2 = this.tileWidth * 0.5,
                    w4 = w2 * 0.5,
                    w34 = w4 * 3,
                    h = this.tileHeight,
                    h2 = h * 0.5,
                    m = h2 / w4,
                    x,
                    y;

            // Determine the "quadrant" in which the click occurred (there are two types, as discussed later)
            var
                    q = this.quadrant(posx, posy),
                    qx = q.x,
                    qy = q.y;

            // Based on the quadrant, calculate the pixel offsets of the click within the quadrant
            var
                    px = (posx - w4) % w34,
                    py = (posy) % h;
            if (px < 0) {
                px += w34;
            }
            if (py < 0) {
                py += h;
            }
            px -= w2;

            // Mode determined by x quadrant
            if (qx % 2) {

                // |_/|  A-type quadrant
                // | \|

                // Start with simple cases
                x = qx;
                y = (1 - qx) * 0.5 - qy - (py > h2 ? 1 : 0);
                if (px <= 0 || py == h2) {
                    return {
                        x: x,
                        y: y
                    };
                }

                // Make adjustments if click happend in right-hand third of the quadrant
                if (py < h2 && py > (h2 - px * m)) {
                    return {
                        x: x + 1,
                        y: y - 1
                    };
                }
                if (py > h2 && py < (h2 + px * m)) {
                    return {
                        x: x + 1,
                        y: y
                    };
                }

            } else {

                // | \|  B-type quadrant
                // | /|

                // Start with simple case
                x = qx;
                y = -qx * 0.5 - qy;
                if (px <= 0 || py == h2) {
                    return {
                        x: x,
                        y: y
                    };
                }

                // Make adjusments if the click happend in the latter third
                if (py < h2 && py < px * m) {
                    return {
                        x: x + 1,
                        y: y
                    };
                }
                if (py > h2 && py > (h - px * m)) {
                    return {
                        x: x + 1,
                        y: y - 1
                    };
                }
            }

            // fall through case - no adjustments necessary
            return {
                x: x,
                y: y
            };

        }

    };

})(window.hex);


/**
 * hex.region.js
 */
(function(hex, undefined) {

    /**
     * The Region prototype.
     */
    var Region = hex.create(hex.evented);

    hex.extend(hex, {
        /**
         * Create a region associated with a given grid.
         * @param grid The grid to which to associate the region.
         * @param options Options hash defining characteristics of the region.
         * @return A region object.
         */
        region: function region(grid, options) {

            // Confirm that a grid was supplied
            if (!grid) {
                throw "no grid was supplied";
            }

            // Combine options to default values
            options = hex.extend({}, options);

            // Check that the inside() option is a function
            if (typeof options.inside !== "function") {
                throw "options.inside is not a function";
            }

            // Create the region
            var r = hex.create(Region, options, {
                grid: grid
            });

            // Keep track of whether the last tile was inside the region
            var wasInside = false;

            // Add grid movenment events
            grid.addEvent("tileover", function(e, x, y) {
                var inside = r.inside(x, y);
                if (inside !== wasInside) {
                    r.trigger(inside ? "regionover" : "regionout", x, y);
                }
                wasInside = inside;
            });
            grid.addEvent("gridout", function(e, x, y) {
                if (wasInside) {
                    r.trigger("regionout", x, y);
                }
                wasInside = false;
            });

            // Keep track of whether the last moused tile was inside the region
            var downInside = false;

            // Add grid click events
            grid.addEvent("tiledown", function(e, x, y) {
                var inside = r.inside(x, y);
                if (inside) {
                    r.trigger("regiondown", x, y);
                }
                downInside = inside;
            });
            grid.addEvent("tileup", function(e, x, y) {
                if (r.inside(x, y)) {
                    r.trigger("regionup", x, y);
                    if (downInside) {
                        r.trigger("regionclick", x, y);
                    }
                }
            });

            return r;

        }

    });

})(window.hex);


/**
 * hex.sprite.js
 */
(function(hex, undefined) {

    /**
     * The sprite prototype.
     */
    var Sprite = {};

    /**
     * The sprite layer prototype.
     */
    var SpriteLayer = {
        /**
         * Default layer options.
         */
        defaults: {
            // Number of milliseconds between frames
            delay: 100,
            // Whether to continue to animate, or just once through
            repeat: false

        },
        /**
         * Animate the sprite layer.
         * @param options Object containing animation options.
         */
        animate: function animate(options) {

            options = hex.extend({}, SpriteLayer.defaults, options);

            var
                    elem = this.elem,
                    coords = this.coords,
                    x = coords[0],
                    y = coords[1],
                    len = coords[2],
                    width = this.sprite.spritemap.width,
                    repeat = options.repeat,
                    i = 0,
                    timeout;

            function callback() {
                i++;
                if (i >= len) {
                    if (repeat) {
                        i = 0;
                    } else {
                        window.clearTimeout(timeout);
                        return;
                    }
                }
                elem.style.left = (-(x + i) * width) + "px";
            }

            timeout = this.timeout = window.setInterval(callback, options.delay);

        },
        /**
         * Stop layer animation.
         */
        stop: function stop() {

            window.clearTimeout(this.timeout);

        }

    };

    /**
     * The spritemap prototype.
     */
    var SpriteMap = {
        /**
         * Default spritemap options.
         */
        defaults: {
        },
        /**
         * Create a new sprite with specified layers.
         * @param layers Strings indicating what sprite to put on each layer.
         * @return A sprite object.
         */
        sprite: function sprite( /* layers */ ) {

            // Create the sprite
            var s = hex.create(Sprite, {
                spritemap: this
            });

            // Setup the base element
            var base = s.base = document.createElement('div');
            base.className = "sprite";
            hex.extend(base.style, {
                position: "relative",
                overflow: "hidden",
                width: this.width + "px",
                height: this.height + "px"
            });

            // Setup layers
            var layers = s.layers = [];
            for (var i = 0, l = arguments.length; i < l; i++) {

                var
                        type = arguments[i],
                        coords = this.map[type],
                        x = coords[0],
                        y = coords[1],
                        elem = document.createElement('div');

                layers[i] = hex.create(SpriteLayer, {
                    type: type,
                    elem: elem,
                    sprite: s,
                    coords: coords
                });

                hex.extend(elem.style, {
                    position: "absolute",
                    width: this.mapwidth + "px",
                    height: this.mapheight + "px",
                    top: (-y * this.height) + "px",
                    left: (-x * this.width) + "px",
                    backgroundImage: "url('" + this.url + "')",
                    filter: "progid:DXImageTransform.Microsoft.AlphaImageLoader(src='" + this.url + "', sizingMethod='crop')"
                });

                base.appendChild(elem);

            }

            return s;

        }

    };

    hex.extend(hex, {
        /**
         * Build a sprite map.
         * @param options Object containing configuration options.
         * @return A sprite map.
         */
        spritemap: function spritemap(options) {

            // Throw exception if no options were supplied
            if (options === undefined) {
                throw "no options hash was supplied";
            }

            // Extend options with defaults
            options = hex.extend({}, SpriteMap.defaults, options);

            // Determine the dimensions of the image
            var
                    map = options.map,
                    x = 0,
                    y = 0;
            for (var k in map) {
                var coords = map[k];
                if (coords[0] > x) {
                    x = coords[0];
                }
                if (coords[1] > y) {
                    y = coords[1];
                }
            }

            // Create spritemap
            var sm = hex.create(SpriteMap, {
                mapwidth: ((x + 1) * options.width),
                mapheight: ((y + 1) * options.height)
            }, options);

            return sm;
        }

    });

})(window.hex);