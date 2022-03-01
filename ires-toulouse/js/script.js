activateExportSelection();

/**
 * Download Excel data file
 *
 * HACK dégueulasse mais on fait avec
 */
function downloadExcelFile(btn, userIds = "") {
    if(userIds === "export-selection"){
        userIds = [...document.querySelectorAll('.checkbox-excel:checked')]
            .map(u => u.value).join(",");
        console.log(userIds);
    }
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = "download_excel";
    input.value = userIds;
    btn.after(input);

    // on supprime pour éviter qu'il interfere avec les autres boutons
    setTimeout(() => {
        input.remove();
    }, 10);
}

/**
 * If a checkbox is checked than the button to export the selection is enabled
 */
function activateExportSelection(){
    document.querySelectorAll(".checkbox-excel").forEach(c => {
        c.addEventListener("change", () => {
            document.querySelector(".export-selection").disabled =
                document.querySelectorAll(".checkbox-excel:checked").length === 0;
        });
    });
}

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