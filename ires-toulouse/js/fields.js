const forms = document.querySelectorAll(".verifiy-form");
forms.forEach(function (form) {
    const formInputs = [...form.querySelectorAll("input")];
    const buttonSubmit = form.querySelector(".menu-submit");
    const nickname = form.querySelector(".update-nickname");
    // disable all inputs if the data is set
    formInputs.forEach(function (element) {
        if (element.dataset?.disabled) {
            element.classList.add("disabled");
            element.disabled = true;
        }
    });
    changeSubmitState();

    // add the input event to the form inputs
    form.addEventListener("input", function (event) {
        if (!String(event.target.type).includes("select")) {
            // uppercase caracters if necessary
            event.target.value = uppercase(event.target);

            // if a regex is present for the input
            if (event.target.dataset?.regex) {
                updateValueFromRegex(event.target);
            }
            if (nickname != null) {
                nickname.value = generateUserLogin();
            }
        }
        changeSubmitState();
    });
    if (nickname !== null && !nickname?.value) {
        nickname.value = generateUserLogin();
    }

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
     * @returns {string} generate the identifier of
     *                   the user for its login
     */
    function generateUserLogin() {
        return ((String(form.querySelector("#first_name").value).substr(0, 1) +
            form.querySelector("#last_name").value).toLowerCase())
            .replaceAll(/(\s|\W)+/g, "-");
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

    function changeSubmitState() {
        buttonSubmit.disabled = !areCorrectlyFilled();
        if(areCorrectlyFilled()){
            buttonSubmit.classList.remove("btn-outline-primary");
            buttonSubmit.classList.add("btn-primary");
        } else {
            buttonSubmit.classList.add("btn-outline-primary");
            buttonSubmit.classList.remove("btn-primary");
        }
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
            if (filled && input.dataset.formtype) {
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
                if(filled){
                    input.classList.add("is-valid");
                    input.classList.remove("is-invalid");
                } else {
                    input.classList.add("is-invalid");
                    input.classList.remove("is-valid");
                }
            }
        });
        return filled;
    }
});