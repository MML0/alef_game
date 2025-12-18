// loader.js
(function (global) {
  const DEFAULT_ID = "appLoader";

  function getEl(id = DEFAULT_ID) {
    const el = document.getElementById(id);
    if (!el) throw new Error(`Loader element not found. Expected id="${id}"`);
    return el;
  }

  function show({ id = DEFAULT_ID } = {}) {
    const el = getEl(id);
    el.classList.add("is-active");
    el.setAttribute("aria-hidden", "false");
  }

  // Fades out, then disables interactions (via CSS class removal)
  function hide({ id = DEFAULT_ID, fadeMs = 280 } = {}) {
    const el = getEl(id);

    // if already hidden, do nothing
    if (!el.classList.contains("is-active")) return;

    el.classList.remove("is-active");
    el.setAttribute("aria-hidden", "true");

    // In case you want to fully remove it from DOM after fade:
    // setTimeout(() => el.remove(), fadeMs);
  }

  global.Loader = { show, hide };
})(window);

// Loader.show();                 // show immediately
  // ...do stuff...
  // Loader.hide();  
setTimeout(Loader.show, 1000);
setTimeout(Loader.hide, 4000);