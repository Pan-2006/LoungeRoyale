INSERT INTO staff (staff_id, staff_name, age, address, phone, email) VALUES
(1, 'Sabrina Reyes', 24, 'San Mateo, Rizal', '09170000001', 'sabrina.reyes@loungeroyale.local'),
(2, 'Mika Santos', 25, 'Marikina City', '09170000002', 'mika.santos@loungeroyale.local'),
(3, 'Angela Cruz', 27, 'Pasig City', '09170000003', 'angela.cruz@loungeroyale.local'),
(4, 'Janelle Lim', 23, 'Cainta, Rizal', '09170000004', 'janelle.lim@loungeroyale.local'),
(5, 'Rica Mendoza', 29, 'Quezon City', '09170000005', 'rica.mendoza@loungeroyale.local'),
(6, 'Trisha Navarro', 26, 'Antipolo City', '09170000006', 'trisha.navarro@loungeroyale.local')
ON DUPLICATE KEY UPDATE
staff_name = VALUES(staff_name),
age = VALUES(age),
address = VALUES(address),
phone = VALUES(phone),
email = VALUES(email);
