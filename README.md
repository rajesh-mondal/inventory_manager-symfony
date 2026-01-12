## Symfony Inventory Management System

This project is a **high-performance Inventory & User Management System** built on the **Symfony framework**. It is designed to bridge the gap between complex ERP systems and simple spreadsheets, providing a streamlined experience for managing assets and user permissions.

The application follows a **Monolithic Architecture** with a strong focus on **developer experience**, utilizing Symfony AssetMapper to manage front-end assets without the overhead of Node.js or Webpack. The system is strictly organized using the **MVC (Model–View–Controller)** pattern, ensuring that database interactions (via Doctrine ORM), business logic, and the UI layer (Twig) remain cleanly decoupled and easy to maintain.


## 🚀 Detailed Key Features

### 1. Advanced User Administration

The admin panel acts as the **core control center** of the system, providing high-level oversight of all users with a strong emphasis on efficiency and security.

- **Bulk Processing Engine:**  Utilizes a custom **JavaScript-to-PHP bridge** to handle multiple user actions—such as blocking, role upgrades, and deletions—in a single server request.

- **Intelligent UI Stacking:**  Features a fully responsive table design with **“Hide-and-Seek” logic**, where wide columns are hidden on smaller screens and their data is intelligently nested within primary cells to ensure **100% usability on mobile devices**.

- **Role-Based Access Control (RBAC):**  Leverages Symfony’s native security system to enforce strict access control, ensuring that sensitive routes (e.g., `/admin`) are **completely inaccessible** to non-admin users.

### 2. Dynamic Inventory Management

Built for scale, the inventory module allows users to **track items across different categories** with ease.

- **Entity Relationships:**  Utilizes **Doctrine ORM** to manage complex **One-to-Many** and **Many-to-Many** relationships between Inventories, Items, and Categories.

- **Contextual Actions:**  Users can perform **bulk edits** on inventory items, including moving items between categories or updating stock levels.

- **Custom ID Patterns:**  Allows storing and applying **customized ID formats** for inventory items, making it easier to maintain consistent item numbering.

- **Configurable Custom Fields:**  Enables defining which of the **15 optional fields** (e.g., text, number, date fields) are active for a particular inventory, offering maximum flexibility.

- **Auto-Save Functionality:**  Implements a **JavaScript-based auto-save** that sends a POST request to a Symfony controller every few seconds while users are editing, preventing data loss.

### 3. Item Management (The "Data")

- **Item Entity:**  Each item includes **fixed fields** such as `created_by` and `created_at`, along with **15 optional columns** in the database to store **custom fields** (e.g., `string_field_1`, `numeric_field_1`), providing flexibility for diverse inventory requirements.

- **ID Generation:**  Implements a **dynamic ID generation system** that concatenates fixed text, date, and sequence numbers based on the inventory’s predefined template whenever an item is saved.

- **Row Actions:**  Eliminates individual row buttons in favor of a **checkbox-based selection system**, allowing users to perform **global actions** like "Delete" or "Edit" directly from a centralized toolbar.

- **Last Update Tracking:**  Displays the **timestamp of the most recent modification** for each item, helping users monitor changes and maintain accurate inventory records.

### 4. Real-Time Discussion System

A **lightweight communication layer** designed for seamless team collaboration on specific inventory items.

- **Intelligent Polling:**  Utilizes a custom **JavaScript interval script** that fetches only new comments since the last received `id`, reducing server load compared to full page refreshes.

- **Recursive Cleanup:**  Prevents duplicate DOM elements by tracking state in the browser’s memory, ensuring **smooth scrolling** and uninterrupted user experience as new messages arrive.

### 5. Optimized Frontend Architecture

The frontend is designed for **performance, usability, and maintainability**, providing a smooth experience without heavy dependencies.

- **Component-Based CSS:**  All styles are centralized in `inventory.css`, following a **utility-first approach** similar to Tailwind, but optimized for **custom branding**.

- **Sticky Selection Bar:**  A **floating toolbar** that appears only when items are selected, giving users **immediate access to bulk actions** without the need to scroll.

- **Zero-Runtime JavaScript:**  Uses **vanilla JavaScript** for core interactions, avoiding the overhead of large frameworks like React or Vue for standard CRUD operations, resulting in **faster load times** and higher performance.

### 6. Social & Search Features

Enhances inventory interaction and discoverability through **real-time engagement** and **powerful search capabilities**.

- **Full-Text Search:**  Implements **MySQL FULLTEXT indexes** on the Inventory and Item tables, allowing users to perform **fast and accurate keyword searches**. A search bar is integrated into the Bootstrap navbar for easy access.

- **Likes & Comments:**  
  - **Comments:** Real-time updates are handled using **Symfony Mercure** or simple **AJAX polling** every 5 seconds, displaying new comments without requiring a page refresh.  
  - **Likes:** Users can toggle likes with a single click, storing entries in a **likes table** (User ID + Item ID) to track engagement.

- **Tag Cloud:**  A **dynamic section on the homepage** that displays the most frequently used tags, helping users quickly navigate popular inventory items.


### 🛠 Technical Highlights

- **Language & Framework:** PHP 8.2+ (Attributes, Typed Properties), Symfony  
- **Database Migration:** Doctrine Migrations for versioned schema control  
- **Security:** Sodium password hashing, CSRF/XSS/SQL Injection protection  
- **Performance:** Symfony Cache Component for faster loading  
- **Frontend:** Twig, Bootstrap 5, Vanilla JS, AJAX for dynamic interactions


## Installation & Setup

1. **Clone the repository**  
```bash
git clone https://github.com/yourusername/inventory-system.git
cd inventory-system
```

2. **Install Dependencies**
Install the backend dependencies using Composer:
```bash
composer install
```

3. **Environment Configuration**
Configure Environment: Copy `.env` to `.env.local` and update your database connection string:
```bash
DATABASE_URL="mysql://db_user:db_password@127.0.0.1:3306/db_name"
```

4. **Setup Database**
```bash
php bin/console doctrine:database:create
php bin/console doctrine:migrations:migrate
```

5. **Start the Server**

```bash
symfony serve
```
