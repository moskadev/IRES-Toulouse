const openPopupButtons = document.querySelectorAll("[data-popup-target]");
const closePopupButtons = document.querySelectorAll("[data-close-button]");
const popup = document.querySelector(".popup");

openPopupButtons.forEach(button => {
    button.addEventListener("click", () => {
        openPopup();
    })
})

popup?.addEventListener("click", (e) => {
    if(e.target.classList.contains("popup") ||
        e.target.dataset?.closeButton !== undefined) {
        closePopup()
    }
})

function openPopup() {
    popup?.classList.add("active");
}

function closePopup() {
    popup?.classList.remove("active")
}

function setUserInfo(user_id, first_name, last_name) {
    document.querySelector(".popup-title").textContent = "Suppression de : " + first_name + " " + last_name;
    document.getElementById("userId").value = user_id;
}