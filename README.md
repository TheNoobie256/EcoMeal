# 🍲 EcoMeal

A dynamic, real-time marketplace built with **Symfony 7** that connects local businesses with surplus food to consumers looking for affordable meals. This platform aims to reduce food waste through a seamless, automated, and secure user experience.

## ✨ Core Features

* **Real-Time Live Feed (AJAX Polling):** The consumer dashboard utilizes JavaScript `setInterval` and the `fetch` API to silently ping the backend `/live-feed` route. New food packages appear instantly in the UI without requiring manual page reloads.
* **Interactive Neighborhood Maps:** Integrated with Leaflet and OpenStreetMap, transforming database backend coordinates into visual, interactive map pins so users can find food rescues physically close to them.
* **Automated Fan Alerts (Symfony Mailer):** Features a "Favorites" Many-To-Many relationship system. When a business posts a new package, the application automatically dispatches an HTML email containing absolute URLs to all users who have favorited that store.
* **Smart Inventory Management:** Utilizes custom Doctrine ORM cascade rules to create a "Soft Release" order flow. If a consumer cancels an order, only the order entity is destroyed; the package safely detaches and is immediately pushed back to the public live feed.
* **Strict Role-Based Access Control (RBAC):** Secure controller-level bouncers and role definitions (`ROLE_ADMIN`, `ROLE_BUSINESS`, `ROLE_CONSUMER`) ensure strict data isolation. Businesses can only view their own statistics, and consumers cannot access administrative routes.
* **Scalable Service Architecture:** Business analytics and revenue calculations are extracted into dedicated Symfony Services. This "brain" is shared seamlessly between the web Controller (for the graphical dashboard) and the terminal Command (for administrative console monitoring).

## 🛠️ Tech Stack

* **Backend:** PHP 8.x, Symfony 7, Doctrine ORM
* **Frontend:** Twig, Bootstrap 5, Vanilla JavaScript
* **Database:** MySQL / PostgreSQL (via Doctrine)
* **Integrations:** Symfony Mailer, Leaflet.js (Maps)

## 🚀 Installation & Setup

To get this project running locally on your machine, follow these steps:

**1. Clone the repository**
```bash
git clone [https://github.com/TheNoobie256/EcoMeal.git](https://github.com/TheNoobie256/EcoMeal.git)
cd EcoMeal
```

**2. Install PHP dependencies**
```bash
composer install
```

**3. Configure your Environment Variables**
Duplicate the `.env` file and rename it to `.env.local`. Update the database and mailer configurations to match your local environment:
```env
# .env.local
DATABASE_URL="mysql://root:password@127.0.0.1:3306/food_rescue?serverVersion=8.0.32&charset=utf8mb4"
MAILER_DSN=smtp://localhost:1025
```

**4. Setup the Database**
Create the database and execute the migrations to build the tables:
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

**5. Start the local Symfony server**
```bash
symfony server:start
```
The application will now be running at `http://localhost:8000`.

## 💻 Usage & Console Commands

**Testing the Terminal Analytics:**
To view real-time statistics for all registered businesses directly from your server terminal, run the custom console command:
```bash
php bin/console app:business-stats
```

**Testing the Roles:**
When navigating the application, try creating three separate accounts to test the isolated environments:
1. **Consumer:** Can browse the map, view the live feed, favorite businesses, and place orders.
2. **Business:** Can post new packages, view their private financial dashboard, but is blocked from placing orders.
3. **Admin:** Has global oversight and terminal command access.

## 🛡️ Security

This application implements strict CSRF (Cross-Site Request Forgery) protection on all authentication and form submissions, alongside server-side validation to ensure data integrity before database persistence.
