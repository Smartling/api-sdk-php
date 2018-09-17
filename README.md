[![Build Status](https://travis-ci.org/Smartling/api-sdk-php.svg?branch=3.0.0-g6)](https://travis-ci.org/Smartling/api-sdk-php)


[Smartling Translation API](http://docs.smartling.com)
=================

This repository contains the PHP SDK for accessing the Smartling Translation API.

The Smartling Translation API lets developers to internationalize their website or app by automating the translation and integration of their site content.
Developers can upload resource files and download the translated files in a language of their choosing. There are options to allow for professional translation, community translation and machine translation.

For a full description of the Smartling Translation API, please read the docs at: http://docs.smartling.com


Bug tracker
-----------

Have a bug? Please create an issue here on GitHub!

https://github.com/Smartling/api-sdk-php/issues


Hacking
-------

To get source code, clone the repo:

`git clone git@github.com:Smartling/api-sdk-php.git`

To contribute, fork it and follow [general GitHub guidelines](http://help.github.com/fork-a-repo/) with pull request.

Run tests
---------
`composer install`

`project_id=project_id user_id=user_id user_key=user_key ./vendor/bin/phpunit`

Copyright and license
---------------------

Copyright 2012 Smartling, Inc.

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

   http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.
