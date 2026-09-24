# Contributing

Bug reports and pull requests are welcome.

- **Bugs:** open an issue with the action configuration you use and a small sample file with private data removed.
- **Questions and ideas:** use GitHub Discussions.
- **Security problems:** follow the [security policy](.github/SECURITY.md) and never open a public issue.

## Pull requests

1. Add or change a test in `tests/` that fails without your change.
2. Run the suite: `composer install && vendor/bin/pest`.
3. Keep one change per pull request, and describe the problem it solves.

A new translation needs the same keys as `resources/lang/en/import.php`; the test suite checks this.
