# MageOS RequireJS Optimizer Module for Magento

Replace not useful JS dependencies from generated requirejs-config.js file on assets folder.

---


## Overview

#### !!! ATTENTION !!! 
This module is under development and is only a POF at the moment. Ex: involved controllers management is missing


The main purpose of this module is give the possibility to generate an alternative requirejs configuration file called "requirejs-config-optimized.js".
This file comes without some JS dependencies considered not useful for the frontend on specific controllers (ex: PDP, PLP, CMS pages). 

## Features

This module used as dependency let you specify the JS dependencies to remove on the generated requireJS config directly from di.xml file.

## Usage

Specify an argument preference on "MageOS\RequireJsOptimizer\Service\RequireJsConfigReplacer" namespace and change the following arguments:

#### moduleIgnoreList
List of modules where relative requirejs-config.js file shouldn't be merged inside requireJs config:
```
<item name="Magento_PageBuilder" xsi:type="string">Magento_PageBuilder</item>
```

#### map
List of "map" node aliases to remove from requireJs config:
```
<item name="ko" xsi:type="string">knockoutjs/knockout</item>
```

#### shim
List of "shim" node aliases to remove from requireJs config:
```
<item name="magnifier/magnifier" xsi:type="string">magnifier/magnifier</item>
```

#### jsMappings
List of js files overrides to operate as "map" requireJS node config:
```
<item name="mage/bootstrap" xsi:type="string">Vendor_Module/js/bootstrap</item>
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
