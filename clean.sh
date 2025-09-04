#!/bin/bash
php artisan filament:clear-cached-components
php artisan cache:clear
php artisan config:clear
php artisan view:clear
php artisan route:clear
rm -fr storage/logs/*.log
rm -fr storage/framework/views/*.php

