
install: generate-env docker-build update

generate-env:
	@rm -f .env
	@echo "UID=`id -u`" >> .env

update: start install-vendors ## Update vendor dependancies

docker-build: ## Build docker image
	docker-compose build

start: stop ## Run containers
	docker-compose up -d

stop: ## Stop all containers
	docker-compose down

install-vendors: ## Install vendors for PHP and yarn
	docker-compose exec --user www-data php bash -c 'composer install'

php: ## Connect to the PHP container
	docker-compose exec --user www-data php /bin/bash


fix: ## Launch PHP-Cs-Fixer and fix PHP code
	docker-compose exec --user www-data php bash -c 'vendor/bin/php-cs-fixer fix --allow-risky=yes'
	docker-compose exec --user www-data php bash -c 'vendor/bin/phpstan analyse src/ -c phpstan.neon --level=8 --no-progress -vvv --memory-limit=1024M'
