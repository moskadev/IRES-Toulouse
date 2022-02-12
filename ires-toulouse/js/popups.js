const openPopupButtons = document.querySelectorAll('[data-popup-target]');
const closePopupButtons = document.querySelectorAll('[data-close-button]');
const overlay = document.getElementById('overlay');

const first_name = document.getElementsByName('first_name');


openPopupButtons.forEach(button => {
    button.addEventListener('click', () => {
        const popup = document.querySelector(button.dataset.popupTarget)
        openPopup(popup)
    })
})
overlay.addEventListener('click', () => {
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
    overlay.classList.add('active')
}

function closePopup(popup) {
    if (popup == null) return;
    popup.classList.remove('active')
    overlay.classList.remove('active')
}