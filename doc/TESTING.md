# Testing
As mentioned in the [contributing guidelines](CONTRIBUTING.md) document, all pull requests related to new features or
bug fixes should contain relevant tests, that pass.

In order to write tests for this project, you'll need to know how it's built and how it expects unit tests to be written
and executed.

To run the tests, you will need to make sure you have all the dependencies available:

```bash
  $ cd /path/to/cakephp-cache-engines
  $ composer install
```

You are now ready to run the tests:

```bash
  $ bin/phpunit test
```