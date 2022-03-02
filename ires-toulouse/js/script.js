/**
 * Reload the page
 */
function reloadPage() {
    if (window.history.replaceState) {
        window.history.replaceState(null, null, window.location.href);
    }
    window.location = window.location.href;
}

/**
 * Return all the element given's parents
 *
 * @param element current element
 * @returns {Element[]} all element's parents
 */
function getParents(element) {
    let parents = [];
    while (element) {
        parents.unshift(element);
        element = element.parentNode;
    }
    return parents;
}