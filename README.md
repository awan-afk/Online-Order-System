# Online-Order-System
A sophisticated, single-page Online Order &amp; Invoice Management System built to demonstrate full-stack web development integration.

### Project Overview
The system allows users to select products, specify quantities, and instantly generate a professional digital invoice. It features automated business logic, such as dynamic discount calculations, and ensures data persistence by logging customers, orders, and individual line items into a relational database.

### Tech Stack
* Frontend: HTML5, Tailwind CSS (for modern UI), FontAwesome (icons).
* Client-Side Logic: JavaScript (ES6+) using the Fetch API for asynchronous (AJAX) form submission.
* Backend: PHP 8.x.
* Database: MySQL (Relational schema with 4 interconnected tables).
* Security: Implementation of Prepared Statements to prevent SQL Injection.

### Key Features
* Asynchronous Processing: Orders are submitted and invoices generated without refreshing the page, providing a smooth user experience.
* Dynamic Invoice Generation: A real-time summary table that calculates subtotals, taxes, and discounts based on server-side data.
* Automated Discount Logic: Applies a 10% discount automatically for orders exceeding Rs. 5,000.
* Relational Data Mapping: Automatically links customer entries to orders and maps order items to a centralized product catalog.
* Form Validation: Includes client-side checks to prevent empty submissions and negative quantities.
* Responsive Design: Fully optimized for mobile, tablet, and desktop views using Tailwind's utility-first grid system.

### Database Architecture
The backend is supported by a structured relational database consisting of:
* Customers Table: Stores unique customer identities.
* Products Table: Acts as the "Source of Truth" for pricing and product IDs.
* Orders Table: Records the transaction summary (Subtotal, Discount, Final Total).
* Order_Items Table: A bridge table for many-to-many relationships, storing the specific quantities and prices of items at the time of purchase.

### How to Use
* Clone the Repository: Download the source files to your local server (XAMPP/WAMP).
* Database Setup: Import the provided SQL schema to create the order system database.
* Configure Connection: Update the mysqli connection strings in the PHP header if your database credentials differ.
* Launch: Access the page via localhost to begin generating orders.
