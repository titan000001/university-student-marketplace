USE university_student_marketplace;

INSERT INTO categories (category_name, description)
VALUES
('Books', 'Academic and non-academic books'),
('Electronics', 'Electronic devices and accessories'),
('Furniture', 'Furniture for student accommodation'),
('Notes', 'Class notes and study materials'),
('Accessories', 'Bags, stationery and other accessories');

INSERT INTO users
(full_name, email, student_id, department, password_hash, role)
VALUES
(
'Rahim Islam',
'rahim@university.edu',
'CSE22001',
'CSE',
'$2y$10$/keEAc.Cwuu5NRqvpBUvmuHVMZern7d4FhBEv4y4Jc8KdmfhZZ3ye',
'student'
),
(
'Karim Ahmed',
'karim@university.edu',
'CSE22002',
'CSE',
'$2y$10$/keEAc.Cwuu5NRqvpBUvmuHVMZern7d4FhBEv4y4Jc8KdmfhZZ3ye',
'student'
),
(
'Admin User',
'admin@university.edu',
'ADMIN001',
'Administration',
'$2y$10$/keEAc.Cwuu5NRqvpBUvmuHVMZern7d4FhBEv4y4Jc8KdmfhZZ3ye',
'admin'
);


INSERT INTO products
(
seller_id,
category_id,
title,
description,
price,
tags,
image_url,
product_condition,
status
)
VALUES
(
1,
1,
'Java Programming Book',
'Slightly used programming textbook.',
450.00,
'java,programming,book',
'images/java-book.jpg',
'Good',
'Active'
),
(
2,
2,
'Scientific Calculator',
'Casio scientific calculator.',
900.00,
'calculator,electronics',
'images/calculator.jpg',
'Like New',
'Active'
);


INSERT INTO transactions
(
product_id,
buyer_id,
seller_id,
amount,
status
)
VALUES
(
1,
2,
1,
450.00,
'Completed'
);


