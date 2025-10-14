# Coocooschka

A simple PHP-based project management application.

## Project Structure

```
coocooschka/
├─ public/              # Web-accessible files
│  ├─ login.php        # User login page
│  ├─ overview.php     # Project overview dashboard
│  ├─ logout.php       # Logout functionality
│  ├─ styles.css       # Application styles
│  └─ .htaccess        # Apache configuration
├─ config/             # Configuration files
│  └─ config.php       # Application configuration
├─ data/               # Data storage
│  └─ projects.json    # Project data (JSON format)
└─ README.md           # This file
```

## Features

- Simple authentication system
- Project overview dashboard
- Responsive design
- Security headers and file protection
- JSON-based data storage

## Setup

1. Clone or download this repository to your web server
2. Ensure Apache/Nginx is configured to serve from the `public/` directory
3. Update the authentication credentials in `config/config.php`
4. Access the application via your web browser

## Default Login Credentials

**⚠️ Change these immediately in production!**

- Username: `admin` | Password: `password123`
- Username: `user` | Password: `userpass`

Update these in `config/config.php` in the `authenticate()` function.

## Requirements

- PHP 7.4 or higher
- Web server (Apache/Nginx)
- mod_rewrite enabled (for Apache)

## Security Notes

- Change default passwords before deployment
- Enable HTTPS in production
- Review and update security headers in `.htaccess`
- Consider using a proper database instead of JSON files for production

## Development

The application uses a simple file-based structure:

- **Authentication**: Basic username/password validation
- **Data Storage**: JSON files (consider database for production)
- **Styling**: Custom CSS with responsive design
- **Security**: Basic protection via `.htaccess` rules

## License

This project is open source. Please check the license file for more details.

## Privacy / No-Index

We set `X-Robots-Tag: noindex, nofollow, noarchive, nosnippet` via `.htaccess` in hub and templates.

We ship `robots.txt` with `Disallow: /` in hub and templates.

Each new subdomain created with the script will include both.

Optional: for hard blocking, enable "Passwortgeschützte Verzeichnisse" in Netcup for the subdomain root.