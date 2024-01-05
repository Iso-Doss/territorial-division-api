#mkdir "cfp-le-savoir-faire" $1
#cd "cfp-le-savoir-faire" $1
#git clone https://github.com/Iso-Doss/africa-express-cargo.git ./
composer install
composer update
composer dump-autoload
php artisan cache:config
php artisan cache:clear
php artisan store:link
npm install
cp .env.example .env
php artisan migrate:refresh --seed
php artisan telescope:install

cd /home/bloom/public_html/cfp.bloom.bj && php artisan queue:listen >/dev/null 2>&1
cd /home/bloom/public_html/cfp.bloom.bj && php artisan schedule:work >/dev/null 2>&1
Install supervisor

php artisan vendor:publish --provider "L5Swagger\L5SwaggerServiceProvider"
php artisan l5-swagger:generate
php artisan l5-swagger:generate --all
