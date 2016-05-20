<?php
/*
 * Copyright (c) 2016, webvariants GbR, http://www.webvariants.de
 *
 * This file is released under the terms of the MIT license. You can find the
 * complete text in the attached LICENSE file or online at:
 *
 * http://www.opensource.org/licenses/mit-license.php
 */

// init the app
$app = new sly\Console\App($container, __DIR__);
sly_Core::setCurrentApp($app);
$app->initialize();

// ... and run it
$app->run();
