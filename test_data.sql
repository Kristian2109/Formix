
INSERT INTO users (email, password, created_at) VALUES
('admin@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-01 00:00:00'), 
('user@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-02 00:00:00'), 
('creator@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2024-01-03 00:00:00'); 

INSERT INTO forms (user_id, name, description, password, allow_multiple_submissions, require_auth, created_at) VALUES
(1, 'Customer Feedback Survey', 'Help us improve our services by providing your feedback', NULL, 1, 0, '2024-01-10 00:00:00'),
(1, 'Event Registration', 'Register for our upcoming workshop', 'event123', 0, 1, '2024-01-11 00:00:00'),
(2, 'Product Review', 'Share your experience with our products', NULL, 1, 0, '2024-01-12 00:00:00'),
(3, 'Job Application', 'Apply for open positions', NULL, 0, 1, '2024-01-13 00:00:00');

INSERT INTO form_fields (form_id, type, name, field_order, is_required) VALUES

(1, 'text', 'Name', 1, 1),
(1, 'text', 'Email', 2, 1),
(1, 'number', 'Rating (1-5)', 3, 1),
(1, 'textarea', 'Comments', 4, 0),

(2, 'text', 'Full Name', 1, 1),
(2, 'text', 'Phone Number', 2, 1),
(2, 'text', 'Attendance Type', 3, 1),

(3, 'text', 'Product Name', 1, 1),
(3, 'number', 'Rating (1-5)', 2, 1),
(3, 'textarea', 'Review', 3, 1),

(4, 'text', 'Full Name', 1, 1),
(4, 'text', 'Email', 2, 1),
(4, 'text', 'Position', 3, 1),
(4, 'textarea', 'Experience', 4, 1);

INSERT INTO form_submissions (form_id, user_id, submission_time) VALUES
(1, 2, '2024-01-15 10:00:00'),
(1, 3, '2024-01-15 11:00:00'),
(2, 2, '2024-01-16 09:00:00'),
(3, 1, '2024-01-17 14:00:00'),
(4, 2, '2024-01-18 16:00:00');

INSERT INTO form_field_values (submission_id, field_id, value) VALUES

(1, 1, 'John Doe'),
(1, 2, 'john@example.com'),
(1, 3, '5'),
(1, 4, 'Great service!'),


(2, 1, 'Jane Smith'),
(2, 2, 'jane@example.com'),
(2, 3, '4'),
(2, 4, 'Very satisfied with the product.'),

(3, 5, 'Alice Johnson'),
(3, 6, '555-123-4567'),
(3, 7, 'In-person'),

(4, 8, 'Premium Widget'),
(4, 9, '5'),
(4, 10, 'Excellent quality and durability.'),

(5, 11, 'Bob Brown'),
(5, 12, 'bob@example.com'),
(5, 13, 'Software Developer'),
(5, 14, '5 years of experience in web development'); 