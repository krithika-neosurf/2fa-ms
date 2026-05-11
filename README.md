# Installation

Please check the official Laravel installation guide for server requirements before you start. [Official Documentation](https://laravel.com/docs/8.x/installation#installation)

Adapt the following instructions depending on your environment (local, dev, or prod).

## 1) Database creation

Connect to MySQL:
``` bash
mysql -u{{ your MySQL user }} -p{{ your password }}
```

For dev and prod:
``` bash
mysql -h{{ your MySQL host }} -u{{ your MySQL user }} -p{{ your password }}
```

> *NOTE*
> If you want to put your password in the command, there is no space between ```-p``` and the password itself.

Create a new database ("local" for "local", "dev" for development", "prod" for production):
``` sql
CREATE DATABASE `neosurf-2fa-ms-local` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci
```

## 2) Project installation

Clone the project from Bitbucket:

    git clone git@bitbucket.org:neosurfiteam/2fa-ms.git

Switch to the repo folder:

    cd 2fa-ms

Switch to the develop branch ("develop" for local & dev, "master" for prod):

    git checkout develop

Install all the dependencies via Composer:

    composer install

## 3) Project configuration

Create your .env file ("local" for "local", "dev" for development", "prod" for production):

    cp .env.local.base .env

Generate a new application key:

    php artisan key:generate

Fill all remaining empty values in your .env file. It means you can have to create your own personal account sometimes (On Pusher.com, Mailtrap.io, etc.).
In local, if needed, you can import the microservices Urls to your .env and change them with  "run php artisan addUrls:env"

Clear cache:

    php artisan cache:clear
    php artisan config:clear

## 4) Tables creation

In local:

    php artisan migrate --seed

In prod and dev:

    php artisan migrate
