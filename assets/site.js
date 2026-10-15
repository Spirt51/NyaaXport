(function () {
    var words = {
        ru: {
            theme: "Светлая / тёмная",
            lead: "Выгрузите каталог магазина с помощью одного PHP-файла. Выберите столбцы, отфильтруйте товары и скачайте таблицу.",
            main: "Скачать для PHP 7–8",
            legacy: "Скачать для PHP 5.6",
            cloud: "Скачать Cloud",
            cmsReq: "CMS-сборка: PDO MySQL; ZipArchive для XLSX.",
            legacyReq: "Старый PHP с тем же интерфейсом. OpenSSL, PDO MySQL; ZipArchive для XLSX.",
            cloudReq: "PHP 7–8, cURL, публичный HTTPS API и токен магазина. CSV, XML и Excel HTML.",
            release: "Релиз 1.1.0: изменения и ограничения",
            screens: "Интерфейс экспортёра",
            screenNote: "Работающий PHP-интерфейс входа, доступный до подключения к базе магазина.",
            languages: "Интерфейс и инструкция на русском, украинском и английском.",
            features: "Возможности экспорта",
            fields: "Название, артикул, URL, цена, остатки, описания, изображения и другие доступные поля.",
            filters: "Фильтры по цене, наличию, статусу, категории, производителю и дате изменения.",
            formats:
                "CSV, XML, XLSX и Excel HTML (.xls). Фиды Google Merchant, Meta и YML имеют статус beta: проверяйте перед импортом.",
            variants:
                "OpenCart: язык товаров, витрина и отдельные значения опций. Без комбинаций опций. Другие CMS выгружают основные товары; Simpla использует первый вариант.",
            protection: "Доступ и выгрузка",
            security:
                "Ключ доступа, проверка CSRF, лимит входов по IP на 15 минут и срок сессии. Список разрешённых IP в CMS-сборках.",
            atomic: "Полный файл готовится во временной папке до скачивания. При сбое подготовки показывается ошибка без отправки неполного каталога.",
            platforms: "Адаптеры и ограничения",
            limits: "Работа адаптеров зависит от версий CMS и настроек. Сверьте цены, URL и остатки со своим магазином. Облачные адаптеры и beta-фиды не проверены в вашем кабинете. Для Horoshop нужен совместимый JSON endpoint. Универсальный XML не является фидом импорта Prom/Rozetka.",
            install: "Установка и экспорт",
            step1: "Скачайте сборку для своей версии PHP.",
            step2: "Укажите длинный уникальный ключ в начале PHP-файла.",
            keyNote:
                "Строки CMS показывают до и после. Измените существующую строку, не добавляйте вторую константу. Создайте свой секрет.",
            step3: "Загрузите один PHP-файл в корень магазина и откройте по HTTPS. Cloud можно разместить отдельно.",
            step4: "Введите ключ, выберите поля и фильтры, затем выполните экспорт. В Cloud выберите платформу и введите данные API.",
            step5: "Проверьте скачанный файл и удалите экспортёр с сервера после работы.",
            disk: "Во временной папке должно хватать места для полной выгрузки. Composer и дополнительные файлы на сервере не нужны.",
            support: "Поддержать проект",
            supportText: "Если NyaaXport вам полезен, вы можете поддержать его разработку.",
            network: "Сеть",
            address: "Адрес",
            paypal: "Поддержать через PayPal",
            crypto: "Перед переводом криптовалюты проверьте сеть и адрес.",
        },
        uk: {
            theme: "Світла / темна",
            lead: "Експортуйте каталог магазину за допомогою одного PHP-файлу. Виберіть стовпці, відфільтруйте товари та завантажте таблицю.",
            main: "Завантажити для PHP 7–8",
            legacy: "Завантажити для PHP 5.6",
            cloud: "Завантажити Cloud",
            cmsReq: "CMS-збірка: PDO MySQL; ZipArchive для XLSX.",
            legacyReq: "Старий PHP з тим самим інтерфейсом. OpenSSL, PDO MySQL; ZipArchive для XLSX.",
            cloudReq: "PHP 7–8, cURL, публічний HTTPS API та токен магазину. CSV, XML і Excel HTML.",
            release: "Реліз 1.1.0: зміни та обмеження",
            screens: "Інтерфейс експортера",
            screenNote: "Робочий PHP-інтерфейс входу, доступний до підключення до бази магазину.",
            languages: "Інтерфейс та інструкція українською, російською й англійською.",
            features: "Можливості експорту",
            fields: "Назва, артикул, URL, ціна, залишки, описи, зображення та інші доступні поля.",
            filters: "Фільтри за ціною, наявністю, статусом, категорією, виробником і датою зміни.",
            formats:
                "CSV, XML, XLSX і Excel HTML (.xls). Фіди Google Merchant, Meta та YML мають статус beta: перевіряйте перед імпортом.",
            variants:
                "OpenCart: мова товарів, вітрина й окремі значення опцій. Без комбінацій опцій. Інші CMS експортують основні товари; Simpla використовує перший варіант.",
            protection: "Доступ та експорт",
            security:
                "Ключ доступу, перевірка CSRF, ліміт входів за IP на 15 хвилин і термін сесії. Список дозволених IP у CMS-збірках.",
            atomic: "Повний файл готується в тимчасовій теці до завантаження. У разі збою підготовки показується помилка без надсилання неповного каталогу.",
            platforms: "Адаптери та обмеження",
            limits: "Робота адаптерів залежить від версій CMS і налаштувань. Звірте ціни, URL та залишки зі своїм магазином. Хмарні адаптери й beta-фіди не перевірені у вашому кабінеті. Для Horoshop потрібен сумісний JSON endpoint. Універсальний XML не є фідом імпорту Prom/Rozetka.",
            install: "Встановлення та експорт",
            step1: "Завантажте збірку для своєї версії PHP.",
            step2: "Укажіть довгий унікальний ключ на початку PHP-файлу.",
            keyNote:
                "Рядки CMS показують до та після. Змініть наявний рядок, не додавайте другу константу. Створіть власний секрет.",
            step3: "Завантажте один PHP-файл у корінь магазину та відкрийте через HTTPS. Cloud можна розмістити окремо.",
            step4: "Уведіть ключ, виберіть поля й фільтри, потім виконайте експорт. У Cloud виберіть платформу та введіть дані API.",
            step5: "Перевірте завантажений файл і видаліть експортер із сервера після роботи.",
            disk: "У тимчасовій теці має вистачати місця для повного експорту. Composer і додаткові файли на сервері не потрібні.",
            support: "Підтримати проєкт",
            supportText: "Якщо NyaaXport вам корисний, ви можете підтримати його розробку.",
            network: "Мережа",
            address: "Адреса",
            paypal: "Підтримати через PayPal",
            crypto: "Перед переказом криптовалюти перевірте мережу та адресу.",
        },
    };
    var english = {};
    document.querySelectorAll("[data-t]").forEach(function (el) {
        english[el.dataset.t] = el.textContent;
    });
    function save(k, v) {
        try {
            localStorage.setItem(k, v);
        } catch (e) {}
    }
    function read(k) {
        try {
            return localStorage.getItem(k);
        } catch (e) {
            return null;
        }
    }
    function setLang(lang) {
        if (["ru", "uk", "en"].indexOf(lang) < 0) lang = "en";
        document.documentElement.lang = lang;
        var text = words[lang] || english;
        document.querySelectorAll("[data-t]").forEach(function (el) {
            el.textContent = text[el.dataset.t] || english[el.dataset.t];
        });
        document.querySelectorAll("[data-lang]").forEach(function (el) {
            el.setAttribute("aria-pressed", String(el.dataset.lang === lang));
        });
        var img = document.getElementById("interface");
        img.src = "assets/interface-" + lang + ".png";
        img.alt = {
            ru: "Страница входа NyaaXport на русском",
            uk: "Сторінка входу NyaaXport українською",
            en: "NyaaXport login interface in English",
        }[lang];
        save("nyaaxport_lang", lang);
    }
    document.querySelectorAll("[data-lang]").forEach(function (el) {
        el.onclick = function () {
            setLang(el.dataset.lang);
        };
    });
    document.documentElement.dataset.theme = read("nx_theme") || "light";
    document.getElementById("theme").onclick = function () {
        var theme = document.documentElement.dataset.theme === "dark" ? "light" : "dark";
        document.documentElement.dataset.theme = theme;
        save("nx_theme", theme);
    };
    setLang((read("nyaaxport_lang") || navigator.language || "en").slice(0, 2).toLowerCase());
})();
