// Работа с формой регистрации

// Получение элементов
var modal = document.getElementById("authModal");
var regmodal = document.getElementById("registrModal");
var btn = document.getElementById("loginBtn");
var regbtn = document.getElementById("registrBtn");
var span = document.getElementsByClassName("close")[0];
var regspan = document.getElementsByClassName("close")[1];

// Открытие модального окна при нажатии на иконку
btn.onclick = function() {
    modal.style.display = "flex";
};

// Открытие модального окна при нажатии на иконку
regbtn.onclick = function() {
    regmodal.style.display = "flex";
};

// Закрытие модального окна при нажатии на <span> (x)
span.onclick = function() {
    modal.style.display = "none";
};

// Закрытие модального окна при нажатии на <span> (x)
regspan.onclick = function() {
    regmodal.style.display = "none";
};

// Закрытие модального окна при клике вне его
window.onclick = function(event) {
    if (event.target == modal || event.target == regmodal) {
        modal.style.display = "none";
        regmodal.style.display = "none";
    }
};
