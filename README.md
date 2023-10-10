# Expiry
- An application specialized for storing documents digitally, reminding expiring items and ending warranties of consumer
goods.
- Implemented as a part of Jan Kousalík's bachelor thesis (https://dspace.cvut.cz/handle/10467/109564) (written in Czech)
  - Screenshots from the application can be found on page 61 in Annex A
 
## Abstract of the bachelor thesis
This bachelor thesis focuses on the design and implementation of a prototype
web application called Expiry – an application specialized for storing documents digitally, reminding expiring items and ending warranties of consumer
goods. The prototype was implemented using the PHP language, the Symfony framework and the Hotwire technology, which is, among other things,
thoroughly described in the theoretical part of this thesis.

Before the development of the prototype, an analysis of the needs of the
potential users of this application and a search of already existing applications
that specialize in solving the same problems was carried out. The resulting
prototype was deployed to a remote server and subjected to user testing.

## How to run the app locally
1. For the application to run, you need to have a running instance of the PostgreSQL database.
	- A docker-compose file is prepared that starts a container on port 5432 with the PostgreSQL database instance.
	- Just have the docker engine installed (https://docs.docker.com/engine/install/ubuntu/) and call `docker-compose up -d`
2. Set up the database connection in the configuration (.env) file.
	- If you are using a preconfigured containerized database, the .env file already contains `DATABASE_URL="postgresql://app:!ChangeMe!@127.0.0.1:5432/app?serverVersion=15&charset=utf8"` and thus nothing needs to be changed.
3. Install PHP and the composer software (https://getcomposer.org/), then install the PHP dependencies using the `composer install` command.
4. Run the database migration with `php bin/console doctrine:migration:migrate`
5. Install the npm software (https://docs.npmjs.com/downloading-and-installing-node-js-and-npm/) and then install the JavaScript dependencies using the `npm install` and `npm run build` commands.
6. Start the local PHP server using the command `php -S localhost:8080 -t public/`
	- The application will be available at `localhost:8080`

### Notes
- Email notifications will not work
- TODO: Docker application container
