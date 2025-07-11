# Portfolio Admin Panel

A PHP-based admin panel to manage a static portfolio website without redeploying it.

## Features

🔒 **Authentication System**
- Secure login with hardcoded credentials
- PHP session management
- Automatic logout functionality

🛠 **Project Management**
- Add new portfolio projects via web interface
- JSON-based data storage (no database required)
- Real-time project preview

📱 **Dynamic Frontend**
- JavaScript-powered project loading
- Responsive design
- Modal functionality for project details

## Setup Instructions

### 1. Server Requirements
- PHP 7.4 or higher
- Web server (Apache/Nginx)
- mod_rewrite enabled (for .htaccess)

### 2. File Structure
```
portolio/
├── admin/
│   ├── login.php          # Admin login page
│   ├── dashboard.php      # Main admin dashboard
│   ├── logout.php         # Logout functionality
│   └── .htaccess          # Security rules
├── api/
│   └── projects.php       # API endpoint for projects
├── assets/
│   ├── style.css          # Main stylesheet
│   ├── portfolio.css      # Portfolio page styles
│   ├── portfolio-loader.js # Frontend JavaScript
│   ├── contact-email.js   # Contact form JavaScript
│   └── [images]           # All portfolio images
├── data/
│   └── projects.json      # Project data storage
├── index.html             # Home page
├── portfolio.html         # Portfolio page
└── test.html              # Path testing utility
```

### 3. Default Login Credentials
- **Username:** `admin`
- **Password:** `portfolio2025`

⚠️ **Important:** Change these credentials in `admin/login.php` for production use.

### 4. File Permissions
Ensure the `data/` directory is writable by the web server:
```bash
chmod 755 data/
chmod 644 data/projects.json
```

## Usage

### Admin Panel Access
1. Navigate to `yourdomain.com/admin/`
2. Login with the credentials above
3. Use the dashboard to add new projects

### Adding Projects
1. Fill in the project form:
   - **Title:** Project name
   - **Description:** Project description
   - **Image URL:** Full URL to the project image
2. Click "Add Project"
3. Projects are automatically saved to `data/projects.json`

### Frontend Integration
The portfolio pages automatically load projects from `data/projects.json`:
- Home page shows the first 4 projects
- Portfolio page shows all projects
- Projects are displayed with your existing CSS styling

## Security Features

🔐 **Multiple Security Layers**
- PHP session authentication
- .htaccess protection for admin folder
- Input sanitization and validation
- XSS protection headers

## Customization

### Changing Login Credentials
Edit `admin/login.php`:
```php
$admin_username = "your_username";
$admin_password = "your_password";
```

### Modifying Project Structure
Edit the project object in `admin/dashboard.php`:
```php
$new_project = [
    "title" => $title,
    "description" => $description,
    "image" => $image_url,
    "date_added" => date('Y-m-d H:i:s'),
    // Add custom fields here
];
```

### Styling
- Admin panel styles are embedded in each PHP file
- Frontend uses your existing CSS files
- No external dependencies or frameworks

## Troubleshooting

### Common Issues

**Projects not loading:**
- Check file permissions on `data/projects.json`
- Verify the file path in `portfolio-loader.js`
- Check browser console for JavaScript errors

**Admin login not working:**
- Verify PHP sessions are enabled
- Check server error logs
- Ensure .htaccess is properly configured

**Images not displaying:**
- Verify image URLs are accessible
- Check CORS settings if using external images
- Ensure image URLs are complete (including http/https)

## File Descriptions

- `admin/login.php` - Authentication page with hardcoded credentials
- `admin/dashboard.php` - Main admin interface for project management
- `admin/logout.php` - Session destruction and logout
- `admin/.htaccess` - Security rules for admin folder
- `api/projects.php` - API endpoint for serving project data
- `assets/style.css` - Main website stylesheet
- `assets/portfolio.css` - Portfolio page specific styles
- `assets/portfolio-loader.js` - Frontend JavaScript for dynamic loading
- `assets/contact-email.js` - Contact form functionality
- `data/projects.json` - JSON storage for project data
- `index.html` - Home page with dynamic portfolio section
- `portfolio.html` - Full portfolio page with dynamic content
- `test.html` - Utility to test all file paths and functionality

## Support

This system is designed to be simple and self-contained. All code is vanilla PHP and JavaScript with no external dependencies. 