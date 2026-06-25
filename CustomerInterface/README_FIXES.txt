Lounge Royale Customer Interface fixes

Copy these replacement files over the matching files in:
C:\xampp\htdocs\LoungeRoyale\CustomerInterface

Fixed:
- register.php no longer requires confirm_password.
- register.php now checks duplicate email addresses.
- login.php now saves user_id and role in the session.
- login.php now redirects customers to customer/dashboard_customer.php.
- logout.php was added.
- about.html was added for the existing About hotspot.
- customer/booking.html now uses APPOINTMENT PAGE_customer.png.
- customer/services.php now requires login and uses safe service lookup.
- customer/booking.php now handles staff_id 0 as no preference.
- customer/booking.php now validates date, Monday closure, and business hours.
- customer/cancel_appointment.php now only cancels the logged-in customer's pending appointments.
- customer/profile.php and customer/my_appointments.php now escape displayed values.
- customer/Dashboard.php now redirects to customer/dashboard_customer.php.

After copying, test registration, login, booking with a specific technician,
booking with no preference, cancellation, and logout.
