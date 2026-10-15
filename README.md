# NyaaXport

Single-file product exporter. Author: **Yevhen Levchenko**. [Website](https://spirt51.github.io/NyaaXport/) · [Downloads](https://github.com/Spirt51/NyaaXport/releases/latest) · [Changes](CHANGELOG.md)

## Русский

Скачайте один файл, замените `CHANGE-ME-NOW` на длинный уникальный ключ и загрузите в корень магазина. Откройте по HTTPS, войдите, выберите поля и фильтры, выполните экспорт. После работы удалите файл.

| Файл | Требования | Форматы |
|---|---|---|
| `nyaaxport.php` | PHP 7–8, PDO MySQL; ZipArchive для XLSX | CSV, XML, XLSX, Excel HTML (.xls) |
| `nyaaxport_56.php` | PHP 5.6, OpenSSL, PDO MySQL; ZipArchive для XLSX | Те же |
| `nyaaxport_cloud.php` | PHP 7–8, cURL, публичный HTTPS API и API-токен | CSV, XML, Excel HTML (.xls) |

Во временной папке должно хватать места для полной выгрузки. До завершения подготовки файл пользователю не отправляется. Приложение использует файловый лимит входов по IP: 8 неудачных попыток за 15 минут. Сессия истекает после 30 минут бездействия. При обратном прокси лимит учитывает `REMOTE_ADDR` сервера.

CMS-адаптеры: OpenCart/ocStore, WooCommerce, PrestaShop, 1C-Битрикс, CS-Cart, Magento 2, Shopware 6, Webasyst, MODX miniShop2, VirtueMart, HikaShop, JoomShopping, Simpla/OkayCMS. Cloud: Shopify, BigCommerce, Wix, Prom.ua, совместимый JSON API/Horoshop.

Адаптеры зависят от версии и настроек магазина: перед использованием сверьте цены, URL и остатки. Выбор языка товаров и витрины, а также выгрузка отдельных значений опций доступны в OpenCart. Комбинации опций не формируются. Другие CMS выгружают основные товары; Simpla использует первый вариант. Cloud Shopify выгружает страницы вариантов без ограничения первыми 100.

Google Merchant, Meta и YML имеют статус beta и требуют проверки импорта. Универсальный XML не является готовым Prom/Rozetka-фидом. Для Horoshop нужен совместимый JSON endpoint, а не только домен магазина. XLS содержит Excel HTML, не бинарный BIFF. Версия CMS может определяться приблизительно, если точные метаданные отсутствуют.

## Українська

Завантажте один файл для своєї версії PHP, замініть `CHANGE-ME-NOW` на довгий унікальний ключ і розмістіть у корені магазину. Відкрийте через HTTPS, увійдіть, виберіть поля та фільтри, виконайте експорт. Після роботи видаліть файл. Cloud можна розмістити окремо.

Для CMS потрібен PDO MySQL, для XLSX також ZipArchive. Для Cloud потрібен PHP 7–8, cURL, публічний HTTPS API та токен. У тимчасовій теці має вистачати місця для повного експорту. Інтерфейс доступний українською, російською та англійською.

Перевірте ціни, URL і залишки на своєму магазині. Значення опцій, мова товарів та вітрина доступні в OpenCart; комбінації не формуються. Інші CMS експортують основні товари; Simpla використовує перший варіант. Фіди Google/Meta/YML мають статус beta. Універсальний XML не є фідом Prom/Rozetka. Для Horoshop потрібен сумісний JSON endpoint.

## English

Download a PHP build, replace `CHANGE-ME-NOW` with your own long secret and upload the single file to the store root. Open it over HTTPS, sign in, select fields and filters, and export. Delete the exporter after use. Cloud may run separately.

CMS builds require PDO MySQL and ZipArchive for XLSX. Cloud requires PHP 7–8, cURL, a public HTTPS API and a token. Allow temporary disk space for the complete export. The UI supports RU/UK/EN and light/dark themes.

Verify prices, URLs and stock against your store configuration. OpenCart supports data language/store selection and individual option values, not combinations. Other CMS adapters export base products; Simpla selects its first variant. Google/Meta/YML feeds are beta; generic XML is not a dedicated Prom/Rozetka feed. Horoshop requires a compatible JSON endpoint. `.xls` means Excel HTML rather than binary BIFF.

## Development

Edit `src/cms.php`, `src/cloud.php`, `src/common.php` and `src/interface.css`. Build outputs in the repository root are standalone deployable files; no `src/`, npm or Composer dependencies are needed on the store server.

```sh
npm ci --ignore-scripts
npm run css
npm run build
node tests/syntax.mjs
php tests/regression.php
```

HTTP checks: run `node tests/prepare-web.mjs`, serve `.test-runtime/web` with PHP on `127.0.0.1:8766`, then run `node tests/web.mjs`. The local fixtures use a public test key and must never be deployed. Test fixtures do not connect to a production store.

Docker on Windows: `powershell -File tests/docker.ps1` builds and runs PHP 5.6, 7.0, 7.4, 8.1, 8.4 and 8.5. Sources are copied into the image, so sharing the project drive is unnecessary. Tests run with a read-only filesystem, temporary `/tmp` and no network. Legacy images compile bundled ZIP with stable zlib development headers from the signed Debian packages in the PHP 7.4 stage.

`tests/mysql.php` additionally checks OpenCart SQL against an isolated MariaDB database named `nyaaxport_test` supplied through `NX_TEST_DSN`; it recreates only its fixture tables. `tests/prepare-web.php` creates local HTTP fixtures in `/tmp/nyaaxport-web`. `tests/downloads.mjs` verifies every format, storefront membership, empty results and indexing headers against that fixture. These files are for tests only.

Every exporter emits `X-Robots-Tag: noindex, nofollow` and an equivalent HTML robots meta tag. The public project website remains indexable.

## Support

| Network | Address |
|---|---|
| USDT (TRC20) | `TR3fkALFRTDyRaHsDU8aUmANGPRDy2gU1s` |
| ETH (ERC20) | `0x9d20248c779dcb742f7795a0c7461346c0c8934e` |
| BTC | `1BQwypiKfhNGb1ryKzdp7TBPL4wR6ckU4J` |
| PayPal | [Support via PayPal](https://www.paypal.com/donate/?business=spirt51%40hotmail.com&currency_code=USD) |

Check the network and address before sending cryptocurrency.

[MIT License](LICENSE) © Yevhen Levchenko
