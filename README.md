# Database Sync - by Celso Nery

[![Maintainer](http://img.shields.io/badge/maintainer-@celsonery-blue.svg?style=flat-square)](https://x.com/celsonery)
[![Latest Version](https://img.shields.io/github/release/celsonery/db-sync-api.svg?style=flat-square)](https://github.com/celsonery/db-sync-api/releases)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE)
[![Build](https://img.shields.io/scrutinizer/build/g/celsonery/db-sync-api.svg?style=flat-square)](https://scrutinizer-ci.com/g/celsonery/db-sync-api)
[![Quality Score](https://img.shields.io/scrutinizer/g/celsonery/db-sync-api.svg?style=flat-square)](https://scrutinizer-ci.com/g/celsonery/db-sync-api/build-status/main)

## Installation

This package available by Composer:

```bash
git clone git@github.com/celsonery/db-sync-api.git
or
git clone https://github.com/celsonery/db-sync-api.git

composer update
php artisan key:generate
php artisan serve
```

## How to run in docker
- Build a docker image
```bash
docker build -t <image-name>:<image-version> -f docker/dockerfile
```

- Run the docker image
```bash
docker run -itd --rm --name <name-of-container> -p <local-port>:8000 <image-name>
```

## To run tests run
```bash
php artisan test
```
## You can see coverage tests in html report
```
/reports
```

## All changes
Please see [CHANGELOG](CHANGELOG.md) for more detail s.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for more details.

## Support

###### Security: If you discover any security related issues, please email celso.nery@gmail.com instead of using the issue tracker.

Thank you

## Credits

- [Celso Nery](https://github.com/celsonery) (Maintainer/Developer)
- [All Contributors](https://github.com/celsonery/db-sync-api/contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
