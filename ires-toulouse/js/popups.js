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

function setGroupInfo(group_name, group_id) {
    document.querySelector(".popup-title").textContent = "Suppression du groupe : " + group_name;
    document.getElementById("groupId").value = group_id;
}

function setDeletionInfo(user_id, user_name) {
    document.querySelector(".popup-title").textContent = "Suppression de " + user_name + " du groupe.";
    document.getElementById("removeMember").value = user_id;
}

function setResponsableInfo(resp_id, resp_name) {
    document.querySelector(".popup-title").textContent = "Retirer le role responsable de " + resp_name;
    document.getElementById("deleteResp").value = resp_id;
    document.getElementById("text").innerHTML = "Êtes vous sur de vouloir retirer cette personne des reponsables du groupe ?";
}