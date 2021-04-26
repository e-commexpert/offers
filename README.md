# offers
Module offers for Dnd

## Installation

The extension must be installed via `composer`. To proceed, run these commands in your terminal:

```
composer config repositories.magegirl-offers git "https://github.com/magegirl/offers.git"
composer require dnd/module-offers
php bin/magento module:enable Dnd_Offers
php bin/magento setup:upgrade
php bin/magento setup:di:compile
php bin/magento setup:static-content:deploy
```
