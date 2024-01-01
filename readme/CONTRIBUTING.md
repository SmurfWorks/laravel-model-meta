# How to contribute to this package?

Honestly, this my (Glyn) first package that I expect other people may use so I've got a bit of experience to gain with
this part of the process.

My preferences would be to use a Github Issue to raise non-critical issues, and a pull request for any changes you'd
like to make directly. I probably won't accept breaking changes without a discussion first, but I'm open to suggestions.

Please ensure you write tests with good code coverage (sorry, this is completely arbitrary), write documentation
amendments where new features or configuration options are added, use Laravel Pint for automated formatting and
appease PHPStan.

Please also ensure you manually test JSONB and Fulltext columns with MySQL, SQLite tests can't do this automatically.

Don't forget to add yourself to the credits section below!

## Security Vulnerabilities

Obviously if you spot something, use common-sense and try not to highlight security vulnerabilities publicly before
giving a chance to fix them. Feel free to DM me on Twitter (@smurfworks) or reach out via email to github@smurfworks.com if you spot something.

## Quality Control

```bash
composer format # Laravel Pint
composer analyse # PHPStan
```

## Testing

```bash
composer test
composer test-coverage
```

## Credits

- [Glyn Simpson (Package Author)](https://github.com/SmurfWorks)
