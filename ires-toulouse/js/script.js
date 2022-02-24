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
 * Download Excel data file
 *
 * HACK dégueulasse mais on fait avec
 */
function downloadExcelFile(btn, userId = "") {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "download_excel";
    input.value = userId;
    btn.after(input);

    // on supprime pour éviter qu'il interfere avec les autres boutons
    setTimeout(() => {
        input.remove();
    }, 10);
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