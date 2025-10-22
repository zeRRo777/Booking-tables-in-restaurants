<script>
    Sfdump = window.Sfdump || (function(doc) {
        doc.documentElement.classList.add('sf-js-enabled');
        var rxEsc = /([.*+?^${}()|\[\]\/\\])/g,
            idRx = /\bsf-dump-\d+-ref[012]\w+\b/,
            keyHint = 0 <= navigator.platform.toUpperCase().indexOf('MAC') ? 'Cmd' : 'Ctrl',
            addEventListener = function(e, n, cb) {
                e.addEventListener(n, cb, false);
            };
        if (!doc.addEventListener) {
            addEventListener = function(element, eventName, callback) {
                element.attachEvent('on' + eventName, function(e) {
                    e.preventDefault = function() {
                        e.returnValue = false;
                    };
                    e.target = e.srcElement;
                    callback(e);
                });
            };
        }

        function toggle(a, recursive) {
            var s = a.nextSibling || {},
                oldClass = s.className,
                arrow, newClass;
            if (/\bsf-dump-compact\b/.test(oldClass)) {
                arrow = '&#9660;';
                newClass = 'sf-dump-expanded';
            } else if (/\bsf-dump-expanded\b/.test(oldClass)) {
                arrow = '&#9654;';
                newClass = 'sf-dump-compact';
            } else {
                return false;
            }
            if (doc.createEvent && s.dispatchEvent) {
                var event = doc.createEvent('Event');
                event.initEvent('sf-dump-expanded' === newClass ? 'sfbeforedumpexpand' : 'sfbeforedumpcollapse', true, false);
                s.dispatchEvent(event);
            }
            a.lastChild.innerHTML = arrow;
            s.className = s.className.replace(/\bsf-dump-(compact|expanded)\b/, newClass);
            if (recursive) {
                try {
                    a = s.querySelectorAll('.' + oldClass);
                    for (s = 0; s < a.length; ++s) {
                        if (-1 == a[s].className.indexOf(newClass)) {
                            a[s].className = newClass;
                            a[s].previousSibling.lastChild.innerHTML = arrow;
                        }
                    }
                } catch (e) {}
            }
            return true;
        };

        function collapse(a, recursive) {
            var s = a.nextSibling || {},
                oldClass = s.className;
            if (/\bsf-dump-expanded\b/.test(oldClass)) {
                toggle(a, recursive);
                return true;
            }
            return false;
        };

        function expand(a, recursive) {
            var s = a.nextSibling || {},
                oldClass = s.className;
            if (/\bsf-dump-compact\b/.test(oldClass)) {
                toggle(a, recursive);
                return true;
            }
            return false;
        };

        function collapseAll(root) {
            var a = root.querySelector('a.sf-dump-toggle');
            if (a) {
                collapse(a, true);
                expand(a);
                return true;
            }
            return false;
        }

        function reveal(node) {
            var previous, parents = [];
            while ((node = node.parentNode || {}) && (previous = node.previousSibling) && 'A' === previous.tagName) {
                parents.push(previous);
            }
            if (0 !== parents.length) {
                parents.forEach(function(parent) {
                    expand(parent);
                });
                return true;
            }
            return false;
        }

        function highlight(root, activeNode, nodes) {
            resetHighlightedNodes(root);
            Array.from(nodes || []).forEach(function(node) {
                if (!/\bsf-dump-highlight\b/.test(node.className)) {
                    node.className = node.className + ' sf-dump-highlight';
                }
            });
            if (!/\bsf-dump-highlight-active\b/.test(activeNode.className)) {
                activeNode.className = activeNode.className + ' sf-dump-highlight-active';
            }
        }

        function resetHighlightedNodes(root) {
            Array.from(root.querySelectorAll('.sf-dump-str, .sf-dump-key, .sf-dump-public, .sf-dump-protected, .sf-dump-private')).forEach(function(strNode) {
                strNode.className = strNode.className.replace(/\bsf-dump-highlight\b/, '');
                strNode.className = strNode.className.replace(/\bsf-dump-highlight-active\b/, '');
            });
        }
        return function(root, x) {
            root = doc.getElementById(root);
            var indentRx = new RegExp('^(' + (root.getAttribute('data-indent-pad') || ' ').replace(rxEsc, '\\$1') + ')+', 'm'),
                options = {
                    "maxDepth": 1,
                    "maxStringLength": 160,
                    "fileLinkFormat": false
                },
                elt = root.getElementsByTagName('A'),
                len = elt.length,
                i = 0,
                s, h, t = [];
            while (i < len) t.push(elt[i++]);
            for (i in x) {
                options[i] = x[i];
            }

            function a(e, f) {
                addEventListener(root, e, function(e, n) {
                    if ('A' == e.target.tagName) {
                        f(e.target, e);
                    } else if ('A' == e.target.parentNode.tagName) {
                        f(e.target.parentNode, e);
                    } else {
                        n = /\bsf-dump-ellipsis\b/.test(e.target.className) ? e.target.parentNode : e.target;
                        if ((n = n.nextElementSibling) && 'A' == n.tagName) {
                            if (!/\bsf-dump-toggle\b/.test(n.className)) {
                                n = n.nextElementSibling || n;
                            }
                            f(n, e, true);
                        }
                    }
                });
            };

            function isCtrlKey(e) {
                return e.ctrlKey || e.metaKey;
            }

            function xpathString(str) {
                var parts = str.match(/[^'"]+|['"]/g).map(function(part) {
                    if ("'" == part) {
                        return '"\'"';
                    }
                    if ('"' == part) {
                        return "'\"'";
                    }
                    return "'" + part + "'";
                });
                return "concat(" + parts.join(",") + ", '')";
            }

            function xpathHasClass(className) {
                return "contains(concat(' ', normalize-space(@class), ' '), ' " + className + " ')";
            }
            a('mouseover', function(a, e, c) {
                if (c) {
                    e.target.style.cursor = "pointer";
                }
            });
            a('click', function(a, e, c) {
                if (/\bsf-dump-toggle\b/.test(a.className)) {
                    e.preventDefault();
                    if (!toggle(a, isCtrlKey(e))) {
                        var r = doc.getElementById(a.getAttribute('href').slice(1)),
                            s = r.previousSibling,
                            f = r.parentNode,
                            t = a.parentNode;
                        t.replaceChild(r, a);
                        f.replaceChild(a, s);
                        t.insertBefore(s, r);
                        f = f.firstChild.nodeValue.match(indentRx);
                        t = t.firstChild.nodeValue.match(indentRx);
                        if (f && t && f[0] !== t[0]) {
                            r.innerHTML = r.innerHTML.replace(new RegExp('^' + f[0].replace(rxEsc, '\\$1'), 'mg'), t[0]);
                        }
                        if (/\bsf-dump-compact\b/.test(r.className)) {
                            toggle(s, isCtrlKey(e));
                        }
                    }
                    if (c) {} else if (doc.getSelection) {
                        try {
                            doc.getSelection().removeAllRanges();
                        } catch (e) {
                            doc.getSelection().empty();
                        }
                    } else {
                        doc.selection.empty();
                    }
                } else if (/\bsf-dump-str-toggle\b/.test(a.className)) {
                    e.preventDefault();
                    e = a.parentNode.parentNode;
                    e.className = e.className.replace(/\bsf-dump-str-(expand|collapse)\b/, a.parentNode.className);
                }
            });
            elt = root.getElementsByTagName('SAMP');
            len = elt.length;
            i = 0;
            while (i < len) t.push(elt[i++]);
            len = t.length;
            for (i = 0; i < len; ++i) {
                elt = t[i];
                if ('SAMP' == elt.tagName) {
                    a = elt.previousSibling || {};
                    if ('A' != a.tagName) {
                        a = doc.createElement('A');
                        a.className = 'sf-dump-ref';
                        elt.parentNode.insertBefore(a, elt);
                    } else {
                        a.innerHTML += ' ';
                    }
                    a.title = (a.title ? a.title + '\n[' : '[') + keyHint + '+click] Expand all children';
                    a.innerHTML += elt.className == 'sf-dump-compact' ? '<span>&#9654;</span>' : '<span>&#9660;</span>';
                    a.className += ' sf-dump-toggle';
                    x = 1;
                    if ('sf-dump' != elt.parentNode.className) {
                        x += elt.parentNode.getAttribute('data-depth') / 1;
                    }
                } else if (/\bsf-dump-ref\b/.test(elt.className) && (a = elt.getAttribute('href'))) {
                    a = a.slice(1);
                    elt.className += ' sf-dump-hover';
                    elt.className += ' ' + a;
                    if (/[\[{]$/.test(elt.previousSibling.nodeValue)) {
                        a = a != elt.nextSibling.id && doc.getElementById(a);
                        try {
                            s = a.nextSibling;
                            elt.appendChild(a);
                            s.parentNode.insertBefore(a, s);
                            if (/^[@#]/.test(elt.innerHTML)) {
                                elt.innerHTML += ' <span>&#9654;</span>';
                            } else {
                                elt.innerHTML = '<span>&#9654;</span>';
                                elt.className = 'sf-dump-ref';
                            }
                            elt.className += ' sf-dump-toggle';
                        } catch (e) {
                            if ('&' == elt.innerHTML.charAt(0)) {
                                elt.innerHTML = '&#8230;';
                                elt.className = 'sf-dump-ref';
                            }
                        }
                    }
                }
            }
            if (doc.evaluate && Array.from && root.children.length > 1) {
                root.setAttribute('tabindex', 0);
                SearchState = function() {
                    this.nodes = [];
                    this.idx = 0;
                };
                SearchState.prototype = {
                    next: function() {
                        if (this.isEmpty()) {
                            return this.current();
                        }
                        this.idx = this.idx < (this.nodes.length - 1) ? this.idx + 1 : 0;
                        return this.current();
                    },
                    previous: function() {
                        if (this.isEmpty()) {
                            return this.current();
                        }
                        this.idx = this.idx > 0 ? this.idx - 1 : (this.nodes.length - 1);
                        return this.current();
                    },
                    isEmpty: function() {
                        return 0 === this.count();
                    },
                    current: function() {
                        if (this.isEmpty()) {
                            return null;
                        }
                        return this.nodes[this.idx];
                    },
                    reset: function() {
                        this.nodes = [];
                        this.idx = 0;
                    },
                    count: function() {
                        return this.nodes.length;
                    },
                };

                function showCurrent(state) {
                    var currentNode = state.current(),
                        currentRect, searchRect;
                    if (currentNode) {
                        reveal(currentNode);
                        highlight(root, currentNode, state.nodes);
                        if ('scrollIntoView' in currentNode) {
                            currentNode.scrollIntoView(true);
                            currentRect = currentNode.getBoundingClientRect();
                            searchRect = search.getBoundingClientRect();
                            if (currentRect.top < (searchRect.top + searchRect.height)) {
                                window.scrollBy(0, -(searchRect.top + searchRect.height + 5));
                            }
                        }
                    }
                    counter.textContent = (state.isEmpty() ? 0 : state.idx + 1) + ' of ' + state.count();
                }
                var search = doc.createElement('div');
                search.className = 'sf-dump-search-wrapper sf-dump-search-hidden';
                search.innerHTML = ' <input type="text" class="sf-dump-search-input"> <span class="sf-dump-search-count">0 of 0<\/span> <button type="button" class="sf-dump-search-input-previous" tabindex="-1"> <svg viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1683 1331l-166 165q-19 19-45 19t-45-19L896 965l-531 531q-19 19-45 19t-45-19l-166-165q-19-19-19-45.5t19-45.5l742-741q19-19 45-19t45 19l742 741q19 19 19 45.5t-19 45.5z"\/><\/svg> <\/button> <button type="button" class="sf-dump-search-input-next" tabindex="-1"> <svg viewBox="0 0 1792 1792" xmlns="http://www.w3.org/2000/svg"><path d="M1683 808l-742 741q-19 19-45 19t-45-19L109 808q-19-19-19-45.5t19-45.5l166-165q19-19 45-19t45 19l531 531 531-531q19-19 45-19t45 19l166 165q19 19 19 45.5t-19 45.5z"\/><\/svg> <\/button> ';
                root.insertBefore(search, root.firstChild);
                var state = new SearchState();
                var searchInput = search.querySelector('.sf-dump-search-input');
                var counter = search.querySelector('.sf-dump-search-count');
                var searchInputTimer = 0;
                var previousSearchQuery = '';
                addEventListener(searchInput, 'keyup', function(e) {
                    var searchQuery = e.target.value; /* Don't perform anything if the pressed key didn't change the query */
                    if (searchQuery === previousSearchQuery) {
                        return;
                    }
                    previousSearchQuery = searchQuery;
                    clearTimeout(searchInputTimer);
                    searchInputTimer = setTimeout(function() {
                        state.reset();
                        collapseAll(root);
                        resetHighlightedNodes(root);
                        if ('' === searchQuery) {
                            counter.textContent = '0 of 0';
                            return;
                        }
                        var classMatches = ["sf-dump-str", "sf-dump-key", "sf-dump-public", "sf-dump-protected", "sf-dump-private", ].map(xpathHasClass).join(' or ');
                        var xpathResult = doc.evaluate('.//span[' + classMatches + '][contains(translate(child::text(), ' + xpathString(searchQuery.toUpperCase()) + ', ' + xpathString(searchQuery.toLowerCase()) + '), ' + xpathString(searchQuery.toLowerCase()) + ')]', root, null, XPathResult.ORDERED_NODE_ITERATOR_TYPE, null);
                        while (node = xpathResult.iterateNext()) state.nodes.push(node);
                        showCurrent(state);
                    }, 400);
                });
                Array.from(search.querySelectorAll('.sf-dump-search-input-next, .sf-dump-search-input-previous')).forEach(function(btn) {
                    addEventListener(btn, 'click', function(e) {
                        e.preventDefault(); - 1 !== e.target.className.indexOf('next') ? state.next() : state.previous();
                        searchInput.focus();
                        collapseAll(root);
                        showCurrent(state);
                    })
                });
                addEventListener(root, 'keydown', function(e) {
                    var isSearchActive = !/\bsf-dump-search-hidden\b/.test(search.className);
                    if ((114 === e.keyCode && !isSearchActive) || (isCtrlKey(e) && 70 === e.keyCode)) {
                        /* F3 or CMD/CTRL + F */
                        if (70 === e.keyCode && document.activeElement === searchInput) {
                            /* * If CMD/CTRL + F is hit while having focus on search input, * the user probably meant to trigger browser search instead. * Let the browser execute its behavior: */
                            return;
                        }
                        e.preventDefault();
                        search.className = search.className.replace(/\bsf-dump-search-hidden\b/, '');
                        searchInput.focus();
                    } else if (isSearchActive) {
                        if (27 === e.keyCode) {
                            /* ESC key */
                            search.className += ' sf-dump-search-hidden';
                            e.preventDefault();
                            resetHighlightedNodes(root);
                            searchInput.value = '';
                        } else if ((isCtrlKey(e) && 71 === e.keyCode) /* CMD/CTRL + G */ || 13 === e.keyCode /* Enter */ || 114 === e.keyCode /* F3 */ ) {
                            e.preventDefault();
                            e.shiftKey ? state.previous() : state.next();
                            collapseAll(root);
                            showCurrent(state);
                        }
                    }
                });
            }
            if (0 >= options.maxStringLength) {
                return;
            }
            try {
                elt = root.querySelectorAll('.sf-dump-str');
                len = elt.length;
                i = 0;
                t = [];
                while (i < len) t.push(elt[i++]);
                len = t.length;
                for (i = 0; i < len; ++i) {
                    elt = t[i];
                    s = elt.innerText || elt.textContent;
                    x = s.length - options.maxStringLength;
                    if (0 < x) {
                        h = elt.innerHTML;
                        elt[elt.innerText ? 'innerText' : 'textContent'] = s.substring(0, options.maxStringLength);
                        elt.className += ' sf-dump-str-collapse';
                        elt.innerHTML = '<span class=sf-dump-str-collapse>' + h + '<a class="sf-dump-ref sf-dump-str-toggle" title="Collapse"> &#9664;</a></span>' + '<span class=sf-dump-str-expand>' + elt.innerHTML + '<a class="sf-dump-ref sf-dump-str-toggle" title="' + x + ' remaining characters"> &#9654;</a></span>';
                    }
                }
            } catch (e) {}
        };
    })(document);
</script>
<style>
    .sf-js-enabled pre.sf-dump .sf-dump-compact,
    .sf-js-enabled .sf-dump-str-collapse .sf-dump-str-collapse,
    .sf-js-enabled .sf-dump-str-expand .sf-dump-str-expand {
        display: none;
    }

    .sf-dump-hover:hover {
        background-color: #B729D9;
        color: #FFF !important;
        border-radius: 2px;
    }

    pre.sf-dump {
        display: block;
        white-space: pre;
        padding: 5px;
        overflow: initial !important;
    }

    pre.sf-dump:after {
        content: "";
        visibility: hidden;
        display: block;
        height: 0;
        clear: both;
    }

    pre.sf-dump .sf-dump-ellipsization {
        display: inline-flex;
    }

    pre.sf-dump a {
        text-decoration: none;
        cursor: pointer;
        border: 0;
        outline: none;
        color: inherit;
    }

    pre.sf-dump img {
        max-width: 50em;
        max-height: 50em;
        margin: .5em 0 0 0;
        padding: 0;
        background: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAAAAAA6mKC9AAAAHUlEQVQY02O8zAABilCaiQEN0EeA8QuUcX9g3QEAAjcC5piyhyEAAAAASUVORK5CYII=) #D3D3D3;
    }

    pre.sf-dump .sf-dump-ellipsis {
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    pre.sf-dump .sf-dump-ellipsis-tail {
        flex-shrink: 0;
    }

    pre.sf-dump code {
        display: inline;
        padding: 0;
        background: none;
    }

    .sf-dump-public.sf-dump-highlight,
    .sf-dump-protected.sf-dump-highlight,
    .sf-dump-private.sf-dump-highlight,
    .sf-dump-str.sf-dump-highlight,
    .sf-dump-key.sf-dump-highlight {
        background: rgba(111, 172, 204, 0.3);
        border: 1px solid #7DA0B1;
        border-radius: 3px;
    }

    .sf-dump-public.sf-dump-highlight-active,
    .sf-dump-protected.sf-dump-highlight-active,
    .sf-dump-private.sf-dump-highlight-active,
    .sf-dump-str.sf-dump-highlight-active,
    .sf-dump-key.sf-dump-highlight-active {
        background: rgba(253, 175, 0, 0.4);
        border: 1px solid #ffa500;
        border-radius: 3px;
    }

    pre.sf-dump .sf-dump-search-hidden {
        display: none !important;
    }

    pre.sf-dump .sf-dump-search-wrapper {
        font-size: 0;
        white-space: nowrap;
        margin-bottom: 5px;
        display: flex;
        position: -webkit-sticky;
        position: sticky;
        top: 5px;
    }

    pre.sf-dump .sf-dump-search-wrapper>* {
        vertical-align: top;
        box-sizing: border-box;
        height: 21px;
        font-weight: normal;
        border-radius: 0;
        background: #FFF;
        color: #757575;
        border: 1px solid #BBB;
    }

    pre.sf-dump .sf-dump-search-wrapper>input.sf-dump-search-input {
        padding: 3px;
        height: 21px;
        font-size: 12px;
        border-right: none;
        border-top-left-radius: 3px;
        border-bottom-left-radius: 3px;
        color: #000;
        min-width: 15px;
        width: 100%;
    }

    pre.sf-dump .sf-dump-search-wrapper>.sf-dump-search-input-next,
    pre.sf-dump .sf-dump-search-wrapper>.sf-dump-search-input-previous {
        background: #F2F2F2;
        outline: none;
        border-left: none;
        font-size: 0;
        line-height: 0;
    }

    pre.sf-dump .sf-dump-search-wrapper>.sf-dump-search-input-next {
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
    }

    pre.sf-dump .sf-dump-search-wrapper>.sf-dump-search-input-next>svg,
    pre.sf-dump .sf-dump-search-wrapper>.sf-dump-search-input-previous>svg {
        pointer-events: none;
        width: 12px;
        height: 12px;
    }

    pre.sf-dump .sf-dump-search-wrapper>.sf-dump-search-count {
        display: inline-block;
        padding: 0 5px;
        margin: 0;
        border-left: none;
        line-height: 21px;
        font-size: 12px;
    }

    pre.sf-dump,
    pre.sf-dump .sf-dump-default {
        background-color: #18171B;
        color: #FF8400;
        line-height: 1.2em;
        font: 12px Menlo, Monaco, Consolas, monospace;
        word-wrap: break-word;
        white-space: pre-wrap;
        position: relative;
        z-index: 99999;
        word-break: break-all
    }

    pre.sf-dump .sf-dump-num {
        font-weight: bold;
        color: #1299DA
    }

    pre.sf-dump .sf-dump-const {
        font-weight: bold
    }

    pre.sf-dump .sf-dump-virtual {
        font-style: italic
    }

    pre.sf-dump .sf-dump-str {
        font-weight: bold;
        color: #56DB3A
    }

    pre.sf-dump .sf-dump-note {
        color: #1299DA
    }

    pre.sf-dump .sf-dump-ref {
        color: #A0A0A0
    }

    pre.sf-dump .sf-dump-public {
        color: #FFFFFF
    }

    pre.sf-dump .sf-dump-protected {
        color: #FFFFFF
    }

    pre.sf-dump .sf-dump-private {
        color: #FFFFFF
    }

    pre.sf-dump .sf-dump-meta {
        color: #B729D9
    }

    pre.sf-dump .sf-dump-key {
        color: #56DB3A
    }

    pre.sf-dump .sf-dump-index {
        color: #1299DA
    }

    pre.sf-dump .sf-dump-ellipsis {
        color: #FF8400
    }

    pre.sf-dump .sf-dump-ns {
        user-select: none;
    }

    pre.sf-dump .sf-dump-ellipsis-note {
        color: #1299DA
    }
</style>
<pre class=sf-dump id=sf-dump-1847210059 data-indent-pad="  "><span class=sf-dump-note>Illuminate\Pagination\LengthAwarePaginator</span> {<a class=sf-dump-ref>#1607</a><samp data-depth=1 class=sf-dump-expanded><span style="color: #A0A0A0;"> // app/Http/Controllers/RestaurantController.php:133</span>
  #<span class=sf-dump-protected title="Protected property">items</span>: <span class="sf-dump-note sf-dump-ellipsization" title="Illuminate\Database\Eloquent\Collection
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">Illuminate\Database\Eloquent</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Collection</span></span> {<a class=sf-dump-ref>#1609</a><samp data-depth=2 class=sf-dump-compact>
    #<span class=sf-dump-protected title="Protected property">items</span>: <span class=sf-dump-note>array:10</span> [<samp data-depth=3 class=sf-dump-compact>
      <span class=sf-dump-index>0</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1628</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="28 characters">&#1054;&#1054;&#1054; &#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103; &#1048;&#1085;&#1092;&#1086;&#1057;&#1072;&#1085;&#1090;&#1077;&#1093;&#1058;&#1086;&#1084;&#1089;&#1082;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="113 characters">Autem harum iste et nam sed voluptatem libero commodi. Est facere tenetur corrupti culpa. Esse eum velit quaerat.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="66 characters">314734, &#1050;&#1086;&#1089;&#1090;&#1088;&#1086;&#1084;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1047;&#1072;&#1088;&#1072;&#1081;&#1089;&#1082;, &#1074;&#1098;&#1077;&#1079;&#1076; &#1041;&#1091;&#1093;&#1072;&#1088;&#1077;&#1089;&#1090;&#1089;&#1082;&#1072;&#1103;, 83</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="7 characters">Italian</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="62 characters">Sit vero quo minima recusandae velit et in qui necessitatibus.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="28 characters">&#1054;&#1054;&#1054; &#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103; &#1048;&#1085;&#1092;&#1086;&#1057;&#1072;&#1085;&#1090;&#1077;&#1093;&#1058;&#1086;&#1084;&#1089;&#1082;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="113 characters">Autem harum iste et nam sed voluptatem libero commodi. Est facere tenetur corrupti culpa. Esse eum velit quaerat.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="66 characters">314734, &#1050;&#1086;&#1089;&#1090;&#1088;&#1086;&#1084;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1047;&#1072;&#1088;&#1072;&#1081;&#1089;&#1082;, &#1074;&#1098;&#1077;&#1079;&#1076; &#1041;&#1091;&#1093;&#1072;&#1088;&#1077;&#1089;&#1090;&#1089;&#1082;&#1072;&#1103;, 83</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="7 characters">Italian</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="62 characters">Sit vero quo minima recusandae velit et in qui necessitatibus.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a><samp data-depth=6 id=sf-dump-1847210059-ref21621 class=sf-dump-compact>
            #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
            #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="19 characters">restaurant_statuses</span>"
            #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
            #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
            +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
            #<span class=sf-dump-protected title="Protected property">with</span>: []
            #<span class=sf-dump-protected title="Protected property">withCount</span>: []
            +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
            #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
            +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
            +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
            #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
            #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:4</span> [<samp data-depth=7 class=sf-dump-compact>
              "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>1</span>
              "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="6 characters">active</span>"
              "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:17</span>"
              "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:17</span>"
            </samp>]
            #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:4</span> [<samp data-depth=7 class=sf-dump-compact>
              "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>1</span>
              "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="6 characters">active</span>"
              "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:17</span>"
              "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:17</span>"
            </samp>]
            #<span class=sf-dump-protected title="Protected property">changes</span>: []
            #<span class=sf-dump-protected title="Protected property">previous</span>: []
            #<span class=sf-dump-protected title="Protected property">casts</span>: []
            #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
            #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
            #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
            #<span class=sf-dump-protected title="Protected property">appends</span>: []
            #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
            #<span class=sf-dump-protected title="Protected property">observables</span>: []
            #<span class=sf-dump-protected title="Protected property">relations</span>: []
            #<span class=sf-dump-protected title="Protected property">touches</span>: []
            #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
            #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
            +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
            +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
            #<span class=sf-dump-protected title="Protected property">hidden</span>: []
            #<span class=sf-dump-protected title="Protected property">visible</span>: []
            #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=7 class=sf-dump-compact>
              <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
            </samp>]
            #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=7 class=sf-dump-compact>
              <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
            </samp>]
          </samp>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>1</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1629</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="22 characters">&#1054;&#1040;&#1054; &#1057;&#1090;&#1088;&#1086;&#1081;&#1056;&#1077;&#1095;&#1052;&#1086;&#1085;&#1090;&#1072;&#1078;&#1057;&#1085;&#1086;&#1089;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="166 characters">Maxime impedit sint similique voluptas labore. Ipsam et repudiandae provident. Soluta quis ut quaerat deserunt non deserunt velit. Est non dolore ut magnam ut et nam.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="66 characters">952419, &#1052;&#1072;&#1075;&#1072;&#1076;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1050;&#1086;&#1083;&#1086;&#1084;&#1085;&#1072;, &#1074;&#1098;&#1077;&#1079;&#1076; &#1041;&#1091;&#1093;&#1072;&#1088;&#1077;&#1089;&#1090;&#1089;&#1082;&#1072;&#1103;, 71</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="75 characters">A rerum perspiciatis aliquam esse reprehenderit nulla nobis quibusdam illo.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="22 characters">&#1054;&#1040;&#1054; &#1057;&#1090;&#1088;&#1086;&#1081;&#1056;&#1077;&#1095;&#1052;&#1086;&#1085;&#1090;&#1072;&#1078;&#1057;&#1085;&#1086;&#1089;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="166 characters">Maxime impedit sint similique voluptas labore. Ipsam et repudiandae provident. Soluta quis ut quaerat deserunt non deserunt velit. Est non dolore ut magnam ut et nam.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="66 characters">952419, &#1052;&#1072;&#1075;&#1072;&#1076;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1050;&#1086;&#1083;&#1086;&#1084;&#1085;&#1072;, &#1074;&#1098;&#1077;&#1079;&#1076; &#1041;&#1091;&#1093;&#1072;&#1088;&#1077;&#1089;&#1090;&#1089;&#1082;&#1072;&#1103;, 71</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="75 characters">A rerum perspiciatis aliquam esse reprehenderit nulla nobis quibusdam illo.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>2</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1630</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>3</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="7 characters">&#1047;&#1040;&#1054; &#1041;&#1091;&#1093;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="197 characters">Facere praesentium commodi fugit pariatur. Alias sed vitae culpa dolores dolorem qui. Qui cum quasi minus expedita alias autem. Hic et mollitia omnis possimus dolor eius. Id ipsa qui quae aut amet.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="70 characters">109865, &#1051;&#1077;&#1085;&#1080;&#1085;&#1075;&#1088;&#1072;&#1076;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1042;&#1080;&#1076;&#1085;&#1086;&#1077;, &#1073;&#1091;&#1083;&#1100;&#1074;&#1072;&#1088; &#1041;&#1091;&#1076;&#1072;&#1087;&#1077;&#1096;&#1090;&#1089;&#1090;&#1082;&#1072;&#1103;, 99</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="7 characters">Italian</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="66 characters">Rem possimus sint et aut velit libero fugit mollitia officiis eum.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>3</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="7 characters">&#1047;&#1040;&#1054; &#1041;&#1091;&#1093;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="197 characters">Facere praesentium commodi fugit pariatur. Alias sed vitae culpa dolores dolorem qui. Qui cum quasi minus expedita alias autem. Hic et mollitia omnis possimus dolor eius. Id ipsa qui quae aut amet.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="70 characters">109865, &#1051;&#1077;&#1085;&#1080;&#1085;&#1075;&#1088;&#1072;&#1076;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1042;&#1080;&#1076;&#1085;&#1086;&#1077;, &#1073;&#1091;&#1083;&#1100;&#1074;&#1072;&#1088; &#1041;&#1091;&#1076;&#1072;&#1087;&#1077;&#1096;&#1090;&#1089;&#1090;&#1082;&#1072;&#1103;, 99</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="7 characters">Italian</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="66 characters">Rem possimus sint et aut velit libero fugit mollitia officiis eum.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>3</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1631</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>4</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="19 characters">&#1047;&#1040;&#1054; &#1042;&#1086;&#1089;&#1090;&#1086;&#1082;&#1056;&#1077;&#1084;&#1043;&#1083;&#1072;&#1074;-&#1052;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="157 characters">Voluptatem saepe autem et asperiores dolores quisquam recusandae. Aperiam ullam et esse quia repudiandae repudiandae. Nam accusantium omnis cum dolores enim.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="60 characters">541139, &#1058;&#1102;&#1084;&#1077;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1052;&#1099;&#1090;&#1080;&#1097;&#1080;, &#1087;&#1088;&#1086;&#1077;&#1079;&#1076; &#1051;&#1072;&#1076;&#1099;&#1075;&#1080;&#1085;&#1072;, 86</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">Indian</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">2000-3000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="100 characters">Rerum eligendi quia alias rerum cupiditate aperiam nihil neque aut nam alias architecto repudiandae.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>4</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="19 characters">&#1047;&#1040;&#1054; &#1042;&#1086;&#1089;&#1090;&#1086;&#1082;&#1056;&#1077;&#1084;&#1043;&#1083;&#1072;&#1074;-&#1052;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="157 characters">Voluptatem saepe autem et asperiores dolores quisquam recusandae. Aperiam ullam et esse quia repudiandae repudiandae. Nam accusantium omnis cum dolores enim.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="60 characters">541139, &#1058;&#1102;&#1084;&#1077;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1052;&#1099;&#1090;&#1080;&#1097;&#1080;, &#1087;&#1088;&#1086;&#1077;&#1079;&#1076; &#1051;&#1072;&#1076;&#1099;&#1075;&#1080;&#1085;&#1072;, 86</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">Indian</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">2000-3000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="100 characters">Rerum eligendi quia alias rerum cupiditate aperiam nihil neque aut nam alias architecto repudiandae.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>4</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1632</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>5</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="18 characters">&#1054;&#1054;&#1054; &#1048;&#1085;&#1092;&#1086;&#1040;&#1089;&#1073;&#1086;&#1094;&#1077;&#1084;&#1077;&#1085;&#1090;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="117 characters">Nihil voluptates aperiam cumque quisquam ratione. Unde laudantium et mollitia error. Quis vitae voluptatem qui rerum.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="57 characters">046242, &#1058;&#1086;&#1084;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1042;&#1080;&#1076;&#1085;&#1086;&#1077;, &#1073;&#1091;&#1083;&#1100;&#1074;&#1072;&#1088; &#1063;&#1077;&#1093;&#1086;&#1074;&#1072;, 72</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="7 characters">Mexican</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="8 characters">500-1000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="63 characters">Eum et laboriosam libero dolorum eius autem quia amet sequi et.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>5</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="18 characters">&#1054;&#1054;&#1054; &#1048;&#1085;&#1092;&#1086;&#1040;&#1089;&#1073;&#1086;&#1094;&#1077;&#1084;&#1077;&#1085;&#1090;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="117 characters">Nihil voluptates aperiam cumque quisquam ratione. Unde laudantium et mollitia error. Quis vitae voluptatem qui rerum.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="57 characters">046242, &#1058;&#1086;&#1084;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1042;&#1080;&#1076;&#1085;&#1086;&#1077;, &#1073;&#1091;&#1083;&#1100;&#1074;&#1072;&#1088; &#1063;&#1077;&#1093;&#1086;&#1074;&#1072;, 72</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="7 characters">Mexican</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="8 characters">500-1000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="63 characters">Eum et laboriosam libero dolorum eius autem quia amet sequi et.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>5</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1633</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>6</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="15 characters">&#1047;&#1040;&#1054; &#1058;&#1077;&#1093;&#1054;&#1088;&#1080;&#1086;&#1085;&#1052;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="126 characters">Ut quia qui sit culpa nesciunt dicta asperiores harum. Doloribus dolores impedit repellendus. Ipsam qui qui quasi veniam odio.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="55 characters">349995, &#1051;&#1080;&#1087;&#1077;&#1094;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1050;&#1083;&#1080;&#1085;, &#1089;&#1087;&#1091;&#1089;&#1082; &#1057;&#1090;&#1072;&#1083;&#1080;&#1085;&#1072;, 96</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">2000-3000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="89 characters">Impedit eos ut incidunt quia alias molestiae consequatur aut suscipit accusamus possimus.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>6</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="15 characters">&#1047;&#1040;&#1054; &#1058;&#1077;&#1093;&#1054;&#1088;&#1080;&#1086;&#1085;&#1052;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="126 characters">Ut quia qui sit culpa nesciunt dicta asperiores harum. Doloribus dolores impedit repellendus. Ipsam qui qui quasi veniam odio.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="55 characters">349995, &#1051;&#1080;&#1087;&#1077;&#1094;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1050;&#1083;&#1080;&#1085;, &#1089;&#1087;&#1091;&#1089;&#1082; &#1057;&#1090;&#1072;&#1083;&#1080;&#1085;&#1072;, 96</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">2000-3000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="89 characters">Impedit eos ut incidunt quia alias molestiae consequatur aut suscipit accusamus possimus.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>6</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1634</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>7</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="23 characters">&#1054;&#1054;&#1054; &#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103; &#1056;&#1077;&#1084;&#1057;&#1086;&#1092;&#1090;&#1043;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="151 characters">Quia cupiditate dolorum placeat dolorem dolores. Voluptas quis quia voluptatem hic. Voluptates voluptatibus error repudiandae quas ratione repellendus.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="62 characters">038057, &#1040;&#1089;&#1090;&#1088;&#1072;&#1093;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1063;&#1077;&#1093;&#1086;&#1074;, &#1087;&#1077;&#1088;. &#1051;&#1086;&#1084;&#1086;&#1085;&#1086;&#1089;&#1086;&#1074;&#1072;, 64</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">2000-3000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="98 characters">Cupiditate aut et quas aliquid laborum eveniet numquam molestiae non aut consequuntur deleniti ut.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>7</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="23 characters">&#1054;&#1054;&#1054; &#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103; &#1056;&#1077;&#1084;&#1057;&#1086;&#1092;&#1090;&#1043;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="151 characters">Quia cupiditate dolorum placeat dolorem dolores. Voluptas quis quia voluptatem hic. Voluptates voluptatibus error repudiandae quas ratione repellendus.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="62 characters">038057, &#1040;&#1089;&#1090;&#1088;&#1072;&#1093;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1063;&#1077;&#1093;&#1086;&#1074;, &#1087;&#1077;&#1088;. &#1051;&#1086;&#1084;&#1086;&#1085;&#1086;&#1089;&#1086;&#1074;&#1072;, 64</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">2000-3000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="98 characters">Cupiditate aut et quas aliquid laborum eveniet numquam molestiae non aut consequuntur deleniti ut.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>7</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1635</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>8</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="23 characters">&#1047;&#1040;&#1054; &#1069;&#1083;&#1077;&#1082;&#1090;&#1088;&#1086;&#1058;&#1077;&#1093;&#1054;&#1088;&#1080;&#1086;&#1085;&#1055;&#1088;&#1086;&#1084;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="193 characters">Consequatur porro aspernatur dignissimos iure animi. Doloremque placeat nihil et ab ea. Nobis cum asperiores veritatis distinctio. Rem unde vel blanditiis voluptas corporis perferendis dolores.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="57 characters">761155, &#1050;&#1091;&#1088;&#1075;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1050;&#1072;&#1096;&#1080;&#1088;&#1072;, &#1091;&#1083;. &#1057;&#1090;&#1072;&#1083;&#1080;&#1085;&#1072;, 14</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="8 characters">Japanese</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="44 characters">Fugiat quidem aut aut saepe cum odio id quo.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>8</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="23 characters">&#1047;&#1040;&#1054; &#1069;&#1083;&#1077;&#1082;&#1090;&#1088;&#1086;&#1058;&#1077;&#1093;&#1054;&#1088;&#1080;&#1086;&#1085;&#1055;&#1088;&#1086;&#1084;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="193 characters">Consequatur porro aspernatur dignissimos iure animi. Doloremque placeat nihil et ab ea. Nobis cum asperiores veritatis distinctio. Rem unde vel blanditiis voluptas corporis perferendis dolores.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="57 characters">761155, &#1050;&#1091;&#1088;&#1075;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1050;&#1072;&#1096;&#1080;&#1088;&#1072;, &#1091;&#1083;. &#1057;&#1090;&#1072;&#1083;&#1080;&#1085;&#1072;, 14</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="8 characters">Japanese</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="44 characters">Fugiat quidem aut aut saepe cum odio id quo.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>8</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1636</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>9</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="16 characters">&#1054;&#1054;&#1054; &#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103; &#1052;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="94 characters">Ab eligendi eum rerum error ipsam nostrum blanditiis. Officia enim asperiores non ab suscipit.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="73 characters">501018, &#1052;&#1072;&#1075;&#1072;&#1076;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1057;&#1077;&#1088;&#1077;&#1073;&#1088;&#1103;&#1085;&#1099;&#1077; &#1055;&#1088;&#1091;&#1076;&#1099;, &#1096;&#1086;&#1089;&#1089;&#1077; &#1041;&#1072;&#1083;&#1082;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103;, 11</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="8 characters">500-1000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="56 characters">Debitis consequatur ratione nisi nihil est sapiente rem.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>9</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="16 characters">&#1054;&#1054;&#1054; &#1050;&#1086;&#1084;&#1087;&#1072;&#1085;&#1080;&#1103; &#1052;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="94 characters">Ab eligendi eum rerum error ipsam nostrum blanditiis. Officia enim asperiores non ab suscipit.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="73 characters">501018, &#1052;&#1072;&#1075;&#1072;&#1076;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1057;&#1077;&#1088;&#1077;&#1073;&#1088;&#1103;&#1085;&#1099;&#1077; &#1055;&#1088;&#1091;&#1076;&#1099;, &#1096;&#1086;&#1089;&#1089;&#1077; &#1041;&#1072;&#1083;&#1082;&#1072;&#1085;&#1089;&#1082;&#1072;&#1103;, 11</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="6 characters">French</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="8 characters">500-1000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="56 characters">Debitis consequatur ratione nisi nihil est sapiente rem.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
      <span class=sf-dump-index>9</span> => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\Restaurant
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">Restaurant</span></span> {<a class=sf-dump-ref>#1637</a><samp data-depth=4 class=sf-dump-compact>
        #<span class=sf-dump-protected title="Protected property">connection</span>: "<span class=sf-dump-str title="5 characters">pgsql</span>"
        #<span class=sf-dump-protected title="Protected property">table</span>: "<span class=sf-dump-str title="11 characters">restaurants</span>"
        #<span class=sf-dump-protected title="Protected property">primaryKey</span>: "<span class=sf-dump-str title="2 characters">id</span>"
        #<span class=sf-dump-protected title="Protected property">keyType</span>: "<span class=sf-dump-str title="3 characters">int</span>"
        +<span class=sf-dump-public title="Public property">incrementing</span>: <span class=sf-dump-const>true</span>
        #<span class=sf-dump-protected title="Protected property">with</span>: []
        #<span class=sf-dump-protected title="Protected property">withCount</span>: []
        +<span class=sf-dump-public title="Public property">preventsLazyLoading</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>15</span>
        +<span class=sf-dump-public title="Public property">exists</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">wasRecentlyCreated</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">attributes</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>10</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="13 characters">&#1052;&#1050;&#1050; &#1052;&#1086;&#1088;&#1042;&#1077;&#1082;&#1090;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="152 characters">Cumque debitis officiis vel pariatur. Eos ab velit et deserunt a et aperiam tenetur. Quis exercitationem velit modi officia aut consequatur et voluptas.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="61 characters">469240, &#1053;&#1080;&#1078;&#1077;&#1075;&#1086;&#1088;&#1086;&#1076;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1053;&#1086;&#1075;&#1080;&#1085;&#1089;&#1082;, &#1087;&#1077;&#1088;. &#1043;&#1086;&#1075;&#1086;&#1083;&#1103;, 28</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="8 characters">Japanese</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="52 characters">Rem dolor aliquid est aspernatur reiciendis tempora.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">original</span>: <span class=sf-dump-note>array:16</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>id</span>" => <span class=sf-dump-num>10</span>
          "<span class=sf-dump-key>name</span>" => "<span class=sf-dump-str title="13 characters">&#1052;&#1050;&#1050; &#1052;&#1086;&#1088;&#1042;&#1077;&#1082;&#1090;&#1086;&#1088;</span>"
          "<span class=sf-dump-key>description</span>" => "<span class=sf-dump-str title="152 characters">Cumque debitis officiis vel pariatur. Eos ab velit et deserunt a et aperiam tenetur. Quis exercitationem velit modi officia aut consequatur et voluptas.</span>"
          "<span class=sf-dump-key>address</span>" => "<span class=sf-dump-str title="61 characters">469240, &#1053;&#1080;&#1078;&#1077;&#1075;&#1086;&#1088;&#1086;&#1076;&#1089;&#1082;&#1072;&#1103; &#1086;&#1073;&#1083;&#1072;&#1089;&#1090;&#1100;, &#1075;&#1086;&#1088;&#1086;&#1076; &#1053;&#1086;&#1075;&#1080;&#1085;&#1089;&#1082;, &#1087;&#1077;&#1088;. &#1043;&#1086;&#1075;&#1086;&#1083;&#1103;, 28</span>"
          "<span class=sf-dump-key>type_kitchen</span>" => "<span class=sf-dump-str title="8 characters">Japanese</span>"
          "<span class=sf-dump-key>price_range</span>" => "<span class=sf-dump-str title="9 characters">1000-2000</span>"
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="8 characters">09:00:00</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="8 characters">22:00:00</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="8 characters">10:00:00</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="8 characters">23:00:00</span>"
          "<span class=sf-dump-key>cancellation_policy</span>" => "<span class=sf-dump-str title="52 characters">Rem dolor aliquid est aspernatur reiciendis tempora.</span>"
          "<span class=sf-dump-key>restaurant_chain_id</span>" => <span class=sf-dump-num>2</span>
          "<span class=sf-dump-key>status_id</span>" => <span class=sf-dump-num>1</span>
          "<span class=sf-dump-key>created_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>updated_at</span>" => "<span class=sf-dump-str title="19 characters">2025-10-20 09:30:18</span>"
          "<span class=sf-dump-key>deleted_at</span>" => <span class=sf-dump-const>null</span>
        </samp>]
        #<span class=sf-dump-protected title="Protected property">changes</span>: []
        #<span class=sf-dump-protected title="Protected property">previous</span>: []
        #<span class=sf-dump-protected title="Protected property">casts</span>: <span class=sf-dump-note>array:5</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>weekdays_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekdays_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_opens_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>weekend_closes_at</span>" => "<span class=sf-dump-str title="12 characters">datetime:H:i</span>"
          "<span class=sf-dump-key>deleted_at</span>" => "<span class=sf-dump-str title="8 characters">datetime</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">classCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">attributeCastCache</span>: []
        #<span class=sf-dump-protected title="Protected property">dateFormat</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">appends</span>: []
        #<span class=sf-dump-protected title="Protected property">dispatchesEvents</span>: []
        #<span class=sf-dump-protected title="Protected property">observables</span>: []
        #<span class=sf-dump-protected title="Protected property">relations</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=5 class=sf-dump-compact>
          "<span class=sf-dump-key>chain</span>" => <span class=sf-dump-const>null</span>
          "<span class=sf-dump-key>status</span>" => <span class="sf-dump-note sf-dump-ellipsization" title="App\Models\RestaurantStatuse
