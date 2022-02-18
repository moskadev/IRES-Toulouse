const openPopupButtons = document.querySelectorAll('[data-popup-target]');
const closePopupButtons = document.querySelectorAll('[data-close-button]');
const overlay = document.getElementById('overlay');

openPopupButtons.forEach(button => {
    button.addEventListener('click', () => {
        const popup = document.querySelector(button.dataset.popupTarget)
        openPopup(popup)
    })
})
overlay?.addEventListener('click', () => {
    const popups = document.querySelectorAll('.popup-delete.active')
    popups.forEach(popup => {
        closePopup(popup)
    })
})

closePopupButtons.forEach(button => {
    button.addEventListener('click', () => {
        const popup = button.closest('.popup-delete')
        closePopup(popup)
    })
})

function openPopup(popup) {
    if (popup == null) return;
    popup.classList.add('active')
    overlay?.classList.add('active')
}

function closePopup(popup) {
    if (popup == null) return;
    popup.classList.remove('active')
    overlay?.classList.remove('active')
}

const popupTitle = document.getElementById('popup-title');
function setUserInfo(user_id, first_name, last_name) {
    popupTitle.textContent = "Suppression de : " + first_name + " " + last_name
    document.getElementById('userId').value = user_id
}