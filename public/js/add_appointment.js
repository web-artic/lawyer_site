

document.addEventListener("DOMContentLoaded", function() {
    // Открытие модального окна
    document.getElementById('addAppointment1')?.addEventListener('click', function() {
        document.getElementById('addAppointmentModal1').style.display = 'flex';
    });

    // Закрытие модального окна по клику на крестик
    document.querySelector('.close')?.addEventListener('click', function() {
        document.getElementById('addAppointmentModal1').style.display = 'none';
    });

    // Закрытие модального окна при клике вне формы
    window.addEventListener('click', function(event) {
        if (event.target === document.getElementById('addAppointmentModal1')) {
            document.getElementById('addAppointmentModal1').style.display = 'none';
        }
    });

    // Проверка наличия ошибок при загрузке страницы
    const hasErrors = document.getElementById('hasErrors')?.getAttribute('data-errors') === '1';
    if (hasErrors) {
        document.getElementById('addAppointmentModal1').style.display = 'flex';
    }
});

function openModal(appointmentId, appointmentDate) {
    // Устанавливаем данные в модальном окне
    document.getElementById('modalAppointmentId').value = appointmentId;
    document.getElementById('modalDate').value = appointmentDate;

    // Показываем модальное окно
    document.getElementById('updateAppointmentModal').style.display = 'flex';
}

// Проверяем наличие ошибок валидации при загрузке страницы
const hasErrorsUpdate = document.getElementById('hasErrorsUpdate')?.getAttribute('data-errors-update') === '1';
if (hasErrorsUpdate) {
    // Убедимся, что модальное окно открывается только один раз
    console.warn("Обнаружены ошибки. Проверьте данные формы.");
    document.getElementById('updateAppointmentModal').style.display = 'flex';
}

function closeModal() {
    document.getElementById('updateAppointmentModal').style.display = 'none';
}

// Закрытие модального окна при клике вне его области
window.onclick = function(event) {
    const modal = document.getElementById('updateAppointmentModal');
    if (event.target === modal) {
        modal.style.display = "none";
    }
}


