# Formica - Form Management System

Formica is a comprehensive web-based form management system that allows users to create, manage, and analyze custom forms and surveys. Built with PHP, this application provides an intuitive interface for designing forms, collecting responses, and visualizing submission data.

## Features

- **User Authentication**: Secure registration and login system
- **Form Builder**: Intuitive drag-and-drop interface to create custom forms
- **Form Management**: Create, edit, publish, and delete forms
- **Submission Collection**: Collect and manage form responses
- **Data Visualization**: View submission statistics through charts and graphs
- **Access Control**: Set password protection and authentication requirements for forms
- **Response Management**: View, analyze, and download form submissions

## Project Structure

```
formix/
├── app/                # Application logic (in MVC pattern)
├── data/               # Database files
├── logic/              # Core business logic
│   ├── auth.php        # Authentication functions
│   ├── forms.php       # Form management functions
│   └── charts.php      # Chart generation functions
├── public/             # Publicly accessible files
│   ├── assets/         # CSS, JavaScript, and media files
│   └── *.php           # Public-facing PHP endpoints
├── templates/          # Reusable template files
│   ├── header.php      # Common header
│   └── footer.php      # Common footer
```

## Installation

1. Clone the repository:
   ```
   git clone https://github.com/Kristian2109/formix.git
   cd formix
   ```

2. **Database Configuration**:
   - The application is configured to use a MySQL database.
   - Copy the database connection settings from `logic/config.php` and adjust them to your environment.
   
   ```php
   return [
       'DB_CONNECTION' => 'mysql',
       'DB_HOST' => '127.0.0.1',
       'DB_PORT' => '3306',
       'DB_DATABASE' => 'formix',
       'DB_USERNAME' => 'root',
       'DB_PASSWORD' => ''
   ];
   ```
   
   > **Note for XAMPP users:** The default username is `root` and the password is typically empty.

3. Make sure you have PHP 7.4+ installed on your system with the `pdo_mysql` and `zip` extensions enabled.

4. Start a local web server (like Apache in XAMPP) and point it to the `public/` directory, or use the built-in PHP server:
   ```
   php -S localhost:8000 -t public
   ```

4. Open your browser and navigate to:
   ```
   http://localhost:8000
   ```

## Usage

1. Register a new account or log in with existing credentials
2. Create a new form using the form builder
3. Design your form by adding and configuring fields
4. Publish your form and share the link with respondents
5. View and analyze submissions through the dashboard
6. Export responses for further analysis

## Technologies Used

- **PHP**: Server-side scripting language
- **MySQL**: Database management
- **HTML/CSS/JavaScript**: Front-end technologies
- **Chart.js**: Data visualization library

## License

This project is licensed under the MIT License - see the LICENSE file for details. 