# Bicycle Ride Tracker 🚴

Bicycle Ride Tracker - Add Ride Name, Distance and Km.

- HTML
- Bulma CSS
- Javascript
- Chart.js
- PHP PDO for API and Store Users Ride data into Database
- MYSQL

## Setup and Modification

- `/api/` folder
- Copy the SQL Query from `query.sql` file and Create Table for users and Ride data
- Add API KEY and Database Details in `.env` file

```env
APIKEY=xxxxxxxx
DBHOST=localhost
DBNAME=xxxxxxxxxx
DBUSER=xxxxxxxxxxxx
DBPASSWORD=xxxxxxxxxxxx
```

- Register New user

```sh
curl --request POST \
  --url http://localhost:6004/api/new.php \
  --header 'Authorization: Bearer APIKEY' \
  --header 'Content-Type: application/json' \
  --data '{
  "username": "username",
  "password": "password"
}
'
```

- Approve users Manually from database : **1 for approved and 0 Not approved**
- Done

## Access and Add data

- Just Login and Start adding your Ride Data

```sh

## Start PHP local server
php -S localhost:6004

## Open URL
http://localhost:6004/

```

## LICENSE

MIT
