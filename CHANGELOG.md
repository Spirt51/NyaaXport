# 1.1.0

## Русский

- Исправлены все 15 замечаний аудита: независимый от cookie лимит входов, обновление ID сессии, атомарная подготовка файла, фильтрация витрины OpenCart и пагинация вариантов Shopify.
- Cloud принимает только публичные HTTPS API, проверяет DNS и фиксирует проверенный IP для cURL; редиректы и прокси отключены.
- Исправлена семантика цен Shopify/Simpla/miniShop2, список валют YML и защита ячеек Meta CSV. Временные файлы XLSX очищаются при сбое.
- Интерфейс CMS и Cloud переведён на RU/UK/EN. Добавлены сообщения входа/выгрузки, две темы, доступные элементы управления и двухколоночные донаты с PayPal.
- Сайт использует реальные снимки страницы входа, три отдельных скачивания и инструкции с примером ключа.
- **Изменение возможностей:** пункты Prom/Rozetka удалены из списка форматов: прежний универсальный XML не соответствовал отдельным фидам. Google/Meta/YML обозначены как beta. Варианты OpenCart означают отдельные значения опций, без комбинаций. Другие CMS экспортируют основные товары; Simpla выбирает первый вариант детерминированно.
- Исходники разделены для сопровождения; каждый релизный PHP-файл остаётся автономным.

## Українська

- Виправлено зауваження аудиту щодо входу, підготовки файлів, вітрин OpenCart, варіантів і цін Shopify та адрес Cloud API.
- Повний інтерфейс RU/UK/EN, дві теми, повідомлення про стан експорту, мобільні донати та PayPal.
- На сайті три окремі завантаження, реальні знімки входу та приклад ключа.
- Prom/Rozetka вилучено зі списку форматів: універсальний XML не є окремим фідом. Google/Meta/YML мають статус beta. Значення опцій OpenCart не є комбінаціями; інші CMS експортують основні товари, Simpla використовує перший варіант.

## English

- Fixed cookie-independent login throttling, session rotation, atomic download preparation, OpenCart storefront membership and Shopify variant pagination/prices.
- Restricted Cloud requests to public HTTPS targets with DNS validation and pinned cURL resolution, without redirects or proxies.
- Localized CMS and Cloud UI in RU/UK/EN, added export/login feedback, light/dark themes and mobile two-column donations with PayPal.
- Replaced the mock interface images with actual PHP login screenshots and documented installation and adapter scope.
- **Capability correction:** removed Prom/Rozetka format entries because the old generic XML was not a dedicated feed. Google/Meta/YML remain beta. OpenCart exports individual option values rather than combinations. Other CMS adapters export base products; Simpla uses its first variant.

## Verification scope

Local regression checks run on PHP 5.6.40, 7.3 and 8.1, including XLSX archive integrity, late export failure, currency handling, Shopify pagination fixtures and unsafe API targets. HTTP tests cover session rotation, invalid CSRF, fresh-cookie throttling and logout. Browser checks cover themes, languages, mobile layout, forms, disabled JavaScript and unavailable localStorage.

No live store databases, marketplace import accounts or API credentials were supplied. Adapter SQL and external imports have not been certified against all supported store versions. Beta labels and explicit scope replace the previous unqualified compatibility claims.

Runtime binaries used for local checks: [official PHP Windows archives](https://downloads.php.net/~windows/releases/archives/). Shopify pricing semantics follow the [ProductVariant reference](https://shopify.dev/docs/api/admin-graphql/latest/objects/ProductVariant).
