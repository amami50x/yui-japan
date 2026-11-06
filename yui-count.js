/**
 * yui-japan 軽量カウント送信
 * - サブページ: yuiCountPageOpen(pageId)
 * - TOP MENU: yuiBindMenuCounts()
 * F5・戻る/進むは加算しない（観光HPと同系）
 */
(function () {
  function isNoCountNavigation() {
    try {
      var nav = performance.getEntriesByType && performance.getEntriesByType("navigation")[0];
      if (nav && nav.type) {
        return nav.type === "reload" || nav.type === "back_forward";
      }
      if (performance.navigation) {
        return performance.navigation.type === 1 || performance.navigation.type === 2;
      }
    } catch (e) {}
    return false;
  }

  function postForm(url, fields) {
    var parts = [];
    for (var k in fields) {
      if (!Object.prototype.hasOwnProperty.call(fields, k)) continue;
      parts.push(encodeURIComponent(k) + "=" + encodeURIComponent(fields[k]));
    }
    var body = parts.join("&");
    try {
      if (navigator.sendBeacon) {
        var blob = new Blob([body], { type: "application/x-www-form-urlencoded" });
        if (navigator.sendBeacon(url, blob)) return;
      }
    } catch (e1) {}
    try {
      fetch(url, {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: body,
        keepalive: true,
        credentials: "same-origin"
      }).catch(function () {});
    } catch (e2) {}
  }

  function yuiCountPageOpen(pageId) {
    if (!pageId || isNoCountNavigation()) return;
    postForm("pageview-counter.php", { action: "page_open", page: String(pageId) });
  }

  function itemIdFromHref(href) {
    if (!href) return "";
    var s = String(href);
    var hash = s.indexOf("#");
    if (hash >= 0) s = s.slice(hash + 1);
    s = s.replace(/^\//, "");
    if (!/^[A-Za-z0-9_\-]+$/.test(s)) return "";
    return s.slice(0, 60);
  }

  function yuiBindMenuCounts() {
    var toggles = document.querySelectorAll(".menu-toggle, #menu-toggle-btn");
    for (var i = 0; i < toggles.length; i++) {
      (function (btn) {
        if (btn.getAttribute("data-yui-menu-btn") === "1") return;
        btn.setAttribute("data-yui-menu-btn", "1");
        btn.addEventListener("click", function () {
          postForm("menu-click-counter.php", { type: "menu_button" });
        });
      })(toggles[i]);
    }
    var links = document.querySelectorAll(".menu-list a[href]");
    for (var j = 0; j < links.length; j++) {
      (function (a) {
        if (a.getAttribute("data-yui-menu-item") === "1") return;
        a.setAttribute("data-yui-menu-item", "1");
        a.addEventListener("click", function () {
          var id = itemIdFromHref(a.getAttribute("href"));
          if (!id) return;
          postForm("menu-click-counter.php", { type: "menu_item", id: id });
        });
      })(links[j]);
    }
  }

  window.yuiCountPageOpen = yuiCountPageOpen;
  window.yuiBindMenuCounts = yuiBindMenuCounts;
  window.yuiIsNoCountNavigation = isNoCountNavigation;
})();