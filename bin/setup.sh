#!/bin/bash

wp-env run cli wp theme activate twentytwentythree
wp-env run cli wp rewrite structure /%postname%
wp-env run cli wp option update blogname "Manage Block Template"
wp-env run cli wp option update blogdescription "A simple plugin to manage block templates easily."
