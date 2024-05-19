# Bicycle Ride Tracker 🚴

Bicycle Ride Tracker - Add Ride Name, Distance and Km.

- HTML
- Bulma CSS
- Javascript
- Chart.js
- PHP PDO for API and Store Users Ride data in Database
- MYSQL

## Modification

- Open `/api/` folder
- Update your database details in `/api/config.php`
- Copy the SQL Query from `query.sql` file and Create Table for users and Ride data
- Add users Manually: **1 for approved and 0 Not approved**
- open `index.php` Home page file and Add Auth Key to access the Form and data
- Generate Strong Random Auth Key From - <https://proton.me/pass/password-generator>

```php
define('API_KEY', '<REPLACE WITH AUTH KEY>');
```

- Done

## Access and Add data

- View Ride Data and insert New Data using this URL format

```sh

## Start PHP local server
php -S localhost:6004

## Open URL
http://localhost:6004/?username=santhosh

```

## LICENSE

MIT
