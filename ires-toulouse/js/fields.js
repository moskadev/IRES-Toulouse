const forms = document.querySelectorAll(".verifiy-form");
forms.forEach(function (form) {
    const formInputs = [...form.querySelectorAll("input")];
    const buttonSubmit = form.querySelector(".menu-submit");
    // disable all inputs if the data is set
    formInputs.forEach(function (element) {
        if (element.dataset?.disabled) {
            element.classList.add("disabled");
            element.disabled = true;
        }
    });
    changeSubmitState(buttonSubmit);

    // add the input event to the form inputs
    form.addEventListener("input", function (event) {
        if (!String(event.target.type).includes("select")) {
            // uppercase characters if necessary
            event.target.value = uppercase(event.target);

            // if a regex is present for the input
            if (event.target.dataset?.regex) {
                updateValueFromRegex(event.target);
            }
            checkCorrectlyFilled(event.target);
        }
        changeSubmitState(buttonSubmit);
    });
    form.addEventListener("click", (event) => {
        if (event.target.dataset?.formtype === "radio") {
            changeSwitchState(event.target);
            updateChildrenFieldDisplay(event.target);
        }
    });
    formInputs.forEach(input => {
        if (input.dataset?.formtype === "radio") {
            updateChildrenFieldDisplay(input);
        }
        checkCorrectlyFilled(input);
    });


    /**
     * Dynamically update the value in the input from
     * the RegEx if it exists
     *
     * @param inout to update
     */
    function updateValueFromRegex(inout) {
        const regex = new RegExp(inout.dataset.regex, "g").exec(inout.value);
        if (regex !== null) { // analyze the input value
            inout.value = regex[0]; // change the value corresponding to the regex
        }
    }

    /**
     * @param element the targeted element
     * @returns {string|*} uppercased value if the data is true
     */
    function uppercase(element) {
        return element.dataset?.uppercase ?
            String(element.value).toUpperCase() :
            element.value;
    }

    /**
     * Change the stat of all given button if all the data
     * has been correctly filled
     * @param btn the button to change
     */
    function changeSubmitState(btn) {
        if (btn !== null) {
            btn.disabled = !areCorrectlyFilled();
        }
    }

    /**
     * Verification of an input of the form by evaluating if they
     * have a value and is correctly filled
     *
     * @returns {boolean} true if the value of this input has
     *                    been entered and follows the imposed format
     */
    function checkCorrectlyFilled(input) {
        let filled = true;
        /*
         * Checks if it's the right input we're looking for with its formType.
         * If the var "filled" is on true, we check again each value :
         * - Required case : we check if the RegEx exists and we test
         *   with the value of the input. If not, we check if the input is not empty
         * - Not required case : it means that if the value or RegEx is empty, it is "filled".
         *   But if the RegEx exists, we check if the value follows it.
         *
         * The value of the inputs are also verified on the back-end
         */
        if ((input.dataset.formtype === "text" || input.dataset.formtype === "email") && input.dataset.formtype) {
            const regex = input.dataset.regex ? new RegExp("^" + input.dataset.regex + "$") : "";
            if (input.dataset.required) {
                /*
                 * If the regex exists, we test the value, if not, we check
                 * if the input contains a value
                 */
                filled = regex ? (input.value && regex.test(input.value)) : input.value;
            } else {
                /*
                 * Checks if the value or the regex is empty, so it is "filled".
                 * If the value and regex exists, we test the value
                 */
                filled = !input.value || !regex || regex.test(input.value);
            }
            if (!input.dataset.disabled) {
                if (filled) {
                    input.classList.add("is-valid");
                    input.classList.remove("is-invalid");
                } else {
                    input.classList.add("is-invalid");
                    input.classList.remove("is-valid");
                }
            }
        }
        return filled;
    }

    /**
     * Verification of each input of the form by evaluating if they
     * have a value.
     *
     * @returns {boolean} true if the value of each input has
     *                    been entered and follows the imposed format
     */
    function areCorrectlyFilled() {
        let filled = true;
        formInputs.some(input => {
            if (filled) {
                filled = checkCorrectlyFilled(input);
            }
        });
        return filled;
    }

    /**
     * Hide the parent row if a switch is off and this data has this child
     * @param parentRadio the parent switch
     */
    function updateChildrenFieldDisplay(parentRadio) {
        const children = form.querySelectorAll("input[data-parent=" + parentRadio.name + "]");
        children.forEach(child => {
            if (!parentRadio.value || parentRadio.value === "non") {
                child.value = child.dataset?.formtype === "radio" ? "non" : "";
            }
            /*
             * We are searching in the form if there's a row corresponding to
             * where is stored our child.
             * We will hide it if the parent switch is equal to false or
             * show it if true
             */
            getParents(child).reverse().forEach(row => {
                if (row.tagName === "TR") {
                    if (!parentRadio.value || parentRadio.value === "non") {
                        row.style.display = "none";
                    } else {
                        row.style.removeProperty("display");
                    }
                }
            });
        })
    }

    /**
     * Change the switch state on off or on when the user clicks on it
     * @param target the switch element
     */
    function changeSwitchState(target) {
        let switchBtn = null;
        if (!target.classList.contains("switch")) {
            getParents(target).reverse().forEach(el => {
                if (!switchBtn && el.classList?.contains("switch")) {
                    switchBtn = el;
                }
            });
        } else {
            switchBtn = target;
        }
        if (switchBtn) {
            const switchRadio = switchBtn.firstElementChild;
            if (target === switchRadio && !switchRadio.disabled) {
                switchRadio.value = !switchRadio.value || switchRadio.value === "oui" ? "non" : "oui";
            }
        }
    }
});

submitFormOnItemSelect();

/**
 * Submit the select input's form when a new item has been
 * selected by the user
 */
function submitFormOnItemSelect() {
    document.querySelectorAll(".confirm-item").forEach(el =>
        el.addEventListener("change", () => {
            getParents(el).reverse().forEach(parent => {
                if (parent.tagName === "FORM") {
                    parent.submit();
                }
            });
        })
    );
}