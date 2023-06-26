# Cella Works Backend API
`Version 1.0 - Updated 26-06-2023`

## Description
**Cella Works** is Human Resources Information System that enables the employee can do attendance anywhere in their customizable area and radius specified by their manager or Human Resource Department (HRD).

## Setup
- Run `composer install` for the first time only (best after running `git clone`)
- Prepare the .env files especially setting the port and database info
- Run through `php spark serve --port <num>` or
- You can set your own server on WIndows IIS and NGINX/Apache

## Note
**Database** will not be provided here. Use **migration** by running command `php spark migrate`
