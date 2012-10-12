# HumusMvcAssetManager
Based on [AssetManager](https://github.com/RWOverdijk/AssetManager) by [Wesley Overdijk](http://blog.spoonx.nl/) and [Marco Pivetta](http://ocramius.github.com/)

## Introduction
This module is intended for usage with a default directory structure of a
[HumusMvcSkeletonApplication](https://github.com/prolic/HumusMvcSkeletonApplication/). It provides functionality to load
assets and static files from your module directories through simple configuration.
This allows you to avoid having to copy your files over to the `public/` directory, and makes usage of assets very
similar to what already is possible with view scripts, which can be overridden by other modules.
In a nutshell, this module allows you to package assets with your module working *out of the box*.

## Installation

 1.  Require assetmanager:

```sh
./composer.phar require prolic/humus-mvc-assetmanager
#when asked for a version, type "dev-master" or "1.*". The latter being prefered.
```

## Usage

Take a look at the **[wiki](https://github.com/RWOverdijk/AssetManager/wiki)** for a quick start and more information.
A lot, if not all of the topics, have been covered in-dept there.