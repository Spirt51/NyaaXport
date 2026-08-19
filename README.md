# NyaaXport

[Русский](#русский) · [Українська](#українська) · [English](#english)

Single-file product exporter for self-hosted and cloud e-commerce platforms. Created by **Yevhen Levchenko**.

## Русский

NyaaXport — автономный экспортёр товаров без установки модулей и Composer. Загрузите один PHP-файл в корень магазина, выберите поля и скачайте каталог.

### Возможности

- Автоопределение CMS и её версии.
- OpenCart/ocStore, WooCommerce, PrestaShop, 1C-Битрикс, CS-Cart/Multi-Vendor, Magento 2, Shopware 6, Webasyst Shop-Script, MODX miniShop2, VirtueMart, HikaShop, JoomShopping, Simpla и OkayCMS.
- Отдельный API-экспортёр для Shopify, BigCommerce, Wix, Prom.ua и Horoshop/JSON API.
- CSV, XML, XLS, XLSX и фиды Google Merchant, Meta Catalog, YML, Prom.ua, Rozetka.
- Выбор полей, языка и витрины; фильтры по цене, наличию, статусу, категории, производителю и дате.
- SEO URL, изображения, варианты товаров, расширенные поля и потоковая обработка больших каталогов.
- Защита ключом, CSRF, ограничение попыток входа, тайм-аут сессии и список разрешённых IP.
- Интерфейс RU / UK / EN и встроенная диагностика сервера.

### Требования

- `nyaaxport_56.php`: PHP 5.6, PDO MySQL; ZipArchive для XLSX.
- `nyaaxport.php`: PHP 7.x–8.x, PDO MySQL; ZipArchive для XLSX.
- `nyaaxport_cloud.php`: PHP 7.x–8.x, cURL и API-токен магазина.
- Доступ к корню сайта и учётным данным базы из конфигурации CMS.

### Установка

1. Скачайте подходящий PHP-файл из [последнего релиза](../../releases/latest).
2. Замените `CHANGE-ME-NOW` в начале файла на длинный уникальный ключ.
3. Загрузите файл в корень сайта.
4. Откройте файл по HTTPS, войдите с ключом и выполните экспорт.
5. После работы удалите или переименуйте файл на сервере.

## Українська

NyaaXport — автономний експортер товарів без встановлення модулів і Composer. Завантажте один PHP-файл у корінь магазину, виберіть поля та отримайте каталог.

### Можливості

- Автоматичне визначення CMS та її версії.
- Підтримка локальних платформ OpenCart/ocStore, WooCommerce, PrestaShop, 1C-Бітрікс, CS-Cart, Magento 2, Shopware 6, Webasyst, MODX miniShop2, Joomla-магазинів, Simpla та OkayCMS.
- Окремий API-експортер для Shopify, BigCommerce, Wix, Prom.ua та Horoshop/JSON API.
- CSV, XML, XLS, XLSX і готові торгові фіди.
- Вибір полів, мови й вітрини, фільтри, SEO URL, зображення та варіанти товарів.
- Потокова обробка великих каталогів і вбудована діагностика.
- Захист ключем, CSRF, обмеження спроб входу та сесії.

### Вимоги та встановлення

- PHP 5.6 для `nyaaxport_56.php`; PHP 7–8 для інших версій.
- PDO MySQL, ZipArchive для XLSX; cURL для хмарних API.
- Завантажте файл із [останнього релізу](../../releases/latest), змініть `CHANGE-ME-NOW`, розмістіть у корені сайту та відкрийте через HTTPS.
- Після експорту видаліть або перейменуйте файл.

## English

NyaaXport is a standalone product exporter with no module or Composer installation. Upload one PHP file to the store root, select fields, and download the catalog.

### Features

- Automatic CMS and version detection.
- Self-hosted adapters for OpenCart/ocStore, WooCommerce, PrestaShop, 1C-Bitrix, CS-Cart, Magento 2, Shopware 6, Webasyst, MODX miniShop2, Joomla shops, Simpla, and OkayCMS.
- Separate API exporter for Shopify, BigCommerce, Wix, Prom.ua, and Horoshop/custom JSON APIs.
- CSV, XML, XLS, XLSX, Google Merchant, Meta Catalog, YML, Prom.ua, and Rozetka feeds.
- Field, language and storefront selection; price, inventory, status, category, manufacturer, and date filters.
- SEO URLs, images, variants, extended fields, and streaming for large catalogs.
- Access key, CSRF protection, login throttling, session timeout, optional IP allowlist, and server diagnostics.

### Requirements and installation

- PHP 5.6 for `nyaaxport_56.php`; PHP 7.x–8.x for the other builds.
- PDO MySQL and ZipArchive for XLSX; cURL for cloud APIs.
- Download a file from the [latest release](../../releases/latest), replace `CHANGE-ME-NOW`, upload it to the site root, and open it over HTTPS.
- Delete or rename the server-side file after export.

## Support the project

| Network | Address |
|---|---|
| USDT — TRC20 | `TR3fkALFRTDyRaHsDU8aUmANGPRDy2gU1s` |
| ETH — ERC20 | `0x9d20248c779dcb742f7795a0c7461346c0c8934e` |
| BTC | `1BQwypiKfhNGb1ryKzdp7TBPL4wR6ckU4J` |
| PayPal | `spirt51@hotmail.com` |

Always verify the selected cryptocurrency network and address before sending funds.

## Security

The exporter reads store data and does not modify the database. Keep it protected with a strong access key, use HTTPS, and remove it from the server when finished. API credentials entered into the cloud exporter are used for the current request and are not written to disk.

## License

[MIT](LICENSE) © Yevhen Levchenko
