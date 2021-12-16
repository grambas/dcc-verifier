# dcc-verifier

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![coverage](https://codecov.io/gh/grambas/dcc-verifier/branch/main/graph/badge.svg?token=5XZZPANO03)](https://codecov.io/gh/grambas/dcc-verifier)
[![Total Downloads][ico-downloads]][link-downloads]

Alpha version of digital COVID-19 verification implementation with php.

## Install

Via Composer

``` bash
$ composer require grambas/dcc-verifier
```

## Info

This package implements Germany public trust list api [repository](https://github.com/Digitaler-Impfnachweis/certification-apis/blob/master/dsc-update/README.md). More about api - [Open API specification](https://github.com/Digitaler-Impfnachweis/certification-apis/blob/master/dsc-update/dsc-update-api.yaml)

## Features

* Decode and read EU Digital COVID-19 Certificate data (DCC)  of vaccination, recovery or test (PCR, Rapid) subject
* Validate DCC date of expiry 
* Validate DCC against authoritative signer signature
* Use Germany trust list
* Ability to use own trust list repository


## Usage

``` php
    $trustListDir = '/tmp/de-trust-list'; // local dir for saving trust list json file

    // 1. Download / Update Germany signer trust list
    $client = new GermanyTrustListClient($trustListDir);
    $client->update()
    
    // 2. Init existing trust list repository.
    $trustListRepository = new GermanyTrustListRepository($trustListDir);

    // 3. Init verfier with qr code content
    $qrCodeContent = 'HC1:...'
    $verifier = new DccVerifier($qrCodeContent, $trustListRepository);
    
    // 4. Decode & verify
    $dcc = $verifier->decode(); // get certificate info
    $verifier->verify(); // validate against signer signature
```

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ make test
$ make coverage
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email milius@mindau.de instead of using the issue tracker.

## Credits

- [Mindaugas Milius][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT), see [License File](LICENSE.md) and [NOTICE](NOTICE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/grambas/dcc-verifier.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/grambas/dcc-verifier/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/grambas/dcc-verifier.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/grambas/dcc-verifier.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/grambas/dcc-verifier.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/grambas/dcc-verifier
[link-scrutinizer]: https://scrutinizer-ci.com/g/grambas/dcc-verifier/code-structure
[link-downloads]: https://packagist.org/packages/grambas/dcc-verifier
[link-author]: https://github.com/grambas
[link-contributors]: ../../contributors