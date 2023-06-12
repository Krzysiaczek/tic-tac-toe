# tic-tac-toe
This is a mulitplayer tic-tac-toe game build in Laravel 10.13 and PHP 8.2.

## Installation

Clone repository to chosen folder
```
$ git clone git@github.com:Krzysiaczek/tic-tac-toe.git
```

This should create a folder called `tic-tac-toe` in your previousl chosen directory.

Go insed that folder and run composer install.

```
$ cd tic-tac-toe
$ composer install
```
Generate the key
```
$ php artisan key:generate
```

Copy `.env.example` to `.env` and edit the content - especcially DB settings
```
$ cp .env.example .env
$ vim .env 
```
Initiate database
```
$ php artisan migrate
```

Create css and javescript files

```
$ npm run dev
```

In another terminal window navigate to the same folder `tic-tac-toe` and start local server
```
$ php artisan serve
```
The site should be available in the browser `http://localhost:8000`. Even port number could be slightly diffferent, if you've got something already running.

### Demo
Demosntration site is available at http://138.68.77.32
