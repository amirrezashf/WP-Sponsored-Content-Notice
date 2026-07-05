# WP Sponsored Content Notice

Displays a sponsored content notice before and after selected WordPress posts.

## Description

WP Sponsored Content Notice adds a clear advertising disclosure box to posts inside a target category.

By default, the plugin targets category ID `1951`. Each eligible post gets the notice before and after the content. Editors can disable the notice per post from a sidebar metabox.

## Features

- Shows a sponsored content notice on selected posts
- Targets posts by category ID
- Adds notice before and after post content
- Per-post disable option
- Responsive frontend design
- Frontend CSS loads only on eligible posts
- No custom database tables
- No external dependencies
- Filterable category ID and notice text
- Single-file plugin

## Requirements

- PHP 7.4+
- WordPress 6.0+

## Installation

1. Create a folder named `wp-sponsored-content-notice`.
2. Place `wp-sponsored-content-notice.php` inside it.
3. Upload the folder to:

```text
wp-content/plugins/
```

4. Activate the plugin from WordPress admin.

## Usage / How it Works

By default, posts inside category ID `1951` display the notice.

To disable the notice for a specific post:

1. Edit the post.
2. Find the “Sponsored Content Notice Settings” metabox.
3. Enable the disable checkbox.
4. Update the post.

## Data Storage

The plugin stores one post meta value only when the notice is disabled for a post:

```text
_wp_scn_disable_notice
```

No custom tables or external services are used.

## Development

Built with:

- WordPress Coding Standards
- Native WordPress APIs
- Post meta
- Category checks
- Metabox API
- Sanitized inputs
- Escaped outputs
- Translation-ready strings
- Lightweight single-file architecture

## Hooks

- `wp_enqueue_scripts`
- `the_content`
- `add_meta_boxes_post`
- `save_post_post`

## Filters

### `wp_scn_target_category_id`

Change the target category ID.

```php
add_filter(
	'wp_scn_target_category_id',
	static function () {
		return 123;
	}
);
```

### `wp_scn_notice_title`

Change the notice title.

### `wp_scn_desktop_notice_text`

Change the desktop notice text.

### `wp_scn_mobile_notice_text`

Change the mobile notice text.

### `wp_scn_should_show_notice`

Customize whether the notice should be shown for a post.

## Future Improvements

- Admin settings page
- Multiple category support
- Custom notice text from admin panel

## License

GPL-2.0-or-later

## Author

Amirreza Shayesteh Far

GitHub: https://github.com/amirrezashf

---

# هشدار محتوای تبلیغاتی وردپرس

نمایش هشدار محتوای تبلیغاتی در ابتدا و انتهای نوشته‌های منتخب وردپرس.

## توضیحات

افزونه WP Sponsored Content Notice برای نوشته‌های داخل یک دسته‌بندی مشخص، باکس هشدار محتوای تبلیغاتی نمایش می‌دهد.

به‌صورت پیش‌فرض، دسته‌بندی هدف `1951` است. برای هر نوشته واجد شرایط، هشدار در ابتدا و انتهای محتوا نمایش داده می‌شود. همچنین امکان غیرفعال کردن هشدار برای هر نوشته از متاباکس کناری وجود دارد.

## ویژگی‌ها

- نمایش هشدار تبلیغاتی برای نوشته‌های منتخب
- هدف‌گیری نوشته‌ها بر اساس category ID
- نمایش هشدار در ابتدا و انتهای محتوا
- امکان غیرفعال‌سازی برای هر نوشته
- طراحی واکنش‌گرا
- لود CSS فقط روی نوشته‌های واجد شرایط
- بدون جدول اختصاصی دیتابیس
- بدون وابستگی خارجی
- امکان تغییر category ID و متن‌ها با filter
- معماری تک‌فایلی

## نیازمندی‌ها

- PHP 7.4+
- WordPress 6.0+

## نصب

1. یک پوشه با نام `wp-sponsored-content-notice` بسازید.
2. فایل `wp-sponsored-content-notice.php` را داخل آن قرار دهید.
3. پوشه را در مسیر زیر آپلود کنید:

```text
wp-content/plugins/
```

4. افزونه را از پنل مدیریت وردپرس فعال کنید.

## نحوه استفاده / عملکرد افزونه

به‌صورت پیش‌فرض، نوشته‌های داخل دسته‌بندی `1951` هشدار تبلیغاتی را نمایش می‌دهند.

برای غیرفعال‌سازی هشدار در یک نوشته:

1. نوشته را ویرایش کنید.
2. متاباکس تنظیمات هشدار تبلیغاتی را پیدا کنید.
3. گزینه غیرفعال‌سازی را فعال کنید.
4. نوشته را به‌روزرسانی کنید.

## ذخیره‌سازی داده

افزونه فقط زمانی که هشدار برای یک نوشته غیرفعال شود، یک post meta ذخیره می‌کند:

```text
_wp_scn_disable_notice
```

جدول اختصاصی یا سرویس خارجی استفاده نمی‌شود.

## توسعه

توسعه داده‌شده بر اساس:

- WordPress Coding Standards
- Native WordPress APIs
- Post meta
- بررسی دسته‌بندی
- Metabox API
- ورودی‌های sanitize شده
- خروجی‌های escape شده
- متن‌های آماده ترجمه
- معماری سبک تک‌فایلی

## هوک‌ها

- `wp_enqueue_scripts`
- `the_content`
- `add_meta_boxes_post`
- `save_post_post`

## فیلترها

### `wp_scn_target_category_id`

تغییر category ID هدف.

```php
add_filter(
	'wp_scn_target_category_id',
	static function () {
		return 123;
	}
);
```

### `wp_scn_notice_title`

تغییر عنوان هشدار.

### `wp_scn_desktop_notice_text`

تغییر متن دسکتاپ.

### `wp_scn_mobile_notice_text`

تغییر متن موبایل.

### `wp_scn_should_show_notice`

تغییر منطق نمایش هشدار برای هر نوشته.

## بهبودهای آینده

- صفحه تنظیمات
- پشتیبانی از چند دسته‌بندی
- تغییر متن هشدار از پنل مدیریت

## مجوز

GPL-2.0-or-later

## نویسنده

Amirreza Shayesteh Far

GitHub: https://github.com/amirrezashf