"><span class="sf-dump-ellipsis sf-dump-ellipsis-note">App\Models</span><span class="sf-dump-ellipsis sf-dump-ellipsis-note">\</span><span class="sf-dump-ellipsis-tail">RestaurantStatuse</span></span> {<a class=sf-dump-ref href=#sf-dump-1847210059-ref21621 title="10 occurrences">#1621</a>}
        </samp>]
        #<span class=sf-dump-protected title="Protected property">touches</span>: []
        #<span class=sf-dump-protected title="Protected property">relationAutoloadCallback</span>: <span class=sf-dump-const>null</span>
        #<span class=sf-dump-protected title="Protected property">relationAutoloadContext</span>: <span class=sf-dump-const>null</span>
        +<span class=sf-dump-public title="Public property">timestamps</span>: <span class=sf-dump-const>true</span>
        +<span class=sf-dump-public title="Public property">usesUniqueIds</span>: <span class=sf-dump-const>false</span>
        #<span class=sf-dump-protected title="Protected property">hidden</span>: []
        #<span class=sf-dump-protected title="Protected property">visible</span>: []
        #<span class=sf-dump-protected title="Protected property">fillable</span>: <span class=sf-dump-note>array:12</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str title="4 characters">name</span>"
          <span class=sf-dump-index>1</span> => "<span class=sf-dump-str title="11 characters">description</span>"
          <span class=sf-dump-index>2</span> => "<span class=sf-dump-str title="7 characters">address</span>"
          <span class=sf-dump-index>3</span> => "<span class=sf-dump-str title="12 characters">type_kitchen</span>"
          <span class=sf-dump-index>4</span> => "<span class=sf-dump-str title="11 characters">price_range</span>"
          <span class=sf-dump-index>5</span> => "<span class=sf-dump-str title="17 characters">weekdays_opens_at</span>"
          <span class=sf-dump-index>6</span> => "<span class=sf-dump-str title="18 characters">weekdays_closes_at</span>"
          <span class=sf-dump-index>7</span> => "<span class=sf-dump-str title="16 characters">weekend_opens_at</span>"
          <span class=sf-dump-index>8</span> => "<span class=sf-dump-str title="17 characters">weekend_closes_at</span>"
          <span class=sf-dump-index>9</span> => "<span class=sf-dump-str title="19 characters">cancellation_policy</span>"
          <span class=sf-dump-index>10</span> => "<span class=sf-dump-str title="19 characters">restaurant_chain_id</span>"
          <span class=sf-dump-index>11</span> => "<span class=sf-dump-str title="9 characters">status_id</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">guarded</span>: <span class=sf-dump-note>array:1</span> [<samp data-depth=5 class=sf-dump-compact>
          <span class=sf-dump-index>0</span> => "<span class=sf-dump-str>*</span>"
        </samp>]
        #<span class=sf-dump-protected title="Protected property">forceDeleting</span>: <span class=sf-dump-const>false</span>
      </samp>}
    </samp>]
    #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
  </samp>}
  #<span class=sf-dump-protected title="Protected property">perPage</span>: <span class=sf-dump-num>10</span>
  #<span class=sf-dump-protected title="Protected property">currentPage</span>: <span class=sf-dump-num>1</span>
  #<span class=sf-dump-protected title="Protected property">path</span>: "<span class=sf-dump-str title="32 characters">http://localhost/api/restaurants</span>"
  #<span class=sf-dump-protected title="Protected property">query</span>: []
  #<span class=sf-dump-protected title="Protected property">fragment</span>: <span class=sf-dump-const>null</span>
  #<span class=sf-dump-protected title="Protected property">pageName</span>: "<span class=sf-dump-str title="4 characters">page</span>"
  #<span class=sf-dump-protected title="Protected property">escapeWhenCastingToString</span>: <span class=sf-dump-const>false</span>
  +<span class=sf-dump-public title="Public property">onEachSide</span>: <span class=sf-dump-num>3</span>
  #<span class=sf-dump-protected title="Protected property">options</span>: <span class=sf-dump-note>array:2</span> [<samp data-depth=2 class=sf-dump-compact>
    "<span class=sf-dump-key>path</span>" => "<span class=sf-dump-str title="32 characters">http://localhost/api/restaurants</span>"
    "<span class=sf-dump-key>pageName</span>" => "<span class=sf-dump-str title="4 characters">page</span>"
  </samp>]
  #<span class=sf-dump-protected title="Protected property">total</span>: <span class=sf-dump-num>20</span>
  #<span class=sf-dump-protected title="Protected property">lastPage</span>: <span class=sf-dump-num>2</span>
</samp>}
</pre>
<script>
    Sfdump("sf-dump-1847210059")
</script>
