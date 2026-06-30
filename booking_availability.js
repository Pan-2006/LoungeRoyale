(function () {
  const forms = document.querySelectorAll('form.booking-panel');

  forms.forEach((form) => {
    const staffSelect = form.querySelector('select[name="staff_id"]');
    const dateInput = form.querySelector('input[name="date"]');
    const timeSelect = form.querySelector('select[name="time"]');

    if (!staffSelect || !dateInput || !timeSelect) return;

    const notice = document.createElement('p');
    notice.className = 'availability-note';
    notice.setAttribute('aria-live', 'polite');
    timeSelect.closest('label').appendChild(notice);

    async function refreshAvailability() {
      Array.from(timeSelect.options).forEach((option) => {
        option.disabled = false;
        option.textContent = option.dataset.label || option.textContent.replace(' - Booked', '');
      });

      notice.textContent = '';

      if (!staffSelect.value || !dateInput.value) return;

      try {
        const response = await fetch(
          `check_availability.php?staff_id=${encodeURIComponent(staffSelect.value)}&date=${encodeURIComponent(dateInput.value)}`,
          { cache: 'no-store' }
        );
        const data = await response.json();
        const booked = new Set(data.booked || []);

        Array.from(timeSelect.options).forEach((option) => {
          if (!option.value) return;
          option.dataset.label = option.dataset.label || option.textContent;

          if (booked.has(option.value)) {
            option.disabled = true;
            option.textContent = `${option.dataset.label} - Booked`;
          }
        });

        if (booked.has(timeSelect.value)) {
          timeSelect.value = '';
        }

        notice.textContent = booked.size
          ? 'Booked times are disabled for the selected employee.'
          : 'All listed times are available for this employee.';
      } catch (error) {
        notice.textContent = 'Could not check availability. The booking will still be checked before saving.';
      }
    }

    staffSelect.addEventListener('change', refreshAvailability);
    dateInput.addEventListener('change', refreshAvailability);

    form.addEventListener('submit', (event) => {
      const selected = timeSelect.options[timeSelect.selectedIndex];
      if (selected && selected.disabled) {
        event.preventDefault();
        notice.textContent = 'Please choose an available time.';
      }
    });
  });
}());
