const forms = document.querySelectorAll("form");
forms.forEach(function(form) {

    const formInputs = [...form.querySelectorAll("input")];
    const buttonCreate = form.querySelector("input[type=submit]");

    form.querySelector("#nickname").value = generateUserLogin();

    form.addEventListener("click", function (event) {
        console.log(event.target);
    })

    // add the input event to the form inputs
    form.addEventListener("input", function (event) {
        event.target.value = uppercase(event.target)
            .replaceAll(/\s/g, " ");

        form.querySelector("#nickname").value = generateUserLogin();
        buttonCreate.disabled = !areFilled();
        buttonCreate.style.cursor = areFilled() ? "pointer" : "not-allowed";

        console.log(event.target.value)
    });

    /**
     * @returns {string} generate the identifier of
     *                   the user for its login
     */
    function generateUserLogin(){
        return ((String(form.querySelector("#first_name").value).substr(0, 1) +
            form.querySelector("#last_name").value).toLowerCase())
            .replaceAll(/(\s|\W)+/g, "-");
    }

    /**
     * @param element the targeted element
     * @returns {string|*} uppercased value if the data is true
     */
    function uppercase(element){
        return element.dataset?.uppercase ?
            String(element.value).toUpperCase() :
            element.value;
    }

    /**
     * Verification of each input of the form by evaluating if they
     * have a value.
     *
     * @returns {boolean} true if the value of each input has
     *                    been entered and follows the imposed format
     */
    function areFilled() {
        let filled = true;
        /* TODO ne prend pas en compte les autres input et select,
         *      il faut ajouter la lecture de tout lorsqu'elles
         *      commencent à être complétés même non obligatoire
         */
        formInputs.some(input => {
            if(filled && input.dataset.required) {
                console.log(input.dataset.regex)
                filled = input.dataset.regex ?
                    (new RegExp(input.dataset.regex)).test(input.value) :
                    input.value;
            }
        });
        return filled;
    }
});