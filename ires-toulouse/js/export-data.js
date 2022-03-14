activateExportSelection();
onDropdownExportClick();


/**
 * If a checkbox is checked than the button to export the selection is enabled
 */
function activateExportSelection() {
    const checkboxesExport = [...document.querySelectorAll(".checkbox-export"),
        document.querySelector(".checkbox-export-all")];
    checkboxesExport.forEach(c => {
        c?.addEventListener("change", () => {
            document.querySelector(".export-selection").disabled =
                document.querySelectorAll(".checkbox-export:checked").length === 0
        });
    });
}

/**
 * Download Excel data file
 *
 * Horrible hack but working nicely
 */
function exportData(elem, type = "csv", userIds = "") {
    const authorizedTypes = ["csv", "excel"];
    if (userIds === "export-selection") {
        userIds = [...document.querySelectorAll('.checkbox-export:checked')]
            .map(u => u.value).join(",");
    }
    // users
    const users = document.createElement("input");
    users.type = "hidden";
    users.name = "export_users";
    users.value = userIds;
    elem.after(users);

    const extension = document.createElement("input");
    extension.type = "hidden";
    extension.name = "export_type";
    extension.value = authorizedTypes.includes(type) ? type : "csv";
    elem.after(extension);

    // deleting the extra input, ugly hack but hey it works great :D
    setTimeout(() => {
        users.remove();
        extension.remove();
    }, 10);
}

/**
 * Start the export on an option lick
 */
function onDropdownExportClick() {
    let firstOption = null;
    document.querySelectorAll(".export-dropdown").forEach(dropdown =>
        dropdown.addEventListener("change", e => {
            dropdown.querySelectorAll("option").forEach((o, i) => {
                if (i === 0) {
                    firstOption = o;
                } else if (o.value === e.target.value) {
                    exportData(dropdown, o.dataset?.type, o.dataset?.userIds);
                    dropdown.form?.submit();
                }
            });
            firstOption.selected = "true";
            e.preventDefault();
        })
    );
}