help: ## This command
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

.DEFAULT_GOAL := help

build: ## Builds the image using docker-compose
	docker-compose build --no-cache cakephp-cache-engines
start: ## Run the application in the background
	docker-compose up -d
start-build: ## Build the application and run application
	docker-compose up -d --force-recreate --remove-orphans
stop: ## Stop application
	@docker-compose stop
run-composer-install: ## Run composer install
	docker-compose exec -T cakephp-cache-engines \
		composer install
run-unit-tests: ## Run unit tests
	docker-compose exec -T cakephp-cache-engines \
		/app/bin/phpunit test