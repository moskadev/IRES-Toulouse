const openPopupButtons = document.querySelectorAll("[data-popup-target]");
const closePopupButtons = document.querySelectorAll("[data-close-button]");
const popup = document.querySelector(".popup");

openPopupButtons.forEach(button => {
    button.addEventListener("click", () => {
        openPopup();
    })
})

popup?.addEventListener("click", (e) => {
    if (e.target.classList.contains("popup") ||
        e.target.dataset?.closeButton !== undefined) {
        closePopup()
    }
})

/**
 * Show the popup to the user
 */
function openPopup() {
    popup?.classList.add("active");
}

/**
 * Remove the popup from the user
 */
function closePopup() {
    popup?.classList.remove("active")
}

/**
 * Show the user's deletion popup
 *
 * @param user_id user's identifier
 * @param first_name user's first name
 * @param last_name user's last name
 */
function setUserInfo(user_id, first_name, last_name) {
    document.querySelector(".popup-title").textContent = "Suppression de : " + first_name + " " + last_name;
    document.getElementById("userId").value = user_id;
}

/**
 * Show the group's deletion popup
 *
 * @param group_name the group's name
 * @param group_id the group's identifier
 */
function setGroupInfo(group_name, group_id) {
    document.querySelector(".popup-title").textContent = "Suppression du groupe : " + group_name;
    document.getElementById("groupId").value = group_id;
}

/**
 * Show the member group's deletion popup
 * @param user_id the member's identifier
 * @param user_name the member's name
 */
function setDeletionInfo(user_id, user_name) {
    document.querySelector(".popup-title").textContent = "Suppression de " + user_name + " du groupe.";
    document.getElementById("removeMember").value = user_id;
}


/**
 * Show the responsable group's deletion popup
 * @param resp_id the responsable's identifier
 * @param resp_name the responsable's name
 */
function setResponsableInfo(resp_id, resp_name) {
    document.querySelector(".popup-title").textContent = "Retirer le role responsable de " + resp_name;
    document.getElementById("deleteResp").value = resp_id;
    document.getElementById("text").innerHTML = "Êtes vous sur de vouloir retirer cette personne des reponsables du groupe ?";
}