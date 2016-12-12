test-server:
	php -S localhost:8989 -t tests/server

test-suite:
	@bin/coke
	@timeout 5s make test-server  > /dev/null &
	@bin/phpunit
