# 🚀 تست فنی برنامه‌نویس Backend (PHP/Laravel)

## 📋 شرح پروژه

شما باید یک **پنل مدیریت محتوای ساده** با قابلیت **تحلیل بازدید پست‌ها** پیاده‌سازی کنید. این سیستم باید بتواند بازدیدهای یکتای روزانه هر پست را به تفکیک کاربران ثبت‌نام‌شده محاسبه کرده و در قالب نمودار نمایش دهد.

---

## ⏱️ زمان تحویل
**حداکثر ۴۸ ساعت** از زمان دریافت تست

---

## 🎯 نیازمندی‌های اصلی

### ۱. سیستم احراز هویت (Authentication)
- [ ] ثبت‌نام کاربر (نام، ایمیل، رمز عبور)
- [ ] ورود/خروج کاربر
- [ ] استفاده از Laravel Sanctum برای API
- [ ] ارسال ایمیل خوش‌آمدگویی (با Queue)

### ۲. مدیریت پست‌ها (CRUD)
- [ ] ایجاد پست جدید (عنوان، محتوا، تصویر شاخص)
- [ ] لیست پست‌ها با Pagination

### ۳. سیستم رهگیری بازدید
- [ ] ثبت هر بازدید از پست (user_id, ip, user_agent)
- [ ] جلوگیری از ثبت بازدید تکراری یک کاربر در یک روز
- [ ] ثبت زمان دقیق بازدید

### ۴. داشبورد تحلیلی
- [ ] نمودار تعداد کاربران یکتای روزانه هر پست
- [ ] آمار کلی: بازدید کل، کاربران یکتا، میانگین روزانه
- [ ] فیلتر بر اساس بازه زمانی
- [ ] خروجی JSON برای رسم نمودار

---

## 🗄️ ساختار دیتابیس

### جدول `users`
- id, name, email, password, created_at, updated_at

### جدول `posts`
- id, user_id (FK), title, content, image, deleted_at, created_at, updated_at

### جدول `post_views`
- id, post_id (FK), user_id (FK, nullable), ip_address, user_agent, viewed_at

> **نکته مهم:** باید یک Unique Constraint روی ترکیب `post_id + user_id + DATE(viewed_at)` ایجاد کنید.

---

## 🔌 API Endpoints
### Authentication

POST   /api/register

POST   /api/login

GET    /api/user


## Posts

GET     /api/posts                     # لیست پست‌ها (عمومی)
```
  response :
  {
      "data": [
          {
              "id": 1,
              "title": "آموزش لاراول ۱۱",
              "content": "خلاصه محتوای پست...",
              "image": "https://example.com/storage/posts/image1.jpg",
              "author": {
                  "id": 1,
                  "name": "علی محمدی"
              },
              "views_count": 245,
              "unique_views_count": 120,
              "created_at": "2024-01-15T08:00:00Z",
              "updated_at": "2024-01-15T10:30:00Z"
          },
          {
              "id": 2,
              "title": "راهنمای PHP 8.3",
              "content": "خلاصه محتوای پست...",
              "image": "https://example.com/storage/posts/image2.jpg",
              "author": {
                  "id": 2,
                  "name": "مریم رضایی"
              },
              "views_count": 189,
              "unique_views_count": 95,
              "created_at": "2024-01-14T15:20:00Z",
              "updated_at": "2024-01-14T16:45:00Z"
          }
      ],
      "links": {
          "first": "https://example.com/api/posts?page=1",
          "last": "https://example.com/api/posts?page=5",
          "prev": null,
          "next": "https://example.com/api/posts?page=2"
      },
      "meta": {
          "current_page": 1,
          "from": 1,
          "last_page": 5,
          "per_page": 15,
          "total": 72
      }
  }
```
GET     /api/posts/{id}                # نمایش پست (ثبت بازدید)

POST    /api/posts                     # ایجاد پست


