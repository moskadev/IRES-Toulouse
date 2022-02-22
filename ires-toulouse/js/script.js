let hideContent = true;
executeCustomDropdownEvents();

/**
 * Custom dropdowns
 */
function executeCustomDropdownEvents() {
    document.onclick = function(){
        if(hideContent)
            document.querySelectorAll(".dropdown-content").forEach(drop => drop.style.display = "none")
        hideContent = true;
    };

    document.querySelectorAll(".dropdown-btn").forEach(btn => {
        btn.onclick = () => {
            hideContent = false;
            btn.nextElementSibling.style.display = "block";
        }
    });
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