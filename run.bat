@echo off
cd /d "%~dp0"
:: Start The MySQL using XAMPP 
:: Start Nuxt.js in a new window
start cmd /k "cd Frontend\LelkiBekeFrontEnd && npm run dev"

:: Start Laravel backend
start cmd /k "cd Lelkibeke && php artisan serve"

:: Start Laravel WebSockets
start cmd /k "cd Lelkibeke && php artisan reverb:start"