## Analytics
GET    /api/posts/{id}/analytics/daily?from=2024-01-01&to=2024-01-31
```
  response :
  {
      "data": {
          "post_id": 1,
          "title": "آموزش لاراول ۱۱",
          "period": {
              "from": "2024-01-01",
              "to": "2024-01-31"
          },
          "analytics": [
              {
                  "date": "2024-01-15",
                  "unique_users": 45,
                  "total_views": 120,
                  "registered_users": 30,
                  "guest_users": 15
              },
              {
                  "date": "2024-01-16",
                  "unique_users": 52,
                  "total_views": 145,
                  "registered_users": 38,
                  "guest_users": 14
              },
              {
                  "date": "2024-01-17",
                  "unique_users": 48,
                  "total_views": 132,
                  "registered_users": 35,
                  "guest_users": 13
              }
          ],
          "meta": {
              "total_days": 31,
              "total_unique_users": 450,
              "total_views": 1250,
              "average_daily_users": 50.2,
              "peak_day": "2024-01-16",
              "peak_users": 52,
              "trend": "upward",
              "trend_percentage": 12.5
          }
      }
  }
```
GET    /api/posts/{id}/analytics/summary
```
response :
  {
    "data": {
        "post_id": 1,
        "title": "آموزش لاراول ۱۱",
        "period": {
            "from": "2024-01-01",
            "to": "2024-01-31"
        },
        "analytics": [
            {
                "date": "2024-01-15",
                "unique_users": 45,
                "total_views": 120,
                "registered_users": 30,
                "guest_users": 15
            },
            {
                "date": "2024-01-16",
                "unique_users": 52,
                "total_views": 145,
                "registered_users": 38,
                "guest_users": 14
            },
            {
                "date": "2024-01-17",
                "unique_users": 48,
                "total_views": 132,
                "registered_users": 35,
                "guest_users": 13
            }
        ],
        "meta": {
            "total_days": 31,
            "total_unique_users": 450,
            "total_views": 1250,
            "average_daily_users": 50.2,
            "peak_day": "2024-01-16",
            "peak_users": 52,
            "trend": "upward",
            "trend_percentage": 12.5
        }
    }
}
```
GET    /api/posts/top-viewed?limit=10
```
  response :
  {
      "data": [
          {
              "rank": 1,
              "post_id": 1,
              "title": "آموزش لاراول ۱۱",
              "author": "علی محمدی",
              "total_views": 1250,
              "unique_users": 450,
              "trend": "upward",
              "created_at": "2024-01-15T08:00:00Z"
          },
          {
              "rank": 2,
              "post_id": 5,
              "title": "راهنمای Docker برای مبتدیان",
              "author": "مریم رضایی",
              "total_views": 980,
              "unique_users": 320,
              "trend": "stable",
              "created_at": "2024-01-10T12:30:00Z"
          },
          {
              "rank": 3,
              "post_id": 12,
              "title": "بهینه‌سازی کوئری در MySQL",
              "author": "حسین احمدی",
              "total_views": 845,
              "unique_users": 290,
              "trend": "downward",
              "created_at": "2024-01-05T09:45:00Z"
          }
      ],
      "meta": {
          "total_posts_analyzed": 72,
          "period_days": 7,
          "average_views_per_post": 345
      }
  }
```

## تست های که باید پاس بشوند
```
public function test_user_can_register()
{
      // تست ثبت‌نام موفق   
    // تست اعتبارسنجی ایمیل تکراری    
    
}

public function test_user_can_login()
{
      // تست ورود موفق
      // تست ورود با اطلاعات نادرست
      // تست توکن معتبر
}
```

### راه اندازی پروژه

```
# کلون پروژه
git clone [your-repo-url]
cd project

# نصب dependencies
composer install
cp .env.example .env

# تنظیم دیتابیس در .env
DB_CONNECTION=mysql
DB_DATABASE=post_analytics

# اجرای migration و seeder
php artisan migrate --seed
php artisan storage:link

# اجرای تست‌ها
php artisan test

```
